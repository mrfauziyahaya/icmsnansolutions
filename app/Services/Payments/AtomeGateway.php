<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Atome (BNPL) — direct merchant integration.
 *
 * Docs: https://doc.apaylater.com/v2/ (portal is JS-gated; spec not readable publicly)
 *
 * AWAITING SPEC + CREDENTIALS. Atome v2 signs requests (HMAC) and requires partner
 * onboarding. The signing scheme must be taken from their spec rather than guessed,
 * so this driver refuses to run until completed.
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
        throw new GatewayException(
            'Atome integration is not implemented yet — awaiting v2 API spec and partner credentials.'
        );
    }

    public function verifyCallback(Request $request): array
    {
        throw new GatewayException(
            'Atome callback verification is not implemented yet — awaiting API spec.'
        );
    }
}
