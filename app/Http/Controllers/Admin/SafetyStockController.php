<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OrderItem;
use App\Models\SalesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SafetyStockController extends Controller
{
    /**
     * Menampilkan daftar produk dengan klasifikasi ABC dan standar deviasi berbasis 90 hari (3 bulan)
     */
    public function index()
    {
        $products = Product::with(['category', 'variants'])->get();
        $daysWindow = 90;
        $defaultLeadTime = 4; // 4 hari standar untuk semua barang
        $startDate = now()->subDays($daysWindow)->toDateString();

        // 1. Dapatkan semua SKU (Simple Products & Product Variants)
        $skus = [];
        foreach ($products as $product) {
            if (!$product->hasVariants()) {
                $skus[] = [
                    'type' => 'simple',
                    'id' => $product->id,
                    'model' => $product,
                    'lead_time' => $defaultLeadTime,
                ];
            } else {
                foreach ($product->variants as $variant) {
                    $skus[] = [
                        'type' => 'variant',
                        'id' => $variant->id,
                        'model' => $variant,
                        'lead_time' => $defaultLeadTime,
                    ];
                }
            }
        }

        // 2. Ambil data penjualan 90 hari dari E-Commerce
        $ecommerceDailySales = OrderItem::whereHas('order', function ($query) {
            $query->whereIn('status', ['paid', 'shipped', 'completed', 'delivered']);
        })
        ->where('created_at', '>=', now()->subDays($daysWindow))
        ->select(
            'product_id', 
            'product_variant_id', 
            DB::raw('DATE(created_at) as date'), 
            DB::raw('SUM(quantity) as total_qty')
        )
        ->groupBy('product_id', 'product_variant_id', DB::raw('DATE(created_at)'))
        ->get();

        // 3. Ambil data penjualan 90 hari dari Olsera POS (sales_histories)
        $posDailySales = SalesHistory::where('date', '>=', $startDate)
            ->select(
                'product_id',
                'product_variant_id',
                'date',
                DB::raw('SUM(qty_sold) as total_qty')
            )
            ->groupBy('product_id', 'product_variant_id', 'date')
            ->get();

        // Gabungkan kedua sumber data (E-Commerce + Olsera POS)
        $dailySalesLookup = [];
        $totalSalesLookup = [];
        $grandTotalSales = 0;

        foreach ($ecommerceDailySales as $item) {
            $key = $item->product_id . '_' . ($item->product_variant_id ?? '0');
            $qty = (float) $item->total_qty;
            $dailySalesLookup[$key][$item->date] = ($dailySalesLookup[$key][$item->date] ?? 0) + $qty;
            $totalSalesLookup[$key] = ($totalSalesLookup[$key] ?? 0) + $qty;
            $grandTotalSales += $qty;
        }

        foreach ($posDailySales as $item) {
            $key = $item->product_id . '_' . ($item->product_variant_id ?? '0');
            $dateStr = is_string($item->date) ? $item->date : $item->date->format('Y-m-d');
            $qty = (float) $item->total_qty;
            $dailySalesLookup[$key][$dateStr] = ($dailySalesLookup[$key][$dateStr] ?? 0) + $qty;
            $totalSalesLookup[$key] = ($totalSalesLookup[$key] ?? 0) + $qty;
            $grandTotalSales += $qty;
        }

        foreach ($skus as &$sku) {
            $key = ($sku['type'] === 'simple') 
                ? $sku['id'] . '_0' 
                : $sku['model']->product_id . '_' . $sku['id'];
            $sku['sales_qty'] = $totalSalesLookup[$key] ?? 0.0;
        }
        unset($sku);

        // Urutkan penjualan secara descending untuk mencari persentase kumulatif
        uasort($skus, function ($a, $b) {
            return $b['sales_qty'] <=> $a['sales_qty'];
        });

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

            // Buat array daily sales berisi 90 hari penuh
            $dailySales = [];
            for ($i = ($daysWindow - 1); $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $dailySales[$dateStr] = (float)($salesData[$dateStr] ?? 0.0);
            }

            $avgSales = array_sum($dailySales) / $daysWindow;
            $stdDev = $this->calculateStandardDeviation(array_values($dailySales));

            // Set data dinamis ke model (di-render ke view)
            $sku['model']->abc_class = $class;
            $sku['model']->z_score = $zScores[$class];
            $sku['model']->service_level = $serviceLevels[$class];
            $sku['model']->std_dev_90d = round($stdDev, 2);
            $sku['model']->avg_sales_90d = round($avgSales, 2);
            $sku['model']->total_sales_90d = (int) $qty;
            $sku['model']->lead_time_days = $defaultLeadTime;
        }
        unset($sku);

        return view('pages.admin.safety-stock.calculation', compact('products', 'daysWindow', 'defaultLeadTime'));
    }

    /**
     * Endpoint API AJAX untuk memuat data riwayat penjualan 90 hari per SKU untuk modal grafik
     */
    public function history($type, $id)
    {
        $daysWindow = 90;
        $defaultLeadTime = 4;
        $startDate = now()->subDays($daysWindow)->toDateString();

        if ($type === 'variant') {
            $model = ProductVariant::with('product')->findOrFail($id);
            $productName = ($model->product->nama_barang ?? 'Produk') . ' - ' . $model->nama_varian;
            $skuCode = $model->kode_barang ?? $model->product->kode_barang ?? '-';
            $productId = $model->product_id;
            $variantId = $model->id;
            $currentSs = $model->nilai_ss;
        } else {
            $model = Product::findOrFail($id);
            $productName = $model->nama_barang;
            $skuCode = $model->kode_barang ?? '-';
            $productId = $model->id;
            $variantId = null;
            $currentSs = $model->nilai_ss;
        }

        // Ambil penjualan dari E-Commerce
        $ecommerceDaily = OrderItem::whereHas('order', function ($query) {
            $query->whereIn('status', ['paid', 'shipped', 'completed', 'delivered']);
        })
        ->where('product_id', $productId)
        ->where('created_at', '>=', now()->subDays($daysWindow));

        if ($variantId) {
            $ecommerceDaily->where('product_variant_id', $variantId);
        } else {
            $ecommerceDaily->whereNull('product_variant_id');
        }

        $ecomData = $ecommerceDaily->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity) as qty'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('qty', 'date')
            ->toArray();

        // Ambil penjualan dari Olsera POS
        $posDaily = SalesHistory::where('product_id', $productId)
            ->where('date', '>=', $startDate);

        if ($variantId) {
            $posDaily->where('product_variant_id', $variantId);
        } else {
            $posDaily->whereNull('product_variant_id');
        }

        $posData = $posDaily->select('date', DB::raw('SUM(qty_sold) as qty'))
            ->groupBy('date')
            ->get();

        $mergedDaily = [];
        foreach ($posData as $p) {
            $dateStr = is_string($p->date) ? $p->date : $p->date->format('Y-m-d');
            $mergedDaily[$dateStr] = ($mergedDaily[$dateStr] ?? 0) + (int) $p->qty;
        }
        foreach ($ecomData as $dStr => $q) {
            $mergedDaily[$dStr] = ($mergedDaily[$dStr] ?? 0) + (int) $q;
        }

        // Build 90-day time-series array
        $historySeries = [];
        $totalSold = 0;
        for ($i = ($daysWindow - 1); $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $qty = (int) ($mergedDaily[$d] ?? 0);
            $totalSold += $qty;
            $historySeries[] = [
                'date' => $d,
                'label' => now()->subDays($i)->format('d M'),
                'qty' => $qty,
            ];
        }

        $qtyValues = array_column($historySeries, 'qty');
        $avgDaily = round($totalSold / $daysWindow, 2);
        $stdDev = round($this->calculateStandardDeviation($qtyValues), 2);

        return response()->json([
            'success' => true,
            'sku_name' => $productName,
            'sku_code' => $skuCode,
            'days_window' => $daysWindow,
            'lead_time' => $defaultLeadTime,
            'total_sold_90d' => $totalSold,
            'avg_daily_sales' => $avgDaily,
            'std_dev' => $stdDev,
            'current_safety_stock' => $currentSs,
            'history' => $historySeries,
        ]);
    }

    /**
     * Menjalankan Algoritma Safety Stock Statistik (Kalkulasi Massal)
     */
    public function calculate(Request $request)
    {
        try {
            // Jalankan command kalkulator safety stock
            Artisan::call('safety-stock:recalculate');

            return redirect()->back()->with('success', 'Kalkulasi otomatis Safety Stock (data 90 hari & lead time 4 hari) untuk semua Varian & Produk berhasil disinkronkan!');
        } catch (\Exception $e) {
            Log::error('Safety Stock Calculation Controller Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Terjadi kesalahan sistem saat menghitung Safety Stock. Silakan coba lagi.']);
        }
    }

    /**
     * Import file laporan penjualan historis dari Olsera Backoffice (Excel/CSV - Bisa 1 file atau banyak file sekaligus)
     */
    public function importOlseraReport(Request $request, \App\Services\OlseraSalesImportService $importService)
    {
        $files = $request->file('sales_files') ?? ($request->file('sales_file') ? [$request->file('sales_file')] : []);

        if (empty($files)) {
            return redirect()->back()->withErrors(['msg' => 'Silakan pilih file Excel (.xlsx, .xls) atau .csv laporan penjualan Olsera.']);
        }

        try {
            $result = $importService->import($files);
            return redirect()->back()->with('success', $result['message']);
        } catch (\Exception $e) {
            Log::error('Import Olsera Report Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
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
