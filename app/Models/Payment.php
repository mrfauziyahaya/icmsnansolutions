<?php

namespace App\Models;

use App\Services\Payments\GatewayException;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Ask the gateway for the real status and update this record if it has moved
     * on. Safe to call repeatedly; a settled payment is never reopened.
     *
     * @return bool whether the status changed
     */
    public function reconcile(): bool
    {
        if (! $this->isPending()) {
            return false;
        }

        try {
            $result = app(PaymentGatewayManager::class)->driver($this->gateway)->getStatus($this);
        } catch (GatewayException $e) {
            Log::warning("Reconcile {$this->reference} failed: {$e->getMessage()}");
            return false;
        }

        if ($result['status'] === $this->status) {
            return false;
        }

        $this->update([
            'status'         => $result['status'],
            'paid_at'        => $result['status'] === 'paid' ? ($this->paid_at ?? now()) : $this->paid_at,
            'failure_reason' => $result['reason'] ?? $this->failure_reason,
        ]);

        return true;
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
