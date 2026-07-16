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
class ChipGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.chip.api_key')) && filled(config('services.chip.brand_id'));
    }

    public function createPayment(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('CHIP is not configured.');
        }

        // CHIP takes the amount in the minor unit (cents).
        $amountInCents = (int) round($payment->amount * 100);

        $response = Http::withToken(config('services.chip.api_key'))
            ->acceptJson()
            ->post(rtrim(config('services.chip.base_url'), '/') . '/purchases/', [
                'brand_id' => config('services.chip.brand_id'),
                'client'   => [
                    'email'      => $payment->payer_email,
                    'full_name'  => $payment->payer_name,
                    'phone'      => $payment->payer_phone,
                ],
                'purchase' => [
                    'currency' => $payment->currency,
                    'products' => [[
                        'name'     => $payment->purposeLabel() . ' - ' . $payment->vehicle_plate,
                        'price'    => $amountInCents,
                        'quantity' => 1,
                    ]],
                ],
                'reference'        => $payment->reference,
                'success_redirect' => route('pay.success', ['reference' => $payment->reference]),
                'failure_redirect' => route('pay.failed', ['reference' => $payment->reference]),
                'cancel_redirect'  => route('pay.failed', ['reference' => $payment->reference]),
                'success_callback' => route('pay.webhook', ['gateway' => 'chip']),
            ]);

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

    public function verifyCallback(Request $request): array
    {
        // CHIP POSTs the Purchase object. Match on our own reference, never on the amount.
        $reference = $request->input('reference');
        $status    = $request->input('status');

        return [
            'reference'         => $reference,
            'gateway_reference' => $request->input('id'),
            'status'            => $status === 'paid' ? 'paid' : 'failed',
            'reason'            => $status === 'paid' ? null : 'CHIP status: ' . $status,
        ];
    }
}
