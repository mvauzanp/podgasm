<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\OrderService;
use App\Exceptions\BusinessException;
use App\Enums\OrderStatus;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Tampilkan riwayat pesanan user
     */
    public function history()
    {
        // Mengambil pesanan milik user yang sedang login, diurutkan dari yang terbaru
        $orders = Order::with(['items.product', 'items.variant'])
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->get();
        
        // Hitung cart count dan wishlist count untuk navbar
        $cart = Cart::where('user_id', Auth::id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;

        return view('pages.frontend.history', compact('orders', 'cartCount', 'wishlistCount'));
    }

    /**
     * Tampilkan detail pesanan
     */
    public function show($id)
    {
        $order = Order::with(['items.product', 'items.variant'])
                    ->where('user_id', auth()->id())
                    ->findOrFail($id);
        
        // Hitung cart count dan wishlist count untuk navbar
        $cart = Cart::where('user_id', Auth::id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;

        $snapToken = null;
        if ($order->isPendingPayment()) {
            try {
                $snapToken = app(\App\Services\MidtransService::class)->getSnapToken($order);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not generate Snap Token: ' . $e->getMessage());
            }
        }

        return view('pages.frontend.order-detail', compact('order', 'cartCount', 'wishlistCount', 'snapToken'));
    }

    /**
     * ✅ PERBAIKAN #4: Konfirmasi pembayaran
     * 
     * Ini dipanggil SETELAH pembayaran berhasil dikonfirmasi
     * (Misalnya: dari payment gateway webhook atau payment confirmation)
     * 
     * Fungsi:
     * - Validasi order milik user yang login
     * - Validasi order status masih 'pending_payment'
     * - Dekrement stok untuk setiap item
     * - Update order status ke 'paid'
     * - Gunakan transaction untuk atomicity
     */
    public function confirmPayment(Request $request, $id)
    {
        // ✅ Validasi input
        $request->validate([
            'payment_reference' => 'nullable|string|max:255', // Reference dari payment gateway
        ]);

        try {
            $order = Order::where('user_id', Auth::id())->findOrFail($id);

            // ✅ Validasi order masih menunggu pembayaran
            if (!$order->isPendingPayment()) {
                return redirect()->back()
                    ->with('error', 'Pesanan ini tidak dapat dikonfirmasi pembayarannya.');
            }

            // ✅ Coba konfirmasi pembayaran
            if ($this->orderService->confirmPayment($order)) {
                return redirect()->route('order.show', $order->id)
                    ->with('success', 'Pembayaran berhasil dikonfirmasi! Pesanan diproses.');
            } else {
                return redirect()->back()
                    ->with('error', 'Gagal mengkonfirmasi pembayaran. Stok mungkin sudah habis.');
            }

        } catch (BusinessException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error confirming payment for Order ID ' . $id . ': ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat mengonfirmasi pembayaran.');
        }
    }

    /**
     * Batalkan pesanan
     */
    public function cancelOrder(Request $request, $id)
    {
        try {
            $order = Order::where('user_id', Auth::id())->findOrFail($id);

            // ✅ Validasi hanya bisa batalkan order yang menunggu pembayaran
            if (!$order->isPendingPayment()) {
                return redirect()->back()
                    ->with('error', 'Pesanan ini tidak dapat dibatalkan.');
            }

            // ✅ Batalkan order
            if ($this->orderService->cancelOrder($order)) {
                return redirect()->route('order.history')
                    ->with('success', 'Pesanan berhasil dibatalkan.');
            } else {
                return redirect()->back()
                    ->with('error', 'Gagal membatalkan pesanan.');
            }

        } catch (BusinessException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error cancelling Order ID ' . $id . ': ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat membatalkan pesanan.');
        }
    }

    /**
     * Halaman Tracking Pesanan
     */
    public function track($id)
    {
        $order = Order::with(['items.product', 'items.variant'])
                    ->where('user_id', auth()->id())
                    ->findOrFail($id);

        $trackingData = null;
        if ($order->resi && $order->kurir) {
            $biteship = app(\App\Services\BiteshipService::class);
            $trackingData = $biteship->getTracking($order->resi, $order->kurir);
        }
                    
        $cartCount = Cart::where('user_id', Auth::id())->first()?->items()->sum('quantity') ?? 0;
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;

        return view('pages.frontend.tracking', compact('order', 'cartCount', 'wishlistCount', 'trackingData'));
    }
}
