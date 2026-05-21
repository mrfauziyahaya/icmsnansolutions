<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppNotification extends Model
{
    protected $fillable = [
        'client_id',
        'type',
        'recipient_phone',
        'message',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }
}
