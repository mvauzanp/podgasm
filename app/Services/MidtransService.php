<?php

namespace App\Services;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    /**
     * Membuat atau mengambil Snap Token untuk pembayaran Order
     */
    public function getSnapToken(Order $order): string
    {
        if (!empty($order->snap_token)) {
            return $order->snap_token;
        }

        $order->loadMissing(['items.product', 'items.variant']);

        $itemDetails = [];
        foreach ($order->items as $item) {
            $name = $item->product ? $item->product->nama_barang : 'Produk';
            if ($item->variant && $item->variant->nama_varian) {
                $name .= ' - ' . $item->variant->nama_varian;
            }

            $itemDetails[] = [
                'id' => 'ITEM-' . $item->id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => Str::limit($name, 50),
            ];
        }

        if ($order->ongkir > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->ongkir,
                'quantity' => 1,
                'name' => 'Ongkos Kirim ' . strtoupper($order->kurir ?? ''),
            ];
        }

        if ($order->voucher_discount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -1 * (int) $order->voucher_discount,
                'quantity' => 1,
                'name' => 'Diskon Voucher (' . ($order->voucher_code ?? 'VOUCHER') . ')',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->invoice_number,
                'gross_amount' => (int) $order->total_harga,
            ],
            'customer_details' => [
                'first_name' => $order->nama_penerima,
                'email' => $order->email,
                'phone' => $order->no_telp,
                'shipping_address' => [
                    'first_name' => $order->nama_penerima,
                    'email' => $order->email,
                    'phone' => $order->no_telp,
                    'address' => $order->alamat_pengiriman,
                ]
            ],
            'item_details' => $itemDetails,
        ];

        // Filter spesifik payment channel jika dipilih
        $method = $order->metode_pembayaran;
        if ($method === 'midtrans_va') {
            $params['enabled_payments'] = ['bca_va', 'bni_va', 'bri_va', 'echannel', 'permata_va', 'other_va', 'cimb_va'];
        } elseif ($method === 'midtrans_qris') {
            $params['enabled_payments'] = ['gopay', 'qris', 'shopeepay'];
        } elseif ($method === 'midtrans_cc') {
            $params['enabled_payments'] = ['credit_card'];
        }

        try {
            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);
            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Generation Failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'invoice' => $order->invoice_number,
            ]);
            throw $e;
        }
    }

    /**
     * Memproses callback notifikasi webhook dari Midtrans
     */
    public function handleNotification(): array
    {
        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            Log::error('Error initializing Midtrans Notification: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Invalid notification payload'
            ];
        }

        $transactionStatus = $notif->transaction_status;
        $type = $notif->payment_type;
        $rawOrderId = $notif->order_id;
        // Parse invoice number jika pernah di-reset dengan suffix timestamp '-R'
        $invoiceNumber = explode('-R', $rawOrderId)[0];

        Log::info("Midtrans Notification Received for Invoice: {$rawOrderId} (Clean Invoice: {$invoiceNumber})", [
            'transaction_status' => $transactionStatus,
            'payment_type' => $type,
            'fraud_status' => $fraudStatus,
        ]);

        $order = Order::where('invoice_number', $invoiceNumber)->orWhere('invoice_number', $rawOrderId)->first();
        if (!$order) {
            Log::warning("Midtrans Notification: Order with invoice {$invoiceNumber} / {$rawOrderId} not found.");
            return [
                'status' => 'error',
                'message' => 'Order not found'
            ];
        }

        $orderService = app(OrderService::class);

        if ($transactionStatus == 'capture') {
            if ($type == 'credit_card') {
                if ($fraudStatus == 'challenge') {
                    $order->update(['status' => OrderStatus::PENDING_PAYMENT->value]);
                } else {
                    $orderService->confirmPayment($order);
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $orderService->confirmPayment($order);
        } elseif ($transactionStatus == 'pending') {
            $order->update(['status' => OrderStatus::PENDING_PAYMENT->value]);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $orderService->cancelOrder($order);
        }

        return [
            'status' => 'success',
            'message' => 'Notification processed successfully',
            'order_id' => $order->id,
            'invoice' => $order->invoice_number
        ];
    }

    /**
     * Reset token pembayaran agar pengguna bisa memilih ulang bank / channel lain di Midtrans
     */
    public function resetSnapToken(Order $order): string
    {
        $order->loadMissing(['items.product', 'items.variant']);

        // Gunakan suffix timestamp unik agar Midtrans menganggap ini transaksi baru
        $uniqueOrderId = $order->invoice_number . '-R' . time();

        $itemDetails = [];
        foreach ($order->items as $item) {
            $name = $item->product ? $item->product->nama_barang : 'Produk';
            if ($item->variant && $item->variant->nama_varian) {
                $name .= ' - ' . $item->variant->nama_varian;
            }

            $itemDetails[] = [
                'id' => 'ITEM-' . $item->id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => Str::limit($name, 50),
            ];
        }

        if ($order->ongkir > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->ongkir,
                'quantity' => 1,
                'name' => 'Ongkos Kirim ' . strtoupper($order->kurir ?? ''),
            ];
        }

        if ($order->voucher_discount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -1 * (int) $order->voucher_discount,
                'quantity' => 1,
                'name' => 'Diskon Voucher (' . ($order->voucher_code ?? 'VOUCHER') . ')',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $uniqueOrderId,
                'gross_amount' => (int) $order->total_harga,
            ],
            'customer_details' => [
                'first_name' => $order->nama_penerima,
                'email' => $order->email,
                'phone' => $order->no_telp,
                'shipping_address' => [
                    'first_name' => $order->nama_penerima,
                    'email' => $order->email,
                    'phone' => $order->no_telp,
                    'address' => $order->alamat_pengiriman,
                ]
            ],
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);
            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Reset Snap Token Failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'invoice' => $order->invoice_number,
            ]);
            throw $e;
        }
    }
}
