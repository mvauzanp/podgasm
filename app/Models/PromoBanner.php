<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoBanner extends Model
{
    use HasFactory;

    protected $table = 'promo_banners';

    protected $fillable = [
        'judul',
        'deskripsi',
        'gambar',
        'link_url',
        'is_active',
        'order',
    ];

    // Helper accessor for banner image url
    public function getGambarUrlAttribute()
    {
        return asset('storage/' . $this->gambar);
    }
}
