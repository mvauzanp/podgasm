<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Tentukan apakah user terotorisasi untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk request ini.
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof \App\Models\Product ? $product->id : $product;

        return [
            'nama_barang'    => 'required|string|max:255|unique:products,nama_barang,' . $productId,
            'kode_barang'    => 'nullable|string|max:255|unique:products,kode_barang,' . $productId,
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
            'variants.*.harga_jual'  => 'nullable|numeric|min:0',
            'variants.*.harga_pokok' => 'nullable|numeric|min:0',
            'variants.*.gambar'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Pesan kesalahan kustom untuk validasi request ini.
     */
    public function messages(): array
    {
        return [
            'nama_barang.required' => 'Nama barang harus diisi.',
            'nama_barang.unique'   => 'Nama barang sudah terdaftar.',
            'kode_barang.unique'   => 'Kode barang sudah digunakan.',
            'category_id.required' => 'Kategori produk harus dipilih.',
            'category_id.exists'   => 'Kategori produk tidak valid.',
            'harga_jual.required'  => 'Harga jual harus diisi.',
            'harga_jual.numeric'   => 'Harga jual harus berupa angka.',
            'harga_pokok.required' => 'Harga pokok harus diisi.',
            'harga_pokok.numeric'  => 'Harga pokok harus berupa angka.',
            'stok_aktual.required_without' => 'Stok aktual harus diisi jika produk tidak memiliki varian.',
            'variants.required_if' => 'Data varian harus diisi jika pilihan varian diaktifkan.',
        ];
    }
}
