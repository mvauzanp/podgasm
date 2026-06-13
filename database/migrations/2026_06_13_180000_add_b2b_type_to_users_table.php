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
        Schema::table('users', function (Blueprint $table) {
            $table->string('b2b_type')->nullable()->after('role'); // 'reseller' or 'branch'
        });

        // Set existing branch users as 'reseller'
        DB::table('users')
            ->where('role', 'branch')
            ->update(['b2b_type' => 'reseller']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('b2b_type');
        });
    }
};
