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
            
            // Tingkat 1
            'min_qty' => 'required|integer|min:1',
            'discount_type' => 'required|string|in:percentage,nominal',
            'discount_value' => 'required|numeric|min:0',
            
            // Tingkat 2 (Opsional)
            'enable_tier_2' => 'nullable|boolean',
            'min_qty_2' => 'required_if:enable_tier_2,1|nullable|integer|min:1',
            'discount_type_2' => 'required_if:enable_tier_2,1|nullable|string|in:percentage,nominal',
            'discount_value_2' => 'required_if:enable_tier_2,1|nullable|numeric|min:0',
        ], [
            'product_id.required' => 'Produk harus dipilih.',
            'min_qty.required' => 'Kuantitas minimal Tingkat 1 harus diisi.',
            'min_qty.min' => 'Kuantitas minimal Tingkat 1 adalah 1.',
            'discount_type.required' => 'Jenis potongan Tingkat 1 harus dipilih.',
            'discount_value.required' => 'Nilai potongan Tingkat 1 harus diisi.',
            'discount_value.min' => 'Nilai potongan Tingkat 1 tidak boleh negatif.',
            
            'min_qty_2.required_if' => 'Kuantitas minimal Tingkat 2 harus diisi.',
            'min_qty_2.min' => 'Kuantitas minimal Tingkat 2 adalah 1.',
            'discount_type_2.required_if' => 'Jenis potongan Tingkat 2 harus dipilih.',
            'discount_value_2.required_if' => 'Nilai potongan Tingkat 2 harus diisi.',
            'discount_value_2.min' => 'Nilai potongan Tingkat 2 tidak boleh negatif.',
        ]);

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Nilai persentase potongan Tingkat 1 tidak boleh melebihi 100%.'])->withInput();
        }

        if (!empty($validated['enable_tier_2'])) {
            if ($validated['discount_type_2'] === 'percentage' && $validated['discount_value_2'] > 100) {
                return back()->withErrors(['discount_value_2' => 'Nilai persentase potongan Tingkat 2 tidak boleh melebihi 100%.'])->withInput();
            }

            if ($validated['min_qty_2'] <= $validated['min_qty']) {
                return back()->withErrors(['min_qty_2' => 'Kuantitas minimal Tingkat 2 harus lebih besar dari Tingkat 1.'])->withInput();
            }
        }

        // Cek duplikasi Tingkat 1
        $exists1 = B2BPriceSetting::where('product_id', $validated['product_id'])
            ->where('product_variant_id', $validated['product_variant_id'])
            ->where('min_qty', $validated['min_qty'])
            ->exists();

        if ($exists1) {
            return back()->withErrors(['min_qty' => 'Setting harga bertingkat untuk produk, varian, dan kuantitas Tingkat 1 ini sudah ada.'])->withInput();
        }

        // Cek duplikasi Tingkat 2
        if (!empty($validated['enable_tier_2'])) {
            $exists2 = B2BPriceSetting::where('product_id', $validated['product_id'])
                ->where('product_variant_id', $validated['product_variant_id'])
                ->where('min_qty', $validated['min_qty_2'])
                ->exists();

            if ($exists2) {
                return back()->withErrors(['min_qty_2' => 'Setting harga bertingkat untuk produk, varian, dan kuantitas Tingkat 2 ini sudah ada.'])->withInput();
            }
        }

        // Simpan Tingkat 1
        B2BPriceSetting::create([
            'product_id' => $validated['product_id'],
            'product_variant_id' => $validated['product_variant_id'],
            'min_qty' => $validated['min_qty'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
        ]);

        // Simpan Tingkat 2 jika aktif
        if (!empty($validated['enable_tier_2'])) {
            B2BPriceSetting::create([
                'product_id' => $validated['product_id'],
                'product_variant_id' => $validated['product_variant_id'],
                'min_qty' => $validated['min_qty_2'],
                'discount_type' => $validated['discount_type_2'],
                'discount_value' => $validated['discount_value_2'],
            ]);
        }

        return redirect()->route('admin.b2b-prices.index')->with('success', 'Setting harga B2B berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function edit($id)
    {
        $setting = B2BPriceSetting::findOrFail($id);

        // Cari semua setting untuk produk dan varian yang sama agar bisa diedit bersamaan
        $allSettings = B2BPriceSetting::where('product_id', $setting->product_id)
            ->where('product_variant_id', $setting->product_variant_id)
            ->orderBy('min_qty', 'asc')
            ->get();

        $products = Product::with('variants')->orderBy('nama_barang')->get();
        return view('pages.admin.b2b-prices.edit', compact('setting', 'allSettings', 'products'));
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
            
            // Tingkat 1
            'min_qty' => 'required|integer|min:1',
            'discount_type' => 'required|string|in:percentage,nominal',
            'discount_value' => 'required|numeric|min:0',
            
            // Tingkat 2 (Opsional)
            'enable_tier_2' => 'nullable|boolean',
            'min_qty_2' => 'required_if:enable_tier_2,1|nullable|integer|min:1',
            'discount_type_2' => 'required_if:enable_tier_2,1|nullable|string|in:percentage,nominal',
            'discount_value_2' => 'required_if:enable_tier_2,1|nullable|numeric|min:0',
        ], [
            'product_id.required' => 'Produk harus dipilih.',
            'min_qty.required' => 'Kuantitas minimal Tingkat 1 harus diisi.',
            'min_qty.min' => 'Kuantitas minimal Tingkat 1 adalah 1.',
            'discount_type.required' => 'Jenis potongan Tingkat 1 harus dipilih.',
            'discount_value.required' => 'Nilai potongan Tingkat 1 harus diisi.',
            'discount_value.min' => 'Nilai potongan Tingkat 1 tidak boleh negatif.',
            
            'min_qty_2.required_if' => 'Kuantitas minimal Tingkat 2 harus diisi.',
            'min_qty_2.min' => 'Kuantitas minimal Tingkat 2 adalah 1.',
            'discount_type_2.required_if' => 'Jenis potongan Tingkat 2 harus dipilih.',
            'discount_value_2.required_if' => 'Nilai potongan Tingkat 2 harus diisi.',
            'discount_value_2.min' => 'Nilai potongan Tingkat 2 tidak boleh negatif.',
        ]);

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Nilai persentase potongan Tingkat 1 tidak boleh melebihi 100%.'])->withInput();
        }

        if (!empty($validated['enable_tier_2'])) {
            if ($validated['discount_type_2'] === 'percentage' && $validated['discount_value_2'] > 100) {
                return back()->withErrors(['discount_value_2' => 'Nilai persentase potongan Tingkat 2 tidak boleh melebihi 100%.'])->withInput();
            }

            if ($validated['min_qty_2'] <= $validated['min_qty']) {
                return back()->withErrors(['min_qty_2' => 'Kuantitas minimal Tingkat 2 harus lebih besar dari Tingkat 1.'])->withInput();
            }
        }

        // Hapus semua setting lama untuk produk & varian asal ini agar bisa di-replace
        B2BPriceSetting::where('product_id', $setting->product_id)
            ->where('product_variant_id', $setting->product_variant_id)
            ->delete();

        // Buat record baru untuk Tingkat 1
        B2BPriceSetting::create([
            'product_id' => $validated['product_id'],
            'product_variant_id' => $validated['product_variant_id'],
            'min_qty' => $validated['min_qty'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
        ]);

        // Buat record baru untuk Tingkat 2 jika diaktifkan
        if (!empty($validated['enable_tier_2'])) {
            B2BPriceSetting::create([
                'product_id' => $validated['product_id'],
                'product_variant_id' => $validated['product_variant_id'],
                'min_qty' => $validated['min_qty_2'],
                'discount_type' => $validated['discount_type_2'],
                'discount_value' => $validated['discount_value_2'],
            ]);
        }

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
