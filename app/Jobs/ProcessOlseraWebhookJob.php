<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessOlseraWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 30]; // Retry backoff: 5 detik, 15 detik, 30 detik

    protected array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $event = $this->payload['event'] ?? $this->payload['event_type'] ?? null;
        $data = $this->payload['data'] ?? $this->payload;

        // 0. Tangani Event Penghapusan Produk (Product Delete)
        if (str_contains((string) $event, 'delete') && (isset($data['sku']) || isset($data['id']))) {
            $sku = trim($data['sku'] ?? $data['kode_barang'] ?? '');
            if (!empty($sku)) {
                $delProduct = Product::where('kode_barang', $sku)->first();
                if ($delProduct) {
                    $delProduct->delete();
                    Log::info("Olsera Webhook Queue: Produk [{$sku}] berhasil dinonaktifkan/dihapus.");
                    return;
                }
            }
        }

        // 1. Tangani Event / Data Kategori
        if (str_contains((string) $event, 'category') || isset($data['category_name']) || isset($data['category'])) {
            $categoryName = trim($data['category_name'] ?? $data['name'] ?? $data['category'] ?? '');
            if (!empty($categoryName)) {
                $cat = Category::firstOrCreate(
                    ['nama_kategori' => $categoryName],
                    ['slug' => Str::slug($categoryName)]
                );
                Log::info("Olsera Webhook Queue: Kategori [{$cat->nama_kategori}] (ID: {$cat->id}) berhasil disinkronkan.");
            }
        }

        // 2. Tangani Event Transaksi / Pesanan Kasir POS (Order Completed / Created)
        // 2. Tangani Event Transaksi / Pesanan Kasir POS (Order Completed / Created / Sales)
        $items = $data['items'] ?? $this->payload['items'] ?? $data['order_items'] ?? $data['details'] ?? $data['products'] ?? $data['lines'] ?? $data['order_details'] ?? null;
        if (!empty($items) && is_array($items) && (str_contains((string) $event, 'order') || str_contains((string) $event, 'sale') || str_contains((string) $event, 'transact') || str_contains((string) $event, 'close') || !empty($data['order_no']))) {
            $this->deductStockFromPosOrder($items);
            return;
        }

        // 3. Tangani Batch Item List (misal array pembaruan inventaris multi-produk)
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            foreach ($data as $subItem) {
                $subSku = $subItem['sku'] ?? $subItem['item_sku'] ?? $subItem['kode_barang'] ?? ($subItem['id'] ? 'SKU-P-' . $subItem['id'] : null);
                $subStock = $subItem['stock_qty'] ?? $subItem['stock'] ?? $subItem['stok_aktual'] ?? null;
                if ($subSku && $subStock !== null) {
                    $this->updateStockOnly((string) $subSku, (int) $subStock, $subItem);
                }
            }
            return;
        }

        // 4. Tangani Pembaruan / Penambahan Produk Penuh (Product Create / Update)
        if (isset($data['name']) && (isset($data['sku']) || isset($data['id']))) {
            $this->syncFullProduct($data);
            return;
        }

        // 5. Tangani Pembaruan Stok Singkat (Stock / Inventory Update)
        $sku = $data['sku'] ?? $data['item_sku'] ?? $data['kode_barang'] ?? ($data['id'] ? 'SKU-P-' . $data['id'] : null);
        $newStock = $data['stock_qty'] ?? $data['stock'] ?? $data['stok_aktual'] ?? null;

        if ($sku && $newStock !== null) {
            $this->updateStockOnly((string) $sku, (int) $newStock, $data);
            return;
        }

        Log::info('Olsera Webhook Ingestion: Payload diproses tanpa aksi spesifik produk.', ['payload' => $this->payload]);
    }

    /**
     * Memotong stok produk & varian secara langsung saat kasir toko fisik (POS) menyelesaikan transaksi
     */
    protected function deductStockFromPosOrder(array $items): void
    {
        DB::transaction(function () use ($items) {
            $today = now()->toDateString();

            foreach ($items as $item) {
                $sku = trim((string) ($item['sku'] ?? $item['kode_barang'] ?? $item['barcode'] ?? $item['item_sku'] ?? ''));
                $qty = (int) ($item['qty'] ?? $item['quantity'] ?? $item['jumlah'] ?? 1);
                $itemId = $item['product_id'] ?? $item['id'] ?? null;
                $numericId = null;

                if (preg_match('/^SKU-[PV]-(\d+)$/i', $sku, $matches)) {
                    $numericId = (int) $matches[1];
                } elseif (is_numeric($sku) && !empty($sku)) {
                    $numericId = (int) $sku;
                }
                if (!$numericId && $itemId && is_numeric($itemId)) {
                    $numericId = (int) $itemId;
                }

                if ($qty <= 0) {
                    continue;
                }

                // 1. Prioritas Lookup Varian
                $variant = null;
                if (!empty($sku)) {
                    $variant = ProductVariant::where('kode_barang', $sku)->lockForUpdate()->first();
                }
                if (!$variant && $numericId) {
                    $variant = ProductVariant::where('kode_barang', 'SKU-V-' . $numericId)
                        ->orWhere('kode_barang', (string) $numericId)
                        ->orWhere('id', $numericId)
                        ->lockForUpdate()
                        ->first();
                }
                if (!$variant && !empty($item['name'])) {
                    $variant = ProductVariant::where('nama_varian', trim($item['name']))->lockForUpdate()->first();
                }

                if ($variant) {
                    $newStock = max(0, (int) $variant->stok_aktual - $qty);
                    $variant->update(['stok_aktual' => $newStock]);

                    $totalVarStock = ProductVariant::where('product_id', $variant->product_id)->sum('stok_aktual');
                    Product::where('id', $variant->product_id)->update(['stok_aktual' => (int) $totalVarStock]);

                    // Catat ke sales_histories source: pos untuk Safety Stock harian
                    try {
                        $history = \App\Models\SalesHistory::firstOrCreate(
                            [
                                'product_id' => $variant->product_id,
                                'product_variant_id' => $variant->id,
                                'date' => $today,
                                'source' => 'pos'
                            ],
                            ['qty_sold' => 0]
                        );
                        $history->increment('qty_sold', $qty);
                    } catch (\Exception $e) {
                        Log::warning("Gagal mencatat sales history POS: " . $e->getMessage());
                    }

                    Log::info("Olsera POS Webhook: Berhasil memotong {$qty} unit dari Varian [{$sku}]. Sisa stok: {$newStock}.");
                    continue;
                }

                // 2. Lookup Produk Utama
                $product = null;
                if (!empty($sku)) {
                    $product = Product::where('kode_barang', $sku)->lockForUpdate()->first();
                }
                if (!$product && $numericId) {
                    $product = Product::where('kode_barang', 'SKU-P-' . $numericId)
                        ->orWhere('kode_barang', (string) $numericId)
                        ->orWhere('id', $numericId)
                        ->lockForUpdate()
                        ->first();
                }
                if (!$product && isset($item['name'])) {
                    $product = Product::where('nama_barang', trim($item['name']))->lockForUpdate()->first();
                }

                if ($product) {
                    $newStock = max(0, (int) $product->stok_aktual - $qty);
                    $product->update(['stok_aktual' => $newStock]);

                    try {
                        $history = \App\Models\SalesHistory::firstOrCreate(
                            [
                                'product_id' => $product->id,
                                'product_variant_id' => null,
                                'date' => $today,
                                'source' => 'pos'
                            ],
                            ['qty_sold' => 0]
                        );
                        $history->increment('qty_sold', $qty);
                    } catch (\Exception $e) {
                        Log::warning("Gagal mencatat sales history POS: " . $e->getMessage());
                    }

                    Log::info("Olsera POS Webhook: Berhasil memotong {$qty} unit dari Produk [{$product->nama_barang}]. Sisa stok: {$newStock}.");
                } else {
                    Log::warning("Olsera POS Webhook: Produk [SKU: {$sku}] dari transaksi kasir tidak ditemukan di database.");
                }
            }
        });
    }

    /**
     * Sinkronisasi produk penuh beserta kategori dan variannya
     */
    protected function syncFullProduct(array $data): void
    {
        DB::transaction(function () use ($data) {
            $categoryName = trim($data['category_name'] ?? $data['category'] ?? 'Umum');
            $category = Category::firstOrCreate(
                ['nama_kategori' => $categoryName],
                ['slug' => Str::slug($categoryName)]
            );

            $sku = trim($data['sku'] ?? '');
            $name = trim($data['name'] ?? '');
            $price = (float) ($data['price'] ?? $data['sell_price'] ?? 0);
            $costPrice = (float) ($data['cost_price'] ?? $data['buy_price'] ?? 0);
            $stock = (int) ($data['stock_qty'] ?? $data['stock'] ?? 0);
            $description = $data['description'] ?? '';

            $photo = $data['photo'] ?? $data['image'] ?? $data['image_url'] ?? $data['photo_url'] ?? $data['picture'] ?? $data['gambar'] ?? null;

            $product = Product::where('kode_barang', $sku)
                ->orWhere('nama_barang', $name)
                ->first();

            if (!$product) {
                $product = Product::create([
                    'nama_barang' => $name,
                    'slug' => Str::slug($name) . '-' . Str::random(4),
                    'category_id' => $category->id,
                    'kode_barang' => $sku,
                    'description' => $description,
                    'harga_jual' => $price,
                    'harga_pokok' => $costPrice,
                    'stok_aktual' => $stock,
                    'gambar' => $photo,
                ]);
                Log::info("Olsera Webhook Queue: Sukses membuat Produk Baru [{$product->nama_barang}] (SKU: {$sku}).");
            } else {
                $product->update([
                    'category_id' => $category->id,
                    'harga_jual' => $price > 0 ? $price : $product->harga_jual,
                    'harga_pokok' => $costPrice > 0 ? $costPrice : $product->harga_pokok,
                    'stok_aktual' => $stock,
                    'description' => $description ?: $product->description,
                    'gambar' => $photo ?: $product->gambar,
                ]);
                Log::info("Olsera Webhook Queue: Sukses memperbarui Produk [{$product->nama_barang}] (SKU: {$sku}).");
            }

            // Sinkronisasi varian jika tersedia dalam payload
            if (!empty($data['variants']) && is_array($data['variants'])) {
                $totalVarStock = 0;
                foreach ($data['variants'] as $vItem) {
                    $vName = trim($vItem['name'] ?? $vItem['nama_varian'] ?? '');
                    $vSku = trim($vItem['sku'] ?? $vItem['kode_barang'] ?? '');
                    $vPrice = (float) ($vItem['price'] ?? $vItem['sell_price'] ?? $price);
                    $vCost = (float) ($vItem['cost_price'] ?? $costPrice);
                    $vStock = (int) ($vItem['stock_qty'] ?? $vItem['stock'] ?? 0);
                    $vPhoto = $vItem['photo'] ?? $vItem['image'] ?? $vItem['image_url'] ?? $vItem['picture'] ?? $vItem['gambar'] ?? null;

                    $totalVarStock += $vStock;

                    if (!empty($vName)) {
                        $variant = ProductVariant::firstOrNew([
                            'product_id' => $product->id,
                            'nama_varian' => $vName,
                        ]);
                        $variant->kode_barang = $vSku ?: $variant->kode_barang;
                        $variant->harga_jual = $vPrice > 0 ? $vPrice : $variant->harga_jual;
                        $variant->harga_pokok = $vCost > 0 ? $vCost : $variant->harga_pokok;
                        $variant->stok_aktual = $vStock;
                        if (!empty($vPhoto)) {
                            $variant->gambar = $vPhoto;
                        }
                        $variant->save();
                    }
                }
                // Rollup total stok varian ke produk induk
                $product->update(['stok_aktual' => $totalVarStock]);
            }
        });
    }

    /**
     * Update hanya stok pada produk atau varian
     */
    protected function updateStockOnly(string $sku, int $newStock, ?array $rawItem = null): void
    {
        DB::transaction(function () use ($sku, $newStock, $rawItem) {
            $today = now()->toDateString();
            $cleanSku = trim($sku);
            $numericId = null;

            // Ekstrak ID numerik jika SKU memiliki pola SKU-P-12345 atau SKU-V-12345
            if (preg_match('/^SKU-[PV]-(\d+)$/i', $cleanSku, $matches)) {
                $numericId = (int) $matches[1];
            } elseif (is_numeric($cleanSku) && !empty($cleanSku)) {
                $numericId = (int) $cleanSku;
            }

            $rawId = $rawItem['id'] ?? $rawItem['product_id'] ?? $rawItem['item_id'] ?? null;
            if (!$numericId && $rawId && is_numeric($rawId)) {
                $numericId = (int) $rawId;
            }

            // 1. Cek Varian Produk
            $variant = null;
            if (!empty($cleanSku)) {
                $variant = ProductVariant::where('kode_barang', $cleanSku)->lockForUpdate()->first();
            }
            if (!$variant && $numericId) {
                $variant = ProductVariant::where('kode_barang', 'SKU-V-' . $numericId)
                    ->orWhere('kode_barang', (string) $numericId)
                    ->orWhere('id', $numericId)
                    ->lockForUpdate()
                    ->first();
            }
            if (!$variant && !empty($rawItem['name'])) {
                $variant = ProductVariant::where('nama_varian', trim($rawItem['name']))->lockForUpdate()->first();
            }

            if ($variant) {
                $oldStock = (int) $variant->stok_aktual;
                $variant->update(['stok_aktual' => max(0, $newStock)]);
                
                // Update total stok pada produk induknya
                $totalStock = ProductVariant::where('product_id', $variant->product_id)->sum('stok_aktual');
                Product::where('id', $variant->product_id)->update(['stok_aktual' => (int) $totalStock]);
                
                // Jika stok berkurang, catat ke sales_histories POS untuk perhitungan Safety Stock harian
                if ($newStock < $oldStock) {
                    $diffQty = $oldStock - $newStock;
                    try {
                        $history = \App\Models\SalesHistory::firstOrCreate(
                            [
                                'product_id' => $variant->product_id,
                                'product_variant_id' => $variant->id,
                                'date' => $today,
                                'source' => 'pos'
                            ],
                            ['qty_sold' => 0]
                        );
                        $history->increment('qty_sold', $diffQty);
                    } catch (\Exception $e) {
                        Log::warning("Gagal mencatat sales history POS: " . $e->getMessage());
                    }
                }

                Log::info("Olsera Webhook Queue: Sukses update stok Varian [{$cleanSku}] (ID: {$variant->id}) dari {$oldStock} menjadi {$newStock} unit. Total stok induk: {$totalStock}.");
                return;
            }

            // 2. Cek Produk Standar / Utama
            $product = null;
            if (!empty($cleanSku)) {
                $product = Product::where('kode_barang', $cleanSku)->lockForUpdate()->first();
            }
            if (!$product && $numericId) {
                $product = Product::where('kode_barang', 'SKU-P-' . $numericId)
                    ->orWhere('kode_barang', (string) $numericId)
                    ->orWhere('id', $numericId)
                    ->lockForUpdate()
                    ->first();
            }
            if (!$product && !empty($rawItem['name'])) {
                $product = Product::where('nama_barang', trim($rawItem['name']))->lockForUpdate()->first();
            }

            if ($product) {
                $oldStock = (int) $product->stok_aktual;
                $product->update(['stok_aktual' => max(0, $newStock)]);

                // Jika produk memiliki varian tunggal/varian tanpa stok spesifik, update varian terkait juga
                if ($product->hasVariants()) {
                    $firstVariant = $product->variants()->first();
                    if ($firstVariant && $product->variants()->count() === 1) {
                        $firstVariant->update(['stok_aktual' => max(0, $newStock)]);
                    }
                }

                if ($newStock < $oldStock) {
                    $diffQty = $oldStock - $newStock;
                    try {
                        $history = \App\Models\SalesHistory::firstOrCreate(
                            [
                                'product_id' => $product->id,
                                'product_variant_id' => null,
                                'date' => $today,
                                'source' => 'pos'
                            ],
                            ['qty_sold' => 0]
                        );
                        $history->increment('qty_sold', $diffQty);
                    } catch (\Exception $e) {
                        Log::warning("Gagal mencatat sales history POS: " . $e->getMessage());
                    }
                }

                Log::info("Olsera Webhook Queue: Sukses update stok Produk [{$product->nama_barang}] (SKU: {$cleanSku}) dari {$oldStock} menjadi {$newStock} unit.");
            } else {
                Log::warning("Olsera Webhook Queue: SKU [{$cleanSku}] tidak ditemukan di database Podgasm.", [
                    'cleanSku' => $cleanSku,
                    'numericId' => $numericId,
                    'rawItem' => $rawItem
                ]);
            }
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Olsera Webhook Ingestion Job Permanently Failed: ' . $exception->getMessage(), [
            'payload' => $this->payload,
            'trace'   => $exception->getTraceAsString()
        ]);
    }
}
