<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2BRegistration extends Model
{
    protected $table = 'b2b_registrations';

    protected $fillable = [
        'user_id',
        'owner_name',
        'store_name',
        'address',
        'phone',
        'email',
        'ktp_file',
        'storefront_photo',
        'status',
        'rejection_reason',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Relationship with reviewer (admin user)
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
