<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        // Verifikasi apakah user benar-benar sudah membeli produk ini dan order selesai
        $order = Order::where('id', $request->order_id)
                      ->where('user_id', Auth::id())
                      ->where('status', 'completed')
                      ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Anda belum membeli produk ini atau pesanan belum selesai.');
        }

        // Cek apakah produk ini ada di dalam order tersebut
        $hasProduct = $order->items()->where('product_id', $request->product_id)->exists();
        
        if (!$hasProduct) {
            return redirect()->back()->with('error', 'Produk ini tidak ada dalam pesanan Anda.');
        }

        // Cek apakah user sudah pernah review produk ini untuk order yang sama
        $existingReview = Review::where('user_id', Auth::id())
                                ->where('product_id', $request->product_id)
                                ->where('order_id', $request->order_id)
                                ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'Anda sudah memberikan ulasan untuk produk ini pada pesanan tersebut.');
        }

        Review::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return redirect()->back()->with('success', 'Terima kasih atas ulasan Anda!');
    }
}
