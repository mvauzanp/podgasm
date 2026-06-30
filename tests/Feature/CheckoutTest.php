<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Services\OrderService;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $category;
    private $product;
    private $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);

        $this->user = User::create([
            'name' => 'Jane Customer',
            'email' => 'jane@customer.com',
            'password' => bcrypt('password123'),
            'role' => 'customer'
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Pods',
            'slug' => 'pods'
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'PD-001',
            'nama_barang' => 'Podgasm System V2',
            'slug' => 'podgasm-system-v2',
            'harga_jual' => 300000,
            'harga_pokok' => 200000,
            'stok_aktual' => 20,
            'nilai_ss' => 2,
            'lead_time' => 3,
            'rata_penjualan' => 1
        ]);
    }

    public function test_checkout_creates_order_without_decrementing_stock()
    {
        $this->actingAs($this->user);

        // Put item in cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->harga_jual
        ]);

        // Run process checkout
        $checkoutData = [
            'nama_penerima' => 'Jane Doe',
            'email' => 'jane@customer.com',
            'no_telp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Menteng Indah No 10',
            'metode_pembayaran' => 'transfer',
            'ongkir' => 15000,
            'kurir' => 'jne',
            'layanan' => 'REG'
        ];

        $response = $this->post(route('cart.processCheckout'), $checkoutData);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('order.show', $order->id));

        // Check stock is NOT decremented yet
        $this->assertEquals(20, $this->product->refresh()->stok_aktual);
        $this->assertEquals(OrderStatus::PENDING_PAYMENT->value, $order->status);
    }

    public function test_confirm_payment_decrements_stock()
    {
        $this->actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->harga_jual
        ]);

        $checkoutData = [
            'nama_penerima' => 'Jane Doe',
            'email' => 'jane@customer.com',
            'no_telp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Menteng Indah No 10',
            'metode_pembayaran' => 'transfer',
            'ongkir' => 15000,
            'kurir' => 'jne',
            'layanan' => 'REG'
        ];

        $order = $this->orderService->processCheckout($checkoutData, $this->user);

        // Confirm payment
        $success = $this->orderService->confirmPayment($order);
        $this->assertTrue($success);

        // Stock MUST be decremented now (20 - 2 = 18)
        $this->assertEquals(18, $this->product->refresh()->stok_aktual);
        $this->assertEquals(OrderStatus::PAID->value, $order->refresh()->status);
    }

    public function test_cancel_order_does_not_change_stock_and_marks_cancelled()
    {
        $this->actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
            'price' => $this->product->harga_jual
        ]);

        $checkoutData = [
            'nama_penerima' => 'Jane Doe',
            'email' => 'jane@customer.com',
            'no_telp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Menteng Indah No 10',
            'metode_pembayaran' => 'transfer',
            'ongkir' => 15000,
            'kurir' => 'jne',
            'layanan' => 'REG'
        ];

        $order = $this->orderService->processCheckout($checkoutData, $this->user);

        // Cancel order
        $success = $this->orderService->cancelOrder($order);
        $this->assertTrue($success);

        // Stock must remain 20
        $this->assertEquals(20, $this->product->refresh()->stok_aktual);
        $this->assertEquals(OrderStatus::CANCELLED->value, $order->refresh()->status);
    }

    public function test_checkout_with_voucher_applies_discount()
    {
        $this->actingAs($this->user);

        $voucher = Voucher::create([
            'code' => 'PODGASM10K',
            'type' => 'nominal',
            'value' => 10000,
            'min_purchase' => 100000,
            'quota' => 10,
            'used_count' => 0,
            'is_active' => true
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->harga_jual // 300,000
        ]);

        // Process checkout with voucher
        $checkoutData = [
            'nama_penerima' => 'Jane Doe',
            'email' => 'jane@customer.com',
            'no_telp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Menteng Indah No 10',
            'metode_pembayaran' => 'transfer',
            'ongkir' => 15000,
            'kurir' => 'jne',
            'layanan' => 'REG'
        ];

        $order = $this->orderService->processCheckout($checkoutData, $this->user, 'PODGASM10K');

        // Total price should be: (300,000 - 10,000) + 15,000 = 305,000
        $this->assertEquals(305000, $order->total_harga);
        $this->assertEquals(10000, $order->voucher_discount);
        $this->assertEquals('PODGASM10K', $order->voucher_code);

        // Check voucher quota decremented
        $this->assertEquals(9, $voucher->refresh()->quota);
    }

    public function test_order_cancellation_refunds_voucher_quota()
    {
        $this->actingAs($this->user);

        $voucher = Voucher::create([
            'code' => 'REFUND10K',
            'type' => 'nominal',
            'value' => 10000,
            'min_purchase' => 100000,
            'quota' => 5,
            'used_count' => 0,
            'is_active' => true
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->harga_jual
        ]);

        $checkoutData = [
            'nama_penerima' => 'Jane Doe',
            'email' => 'jane@customer.com',
            'no_telp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Menteng Indah No 10',
            'metode_pembayaran' => 'transfer',
            'ongkir' => 15000,
            'kurir' => 'jne',
            'layanan' => 'REG'
        ];

        $order = $this->orderService->processCheckout($checkoutData, $this->user, 'REFUND10K');
        $this->assertEquals(4, $voucher->refresh()->quota);

        // Cancel order
        $this->orderService->cancelOrder($order);

        // Quota should refund (back to 5)
        $this->assertEquals(5, $voucher->refresh()->quota);
    }
}
