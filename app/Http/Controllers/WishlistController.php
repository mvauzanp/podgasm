<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
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
