<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2BPriceSetting extends Model
{
    protected $table = 'b2b_price_settings';

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'min_qty',
        'discount_type',
        'discount_value',
    ];

    protected $casts = [
        'min_qty' => 'integer',
        'discount_value' => 'decimal:2',
    ];

    /**
     * Relationship with Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship with ProductVariant
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
