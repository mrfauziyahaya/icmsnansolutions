<?php

namespace App\Services\Payments;

use App\Models\Payment;

/**
 * A gateway whose checkout must be entered by a browser form POST rather than
 * a plain redirect. createPayment() returns a URL to our own handoff page,
 * which renders checkoutForm() and auto-submits it to the provider.
 */
interface FormPostGateway
{
    /**
     * Build the form fresh at render time (signed tokens may be short-lived).
     *
     * @return array{action: string, fields: array<string, string>}
     * @throws GatewayException on failure
     */
    public function checkoutForm(Payment $payment): array;
}
