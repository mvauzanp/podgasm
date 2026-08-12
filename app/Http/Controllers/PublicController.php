<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        // 1. Mengambil semua kategori dengan eager load children untuk Mega Menu
        $categories = Category::with('children')->get();
        
        // 2. Mengambil produk (Non-Promo) untuk grid utama dengan sorting
        $query = Product::where('is_promo', false);
        
        $sort = $request->query('sort', 'newest');
        if ($sort === 'price_low') {
            $query->orderBy('harga_jual', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('harga_jual', 'desc');
        } else { // default atau 'newest'
            $query->latest();
        }
        
        $products = $query->take(8)->get();

        // 3. Mengambil produk khusus promo untuk _promo-banner
        $promoProducts = Product::where('is_promo', true)
                                ->take(4)
                                ->get();

        // 3.5 Ambil banner promo aktif dari database
        $promoBanners = \App\Models\PromoBanner::where('is_active', true)
                                              ->orderBy('order', 'asc')
                                              ->orderBy('created_at', 'desc')
                                              ->get();

        // 4. LOGIKA TERBARU: Ambil cart count dari database jika user login, atau session jika belum
        $cartCount = 0;
        if (Auth::check()) {
            // User sudah login: ambil dari database
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cartCount = $cart->items()->sum('quantity');
            }
        } else {
            // User belum login: ambil dari session
            $cart = session()->get('cart', []);
            foreach ($cart as $item) {
                $cartCount += $item['quantity'];
            }
        }
        
        // 5. Ambil wishlist count
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;
        
        // 6. Kirim semua variabel ke view
        return view('pages.public.index', compact(
            'categories', 
            'products', 
            'promoProducts', 
            'promoBanners',
            'cartCount',
            'wishlistCount' // Variabel ini yang akan dipakai di Navbar
        ));
    }

    public function categoryIndex($slug)
    {
        // Cari kategori berdasarkan slug
        $category = Category::where('slug', $slug)->firstOrFail();

        // Ambil semua kategori dengan eager load children untuk Navbar & Sidebar
        $categories = Category::with('children')->get();

        // Ambil ID kategori beserta seluruh ID sub-kategorinya jika ini adalah kategori induk (parent)
        $categoryIds = $category->children->count() > 0 
            ? $category->children->pluck('id')->push($category->id)
            : [$category->id];

        // Ambil produk yang termasuk dalam kategori atau sub-kategorinya
        $products = Product::whereIn('category_id', $categoryIds)
                            ->latest()
                            ->paginate(12); // Menggunakan pagination agar tidak berat
        
        // Hitung cart count (sama seperti di index)
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
        
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;

        return view('pages.public.catalog', compact('category', 'categories', 'products', 'cartCount', 'wishlistCount'));
    }

    // ✅ PERBAIKAN #6: Product detail page
    public function show($slug)
    {
        // Cari produk berdasarkan slug dan load reviews
        $product = Product::with('reviews.user')->where('slug', $slug)->firstOrFail();
        
        // Ambil semua kategori untuk navbar
        $categories = Category::with('children')->get();
        
        // Ambil produk serupa (same category, exclude current product)
        $relatedProducts = Product::where('category_id', $product->category_id)
                                   ->where('id', '!=', $product->id)
                                   ->latest()
                                   ->take(4)
                                   ->get();
        
        // Hitung cart count
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
        
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;
        
        // Cek jika produk di wishlist user
        $inWishlist = false;
        if (auth()->check()) {
            // TODO: Implement wishlist check dari database
            // $inWishlist = auth()->user()->wishlist()->where('product_id', $product->id)->exists();
        }
        
        return view('pages.public.product-detail', compact('product', 'categories', 'relatedProducts', 'inWishlist', 'cartCount', 'wishlistCount'));
    }

    // ✅ Search products
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));
        $categories = Category::with('children')->get();
        
        // Validasi query
        if (strlen($query) < 2) {
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
            $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;
            
            return view('pages.public.search-results', compact('categories', 'cartCount', 'wishlistCount'))
                ->with('products', collect())
                ->with('query', $query)
                ->with('message', 'Masukkan minimal 2 karakter untuk pencarian');
        }
        
        // Search pintar di nama produk, kode barang, deskripsi, dan relasi nama kategori
        $products = Product::with('category')
            ->where(function($q) use ($query) {
                $q->where('nama_barang', 'like', '%' . $query . '%')
                  ->orWhere('kode_barang', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhereHas('category', function($catQuery) use ($query) {
                      $catQuery->where('nama_kategori', 'like', '%' . $query . '%');
                  });
            })
            ->latest()
            ->paginate(12)
            ->appends(['q' => $query]);
        
        // Hitung cart count
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
        
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;
        
        return view('pages.public.search-results', compact('categories', 'products', 'query', 'cartCount', 'wishlistCount'));
    }
}