<?php

namespace Tests\Unit;

use App\Jobs\PushSalesOrderToOlseraJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OlseraApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PushSalesOrderToOlseraTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Buyer Test',
            'email' => 'buyer@podgasm.id',
            'password' => bcrypt('password'),
            'role' => 'customer'
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Pods',
            'slug' => 'pods'
        ]);
    }

    /**
     * Test 1: Pemotongan stok produk standar (non-varian) ke Olsera via POST /product/update
     */
    public function test_it_updates_olsera_stock_for_standalone_product_via_post_product_update(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'TEST-SKU-001',
            'nama_barang' => 'OXVA Pod Kit',
            'slug' => 'oxva-pod-kit',
            'harga_jual' => 300000,
            'harga_pokok' => 200000,
            'stok_aktual' => 8, // Sisa stok lokal setelah dipotong
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'nama_penerima' => 'Buyer Test',
            'email' => 'buyer@podgasm.id',
            'no_telp' => '08123456789',
            'invoice_number' => 'INV-TEST-001',
            'total_harga' => 600000,
            'metode_pembayaran' => 'midtrans_qris',
            'alamat_pengiriman' => 'Surabaya',
            'status' => 'paid'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 300000
        ]);

        // Mocking HTTP Olsera
        $baseUrl = config('services.olsera.base_url', 'https://api-open.olsera.co.id/api/open-api/v1/id');
        Http::fake([
            "{$baseUrl}/product?*" => Http::response([
                'status' => 200,
                'data' => [
                    [
                        'id' => 991234,
                        'name' => 'OXVA Pod Kit',
                        'sku' => 'TEST-SKU-001',
                        'stock_qty' => 10,
                        'variants' => []
                    ]
                ]
            ], 200),
            "{$baseUrl}/product/update" => Http::response([
                'status' => 200,
                'message' => 'Product updated successfully'
            ], 200)
        ]);

        $service = app(OlseraApiService::class);
        (new PushSalesOrderToOlseraJob($order->id))->handle($service);

        // Verifikasi request update dikirim ke Olsera dengan format resmi
        Http::assertSent(function ($request) use ($baseUrl) {
            if ($request->url() === "{$baseUrl}/product/update") {
                $data = $request->data();
                return $data['id'] === 991234 
                    && $data['to_store_id'] === '103944'
                    && $data['product']['stock_qty'] === 8;
            }
            return true;
        });
    }

    /**
     * Test 2: Pemotongan stok varian produk spesifik ke Olsera via POST /product/update
     */
    public function test_it_updates_olsera_variant_stock_via_post_product_update(): void
    {
        $parent = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'PARENT-SKU',
            'nama_barang' => 'Xlim Pro Multi Color',
            'slug' => 'xlim-pro-multi-color',
            'harga_jual' => 350000,
            'harga_pokok' => 250000,
            'stok_aktual' => 15,
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $variant = ProductVariant::create([
            'product_id' => $parent->id,
            'nama_varian' => 'Carbon Fiber',
            'kode_barang' => 'VAR-CARBON-01',
            'harga_jual' => 350000,
            'harga_pokok' => 250000,
            'stok_aktual' => 4, // Sisa stok varian lokal
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'nama_penerima' => 'Buyer Test',
            'email' => 'buyer@podgasm.id',
            'no_telp' => '08123456789',
            'invoice_number' => 'INV-TEST-002',
            'total_harga' => 350000,
            'metode_pembayaran' => 'midtrans_va',
            'alamat_pengiriman' => 'Surabaya',
            'status' => 'paid'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $parent->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 350000
        ]);

        $baseUrl = config('services.olsera.base_url', 'https://api-open.olsera.co.id/api/open-api/v1/id');
        Http::fake([
            "{$baseUrl}/product?*" => Http::response([
                'status' => 200,
                'data' => [
                    [
                        'id' => 881234,
                        'name' => 'Xlim Pro Multi Color',
                        'sku' => 'PARENT-SKU',
                        'variants' => [
                            [
                                'id' => 55001,
                                'name' => 'Carbon Fiber',
                                'sku' => 'VAR-CARBON-01',
                                'stock_qty' => 5
                            ]
                        ]
                    ]
                ]
            ], 200),
            "{$baseUrl}/product/update" => Http::response([
                'status' => 200,
                'message' => 'Variant updated successfully'
            ], 200)
        ]);

        $service = app(OlseraApiService::class);
        (new PushSalesOrderToOlseraJob($order->id))->handle($service);

        Http::assertSent(function ($request) use ($baseUrl) {
            if ($request->url() === "{$baseUrl}/product/update") {
                $data = $request->data();
                return $data['id'] === 881234 
                    && $data['to_store_id'] === '103944'
                    && isset($data['product']['variants'][0])
                    && $data['product']['variants'][0]['id'] === 55001
                    && $data['product']['variants'][0]['stock_qty'] === 4;
            }
            return true;
        });
    }

    /**
     * Test 3: Anti-Silent-Failure: Jika server Olsera menolak / error, lempar Exception agar Queue Worker me-retry
     */
    public function test_it_throws_exception_when_olsera_api_rejects_update_to_trigger_queue_retry(): void
    {
        $this->expectException(\RuntimeException::class);

        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'FAIL-SKU-001',
            'nama_barang' => 'Item Error Test',
            'slug' => 'item-error-test',
            'harga_jual' => 100000,
            'harga_pokok' => 80000,
            'stok_aktual' => 5,
            'lead_time' => 1,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'nama_penerima' => 'Buyer Fail',
            'email' => 'buyer@podgasm.id',
            'no_telp' => '08123456789',
            'invoice_number' => 'INV-TEST-FAIL',
            'total_harga' => 100000,
            'metode_pembayaran' => 'midtrans_qris',
            'alamat_pengiriman' => 'Surabaya',
            'status' => 'paid'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100000
        ]);

        $baseUrl = config('services.olsera.base_url', 'https://api-open.olsera.co.id/api/open-api/v1/id');
        Http::fake([
            "{$baseUrl}/product?*" => Http::response([
                'status' => 200,
                'data' => [
                    [
                        'id' => 771234,
                        'name' => 'Item Error Test',
                        'sku' => 'FAIL-SKU-001',
                        'stock_qty' => 6,
                        'variants' => []
                    ]
                ]
            ], 200),
            // Olsera merespon dengan error HTTP 500
            "{$baseUrl}/product/update" => Http::response([
                'error' => 'Internal Olsera Server Error'
            ], 500)
        ]);

        $service = app(OlseraApiService::class);
        // Harus melempar exception, tidak boleh lolos diam-diam (zero silent failure)
        (new PushSalesOrderToOlseraJob($order->id))->handle($service);
    }

    /**
     * Test 4: Memastikan Transaksi E-Commerce resmi dibuat di Olsera POS dan difinalisasi ke Status Z
     * agar otomatis tercatat dalam Laporan Penjualan Harian & Laporan Penutupan Penjualan POS Kasir
     */
    public function test_it_creates_pos_sales_order_and_finalizes_with_status_z_for_daily_closing(): void
    {
        $product = Product::create([
            'category_id'    => $this->category->id,
            'kode_barang'    => 'CLOSING-SKU-001',
            'nama_barang'    => 'Closing Report Device',
            'slug'           => 'closing-report-device',
            'harga_jual'     => 500000,
            'harga_pokok'    => 350000,
            'stok_aktual'    => 19,
            'lead_time'      => 3,
            'nilai_ss'       => 0,
            'rata_penjualan' => 0
        ]);

        $order = Order::create([
            'user_id'           => $this->user->id,
            'nama_penerima'     => 'Closing Customer',
            'email'             => 'closing@podgasm.id',
            'no_telp'           => '081299998888',
            'invoice_number'    => 'INV-CLOSING-001',
            'total_harga'       => 500000,
            'metode_pembayaran' => 'midtrans_qris',
            'alamat_pengiriman' => 'Surabaya',
            'status'            => 'paid'
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => 500000
        ]);

        $baseUrl = config('services.olsera.base_url', 'https://api-open.olsera.co.id/api/open-api/v1/id');
        Http::fake([
            "{$baseUrl}/order/openorder" => Http::response([
                'status' => 200,
                'data'   => ['id' => 88001, 'order_no' => 'ORD-POS-88001']
            ], 200),
            "{$baseUrl}/order/openorder/additem" => Http::response([
                'status' => 200,
                'message' => 'Item added successfully'
            ], 200),
            "{$baseUrl}/order/openorder/updatepaymentstatus" => Http::response([
                'status' => 200,
                'message' => 'Payment updated'
            ], 200),
            "{$baseUrl}/order/openorder/updatestatus" => Http::response([
                'status' => 200,
                'message' => 'Order marked as completed (Status Z)'
            ], 200),
            "{$baseUrl}/product?*" => Http::response([
                'status' => 200,
                'data'   => [
                    [
                        'id'        => 661234,
                        'name'      => 'Closing Report Device',
                        'sku'       => 'CLOSING-SKU-001',
                        'stock_qty' => 20,
                        'variants'  => []
                    ]
                ]
            ], 200),
            "{$baseUrl}/product/update" => Http::response([
                'status'  => 200,
                'message' => 'Stock updated'
            ], 200),
        ]);

        $service = app(OlseraApiService::class);
        $result = $service->pushSalesOrder($order);

        $this->assertTrue($result['success']);
        $this->assertEquals(88001, $result['olsera_order_id']);

        // 1. Verifikasi request pembuatan Open Order ke Olsera
        Http::assertSent(function ($request) use ($baseUrl) {
            return $request->url() === "{$baseUrl}/order/openorder"
                && $request['outlet_id'] === '103944';
        });

        // 2. Verifikasi request penambahan item ke Order
        Http::assertSent(function ($request) use ($baseUrl) {
            return $request->url() === "{$baseUrl}/order/openorder/additem"
                && $request['order_id'] === '88001'
                && $request['item_products'] === '661234|0'
                && (int)$request['item_qty'] === 1;
        });

        // 3. Verifikasi request update status pembayaran ke Paid (1)
        Http::assertSent(function ($request) use ($baseUrl) {
            return $request->url() === "{$baseUrl}/order/openorder/updatepaymentstatus"
                && $request['order_id'] === '88001'
                && $request['status'] === '1';
        });

        // 4. Verifikasi request update status order ke 'Z' (Selesai -> Masuk Laporan Penutupan Penjualan POS Kasir)
        Http::assertSent(function ($request) use ($baseUrl) {
            return $request->url() === "{$baseUrl}/order/openorder/updatestatus"
                && $request['order_id'] === '88001'
                && $request['status'] === 'Z';
        });
    }
}
