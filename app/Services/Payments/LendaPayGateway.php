<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lenda Pay (Direct Lending) — BNPL/FPX-style checkout API.
 *
 * Spec: "Lenda Pay E-Commerce Sandbox Swagger (8443) v1.0" (OpenAPI 3.0.3).
 *   Auth:    POST /auth/token {accessKeyId, secretAccessKey}, no bearer on this call -> Bearer JWT,
 *            expiresIn 900s. Cached below expiry so we don't refetch per checkout (60/min per IP limit).
 *   Create:  POST /create (amount in **sen**, integer). merchant_order_id is the idempotency key
 *            (case-insensitive) — reusing the same payment's reference returns the same checkout;
 *            a 409 ORDER_DETAILS_MISMATCH means the same id was reused with different details.
 *            -> redirectUrl, checkout_id, status PROCESSING.
 *   Status:  GET /status?merchant_order_id=... -> status one of PROCESSING/PAID/CANCELLED. No FAILED
 *            state exists in this API.
 *   Webhook: thin poke `{merchant_order_id, referenceId}` (same value, no status of its own) — always
 *            re-fetch GET /status for the authoritative state. Signed
 *            `X-Signature: t=<epoch>,v1=<hex>` where v1 = HMAC-SHA256(webhookSecret, "{t}.{rawBody}");
 *            reject if |now - t| > 300s.
 */
class LendaPayGateway implements PaymentGateway, SiteAwareGateway
{
    use Concerns\ResolvesSiteCredentials;

    protected function gatewayKey(): string
    {
        return 'lendapay';
    }

    public function isConfigured(): bool
    {
        return filled($this->cfg('access_key_id'))
            && filled($this->cfg('secret_access_key'))
            && filled($this->cfg('base_url'));
    }

    public function createPayment(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('Lenda Pay is not configured.');
        }

        // Sen, integer — the API rejects decimals and enforces RM30-RM10,000 server-side.
        $amount = (int) round($payment->amount * 100);

        $body = [
            'merchant_order_id'   => $payment->reference,
            'amount'              => $amount,
            'currency'            => $payment->currency,
            'callbackUrl'         => route('pay.webhook', ['gateway' => 'lendapay']),
            'paymentResultUrl'    => route('pay.success', ['reference' => $payment->reference]),
            'paymentCancelUrl'    => route('pay.failed', ['reference' => $payment->reference]),
            'merchantReferenceId' => $payment->reference,
            'customerInfo'        => [
                'mobileNumber' => $this->toE164($payment->payer_phone),
                'fullName'     => $payment->payer_name,
                'email'        => $payment->payer_email,
            ],
        ];

        $response = $this->post('/create', $body);

        if (! $response->successful()) {
            $err = $response->json('message') ?? $response->json('code') ?? $response->body();
            Log::error("Lenda Pay createPayment failed for {$payment->reference}: " . (is_string($err) ? $err : json_encode($err)));
            throw new GatewayException('Lenda Pay: ' . (is_string($err) ? $err : 'request failed'));
        }

        $url = $response->json('redirectUrl');

        if (! $url) {
            throw new GatewayException('Lenda Pay did not return a redirectUrl.');
        }

        $payment->update([
            'checkout_url'      => $url,
            'gateway_reference' => $response->json('checkout_id') ?? $payment->gateway_reference,
        ]);

        return $url;
    }

    public function getStatus(Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('Lenda Pay is not configured.');
        }

        return $this->queryStatus($payment->reference);
    }

    public function verifyCallback(Request $request): array
    {
        $this->assertSignatureIsValid($request);

        $body      = json_decode($request->getContent(), true) ?: $request->all();
        $reference = data_get($body, 'merchant_order_id') ?? data_get($body, 'referenceId');

        if (! $reference) {
            throw new GatewayException('Lenda Pay callback missing merchant_order_id.');
        }

        // The webhook is only a "something changed" poke and carries no status of
        // its own — re-fetch the authoritative state from GET /status.
        $result = $this->queryStatus($reference);

        return [
            'reference'         => $reference,
            'gateway_reference' => $reference,
            'status'            => $result['status'],
            'reason'            => $result['reason'],
        ];
    }

    /**
     * Query Lenda Pay for the authoritative checkout status by our merchant_order_id.
     */
    private function queryStatus(string $reference): array
    {
        $response = $this->get('/status', ['merchant_order_id' => $reference]);

        // Not found = the order never reached Lenda Pay (or was queried too early).
        if ($response->status() === 404) {
            return ['status' => 'pending', 'reason' => null];
        }

        if (! $response->successful()) {
            throw new GatewayException('Lenda Pay getStatus failed: HTTP ' . $response->status());
        }

        $status = strtoupper((string) $response->json('status'));

        return [
            'status' => $this->mapStatus($status),
            'reason' => $status === 'CANCELLED' ? 'Lenda Pay status CANCELLED' : null,
        ];
    }

    /**
     * POST with the cached Bearer token, retrying once with a fresh token on 401
     * (the cached one is short-lived and could be revoked/rotated mid-life).
     */
    private function post(string $path, array $body)
    {
        $response = Http::withToken($this->token())->acceptJson()
            ->post(rtrim($this->cfg('base_url'), '/') . $path, $body);

        if ($response->status() === 401) {
            $this->forgetToken();
            $response = Http::withToken($this->token())->acceptJson()
                ->post(rtrim($this->cfg('base_url'), '/') . $path, $body);
        }

        return $response;
    }

    private function get(string $path, array $query)
    {
        $response = Http::withToken($this->token())->acceptJson()
            ->get(rtrim($this->cfg('base_url'), '/') . $path, $query);

        if ($response->status() === 401) {
            $this->forgetToken();
            $response = Http::withToken($this->token())->acceptJson()
                ->get(rtrim($this->cfg('base_url'), '/') . $path, $query);
        }

        return $response;
    }

    /**
     * Cached Bearer token, fetched on first use and refreshed well before the
     * 15-minute expiry so we never hand out a token that dies mid-flight, and
     * never hit the 60/min-per-IP token endpoint on every checkout.
     */
    private function token(): string
    {
        return Cache::remember($this->tokenCacheKey(), now()->addSeconds(780), function () {
            $response = Http::acceptJson()->post(rtrim($this->cfg('base_url'), '/') . '/auth/token', [
                'accessKeyId'     => $this->cfg('access_key_id'),
                'secretAccessKey' => $this->cfg('secret_access_key'),
            ]);

            if (! $response->successful()) {
                $err = $response->json('message') ?? $response->body();
                throw new GatewayException('Lenda Pay auth failed: ' . (is_string($err) ? $err : 'request failed'));
            }

            $token = $response->json('accessToken');

            if (! $token) {
                throw new GatewayException('Lenda Pay auth did not return an accessToken.');
            }

            return $token;
        });
    }

    private function forgetToken(): void
    {
        Cache::forget($this->tokenCacheKey());
    }

    private function tokenCacheKey(): string
    {
        return 'lendapay_token_' . $this->site();
    }

    /**
     * Verify X-Signature: t=<epoch>,v1=<hex>, where
     * v1 = HMAC-SHA256(webhookSecret, "{t}.{rawBody}"). Reject a stale signature
     * (>300s clock skew/replay window) per spec.
     */
    private function assertSignatureIsValid(Request $request): void
    {
        $secret = $this->cfg('webhook_secret');
        $header = (string) $request->header('X-Signature');

        if (blank($secret)) {
            Log::warning('Lenda Pay webhook secret not configured — X-Signature not verified (status is still re-fetched from Lenda Pay).');
            return;
        }

        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$k, $v] = array_pad(explode('=', $pair, 2), 2, null);
            $parts[$k] = $v;
        }

        $t  = $parts['t'] ?? null;
        $v1 = $parts['v1'] ?? null;

        if (! $t || ! $v1) {
            Log::warning('Lenda Pay webhook missing t/v1 in X-Signature.', ['ip' => $request->ip()]);
            throw new GatewayException('Lenda Pay signature verification failed.');
        }

        if (abs(time() - (int) $t) > 300) {
            Log::warning('Lenda Pay webhook signature stale.', ['ip' => $request->ip(), 't' => $t]);
            throw new GatewayException('Lenda Pay signature verification failed (stale).');
        }

        $expected = hash_hmac('sha256', $t . '.' . $request->getContent(), $secret);

        if (! hash_equals($expected, $v1)) {
            Log::warning('Lenda Pay webhook signature mismatch.', ['ip' => $request->ip()]);
            throw new GatewayException('Lenda Pay signature verification failed.');
        }
    }

    /**
     * Lenda Pay statuses: PROCESSING (pending), PAID, CANCELLED — there is no
     * FAILED state in this API. Anything unrecognised stays pending (never
     * falsely paid).
     */
    private function mapStatus(string $status): string
    {
        return match ($status) {
            'PAID'      => 'paid',
            'CANCELLED' => 'cancelled',
            default     => 'pending',
        };
    }

    /**
     * Best-effort Malaysian phone -> E.164 (Lenda Pay requires mobileNumber in E.164).
     */
    private function toE164(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '60')) {
            return '+' . $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '+60' . substr($digits, 1);
        }

        return '+60' . $digits;
    }
}
