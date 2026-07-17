<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    protected $fillable = [
        'reference',
        'payer_name',
        'payer_email',
        'payer_phone',
        'address',
        'purpose',
        'vehicle_plate',
        'vehicle_type',
        'notes',
        'amount',
        'currency',
        'gateway',
        'method',
        'gateway_reference',
        'checkout_url',
        'status',
        'paid_at',
        'failure_reason',
        'callback_payload',
        'ip_address',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'paid_at'          => 'datetime',
        'callback_payload' => 'array',
    ];

    public const PURPOSE_LABELS = [
        'road_tax'  => 'Cukai Jalan',
        'insurance' => 'Insurans',
        'both'      => 'Cukai Jalan & Insurans',
    ];

    public const GATEWAY_LABELS = [
        'chip'      => 'CHIP',
        'fiuu'      => 'Fiuu',
        'atome'     => 'Atome',
        'ahapay'    => 'AhaPay',
        'senangpay' => 'senangPay',
    ];

    public function purposeLabel(): string
    {
        return self::PURPOSE_LABELS[$this->purpose] ?? $this->purpose;
    }

    public function gatewayLabel(): string
    {
        return self::GATEWAY_LABELS[$this->gateway] ?? $this->gateway;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * PAY-YYYY-XXXX, sequential per year.
     */
    public static function nextReference(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $count = self::whereYear('created_at', $year)->lockForUpdate()->count();
            return 'PAY-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}
