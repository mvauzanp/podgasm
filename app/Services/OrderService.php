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
                        
                        // Record usage kuota voucher
                        if ($voucher->quota !== null) {
                            $voucher->decrement('quota');
                        }
                        
                        // Catat ke usage history
                        \App\Models\VoucherUsage::create([
                            'user_id' => $user->id,
                            'voucher_id' => $voucher->id,
                            'discount_amount' => $discount,
                            'used_at' => now(),
                        ]);
                    }
                }
            }

            $finalTotal = max(0, $total - $discount);

            // 3. Simpan data Order ke database
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str_random(4));
            
            // Generate unique invoice number
            while (Order::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str_random(4));
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
                'status' => $orderStatus
            ]);

            // 4. Simpan ke order_items
            foreach ($orderItems as $oItem) {
                $order->items()->create($oItem);
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

            // Kurangi stok untuk setiap item dengan Pessimistic Locking
            foreach ($order->items as $orderItem) {
                // Kunci record produk untuk update
                $product = Product::where('id', $orderItem->product_id)->lockForUpdate()->firstOrFail();
                
                if ($orderItem->product_variant_id) {
                    $variant = ProductVariant::where('id', $orderItem->product_variant_id)->lockForUpdate()->firstOrFail();
                    if ($variant->stok_aktual < $orderItem->quantity) {
                        throw new BusinessException("Gagal konfirmasi pembayaran: Stok varian '{$variant->nama_varian}' tidak cukup.");
                    }
                    
                    $variant->decrement('stok_aktual', $orderItem->quantity);
                    $product->decrement('stok_aktual', $orderItem->quantity);
                } else {
                    if ($product->stok_aktual < $orderItem->quantity) {
                        throw new BusinessException("Gagal konfirmasi pembayaran: Stok produk '{$product->nama_barang}' tidak cukup.");
                    }
                    
                    $product->decrement('stok_aktual', $orderItem->quantity);
                }

                // Catat mutasi penjualan ke tabel transactions
                Transaction::create([
                    'product_id'   => $orderItem->product_id,
                    'jumlah'       => $orderItem->quantity,
                    'total_harga'  => $orderItem->price * $orderItem->quantity,
                    'jenis'        => ($order->user && $order->user->role === 'branch') ? 'B2B' : 'B2C',
                    'tanggal'      => now()
                ]);
            }

            // Kirim notifikasi
            if ($order->user) {
                $order->user->notify(new \App\Notifications\OrderStatusNotification($order, 'Pembayaran untuk pesanan Anda telah berhasil dikonfirmasi dan pesanan sedang diproses.'));
            }

            return true;
        });
    }

    /**
     * Membatalkan pesanan yang belum dibayar.
     */
    public function cancelOrder(Order $order): bool
    {
        $status = $order->status instanceof OrderStatus ? $order->status : OrderStatus::tryFrom($order->status);
        if ($status !== OrderStatus::PENDING_PAYMENT) {
            return false;
        }

        return DB::transaction(function () use ($order) {
            $order->update(['status' => OrderStatus::CANCELLED->value]);
            
            // Kembalikan kuota voucher jika digunakan
            if ($order->voucher_code) {
                $voucher = Voucher::where('code', $order->voucher_code)->first();
                if ($voucher && $voucher->quota !== null) {
                    $voucher->increment('quota');
                }
            }

            return true;
        });
    }
}

/**
 * Helper Str_random yang aman.
 */
if (!function_exists('Str_random')) {
    function Str_random($length = 16) {
        return \Illuminate\Support\Str::random($length);
    }
}
