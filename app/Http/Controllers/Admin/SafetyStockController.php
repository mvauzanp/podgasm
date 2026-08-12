<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SafetyStockController extends Controller
{
    /**
     * Menampilkan daftar produk dengan klasifikasi ABC dan standar deviasi
     */
    public function index()
    {
        $products = Product::with(['category', 'variants'])->get();

        // 1. Dapatkan semua SKU (Simple Products & Product Variants)
        $skus = [];
        foreach ($products as $product) {
            if (!$product->hasVariants()) {
                $skus[] = [
                    'type' => 'simple',
                    'id' => $product->id,
                    'model' => $product,
                    'lead_time' => $product->lead_time ?: 3,
                ];
            } else {
                foreach ($product->variants as $variant) {
                    $skus[] = [
                        'type' => 'variant',
                        'id' => $variant->id,
                        'model' => $variant,
                        'lead_time' => $variant->lead_time ?: 3,
                    ];
                }
            }
        }

        // 2. Hitung total penjualan 30 hari dalam satu kueri agregasi tunggal (Mencegah N+1 Queries)
        $orderItemsGrouped = OrderItem::whereHas('order', function ($query) {
            $query->whereIn('status', ['paid', 'shipped', 'completed', 'delivered']);
        })
        ->where('created_at', '>=', now()->subDays(30))
        ->select('product_id', 'product_variant_id', DB::raw('SUM(quantity) as total_qty'))
        ->groupBy('product_id', 'product_variant_id')
        ->get();

        $salesLookup = [];
        $grandTotalSales = 0;
        foreach ($orderItemsGrouped as $item) {
            $key = $item->product_id . '_' . ($item->product_variant_id ?? '0');
            $salesLookup[$key] = (float) $item->total_qty;
            $grandTotalSales += (float) $item->total_qty;
        }

        foreach ($skus as &$sku) {
            $key = ($sku['type'] === 'simple') 
                ? $sku['id'] . '_0' 
                : $sku['model']->product_id . '_' . $sku['id'];
            $sku['sales_qty'] = $salesLookup[$key] ?? 0.0;
        }
        unset($sku);

        // Urutkan penjualan secara descending untuk mencari persentase kumulatif
        uasort($skus, function ($a, $b) {
            return $b['sales_qty'] <=> $a['sales_qty'];
        });

        // 3. Ambil data penjualan harian 30 hari terakhir dalam satu kueri agregasi (Mencegah N+1 Queries)
        $dailySalesGrouped = OrderItem::whereHas('order', function ($query) {
            $query->whereIn('status', ['paid', 'shipped', 'completed', 'delivered']);
        })
        ->where('created_at', '>=', now()->subDays(30))
        ->select(
            'product_id', 
            'product_variant_id', 
            DB::raw('DATE(created_at) as date'), 
            DB::raw('SUM(quantity) as total_qty')
        )
        ->groupBy('product_id', 'product_variant_id', DB::raw('DATE(created_at)'))
        ->get();

        $dailySalesLookup = [];
        foreach ($dailySalesGrouped as $item) {
            $key = $item->product_id . '_' . ($item->product_variant_id ?? '0');
            $dailySalesLookup[$key][$item->date] = (float) $item->total_qty;
        }

        // Hitung klasifikasi ABC untuk tiap SKU berdasarkan kontribusi kumulatif
        $runningSum = 0;
        $zScores = ['A' => 2.05, 'B' => 1.65, 'C' => 1.28];
        $serviceLevels = ['A' => '98%', 'B' => '95%', 'C' => '90%'];

        foreach ($skus as &$sku) {
            $qty = $sku['sales_qty'];
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

            $key = ($sku['type'] === 'simple') 
                ? $sku['id'] . '_0' 
                : $sku['model']->product_id . '_' . $sku['id'];

            $salesData = $dailySalesLookup[$key] ?? [];

            // Buat array daily sales berisi 30 hari penuh
            $dailySales = [];
            for ($i = 29; $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $dailySales[$dateStr] = (float)($salesData[$dateStr] ?? 0.0);
            }

            // Set data dinamis ke model (di-render ke view)
            $sku['model']->abc_class = $class;
            $sku['model']->z_score = $zScores[$class];
            $sku['model']->service_level = $serviceLevels[$class];
            $sku['model']->std_dev_30d = round($this->calculateStandardDeviation(array_values($dailySales)), 2);
            $sku['model']->avg_sales_30d = round(array_sum($dailySales) / 30, 2);
        }
        unset($sku);

        return view('pages.admin.safety-stock.calculation', compact('products'));
    }

    /**
     * Menjalankan Algoritma Safety Stock Statistik (Kalkulasi Massal)
     */
    public function calculate(Request $request)
    {
        try {
            // Jalankan command kalkulator safety stock
            Artisan::call('safety-stock:recalculate');

            return redirect()->back()->with('success', 'Kalkulasi otomatis Safety Stock untuk semua Varian & Produk berhasil disinkronkan!');
        } catch (\Exception $e) {
            Log::error('Safety Stock Calculation Controller Error: ' . $e->getMessage());
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
