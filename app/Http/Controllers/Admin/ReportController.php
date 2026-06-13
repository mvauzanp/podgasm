<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product; // Jangan lupa import model Product
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function inventoryReport()
    {
        $products = Product::with('category')->get();
        
        $reports = $products->map(function($product) {
            $status = 'Aman';
            $risiko_kerugian = 0;
            
            // Logika Estimasi Kerugian (Depresiasi Nilai)
            // Jika barang expired dalam < 30 hari, nilai dianggap depresiasi 100%
            if ($product->tgl_expired) {
                $daysToExpired = now()->diffInDays($product->tgl_expired, false);
                if ($daysToExpired <= 30 && $daysToExpired > 0) {
                    $status = 'Risiko Sedang (Hampir Expired)';
                    $risiko_kerugian = $product->harga_pokok * $product->stok_aktual * 0.5; // Potensi rugi 50% dari harga pokok/beli
                } elseif ($daysToExpired <= 0) {
                    $status = 'Risiko Tinggi (Expired)';
                    $risiko_kerugian = $product->harga_pokok * $product->stok_aktual; // Potensi rugi 100% dari harga pokok/beli
                }
            }

            return [
                'nama' => $product->nama_barang,
                'kategori' => $product->category->nama_kategori ?? '-',
                'stok' => $product->stok_aktual,
                'harga' => $product->harga_jual,
                'tgl_expired' => $product->tgl_expired,
                'status' => $status,
                'estimasi_rugi' => $risiko_kerugian
            ];
        });

        return view('pages.admin.reports.inventory', compact('reports'));
    }
}
