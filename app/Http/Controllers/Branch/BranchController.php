<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\StockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    // Tampilkan Form Request & Daftar Produk
    public function index()
    {
        // Ambil produk dengan variant-nya
        $products = Product::with('variants')->orderBy('nama_barang', 'asc')->get();
        return view('pages.branch.request-stock', compact('products'));
    }

    // Simpan Permintaan Stok
    public function storeRequest(Request $request)
    {
        $request->validate([
            'product_selection' => 'required|string',
            'jumlah' => 'required|numeric|min:1',
            'prioritas' => 'required|in:Normal,Urgent',
        ]);

        try {
            $selection = $request->input('product_selection');
            $productId = null;
            $variantId = null;

            if (str_starts_with($selection, 'variant_')) {
                $variantId = (int) str_replace('variant_', '', $selection);
                $variant = \App\Models\ProductVariant::findOrFail($variantId);
                $productId = $variant->product_id;
            } else if (str_starts_with($selection, 'product_')) {
                $productId = (int) str_replace('product_', '', $selection);
                $product = Product::findOrFail($productId);
                if ($product->hasVariants()) {
                    return back()->withInput()->withErrors(['msg' => 'Produk ini memiliki varian, silakan pilih varian tertentu.']);
                }
            } else {
                return back()->withInput()->withErrors(['msg' => 'Pilihan produk tidak valid.']);
            }

            StockRequest::create([
                'product_id' => $productId, 
                'product_variant_id' => $variantId,
                'user_id'   => Auth::id(),
                'jumlah'    => $request->jumlah,
                'prioritas' => $request->prioritas,
                'keterangan'=> $request->keterangan,
                'status'    => 'Pending',
            ]);

            return redirect()->route('branch.tracking')->with('success', 'Gacor! Permintaan stok berhasil dikirim.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'Gagal kirim request: ' . $e->getMessage()]);
        }
    }

    public function tracking()
    {
        $userId = Auth::id();

        // Eager Loading 'product' & 'variant' untuk optimize query
        $requests = StockRequest::with(['product', 'variant'])
                    ->where('user_id', $userId)
                    ->latest()
                    ->get();

        return view('pages.branch.tracking', compact('requests'));
    }

    public function dashboard()
    {
        $userId = Auth::id();
        
        $totalStok = BranchStock::where('user_id', $userId)->sum('stok_lokal');
        
        // Kita kirim kategori juga kalau sidebar butuh
        $categories = Category::all();
        
        $pendingRequests = StockRequest::where('user_id', $userId)
                            ->where('status', 'Pending')
                            ->count();

        $recentRequests = StockRequest::with(['product', 'variant'])
                            ->where('user_id', $userId)
                            ->latest()
                            ->take(5)
                            ->get();

        // Tambahkan categories di compact biar gak error di layout
        return view('pages.branch.dashboard', compact('totalStok', 'pendingRequests', 'recentRequests', 'categories'));
    }
}