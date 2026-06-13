<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsMessage extends Model
{
    use HasFactory;

    protected $table = 'cs_messages';

    protected $fillable = [
        'user_id',
        'sender_id',
        'message',
        'is_read',
    ];

    /**
     * Get the user who owns the chat thread.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
