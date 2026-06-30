<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $otherUser;
    private $category;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic models for testing
        $this->user = User::create([
            'name' => 'John Customer',
            'email' => 'john@customer.com',
            'password' => bcrypt('password123'),
            'role' => 'customer'
        ]);

        $this->otherUser = User::create([
            'name' => 'Other Customer',
            'email' => 'other@customer.com',
            'password' => bcrypt('password123'),
            'role' => 'customer'
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'E-Liquid',
            'slug' => 'e-liquid'
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'EL-001',
            'nama_barang' => 'Podgasm Strawberry',
            'slug' => 'podgasm-strawberry',
            'harga_jual' => 100000,
            'harga_pokok' => 70000,
            'stok_aktual' => 50,
            'nilai_ss' => 5,
            'lead_time' => 3,
            'rata_penjualan' => 2
        ]);
    }

    public function test_guest_cannot_access_cart()
    {
        $response = $this->get(route('cart.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_add_product_to_cart()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('cart.add', $this->product->id), [
            'quantity' => 2
        ]);

        $response->assertSessionHas('success');
        
        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertNotNull($cart);
        $this->assertEquals(1, $cart->items()->count());
        $this->assertEquals(2, $cart->items()->first()->quantity);
    }

    public function test_user_cannot_add_out_of_stock_product()
    {
        $this->actingAs($this->user);

        $outOfStockProduct = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'EL-002',
            'nama_barang' => 'Empty Liquid',
            'slug' => 'empty-liquid',
            'harga_jual' => 100000,
            'harga_pokok' => 70000,
            'stok_aktual' => 0
        ]);

        $response = $this->post(route('cart.add', $outOfStockProduct->id), [
            'quantity' => 1
        ]);

        $response->assertSessionHas('error', 'Produk tidak tersedia (stok habis)');
        
        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertNull($cart);
    }

    public function test_user_cannot_exceed_max_quantity()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('cart.add', $this->product->id), [
            'quantity' => 1001
        ]);

        $response->assertSessionHas('error', 'Jumlah harus antara 1-1000 unit');
    }

    public function test_user_can_update_cart_quantity()
    {
        $this->actingAs($this->user);

        // Setup existing cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->harga_jual
        ]);

        $response = $this->patch(route('cart.update'), [
            'id' => $item->id,
            'quantity' => 5
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(5, $item->refresh()->quantity);
    }

    public function test_user_cannot_update_cart_exceeding_stock()
    {
        $this->actingAs($this->user);

        // Setup existing cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->harga_jual
        ]);

        $response = $this->patch(route('cart.update'), [
            'id' => $item->id,
            'quantity' => 51 // Product stock is 50
        ]);

        $response->assertSessionHas('error', 'Stok tidak cukup. Tersedia: 50 unit');
        $this->assertEquals(2, $item->refresh()->quantity);
    }

    public function test_user_can_remove_item_from_cart()
    {
        $this->actingAs($this->user);

        // Setup existing cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->harga_jual
        ]);

        $response = $this->delete(route('cart.remove'), [
            'id' => $item->id
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(0, CartItem::count());
    }

    public function test_user_cannot_remove_another_users_cart_item()
    {
        // Add item to User 1's cart
        $cart1 = Cart::create(['user_id' => $this->user->id]);
        $item = CartItem::create([
            'cart_id' => $cart1->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->harga_jual
        ]);

        // Login as User 2
        $this->actingAs($this->otherUser);

        $response = $this->delete(route('cart.remove'), [
            'id' => $item->id
        ]);

        $response->assertSessionHas('error', 'Anda tidak berhak menghapus item ini');
        $this->assertEquals(1, CartItem::count());
    }
}
