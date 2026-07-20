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

    /**
     * Gateways that expose more than one selectable method at checkout. Each
     * entry becomes its own option; the chosen method is stored on the Payment
     * and the driver narrows the hosted page to it (e.g. CHIP's whitelist).
     *
     * @var array<string, array<int, array{method: string, label: string}>>
     */
    private const METHODS = [
        'chip' => [
            ['method' => 'fpx',  'label' => 'CHIP — FPX (Maybank, CIMB, dll.)'],
            ['method' => 'card', 'label' => 'CHIP — Kad Kredit / Debit'],
        ],
    ];

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
        $bnplMin  = (float) config('services.payments.bnpl_min', 30);
        $disabled = (array) config('services.payments.disabled', []);
        $out      = [];

        foreach (self::DRIVERS as $key => $class) {
            if (in_array($key, $disabled, true) || ! app($class)->isConfigured()) {
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

    /**
     * Flat list of selectable checkout options. A gateway with multiple methods
     * (CHIP → FPX / Card) yields one option per method; every other gateway
     * yields a single option. The `value` is what the checkout form submits.
     *
     * @return array<int, array{value: string, gateway: string, method: ?string, label: string, bnpl: bool}>
     */
    public function checkoutOptions(?float $amount = null): array
    {
        $bnplMin  = (float) config('services.payments.bnpl_min', 30);
        $disabled = (array) config('services.payments.disabled', []);
        $options  = [];

        foreach (self::DRIVERS as $key => $class) {
            if (in_array($key, $disabled, true) || ! app($class)->isConfigured()) {
                continue;
            }

            $isBnpl = in_array($key, self::BNPL, true);

            // BNPL providers reject small amounts — don't offer what will fail.
            if ($amount !== null && $isBnpl && $amount < $bnplMin) {
                continue;
            }

            if (isset(self::METHODS[$key])) {
                foreach (self::METHODS[$key] as $m) {
                    $options[] = [
                        'value'   => $key . ':' . $m['method'],
                        'gateway' => $key,
                        'method'  => $m['method'],
                        'label'   => $m['label'],
                        'bnpl'    => $isBnpl,
                    ];
                }

                continue;
            }

            $options[] = [
                'value'   => $key,
                'gateway' => $key,
                'method'  => null,
                'label'   => \App\Models\Payment::GATEWAY_LABELS[$key] ?? $key,
                'bnpl'    => $isBnpl,
            ];
        }

        return $options;
    }

    /**
     * Resolve a submitted option value against the options actually available
     * for the given amount. Returns null if the value is unknown or gated out
     * (BNPL below minimum, gateway unconfigured), so the caller can reject it.
     *
     * @return array{value: string, gateway: string, method: ?string, label: string, bnpl: bool}|null
     */
    public function resolveOption(string $value, ?float $amount = null): ?array
    {
        foreach ($this->checkoutOptions($amount) as $option) {
            if ($option['value'] === $value) {
                return $option;
            }
        }

        return null;
    }
}
