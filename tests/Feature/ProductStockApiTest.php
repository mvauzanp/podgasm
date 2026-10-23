<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockApiTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'nama_kategori' => 'Pods',
            'slug' => 'pods'
        ]);
    }

    public function test_it_returns_live_stock_for_standalone_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'TEST-POD-01',
            'nama_barang' => 'Pod Kit Starter',
            'slug' => 'pod-kit-starter',
            'harga_jual' => 200000,
            'harga_pokok' => 150000,
            'stok_aktual' => 14,
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $response = $this->getJson(route('public.product.stock', $product->id));

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'product_id' => $product->id,
                     'has_variants' => false,
                     'total_stock' => 14,
                     'stock' => 14,
                     'is_out_of_stock' => false,
                 ]);
    }

    public function test_it_returns_live_stock_for_product_with_variants(): void
    {
        $parent = Product::create([
            'category_id' => $this->category->id,
            'kode_barang' => 'VAR-POD-01',
            'nama_barang' => 'Pod Kit Multi Color',
            'slug' => 'pod-kit-multi-color',
            'harga_jual' => 250000,
            'harga_pokok' => 180000,
            'stok_aktual' => 25,
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $v1 = ProductVariant::create([
            'product_id' => $parent->id,
            'nama_varian' => 'Red',
            'kode_barang' => 'VAR-RED',
            'harga_jual' => 250000,
            'harga_pokok' => 180000,
            'stok_aktual' => 10,
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $v2 = ProductVariant::create([
            'product_id' => $parent->id,
            'nama_varian' => 'Blue',
            'kode_barang' => 'VAR-BLU',
            'harga_jual' => 250000,
            'harga_pokok' => 180000,
            'stok_aktual' => 0,
            'lead_time' => 3,
            'nilai_ss' => 0,
            'rata_penjualan' => 0
        ]);

        $response = $this->getJson(route('public.product.stock', $parent->id));

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'product_id' => $parent->id,
                     'has_variants' => true,
                     'total_stock' => 10,
                     'is_out_of_stock' => false,
                 ])
                 ->assertJsonFragment([
                     'id' => $v1->id,
                     'name' => 'Red',
                     'stock' => 10,
                     'is_out_of_stock' => false,
                 ])
                 ->assertJsonFragment([
                     'id' => $v2->id,
                     'name' => 'Blue',
                     'stock' => 0,
                     'is_out_of_stock' => true,
                 ]);
    }

    public function test_it_returns_404_for_non_existent_product(): void
    {
        $response = $this->getJson(route('public.product.stock', 999999));
        $response->assertStatus(404);
    }
}
