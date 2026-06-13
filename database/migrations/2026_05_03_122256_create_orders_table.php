<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            // Diganti ke bigInteger agar lebih aman untuk angka nominal besar
            $table->bigInteger('total_harga'); 
            $table->string('status')->default('pending'); // pending, paid, shipped, completed
            $table->text('alamat_pengiriman');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Urutan hapus harus order_items dulu karena dia punya foreign key ke orders
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};