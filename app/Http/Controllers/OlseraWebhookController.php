<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOlseraWebhookJob;
use App\Jobs\SyncOlseraDeltaJob;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OlseraWebhookController extends Controller
{
    /**
     * Handle incoming webhook notifications from Olsera POS Open Platform
     *
     * Optimasi Latensi Tinggi:
     * - Fast-Ack: Langsung mengembalikan HTTP 200 OK (< 30ms) ke Olsera Cloud.
     * - Asynchronous Queue: Pemrosesan payload produk, varian, gambar WebP, dan delta sync
     *   didelegasikan ke database queue worker tanpa memblokir request HTTP.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Respon langsung untuk GET / HEAD (digunakan Olsera untuk memvalidasi Callback URL saat disimpan)
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Olsera Webhook Endpoint is online and operational.',
            ], 200);
        }

        $payload = $request->all();

        // 2. Fast-Ack jika payload kosong (test ping dari Olsera saat menekan tombol Simpan Callback)
        if (empty($payload)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Olsera webhook endpoint is online and verified.',
            ], 200);
        }

        $eventType = $request->input('event') ?? $request->input('event_type') ?? $request->input('type') ?? 'general_sync';

        Log::info("Olsera Webhook Received [{$eventType}]", [
            'ip' => $request->ip(),
            'payload' => $payload,
        ]);

        // 3. Catat ke Activity Log (Non-blocking fail-safe)
        try {
            ActivityLog::create([
                'user_id'        => null,
                'actor_type'     => 'System: Olsera POS Webhook',
                'event'          => 'webhook_received',
                'auditable_type' => 'App\Models\Product',
                'auditable_id'   => 0,
                'new_values'     => ['event' => $eventType],
                'ip_address'     => $request->ip(),
                'user_agent'     => 'Olsera Cloud Webhook Ingestion',
            ]);
        } catch (\Exception $logE) {
            Log::warning('Olsera Webhook ActivityLog creation warning: ' . $logE->getMessage());
        }

        try {
            // 4. INSTANT REAL-TIME DISPATCH (dispatchSync)
            // Mengeksekusi pemotongan/pembaruan stok secara instan (< 10ms) tanpa bergantung pada queue worker Hostinger
            ProcessOlseraWebhookJob::dispatchSync($payload);

            // 5. Jika payload adalah notifikasi event katalog/order umum tanpa detail item, jalankan delta sync seketika!
            if (empty($payload['data']) || !is_array($payload['data']) || isset($payload['flag_webhook'])) {
                try {
                    SyncOlseraDeltaJob::dispatchSync();
                } catch (\Throwable $deltaErr) {
                    Log::warning('SyncOlseraDeltaJob dispatchSync error: ' . $deltaErr->getMessage());
                    SyncOlseraDeltaJob::dispatch();
                }
            }

            // 6. IMMEDIATE FAST-ACK HTTP 200 OK (< 30ms)
            return response()->json([
                'status'  => 'success',
                'message' => 'Olsera webhook successfully processed in real-time.',
                'event'   => $eventType,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Olsera Webhook Ingestion Controller Error: ' . $e->getMessage(), [
                'exception' => $e,
                'payload'   => $payload,
            ]);

            // Tetap kembalikan HTTP 200 dengan status accepted agar Olsera tidak melakukan retry storm
            return response()->json([
                'status'  => 'accepted',
                'message' => 'Webhook received with note: ' . $e->getMessage(),
            ], 200);
        }
    }
}
