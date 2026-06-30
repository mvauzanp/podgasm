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
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('ongkir')->default(0)->after('alamat_pengiriman');
            $table->string('kurir')->nullable()->after('ongkir');
            $table->string('layanan')->nullable()->after('kurir');
            $table->string('resi')->nullable()->after('layanan');
            $table->string('biteship_order_id')->nullable()->after('resi');
            $table->string('destination_area_id')->nullable()->after('biteship_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'ongkir',
                'kurir',
                'layanan',
                'resi',
                'biteship_order_id',
                'destination_area_id'
            ]);
        });
    }
};
