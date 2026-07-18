<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\Payments\GatewayException;
use App\Services\Payments\SenangPayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * senangPay (DOKU) driver — verifies the HMAC signing on the way out and the
 * callback verification on the way back. DOKU itself is never called; the point
 * is to prove the signature scheme is internally consistent and that a forged
 * callback is rejected (fail-closed), so a wrong "paid" can't slip through.
 */
class SenangPayGatewayTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT_ID = 'SNP-0075-1781019799848';
    private const SECRET     = 'SK-RcO4406lGQDPtt9UrLKT';
    private const BASE_URL   = 'https://api-sandbox.doku.com';
    private const PATH       = '/checkout/v1/payment';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.senangpay', [
            'client_id'  => self::CLIENT_ID,
            'secret_key' => self::SECRET,
            'base_url'   => self::BASE_URL,
        ]);
    }

    private function makePayment(): Payment
    {
        return Payment::create([
            'reference'   => 'PAY-2026-0001',
            'payer_name'  => 'AHMAD BIN ALI',
            'payer_email' => 'ahmad@example.com',
            'payer_phone' => '0123456789',
            'address'     => 'No 1, Jalan Test, 50000 KL',
            'amount'      => 200.00,
            'currency'    => 'MYR',
            'gateway'     => 'senangpay',
            'status'      => 'pending',
        ]);
    }

    public function test_it_is_configured_when_keys_present(): void
    {
        $this->assertTrue(app(SenangPayGateway::class)->isConfigured());
    }

    public function test_create_payment_returns_hosted_page_url_and_signs_the_request(): void
    {
        Http::fake([
            'api-sandbox.doku.com/*' => Http::response([
                'order'   => ['invoice_number' => 'PAY-2026-0001', 'amount' => '200.00'],
                'payment' => ['url' => 'https://sandbox.doku.com/checkout-link-v2/abc123', 'token_id' => 'tok-abc123'],
            ], 200),
        ]);

        $payment = $this->makePayment();
        $url     = app(SenangPayGateway::class)->createPayment($payment);

        $this->assertSame('https://sandbox.doku.com/checkout-link-v2/abc123', $url);
        $this->assertSame('https://sandbox.doku.com/checkout-link-v2/abc123', $payment->fresh()->checkout_url);

        // The Signature header must be exactly what DOKU's scheme prescribes for
        // the bytes we actually sent — recompute it from the captured request.
        Http::assertSent(function ($request) {
            $body   = $request->body();
            $cid    = $request->header('Client-Id')[0];
            $rid    = $request->header('Request-Id')[0];
            $ts     = $request->header('Request-Timestamp')[0];
            $sig    = $request->header('Signature')[0];
            $digest = base64_encode(hash('sha256', $body, true));

            $component = "Client-Id:{$cid}\n"
                . "Request-Id:{$rid}\n"
                . "Request-Timestamp:{$ts}\n"
                . "Request-Target:" . self::PATH . "\n"
                . "Digest:{$digest}";

            $expected = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $component, self::SECRET, true));

            return str_contains($request->url(), self::PATH)
                && $cid === self::CLIENT_ID
                && $sig === $expected
                && str_contains($body, '"invoice_number":"PAY-2026-0001"');
        });
    }

    public function test_create_payment_throws_when_doku_rejects(): void
    {
        Http::fake([
            'api-uat.doku.com/*' => Http::response(['error' => ['message' => 'Invalid client']], 401),
        ]);

        $this->expectException(GatewayException::class);

        app(SenangPayGateway::class)->createPayment($this->makePayment());
    }

    public function test_verify_callback_accepts_a_correctly_signed_success(): void
    {
        $request = $this->signedNotification([
            'order'       => ['invoice_number' => 'PAY-2026-0001', 'amount' => '200.00'],
            'transaction' => ['status' => 'SUCCESS', 'original_request_id' => 'txn-abc-123'],
        ]);

        $result = app(SenangPayGateway::class)->verifyCallback($request);

        $this->assertSame('PAY-2026-0001', $result['reference']);
        $this->assertSame('txn-abc-123', $result['gateway_reference']);
        $this->assertSame('paid', $result['status']);
        $this->assertNull($result['reason']);
    }

    public function test_verify_callback_maps_failed_status(): void
    {
        $request = $this->signedNotification([
            'order'       => ['invoice_number' => 'PAY-2026-0001'],
            'transaction' => ['status' => 'FAILED'],
        ]);

        $result = app(SenangPayGateway::class)->verifyCallback($request);

        $this->assertSame('failed', $result['status']);
        $this->assertNotNull($result['reason']);
    }

    public function test_verify_callback_treats_pending_as_pending(): void
    {
        $request = $this->signedNotification([
            'order'       => ['invoice_number' => 'PAY-2026-0001'],
            'transaction' => ['status' => 'PENDING'],
        ]);

        $result = app(SenangPayGateway::class)->verifyCallback($request);

        $this->assertSame('pending', $result['status']);
    }

    public function test_verify_callback_rejects_a_forged_signature(): void
    {
        // Correctly built body, but the signature is computed with the wrong key.
        $request = $this->signedNotification(
            ['order' => ['invoice_number' => 'PAY-2026-0001'], 'transaction' => ['status' => 'SUCCESS']],
            secret: 'SK-attacker-key',
        );

        $this->expectException(GatewayException::class);

        app(SenangPayGateway::class)->verifyCallback($request);
    }

    public function test_verify_callback_rejects_a_tampered_body(): void
    {
        // Sign the original amount, then swap the payload the receiver sees. The
        // digest no longer matches, so verification must fail.
        $signed  = ['order' => ['invoice_number' => 'PAY-2026-0001', 'amount' => '1.00'], 'transaction' => ['status' => 'SUCCESS']];
        $request = $this->signedNotification($signed);

        $tampered = json_encode(['order' => ['invoice_number' => 'PAY-2026-0001', 'amount' => '999.00'], 'transaction' => ['status' => 'SUCCESS']]);
        $forged   = Request::create(
            '/webhooks/payments/senangpay',
            'POST',
            [], [], [],
            $this->serverHeaders($request),
            $tampered,
        );

        $this->expectException(GatewayException::class);

        app(SenangPayGateway::class)->verifyCallback($forged);
    }

    /**
     * Build a Request carrying a DOKU-style notification signed exactly the way
     * the driver's verifier recomputes it (Request-Target = our webhook path).
     */
    private function signedNotification(array $payload, ?string $secret = null): Request
    {
        $secret = $secret ?? self::SECRET;
        $path   = '/webhooks/payments/senangpay';
        $body   = json_encode($payload);

        $clientId  = self::CLIENT_ID;
        $requestId = 'req-uuid-0001';
        $timestamp = '2026-07-18T03:38:28Z';
        $digest    = base64_encode(hash('sha256', $body, true));

        $component = "Client-Id:{$clientId}\n"
            . "Request-Id:{$requestId}\n"
            . "Request-Timestamp:{$timestamp}\n"
            . "Request-Target:{$path}\n"
            . "Digest:{$digest}";

        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $component, $secret, true));

        return Request::create($path, 'POST', [], [], [], [
            'HTTP_CLIENT_ID'         => $clientId,
            'HTTP_REQUEST_ID'        => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE'         => $signature,
            'CONTENT_TYPE'           => 'application/json',
        ], $body);
    }

    /**
     * Re-extract the header set from a signed request so a tampered-body variant
     * can reuse the original (now-stale) signature.
     */
    private function serverHeaders(Request $request): array
    {
        return [
            'HTTP_CLIENT_ID'         => $request->header('Client-Id'),
            'HTTP_REQUEST_ID'        => $request->header('Request-Id'),
            'HTTP_REQUEST_TIMESTAMP' => $request->header('Request-Timestamp'),
            'HTTP_SIGNATURE'         => $request->header('Signature'),
            'CONTENT_TYPE'           => 'application/json',
        ];
    }
}
