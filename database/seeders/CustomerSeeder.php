<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan foreign key constraint
        Schema::disableForeignKeyConstraints();

        // Hapus customer lama untuk menghindari duplikasi
        User::where('role', 'customer')->delete();

        // Nyalakan kembali foreign key constraint
        Schema::enableForeignKeyConstraints();

        // Data customer demo
        $customers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad.wijaya@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Rina Puspita',
                'email' => 'rina.puspita@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Doni Hermawan',
                'email' => 'doni.hermawan@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Lisna Dwiyanti',
                'email' => 'lisna.dwiyanti@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Rendra Pratama',
                'email' => 'rendra.pratama@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Yuki Tanaka',
                'email' => 'yuki.tanaka@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Hendra Kusuma',
                'email' => 'hendra.kusuma@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
            [
                'name' => 'Maria Susanti',
                'email' => 'maria.susanti@email.com',
                'password' => bcrypt('password123'),
                'role' => 'customer'
            ],
        ];

        foreach ($customers as $customer) {
            User::create($customer);
        }
    }
}
