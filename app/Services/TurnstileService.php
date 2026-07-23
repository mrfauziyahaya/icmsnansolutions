<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Turnstile keys are bound to a hostname, so each site has its own pair.
     * Falls back to the legacy single-site keys when a site hasn't got any.
     */
    public function siteKey(?string $site = null): ?string
    {
        return site()->turnstile('site_key', $site) ?: config('services.turnstile.site_key');
    }

    public function secretKey(?string $site = null): ?string
    {
        return site()->turnstile('secret_key', $site) ?: config('services.turnstile.secret_key');
    }

    public function isConfigured(?string $site = null): bool
    {
        return filled($this->siteKey($site)) && filled($this->secretKey($site));
    }

    /**
     * Verify a Turnstile token. When Turnstile is not configured the check is
     * skipped so local/staging still work, but that is logged — production
     * should always have the keys set.
     */
    public function verify(?string $token, ?string $ip = null, ?string $site = null): bool
    {
        if (! $this->isConfigured($site)) {
            Log::warning('Turnstile is not configured — captcha check skipped.', ['site' => $site ?? site()->key()]);
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post(self::VERIFY_URL, [
                'secret'   => $this->secretKey($site),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->successful() && $response->json('success') === true;
        } catch (\Throwable $e) {
            Log::error('Turnstile verification error: ' . $e->getMessage());
            return false;
        }
    }
}
