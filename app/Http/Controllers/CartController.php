<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\B2BRegistration;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckoutRequest;
use App\Services\OrderService;

class CartController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display cart items for offcanvas
     */
    public function offcanvas()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = Cart::getOrCreateForUser(Auth::id());
        $items = $cart->items()->with(['product', 'variant'])->get();

        return view('components._offcanvas_cart', compact('cart', 'items'));
    }

    /**
     * Display cart items
     */
    public function index()
    {
        $cart = Cart::getOrCreateForUser(Auth::id());
        $items = $cart->items()->with(['product', 'variant'])->get();
        
        // Periksa apakah ada perubahan harga
        $priceChanges = [];
        foreach ($items as $item) {
            if ($item->isPriceChanged()) {
                $priceChanges[] = [
                    'product' => $item->product->nama_barang,
                    'old_price' => $item->price,
                    'new_price' => $item->getCurrentPrice(),
                ];
            }
        }
        
        // Hitung cart count dan wishlist count untuk navbar
        $cartCount = $cart->items()->sum('quantity');
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;

        return view('pages.frontend.cart', compact('cart', 'items', 'priceChanges', 'cartCount', 'wishlistCount'));
    }

    /**
     * Add product to cart
     */
    public function addToCart(Request $request, $id)
    {
        // ✅ PERBAIKAN #6.3: Check jika user belum login
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu',
                    'redirect' => route('login')
                ], 401);
            }
            return redirect()->route('login');
        }

        // ✅ Check jika user adalah branch dengan status pending
        if (Auth::user()->role === 'branch') {
            $b2bReg = \App\Models\B2BRegistration::where('user_id', Auth::id())->first();
            if ($b2bReg && $b2bReg->status === 'pending') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun Anda sedang menunggu persetujuan admin. Belanja akan aktif setelah disetujui.'
                    ], 403);
                }
                return redirect()->route('b2b.pending')->with('warning', 'Akun Anda belum disetujui untuk berbelanja.');
            }
            if ($b2bReg && $b2bReg->status === 'rejected') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aplikasi B2B Anda telah ditolak. Silakan hubungi admin.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Aplikasi B2B Anda telah ditolak.');
            }
        }

        \Log::info('AddToCart called', ['user_id' => Auth::id(), 'product_id' => $id, 'quantity' => $request->input('quantity', 1)]);
        
        // ✅ Validasi parameter ID produk
        if (!is_numeric($id) || $id <= 0) {
            \Log::warning('Invalid product ID', ['id' => $id]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID produk tidak valid'
                ], 422);
            }
            return redirect()->back()->with('error', 'ID produk tidak valid');
        }

        try {
            $product = Product::with('variants')->findOrFail($id);
            \Log::info('Product found', ['product_id' => $id, 'product_name' => $product->nama_barang, 'stock' => $product->stok_aktual]);
            
            // Check if product has variants
            $hasVariants = $product->hasVariants();
            $variantId = null;
            $variant = null;
            
            if ($hasVariants) {
                $variantId = $request->input('product_variant_id');
                if (!$variantId) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Silakan pilih varian produk terlebih dahulu!'
                        ], 422);
                    }
                    return redirect()->back()->with('error', 'Silakan pilih varian produk terlebih dahulu!');
                }
                
                $variant = $product->variants()->findOrFail($variantId);
                $availableStock = $variant->stok_aktual;
            } else {
                $availableStock = $product->stok_aktual;
            }

            // Get quantity from request early so we can compute B2B tiered price
            $quantity = $request->has('quantity') ? (int) $request->input('quantity', 1) : 1;
            $isB2B = Auth::check() && Auth::user()->role === 'branch';

            if ($hasVariants) {
                $price = $isB2B ? $variant->getB2bPrice($quantity) : $variant->harga_jual_actual;
            } else {
                $price = $isB2B ? $product->getB2bPrice($quantity) : $product->harga_jual;
            }
            
            // ✅ Validasi stok tersedia
            if ($availableStock <= 0) {
                \Log::warning('Product out of stock', ['product_id' => $id, 'variant_id' => $variantId]);
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk tidak tersedia (stok habis)'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Produk tidak tersedia (stok habis)');
            }

            \Log::info('Quantity from request', ['quantity' => $quantity]);
            
            // Validasi quantity
            if ($quantity < 1 || $quantity > 1000) {
                \Log::warning('Invalid quantity', ['quantity' => $quantity]);
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jumlah harus antara 1-1000 unit'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Jumlah harus antara 1-1000 unit');
            }

            $cart = Cart::getOrCreateForUser(Auth::id());
            \Log::info('Cart retrieved/created', ['cart_id' => $cart->id, 'user_id' => Auth::id()]);
            
            // Cek apakah sudah ada di cart
            $query = $cart->items()->where('product_id', $id);
            if ($hasVariants) {
                $query->where('product_variant_id', $variantId);
            } else {
                $query->whereNull('product_variant_id');
            }
            $existingItem = $query->first();
            
            if ($existingItem) {
                // Update quantity jika sudah ada
                $newQuantity = $existingItem->quantity + $quantity;
                \Log::info('Existing item found, updating quantity', ['existing_qty' => $existingItem->quantity, 'new_qty' => $newQuantity]);
                
                // ✅ Validasi quantity tidak melebihi batas maksimal
                if ($newQuantity > 1000) {
                    \Log::warning('Quantity exceeds limit', ['new_quantity' => $newQuantity]);
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Jumlah maksimal per produk adalah 1000 unit'
                        ], 422);
                    }
                    return redirect()->back()->with('error', 'Jumlah maksimal per produk adalah 1000 unit');
                }
                
                // ✅ Validasi stok cukup
                if ($newQuantity > $availableStock) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stok tidak cukup. Tersedia hanya {$availableStock} unit"
                        ], 422);
                    }
                    return redirect()->back()->with('error', "Stok tidak cukup. Tersedia hanya {$availableStock} unit");
                }
                
                $existingItem->quantity = $newQuantity;
                $existingItem->price = $existingItem->getCurrentPrice();
                $existingItem->save();
            } else {
                // Tambah item baru
                
                // ✅ Validasi stok cukup untuk item baru
                if ($quantity > $availableStock) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stok tidak cukup. Tersedia hanya {$availableStock} unit"
                        ], 422);
                    }
                    return redirect()->back()->with('error', "Stok tidak cukup. Tersedia hanya {$availableStock} unit");
                }
                
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $id,
                    'product_variant_id' => $variantId,
                    'quantity' => $quantity,
                    'price' => $price, // Simpan harga saat ditambahkan
                ]);
            }
            
            // Hitung ulang total
            $cart->calculateTotal();
            
            // Get total items count in cart
            $cartCount = $cart->items()->sum('quantity');
            
            // Return response sesuai tipe request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan ke keranjang',
                    'cartCount' => $cartCount,
                    'cartTotal' => $cart->total_price ?? 0
                ], 200);
            }
            
            return redirect()->back()->with('success', 'Produk ditambahkan ke keranjang');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error adding to cart: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->all()
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat menambahkan produk ke keranjang.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menambahkan produk ke keranjang.');
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCart(Request $request)
    {
        try {
            // ✅ Validasi input dengan rules yang komprehensif
            $request->validate([
                'id' => 'required|integer|exists:cart_items,id',
                'quantity' => 'required|integer|min:1|max:1000',
            ], [
                'id.required' => 'ID item tidak boleh kosong',
                'id.integer' => 'ID item harus berupa angka',
                'id.exists' => 'Item tidak ditemukan di keranjang',
                'quantity.required' => 'Jumlah tidak boleh kosong',
                'quantity.integer' => 'Jumlah harus berupa angka bulat',
                'quantity.min' => 'Jumlah minimal adalah 1 unit',
                'quantity.max' => 'Jumlah maksimal adalah 1000 unit',
            ]);

            $item = CartItem::with(['product', 'variant'])->findOrFail($request->id);
            $product = $item->product;

            // ✅ Validasi stok cukup untuk quantity baru
            $limit = $item->product_variant_id ? ($item->variant->stok_aktual ?? 0) : $product->stok_aktual;
            if ($request->quantity > $limit) {
                return redirect()->back()->with('error', 
                    "Stok tidak cukup. Tersedia: {$limit} unit");
            }

            $item->quantity = $request->quantity;
            $item->price = $item->getCurrentPrice();
            $item->save();
            
            // Hitung ulang total
            $item->cart->calculateTotal();

            return redirect()->back()->with('success', 'Keranjang berhasil diperbarui!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating cart: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memperbarui keranjang belanja.');
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        try {
            // ✅ Validasi input dengan pesan error yang jelas
            $request->validate([
                'id' => 'required|integer|exists:cart_items,id',
            ], [
                'id.required' => 'ID item tidak boleh kosong',
                'id.integer' => 'ID item harus berupa angka',
                'id.exists' => 'Item tidak ditemukan di keranjang',
            ]);

            $item = CartItem::findOrFail($request->id);
            $cart = $item->cart;
            
            // ✅ Validasi item milik user yang login
            if ($cart->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Anda tidak berhak menghapus item ini');
            }
            
            $item->delete();
            
            // Hitung ulang total
            $cart->calculateTotal();

            return redirect()->back()->with('success', 'Produk berhasil dihapus!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error removing from cart: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menghapus produk dari keranjang.');
        }
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

    // --- FITUR WISHLIST (masih pakai session untuk sekarang) ---
    public function wishlist()
    {
        $wishlist = session()->get('wishlist', []);
        
        // Hitung cart count dan wishlist count untuk navbar
        $cartCount = 0;
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cartCount = $cart->items()->sum('quantity');
            }
        } else {
            $cart = session()->get('cart', []);
            foreach ($cart as $item) {
                $cartCount += $item['quantity'];
            }
        }
        $wishlistCount = count($wishlist);
        
        return view('pages.frontend.wishlist', compact('wishlist', 'cartCount', 'wishlistCount'));
    }

    public function addToWishlist($id)
    {
        $product = Product::findOrFail($id);
        $wishlist = session()->get('wishlist', []);
        $isAjax = request()->expectsJson();

        if (!isset($wishlist[$id])) {
            $wishlist[$id] = [
                "nama" => $product->nama_barang,
                "harga" => $product->harga_jual,
                "gambar" => $product->gambar
            ];
            session()->put('wishlist', $wishlist);
            
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan ke Wishlist!',
                    'wishlistCount' => count($wishlist),
                    'inWishlist' => true
                ]);
            }
            return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke Wishlist!');
        }
        
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => 'Produk sudah ada di Wishlist kamu.',
                'inWishlist' => true
            ]);
        }
        return redirect()->back()->with('info', 'Produk sudah ada di Wishlist kamu.');
    }

    public function removeFromWishlist($id)
    {
        $wishlist = session()->get('wishlist', []);
        $isAjax = request()->expectsJson();
        
        if (isset($wishlist[$id])) {
            unset($wishlist[$id]);
            session()->put('wishlist', $wishlist);
        }
        
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus dari Wishlist.',
                'wishlistCount' => count($wishlist),
                'inWishlist' => false
            ]);
        }
        return redirect()->back()->with('success', 'Produk berhasil dihapus dari Wishlist.');
    }
}