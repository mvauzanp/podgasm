<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockItem extends Model
{
    protected $fillable = [
        'restock_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'purchase_price',
    ];

    /**
     * Relasi ke transaksi restock induk
     */
    public function restock()
    {
        return $this->belongsTo(Restock::class);
    }

    /**
     * Relasi ke produk yang direstock
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi ke varian produk yang direstock
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
