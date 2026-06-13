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
        // 1. Create b2b_price_settings table
        Schema::create('b2b_price_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->integer('min_qty')->default(1);
            $table->enum('discount_type', ['percentage', 'nominal']);
            $table->decimal('discount_value', 15, 2);
            $table->timestamps();
        });

        // 2. Add keterangan_admin to stock_requests table
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->text('keterangan_admin')->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_price_settings');

        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn('keterangan_admin');
        });
    }
};
