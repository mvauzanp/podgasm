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
        // 1. Mengambil semua kategori publik dengan eager load children untuk Mega Menu (Filter excluded non-vape)
        $categories = Category::publicVisible()->with('children')->get();
        
        // 2. Mengambil SEMUA produk publik untuk grid utama dengan sorting & pagination
        $query = Product::publicVisible();

        // Filter kategori opsional jika ada query ?category=slug
        if ($request->filled('category')) {
            $catSlug = $request->query('category');
            if (in_array($catSlug, Category::EXCLUDED_PUBLIC_SLUGS)) {
                abort(404);
            }
            $query->whereHas('category', function ($q) use ($catSlug) {
                $q->where('slug', $catSlug);
            });
        }
        
        $sort = $request->query('sort', 'default');
        if ($sort === 'price_low') {
            $query->orderBy('harga_jual', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('harga_jual', 'desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('nama_barang', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('nama_barang', 'desc');
        } elseif ($sort === 'promo') {
            $query->where('is_promo', true)->orderBy('diskon_persen', 'desc')->latest();
        } elseif ($sort === 'newest') {
            $query->latest();
        } else { // default (mengikuti urutan katalog asli Olsera POS)
            $query->latest();
        }
        
        $products = $query->paginate(16)->withQueryString();

        // 3. Mengambil produk khusus promo untuk _promo-banner
        $promoProducts = Product::publicVisible()->where('is_promo', true)
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
            // User belum login (Guest): ambil dari Session
            $cart = session()->get('cart', []);
            foreach ($cart as $item) {
                $cartCount += $item['quantity'];
            }
        }

        // 5. ✅ PERBAIKAN #6: Wishlist count
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

    public function categoryIndex(Request $request, $slug)
    {
        // Tolak akses langsung ke kategori yang dikecualikan (Soft Drink / Minuman)
        if (in_array($slug, Category::EXCLUDED_PUBLIC_SLUGS)) {
            abort(404);
        }

        // Cari kategori berdasarkan slug yang boleh tampil
        $category = Category::publicVisible()->where('slug', $slug)->firstOrFail();

        // Ambil semua kategori dengan eager load children untuk Navbar & Sidebar
        $categories = Category::publicVisible()->with('children')->get();
        $subCats = Category::publicVisible()->where('parent_id', $category->id)->get();

        // Ambil ID kategori beserta seluruh ID sub-kategorinya jika ini adalah kategori induk (parent)
        $categoryIds = $category->children->count() > 0 
            ? $category->children->pluck('id')->push($category->id)
            : [$category->id];

        // Ambil produk yang termasuk dalam kategori atau sub-kategorinya dengan sorting
        $query = Product::publicVisible()->whereIn('category_id', $categoryIds);

        $sort = $request->query('sort', 'default');
        if ($sort === 'price_low') {
            $query->orderBy('harga_jual', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('harga_jual', 'desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('nama_barang', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('nama_barang', 'desc');
        } elseif ($sort === 'promo') {
            $query->where('is_promo', true)->orderBy('diskon_persen', 'desc')->latest();
        } elseif ($sort === 'newest') {
            $query->latest();
        } else { // default (mengikuti urutan katalog asli Olsera POS)
            $query->latest();
        }

        $products = $query->paginate(16)->withQueryString();
        
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

        return view('pages.public.catalog', compact('category', 'categories', 'subCats', 'products', 'cartCount', 'wishlistCount'));
    }

    // ✅ PERBAIKAN #6: Product detail page
    public function show($slug)
    {
        // Cari produk publik berdasarkan slug dan load reviews
        $product = Product::publicVisible()->with('reviews.user')->where('slug', $slug)->firstOrFail();
        
        // Ambil semua kategori untuk navbar
        $categories = Category::publicVisible()->with('children')->get();
        
        // Ambil produk serupa (same category, exclude current product)
        $relatedProducts = Product::publicVisible()->where('category_id', $product->category_id)
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
        $categories = Category::publicVisible()->with('children')->get();
        
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
        
        // Search komprehensif di nama produk, kode barang, deskripsi, relasi kategori, dan SELURUH varian (rasa/warna/SKU)
        $products = Product::publicVisible()->with(['category', 'variants'])
            ->where(function($q) use ($query) {
                $q->where('nama_barang', 'like', '%' . $query . '%')
                  ->orWhere('kode_barang', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhereHas('category', function($catQuery) use ($query) {
                      $catQuery->where('nama_kategori', 'like', '%' . $query . '%');
                  })
                  ->orWhereHas('variants', function($varQuery) use ($query) {
                      $varQuery->where('nama_varian', 'like', '%' . $query . '%')
                               ->orWhere('kode_barang', 'like', '%' . $query . '%');
                  });
            })
            ->latest()
            ->paginate(16)
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

    /**
     * Live AJAX Search endpoint for instant navbar search dropdown
     */
    public function liveSearch(Request $request)
    {
        $query = trim($request->input('q', ''));
        if (strlen($query) < 2) {
            return response()->json(['success' => true, 'products' => []]);
        }

        $products = Product::publicVisible()->with(['category', 'variants'])
            ->where(function($q) use ($query) {
                $q->where('nama_barang', 'like', '%' . $query . '%')
                  ->orWhere('kode_barang', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhereHas('category', function($catQuery) use ($query) {
                      $catQuery->where('nama_kategori', 'like', '%' . $query . '%');
                  })
                  ->orWhereHas('variants', function($varQuery) use ($query) {
                      $varQuery->where('nama_varian', 'like', '%' . $query . '%')
                               ->orWhere('kode_barang', 'like', '%' . $query . '%');
                  });
            })
            ->take(8)
            ->get()
            ->map(function($item) use ($query) {
                // Cari jika ada varian spesifik yang cocok dengan kata kunci
                $matchingVariant = $item->variants->first(function($v) use ($query) {
                    return str_contains(strtolower($v->nama_varian), strtolower($query)) 
                        || str_contains(strtolower($v->kode_barang ?? ''), strtolower($query));
                });

                $variantName = $matchingVariant ? $matchingVariant->nama_varian : null;
                $imageUrl = $matchingVariant && $matchingVariant->gambar_url 
                    ? $matchingVariant->gambar_url 
                    : ($item->gambar_url ?: null);

                return [
                    'id' => $item->id,
                    'name' => $item->nama_barang,
                    'variant_match' => $variantName,
                    'slug' => $item->slug,
                    'url' => route('public.product.show', $item->slug),
                    'image' => $imageUrl,
                    'category' => $item->category ? $item->category->nama_kategori : 'Podgasm Store',
                    'price' => $item->formatted_price,
                    'stock' => $item->total_stok,
                    'is_out_of_stock' => $item->total_stok <= 0,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $products->count(),
            'products' => $products
        ]);
    }

    /**
     * Endpoint ringan untuk sinkronisasi stok live (Smart On-Action & Tab Focus Sync)
     */
    public function getStock(int $id)
    {
        $product = Product::select('id', 'stok_aktual')
            ->with(['variants:id,product_id,nama_varian,stok_aktual'])
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $hasVariants = $product->variants->isNotEmpty();
        $totalStock = $hasVariants ? (int) $product->variants->sum('stok_aktual') : (int) $product->stok_aktual;

        $variantsData = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->nama_varian,
                'stock' => (int) $variant->stok_aktual,
                'is_out_of_stock' => (int) $variant->stok_aktual <= 0,
            ];
        });

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'has_variants' => $hasVariants,
            'total_stock' => $totalStock,
            'stock' => (int) $product->stok_aktual,
            'is_out_of_stock' => $totalStock <= 0,
            'variants' => $variantsData,
        ]);
    }
}