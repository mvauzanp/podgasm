<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'address',
        'biteship_area_id',
        'biteship_area_name',
        'postal_code',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Relasi ke User pemilik alamat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk alamat utama
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Helper untuk format alamat lengkap
     */
    public function getFormattedFullAddressAttribute(): string
    {
        $parts = [$this->address, $this->biteship_area_name];
        if ($this->postal_code && !str_contains($this->biteship_area_name, $this->postal_code)) {
            $parts[] = $this->postal_code;
        }
        return implode(', ', array_filter($parts));
    }
}
