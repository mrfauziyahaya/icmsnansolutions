<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Create the payment at the provider and return the URL to redirect the payer to.
     * Should also stamp $payment->gateway_reference / checkout_url.
     *
     * @throws GatewayException on failure
     */
    public function createPayment(Payment $payment): string;

    /**
     * Interpret an incoming webhook.
     *
     * Must verify authenticity (signature/hash) before trusting anything, and must
     * NOT trust an amount echoed by the payload — reconcile against the stored row.
     *
     * @return array{reference: string|null, status: string, gateway_reference: string|null, reason: string|null}
     */
    public function verifyCallback(Request $request): array;

    /**
     * Whether the provider has enough config to be used.
     */
    public function isConfigured(): bool;
}
