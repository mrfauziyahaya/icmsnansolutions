<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Resolves which site (domain) the app is acting as, and exposes that site's
 * configuration: gateways + credentials, Turnstile keys, labels, branding.
 *
 * Registered as a singleton. The site is set from the request host by
 * ResolveCurrentSite middleware, and can be overridden explicitly — important
 * for CLI work (reconcile cron) where there is no host and the site must come
 * from the payment record instead.
 */
class SiteManager
{
    private ?string $current = null;

    /** Site key currently in effect. */
    public function key(): string
    {
        return $this->current ?? $this->defaultKey();
    }

    public function defaultKey(): string
    {
        return (string) config('sites.default', 'nansolutions');
    }

    /** Force a specific site (CLI, tests, or per-payment work). */
    public function use(?string $site): self
    {
        $this->current = $this->exists($site) ? $site : null;

        return $this;
    }

    /** Run a callback with a different site active, then restore. */
    public function withSite(?string $site, callable $callback): mixed
    {
        $previous = $this->current;
        $this->use($site);

        try {
            return $callback();
        } finally {
            $this->current = $previous;
        }
    }

    public function exists(?string $site): bool
    {
        return $site !== null && is_array(config("sites.sites.{$site}"));
    }

    /** All configured site keys. */
    public function keys(): array
    {
        return array_keys((array) config('sites.sites', []));
    }

    /**
     * Map a hostname to a site key. Returns null when the host isn't claimed
     * by any site, so callers can decide (we fall back to the default).
     */
    public function keyForHost(?string $host): ?string
    {
        if (! $host) {
            return null;
        }

        $host = Str::lower(Str::before($host, ':'));   // strip any :port

        foreach ((array) config('sites.sites', []) as $key => $site) {
            foreach ((array) ($site['domains'] ?? []) as $domain) {
                if (Str::lower($domain) === $host) {
                    return $key;
                }
            }
        }

        return null;
    }

    // ── Config accessors ─────────────────────────────────────────────────────

    public function config(?string $path = null, mixed $default = null, ?string $site = null): mixed
    {
        $site = $site ?? $this->key();
        $key  = "sites.sites.{$site}" . ($path ? ".{$path}" : '');

        return config($key, $default);
    }

    public function label(?string $site = null): string
    {
        return (string) $this->config('label', $site ?? $this->key(), $site);
    }

    public function referencePrefix(?string $site = null): string
    {
        return (string) $this->config('reference_prefix', 'PAY', $site);
    }

    public function whatsappLink(?string $site = null): ?string
    {
        return $this->config('whatsapp_link', null, $site);
    }

    /** Gateway definitions (label, methods, bnpl flag, config) for a site. */
    public function gateways(?string $site = null): array
    {
        return (array) $this->config('gateways', [], $site);
    }

    /** A single gateway's credential bag, e.g. gatewayConfig('chip', 'api_key'). */
    public function gatewayConfig(string $gateway, ?string $key = null, mixed $default = null, ?string $site = null): mixed
    {
        $path = "gateways.{$gateway}.config" . ($key ? ".{$key}" : '');

        return $this->config($path, $default, $site);
    }

    public function gatewayLabel(string $gateway, ?string $site = null): string
    {
        return (string) $this->config("gateways.{$gateway}.label", $gateway, $site);
    }

    public function turnstile(?string $key = null, ?string $site = null): mixed
    {
        return $this->config('turnstile' . ($key ? ".{$key}" : ''), null, $site);
    }

    // ── Branding ─────────────────────────────────────────────────────────────

    /**
     * Company name for this site. The default site falls back to the name in
     * admin Settings so it stays editable there; other sites use config.
     */
    public function companyName(?string $site = null): string
    {
        if ($configured = $this->config('company', null, $site)) {
            return $configured;
        }

        if (($site ?? $this->key()) === $this->defaultKey()) {
            return \App\Models\Setting::instance()->company_name ?? $this->label($site);
        }

        return $this->label($site);
    }

    /**
     * Logo URL for this site, or null to fall back to a text wordmark.
     * Checks the file exists so a missing asset never renders broken.
     */
    public function logoUrl(?string $site = null): ?string
    {
        $path = $this->config('logo', null, $site);

        if ($path && is_file(public_path($path))) {
            return asset($path);
        }

        if (($site ?? $this->key()) === $this->defaultKey()) {
            $setting = \App\Models\Setting::instance();

            if ($setting->logo_path && is_file(storage_path('app/public/' . $setting->logo_path))) {
                return \Illuminate\Support\Facades\Storage::url($setting->logo_path);
            }

            return is_file(public_path('images/logo.png')) ? asset('images/logo.png') : null;
        }

        return null;
    }

    /** Whether a named route is reachable on this site. */
    public function allowsRoute(?string $routeName, ?string $site = null): bool
    {
        $allowed = (array) $this->config('routes', ['*'], $site);

        if (in_array('*', $allowed, true)) {
            return true;
        }
        if ($routeName === null) {
            return false;
        }

        foreach ($allowed as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }
}
