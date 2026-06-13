<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan foreign key constraint
        Schema::disableForeignKeyConstraints();

        // Hapus branch lama untuk menghindari duplikasi
        User::where('role', 'branch')->delete();

        // Nyalakan kembali foreign key constraint
        Schema::enableForeignKeyConstraints();

        // Data branch gudang demo
        $branches = [
            [
                'name' => 'Manager Gudang Jakarta',
                'email' => 'branch.jakarta@podgasm.com',
                'password' => bcrypt('password123'),
                'role' => 'branch'
            ],
            [
                'name' => 'Manager Gudang Surabaya',
                'email' => 'branch.surabaya@podgasm.com',
                'password' => bcrypt('password123'),
                'role' => 'branch'
            ],
            [
                'name' => 'Manager Gudang Bandung',
                'email' => 'branch.bandung@podgasm.com',
                'password' => bcrypt('password123'),
                'role' => 'branch'
            ],
            [
                'name' => 'Manager Gudang Medan',
                'email' => 'branch.medan@podgasm.com',
                'password' => bcrypt('password123'),
                'role' => 'branch'
            ],
            [
                'name' => 'Manager Gudang Yogyakarta',
                'email' => 'branch.yogyakarta@podgasm.com',
                'password' => bcrypt('password123'),
                'role' => 'branch'
            ],
        ];

        foreach ($branches as $branch) {
            User::create($branch);
        }
    }
}
