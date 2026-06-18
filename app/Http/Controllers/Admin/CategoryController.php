<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')->with('children')->latest()->paginate(10);
        $allCategories = Category::whereNull('parent_id')->get(); // for parent dropdown
        return view('pages.admin.category.index', compact('categories', 'allCategories'));
    }

    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('pages.admin.category.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:categories,nama_kategori',
            'parent_id'     => 'nullable|exists:categories,id',
        ]);

        Category::create([
            'nama_kategori' => $request->nama_kategori,
            'slug'          => Str::slug($request->nama_kategori),
            'parent_id'     => $request->parent_id,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('pages.admin.category.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:categories,nama_kategori,' . $category->id,
            'parent_id'     => 'nullable|exists:categories,id',
        ]);

        $category->update([
            'nama_kategori' => $request->nama_kategori,
            'slug'          => Str::slug($request->nama_kategori),
            'parent_id'     => $request->parent_id,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus (Soft Delete).');
    }
}
