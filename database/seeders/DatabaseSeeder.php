<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\OlseraApiService;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Essential Users & Roles (Admin HQ, Branch, Customer)
        $this->call(UserSeeder::class);

        // 2. Sync Master Data Inventori Langsung Dari Olsera POS API
        $olsera = app(OlseraApiService::class);
        $olsera->syncCatalogAndStock();
    }
}