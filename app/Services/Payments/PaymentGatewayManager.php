<?php

namespace App\Services\Payments;

class PaymentGatewayManager
{
    private const DRIVERS = [
        'chip'      => ChipGateway::class,
        'fiuu'      => FiuuGateway::class,
        'atome'     => AtomeGateway::class,
        'ahapay'    => AhaPayGateway::class,
        'senangpay' => SenangPayGateway::class,
    ];

    /**
     * Gateways that are BNPL — hidden below the BNPL minimum amount.
     */
    public const BNPL = ['atome', 'ahapay'];

    public function driver(string $gateway): PaymentGateway
    {
        $class = self::DRIVERS[$gateway] ?? null;

        if (! $class) {
            throw new GatewayException("Unknown payment gateway [{$gateway}].");
        }

        return app($class);
    }

    public function exists(string $gateway): bool
    {
        return isset(self::DRIVERS[$gateway]);
    }

    /**
     * Gateways that are configured and therefore selectable by a payer.
     *
     * @return array<string, string> gateway key => label
     */
    public function available(?float $amount = null): array
    {
        $bnplMin = (float) config('services.payments.bnpl_min', 30);
        $out     = [];

        foreach (self::DRIVERS as $key => $class) {
            if (! app($class)->isConfigured()) {
                continue;
            }

            // BNPL providers reject small amounts — don't offer what will fail.
            if ($amount !== null && in_array($key, self::BNPL, true) && $amount < $bnplMin) {
                continue;
            }

            $out[$key] = \App\Models\Payment::GATEWAY_LABELS[$key] ?? $key;
        }

        return $out;
    }
}
