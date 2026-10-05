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
        Schema::create('sales_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->date('date');
            $table->integer('qty_sold')->default(0);
            $table->string('source', 50)->default('olsera_pos'); // 'olsera_pos' or 'ecommerce'
            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id', 'date', 'source'], 'unique_sku_date_source_sales');
            $table->index(['date', 'product_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_histories');
    }
};
