<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'client_id',
        'type',
        'nett_premium',
        'premium',
        'road_tax_price',
        'total_amount',
        'pdf_path',
        'issued_at',
    ];

    protected $casts = [
        'nett_premium'   => 'decimal:2',
        'premium'        => 'decimal:2',
        'road_tax_price' => 'decimal:2',
        'total_amount'   => 'decimal:2',
        'issued_at'      => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }
}
