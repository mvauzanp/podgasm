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
        // ✅ PERBAIKAN #6: Add description and kode_barang fields for product detail page
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'kode_barang')) {
                $table->string('kode_barang')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('nama_barang');
            }
            if (!Schema::hasColumn('products', 'harga_pokok')) {
                $table->bigInteger('harga_pokok')->default(0)->after('harga_jual');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'kode_barang')) {
                $table->dropColumn('kode_barang');
            }
            if (Schema::hasColumn('products', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('products', 'harga_pokok')) {
                $table->dropColumn('harga_pokok');
            }
        });
    }
};
