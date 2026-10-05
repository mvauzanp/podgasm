<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiteshipWebhookController extends Controller
{
    /**
     * Handle incoming webhook event notifications from Biteship
     * Sesuai dokumentasi resmi Biteship: https://biteship.com/id/docs/api/webhooks
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('Biteship Webhook Received:', $payload);

        $event = $payload['event'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $waybillId = $payload['courier_waybill_id'] ?? $payload['courier_tracking_id'] ?? null;
        $status = strtolower($payload['status'] ?? '');

        if (!$orderId && !$waybillId) {
            return response()->json([
                'success' => false,
                'message' => 'No order identifier provided'
            ], 400);
        }

        // Cari Order berdasarkan biteship_order_id atau nomor resi
        $order = Order::where('biteship_order_id', $orderId)
            ->orWhere('resi', $waybillId)
            ->first();

        if (!$order) {
            Log::warning("Biteship Webhook: Order not found for Biteship ID: {$orderId} / AWB: {$waybillId}");
            return response()->json([
                'success' => true,
                'message' => 'Order not found in database, acknowledged'
            ], 200);
        }

        // Update nomor resi jika baru terbit dari kurir
        if ($waybillId && empty($order->resi)) {
            $order->resi = $waybillId;
        }

        $oldStatus = $order->status;
        $statusChanged = false;
        $notifyMessage = null;

        // Map status Biteship ke Enum OrderStatus Podgasm
        switch ($status) {
            case 'allocated':
            case 'picking_up':
            case 'picked':
                // Kurir ditugaskan / sedang menjemput paket
                if ($order->status === OrderStatus::PENDING_PAYMENT->value) {
                    $order->status = OrderStatus::PAID->value;
                    $statusChanged = true;
                }
                break;

            case 'dropping_off':
            case 'on_process':
            case 'in_transit':
                // Paket sedang dalam perjalanan oleh kurir
                if ($order->status !== OrderStatus::SHIPPED->value && $order->status !== OrderStatus::COMPLETED->value) {
                    $order->status = OrderStatus::SHIPPED->value;
                    $statusChanged = true;
                    $notifyMessage = "Pesanan Anda #{$order->invoice_number} sedang dalam perjalanan dengan nomor resi: {$order->resi}.";
                }
                break;

            case 'delivered':
                // Paket telah diterima oleh pelanggan
                if ($order->status !== OrderStatus::COMPLETED->value) {
                    $order->status = OrderStatus::COMPLETED->value;
                    $statusChanged = true;
                    $notifyMessage = "Pesanan Anda #{$order->invoice_number} telah berhasil sampai di tujuan. Terima kasih telah berbelanja di Podgasm!";
                }
                break;

            case 'returned':
            case 'return_in_transit':
            case 'returned_to_origin':
                Log::warning("Biteship Webhook: Paket order {$order->invoice_number} berstatus RETUR/KEMBALI KE ASAL oleh kurir.");
                break;

            case 'cancelled':
            case 'rejected':
                if ($order->status !== OrderStatus::COMPLETED->value && $order->status !== OrderStatus::SHIPPED->value) {
                    $order->status = OrderStatus::CANCELLED->value;
                    $statusChanged = true;
                    $notifyMessage = "Pengiriman pesanan Anda #{$order->invoice_number} dibatalkan oleh pihak kurir.";
                }
                break;
        }

        $order->save();

        // Kirim notifikasi jika status pengiriman berubah
        if ($statusChanged && $notifyMessage && $order->user) {
            try {
                $order->user->notify(new OrderStatusNotification($order, $notifyMessage));
            } catch (\Exception $e) {
                Log::warning("Failed sending webhook status notification for {$order->invoice_number}: " . $e->getMessage());
            }
        }

        Log::info("Biteship Webhook: Order #{$order->invoice_number} updated status from '{$oldStatus}' to '{$order->status}' (AWB: {$order->resi}).");

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed successfully',
            'order_id' => $order->id,
            'invoice' => $order->invoice_number,
            'status' => $order->status,
        ], 200);
    }
}
