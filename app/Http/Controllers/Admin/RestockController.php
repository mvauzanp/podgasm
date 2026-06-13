<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Restock;
use App\Models\RestockItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RestockController extends Controller
{
    /**
     * Tampilkan riwayat penerimaan barang masuk
     */
    public function index(Request $request)
    {
        $query = Restock::with(['user', 'items']);

        if ($request->has('search') && !empty($request->search)) {
            $query->where('supplier_name', 'like', '%' . $request->search . '%')
                  ->orWhere('id', $request->search);
        }

        $restocks = $query->latest()->paginate(10);
        return view('pages.admin.restocks.index', compact('restocks'));
    }

    /**
     * Form untuk menambah barang masuk baru
     */
    public function create()
    {
        // Ambil produk beserta variannya
        $products = Product::with('variants')->get();
        return view('pages.admin.restocks.create', compact('products'));
    }

    /**
     * Simpan pengadaan barang masuk & update stok aktual database
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Simpan Transaksi Restock Induk
            $restock = Restock::create([
                'user_id'       => Auth::id(),
                'supplier_name' => $request->supplier_name,
                'notes'         => $request->notes,
                'total_cost'    => 0, // dihitung dinamis di bawah
            ]);

            $totalCost = 0;

            // 2. Loop & Simpan Item Detail
            foreach ($request->items as $itemData) {
                $itemCost = $itemData['quantity'] * $itemData['purchase_price'];
                $totalCost += $itemCost;

                // Simpan item restock
                RestockItem::create([
                    'restock_id'         => $restock->id,
                    'product_id'         => $itemData['product_id'],
                    'product_variant_id' => $itemData['product_variant_id'] ?? null,
                    'quantity'           => $itemData['quantity'],
                    'purchase_price'     => $itemData['purchase_price'],
                ]);

                // 3. Update/Increment stok aktual barang & update harga pokok terbaru
                if (!empty($itemData['product_variant_id'])) {
                    // Update stok varian & harga pokok varian
                    $variant = ProductVariant::findOrFail($itemData['product_variant_id']);
                    $variant->increment('stok_aktual', $itemData['quantity']);
                    $variant->update(['harga_pokok' => $itemData['purchase_price']]);

                    // Sinkronisasikan total stok ke produk induk & update harga pokok produk induk
                    $product = Product::findOrFail($itemData['product_id']);
                    $totalVariantStock = ProductVariant::where('product_id', $product->id)->sum('stok_aktual');
                    $product->update([
                        'stok_aktual' => $totalVariantStock,
                        'harga_pokok' => $itemData['purchase_price']
                    ]);
                } else {
                    // Update stok induk & harga pokok langsung
                    $product = Product::findOrFail($itemData['product_id']);
                    $product->increment('stok_aktual', $itemData['quantity']);
                    $product->update(['harga_pokok' => $itemData['purchase_price']]);
                }
            }

            // Update total biaya transaksi restock
            $restock->update(['total_cost' => $totalCost]);

            DB::commit();

            return redirect()->route('admin.restocks.index')
                ->with('success', 'Hore! Transaksi Barang Masuk dari ' . $request->supplier_name . ' berhasil dicatat dan stok aktual diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Restock Save Error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['msg' => 'Gagal mencatat barang masuk: ' . $e->getMessage()]);
        }
    }

    /**
     * Tampilkan detail bukti transaksi barang masuk (Audit Trail)
     */
    public function show($id)
    {
        $restock = Restock::with(['user', 'items.product', 'items.variant'])->findOrFail($id);
        return view('pages.admin.restocks.show', compact('restock'));
    }
}
