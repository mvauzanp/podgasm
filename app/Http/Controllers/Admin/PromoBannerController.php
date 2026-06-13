<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoBanner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromoBannerController extends Controller
{
    public function index()
    {
        $banners = PromoBanner::orderBy('order', 'asc')->orderBy('created_at', 'desc')->get();
        return view('pages.admin.banners.index', compact('banners'));
    }

    public function create()
    {
        $products = Product::orderBy('nama_barang')->get(['id', 'nama_barang', 'slug']);
        return view('pages.admin.banners.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'      => 'nullable|string|max:255',
            'deskripsi'  => 'nullable|string',
            'gambar'     => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'product_id' => 'nullable|exists:products,id',
            'order'      => 'required|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        try {
            // Bangun link_url otomatis dari produk yang dipilih
            $linkUrl = null;
            if ($request->product_id) {
                $product = Product::findOrFail($request->product_id);
                $linkUrl = '/product/' . $product->slug;
            }

            $data = $request->only(['judul', 'deskripsi', 'order']);
            $data['link_url']  = $linkUrl;
            $data['is_active'] = $request->has('is_active') ? true : false;

            if ($request->hasFile('gambar')) {
                $path = $request->file('gambar')->store('banners', 'public');
                $data['gambar'] = $path;
            }

            PromoBanner::create($data);

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner promo berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'Gagal Simpan: ' . $e->getMessage()]);
        }
    }

    public function edit(PromoBanner $banner)
    {
        $products = Product::orderBy('nama_barang')->get(['id', 'nama_barang', 'slug']);
        return view('pages.admin.banners.edit', compact('banner', 'products'));
    }

    public function update(Request $request, PromoBanner $banner)
    {
        $request->validate([
            'judul'      => 'nullable|string|max:255',
            'deskripsi'  => 'nullable|string',
            'gambar'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'product_id' => 'nullable|exists:products,id',
            'order'      => 'required|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        try {
            // Bangun link_url otomatis dari produk yang dipilih
            $linkUrl = null;
            if ($request->product_id) {
                $product = Product::findOrFail($request->product_id);
                $linkUrl = '/product/' . $product->slug;
            }

            $data = $request->only(['judul', 'deskripsi', 'order']);
            $data['link_url']  = $linkUrl;
            $data['is_active'] = $request->has('is_active') ? true : false;

            if ($request->hasFile('gambar')) {
                if ($banner->gambar) {
                    Storage::disk('public')->delete($banner->gambar);
                }
                $path = $request->file('gambar')->store('banners', 'public');
                $data['gambar'] = $path;
            }

            $banner->update($data);

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner promo berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'Gagal Update: ' . $e->getMessage()]);
        }
    }

    public function destroy(PromoBanner $banner)
    {
        try {
            if ($banner->gambar) {
                Storage::disk('public')->delete($banner->gambar);
            }

            $banner->delete();

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner promo berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Gagal Hapus: ' . $e->getMessage()]);
        }
    }
}
