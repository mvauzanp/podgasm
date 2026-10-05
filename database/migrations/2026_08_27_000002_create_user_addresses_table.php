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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('label', 50)->default('Rumah'); // Rumah, Kantor, Toko, dll
            $table->string('recipient_name', 150);
            $table->string('phone', 30);
            $table->text('address'); // Alamat jalan, nomor rumah, RT/RW
            $table->string('biteship_area_id', 100); // Area ID resmi Biteship
            $table->string('biteship_area_name', 255); // e.g. "Kebayoran Baru, Jakarta Selatan, DKI Jakarta"
            $table->string('postal_code', 20)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
