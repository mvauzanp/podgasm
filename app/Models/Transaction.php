<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'jumlah',
        'total_harga',
        'jenis',
        'tanggal'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
