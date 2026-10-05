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
        // 1. Tambah branch_id ke transactions jika belum ada
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            }
        });

        // 2. Tambah index performa ke orders jika belum ada
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'orders_status_created_idx');
            $table->index(['metode_pembayaran', 'status'], 'orders_payment_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_idx');
            $table->dropIndex('orders_payment_status_idx');
        });
    }
};
