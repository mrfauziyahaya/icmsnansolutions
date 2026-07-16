<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Fiuu (formerly Razer Merchant Services).
 *
 * Docs: https://github.com/FiuuPayment/Documentation-Fiuu_API_Spec
 *
 * AWAITING SPEC + CREDENTIALS. Fiuu's spec is distributed as a PDF (v13.90); the
 * request signature (vcode) and return verification (skey) must be implemented
 * exactly as specified there — guessing the hash order would silently accept
 * forged callbacks, so this driver intentionally refuses to run until completed.
 */
class FiuuGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.fiuu.merchant_id'))
            && filled(config('services.fiuu.verify_key'))
            && filled(config('services.fiuu.secret_key'));
    }

    public function createPayment(Payment $payment): string
    {
        throw new GatewayException(
            'Fiuu integration is not implemented yet — awaiting API spec (v13.90) and merchant credentials.'
        );
    }

    public function verifyCallback(Request $request): array
    {
        throw new GatewayException(
            'Fiuu callback verification is not implemented yet — awaiting API spec.'
        );
    }
}
