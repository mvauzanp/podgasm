<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('nama_barang');
            $table->string('slug')->unique(); // Penting untuk SEO/Katalog
            $table->bigInteger('harga_jual');
            $table->integer('stok_aktual')->default(0);
            
            // Kolom Safety Stock (Inti TA Anda)
            $table->integer('nilai_ss')->default(0);
            $table->integer('lead_time')->default(0); 
            $table->integer('rata_penjualan')->default(0);

            // Kolom Baru yang Anda tanyakan
            $table->boolean('is_promo')->default(false); // Untuk filter Terlaris/Promo
            $table->integer('diskon_persen')->nullable()->default(0);
            $table->string('gambar')->nullable(); // Menyimpan nama file gambar
            
            // Kolom Risiko (Dashboard Admin)
            $table->date('tgl_expired')->nullable();
            $table->date('tgl_cukai')->nullable();
            
            $table->timestamps();
        });
    }
};
