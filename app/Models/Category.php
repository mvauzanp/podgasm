<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nama_kategori', 'slug', 'parent_id'];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public const EXCLUDED_PUBLIC_SLUGS = [
        'soft-drink',
        'soft-drinks',
        'minuman',
    ];

    /**
     * Scope untuk kategori yang boleh tampil di frontend publik
     */
    public function scopePublicVisible($query)
    {
        return $query->whereNotIn('slug', self::EXCLUDED_PUBLIC_SLUGS)
            ->where(function($q) {
                $q->where('nama_kategori', 'NOT LIKE', '%SOFT DRINK%')
                  ->where('nama_kategori', 'NOT LIKE', '%MINUMAN%');
            });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
