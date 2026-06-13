<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // ID Cabang
            $table->foreignId('product_id')->constrained('products'); // Foreign key ke products table
            $table->integer('stok_lokal')->default(0);
            $table->timestamps();
        });
    }
};
