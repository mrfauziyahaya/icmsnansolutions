<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * RHB Pay — Assisted Host (RHB's hosted page offering FPX, cards and DuitNow Pay).
 *
 * Spec: "RHB Pay Merchant API Integration Guide P1" v1.0.12 (docs/).
 *
 * Auth: every request carries an HS256 JWT signed with the RequestKey:
 *   { sub: collectionId, key: appKey, jti: uuid, nbf, iat, exp: iat+60, data: {...} }
 * Responses (Direct + Indirect) carry a JWT signed with the ResponseKey.
 *
 * Flow:
 *   1. createPayment() returns a signed URL to our /pay/handoff page; the
 *      payment form is built there at render time (the JWT lives 1 minute)
 *      and auto-POSTed to /api/v1/gateway/payment.
 *   2. Direct Message: S2S JSON POST to our webhook, JWT in Authorization.
 *   3. Indirect Message: the browser POSTs rp_* fields + rp_token back to
 *      the same webhook (returnURL); the controller redirects the payer on.
 *   4. getStatus(): GET /api/v1/gateway/enquiry?orderId=.
 *
 * A callback's status is only trusted when the signed token itself binds the
 * orderId and statusCode; otherwise the enquiry API is asked instead.
 */
class RhbPayGateway implements PaymentGateway, SiteAwareGateway, FormPostGateway
{
    use Concerns\ResolvesSiteCredentials;

    private const PAYMENT_PATH = '/api/v1/gateway/payment';
    private const ENQUIRY_PATH = '/api/v1/gateway/enquiry';

    /** The request JWT is valid for 1 minute (spec §6). */
    private const TOKEN_TTL = 60;

    /** How long the handoff link stays usable. */
    private const HANDOFF_TTL_MINUTES = 30;

    /** A payment RHB still can't find after this long was never created there. */
    private const NOT_FOUND_GRACE_MINUTES = 60;

    protected function gatewayKey(): string
    {
        return 'rhb';
    }

    public function isConfigured(): bool
    {
        return filled($this->cfg('collection_id'))
            && filled($this->cfg('app_key'))
            && filled($this->cfg('request_key'))
            && filled($this->cfg('response_key'))
            && filled($this->cfg('base_url'));
    }

    public function createPayment(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('RHB Pay is not configured.');
        }

        // Relative signature: survives proxies rewriting scheme/host. The
        // reference is sequential, so the signature is what keeps one payer
        // from opening another's handoff page (it carries their details).
        $url = url(URL::temporarySignedRoute(
            'pay.handoff',
            now()->addMinutes(self::HANDOFF_TTL_MINUTES),
            ['reference' => $payment->reference],
            absolute: false,
        ));

        $payment->update(['checkout_url' => $url]);

        return $url;
    }

    public function checkoutForm(Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('RHB Pay is not configured.');
        }

        $amount = $this->money($payment->amount);
        $name   = $this->buyerName($payment->payer_name);
        $phone  = $this->buyerPhone($payment->payer_phone);

        $token = $this->requestToken([
            'orderId'    => $payment->reference,
            'amount'     => $amount,
            'currency'   => $payment->currency,
            'buyerName'  => $name,
            'buyerEmail' => (string) $payment->payer_email,
            'buyerPhone' => $phone,
        ]);

        return [
            'action' => $this->baseUrl() . self::PAYMENT_PATH,
            'fields' => [
                'collectionId' => (string) $this->cfg('collection_id'),
                'orderId'      => $payment->reference,
                'currency'     => $payment->currency,
                'amount'       => $amount,
                'buyerName'    => $name,
                'buyerEmail'   => (string) $payment->payer_email,
                'buyerPhone'   => $phone,
                // Spec: "Dynamic redirect (optional – URL encoded)". Overrides
                // the Redirect URL registered at onboarding.
                'returnURL'    => rawurlencode(route('pay.webhook', ['gateway' => 'rhb'])),
                'token'        => $token,
            ],
        ];
    }

    public function verifyCallback(Request $request): array
    {
        // Direct Message is JSON with the JWT in Authorization; the Indirect
        // (browser) message is a form with the JWT in rp_token.
        $isDirect = $request->isJson();
        $fields   = $isDirect ? $request->json()->all() : $request->all();
        $token    = $isDirect ? (string) $request->bearerToken() : (string) ($fields['rp_token'] ?? '');

        // Optional static header agreed with RHB support (spec §4).
        $apiKey = $this->cfg('callback_api_key');
        if ($isDirect && filled($apiKey) && ! hash_equals((string) $apiKey, (string) $request->header('X-API-Key'))) {
            Log::warning('RHB Pay callback X-API-Key mismatch.', ['ip' => $request->ip()]);
            throw new GatewayException('RHB Pay callback API key mismatch.');
        }

        $claims = $this->decodeToken($token);

        if (isset($claims['sub']) && $claims['sub'] !== $this->cfg('collection_id')) {
            throw new GatewayException('RHB Pay token is for another collection.');
        }

        $reference = isset($fields['rp_orderId']) ? (string) $fields['rp_orderId'] : null;
        $data      = $this->tokenData($claims);

        // Anything the token signs must match the fields it travelled with.
        foreach (['orderId' => 'rp_orderId', 'statusCode' => 'rp_statusCode', 'transactionRef' => 'rp_transactionRef'] as $claim => $field) {
            if (isset($data[$claim], $fields[$field]) && (string) $data[$claim] !== (string) $fields[$field]) {
                Log::warning("RHB Pay token {$claim} does not match {$field}.", ['reference' => $reference]);
                throw new GatewayException("RHB Pay token {$claim} mismatch.");
            }
        }

        $payment = $reference ? Payment::where('reference', $reference)->first() : null;

        // Never trust an echoed amount — it must equal what we stored.
        foreach ([$fields['rp_amount'] ?? null, $data['amount'] ?? null] as $amount) {
            if ($payment && $amount !== null && $this->money($amount) !== $this->money($payment->amount)) {
                Log::warning("RHB Pay amount mismatch for {$reference}.", ['received' => $amount]);
                throw new GatewayException('RHB Pay amount mismatch.');
            }
        }

        $result = [
            'reference'         => $reference,
            'gateway_reference' => filled($fields['rp_transactionRef'] ?? null) ? (string) $fields['rp_transactionRef'] : null,
            'status'            => 'pending',
            'reason'            => null,
        ];

        // Only a token that binds both the order and its status can settle it;
        // otherwise a genuine token could be replayed with an edited status.
        if (isset($data['orderId'], $data['statusCode'])) {
            $code = (string) $data['statusCode'];

            return array_merge($result, $this->outcome($code, $fields['rp_remarks'] ?? null));
        }

        if ($payment) {
            return array_merge($result, $this->getStatus($payment));
        }

        return $result;
    }

    public function getStatus(Payment $payment): array
    {
        $unchanged = ['status' => $payment->status, 'reason' => null];

        if (! $this->isConfigured()) {
            throw new GatewayException('RHB Pay is not configured.');
        }

        $response = Http::withToken($this->requestToken(['OrderId' => $payment->reference]))
            ->acceptJson()
            ->get($this->baseUrl() . self::ENQUIRY_PATH, ['orderId' => $payment->reference]);

        if ($response->status() === 404) {
            // RP05: RHB never created it (e.g. the payer abandoned the handoff
            // page or the request was rejected). Allow time for one in flight.
            return $payment->created_at?->lt(now()->subMinutes(self::NOT_FOUND_GRACE_MINUTES))
                ? ['status' => 'failed', 'reason' => 'RP05 Transaction not found at RHB Pay']
                : $unchanged;
        }

        if (! $response->successful()) {
            throw new GatewayException('RHB Pay enquiry failed: HTTP ' . $response->status() . ' ' . ($response->json('code') ?? ''));
        }

        // §7.3.1 shows the rp_* fields flat; §10 wraps successes in "data".
        $data = $response->json('data') ?? $response->json() ?? [];
        $code = (string) ($data['rp_statusCode'] ?? '');

        // RP503: the channel couldn't be reached — try again next tick.
        if ($code === '' || $code === 'RP503') {
            return $unchanged;
        }

        if (isset($data['rp_amount']) && $this->money($data['rp_amount']) !== $this->money($payment->amount)) {
            throw new GatewayException("RHB Pay enquiry amount mismatch for {$payment->reference}.");
        }

        return $this->outcome($code, $data['rp_remarks'] ?? null);
    }

    /**
     * Map an RHB rp_statusCode (spec §11.1) to our status.
     *
     * @return array{status: string, reason: string|null}
     */
    private function outcome(string $code, ?string $remarks): array
    {
        $status = match ($code) {
            'RP00'         => 'paid',
            // RP41 is explicitly not final; RP91 is a redirect still in
            // progress; RP43 (duplicate order id) is a re-submission whose
            // original attempt is still governed by its own result.
            'RP41', 'RP91', 'RP43' => 'pending',
            'RP04'         => 'cancelled',
            default        => 'failed',
        };

        return [
            'status' => $status,
            'reason' => $status === 'paid' ? null : trim($code . ' ' . $remarks),
        ];
    }

    /**
     * Sign a request JWT (HS256, RequestKey) carrying $data.
     */
    private function requestToken(array $data): string
    {
        $now = time();

        $header  = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'sub'  => (string) $this->cfg('collection_id'),
            'key'  => (string) $this->cfg('app_key'),
            'jti'  => (string) Str::uuid(),
            'nbf'  => $now,
            'iat'  => $now,
            'exp'  => $now + self::TOKEN_TTL,
            'data' => $data,
        ];

        $signingInput = $this->b64url(json_encode($header)) . '.' . $this->b64url(json_encode($payload, JSON_UNESCAPED_SLASHES));

        return $signingInput . '.' . $this->b64url(hash_hmac('sha256', $signingInput, (string) $this->cfg('request_key'), true));
    }

    /**
     * Verify a response JWT (HS256, ResponseKey) and return its claims.
     * Expiry isn't enforced: RHB retries the Direct Message with its original
     * token, and a replay can only repeat an outcome bound inside the token.
     */
    private function decodeToken(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new GatewayException('RHB Pay token is missing or malformed.');
        }

        [$h, $p, $s] = $parts;
        $header = json_decode($this->b64urlDecode($h), true);

        if (($header['alg'] ?? null) !== 'HS256') {
            throw new GatewayException('RHB Pay token uses an unexpected algorithm.');
        }

        $expected = $this->b64url(hash_hmac('sha256', "{$h}.{$p}", (string) $this->cfg('response_key'), true));

        if (! hash_equals($expected, $s)) {
            Log::warning('RHB Pay token signature verification failed.', ['site' => $this->site()]);
            throw new GatewayException('RHB Pay token signature verification failed.');
        }

        return json_decode($this->b64urlDecode($p), true) ?: [];
    }

    /** The token's "data" claim — an object, or (as some spec samples show) a JSON string. */
    private function tokenData(array $claims): array
    {
        $data = $claims['data'] ?? [];

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return is_array($data) ? $data : [];
    }

    private function baseUrl(): string
    {
        return rtrim((string) $this->cfg('base_url'), '/');
    }

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /** buyerName: max 50, alphanumeric, space and #$&-_,@() only. */
    private function buyerName(?string $name): string
    {
        $clean = preg_replace('/[^A-Za-z0-9 #$&\-_,@()]/', '', (string) $name);

        return trim(substr(preg_replace('/\s+/', ' ', $clean), 0, 50));
    }

    /** buyerPhone: country code, digits only (spec example 601234567890). */
    private function buyerPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '60')) {
            return $digits;
        }

        return '60' . ltrim($digits, '0');
    }

    private function b64url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private function b64urlDecode(string $encoded): string
    {
        return (string) base64_decode(strtr($encoded, '-_', '+/'), true);
    }
}
