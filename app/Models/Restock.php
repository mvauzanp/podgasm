<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'supplier_name',
        'notes',
        'total_cost',
    ];

    /**
     * Relasi ke admin yang menginput restock
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke item yang direstock
     */
    public function items()
    {
        return $this->hasMany(RestockItem::class);
    }
}
