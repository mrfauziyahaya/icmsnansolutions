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
     * Ask the provider for the current status of a payment (source of truth).
     *
     * Used to reconcile records stuck at "pending" — e.g. a payer who failed,
     * cancelled, or closed the tab, for which no webhook is sent.
     *
     * @return array{status: string, reason: string|null}
     *   status is one of: pending, paid, failed, cancelled.
     * @throws GatewayException on failure
     */
    public function getStatus(Payment $payment): array;

    /**
     * Whether the provider has enough config to be used.
     */
    public function isConfigured(): bool;
}
