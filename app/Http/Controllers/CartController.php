<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{

    /**
     * Display cart items for offcanvas
     */
    public function offcanvas()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = Cart::getOrCreateForUser(Auth::id());
        $items = $cart->items()->with(['product', 'variant', 'cart.user'])->get();

        return view('components._offcanvas_cart', compact('cart', 'items'));
    }

    /**
     * Display cart items
     */
    public function index()
    {
        $cart = Cart::getOrCreateForUser(Auth::id());
        $items = $cart->items()->with(['product', 'variant', 'cart.user'])->get();
        
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
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak cukup. Tersedia: {$limit} unit",
                        'available_stock' => $limit
                    ], 422);
                }
                return redirect()->back()->with('error', 
                    "Stok tidak cukup. Tersedia: {$limit} unit");
            }

            $item->quantity = $request->quantity;
            $item->price = $item->getCurrentPrice();
            $item->save();
            
            // Hitung ulang total
            $item->cart->calculateTotal();
            $cartTotal = $item->cart->total_price;
            $cartCount = $item->cart->items()->sum('quantity');

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Keranjang berhasil diperbarui!',
                    'quantity' => $item->quantity,
                    'itemSubtotal' => $item->price * $item->quantity,
                    'itemSubtotalFormatted' => 'Rp ' . number_format($item->price * $item->quantity, 0, ',', '.'),
                    'cartTotal' => $cartTotal,
                    'cartTotalFormatted' => 'Rp ' . number_format($cartTotal, 0, ',', '.'),
                    'cartCount' => $cartCount
                ]);
            }

            return redirect()->back()->with('success', 'Keranjang berhasil diperbarui!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating cart: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->all()
            ]);
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat memperbarui keranjang belanja.'
                ], 500);
            }
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
    }}