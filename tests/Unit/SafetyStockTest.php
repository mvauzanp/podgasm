<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SafetyStockTest extends TestCase
{
    use RefreshDatabase;

    private $category;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'nama_kategori' => 'Atomizers',
            'slug' => 'atomizers'
        ]);

        // Simple product with lead_time = 4 days
        $this->product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'AT-001',
            'nama_barang' => 'RDA Atomizer X',
            'slug' => 'rda-atomizer-x',
            'harga_jual' => 150000,
            'harga_pokok' => 100000,
            'stok_aktual' => 100,
            'nilai_ss' => 0,
            'lead_time' => 4,
            'rata_penjualan' => 0
        ]);
    }

    public function test_safety_stock_recalculation_command_executes_successfully()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@email.com',
            'password' => bcrypt('password'),
            'role' => 'customer'
        ]);

        // 1. Create some transactions/orders in the last 30 days to generate sales data
        $order = Order::create([
            'user_id' => $user->id,
            'nama_penerima' => 'Buyer A',
            'email' => 'buyer@test.com',
            'no_telp' => '081299999999',
            'invoice_number' => 'INV-TEST-001',
            'total_harga' => 150000,
            'metode_pembayaran' => 'cash',
            'alamat_pengiriman' => 'Test Street No 1',
            'status' => 'paid'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'price' => $this->product->harga_jual,
            'created_at' => now()->subDays(5) // within 30 days
        ]);

        // 2. Run the recalculate artisan command
        $exitCode = Artisan::call('safety-stock:recalculate');
        $this->assertEquals(0, $exitCode);

        // 3. Verify safety stock and average sales are calculated and updated in DB
        $this->product->refresh();

        // Average daily sales = 10 / 30 = 0.33
        $this->assertEquals(0.33, $this->product->rata_penjualan);

        // Z-score for class A/B/C: Since there is only one product, it contributes 100% of sales.
        // In the command, if sales cumulative percentage is <= 70% it is A, but if it is the only one, cumulative is 100% which falls into C.
        // Or if runningSum / grandTotal = 100%, it classified it.
        // Let's check what safety stock value was written. It should be non-zero since standard deviation of daily sales is positive.
        $this->assertGreaterThan(0, $this->product->nilai_ss);
        $this->assertEquals(4, $this->product->lead_time);
    }
}
