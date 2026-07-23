<?php

namespace App\Services\Payments;

use App\Services\SiteManager;

class PaymentGatewayManager
{
    private const DRIVERS = [
        'chip'      => ChipGateway::class,
        'fiuu'      => FiuuGateway::class,
        'atome'     => AtomeGateway::class,
        'ahapay'    => AhaPayGateway::class,
        'senangpay' => SenangPayGateway::class,
    ];

    public function __construct(private SiteManager $sites) {}

    /**
     * Resolve a driver bound to a specific site, so it reads that site's
     * credentials. Always pass the site explicitly where it matters:
     *   - createPayment  -> the current request's site
     *   - getStatus      -> the payment's own site (CLI-safe)
     *   - verifyCallback -> the site the callback arrived on
     */
    public function driver(string $gateway, ?string $site = null): PaymentGateway
    {
        $class = self::DRIVERS[$gateway] ?? null;

        if (! $class) {
            throw new GatewayException("Unknown payment gateway [{$gateway}].");
        }

        $driver = app($class);

        if ($driver instanceof SiteAwareGateway) {
            $driver->usingSite($site ?? $this->sites->key());
        }

        return $driver;
    }

    public function exists(string $gateway): bool
    {
        return isset(self::DRIVERS[$gateway]);
    }

    /**
     * Gateways this site has switched on and configured, as label => key.
     *
     * @return array<string, string>
     */
    public function available(?float $amount = null, ?string $site = null): array
    {
        $out = [];

        foreach ($this->checkoutOptions($amount, $site) as $option) {
            $out[$option['gateway']] = $option['label'];
        }

        return $out;
    }

    /**
     * Flat list of selectable checkout options for a site. A gateway with
     * several methods (CHIP → FPX / Card) yields one option per method.
     *
     * @return array<int, array{value: string, gateway: string, method: ?string, label: string, bnpl: bool}>
     */
    public function checkoutOptions(?float $amount = null, ?string $site = null): array
    {
        $site     = $site ?? $this->sites->key();
        $bnplMin  = (float) config('services.payments.bnpl_min', 30);
        $disabled = (array) config('services.payments.disabled', []);
        $options  = [];

        foreach ($this->sites->gateways($site) as $key => $definition) {
            if (! isset(self::DRIVERS[$key]) || in_array($key, $disabled, true)) {
                continue;
            }

            if (! $this->driver($key, $site)->isConfigured()) {
                continue;
            }

            $isBnpl = (bool) ($definition['bnpl'] ?? false);

            // BNPL providers reject small amounts — don't offer what will fail.
            if ($amount !== null && $isBnpl && $amount < $bnplMin) {
                continue;
            }

            $methods = $definition['methods'] ?? null;

            if (is_array($methods) && $methods !== []) {
                foreach ($methods as $m) {
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
                'label'   => $definition['label'] ?? $key,
                'bnpl'    => $isBnpl,
            ];
        }

        return $options;
    }

    /**
     * Resolve a submitted option value against what's actually available for
     * this amount on this site. Null when unknown or gated out, so the caller
     * can reject it.
     *
     * @return array{value: string, gateway: string, method: ?string, label: string, bnpl: bool}|null
     */
    public function resolveOption(string $value, ?float $amount = null, ?string $site = null): ?array
    {
        foreach ($this->checkoutOptions($amount, $site) as $option) {
            if ($option['value'] === $value) {
                return $option;
            }
        }

        return null;
    }
}
