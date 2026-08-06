<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CHIP Collect.
 *
 * Docs: https://docs.chip-in.asia/chip-collect/api-reference/purchases/create
 * Flow: POST /purchases/ -> checkout_url -> payer redirected -> success_callback webhook.
 */
class ChipGateway implements PaymentGateway, SiteAwareGateway
{
    use Concerns\ResolvesSiteCredentials;

    protected function gatewayKey(): string
    {
        return 'chip';
    }

    public function isConfigured(): bool
    {
        return filled($this->cfg('api_key')) && filled($this->cfg('brand_id'));
    }

    public function createPayment(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('CHIP is not configured.');
        }

        // CHIP takes the amount in the minor unit (cents).
        $amountInCents = (int) round($payment->amount * 100);

        $body = [
            'brand_id' => $this->cfg('brand_id'),
            'client'   => [
                'email'      => $payment->payer_email,
                'full_name'  => $payment->payer_name,
                'phone'      => $payment->payer_phone,
            ],
            'purchase' => [
                'currency' => $payment->currency,
                'products' => [[
                    'name'     => 'Pembayaran ' . $payment->reference,
                    'price'    => $amountInCents,
                    'quantity' => 1,
                ]],
            ],
            'reference'        => $payment->reference,
            'success_redirect' => route('pay.success', ['reference' => $payment->reference]),
            'failure_redirect' => route('pay.failed', ['reference' => $payment->reference]),
            'cancel_redirect'  => route('pay.failed', ['reference' => $payment->reference]),
            'success_callback' => route('pay.webhook', ['gateway' => 'chip']),
        ];

        // The buyer already chose FPX or card on our form, so pin CHIP's hosted
        // page to that category instead of showing the full method picker.
        $whitelist = $this->methodWhitelist($payment->method);
        if ($whitelist !== null) {
            $body['payment_method_whitelist'] = $whitelist;
        }

        $response = Http::withToken($this->cfg('api_key'))
            ->acceptJson()
            ->post(rtrim($this->cfg('base_url'), '/') . '/purchases/', $body);

        if (! $response->successful()) {
            $error = $response->json('message') ?? $response->body();
            Log::error("CHIP createPayment failed for {$payment->reference}: {$error}");
            throw new GatewayException('CHIP: ' . (is_string($error) ? $error : 'request failed'));
        }

        $checkoutUrl = $response->json('checkout_url');

        if (! $checkoutUrl) {
            throw new GatewayException('CHIP did not return a checkout_url.');
        }

        $payment->update([
            'gateway_reference' => $response->json('id'),
            'checkout_url'      => $checkoutUrl,
        ]);

        return $checkoutUrl;
    }

    /**
     * Map our stored method to CHIP's payment_method identifiers. Null means no
     * restriction (show the full picker). Identifiers confirmed against CHIP's
     * live payment-methods list for this account.
     *
     * @return array<int, string>|null
     */
    private function methodWhitelist(?string $method): ?array
    {
        return match ($method) {
            'fpx'   => ['fpx', 'fpx_b2b1'],
            'card'  => ['visa', 'mastercard', 'maestro'],
            default => null,
        };
    }

    public function getStatus(Payment $payment): array
    {
        if (! $payment->gateway_reference) {
            return ['status' => 'pending', 'reason' => 'No gateway reference to query.'];
        }

        $response = Http::withToken($this->cfg('api_key'))
            ->acceptJson()
            ->get(rtrim($this->cfg('base_url'), '/') . '/purchases/' . $payment->gateway_reference . '/');

        if (! $response->successful()) {
            throw new GatewayException('CHIP getStatus failed: ' . $response->status());
        }

        // CHIP purchase status values: created, paid, refunded, cancelled, expired, error, ...
        $chipStatus = $response->json('status');

        $status = match ($chipStatus) {
            'paid', 'refunded'              => 'paid',
            'cancelled'                     => 'cancelled',
            'expired', 'error', 'blocked'   => 'failed',
            default                         => 'pending',   // created / pending / hold
        };

        return [
            'status' => $status,
            'reason' => $status === 'failed' || $status === 'cancelled' ? 'CHIP status: ' . $chipStatus : null,
        ];
    }

    public function verifyCallback(Request $request): array
    {
        $this->assertSignatureIsValid($request);

        // CHIP POSTs the Purchase object. Match on our own reference, never on the amount.
        $status = $request->input('status');

        return [
            'reference'         => $request->input('reference'),
            'gateway_reference' => $request->input('id'),
            'status'            => $status === 'paid' ? 'paid' : 'failed',
            'reason'            => $status === 'paid' ? null : 'CHIP status: ' . $status,
        ];
    }

    /**
     * CHIP signs the raw body with its private key and sends the base64 signature
     * in X-Signature. Without this check, anyone who knows the webhook URL could
     * POST a fake "paid" and mark an unpaid order as settled.
     */
    private function assertSignatureIsValid(Request $request): void
    {
        $publicKey = $this->cfg('webhook_public_key');

        if (blank($publicKey)) {
            throw new GatewayException('CHIP webhook public key is not configured — refusing to trust callback.');
        }

        $signature = $request->header('X-Signature');

        if (blank($signature)) {
            throw new GatewayException('CHIP webhook is missing the X-Signature header.');
        }

        $key = openssl_pkey_get_public($publicKey);

        if ($key === false) {
            throw new GatewayException('CHIP webhook public key is invalid.');
        }

        $verified = openssl_verify(
            $request->getContent(),
            base64_decode($signature, true) ?: '',
            $key,
            OPENSSL_ALGO_SHA256
        );

        if ($verified !== 1) {
            Log::warning('CHIP webhook signature verification failed.', ['ip' => $request->ip()]);
            throw new GatewayException('CHIP webhook signature is invalid.');
        }
    }
}
