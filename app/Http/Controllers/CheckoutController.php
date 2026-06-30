<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Voucher;
use App\Services\OrderService;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show checkout page
     */
    public function checkout()
    {
        $cart = Cart::getOrCreateForUser(Auth::id());
        $items = $cart->items()->with(['product', 'variant'])->get();
        
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong!');
        }
        
        // Hitung subtotal belanja
        $subtotal = $cart->total_price;
        $discount = 0;
        $voucher = null;
        $voucherError = null;

        $appliedVoucherCode = session('applied_voucher_code');
        if ($appliedVoucherCode) {
            $voucher = Voucher::where('code', $appliedVoucherCode)->first();
            if ($voucher) {
                $check = $voucher->isValidForUser(Auth::user(), $subtotal);
                if ($check['valid']) {
                    $discount = $voucher->calculateDiscount($subtotal);
                } else {
                    $voucherError = $check['message'];
                    session()->forget('applied_voucher_code');
                }
            } else {
                session()->forget('applied_voucher_code');
            }
        }
        
        // Hitung cart count dan wishlist count untuk navbar
        $cartCount = $cart->items()->sum('quantity');
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;

        return view('pages.frontend.checkout', compact('cart', 'items', 'cartCount', 'wishlistCount', 'voucher', 'discount', 'voucherError'));
    }

    /**
     * Apply B2C Voucher code
     */
    public function applyVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50'
        ]);

        // Only allow B2C users (customers) to use B2C vouchers
        if (Auth::user()->role === 'branch') {
            return response()->json([
                'success' => false,
                'message' => 'Akun B2B cabang tidak dapat menggunakan voucher B2C.'
            ], 403);
        }

        $code = strtoupper(trim($request->code));
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak valid.'
            ]);
        }

        $cart = Cart::getOrCreateForUser(Auth::id());
        $subtotal = $cart->total_price;

        $check = $voucher->isValidForUser(Auth::user(), $subtotal);
        if (!$check['valid']) {
            return response()->json([
                'success' => false,
                'message' => $check['message']
            ]);
        }

        // Save voucher code to session
        session(['applied_voucher_code' => $code]);
        $discount = $voucher->calculateDiscount($subtotal);
        $finalTotal = $subtotal - $discount;

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil diterapkan!',
            'type' => $voucher->type,
            'label' => $voucher->type === 'shipping_subsidy' ? 'Subsidi Ongkir' : 'Potongan Voucher',
            'discount' => $discount,
            'discount_formatted' => 'Rp ' . number_format($discount, 0, ',', '.'),
            'final_total' => $finalTotal,
            'final_total_formatted' => 'Rp ' . number_format($finalTotal, 0, ',', '.')
        ]);
    }

    /**
     * Remove applied B2C Voucher code
     */
    public function removeVoucher()
    {
        session()->forget('applied_voucher_code');
        $cart = Cart::getOrCreateForUser(Auth::id());
        
        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dihapus.',
            'final_total' => $cart->total_price,
            'final_total_formatted' => 'Rp ' . number_format($cart->total_price, 0, ',', '.')
        ]);
    }

    /**
     * Process checkout - Create order
     */
    public function processCheckout(CheckoutRequest $request)
    {
        try {
            $voucherCode = session('applied_voucher_code');
            $order = $this->orderService->processCheckout(
                $request->validated(),
                Auth::user(),
                $voucherCode
            );

            // Clear voucher session
            session()->forget('applied_voucher_code');

            // Redirect ke halaman pembayaran (akan diimplementasi di payment gateway)
            return redirect()->route('order.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');

        } catch (\App\Exceptions\BusinessException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error processing checkout: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memproses checkout Anda.');
        }
    }
}
