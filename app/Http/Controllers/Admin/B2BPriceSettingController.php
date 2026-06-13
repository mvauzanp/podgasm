<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\B2BPriceSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class B2BPriceSettingController extends Controller
{
    /**
     * Display a listing of B2B price settings.
     */
    public function index()
    {
        $settings = B2BPriceSetting::with(['product', 'variant'])
            ->orderBy('product_id')
            ->orderBy('product_variant_id')
            ->orderBy('min_qty')
            ->paginate(15);

        return view('pages.admin.b2b-prices.index', compact('settings'));
    }

    /**
     * Show the form for creating a new setting.
     */
    public function create()
    {
        $products = Product::with('variants')->orderBy('nama_barang')->get();
        return view('pages.admin.b2b-prices.create', compact('products'));
    }

    /**
     * Store a newly created setting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'min_qty' => 'required|integer|min:1',
            'discount_type' => 'required|string|in:percentage,nominal',
            'discount_value' => 'required|numeric|min:0',
        ], [
            'product_id.required' => 'Produk harus dipilih.',
            'min_qty.required' => 'Kuantitas minimal harus diisi.',
            'min_qty.min' => 'Kuantitas minimal adalah 1.',
            'discount_type.required' => 'Jenis potongan harus dipilih.',
            'discount_value.required' => 'Nilai potongan harus diisi.',
            'discount_value.min' => 'Nilai potongan tidak boleh negatif.',
        ]);

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Nilai persentase potongan tidak boleh melebihi 100%.'])->withInput();
        }

        // Cek duplikasi
        $exists = B2BPriceSetting::where('product_id', $validated['product_id'])
            ->where('product_variant_id', $validated['product_variant_id'])
            ->where('min_qty', $validated['min_qty'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['min_qty' => 'Setting harga bertingkat untuk produk, varian, dan kuantitas ini sudah ada.'])->withInput();
        }

        B2BPriceSetting::create($validated);

        return redirect()->route('admin.b2b-prices.index')->with('success', 'Setting harga B2B berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function edit($id)
    {
        $setting = B2BPriceSetting::findOrFail($id);
        $products = Product::with('variants')->orderBy('nama_barang')->get();
        return view('pages.admin.b2b-prices.edit', compact('setting', 'products'));
    }

    /**
     * Update the specified setting.
     */
    public function update(Request $request, $id)
    {
        $setting = B2BPriceSetting::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'min_qty' => 'required|integer|min:1',
            'discount_type' => 'required|string|in:percentage,nominal',
            'discount_value' => 'required|numeric|min:0',
        ], [
            'product_id.required' => 'Produk harus dipilih.',
            'min_qty.required' => 'Kuantitas minimal harus diisi.',
            'min_qty.min' => 'Kuantitas minimal adalah 1.',
            'discount_type.required' => 'Jenis potongan harus dipilih.',
            'discount_value.required' => 'Nilai potongan harus diisi.',
            'discount_value.min' => 'Nilai potongan tidak boleh negatif.',
        ]);

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Nilai persentase potongan tidak boleh melebihi 100%.'])->withInput();
        }

        // Cek duplikasi di baris lain
        $exists = B2BPriceSetting::where('product_id', $validated['product_id'])
            ->where('product_variant_id', $validated['product_variant_id'])
            ->where('min_qty', $validated['min_qty'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['min_qty' => 'Setting harga bertingkat untuk produk, varian, dan kuantitas ini sudah ada.'])->withInput();
        }

        $setting->update($validated);

        return redirect()->route('admin.b2b-prices.index')->with('success', 'Setting harga B2B berhasil diperbarui.');
    }

    /**
     * Remove the specified setting.
     */
    public function destroy($id)
    {
        $setting = B2BPriceSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('admin.b2b-prices.index')->with('success', 'Setting harga B2B berhasil dihapus.');
    }
}
