<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
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

        return view('pages.frontend.order-detail', compact('order', 'cartCount', 'wishlistCount'));
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

            // ✅ Coba konfirmasi pembayaran (akan validate stok dan dekrement)
            if ($order->confirmPayment()) {
                return redirect()->route('order.show', $order->id)
                    ->with('success', 'Pembayaran berhasil dikonfirmasi! Pesanan diproses.');
            } else {
                return redirect()->back()
                    ->with('error', 'Gagal mengkonfirmasi pembayaran. Stok mungkin sudah habis.');
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * ✅ PERBAIKAN #4: Batalkan pesanan
     * 
     * User bisa membatalkan order yang masih menunggu pembayaran
     * Tidak perlu release stok karena belum dikurangi dari awal
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
            if ($order->cancelOrder()) {
                return redirect()->route('order.history')
                    ->with('success', 'Pesanan berhasil dibatalkan.');
            } else {
                return redirect()->back()
                    ->with('error', 'Gagal membatalkan pesanan.');
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
                    
        $cartCount = Cart::where('user_id', Auth::id())->first()?->items()->sum('quantity') ?? 0;
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;

        return view('pages.frontend.tracking', compact('order', 'cartCount', 'wishlistCount'));
    }
}
