<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Memproses checkout belanja, validasi stok, diskon, dan menyimpan Order.
     */
    public function processCheckout(array $data, User $user, ?string $appliedVoucherCode = null): Order
    {
        return DB::transaction(function () use ($data, $user, $appliedVoucherCode) {
            $cart = Cart::getOrCreateForUser($user->id);
            $items = $cart->items()->with(['product', 'variant'])->get();

            if ($items->isEmpty()) {
                throw new BusinessException('Keranjang belanja kosong.');
            }

            $total = 0;
            $orderItems = [];

            // 1. Validasi stok tersedia dan harga stabil
            foreach ($items as $item) {
                $product = $item->product;
                $variant = $item->variant;
                
                // Ambil limit stok
                $limit = $item->product_variant_id ? ($variant->stok_aktual ?? 0) : $product->stok_aktual;
                if ($item->quantity > $limit) {
                    throw new BusinessException("Stok produk '{$product->nama_barang}' tidak mencukupi. Tersedia: {$limit} unit.");
                }

                // Ambil harga actual
                $currentPrice = $item->getCurrentPrice();
                if ($item->price != $currentPrice) {
                    throw new BusinessException("Harga produk '{$product->nama_barang}' telah berubah. Silakan periksa kembali keranjang belanja Anda.");
                }

                $subtotalItem = $currentPrice * $item->quantity;
                $total += $subtotalItem;

                $orderItems[] = [
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $currentPrice,
                ];
            }

            // 2. Kalkulasi Voucher jika ada
            $discount = 0;
            if ($appliedVoucherCode && $user->role !== 'branch') {
                $voucher = Voucher::where('code', $appliedVoucherCode)->first();
                if ($voucher) {
                    $check = $voucher->isValidForUser($user, $total);
                    if ($check['valid']) {
                        $discount = $voucher->calculateDiscount($total);
                        if ($voucher->quota !== null) {
                            $voucher->decrement('quota');
                        }
                    }
                }
            }

            $ongkir = isset($data['ongkir']) ? (int) $data['ongkir'] : 0;
            $finalTotal = max(0, $total - $discount) + $ongkir;

            // 3. Simpan data Order ke database
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            
            // Generate unique invoice number
            while (Order::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            }

            // Set status default ke PENDING_PAYMENT (Deferred Stock Decrement)
            $orderStatus = ($data['metode_pembayaran'] === 'branch_request') ? OrderStatus::PENDING->value : OrderStatus::PENDING_PAYMENT->value;

            $order = Order::create([
                'user_id' => $user->id,
                'nama_penerima' => $data['nama_penerima'],
                'email' => $data['email'],
                'no_telp' => $data['no_telp'],
                'invoice_number' => $invoiceNumber,
                'total_harga' => $finalTotal,
                'voucher_code' => $discount > 0 ? $appliedVoucherCode : null,
                'voucher_discount' => $discount,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'alamat_pengiriman' => $data['alamat_pengiriman'],
                'ongkir' => $ongkir,
                'kurir' => $data['kurir'] ?? null,
                'courier_company' => $data['courier_company'] ?? null,
                'courier_type' => $data['courier_type'] ?? null,
                'layanan' => $data['layanan'] ?? null,
                'destination_area_id' => $data['destination_area_id'] ?? null,
                'destination_latitude' => isset($data['destination_latitude']) ? (float) $data['destination_latitude'] : null,
                'destination_longitude' => isset($data['destination_longitude']) ? (float) $data['destination_longitude'] : null,
                'status' => $orderStatus
            ]);

            // 4. Simpan ke order_items
            foreach ($orderItems as $oItem) {
                $order->items()->create($oItem);
            }

            // Catat ke usage history jika ada voucher yang diterapkan
            if (isset($voucher) && $discount > 0) {
                \App\Models\VoucherUsage::create([
                    'user_id' => $user->id,
                    'voucher_id' => $voucher->id,
                    'order_id' => $order->id,
                ]);
            }

            // 5. Kosongkan keranjang belanja
            $cart->clear();

            return $order;
        });
    }

    /**
     * Mengonfirmasi pembayaran pesanan, memotong stok secara aman (pessimistic lock), dan mencatat mutasi.
     */
    public function confirmPayment(Order $order): bool
    {
        $status = $order->status instanceof OrderStatus ? $order->status : OrderStatus::tryFrom($order->status);
        if ($status !== OrderStatus::PENDING_PAYMENT) {
            return false;
        }

        return DB::transaction(function () use ($order) {
            // Update status ke 'paid'
            $order->update(['status' => OrderStatus::PAID->value]);

            $hasStockDeficit = false;
            $deficitDetails = [];

            // Kurangi stok untuk setiap item dengan Pessimistic Locking
            foreach ($order->items as $orderItem) {
                // Kunci record produk untuk update
                $product = Product::where('id', $orderItem->product_id)->lockForUpdate()->first();
                if (!$product) {
                    continue;
                }
                
                if ($orderItem->product_variant_id) {
                    $variant = ProductVariant::where('id', $orderItem->product_variant_id)->lockForUpdate()->first();
                    if ($variant) {
                        if ($variant->stok_aktual < $orderItem->quantity) {
                            $hasStockDeficit = true;
                            $deficitDetails[] = "Varian '{$variant->nama_varian}' (Stok tersisa: {$variant->stok_aktual}, Terbayar: {$orderItem->quantity})";
                            Log::critical("CONCURRENCY ALERT: Pesanan {$order->invoice_number} telah dibayar namun stok varian '{$variant->nama_varian}' defisit! (Sisa: {$variant->stok_aktual}, Dibeli: {$orderItem->quantity})");
                            $variant->update(['stok_aktual' => max(0, $variant->stok_aktual - $orderItem->quantity)]);
                        } else {
                            $variant->decrement('stok_aktual', $orderItem->quantity);
                        }
                    }
                    $product->update(['stok_aktual' => max(0, $product->stok_aktual - $orderItem->quantity)]);
                } else {
                    if ($product->stok_aktual < $orderItem->quantity) {
                        $hasStockDeficit = true;
                        $deficitDetails[] = "Produk '{$product->nama_barang}' (Stok tersisa: {$product->stok_aktual}, Terbayar: {$orderItem->quantity})";
                        Log::critical("CONCURRENCY ALERT: Pesanan {$order->invoice_number} telah dibayar namun stok produk '{$product->nama_barang}' defisit! (Sisa: {$product->stok_aktual}, Dibeli: {$orderItem->quantity})");
                        $product->update(['stok_aktual' => max(0, $product->stok_aktual - $orderItem->quantity)]);
                    } else {
                        $product->decrement('stok_aktual', $orderItem->quantity);
                    }
                }

                // Catat ke sales_histories untuk kalkulasi Safety Stock harian
                try {
                    $today = now()->toDateString();
                    $history = \App\Models\SalesHistory::firstOrCreate(
                        [
                            'product_id' => $orderItem->product_id,
                            'product_variant_id' => $orderItem->product_variant_id,
                            'date' => $today,
                            'source' => 'ecommerce'
                        ],
                        [
                            'qty_sold' => 0
                        ]
                    );
                    $history->increment('qty_sold', (int) $orderItem->quantity);
                } catch (\Exception $shE) {
                    Log::warning('Failed logging sales history: ' . $shE->getMessage());
                }
            }

            // Kirim notifikasi konfirmasi pembayaran ke pembeli
            if ($order->user) {
                $order->user->notify(new \App\Notifications\OrderStatusNotification($order, 'Pembayaran untuk pesanan Anda telah berhasil dikonfirmasi dan pesanan sedang diproses.'));
            }

            // Integrasi Olsera POS: Push Sales Order secara asynchronous via Queue Job
            try {
                \App\Jobs\PushSalesOrderToOlseraJob::dispatch($order->id);
            } catch (\Exception $e) {
                Log::warning("Olsera Queue Dispatch failed for Order {$order->invoice_number}: " . $e->getMessage());
            }

            return true;
        });
    }

    /**
     * Membatalkan pesanan (akibat Midtrans expire/cancel/deny atau pembatalan admin),
     * mengembalikan stok (restock) jika pernah dipotong, dan merestorasi kuota voucher.
     */
    public function cancelOrder(Order $order): bool
    {
        $currentStatus = $order->status instanceof OrderStatus ? $order->status->value : $order->status;
        
        // Jangan batalkan pesanan yang sudah selesai dikirim / dibatalkan sebelumnya
        if (in_array($currentStatus, [OrderStatus::CANCELLED->value, OrderStatus::SHIPPED->value, OrderStatus::COMPLETED->value])) {
            return false;
        }

        return DB::transaction(function () use ($order, $currentStatus) {
            // 1. Jika pesanan sudah sempat berstatus 'paid' (stok pernah terpotong), kembalikan stok secara atomik
            if ($currentStatus === OrderStatus::PAID->value) {
                $order->loadMissing('items');
                foreach ($order->items as $item) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $product->increment('stok_aktual', $item->quantity);
                    }

                    if ($item->product_variant_id) {
                        $variant = ProductVariant::where('id', $item->product_variant_id)->lockForUpdate()->first();
                        if ($variant) {
                            $variant->increment('stok_aktual', $item->quantity);
                        }
                    }
                }

                Log::info("Restock Berhasil: Stok pesanan {$order->invoice_number} telah dikembalikan ke database.");
            }

            // 2. Kembalikan kuota voucher jika transaksi ini menggunakan voucher
            if ($order->voucher_code) {
                $voucher = Voucher::where('code', $order->voucher_code)->first();
                if ($voucher && $voucher->quota !== null) {
                    $voucher->increment('quota');
                }
            }

            // 3. Hapus catatan pemakaian voucher (jika ada) agar user bisa memakai voucher lagi di pesanan baru
            \App\Models\VoucherUsage::where('order_id', $order->id)->delete();

            // 4. Update status pesanan ke 'cancelled'
            $order->update(['status' => OrderStatus::CANCELLED->value]);

            Log::info("Order Cancelled: Pesanan {$order->invoice_number} berhasil dibatalkan (Status sebelumnya: {$currentStatus}).");

            return true;
        });
    }
}
