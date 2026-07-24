<?php

namespace App\Services\Payments\Concerns;

/**
 * Gives a gateway driver its per-site credential bag.
 *
 * Drivers read credentials through cfg() instead of config('services.*'),
 * so the same driver class serves every site with the right keys.
 */
trait ResolvesSiteCredentials
{
    protected ?string $siteKey = null;

    /** Gateway key in config/sites.php (e.g. 'chip'). */
    abstract protected function gatewayKey(): string;

    public function usingSite(?string $site): static
    {
        $this->siteKey = $site;

        return $this;
    }

    public function site(): string
    {
        return $this->siteKey ?? site()->key();
    }

    /** A credential for this gateway on the bound site. */
    protected function cfg(string $key, mixed $default = null): mixed
    {
        return site()->gatewayConfig($this->gatewayKey(), $key, $default, $this->site());
    }

    /** This gateway's customer-facing label on the bound site. */
    protected function label(): string
    {
        return site()->gatewayLabel($this->gatewayKey(), $this->site());
    }
}
