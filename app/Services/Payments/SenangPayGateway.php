<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * senangPay — runs on DOKU's unified Payment API.
 *
 * Docs: https://doku-developers.apidog.io/ (DOKU) and https://developer.senangpay.my/
 *
 * AWAITING SPEC + CREDENTIALS. Both senangPay's classic Open API and DOKU's unified
 * API sign requests with an HMAC hash whose field order must come from the spec —
 * guessing it would silently accept forged callbacks, so this driver refuses to run
 * until completed.
 */
class SenangPayGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.senangpay.merchant_id'))
            && filled(config('services.senangpay.secret_key'))
            && filled(config('services.senangpay.base_url'));
    }

    public function createPayment(Payment $payment): string
    {
        throw new GatewayException(
            'senangPay integration is not implemented yet — awaiting DOKU API spec and merchant credentials.'
        );
    }

    public function verifyCallback(Request $request): array
    {
        throw new GatewayException(
            'senangPay callback verification is not implemented yet — awaiting API spec.'
        );
    }

    public function getStatus(Payment $payment): array
    {
        throw new GatewayException(
            'senangPay status query is not implemented yet — awaiting API spec.'
        );
    }
}
