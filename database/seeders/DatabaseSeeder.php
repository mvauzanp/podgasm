<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Tambahkan ini
use Illuminate\Support\Facades\Schema; // Tambahkan ini

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- PROTEKSI ANTI-DUPLICATE ---
        // Matikan pengecekan foreign key agar bisa mengosongkan tabel
        Schema::disableForeignKeyConstraints();
        
        // Hapus semua data lama di tabel categories agar tidak bentrok
        Category::truncate();

        // --- 1. LIQUID ---
        $liquid = Category::create(['nama_kategori' => 'Liquid', 'slug' => 'liquid']);
        Category::create(['nama_kategori' => 'Freebase', 'slug' => 'freebase', 'parent_id' => $liquid->id]);
        Category::create(['nama_kategori' => 'Saltnic', 'slug' => 'saltnic', 'parent_id' => $liquid->id]);

        // --- 2. POD ---
        $pod = Category::create(['nama_kategori' => 'POD', 'slug' => 'pod']);
        Category::create(['nama_kategori' => 'AIO', 'slug' => 'aio', 'parent_id' => $pod->id]);
        Category::create(['nama_kategori' => 'POD System', 'slug' => 'pod-system', 'parent_id' => $pod->id]);

        // --- 3. MOD ---
        $mod = Category::create(['nama_kategori' => 'MOD', 'slug' => 'mod']);
        Category::create(['nama_kategori' => 'Electrical MOD', 'slug' => 'electrical-mod', 'parent_id' => $mod->id]);
        Category::create(['nama_kategori' => 'Mechanical MOD', 'slug' => 'mechanical-mod', 'parent_id' => $mod->id]);

        // --- 4. ATOMIZER ---
        $atom = Category::create(['nama_kategori' => 'Atomizer', 'slug' => 'atomizer']);
        $atomSubs = ['RDA', 'RTA', 'RDTA', 'RBA', 'Coil Prebuild', 'Coil & Cartridge', 'Wire'];
        foreach ($atomSubs as $sub) {
            Category::create([
                'nama_kategori' => $sub,
                'slug' => Str::slug($sub),
                'parent_id' => $atom->id
            ]);
        }

        // --- 5. ACCESSORIES ---
        $acc = Category::create(['nama_kategori' => 'Accessories', 'slug' => 'accessories']);
        $accSubs = ['Battery Cell', 'Charger', 'Cotton', 'Driptip', 'Toolkit', 'Other'];
        foreach ($accSubs as $sub) {
            Category::create([
                'nama_kategori' => $sub,
                'slug' => Str::slug($sub),
                'parent_id' => $acc->id
            ]);
        }

        // Hidupkan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();

        // --- SEED USERS (ADMIN, BRANCH, CUSTOMER) ---
        $this->call(UserSeeder::class);

        // ✅ PERBAIKAN #5: Seed produk dan transaksi
        $this->call(ProductSeeder::class);
        $this->call(TransactionSeeder::class);
    }
}