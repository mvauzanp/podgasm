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
        if (Schema::hasTable('user_addresses')) {
            Schema::table('user_addresses', function (Blueprint $table) {
                if (!Schema::hasColumn('user_addresses', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('postal_code');
                }
                if (!Schema::hasColumn('user_addresses', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'destination_latitude')) {
                    $table->decimal('destination_latitude', 10, 8)->nullable()->after('destination_area_id');
                }
                if (!Schema::hasColumn('orders', 'destination_longitude')) {
                    $table->decimal('destination_longitude', 11, 8)->nullable()->after('destination_latitude');
                }
                if (!Schema::hasColumn('orders', 'courier_company')) {
                    $table->string('courier_company', 50)->nullable()->after('kurir');
                }
                if (!Schema::hasColumn('orders', 'courier_type')) {
                    $table->string('courier_type', 50)->nullable()->after('courier_company');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_addresses')) {
            Schema::table('user_addresses', function (Blueprint $table) {
                $table->dropColumn(['latitude', 'longitude']);
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn(['destination_latitude', 'destination_longitude', 'courier_company', 'courier_type']);
            });
        }
    }
};
