<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'total_price',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    /**
     * Cart belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cart has many items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get or create cart for user
     */
    public static function getOrCreateForUser($userId)
    {
        return self::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Calculate total price based on items
     */
    public function calculateTotal()
    {
        $total = $this->items()->sum(DB::raw('quantity * price'));
        $this->update(['total_price' => $total ?? 0]);
        return $total ?? 0;
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        $this->items()->delete();
        $this->update(['total_price' => 0]);
    }
}
