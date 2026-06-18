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

            // Hitung penjualan 30 hari terakhir untuk Simple Products
            foreach ($simpleProducts as $product) {
                $totalQty = (float) OrderItem::where('product_id', $product->id)
                    ->whereNull('product_variant_id')
                    ->whereHas('order', function ($query) {
                        $query->whereIn('status', ['paid', 'shipped', 'completed']);
                    })
                    ->where('created_at', '>=', now()->subDays(30))
                    ->sum('quantity') ?? 0;

                $skus[] = [
                    'type' => 'simple',
                    'id' => $product->id,
                    'model' => $product,
                    'sales_qty' => $totalQty,
                    'lead_time' => $product->lead_time ?: 3, // Fallback ke default 3 hari
                ];
            }

            // Hitung penjualan 30 hari terakhir untuk Product Variants
            foreach ($variants as $variant) {
                $totalQty = (float) OrderItem::where('product_variant_id', $variant->id)
                    ->whereHas('order', function ($query) {
                        $query->whereIn('status', ['paid', 'shipped', 'completed']);
                    })
                    ->where('created_at', '>=', now()->subDays(30))
                    ->sum('quantity') ?? 0;

                $skus[] = [
                    'type' => 'variant',
                    'id' => $variant->id,
                    'model' => $variant,
                    'sales_qty' => $totalQty,
                    'lead_time' => $variant->lead_time ?: 3, // Fallback ke default 3 hari
                ];
            }

            // 2. Lakukan ABC Classification secara global berdasarkan penjualan seluruh SKU
            uasort($skus, function ($a, $b) {
                return $b['sales_qty'] <=> $a['sales_qty'];
            });

            $grandTotalSales = array_sum(array_column($skus, 'sales_qty'));
            $runningSum = 0;

            foreach ($skus as &$sku) {
                $qty = $sku['sales_qty'];
                if ($grandTotalSales > 0) {
                    $runningSum += $qty;
                    $cumulativePercentage = ($runningSum / $grandTotalSales) * 100;
                } else {
                    $cumulativePercentage = 100;
                }

                if ($qty == 0) {
                    $sku['class'] = 'C';
                } elseif ($cumulativePercentage <= 70) {
                    $sku['class'] = 'A';
                } elseif ($cumulativePercentage <= 90) {
                    $sku['class'] = 'B';
                } else {
                    $sku['class'] = 'C';
                }
            }
            unset($sku); // Break reference

            $zScores = ['A' => 2.05, 'B' => 1.65, 'C' => 1.28];

            // 3. Hitung Standard Deviasi, Avg Sales, dan Safety Stock per SKU
            foreach ($skus as $sku) {
                $model = $sku['model'];
                $class = $sku['class'];
                $zScore = $zScores[$class];
                $leadTime = $sku['lead_time'];

                // Ambil penjualan harian 30 hari terakhir
                $query = OrderItem::whereHas('order', function ($query) {
                    $query->whereIn('status', ['paid', 'shipped', 'completed']);
                })->where('created_at', '>=', now()->subDays(30));

                if ($sku['type'] === 'simple') {
                    $query->where('product_id', $sku['id'])->whereNull('product_variant_id');
                } else {
                    $query->where('product_variant_id', $sku['id']);
                }

                $salesData = $query->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity) as total_qty'))
                    ->groupBy('date')
                    ->pluck('total_qty', 'date')
                    ->toArray();

                // daily sales array untuk 30 hari penuh
                $dailySales = [];
                for ($i = 29; $i >= 0; $i--) {
                    $dateStr = now()->subDays($i)->format('Y-m-d');
                    $dailySales[$dateStr] = (float)($salesData[$dateStr] ?? 0);
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
