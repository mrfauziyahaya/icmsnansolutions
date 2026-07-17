<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AhaPay (BNPL).
 *
 * Docs: https://apidocs.ahapay.my/docs/api/Introduction
 * Known: auth via "X-ApiKey" header; POST /v1/orders returns a payment link.
 *
 * NOT VERIFIED: exact request body, callback payload and signature scheme.
 * Confirm against their docs before enabling in production.
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

    public function verifyCallback(Request $request): array
    {
        // TODO: verify signature per AhaPay's Callback-Security section before trusting.
        $status = strtolower((string) $request->input('status'));

        return [
            'reference'         => $request->input('order_id'),
            'gateway_reference' => $request->input('id'),
            'status'            => in_array($status, ['paid', 'success', 'completed']) ? 'paid' : 'failed',
            'reason'            => in_array($status, ['paid', 'success', 'completed']) ? null : 'AhaPay status: ' . $status,
        ];
    }
}
