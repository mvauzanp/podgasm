<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyOlseraWebhook
{
    /**
     * Handle an incoming request from Olsera POS Webhook.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 0. Izinkan request GET / HEAD untuk health check / ping verifikasi URL dari Olsera saat disimpan
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        // 1. Izinkan test ping saat registrasi URL (payload kosong atau event ping)
        if (empty($request->all()) || $request->input('event') === 'test' || $request->input('event') === 'ping') {
            return $next($request);
        }

        $configuredSecret = config('services.olsera.webhook_secret', env('OLSERA_WEBHOOK_SECRET'));

        // Jika secret belum diset di .env, izinkan dengan peringatan log
        if (empty($configuredSecret)) {
            return $next($request);
        }

        // 2. Ekstrak token dari Authorization header (Bearer), X-Olsera-Secret, atau X-Olsera-Signature
        $bearerToken = $request->bearerToken();
        $headerSecret = $request->header('X-Olsera-Secret') ?? $request->header('X-Olsera-Signature') ?? $request->header('x-olsera-secret');
        $providedSecret = $bearerToken ?? $headerSecret ?? $request->input('secret');

        // Jika token dikirimkan tetapi tidak cocok, tolak
        if ($providedSecret && !hash_equals((string) $configuredSecret, (string) $providedSecret)) {
            Log::warning('Olsera Webhook Unauthorized Attempt: Invalid Token Provided', [
                'ip'              => $request->ip(),
                'provided_secret' => substr((string) $providedSecret, 0, 4) . '***',
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid Olsera Webhook Secret.'
            ], 401);
        }

        return $next($request);
    }
}
