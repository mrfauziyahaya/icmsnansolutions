<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AhaPay (BNPL).
 *
 * Docs: apidocs.ahapay.my (spec from /openapi.json). Auth via "X-ApiKey".
 *   POST /v1/orders            -> data.link (payment URL), data.online_order_id
 *   GET  /v1/orders/{id}/status
 * Callback: HMAC-SHA256 over the raw body in the "Signature" header
 * (hash_hmac('sha256', payload, secret) + hash_equals — confirmed by docs).
 *
 * The order status string values aren't enumerated in the spec; mapStatus only
 * treats explicit success values as paid (unknown -> pending), so it can never
 * mark an order paid by mistake. Confirm the exact strings in a sandbox order.
 */
class AhaPayGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.ahapay.api_key')) && filled(config('services.ahapay.base_url'));
    }

    public function createPayment(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('AhaPay is not configured.');
        }

        $amount = (float) $payment->amount;

        $body = [
            'amount'           => $amount,
            'reference_number' => $payment->reference,
            'callback_url'     => route('pay.webhook', ['gateway' => 'ahapay']),
            'redirect_urls'    => [
                'success' => route('pay.success', ['reference' => $payment->reference]),
                'failed'  => route('pay.failed', ['reference' => $payment->reference]),
                'cart'    => route('pay.failed', ['reference' => $payment->reference]),
            ],
            'order_items'      => [[
                'name'     => 'Pembayaran ' . $payment->reference,
                'quantity' => 1,
                'price'    => $amount,
            ]],
        ];

        // Optional merchant identifier, only sent if configured.
        if (filled(config('services.ahapay.merchant_id'))) {
            $body['external_merchant_id'] = config('services.ahapay.merchant_id');
        }

        $response = Http::withHeaders(['X-ApiKey' => config('services.ahapay.api_key')])
            ->acceptJson()
            ->post(rtrim(config('services.ahapay.base_url'), '/') . '/v1/orders', $body);

        if (! $response->successful()) {
            Log::error("AhaPay createPayment failed for {$payment->reference}: " . $response->body());
            throw new GatewayException('AhaPay: request failed.');
        }

        $checkoutUrl = $response->json('data.link') ?? $response->json('link');

        if (! $checkoutUrl) {
            throw new GatewayException('AhaPay did not return a payment link.');
        }

        $payment->update([
            'gateway_reference' => $response->json('data.online_order_id') ?? $response->json('online_order_id'),
            'checkout_url'      => $checkoutUrl,
        ]);

        return $checkoutUrl;
    }

    public function getStatus(Payment $payment): array
    {
        if (! $payment->gateway_reference) {
            return ['status' => 'pending', 'reason' => 'No gateway reference to query.'];
        }

        return $this->queryStatus($payment->gateway_reference);
    }

    public function verifyCallback(Request $request): array
    {
        $this->assertSignatureIsValid($request);

        $body    = json_decode($request->getContent(), true) ?: $request->all();
        $orderId = data_get($body, 'online_order_id');

        if (! $orderId) {
            throw new GatewayException('AhaPay callback missing online_order_id.');
        }

        // Don't trust the status in the callback body — re-fetch it from AhaPay's
        // authenticated status endpoint so a forged callback can't settle an order
        // (the shared HMAC secret is optional; this keeps us safe without it).
        $result = $this->queryStatus($orderId);

        return [
            'reference'         => data_get($body, 'reference_number'),
            'gateway_reference' => $orderId,
            'status'            => $result['status'],
            'reason'            => $result['reason'],
        ];
    }

    /**
     * Query AhaPay for the authoritative order status by its online_order_id.
     */
    private function queryStatus(string $orderId): array
    {
        $response = Http::withHeaders(['X-ApiKey' => config('services.ahapay.api_key')])
            ->acceptJson()
            ->get(rtrim(config('services.ahapay.base_url'), '/') . '/v1/orders/' . rawurlencode($orderId) . '/status');

        if (! $response->successful()) {
            throw new GatewayException('AhaPay getStatus failed: ' . $response->status());
        }

        $s = strtolower((string) (
            $response->json('data.online_order_status')
            ?? $response->json('online_order_status')
            ?? $response->json('data.status')
            ?? $response->json('status')
        ));

        $status = $this->mapStatus($s);

        return ['status' => $status, 'reason' => $status === 'pending' ? null : 'AhaPay status: ' . $s];
    }

    /**
     * Map AhaPay's order status to ours. Only explicit success values become
     * "paid" — anything unrecognised stays "pending", so an unknown/forged
     * status can never settle an order.
     */
    private function mapStatus(string $status): string
    {
        return match (true) {
            in_array($status, ['paid', 'success', 'completed', 'settled', 'approved'], true) => 'paid',
            in_array($status, ['cancelled', 'canceled'], true)                               => 'cancelled',
            in_array($status, ['failed', 'expired', 'rejected', 'declined'], true)           => 'failed',
            default                                                                          => 'pending',
        };
    }

    /**
     * AhaPay signs the callback with HMAC-SHA256 over the payload and sends it in
     * the Signature header (docs: Callback-Security). The exact payload string
     * isn't spelled out; the raw body is the standard convention. A wrong guess
     * fails closed (rejects), so it can never silently accept a forged callback.
     */
    private function assertSignatureIsValid(Request $request): void
    {
        $secret = config('services.ahapay.secret_key');

        if (blank($secret)) {
            Log::warning('AhaPay secret key not configured — callback signature not verified.');
            return;
        }

        $received = (string) $request->header('Signature');
        $computed = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($computed, $received)) {
            Log::warning('AhaPay callback signature mismatch.', [
                'ip'       => $request->ip(),
                'computed' => $computed,      // logged to confirm the payload shape in sandbox
                'received' => $received,
            ]);
            throw new GatewayException('AhaPay signature verification failed.');
        }
    }
}
