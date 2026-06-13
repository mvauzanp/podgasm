<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('nama_varian'); // e.g. "Nic 3mg", "Nic 6mg"
            $table->string('kode_barang')->nullable()->unique(); // SKU khusus varian
            $table->bigInteger('harga_jual')->nullable(); // null berarti pakai harga induk
            $table->integer('stok_aktual')->default(0);
            
            // Kolom Safety Stock per varian
            $table->integer('nilai_ss')->default(0);
            
            // Kolom Expiry & Cukai khusus varian (opsional)
            $table->date('tgl_expired')->nullable();
            $table->date('tgl_cukai')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Dukung soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
