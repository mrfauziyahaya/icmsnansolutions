<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function isConfigured(): bool
    {
        return filled(config('services.turnstile.site_key'))
            && filled(config('services.turnstile.secret_key'));
    }

    /**
     * Verify a Turnstile token. When Turnstile is not configured the check is
     * skipped so local/staging still work, but that is logged — production
     * should always have the keys set.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('Turnstile is not configured — captcha check skipped.');
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post(self::VERIFY_URL, [
                'secret'   => config('services.turnstile.secret_key'),
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
