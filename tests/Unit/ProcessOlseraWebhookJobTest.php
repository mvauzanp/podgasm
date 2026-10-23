<?php

namespace Tests\Unit;

use App\Jobs\ProcessOlseraWebhookJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessOlseraWebhookJobTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'nama_kategori' => 'E-Liquid',
            'slug' => 'e-liquid'
        ]);
    }

    /**
     * Skenario 1: Penjualan Kasir POS untuk Produk Standar (tanpa varian)
     */
    public function test_it_deducts_stock_for_standalone_product_when_pos_order_webhook_received(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'TEST-LIQUID-01',
            'nama_barang' => 'Podgasm Ice Mango',
            'slug' => 'podgasm-ice-mango',
            'harga_jual' => 120000,
            'harga_pokok' => 80000,
            'stok_aktual' => 20,
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $payload = [
            'event' => 'order.completed',
            'data' => [
                'order_no' => 'POS-ORD-001',
                'items' => [
                    [
                        'sku' => 'TEST-LIQUID-01',
                        'qty' => 4,
                        'name' => 'Podgasm Ice Mango'
                    ]
                ]
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $product->refresh();
        $this->assertEquals(16, $product->stok_aktual);

        $salesHistory = SalesHistory::where('product_id', $product->id)->first();
        $this->assertNotNull($salesHistory);
        $this->assertEquals(4, $salesHistory->qty_sold);
    }

    /**
     * Skenario 2: Penjualan Kasir POS untuk Produk Varian & update roll-up ke induk
     */
    public function test_it_deducts_stock_for_product_variants_and_updates_parent_product_stock(): void
    {
        $parent = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'OXVA-XLIM-PRO',
            'nama_barang' => 'OXVA Xlim Pro 2',
            'slug' => 'oxva-xlim-pro-2',
            'harga_jual' => 350000,
            'harga_pokok' => 280000,
            'stok_aktual' => 25,
            'lead_time' => 4,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $var1 = ProductVariant::create([
            'product_id' => $parent->id,
            'nama_varian' => 'Black Carbon',
            'kode_barang' => 'OXVA-VAR-BLK',
            'harga_jual' => 350000,
            'harga_pokok' => 280000,
            'stok_aktual' => 10,
            'lead_time' => 4,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $var2 = ProductVariant::create([
            'product_id' => $parent->id,
            'nama_varian' => 'Silver Grey',
            'kode_barang' => 'OXVA-VAR-SLV',
            'harga_jual' => 350000,
            'harga_pokok' => 280000,
            'stok_aktual' => 15,
            'lead_time' => 4,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $payload = [
            'event' => 'sales_order',
            'data' => [
                'order_no' => 'POS-ORD-002',
                'items' => [
                    ['sku' => 'OXVA-VAR-BLK', 'qty' => 3],
                    ['sku' => 'OXVA-VAR-SLV', 'qty' => 5],
                ]
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $var1->refresh();
        $var2->refresh();
        $parent->refresh();

        $this->assertEquals(7, $var1->stok_aktual);
        $this->assertEquals(10, $var2->stok_aktual);
        $this->assertEquals(17, $parent->stok_aktual); // 7 + 10 = 17
    }

    /**
     * Skenario 3: Penjualan Kasir POS melebihi stok yang tersedia (Edge Case Defensif)
     */
    public function test_it_caps_stock_at_zero_when_sales_qty_exceeds_available_stock(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'LIMITED-EDITION',
            'nama_barang' => 'Limited Edition Cap',
            'slug' => 'limited-edition-cap',
            'harga_jual' => 50000,
            'harga_pokok' => 30000,
            'stok_aktual' => 2,
            'lead_time' => 1,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $payload = [
            'event' => 'order.completed',
            'data' => [
                'order_no' => 'POS-ORD-003',
                'items' => [
                    ['sku' => 'LIMITED-EDITION', 'qty' => 10]
                ]
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $product->refresh();
        $this->assertEquals(0, $product->stok_aktual);
    }

    /**
     * Skenario 4: Transaksi dengan SKU yang tidak terdaftar di sistem (Edge Case)
     */
    public function test_it_handles_unknown_sku_gracefully_without_exception(): void
    {
        $payload = [
            'event' => 'order.completed',
            'data' => [
                'order_no' => 'POS-ORD-004',
                'items' => [
                    ['sku' => 'NON-EXISTENT-SKU-999', 'qty' => 2]
                ]
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();
        $this->assertTrue(true);
    }

    /**
     * Skenario 5: Pembaruan stok tunggal (Stock Adjustment / Opname dari Olsera)
     */
    public function test_it_handles_stock_adjustment_payload(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'STOCK-ADJ-01',
            'nama_barang' => 'Coil Master 0.6',
            'slug' => 'coil-master-06',
            'harga_jual' => 45000,
            'harga_pokok' => 30000,
            'stok_aktual' => 5,
            'lead_time' => 2,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $payload = [
            'event' => 'inventory.stock_updated',
            'data' => [
                'sku' => 'STOCK-ADJ-01',
                'stock_qty' => 50
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $product->refresh();
        $this->assertEquals(50, $product->stok_aktual);
    }

    /**
     * Skenario 6: Pembaruan batch item list (Multi-produk opname/transfer)
     */
    public function test_it_handles_batch_item_stock_updates(): void
    {
        $p1 = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'BATCH-01',
            'nama_barang' => 'Item 1',
            'slug' => 'item-1',
            'harga_jual' => 10000,
            'harga_pokok' => 5000,
            'stok_aktual' => 10,
            'lead_time' => 1,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $p2 = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'BATCH-02',
            'nama_barang' => 'Item 2',
            'slug' => 'item-2',
            'harga_jual' => 20000,
            'harga_pokok' => 10000,
            'stok_aktual' => 20,
            'lead_time' => 1,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $payload = [
            'event' => 'inventory.bulk_update',
            'data' => [
                ['sku' => 'BATCH-01', 'stock_qty' => 35],
                ['sku' => 'BATCH-02', 'stock_qty' => 45],
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $p1->refresh();
        $p2->refresh();

        $this->assertEquals(35, $p1->stok_aktual);
        $this->assertEquals(45, $p2->stok_aktual);
    }

    /**
     * Skenario 7: Olsera mengirimkan SKU dengan prefix otomatis (SKU-P-xxx atau SKU-V-xxx)
     */
    public function test_it_handles_olsera_prefixed_sku_for_product_and_variant(): void
    {
        // 1. Produk dengan kode_barang numerik murni
        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => '120687279',
            'nama_barang' => 'OXVA XLIM PRO 2 SE',
            'slug' => 'oxva-xlim-pro-2-se',
            'harga_jual' => 350000,
            'harga_pokok' => 250000,
            'stok_aktual' => 20,
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $payload = [
            'event' => 'stock_updated',
            'data' => [
                'sku' => 'SKU-P-120687279',
                'stock_qty' => 12
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $product->refresh();
        $this->assertEquals(12, $product->stok_aktual);

        // 2. Varian dengan kode_barang numerik
        $parent = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'PARENT-DEV',
            'nama_barang' => 'Parent Device',
            'slug' => 'parent-dev',
            'harga_jual' => 100000,
            'harga_pokok' => 70000,
            'stok_aktual' => 10,
            'lead_time' => 2,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $variant = ProductVariant::create([
            'product_id' => $parent->id,
            'nama_varian' => 'Gunmetal',
            'kode_barang' => '88888',
            'harga_jual' => 100000,
            'harga_pokok' => 70000,
            'stok_aktual' => 10,
            'lead_time' => 2,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $payloadVariant = [
            'event' => 'stock_updated',
            'data' => [
                'sku' => 'SKU-V-88888',
                'stock_qty' => 3
            ]
        ];

        (new ProcessOlseraWebhookJob($payloadVariant))->handle();

        $variant->refresh();
        $parent->refresh();
        $this->assertEquals(3, $variant->stok_aktual);
        $this->assertEquals(3, $parent->stok_aktual);
    }

    /**
     * Skenario 8: Olsera mengirimkan payload pembaruan stok tanpa kolom 'sku', hanya memiliki 'id'
     */
    public function test_it_handles_stock_update_when_sku_is_absent_but_id_is_provided(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'PROD-ID-RESOLVE',
            'nama_barang' => 'ID Resolve Product',
            'slug' => 'id-resolve-product',
            'harga_jual' => 50000,
            'harga_pokok' => 30000,
            'stok_aktual' => 15,
            'lead_time' => 1,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $payload = [
            'event' => 'stock_updated',
            'data' => [
                'id' => $product->id,
                'stock_qty' => 7
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $product->refresh();
        $this->assertEquals(7, $product->stok_aktual);
    }

    /**
     * Skenario 9: Transaksi di POS Dibatalkan (Void / Sales Return) -> Stok bertambah kembali
     */
    public function test_it_handles_pos_void_or_sales_return_restocking(): void
    {
        $parent = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'VOID-TEST-PARENT',
            'nama_barang' => 'Void Parent Item',
            'slug'        => 'void-parent-item',
            'harga_jual'  => 100000,
            'harga_pokok' => 70000,
            'stok_aktual' => 5,
            'lead_time'   => 1,
            'nilai_ss'    => 0,
            'rata_penjualan' => 0
        ]);

        $variant = ProductVariant::create([
            'product_id'  => $parent->id,
            'nama_varian' => 'Void Variant Berry',
            'kode_barang' => 'SKU-VOID-01',
            'harga_jual'  => 100000,
            'harga_pokok' => 70000,
            'stok_aktual' => 5,
            'lead_time'   => 1,
            'nilai_ss'    => 0,
            'rata_penjualan' => 0
        ]);

        // Kasir void pesanan di Olsera POS sehingga stok bertambah dari 5 kembali ke 6
        $payload = [
            'event' => 'stock_updated',
            'data'  => [
                'sku'       => 'SKU-VOID-01',
                'stock_qty' => 6,
                'note'      => 'Void Transaksi Kasir POS'
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $variant->refresh();
        $parent->refresh();
        $this->assertEquals(6, $variant->stok_aktual, 'Stok varian harus bertambah menjadi 6');
        $this->assertEquals(6, $parent->stok_aktual, 'Stok induk harus ter-rollup menjadi 6');
    }

    /**
     * Skenario 10: Ada Barang Masuk Baru dari Pembelian (Purchase Order / Restock)
     */
    public function test_it_handles_incoming_goods_purchase_restocking(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'PURCHASE-IN-001',
            'nama_barang' => 'Incoming Liquid 30ml',
            'slug'        => 'incoming-liquid-30ml',
            'harga_jual'  => 120000,
            'harga_pokok' => 80000,
            'stok_aktual' => 2, // Stok menipis
            'lead_time'   => 2,
            'nilai_ss'    => 0,
            'rata_penjualan' => 0
        ]);

        // Kulakan masuk 50 pcs dari supplier di Olsera Backoffice
        $payload = [
            'event' => 'inventory_update',
            'data'  => [
                'sku'       => 'PURCHASE-IN-001',
                'stock_qty' => 52, // 2 + 50
                'note'      => 'Penerimaan Barang PO Supplier'
            ]
        ];

        (new ProcessOlseraWebhookJob($payload))->handle();

        $product->refresh();
        $this->assertEquals(52, $product->stok_aktual, 'Stok produk di web harus otomatis bertambah menjadi 52');
    }
}
