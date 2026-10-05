<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesHistory extends Model
{
    use HasFactory;

    protected $table = 'sales_histories';

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'date',
        'qty_sold',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
        'qty_sold' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
