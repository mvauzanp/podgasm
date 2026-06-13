<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Fitur Search (biar admin gampang cari barang)
        if ($request->has('search')) {
            $query->where('nama_barang', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(10);
        return view('pages.admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('pages.admin.products.create', compact('categories'));
    }



    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_barang'    => 'required|unique:products,nama_barang',
            'kode_barang'    => 'nullable|string|max:255|unique:products,kode_barang',
            'category_id'    => 'required|exists:categories,id',
            'harga_jual'     => 'required|numeric|min:0',
            'harga_pokok'    => 'required|numeric|min:0',
            'stok_aktual'    => 'required_without:has_variants|nullable|integer|min:0',
            'lead_time'      => 'nullable|integer|min:0',
            'rata_penjualan' => 'nullable|integer|min:0',
            'nilai_ss'       => 'nullable|integer|min:0',
            'gambar'         => 'nullable|array',
            'gambar.*'       => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'tgl_expired'    => 'nullable|date',
            'tgl_cukai'      => 'nullable|date',
            'variants'       => 'required_if:has_variants,1|array',
            'variants.*.nama_varian' => 'required_with:variants|string|max:255',
            'variants.*.stok_aktual' => 'required_with:variants|integer|min:0',
            'variants.*.harga_jual' => 'nullable|numeric|min:0',
            'variants.*.harga_pokok' => 'nullable|numeric|min:0',
            'variants.*.gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $data = $request->all();

            // Generate Slug otomatis dari nama barang
            $data['slug'] = Str::slug($request->nama_barang);

            // Logika Checkbox Promo
            $data['is_promo'] = $request->has('is_promo') ? true : false;
            $data['diskon_persen'] = $request->diskon_persen ?? 0;

            // Pastikan kolom inti TA kamu ada nilainya (default 0 jika kosong)
            $data['lead_time'] = $request->lead_time ?? 0;
            $data['rata_penjualan'] = $request->rata_penjualan ?? 0;
            $data['nilai_ss'] = $request->nilai_ss ?? 0;

            // Set default gambar induk null, nanti diisi dari gambar pertama yang diunggah
            $data['gambar'] = null;

            // Jika punya varian, stok_aktual induk dihitung dari jumlah stok varian
            $hasVariants = $request->has('has_variants') && $request->has('variants');
            if ($hasVariants) {
                $totalStock = collect($request->variants)->sum('stok_aktual');
                $data['stok_aktual'] = $totalStock;
            } else {
                $data['stok_aktual'] = $request->stok_aktual ?? 0;
            }

            // EKSEKUSI SIMPAN
            $product = Product::create($data);

            // Simpan ke tabel product_images jika ada file
            if ($request->hasFile('gambar')) {
                $files = $request->file('gambar');
                $primaryPath = null;
                foreach ($files as $index => $file) {
                    $path = $file->store('products', 'public');
                    if ($index === 0) {
                        $primaryPath = $path;
                    }
                    $product->images()->create(['path' => $path]);
                }
                
                if ($primaryPath) {
                    $product->update(['gambar' => $primaryPath]);
                }
            }

            // Simpan varian jika ada
            if ($hasVariants) {
                foreach ($request->variants as $index => $variantData) {
                    $variantPath = null;
                    if ($request->hasFile("variants.{$index}.gambar")) {
                        $variantFile = $request->file("variants.{$index}.gambar");
                        $variantPath = $variantFile->store('variants', 'public');
                    }

                    $product->variants()->create([
                        'nama_varian' => $variantData['nama_varian'],
                        'kode_barang' => $variantData['kode_barang'] ?? null,
                        'harga_jual' => $variantData['harga_jual'] ?? null,
                        'harga_pokok' => $variantData['harga_pokok'] ?? null,
                        'stok_aktual' => $variantData['stok_aktual'],
                        'tgl_expired' => $variantData['tgl_expired'] ?? null,
                        'tgl_cukai' => $variantData['tgl_cukai'] ?? null,
                        'gambar' => $variantPath,
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Gacor! Produk ' . $request->nama_barang . ' berhasil ditambah.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->withErrors(['msg' => 'Gagal Simpan: ' . $e->getMessage()]);
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        // Eager load variants
        $product->load('variants');
        return view('pages.admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'nama_barang'    => 'required|unique:products,nama_barang,' . $product->id,
            'kode_barang'    => 'nullable|string|max:255|unique:products,kode_barang,' . $product->id,
            'category_id'    => 'required|exists:categories,id',
            'harga_jual'     => 'required|numeric|min:0',
            'harga_pokok'    => 'required|numeric|min:0',
            'stok_aktual'    => 'required_without:has_variants|nullable|integer|min:0',
            'gambar'         => 'nullable|array',
            'gambar.*'       => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'variants'       => 'required_if:has_variants,1|array',
            'variants.*.nama_varian' => 'required_with:variants|string|max:255',
            'variants.*.stok_aktual' => 'required_with:variants|integer|min:0',
            'variants.*.harga_jual' => 'nullable|numeric|min:0',
            'variants.*.harga_pokok' => 'nullable|numeric|min:0',
            'variants.*.gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $data = $request->all();
            $data['slug'] = Str::slug($request->nama_barang);
            $data['is_promo'] = $request->has('is_promo') ? true : false;
            
            // Default values untuk kolom numerik
            $data['lead_time'] = $request->lead_time ?? $product->lead_time;
            $data['rata_penjualan'] = $request->rata_penjualan ?? $product->rata_penjualan;
            $data['nilai_ss'] = $request->nilai_ss ?? $product->nilai_ss;

            // 1. Tangani gambar yang dihapus
            if ($request->has('deleted_images')) {
                $deleteIds = $request->deleted_images;
                $imagesToDelete = $product->images()->whereIn('id', $deleteIds)->get();
                foreach ($imagesToDelete as $oldImage) {
                    Storage::disk('public')->delete($oldImage->path);
                    $oldImage->delete();
                }
            }

            // 2. Tangani upload gambar baru
            if ($request->hasFile('gambar')) {
                $files = $request->file('gambar');
                foreach ($files as $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create(['path' => $path]);
                }
            }

            // 3. Sinkronisasi gambar utama (products.gambar)
            $firstImage = $product->images()->first();
            if ($firstImage) {
                // Hapus file fisik gambar utama lama jika berubah dan tidak ada di daftar images lain
                // Tapi karena gambar utama sekarang selalu sinkron dengan firstImage->path, kita cukup update path-nya saja
                $data['gambar'] = $firstImage->path;
            } else {
                // Jika tidak ada gambar sama sekali
                if ($product->gambar) {
                    Storage::disk('public')->delete($product->gambar);
                }
                $data['gambar'] = null;
            }

            // Hitung stok aktual induk dari total stok varian
            $hasVariants = $request->has('has_variants') && $request->has('variants');
            if ($hasVariants) {
                $totalStock = collect($request->variants)->sum('stok_aktual');
                $data['stok_aktual'] = $totalStock;
            } else {
                $data['stok_aktual'] = $request->stok_aktual ?? 0;
            }

            $product->update($data);

            if ($hasVariants) {
                // Update atau buat varian baru
                $keepIds = [];
                foreach ($request->variants as $index => $variantData) {
                    if (isset($variantData['id']) && $variantData['id']) {
                        $variant = $product->variants()->findOrFail($variantData['id']);
                        
                        $variantPath = $variant->gambar;
                        if ($request->hasFile("variants.{$index}.gambar")) {
                            if ($variant->gambar) {
                                Storage::disk('public')->delete($variant->gambar);
                            }
                            $variantFile = $request->file("variants.{$index}.gambar");
                            $variantPath = $variantFile->store('variants', 'public');
                        }

                        $variant->update([
                            'nama_varian' => $variantData['nama_varian'],
                            'kode_barang' => $variantData['kode_barang'] ?? null,
                            'harga_jual' => $variantData['harga_jual'] ?? null,
                            'harga_pokok' => $variantData['harga_pokok'] ?? null,
                            'stok_aktual' => $variantData['stok_aktual'],
                            'tgl_expired' => $variantData['tgl_expired'] ?? null,
                            'tgl_cukai' => $variantData['tgl_cukai'] ?? null,
                            'gambar' => $variantPath,
                        ]);
                        $keepIds[] = $variant->id;
                    } else {
                        $variantPath = null;
                        if ($request->hasFile("variants.{$index}.gambar")) {
                            $variantFile = $request->file("variants.{$index}.gambar");
                            $variantPath = $variantFile->store('variants', 'public');
                        }

                        $newVariant = $product->variants()->create([
                            'nama_varian' => $variantData['nama_varian'],
                            'kode_barang' => $variantData['kode_barang'] ?? null,
                            'harga_jual' => $variantData['harga_jual'] ?? null,
                            'harga_pokok' => $variantData['harga_pokok'] ?? null,
                            'stok_aktual' => $variantData['stok_aktual'],
                            'tgl_expired' => $variantData['tgl_expired'] ?? null,
                            'tgl_cukai' => $variantData['tgl_cukai'] ?? null,
                            'gambar' => $variantPath,
                        ]);
                        $keepIds[] = $newVariant->id;
                    }
                }

                // Hapus varian lama yang tidak disimpan kembali & hapus gambarnya
                $oldVariants = $product->variants()->whereNotIn('id', $keepIds)->get();
                foreach ($oldVariants as $oldVariant) {
                    if ($oldVariant->gambar) {
                        Storage::disk('public')->delete($oldVariant->gambar);
                    }
                    $oldVariant->delete();
                }
            } else {
                // Hapus semua varian jika dimatikan & hapus gambarnya
                $oldVariants = $product->variants()->get();
                foreach ($oldVariants as $oldVariant) {
                    if ($oldVariant->gambar) {
                        Storage::disk('public')->delete($oldVariant->gambar);
                    }
                    $oldVariant->delete();
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil diupdate!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->withErrors(['msg' => 'Gagal Update: ' . $e->getMessage()]);
        }
    }

    public function destroy(Product $product)
    {
        try {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->path);
            }
            if ($product->gambar) {
                Storage::disk('public')->delete($product->gambar);
            }
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', 'Produk dihapus!');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Gagal Hapus: ' . $e->getMessage()]);
        }
    }
}