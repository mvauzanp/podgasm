<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CloseSystemCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Create parent category "Close System"
        $parent = Category::create([
            'nama_kategori' => 'Close System',
            'slug'          => 'close-system',
            'parent_id'     => null,
        ]);

        // Create sub-category "Disposable"
        Category::create([
            'nama_kategori' => 'Disposable',
            'slug'          => 'disposable',
            'parent_id'     => $parent->id,
        ]);

        // Create sub-category "Pod Close System"
        Category::create([
            'nama_kategori' => 'Pod Close System',
            'slug'          => 'pod-close-system',
            'parent_id'     => $parent->id,
        ]);

        echo "Close System (ID: {$parent->id}) + 2 sub-categories created.\n";
    }
}
