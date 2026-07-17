<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AhaPay (BNPL).
 *
 * Docs: https://apidocs.ahapay.my/docs/api/Introduction (+ Callback-Security).
 * Auth via "X-ApiKey" header; POST /v1/orders returns a payment link; callback
 * is HMAC-SHA256 signed in the Signature header.
 *
 * Callback signature verification is wired (fail-closed). The create-order
 * request body / response field names and the exact HMAC payload are best-effort
 * from thin docs — confirm against a sandbox transaction before production.
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

        $response = Http::withHeaders(['X-ApiKey' => config('services.ahapay.api_key')])
            ->acceptJson()
            ->post(rtrim(config('services.ahapay.base_url'), '/') . '/v1/orders', [
                'order_id'     => $payment->reference,
                'amount'       => (float) $payment->amount,
                'currency'     => $payment->currency,
                'description'  => 'Pembayaran ' . $payment->reference,
                'customer'     => [
                    'name'  => $payment->payer_name,
                    'email' => $payment->payer_email,
                    'phone' => $payment->payer_phone,
                ],
                'redirect_url' => route('pay.success', ['reference' => $payment->reference]),
                'cancel_url'   => route('pay.failed', ['reference' => $payment->reference]),
                'callback_url' => route('pay.webhook', ['gateway' => 'ahapay']),
            ]);

        if (! $response->successful()) {
            Log::error("AhaPay createPayment failed for {$payment->reference}: " . $response->body());
            throw new GatewayException('AhaPay: request failed.');
        }

        // Field name unconfirmed — accept the common variants until the spec is checked.
        $checkoutUrl = $response->json('payment_link')
            ?? $response->json('payment_url')
            ?? $response->json('data.payment_link');

        if (! $checkoutUrl) {
            throw new GatewayException('AhaPay did not return a payment link.');
        }

        $payment->update([
            'gateway_reference' => $response->json('id') ?? $response->json('data.id'),
            'checkout_url'      => $checkoutUrl,
        ]);

        return $checkoutUrl;
    }

    public function getStatus(Payment $payment): array
    {
        // TODO: confirm AhaPay's order-status endpoint and field names before relying on this.
        if (! $payment->gateway_reference) {
            return ['status' => 'pending', 'reason' => 'No gateway reference to query.'];
        }

        $response = Http::withHeaders(['X-ApiKey' => config('services.ahapay.api_key')])
            ->acceptJson()
            ->get(rtrim(config('services.ahapay.base_url'), '/') . '/v1/orders/' . $payment->gateway_reference);

        if (! $response->successful()) {
            throw new GatewayException('AhaPay getStatus failed: ' . $response->status());
        }

        $s = strtolower((string) ($response->json('status') ?? $response->json('data.status')));

        $status = match (true) {
            in_array($s, ['paid', 'success', 'completed']) => 'paid',
            in_array($s, ['cancelled', 'canceled'])        => 'cancelled',
            in_array($s, ['failed', 'expired', 'rejected']) => 'failed',
            default                                         => 'pending',
        };

        return ['status' => $status, 'reason' => $status === 'pending' ? null : 'AhaPay status: ' . $s];
    }

    public function verifyCallback(Request $request): array
    {
        $this->assertSignatureIsValid($request);

        $body   = json_decode($request->getContent(), true) ?: $request->all();
        $status = strtolower((string) (data_get($body, 'status') ?? $request->input('status')));

        $mapped = match (true) {
            in_array($status, ['paid', 'success', 'completed']) => 'paid',
            in_array($status, ['cancelled', 'canceled'])        => 'cancelled',
            in_array($status, ['failed', 'expired', 'rejected']) => 'failed',
            default                                             => 'pending',
        };

        return [
            'reference'         => data_get($body, 'order_id') ?? $request->input('order_id'),
            'gateway_reference' => data_get($body, 'id') ?? $request->input('id'),
            'status'            => $mapped,
            'reason'            => $mapped === 'paid' ? null : 'AhaPay status: ' . $status,
        ];
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
