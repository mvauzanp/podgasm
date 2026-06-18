<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'nama_varian',
        'kode_barang',
        'harga_jual',
        'harga_pokok',
        'stok_aktual',
        'nilai_ss',
        'lead_time',
        'rata_penjualan',
        'tgl_expired',
        'tgl_cukai',
        'gambar',
    ];

    // Relasi ke Produk Induk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Ambil harga jual (fallback ke harga jual produk induk jika null)
    public function getHargaJualActualAttribute()
    {
        return $this->harga_jual ?? $this->product->harga_jual;
    }

    // Ambil URL gambar varian (fallback ke gambar utama produk jika kosong)
    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            if (str_starts_with($this->gambar, 'http')) {
                return $this->gambar;
            }
            return asset('storage/' . $this->gambar);
        }
        return $this->product->gambar_url;
    }

    public function b2bPriceSettings()
    {
        return $this->hasMany(B2BPriceSetting::class, 'product_variant_id');
    }

    public function getB2bPrice($qty = 1)
    {
        $originalPrice = $this->harga_jual_actual;
        
        $setting = B2BPriceSetting::where('product_id', $this->product_id)
            ->where(function($q) {
                $q->where('product_variant_id', $this->id)
                  ->orWhereNull('product_variant_id');
            })
            ->where('min_qty', '<=', $qty)
            ->orderBy('min_qty', 'desc')
            ->orderByRaw('product_variant_id DESC')
            ->first();

        if ($setting) {
            if ($setting->discount_type === 'percentage') {
                return $originalPrice - ($originalPrice * ($setting->discount_value / 100));
            } else {
                return $originalPrice - $setting->discount_value;
            }
        }

        return $originalPrice;
    }

    // Relasi ke Branch Stocks
    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class, 'product_variant_id');
    }

    // Relasi ke Cart Items
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'product_variant_id');
    }

    // Relasi ke Order Items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }

    // Relasi ke Stock Requests
    public function stockRequests()
    {
        return $this->hasMany(StockRequest::class, 'product_variant_id');
    }
}
