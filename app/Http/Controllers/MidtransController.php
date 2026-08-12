<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MidtransController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Webhook HTTP POST dari Midtrans untuk status transaksi
     */
    public function notification(Request $request)
    {
        $result = $this->midtransService->handleNotification();
        return response()->json($result);
    }

    /**
     * Dapatkan Snap Token untuk Order (AJAX / Request dari Frontend)
     */
    public function getSnapToken(Order $order)
    {
        if ($order->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke pesanan ini.'
            ], 403);
        }

        try {
            $snapToken = $this->midtransService->getSnapToken($order);
            return response()->json([
                'success' => true,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset Snap Token dan generate token baru agar bisa pilih bank lain
     */
    public function resetSnapToken(Order $order)
    {
        if ($order->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke pesanan ini.'
            ], 403);
        }

        try {
            $snapToken = $this->midtransService->resetSnapToken($order);
            return response()->json([
                'success' => true,
                'message' => 'Silakan pilih kembali bank / channel pembayaran baru!',
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset pilihan pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
