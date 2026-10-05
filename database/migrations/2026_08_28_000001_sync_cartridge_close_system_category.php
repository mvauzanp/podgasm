<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Category;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat kategori CARTRIDGE CLOSE SYSTEM jika belum ada
        $category = Category::firstOrCreate(
            ['slug' => 'cartridge-close-system'],
            ['nama_kategori' => 'CARTRIDGE CLOSE SYSTEM']
        );

        // 2. Hubungkan produk RELX CARTRIDGE SINGLE PODS ke kategori ini
        Product::where('nama_barang', 'like', '%RELX CARTRIDGE SINGLE PODS%')
            ->update(['category_id' => $category->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
