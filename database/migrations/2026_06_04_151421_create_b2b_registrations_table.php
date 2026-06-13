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
        Schema::create('b2b_registrations', function (Blueprint $table) {
            $table->id();
            
            // User Foreign Key
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Owner Data
            $table->string('owner_name');
            $table->string('store_name');
            $table->text('address');
            $table->string('phone');
            $table->string('email')->unique();
            
            // Documents
            $table->string('ktp_file')->nullable(); // path to uploaded KTP
            $table->string('storefront_photo')->nullable(); // path to uploaded photo
            
            // Status & Admin Review
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable(); // admin user_id
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_registrations');
    }
};
