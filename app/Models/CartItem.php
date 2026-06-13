<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * CartItem belongs to Cart
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * CartItem belongs to Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * CartItem belongs to Product Variant
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get subtotal for this item
     */
    public function getSubtotal()
    {
        return $this->quantity * $this->price;
    }

    /**
     * Check if product price changed since added to cart
     */
    public function isPriceChanged()
    {
        return $this->price != $this->getCurrentPrice();
    }

    /**
     * Get current product price
     */
    public function getCurrentPrice()
    {
        $isB2B = $this->cart->user && $this->cart->user->role === 'branch';

        if ($this->product_variant_id && $this->variant) {
            return $isB2B 
                ? $this->variant->getB2bPrice($this->quantity) 
                : $this->variant->harga_jual_actual;
        }

        return $isB2B 
            ? $this->product->getB2bPrice($this->quantity) 
            : $this->product->harga_jual;
    }
}
