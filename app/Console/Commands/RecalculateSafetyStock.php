<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OrderItem;
use App\Models\SalesHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateSafetyStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'safety-stock:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate Safety Stock automatically using 90-day (3 months) historical sales data from Olsera POS & E-Commerce';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Safety Stock recalculation (90-day window, 4-day lead time)...');
        Log::info('Artisan safety-stock:recalculate (90-day window) started.');

        try {
            $daysWindow = 90;
            $defaultLeadTime = 4; // 4 hari standar untuk semua barang
            $startDate = now()->subDays($daysWindow)->toDateString();

            // 1. Dapatkan semua SKU (Simple Products & Product Variants)
            $simpleProducts = Product::doesntHave('variants')->get();
            $variants = ProductVariant::all();

            $skus = [];

            // Petakan Simple Products ke struktur SKU
            foreach ($simpleProducts as $product) {
                $skus[] = [
                    'type' => 'simple',
                    'id' => $product->id,
                    'model' => $product,
                    'lead_time' => $defaultLeadTime,
                ];
            }

            // Petakan Product Variants ke struktur SKU
            foreach ($variants as $variant) {
                $skus[] = [
                    'type' => 'variant',
                    'id' => $variant->id,
                    'model' => $variant,
                    'lead_time' => $defaultLeadTime,
                ];
            }

            // 2. Ambil data penjualan 90 hari dari E-Commerce (Order Items Paid/Completed)
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

            // Urutkan penjualan secara descending untuk Pareto ABC Cumulative Percentage
            uasort($skus, function ($a, $b) {
                return $b['sales_qty'] <=> $a['sales_qty'];
            });

            $zScores = ['A' => 2.05, 'B' => 1.65, 'C' => 1.28];
            $runningSum = 0;

            // 4. Hitung Standard Deviasi, Avg Sales, dan Safety Stock per SKU (90 Hari)
            foreach ($skus as $sku) {
                $model = $sku['model'];
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

                $zScore = $zScores[$class];
                $leadTime = $defaultLeadTime; // 4 hari standar

                $key = ($sku['type'] === 'simple') 
                    ? $sku['id'] . '_0' 
                    : $model->product_id . '_' . $sku['id'];

                $salesData = $dailySalesLookup[$key] ?? [];

                // Array penjualan harian untuk 90 hari penuh
                $dailySales = [];
                for ($i = ($daysWindow - 1); $i >= 0; $i--) {
                    $dateStr = now()->subDays($i)->format('Y-m-d');
                    $dailySales[$dateStr] = (float)($salesData[$dateStr] ?? 0.0);
                }

                $avgSales = array_sum($dailySales) / $daysWindow;
                $stdDev = $this->calculateStandardDeviation(array_values($dailySales));

                // Rumus SS = Z * std_dev * sqrt(lead_time)
                $safetyStock = $zScore * $stdDev * sqrt($leadTime);

                // Update ke database
                $model->update([
                    'nilai_ss' => ceil($safetyStock),
                    'rata_penjualan' => round($avgSales, 2),
                    'lead_time' => $leadTime
                ]);
            }

            // 5. Update parent products dengan akumulasi variannya secara batch (Anti N+1 Query)
            $variantAggregates = ProductVariant::selectRaw('product_id, SUM(nilai_ss) as total_ss, SUM(rata_penjualan) as total_avg')
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            $parentProducts = Product::has('variants')->get();
            foreach ($parentProducts as $parent) {
                $agg = $variantAggregates->get($parent->id);
                $parent->update([
                    'nilai_ss' => $agg ? (int) $agg->total_ss : 0,
                    'rata_penjualan' => $agg ? (float) $agg->total_avg : 0,
                    'lead_time' => $defaultLeadTime
                ]);
            }

            $this->info('Recalculation completed successfully for ' . count($skus) . ' SKUs using 90-day Olsera & E-Commerce sales data.');
            Log::info('Artisan safety-stock:recalculate finished successfully.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Recalculation failed: ' . $e->getMessage());
            Log::error('Safety Stock Recalculation Command Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return Command::FAILURE;
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
