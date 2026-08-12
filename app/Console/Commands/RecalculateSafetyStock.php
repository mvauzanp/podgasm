<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OrderItem;
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
    protected $description = 'Recalculate Safety Stock automatically at the SKU level (Product Variant or Simple Product)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Safety Stock recalculation at SKU level...');
        Log::info('Artisan safety-stock:recalculate started.');

        try {
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
                    'lead_time' => $product->lead_time ?: 3, // Fallback ke default 3 hari
                ];
            }

            // Petakan Product Variants ke struktur SKU
            foreach ($variants as $variant) {
                $skus[] = [
                    'type' => 'variant',
                    'id' => $variant->id,
                    'model' => $variant,
                    'lead_time' => $variant->lead_time ?: 3, // Fallback ke default 3 hari
                ];
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

            $zScores = ['A' => 2.05, 'B' => 1.65, 'C' => 1.28];
            $runningSum = 0;

            // Hitung Standard Deviasi, Avg Sales, dan Safety Stock per SKU
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
                $leadTime = $sku['lead_time'];

                $key = ($sku['type'] === 'simple') 
                    ? $sku['id'] . '_0' 
                    : $model->product_id . '_' . $sku['id'];

                $salesData = $dailySalesLookup[$key] ?? [];

                // daily sales array untuk 30 hari penuh
                $dailySales = [];
                for ($i = 29; $i >= 0; $i--) {
                    $dateStr = now()->subDays($i)->format('Y-m-d');
                    $dailySales[$dateStr] = (float)($salesData[$dateStr] ?? 0.0);
                }

                $avgSales = array_sum($dailySales) / 30;
                $stdDev = $this->calculateStandardDeviation(array_values($dailySales));

                // Rumus SS = Z * std_dev * sqrt(lead_time)
                $safetyStock = $zScore * $stdDev * sqrt($leadTime);

                // Update ke database
                $model->update([
                    'nilai_ss' => round($safetyStock, 2),
                    'rata_penjualan' => round($avgSales, 2),
                    'lead_time' => $leadTime
                ]);
            }

            // 4. Update parent products with the sum of their variants' safety stock metrics
            $parentProducts = Product::has('variants')->get();
            foreach ($parentProducts as $parent) {
                $parent->update([
                    'nilai_ss' => $parent->variants()->sum('nilai_ss'),
                    'rata_penjualan' => $parent->variants()->sum('rata_penjualan'),
                    'lead_time' => (int) $parent->variants()->avg('lead_time') ?: 3
                ]);
            }

            $this->info('Recalculation completed successfully for ' . count($skus) . ' SKUs.');
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
