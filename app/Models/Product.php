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
        'weight',      // Berat dalam gram (Olsera POS & Biteship)
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

    /**
     * Scope untuk menyaring produk yang boleh tampil di frontend website
     * (Menyembunyikan kategori non-vape seperti Soft Drink / Minuman)
     */
    public function scopePublicVisible($query)
    {
        return $query->whereDoesntHave('category', function ($q) {
            $q->whereIn('slug', Category::EXCLUDED_PUBLIC_SLUGS)
              ->orWhere('nama_kategori', 'LIKE', '%SOFT DRINK%')
              ->orWhere('nama_kategori', 'LIKE', '%MINUMAN%');
        });
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function salesHistories()
    {
        return $this->hasMany(SalesHistory::class);
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

    /**
     * Kategori yang berhak mendapatkan diskon kuantitas bertingkat (Wholesale/Tier Discount)
     */
    public const TIER_DISCOUNT_CATEGORY_SLUGS = [
        'freebase-liquid',
        'freebase',
        'saltnic-liquid',
        'saltnic',
        'pod-device',
        'pod-close-system',
        'mod-device',
        'cartridge',
        'disposable-pod',
        'disposable',
        'aksesoris',
        'accessories',
        'cartridge-close-system',
    ];

    public const TIER_DISCOUNT_CATEGORY_NAMES = [
        'liquid freebase',
        'freebase liquid',
        'freebase',
        'liquid saltnic',
        'saltnic liquid',
        'saltnic',
        'pod device',
        'pod close system',
        'mod device',
        'cartridge',
        'disposable pod',
        'aksesoris',
        'accessories',
        'cartridge close system',
    ];

    /**
     * Cek apakah produk masuk kategori yang berhak diskon bertingkat (Hanya 9 Kategori Target)
     */
    public function isEligibleForTierDiscount(): bool
    {
        $cat = $this->category;
        if (!$cat) {
            return false;
        }

        $slug = strtolower(trim($cat->slug ?? ''));
        $name = strtolower(trim($cat->nama_kategori ?? ''));

        // Cek kesamaan slug langsung
        if (in_array($slug, self::TIER_DISCOUNT_CATEGORY_SLUGS, true)) {
            return true;
        }

        // Cek kesamaan nama kategori langsung
        foreach (self::TIER_DISCOUNT_CATEGORY_NAMES as $targetName) {
            if ($name === $targetName || str_contains($name, $targetName) || str_contains($targetName, $name)) {
                return true;
            }
        }

        // Cek kategori induknya (parent)
        if ($cat->parent) {
            $parentSlug = strtolower(trim($cat->parent->slug ?? ''));
            $parentName = strtolower(trim($cat->parent->nama_kategori ?? ''));

            if (in_array($parentSlug, self::TIER_DISCOUNT_CATEGORY_SLUGS, true)) {
                return true;
            }
            foreach (self::TIER_DISCOUNT_CATEGORY_NAMES as $targetName) {
                if ($parentName === $targetName || str_contains($parentName, $targetName) || str_contains($targetName, $parentName)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Hitung persentase potongan harga berdasarkan kuantitas
     * Beli 1 pcs = normal (0%)
     * Beli 2-4 pcs = 2%
     * Beli 5-6 pcs (5-7) = 5%
     * Beli 7-9 pcs = 8%
     * Beli 10+ pcs = 10%
     */
    public function getTierDiscountPercentage(int $qty = 1): float
    {
        if ($qty < 2 || !$this->isEligibleForTierDiscount()) {
            return 0.0;
        }

        if ($qty >= 10) {
            return 10.0;
        } elseif ($qty >= 7) {
            return 8.0;
        } elseif ($qty >= 5) {
            return 5.0;
        } elseif ($qty >= 2) {
            return 2.0;
        }

        return 0.0;
    }

    /**
     * Hitung harga produk setelah diskon bertingkat untuk kuantitas tertentu
     */
    public function getTieredPrice(int $qty = 1): float
    {
        $basePrice = (float) $this->harga_jual_actual;
        $discountPercent = $this->getTierDiscountPercentage($qty);

        if ($discountPercent > 0) {
            return (float) round($basePrice - ($basePrice * ($discountPercent / 100)));
        }

        return $basePrice;
    }

    /**
     * Ambil rincian struktur tier untuk produk ini
     */
    public function getPricingTiers(?float $customBasePrice = null): array
    {
        $basePrice = $customBasePrice !== null ? $customBasePrice : (float) $this->harga_jual_actual;
        $isEligible = $this->isEligibleForTierDiscount();

        if (!$isEligible) {
            return [
                [
                    'min_qty' => 1,
                    'max_qty' => null,
                    'label' => 'Harga Normal',
                    'save_percent' => 0,
                    'unit_price' => $basePrice,
                ]
            ];
        }

        return [
            [
                'min_qty' => 1,
                'max_qty' => 1,
                'label' => 'Beli 1 pcs',
                'save_percent' => 0,
                'unit_price' => $basePrice,
            ],
            [
                'min_qty' => 2,
                'max_qty' => 4,
                'label' => 'Beli 2 - 4 pcs',
                'save_percent' => 2,
                'unit_price' => (float) round($basePrice * 0.98),
            ],
            [
                'min_qty' => 5,
                'max_qty' => 6,
                'label' => 'Beli 5 - 6 pcs',
                'save_percent' => 5,
                'unit_price' => (float) round($basePrice * 0.95),
            ],
            [
                'min_qty' => 7,
                'max_qty' => 9,
                'label' => 'Beli 7 - 9 pcs',
                'save_percent' => 8,
                'unit_price' => (float) round($basePrice * 0.92),
            ],
            [
                'min_qty' => 10,
                'max_qty' => null,
                'label' => 'Beli 10+ pcs',
                'save_percent' => 10,
                'unit_price' => (float) round($basePrice * 0.90),
            ],
        ];
    }

    public function getB2bPrice($qty = 1)
    {
        $basePrice = $this->harga_jual_actual;
        $setting = $this->b2bPriceSettings()
            ->whereNull('product_variant_id')
            ->where('min_qty', '<=', $qty)
            ->orderBy('min_qty', 'desc')
            ->first();

        if ($setting) {
            if ($setting->discount_type === 'percentage') {
                return $basePrice - ($basePrice * ($setting->discount_value / 100));
            } else {
                return $basePrice - $setting->discount_value;
            }
        }

        // Fallback to category tier discount if available
        $tieredPrice = $this->getTieredPrice($qty);
        if ($tieredPrice < $basePrice) {
            return $tieredPrice;
        }

        return $basePrice;
    }

    // Get raw actual price (handles fallback to variants min price if base is 0)
    public function getHargaJualActualAttribute()
    {
        if ($this->hasVariants()) {
            $minPrice = $this->variants()->min('harga_jual');
            if ($minPrice > 0) {
                return $minPrice;
            }
        }
        return $this->harga_jual;
    }

    // Get total actual stock across all variants
    public function getTotalStokAttribute()
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty() ? (int) $this->variants->sum('stok_aktual') : (int) $this->stok_aktual;
        }
        return $this->variants()->exists() ? (int) $this->variants()->sum('stok_aktual') : (int) $this->stok_aktual;
    }

    // Check if product is completely out of stock
    public function getIsOutOfStockAttribute()
    {
        return $this->total_stok <= 0;
    }

    // Get formatted price or range
    public function getFormattedPriceAttribute()
    {
        if ($this->hasVariants()) {
            $minPrice = $this->variants()->min('harga_jual');
            $maxPrice = $this->variants()->max('harga_jual');
            if ($minPrice > 0) {
                if ($minPrice == $maxPrice) {
                    return 'Rp ' . number_format($minPrice, 0, ',', '.');
                }
                return 'Rp ' . number_format($minPrice, 0, ',', '.') . ' - Rp ' . number_format($maxPrice, 0, ',', '.');
            }
        }
        return 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
    }

    // 💰 Harga setelah diskon
    public function getHargaDiskonAttribute()
    {
        $basePrice = $this->harga_jual_actual;
        if ($this->is_promo) {
            return $basePrice - ($basePrice * ($this->diskon_persen / 100));
        }

        return $basePrice;
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