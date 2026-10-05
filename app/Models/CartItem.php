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
        $user = $this->cart?->user;
        $isB2B = $user && ($user->role === 'branch' || $user->b2b_type === 'reseller' || $user->b2b_type === 'branch');

        if ($this->product_variant_id && $this->variant) {
            return $isB2B ? $this->variant->getB2bPrice($this->quantity) : $this->variant->getTieredPrice($this->quantity);
        }

        if ($this->product) {
            return $isB2B ? $this->product->getB2bPrice($this->quantity) : $this->product->getTieredPrice($this->quantity);
        }

        return $this->price;
    }

    /**
     * Get original un-discounted price
     */
    public function getOriginalPrice()
    {
        if ($this->product_variant_id && $this->variant) {
            return $this->variant->harga_jual_actual;
        }

        if ($this->product) {
            return $this->product->harga_jual_actual ?? $this->product->harga_jual;
        }

        return $this->price;
    }

    /**
     * Get active discount percentage for this cart item
     */
    public function getDiscountPercentage(): float
    {
        $original = (float) $this->getOriginalPrice();
        $current = (float) $this->price;

        if ($original > 0 && $current < $original) {
            return (float) round((($original - $current) / $original) * 100);
        }

        return 0.0;
    }

    /**
     * Check if this item has active discount
     */
    public function hasDiscount(): bool
    {
        return $this->getDiscountPercentage() > 0;
    }
}
