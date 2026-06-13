<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan foreign key constraint
        Schema::disableForeignKeyConstraints();

        // ✅ PERBAIKAN #5: Hapus user lama agar tidak duplikasi
        User::truncate();

        // Nyalakan kembali foreign key constraint
        Schema::enableForeignKeyConstraints();

        // Admin user
        User::create([
            'name' => 'Admin Gudang Podgasm',
            'email' => 'admin@podgasm.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        // ✅ PERBAIKAN #5: Seed customer dan branch users
        $this->call(CustomerSeeder::class);
        $this->call(BranchSeeder::class);
    }
}
