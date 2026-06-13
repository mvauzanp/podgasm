<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('nama_penerima')->after('user_id');
            $table->string('email')->after('nama_penerima');
            $table->string('no_telp')->after('email');
            $table->string('metode_pembayaran')->after('total_harga'); // QRIS atau Transfer BCA
        });
    }
};
