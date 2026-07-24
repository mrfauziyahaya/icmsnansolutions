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
        'site',
        'payer_name',
        'payer_email',
        'payer_phone',
        'address',
        'postcode',
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

    protected static function booted(): void
    {
        // Fire the "payment received" WhatsApp exactly once, on the transition
        // to paid — this catches every path (webhook + reconcile) in one place.
        // Guarded so a messaging failure never breaks payment processing.
        static::updated(function (Payment $payment) {
            if ($payment->wasChanged('status') && $payment->status === 'paid') {
                try {
                    app(\App\Services\WhatsAppService::class)->notifyPaymentReceived($payment);
                } catch (\Throwable $e) {
                    Log::error("Payment {$payment->reference} WhatsApp notify failed: {$e->getMessage()}");
                }
            }
        });
    }

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
        'senangpay' => 'Grab PayLater',
    ];

    public function purposeLabel(): string
    {
        return self::PURPOSE_LABELS[$this->purpose] ?? $this->purpose;
    }

    /**
     * Label as shown on this payment's own site (Fiuu is "Fiuu" on NAN
     * Solutions but "SPayLater" on Reniu), falling back to the global map.
     */
    public function gatewayLabel(): string
    {
        $label = site()->config("gateways.{$this->gateway}.label", null, $this->site);

        return $label ?? (self::GATEWAY_LABELS[$this->gateway] ?? $this->gateway);
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
            // Use the site this payment was made on, not the ambient one — the
            // reconcile cron runs in CLI where there's no request host.
            $result = app(PaymentGatewayManager::class)
                ->driver($this->gateway, $this->site)
                ->getStatus($this);
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
     * PREFIX-YYYY-XXXX, sequential per year per site (PAY- for NAN Solutions,
     * RNU- for Reniu) so references stay distinguishable in gateway dashboards
     * even though both sites share this table.
     */
    public static function nextReference(?string $site = null): string
    {
        $site   = $site ?? site()->key();
        $prefix = site()->referencePrefix($site);
        $year   = now()->year;

        return DB::transaction(function () use ($prefix, $year) {
            $count = self::whereYear('created_at', $year)
                ->where('reference', 'like', $prefix . '-%')
                ->lockForUpdate()
                ->count();

            return $prefix . '-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    /** Which site this payment was made on. */
    public function siteLabel(): string
    {
        return site()->label($this->site);
    }

    public function scopeForSite($query, ?string $site)
    {
        return $site ? $query->where('site', $site) : $query;
    }
}
