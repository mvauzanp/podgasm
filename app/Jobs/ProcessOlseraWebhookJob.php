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

        // 2. Tangani Pembaruan / Penambahan Produk Penuh (Product Create / Update)
        if (isset($data['name']) && isset($data['sku'])) {
            $this->syncFullProduct($data);
            return;
        }

        // 3. Tangani Pembaruan Stok Singkat (Stock / Inventory Update)
        $sku = $data['sku'] ?? $data['item_sku'] ?? $data['kode_barang'] ?? null;
        $newStock = $data['stock_qty'] ?? $data['stock'] ?? $data['stok_aktual'] ?? null;

        if ($sku && $newStock !== null) {
            $this->updateStockOnly($sku, (int) $newStock);
            return;
        }

        Log::info('Olsera Webhook Ingestion: Payload diproses tanpa aksi spesifik produk.', ['payload' => $this->payload]);
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
    protected function updateStockOnly(string $sku, int $newStock): void
    {
        DB::transaction(function () use ($sku, $newStock) {
            // 1. Cek varian produk
            $variant = ProductVariant::where('kode_barang', $sku)->lockForUpdate()->first();
            if ($variant) {
                $variant->update(['stok_aktual' => $newStock]);
                
                // Update total stok pada produk induknya
                $totalStock = ProductVariant::where('product_id', $variant->product_id)->sum('stok_aktual');
                Product::where('id', $variant->product_id)->update(['stok_aktual' => (int) $totalStock]);
                
                Log::info("Olsera Webhook Queue: Sukses update stok Varian [{$sku}] menjadi {$newStock} unit.");
                return;
            }

            // 2. Cek produk standar
            $product = Product::where('kode_barang', $sku)->lockForUpdate()->first();
            if ($product) {
                $product->update(['stok_aktual' => $newStock]);
                Log::info("Olsera Webhook Queue: Sukses update stok Produk [{$sku}] menjadi {$newStock} unit.");
            } else {
                Log::warning("Olsera Webhook Queue: SKU [{$sku}] tidak ditemukan di database Podgasm.");
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
