<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    protected $fillable = [
        'nama_pemilik',
        'no_ic',
        'poskod',
        'no_plate',
        'ehailing',
        'ehailing_usage',
        'tukar_milik',
        'whatsapp',
        'jenis_perlindungan',
        'perlindungan_tambahan',
        'jumlah_perlindungan_cermin',
        'jenis_pembayaran',
        'is_read',
    ];

    protected $casts = [
        'ehailing'                   => 'boolean',
        'tukar_milik'                => 'boolean',
        'is_read'                    => 'boolean',
        'perlindungan_tambahan'      => 'array',
        'jumlah_perlindungan_cermin' => 'decimal:2',
    ];
}
