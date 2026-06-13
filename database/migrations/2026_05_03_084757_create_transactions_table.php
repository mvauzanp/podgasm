<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->integer('jumlah');
            $table->decimal('total_harga', 12, 2); // <--- PASTIKAN KOLOM INI ADA
            $table->enum('jenis', ['B2B', 'B2C']);
            $table->date('tanggal');
            $table->timestamps();
        });
    }
};
