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
 * Flow: POST a signed request to /checkout/v1/payment (DOKU Checkout — the
 * unified hosted page listing every channel enabled on the account: FPX,
 * cards, GrabPay / ShopeePay / Touch 'n Go, BNPL). DOKU returns a hosted-page
 * URL; the result comes back as a browser redirect to callback_url plus an
 * HTTP notification we verify by recomputing the same HMAC signature.
 *
 * (The card-only /credit-card/v1/payment-page endpoint renders just a card
 * form, so it isn't used — Checkout is what shows FPX & e-wallets.)
 *
 * Signature (non-SNAP), confirmed verbatim from DOKU docs:
 *   digest    = base64( sha256(rawBody) )                    // omit for GET
 *   component = "Client-Id:{id}\nRequest-Id:{rid}\nRequest-Timestamp:{ts}\nRequest-Target:{path}\nDigest:{digest}"
 *   signature = "HMACSHA256=" . base64( hmac_sha256(component, secretKey) )
 */
class SenangPayGateway implements PaymentGateway
{
    private const PAYMENT_PATH = '/checkout/v1/payment';

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

        $amount = round((float) $payment->amount, 2);

        $body = [
            'order' => [
                'amount'         => $amount,
                'invoice_number' => $payment->reference,
                'currency'       => $payment->currency,
                // DOKU Checkout redirects the buyer here after payment; the
                // authoritative result still arrives via the signed webhook.
                'callback_url'   => route('pay.success', ['reference' => $payment->reference]),
                'line_items'     => [[
                    'name'     => 'Pembayaran ' . $payment->reference,
                    'quantity' => 1,
                    'price'    => $amount,
                ]],
            ],
            'payment' => [
                'payment_due_date' => 60,   // minutes before the checkout link expires
            ],
            'customer' => [
                'id'      => $payment->reference,
                'name'    => $payment->payer_name,
                'email'   => $payment->payer_email,
                'phone'   => $payment->payer_phone,
                'address' => $payment->address,
                'country' => 'MY',
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

        // DOKU Checkout returns the hosted-page URL under payment.url.
        $url = $response->json('payment.url')
            ?? $response->json('response.payment.url')
            ?? $response->json('checkout.payment.url');

        if (! $url) {
            throw new GatewayException('senangPay did not return a payment page URL.');
        }

        $payment->update([
            'checkout_url'      => $url,
            'gateway_reference' => $response->json('payment.token_id') ?? $payment->gateway_reference,
        ]);

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

        // DOKU sends the Signature as raw base64 (no "HMACSHA256=" prefix) on
        // notifications, though its request docs show the prefix — accept either.
        $rawSignature = base64_encode(hash_hmac('sha256', $component, $secretKey, true));
        $expected     = 'HMACSHA256=' . $rawSignature;
        $received     = (string) $request->header('Signature');

        if (! hash_equals($expected, $received) && ! hash_equals($rawSignature, $received)) {
            // TEMPORARY (production diagnosis): DOKU is delivering notifications
            // but our recompute doesn't match. Log every input so the difference
            // can be identified, then trim this back to a plain warning.
            // Still fail-closed — a mismatch is always rejected.
            Log::warning('senangPay (DOKU) signature verification failed.', [
                'ip'                => $request->ip(),
                'received'          => $received,
                'expected'          => $expected,
                'expected_noprefix' => $rawSignature,
                'client_id_header'  => $request->header('Client-Id'),
                'client_id_config'  => config('services.senangpay.client_id'),
                'request_id'        => $request->header('Request-Id'),
                'request_timestamp' => $request->header('Request-Timestamp'),
                'digest_header'     => $request->header('Digest'),
                'digest_computed'   => $digest,
                'path_info'         => $request->getPathInfo(),
                'request_uri'       => $request->getRequestUri(),
                'full_url'          => $request->fullUrl(),
                'content_type'      => $request->header('Content-Type'),
                'all_headers'       => array_keys($request->headers->all()),
                'raw_body'          => $rawBody,
            ]);
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
        // DOKU's Check Status endpoint spec isn't available, so senangPay can't
        // self-query: the signed HTTP notification (verifyCallback) is the only
        // source of truth. Report "unchanged" rather than throwing — the
        // reconcile cron runs every 10 minutes and an exception here logged a
        // warning per stuck payment per tick, drowning out real errors.
        //
        // Consequence: a missed notification leaves a payment pending until it
        // is resolved by hand. Wire this up properly once DOKU supplies the spec.
        return ['status' => $payment->status, 'reason' => null];
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
