<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * senangPay — runs on DOKU's Malaysia unified Payment API.
 *
 * Docs: doku-developers.apidog.io + developers.doku.com (signature scheme).
 *
 * Flow: POST a signed request to /credit-card/v1/payment-page; DOKU returns a
 * hosted-page URL (buyer picks FPX / card / e-wallet there). Result comes back
 * as a browser redirect to callback_url/failed_url plus an HTTP notification we
 * verify by recomputing the same HMAC signature.
 *
 * Signature (non-SNAP), confirmed verbatim from DOKU docs:
 *   digest    = base64( sha256(rawBody) )                    // omit for GET
 *   component = "Client-Id:{id}\nRequest-Id:{rid}\nRequest-Timestamp:{ts}\nRequest-Target:{path}\nDigest:{digest}"
 *   signature = "HMACSHA256=" . base64( hmac_sha256(component, secretKey) )
 */
class SenangPayGateway implements PaymentGateway
{
    private const PAYMENT_PATH = '/credit-card/v1/payment-page';

    public function isConfigured(): bool
    {
        return filled(config('services.senangpay.client_id'))
            && filled(config('services.senangpay.secret_key'))
            && filled(config('services.senangpay.base_url'));
    }

    public function createPayment(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new GatewayException('senangPay (DOKU) is not configured.');
        }

        $body = [
            'order' => [
                'amount'         => round((float) $payment->amount, 2),
                'invoice_number' => $payment->reference,
                'currency'       => $payment->currency,
                'callback_url'   => route('pay.success', ['reference' => $payment->reference]),
                'failed_url'     => route('pay.failed', ['reference' => $payment->reference]),
                'auto_redirect'  => true,
                // DOKU caps the statement descriptor length (~20 chars), so use
                // just the reference (e.g. PAY-2026-0009 = 13 chars).
                'descriptor'     => substr($payment->reference, 0, 20),
            ],
            'customer' => [
                'id'      => $payment->reference,
                'name'    => $payment->payer_name,
                'email'   => $payment->payer_email,
                'phone'   => $payment->payer_phone,
                'address' => $payment->address,
                'country' => 'MY',
            ],
            'payment' => [
                'type' => 'SALE',
            ],
        ];

        $json    = json_encode($body);
        $headers = $this->signedHeaders(self::PAYMENT_PATH, $json);

        $response = Http::withHeaders($headers)
            ->withBody($json, 'application/json')
            ->post(rtrim(config('services.senangpay.base_url'), '/') . self::PAYMENT_PATH);

        if (! $response->successful()) {
            $err = $response->json('error.message') ?? $response->body();
            Log::error("senangPay createPayment failed for {$payment->reference}: " . (is_string($err) ? $err : json_encode($err)));
            throw new GatewayException('senangPay: ' . (is_string($err) ? $err : 'request failed'));
        }

        // DOKU returns the hosted-page URL under credit_card_payment_page.url
        // (the generic hosted page where the buyer chooses the method).
        $url = $response->json('credit_card_payment_page.url')
            ?? $response->json('payment.url')
            ?? $response->json('checkout.payment.url');

        if (! $url) {
            throw new GatewayException('senangPay did not return a payment page URL.');
        }

        $payment->update(['checkout_url' => $url]);

        return $url;
    }

    public function verifyCallback(Request $request): array
    {
        $secretKey = config('services.senangpay.secret_key');
        $rawBody   = $request->getContent();

        // DOKU signs its notification with the same scheme, using the path it
        // POSTs to on our server as the Request-Target. Recompute and compare.
        $digest = base64_encode(hash('sha256', $rawBody, true));

        $component = "Client-Id:" . $request->header('Client-Id') . "\n"
            . "Request-Id:" . $request->header('Request-Id') . "\n"
            . "Request-Timestamp:" . $request->header('Request-Timestamp') . "\n"
            . "Request-Target:" . $request->getPathInfo() . "\n"
            . "Digest:" . $digest;

        $expected = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $component, $secretKey, true));
        $received = (string) $request->header('Signature');

        if (! hash_equals($expected, $received)) {
            Log::warning('senangPay (DOKU) signature verification failed.', ['ip' => $request->ip()]);
            throw new GatewayException('senangPay signature verification failed.');
        }

        $body = json_decode($rawBody, true) ?: [];

        $invoice = data_get($body, 'order.invoice_number') ?? data_get($body, 'invoice_number');
        $status  = strtoupper((string) (
            data_get($body, 'transaction.status')
            ?? data_get($body, 'status')
            ?? data_get($body, 'order.status')
        ));

        $mapped = $this->mapStatus($status);

        return [
            'reference'         => $invoice,
            'gateway_reference' => data_get($body, 'transaction.original_request_id')
                ?? data_get($body, 'transaction.id')
                ?? data_get($body, 'order.invoice_number'),
            'status'            => $mapped,
            'reason'            => $mapped === 'failed' ? ('DOKU status ' . $status) : null,
        ];
    }

    public function getStatus(Payment $payment): array
    {
        // DOKU's Transaction/Check Status endpoint spec isn't wired yet. The
        // signed HTTP notification (verifyCallback) is the source of truth, and
        // DOKU sends one even for EXPIRED, so pending payments still resolve.
        throw new GatewayException('senangPay status query is not implemented yet — awaiting Check Status API spec.');
    }

    /**
     * Build the DOKU signed request headers for a POST with a JSON body.
     */
    private function signedHeaders(string $path, string $jsonBody): array
    {
        $clientId  = config('services.senangpay.client_id');
        $secretKey = config('services.senangpay.secret_key');
        $requestId = (string) Str::uuid();
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');   // ISO-8601 UTC, e.g. 2020-10-21T03:38:28Z
        $digest    = base64_encode(hash('sha256', $jsonBody, true));

        $component = "Client-Id:{$clientId}\n"
            . "Request-Id:{$requestId}\n"
            . "Request-Timestamp:{$timestamp}\n"
            . "Request-Target:{$path}\n"
            . "Digest:{$digest}";

        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $component, $secretKey, true));

        return [
            'Client-Id'         => $clientId,
            'Request-Id'        => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature'         => $signature,
            'Content-Type'      => 'application/json',
        ];
    }

    /**
     * DOKU status values: SUCCESS/REFUNDED = paid, FAILED/EXPIRED = failed,
     * everything else (PENDING, REDIRECT, WAITING, INITIATE) = still pending.
     */
    private function mapStatus(string $status): string
    {
        return match ($status) {
            'SUCCESS', 'REFUNDED'  => 'paid',
            'FAILED', 'EXPIRED'    => 'failed',
            default                => 'pending',
        };
    }
}
