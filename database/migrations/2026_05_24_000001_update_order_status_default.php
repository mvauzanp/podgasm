<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update default status value dari 'pending' ke 'pending_payment'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status VARCHAR(255) DEFAULT 'pending_payment'");
        }
        
        // Update existing orders dengan status 'pending' menjadi 'pending_payment'
        DB::table('orders')
            ->where('status', 'pending')
            ->update(['status' => 'pending_payment']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ke status 'pending'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status VARCHAR(255) DEFAULT 'pending'");
        }
        
        DB::table('orders')
            ->where('status', 'pending_payment')
            ->update(['status' => 'pending']);
    }
};
