<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\Payments\GatewayException;
use App\Services\Payments\LendaPayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Lenda Pay (Direct Lending) driver — verifies token caching, the /create
 * request shape (amount in sen, merchant_order_id), and the webhook signature
 * scheme (t=<epoch>,v1=<hex>). Lenda Pay itself is never called; the point is
 * to prove the signature is internally consistent and a forged/stale callback
 * is rejected (fail-closed), and that the webhook poke is never trusted for
 * status — it must always trigger a GET /status re-fetch.
 */
class LendaPayGatewayTest extends TestCase
{
    use RefreshDatabase;

    private const ACCESS_KEY_ID = 'nan-sandbox';
    private const SECRET_KEY    = 'l8Plsmd1l7KWmc6CIM9BAOMQcdAKebSM';
    private const WEBHOOK_SECRET = 'whsec_nkd8yInFYZXhghIQCU33SJAgGL2wZkZuopow5kXH';
    private const BASE_URL      = 'https://localdev.directlending.com.my:8443/ecommerce/v1/e-commerce';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.sites.nansolutions.gateways.lendapay.config', [
            'access_key_id'     => self::ACCESS_KEY_ID,
            'secret_access_key' => self::SECRET_KEY,
            'base_url'          => self::BASE_URL,
            'webhook_secret'    => self::WEBHOOK_SECRET,
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
            'amount'      => 1200.00,
            'currency'    => 'MYR',
            'gateway'     => 'lendapay',
            'status'      => 'pending',
        ]);
    }

    private function fakeToken(): array
    {
        return [
            self::BASE_URL . '/auth/token' => Http::response([
                'accessToken' => 'fake-jwt-token',
                'tokenType'   => 'Bearer',
                'expiresIn'   => 900,
            ], 200),
        ];
    }

    public function test_it_is_configured_when_keys_present(): void
    {
        $this->assertTrue(app(LendaPayGateway::class)->isConfigured());
    }

    public function test_create_payment_sends_amount_in_sen_and_returns_redirect_url(): void
    {
        Http::fake($this->fakeToken() + [
            self::BASE_URL . '/create' => Http::response([
                'merchant_order_id' => 'PAY-2026-0001',
                'checkout_id'       => 'chk_9f2a',
                'redirectUrl'       => 'https://www.directlending.com.my/ecommerce/pay/chk_9f2a',
                'status'            => 'PROCESSING',
            ], 200),
        ]);

        $payment = $this->makePayment();
        $url     = app(LendaPayGateway::class)->createPayment($payment);

        $this->assertSame('https://www.directlending.com.my/ecommerce/pay/chk_9f2a', $url);
        $this->assertSame('https://www.directlending.com.my/ecommerce/pay/chk_9f2a', $payment->fresh()->checkout_url);
        $this->assertSame('chk_9f2a', $payment->fresh()->gateway_reference);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/create')) {
                return true;
            }

            return $request->hasHeader('Authorization', 'Bearer fake-jwt-token')
                && $request['merchant_order_id'] === 'PAY-2026-0001'
                && $request['amount'] === 120000
                && $request['currency'] === 'MYR'
                && $request['customerInfo']['mobileNumber'] === '+60123456789';
        });
    }

    public function test_token_is_fetched_once_and_reused_across_calls(): void
    {
        Http::fake($this->fakeToken() + [
            self::BASE_URL . '/create' => Http::response([
                'checkout_id' => 'chk_1', 'redirectUrl' => 'https://x/pay/chk_1', 'status' => 'PROCESSING',
            ], 200),
            self::BASE_URL . '/status*' => Http::response(['status' => 'PROCESSING'], 200),
        ]);

        $gateway = app(LendaPayGateway::class);
        $payment = $this->makePayment();

        $gateway->createPayment($payment);
        $gateway->getStatus($payment);

        Http::assertSentCount(3); // 1 token fetch + /create + /status, token cached across both

        $tokenCalls = 0;
        foreach (Http::recorded() as [$request, $response]) {
            if (str_contains($request->url(), '/auth/token')) {
                $tokenCalls++;
            }
        }
        $this->assertSame(1, $tokenCalls);
    }

    public function test_create_payment_throws_when_lenda_pay_rejects(): void
    {
        Http::fake($this->fakeToken() + [
            self::BASE_URL . '/create' => Http::response(['code' => 'AMOUNT_NOT_VALID', 'message' => 'amount must be between 3000 and 1000000 sen'], 400),
        ]);

        $this->expectException(GatewayException::class);

        app(LendaPayGateway::class)->createPayment($this->makePayment());
    }

    public function test_get_status_maps_paid(): void
    {
        Http::fake($this->fakeToken() + [
            self::BASE_URL . '/status*' => Http::response(['status' => 'PAID'], 200),
        ]);

        $result = app(LendaPayGateway::class)->getStatus($this->makePayment());

        $this->assertSame('paid', $result['status']);
        $this->assertNull($result['reason']);
    }

    public function test_get_status_maps_cancelled(): void
    {
        Http::fake($this->fakeToken() + [
            self::BASE_URL . '/status*' => Http::response(['status' => 'CANCELLED'], 200),
        ]);

        $result = app(LendaPayGateway::class)->getStatus($this->makePayment());

        $this->assertSame('cancelled', $result['status']);
        $this->assertNotNull($result['reason']);
    }

    public function test_verify_callback_is_only_a_poke_and_refetches_status(): void
    {
        Http::fake($this->fakeToken() + [
            self::BASE_URL . '/status*' => Http::response(['status' => 'PAID'], 200),
        ]);

        $request = $this->signedWebhook(['merchant_order_id' => 'PAY-2026-0001', 'referenceId' => 'PAY-2026-0001']);

        $result = app(LendaPayGateway::class)->verifyCallback($request);

        $this->assertSame('PAY-2026-0001', $result['reference']);
        $this->assertSame('paid', $result['status']);
    }

    public function test_verify_callback_rejects_forged_signature(): void
    {
        $request = $this->signedWebhook(
            ['merchant_order_id' => 'PAY-2026-0001', 'referenceId' => 'PAY-2026-0001'],
            secret: 'wrong-secret',
        );

        $this->expectException(GatewayException::class);

        app(LendaPayGateway::class)->verifyCallback($request);
    }

    public function test_verify_callback_rejects_stale_timestamp(): void
    {
        $request = $this->signedWebhook(
            ['merchant_order_id' => 'PAY-2026-0001', 'referenceId' => 'PAY-2026-0001'],
            timestamp: time() - 600, // outside the 300s window
        );

        $this->expectException(GatewayException::class);

        app(LendaPayGateway::class)->verifyCallback($request);
    }

    public function test_verify_callback_rejects_tampered_body(): void
    {
        $signed = $this->signedWebhook(['merchant_order_id' => 'PAY-2026-0001', 'referenceId' => 'PAY-2026-0001']);
        $sig    = $signed->header('X-Signature');

        $tampered = Request::create(
            '/webhooks/payments/lendapay',
            'POST',
            [], [], [],
            ['HTTP_X_SIGNATURE' => $sig, 'CONTENT_TYPE' => 'application/json'],
            json_encode(['merchant_order_id' => 'PAY-2026-9999', 'referenceId' => 'PAY-2026-9999']),
        );

        $this->expectException(GatewayException::class);

        app(LendaPayGateway::class)->verifyCallback($tampered);
    }

    private function signedWebhook(array $payload, ?string $secret = null, ?int $timestamp = null): Request
    {
        $secret = $secret ?? self::WEBHOOK_SECRET;
        $t      = $timestamp ?? time();
        $body   = json_encode($payload);
        $v1     = hash_hmac('sha256', $t . '.' . $body, $secret);

        return Request::create(
            '/webhooks/payments/lendapay',
            'POST',
            [], [], [],
            [
                'HTTP_X_SIGNATURE' => "t={$t},v1={$v1}",
                'CONTENT_TYPE'     => 'application/json',
            ],
            $body,
        );
    }
}
