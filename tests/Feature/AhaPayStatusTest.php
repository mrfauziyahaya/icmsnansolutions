<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\Payments\AhaPayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * AhaPay order-status mapping.
 *
 * A live successful order returns online_order_status = "PAYMENT_SUCCESSFUL",
 * which the old map didn't recognise — so it silently fell through to "pending"
 * and paid orders never settled. These lock in the real value and make sure an
 * unknown status is logged rather than swallowed.
 */
class AhaPayStatusTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://ahapay.test';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ahapay', [
            'api_key'    => 'test-key',
            'secret_key' => null,
            'base_url'   => self::BASE,
        ]);
    }

    private function payment(): Payment
    {
        return Payment::create([
            'reference'         => 'PAY-2026-0021',
            'payer_name'        => 'TEST PAYER',
            'payer_email'       => 'test@example.com',
            'payer_phone'       => '0123456789',
            'address'           => 'No 1, Jalan Test',
            'amount'            => 3314.00,
            'currency'          => 'MYR',
            'gateway'           => 'ahapay',
            'gateway_reference' => 'aBLFYXb3394N9qU4',
            'status'            => 'pending',
        ]);
    }

    private function fakeStatus(string $status): void
    {
        Http::fake([
            self::BASE . '/*' => Http::response([
                'data' => [
                    'online_order_status' => $status,
                    'reference_number'    => 'PAY-2026-0021',
                    'online_order_id'     => 'aBLFYXb3394N9qU4',
                ],
            ], 200),
        ]);
    }

    /** The exact string a live AhaPay order returns once paid. */
    public function test_payment_successful_is_treated_as_paid(): void
    {
        $this->fakeStatus('PAYMENT_SUCCESSFUL');

        $result = app(AhaPayGateway::class)->getStatus($this->payment());

        $this->assertSame('paid', $result['status']);
    }

    public function test_the_full_callback_settles_the_payment(): void
    {
        $this->fakeStatus('PAYMENT_SUCCESSFUL');
        $payment = $this->payment();

        $this->postJson('/webhooks/payments/ahapay', [
            'online_order_id'  => 'aBLFYXb3394N9qU4',
            'reference_number' => 'PAY-2026-0021',
            'status'           => 'PAYMENT_SUCCESSFUL',
        ])->assertOk();

        $this->assertSame('paid', $payment->fresh()->status);
    }

    /**
     * "approved" is AhaPay's BNPL credit-scoring flag, not payment — a live
     * paid order carries approved=true *and* PAYMENT_SUCCESSFUL. Treating it as
     * paid would settle an unpaid order, so it must stay pending.
     */
    public function test_approved_is_not_treated_as_paid(): void
    {
        $this->fakeStatus('APPROVED');

        $this->assertSame('pending', app(AhaPayGateway::class)->getStatus($this->payment())['status']);
    }

    public function test_failure_statuses_map_to_failed(): void
    {
        $this->fakeStatus('PAYMENT_FAILED');

        $this->assertSame('failed', app(AhaPayGateway::class)->getStatus($this->payment())['status']);
    }

    public function test_pending_statuses_stay_pending_without_warning(): void
    {
        Log::spy();
        $this->fakeStatus('PAYMENT_PENDING');

        $this->assertSame('pending', app(AhaPayGateway::class)->getStatus($this->payment())['status']);

        Log::shouldNotHaveReceived('warning');
    }

    /** An unknown status must never settle the order — and must be logged. */
    public function test_unknown_status_is_pending_and_logged(): void
    {
        Log::spy();
        $this->fakeStatus('SOME_BRAND_NEW_STATUS');

        $this->assertSame('pending', app(AhaPayGateway::class)->getStatus($this->payment())['status']);

        Log::shouldHaveReceived('warning')
            ->withArgs(fn ($message, $context = []) => str_contains($message, 'unrecognised order status')
                && ($context['status'] ?? null) === 'some_brand_new_status');
    }
}
