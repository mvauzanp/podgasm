<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 
        'kode_barang',
        'nama_barang',
        'description', // ✅ PERBAIKAN #6
        'slug', 
        'harga_jual',
        'harga_pokok', // ✅ PERBAIKAN #6
        'stok_aktual', 
        'tgl_expired', 
        'tgl_cukai',
        'nilai_ss',
        'lead_time',
        'rata_penjualan',
        'is_promo',
        'diskon_persen',
        'gambar'
    ];

    // 🔗 Relasi ke kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function hasVariants()
    {
        return $this->variants()->exists();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockRequests()
    {
        return $this->hasMany(StockRequest::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function b2bPriceSettings()
    {
        return $this->hasMany(B2BPriceSetting::class);
    }

    public function restockItems()
    {
        return $this->hasMany(RestockItem::class);
    }

    public function getB2bPrice($qty = 1)
    {
        $setting = $this->b2bPriceSettings()
            ->whereNull('product_variant_id')
            ->where('min_qty', '<=', $qty)
            ->orderBy('min_qty', 'desc')
            ->first();

        if ($setting) {
            if ($setting->discount_type === 'percentage') {
                return $this->harga_jual - ($this->harga_jual * ($setting->discount_value / 100));
            } else {
                return $this->harga_jual - $setting->discount_value;
            }
        }

        return $this->harga_jual;
    }

    // 💰 Harga setelah diskon
    public function getHargaDiskonAttribute()
    {
        if ($this->is_promo) {
            return $this->harga_jual - ($this->harga_jual * ($this->diskon_persen / 100));
        }

        return $this->harga_jual;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($product) {
            if ($product->isForceDeleting()) {
                $product->variants()->forceDelete();
                $product->images()->forceDelete();
            } else {
                $product->variants()->delete();
            }
        });
        
        static::restoring(function ($product) {
            $product->variants()->restore();
        });
    }

    // 🖼️ Ambil URL gambar (biar blade lebih bersih)
    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            if (str_starts_with($this->gambar, 'http')) {
                return $this->gambar;
            }
            return asset('storage/' . $this->gambar);
        }

        return null; // atau bisa kasih default image
    }
}