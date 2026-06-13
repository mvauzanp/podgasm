<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SafetyStockRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SafetyStockController extends Controller
{
    /**
     * Menampilkan daftar produk dengan klasifikasi ABC dan standar deviasi
     */
    public function index()
    {
        $products = Product::with('category')->get();

        // 1. Hitung total penjualan 30 hari untuk tiap produk untuk Klasifikasi ABC
        $salesTotals = [];
        $grandTotalSales = 0;
        foreach ($products as $product) {
            $totalQty = (float) OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereIn('status', ['paid', 'shipped', 'completed']);
                })
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('quantity') ?? 0;
            
            $salesTotals[$product->id] = $totalQty;
            $grandTotalSales += $totalQty;
        }

        // Urutkan penjualan secara descending untuk mencari persentase kumulatif
        uasort($salesTotals, function($a, $b) {
            return $b <=> $a;
        });

        // Hitung klasifikasi ABC untuk tiap produk berdasarkan kontribusi kumulatif
        $runningSum = 0;
        $productClasses = [];
        foreach ($salesTotals as $productId => $qty) {
            if ($grandTotalSales > 0) {
                $runningSum += $qty;
                $cumulativePercentage = ($runningSum / $grandTotalSales) * 100;
            } else {
                $cumulativePercentage = 100;
            }

            if ($qty == 0) {
                $class = 'C';
            } elseif ($cumulativePercentage <= 70) {
                $class = 'A';
            } elseif ($cumulativePercentage <= 90) {
                $class = 'B';
            } else {
                $class = 'C';
            }
            $productClasses[$productId] = $class;
        }

        // Mapping Z-score & Service Level
        $zScores = ['A' => 2.05, 'B' => 1.65, 'C' => 1.28];
        $serviceLevels = ['A' => '98%', 'B' => '95%', 'C' => '90%'];

        // 2. Hitung Standard Deviasi dan Rata-rata Penjualan harian
        foreach ($products as $product) {
            $class = $productClasses[$product->id] ?? 'C';
            $product->abc_class = $class;
            $product->z_score = $zScores[$class];
            $product->service_level = $serviceLevels[$class];

            // Ambil data penjualan harian 30 hari terakhir
            $salesData = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereIn('status', ['paid', 'shipped', 'completed']);
                })
                ->where('created_at', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('date')
                ->pluck('total_qty', 'date')
                ->toArray();

            // Buat array daily sales berisi 30 hari penuh (mengisi hari tanpa penjualan dengan 0)
            $dailySales = [];
            for ($i = 29; $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $dailySales[$dateStr] = (float)($salesData[$dateStr] ?? 0);
            }

            $product->avg_sales_30d = round(array_sum($dailySales) / 30, 2);
            $product->std_dev_30d = round($this->calculateStandardDeviation(array_values($dailySales)), 2);
        }

        return view('pages.admin.safety-stock.calculation', compact('products'));
    }

    /**
     * Menjalankan Algoritma Safety Stock Statistik (ABC Analysis + Standard Deviation)
     */
    public function calculate(SafetyStockRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $products = Product::all();

            // 1. Klasifikasi ABC dinamis untuk seluruh produk
            $salesTotals = [];
            $grandTotalSales = 0;
            foreach ($products as $p) {
                $totalQty = (float) OrderItem::where('product_id', $p->id)
                    ->whereHas('order', function ($query) {
                        $query->whereIn('status', ['paid', 'shipped', 'completed']);
                    })
                    ->where('created_at', '>=', now()->subDays(30))
                    ->sum('quantity') ?? 0;
                
                $salesTotals[$p->id] = $totalQty;
                $grandTotalSales += $totalQty;
            }

            uasort($salesTotals, function($a, $b) {
                return $b <=> $a;
            });

            $runningSum = 0;
            $productClass = 'C';
            foreach ($salesTotals as $productId => $qty) {
                if ($grandTotalSales > 0) {
                    $runningSum += $qty;
                    $cumulativePercentage = ($runningSum / $grandTotalSales) * 100;
                } else {
                    $cumulativePercentage = 100;
                }

                $class = 'C';
                if ($qty > 0) {
                    if ($cumulativePercentage <= 70) {
                        $class = 'A';
                    } elseif ($cumulativePercentage <= 90) {
                        $class = 'B';
                    }
                }

                if ($productId == $product->id) {
                    $productClass = $class;
                    break;
                }
            }

            $zScores = ['A' => 2.05, 'B' => 1.65, 'C' => 1.28];
            $zScore = $zScores[$productClass];

            // 2. Ambil data penjualan harian 30 hari terakhir untuk produk ini
            $salesData = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereIn('status', ['paid', 'shipped', 'completed']);
                })
                ->where('created_at', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('date')
                ->pluck('total_qty', 'date')
                ->toArray();

            $dailySales = [];
            for ($i = 29; $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $dailySales[$dateStr] = (float)($salesData[$dateStr] ?? 0);
            }

            $avgSales = array_sum($dailySales) / 30;
            $stdDev = $this->calculateStandardDeviation(array_values($dailySales));

            // --- RUMUS SAFETY STOCK STATISTIK ---
            // SS = Z * std_dev * sqrt(Lead Time)
            $safetyStock = $zScore * $stdDev * sqrt($request->lead_time);

            // Update ke Database (simpan Safety Stock, Rata-rata Penjualan, dan Lead Time)
            $product->update([
                'nilai_ss'       => round($safetyStock, 2),
                'lead_time'      => $request->lead_time,
                'rata_penjualan' => round($avgSales, 2)
            ]);

            return redirect()->back()->with('success', 'Perhitungan Safety Stock Statistik ' . $product->nama_barang . ' (Kelas ' . $productClass . ') berhasil diperbarui!');
            
        } catch (\Exception $e) {
            Log::error('Safety Stock Calculation Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Terjadi kesalahan sistem saat menghitung Safety Stock. Silakan coba lagi.']);
        }
    }

    /**
     * Menghitung standar deviasi dari array angka
     */
    private function calculateStandardDeviation(array $values): float
    {
        $count = count($values);
        if ($count === 0) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;

        $varianceSum = 0.0;
        foreach ($values as $val) {
            $varianceSum += pow($val - $mean, 2);
        }

        $variance = $varianceSum / $count;

        return sqrt($variance);
    }
}
