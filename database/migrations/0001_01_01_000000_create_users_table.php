<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            // Role: admin (gudang pusat), branch (cabang B2B), customer (ritel B2C)
            $table->enum('role', ['admin', 'branch', 'customer'])->default('customer');
            $table->rememberToken();
            $table->timestamps();
        });
    }
};
