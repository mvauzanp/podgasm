<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vouchers';

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'quota',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'is_active'  => 'boolean',
    ];

    /**
     * Relationship with usages
     */
    public function usages()
    {
        return $this->hasMany(VoucherUsage::class, 'voucher_id');
    }

    /**
     * Check if the voucher is valid for a specific user and order subtotal
     */
    public function isValidForUser($user, $subtotal)
    {
        if (!$this->is_active) {
            return [
                'valid' => false,
                'message' => 'Voucher sudah tidak aktif.'
            ];
        }

        $now = now();
        if ($this->start_date && $now->lt($this->start_date)) {
            return [
                'valid' => false,
                'message' => 'Voucher belum dimulai/aktif.'
            ];
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return [
                'valid' => false,
                'message' => 'Voucher telah kedaluwarsa.'
            ];
        }

        if ($this->quota !== null && $this->used_count >= $this->quota) {
            return [
                'valid' => false,
                'message' => 'Kuota penukaran voucher telah habis.'
            ];
        }

        if ($subtotal < $this->min_purchase) {
            return [
                'valid' => false,
                'message' => 'Minimal pembelian untuk menggunakan voucher ini adalah Rp ' . number_format($this->min_purchase, 0, ',', '.') . '.'
            ];
        }

        if ($user) {
            $alreadyUsed = VoucherUsage::where('user_id', $user->id)
                ->where('voucher_id', $this->id)
                ->exists();

            if ($alreadyUsed) {
                return [
                    'valid' => false,
                    'message' => 'Anda telah menggunakan voucher ini sebelumnya.'
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Calculate discount amount based on subtotal
     */
    public function calculateDiscount($subtotal)
    {
        if ($this->type === 'nominal' || $this->type === 'shipping_subsidy') {
            return min($this->value, $subtotal);
        }

        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
            if ($this->max_discount !== null) {
                return min($discount, $this->max_discount);
            }
            return min($discount, $subtotal);
        }

        return 0;
    }
}
