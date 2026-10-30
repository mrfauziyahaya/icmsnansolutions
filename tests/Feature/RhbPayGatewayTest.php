<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\Payments\RhbPayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * RHB Pay (Assisted Host) driver — the request JWT (HS256, RequestKey), the
 * signed handoff page that form-POSTs to RHB, Direct (JSON) and Indirect
 * (browser form) callbacks verified with the ResponseKey, and the enquiry
 * API. RHB itself is never called; tokens are minted here with the same keys.
 */
class RhbPayGatewayTest extends TestCase
{
    use RefreshDatabase;

    private const COLLECTION_ID = 'MERCHANTTEST01';
    private const APP_KEY       = 'appkey123';
    private const REQUEST_KEY   = 'request-secret';
    private const RESPONSE_KEY  = 'response-secret';
    private const BASE_URL      = 'https://gw.rhbpayuat.rhbgroup.com';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.sites.nansolutions.gateways.rhb.config', [
            'collection_id' => self::COLLECTION_ID,
            'app_key'       => self::APP_KEY,
            'request_key'   => self::REQUEST_KEY,
            'response_key'  => self::RESPONSE_KEY,
            'base_url'      => self::BASE_URL,
        ]);
    }

    private function makePayment(array $attrs = []): Payment
    {
        return Payment::create(array_merge([
            'reference'   => 'PAY-2026-0001',
            'site'        => 'nansolutions',
            'payer_name'  => "AHMAD A/L ALI O'BRIEN",
            'payer_email' => 'ahmad@example.com',
            'payer_phone' => '012-345 6789',
            'address'     => 'No 1, Jalan Test',
            'amount'      => 100.00,
            'currency'    => 'MYR',
            'gateway'     => 'rhb',
            'status'      => 'pending',
        ], $attrs));
    }

    private static function b64url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    /** Mint a JWT the way RHB signs its responses. */
    private function responseToken(array $data, string $key = self::RESPONSE_KEY): string
    {
        $input = self::b64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']))
            . '.' . self::b64url(json_encode(['sub' => self::COLLECTION_ID, 'key' => self::APP_KEY, 'data' => $data]));

        return $input . '.' . self::b64url(hash_hmac('sha256', $input, $key, true));
    }

    private function decodeRequestToken(string $jwt): array
    {
        [$h, $p, $s] = explode('.', $jwt);
        $this->assertSame(self::b64url(hash_hmac('sha256', "{$h}.{$p}", self::REQUEST_KEY, true)), $s);

        return json_decode(base64_decode(strtr($p, '-_', '+/')), true);
    }

    private function directFields(string $status = 'RP00', array $overrides = []): array
    {
        return array_merge([
            'rp_collectionId'   => self::COLLECTION_ID,
            'rp_orderId'        => 'PAY-2026-0001',
            'rp_currency'       => 'MYR',
            'rp_amount'         => '100.00',
            'rp_statusCode'     => $status,
            'rp_transactionRef' => '2602121309867435',
            'rp_txnTime'        => '20260214143423',
            'rp_paymentMode'    => 'FPX',
            'rp_remarks'        => 'Transaction Success',
        ], $overrides);
    }

    private function boundData(string $status = 'RP00'): array
    {
        return ['orderId' => 'PAY-2026-0001', 'amount' => '100.00', 'currency' => 'MYR', 'statusCode' => $status, 'transactionRef' => '2602121309867435'];
    }

    public function test_it_is_configured_when_keys_present(): void
    {
        $this->assertTrue(app(RhbPayGateway::class)->isConfigured());

        config()->set('sites.sites.nansolutions.gateways.rhb.config.response_key', null);
        $this->assertFalse(app(RhbPayGateway::class)->isConfigured());
    }

    public function test_create_payment_returns_a_signed_handoff_url(): void
    {
        $payment = $this->makePayment();
        $url     = app(RhbPayGateway::class)->createPayment($payment);

        $this->assertStringContainsString('/pay/handoff/PAY-2026-0001', $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertSame($url, $payment->fresh()->checkout_url);
    }

    public function test_handoff_page_posts_a_signed_form_to_rhb(): void
    {
        $payment = $this->makePayment();
        $url     = app(RhbPayGateway::class)->createPayment($payment);

        $response = $this->get($url)->assertOk();
        $response->assertSee('action="' . self::BASE_URL . '/api/v1/gateway/payment"', false);

        $form = app(RhbPayGateway::class)->checkoutForm($payment);
        $f    = $form['fields'];

        $this->assertSame(self::COLLECTION_ID, $f['collectionId']);
        $this->assertSame('100.00', $f['amount']);
        $this->assertSame('AHMAD AL ALI OBRIEN', $f['buyerName']);   // disallowed chars stripped
        $this->assertSame('60123456789', $f['buyerPhone']);
        $this->assertSame(rawurlencode(route('pay.webhook', ['gateway' => 'rhb'])), $f['returnURL']);

        $claims = $this->decodeRequestToken($f['token']);
        $this->assertSame(self::COLLECTION_ID, $claims['sub']);
        $this->assertSame(self::APP_KEY, $claims['key']);
        $this->assertSame(60, $claims['exp'] - $claims['iat']);
        $this->assertSame('PAY-2026-0001', $claims['data']['orderId']);
        $this->assertSame('100.00', $claims['data']['amount']);
    }

    public function test_handoff_without_a_valid_signature_is_refused(): void
    {
        $this->makePayment();

        $this->get('/pay/handoff/PAY-2026-0001')->assertForbidden();
    }

    public function test_handoff_for_a_settled_payment_goes_to_the_result_page(): void
    {
        $payment = $this->makePayment(['status' => 'paid']);
        $url     = app(RhbPayGateway::class)->createPayment($payment);

        $this->get($url)->assertRedirect(route('pay.success', ['reference' => 'PAY-2026-0001']));
    }

    public function test_direct_message_with_a_bound_token_settles_the_payment(): void
    {
        $this->makePayment();

        $this->withToken($this->responseToken($this->boundData('RP00')))
            ->postJson('/webhooks/payments/rhb', $this->directFields('RP00'))
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $payment = Payment::first();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('2602121309867435', $payment->gateway_reference);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_direct_message_signed_with_the_wrong_key_is_rejected(): void
    {
        $this->makePayment();

        $this->withToken($this->responseToken($this->boundData('RP00'), 'wrong-key'))
            ->postJson('/webhooks/payments/rhb', $this->directFields('RP00'))
            ->assertStatus(400);

        $this->assertSame('pending', Payment::first()->status);
    }

    public function test_an_edited_status_that_disagrees_with_the_token_is_rejected(): void
    {
        $this->makePayment();

        // Genuine token for a failed attempt, body edited to claim success.
        $this->withToken($this->responseToken($this->boundData('RP01')))
            ->postJson('/webhooks/payments/rhb', $this->directFields('RP00'))
            ->assertStatus(400);

        $this->assertSame('pending', Payment::first()->status);
    }

    public function test_an_amount_that_differs_from_the_stored_one_is_rejected(): void
    {
        $this->makePayment();

        $this->withToken($this->responseToken($this->boundData('RP00')))
            ->postJson('/webhooks/payments/rhb', $this->directFields('RP00', ['rp_amount' => '1.00']))
            ->assertStatus(400);

        $this->assertSame('pending', Payment::first()->status);
    }

    public function test_a_token_that_does_not_bind_the_status_falls_back_to_enquiry(): void
    {
        $this->makePayment();

        Http::fake([
            self::BASE_URL . '/api/v1/gateway/enquiry*' => Http::response($this->directFields('RP01', ['rp_remarks' => 'Failed']), 200),
        ]);

        // Body claims success, but the token only carries the order id.
        $this->withToken($this->responseToken(['orderId' => 'PAY-2026-0001']))
            ->postJson('/webhooks/payments/rhb', $this->directFields('RP00'))
            ->assertOk();

        $payment = Payment::first();
        $this->assertSame('failed', $payment->status);
        $this->assertSame('RP01 Failed', $payment->failure_reason);
    }

    public function test_browser_return_settles_and_redirects_to_the_result_page(): void
    {
        $this->makePayment();

        $fields = $this->directFields('RP00') + ['rp_token' => $this->responseToken($this->boundData('RP00'))];

        $this->post('/webhooks/payments/rhb', $fields)
            ->assertRedirect(route('pay.success', ['reference' => 'PAY-2026-0001']));

        $this->assertSame('paid', Payment::first()->status);
    }

    public function test_a_forged_browser_return_redirects_to_failed_without_settling(): void
    {
        $this->makePayment();

        $fields = $this->directFields('RP00') + ['rp_token' => 'not.a.jwt'];

        $this->post('/webhooks/payments/rhb', $fields)
            ->assertRedirect(route('pay.failed', ['reference' => 'PAY-2026-0001']));

        $this->assertSame('pending', Payment::first()->status);
    }

    public function test_buyer_cancelled_maps_to_cancelled(): void
    {
        $this->makePayment();

        $this->withToken($this->responseToken($this->boundData('RP04')))
            ->postJson('/webhooks/payments/rhb', $this->directFields('RP04', ['rp_remarks' => 'Buyer Cancelled']))
            ->assertOk();

        $this->assertSame('cancelled', Payment::first()->status);
    }

    public function test_enquiry_sends_a_signed_token_and_maps_status(): void
    {
        $payment = $this->makePayment();

        Http::fake([
            self::BASE_URL . '/api/v1/gateway/enquiry*' => Http::response($this->directFields('RP00'), 200),
        ]);

        $this->assertSame('paid', app(RhbPayGateway::class)->getStatus($payment)['status']);

        Http::assertSent(function ($request) {
            $claims = $this->decodeRequestToken(substr($request->header('Authorization')[0], 7));

            return str_contains($request->url(), 'orderId=PAY-2026-0001')
                && $claims['data'] === ['OrderId' => 'PAY-2026-0001'];
        });
    }

    public function test_enquiry_reads_a_data_envelope_too(): void
    {
        $payment = $this->makePayment();

        Http::fake([
            self::BASE_URL . '/api/v1/gateway/enquiry*' => Http::response(['code' => 'RP00', 'data' => $this->directFields('RP41')], 200),
        ]);

        $this->assertSame('pending', app(RhbPayGateway::class)->getStatus($payment)['status']);
    }

    public function test_enquiry_service_unavailable_leaves_status_unchanged(): void
    {
        $payment = $this->makePayment();

        Http::fake([
            self::BASE_URL . '/api/v1/gateway/enquiry*' => Http::response([
                'rp_orderId' => 'PAY-2026-0001', 'rp_statusCode' => 'RP503', 'rp_remarks' => 'Enquiry Service Unavailable, try later',
            ], 200),
        ]);

        $this->assertSame('pending', app(RhbPayGateway::class)->getStatus($payment)['status']);
    }

    public function test_not_found_is_failed_only_after_the_grace_period(): void
    {
        Http::fake([
            self::BASE_URL . '/api/v1/gateway/enquiry*' => Http::response(['code' => 'RP05', 'message' => 'Transaction Not Found'], 404),
        ]);

        $fresh = $this->makePayment();
        $this->assertSame('pending', app(RhbPayGateway::class)->getStatus($fresh)['status']);

        $old = $this->makePayment(['reference' => 'PAY-2026-0002']);
        $old->forceFill(['created_at' => now()->subHours(2)])->save();
        $this->assertSame('failed', app(RhbPayGateway::class)->getStatus($old)['status']);
    }
}
