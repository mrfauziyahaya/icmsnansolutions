<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Atome (BNPL) — direct merchant integration, v2 online API.
 *
 * Spec: https://doc.apaylater.com/v2/ (swagger.yaml).
 *   Auth:   HTTP Basic (partner_id : secret_key)
 *   Create: POST /payments        -> redirectUrl  (amount in the minor unit, sen)
 *   Status: GET  /payments/{referenceId}  -> status: PROCESSING/PAID/FAILED/REFUNDED/CANCELLED
 *   Base:   test https://api.apaylater.net/v2 | prod https://api.apaylater.com/v2
 *
 * Callback: Atome POSTs only { referenceId } to callbackUrl when status changes,
 * signed HMAC-SHA256 over the raw body in the X-Signature header. Because the
 * callback carries no status, we always re-fetch the authoritative status from
 * the authenticated Get Payment API — so even an unsigned/forged ping cannot
 * settle an order. Signature is still verified when a callback secret is set.
 */
class AtomeGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.atome.partner_id'))
            && filled(config('services.atome.secret_key'))
            && filled(config('services.atome.base_url'));
    }

    public function createPayment(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('Atome is not configured.');
        }

        // Atome takes the amount in the smallest currency unit (MYR sen).
        $amount = (int) round($payment->amount * 100);

        $body = [
            'referenceId'         => $payment->reference,
            'currency'            => $payment->currency,
            'amount'              => $amount,
            'callbackUrl'         => route('pay.webhook', ['gateway' => 'atome']),
            'paymentResultUrl'    => route('pay.success', ['reference' => $payment->reference]),
            'paymentCancelUrl'    => route('pay.failed', ['reference' => $payment->reference]),
            'merchantReferenceId' => $payment->reference,
            'customerInfo'        => [
                'mobileNumber' => $this->toE164($payment->payer_phone),
                'fullName'     => $payment->payer_name,
                'email'        => $payment->payer_email,
            ],
            'shippingAddress'     => [
                'countryCode' => 'MY',
                'lines'       => [$payment->address ?: '-'],
                'postCode'    => $this->postcodeFrom($payment->address),
            ],
            'items'               => [[
                'itemId'   => $payment->reference,
                'name'     => 'Pembayaran ' . $payment->reference,
                'quantity' => 1,
                'price'    => $amount,
            ]],
        ];

        $response = Http::withBasicAuth(
            config('services.atome.partner_id'),
            config('services.atome.secret_key'),
        )->acceptJson()->post(rtrim(config('services.atome.base_url'), '/') . '/payments', $body);

        if (! $response->successful()) {
            $err = $response->json('message') ?? $response->json('code') ?? $response->body();
            Log::error("Atome createPayment failed for {$payment->reference}: " . (is_string($err) ? $err : json_encode($err)));
            throw new GatewayException('Atome: ' . (is_string($err) ? $err : 'request failed'));
        }

        $url = $response->json('redirectUrl');

        if (! $url) {
            throw new GatewayException('Atome did not return a redirectUrl.');
        }

        $payment->update([
            'checkout_url'      => $url,
            'gateway_reference' => $response->json('referenceId') ?? $payment->reference,
        ]);

        return $url;
    }

    public function getStatus(Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('Atome is not configured.');
        }

        // We key Atome's payment on our own reference (sent as referenceId).
        return $this->queryStatus($payment->reference);
    }

    public function verifyCallback(Request $request): array
    {
        $this->assertSignatureIsValid($request);

        $body        = json_decode($request->getContent(), true) ?: $request->all();
        $referenceId = data_get($body, 'referenceId');

        if (! $referenceId) {
            throw new GatewayException('Atome callback missing referenceId.');
        }

        // The callback is only a status-change signal; fetch the real status
        // from Atome's authenticated API rather than trusting the ping.
        $result = $this->queryStatus($referenceId);

        return [
            'reference'         => $referenceId,
            'gateway_reference' => $referenceId,
            'status'            => $result['status'],
            'reason'            => $result['reason'],
        ];
    }

    /**
     * Query Atome for the authoritative payment status by our referenceId.
     */
    private function queryStatus(string $referenceId): array
    {
        $response = Http::withBasicAuth(
            config('services.atome.partner_id'),
            config('services.atome.secret_key'),
        )->acceptJson()->get(rtrim(config('services.atome.base_url'), '/') . '/payments/' . rawurlencode($referenceId));

        // Not found yet = buyer hasn't progressed → still pending.
        if ($response->status() === 404) {
            return ['status' => 'pending', 'reason' => null];
        }

        if (! $response->successful()) {
            throw new GatewayException('Atome getStatus failed: HTTP ' . $response->status());
        }

        $atomeStatus = strtoupper((string) $response->json('status'));
        $status      = $this->mapStatus($atomeStatus);

        return [
            'status' => $status,
            'reason' => in_array($status, ['failed', 'cancelled'], true) ? ('Atome status ' . $atomeStatus) : null,
        ];
    }

    /**
     * Verify the callback's X-Signature when a shared secret is configured.
     * Format (hex vs base64) isn't stated in the spec, so accept either. When
     * no secret is set we skip (still safe — status is re-fetched from the API).
     */
    private function assertSignatureIsValid(Request $request): void
    {
        $secret = config('services.atome.callback_secret');

        if (blank($secret)) {
            Log::warning('Atome callback secret not configured — X-Signature not verified (status is still re-fetched from Atome).');
            return;
        }

        $received = (string) $request->header('X-Signature');
        $hex      = hash_hmac('sha256', $request->getContent(), $secret);
        $base64   = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));

        if (! hash_equals($hex, $received) && ! hash_equals($base64, $received)) {
            Log::warning('Atome callback signature mismatch.', ['ip' => $request->ip()]);
            throw new GatewayException('Atome signature verification failed.');
        }
    }

    /**
     * Atome statuses: PROCESSING (pending), PAID/REFUNDED (paid), FAILED,
     * CANCELLED. Anything unrecognised stays pending (never falsely paid).
     */
    private function mapStatus(string $status): string
    {
        return match ($status) {
            'PAID', 'REFUNDED' => 'paid',
            'FAILED'           => 'failed',
            'CANCELLED'        => 'cancelled',
            default            => 'pending',
        };
    }

    /**
     * Best-effort Malaysian phone → E.164 (Atome requires mobileNumber in E.164).
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

    /**
     * Atome requires a postcode, but our checkout collects a single address
     * line — pull a 5-digit code out of it if present, else a placeholder.
     */
    private function postcodeFrom(?string $address): string
    {
        if (preg_match('/\b(\d{5})\b/', (string) $address, $m)) {
            return $m[1];
        }

        return '00000';
    }
}
