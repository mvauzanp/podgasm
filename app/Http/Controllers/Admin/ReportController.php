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
        $products = Product::with(['category', 'variants'])->get();
        $reports = collect();

        foreach ($products as $product) {
            if ($product->hasVariants()) {
                foreach ($product->variants as $variant) {
                    $reports->push($this->buildReportItem(
                        $product->nama_barang . ' (' . $variant->nama_varian . ')',
                        $product->category->nama_kategori ?? '-',
                        $variant->stok_aktual,
                        $variant->harga_jual_actual,
                        $variant->harga_pokok ?? $product->harga_pokok,
                        $variant->tgl_expired,
                        $variant->tgl_cukai
                    ));
                }
            } else {
                $reports->push($this->buildReportItem(
                    $product->nama_barang,
                    $product->category->nama_kategori ?? '-',
                    $product->stok_aktual,
                    $product->harga_jual,
                    $product->harga_pokok,
                    $product->tgl_expired,
                    $product->tgl_cukai
                ));
            }
        }

        return view('pages.admin.reports.inventory', compact('reports'));
    }

    private function buildReportItem($nama, $kategori, $stok, $hargaJual, $hargaPokok, $tglExpired, $tglCukai)
    {
        $status = 'Aman';
        $risiko_kerugian = 0;
        
        // 1. Cek Cukai Melewati Tahun (Cukai Lama)
        if ($tglCukai) {
            $cukaiYear = \Carbon\Carbon::parse($tglCukai)->year;
            $currentYear = now()->year;
            if ($cukaiYear < $currentYear) {
                $status = 'Risiko Cukai (Melewati Tahun)';
                // Potensi depresiasi nilai sebesar 30% untuk barang dengan cukai tahun lalu
                $risiko_kerugian = $hargaPokok * $stok * 0.3;
            }
        }

        // 2. Cek Expiry Date (memiliki prioritas status & risiko kerugian lebih tinggi jika expired)
        if ($tglExpired) {
            $daysToExpired = now()->diffInDays(\Carbon\Carbon::parse($tglExpired), false);
            if ($daysToExpired <= 30 && $daysToExpired > 0) {
                $status = 'Risiko Sedang (Hampir Expired)';
                $risiko_kerugian = max($risiko_kerugian, $hargaPokok * $stok * 0.5); // Potensi rugi 50% dari harga pokok
            } elseif ($daysToExpired <= 0) {
                $status = 'Risiko Tinggi (Expired)';
                $risiko_kerugian = $hargaPokok * $stok; // Potensi rugi 100% dari harga pokok
            }
        }

        return [
            'nama' => $nama,
            'kategori' => $kategori,
            'stok' => $stok,
            'harga' => $hargaJual,
            'tgl_expired' => $tglExpired,
            'tgl_cukai' => $tglCukai,
            'status' => $status,
            'estimasi_rugi' => $risiko_kerugian
        ];
    }
}
