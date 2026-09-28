<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\Payments\ChipGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * CHIP Collect — verifies createPayment pins the hosted page to the right
 * payment_method_whitelist for each of our three checkout options (FPX,
 * card, SPayLater). CHIP itself is never called.
 *
 * CHIP has one combined channel, "shopee_pay", for both the ShopeePay
 * wallet and Shopee SPayLater (confirmed against the account's real
 * GET /payment_methods/ list) — there is no SPayLater-only code, so
 * whitelisting it still lets the payer land on ShopeePay too.
 */
class ChipGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.sites.nansolutions.gateways.chip.config', [
            'api_key'            => 'test-key',
            'brand_id'           => 'test-brand',
            'base_url'           => 'https://gate.chip-in.test/api/v1',
            'webhook_public_key' => null,
        ]);
    }

    private function makePayment(?string $method): Payment
    {
        return Payment::create([
            'reference'   => 'PAY-2026-0001',
            'payer_name'  => 'AHMAD BIN ALI',
            'payer_email' => 'ahmad@example.com',
            'payer_phone' => '0123456789',
            'address'     => 'No 1, Jalan Test, 50000 KL',
            'amount'      => 100.00,
            'currency'    => 'MYR',
            'gateway'     => 'chip',
            'method'      => $method,
            'status'      => 'pending',
        ]);
    }

    private function fakeChip(): void
    {
        Http::fake([
            'gate.chip-in.test/*' => Http::response([
                'id' => 'purchase_123',
                'checkout_url' => 'https://gate.chip-in.test/checkout/purchase_123',
            ], 200),
        ]);
    }

    public function test_fpx_whitelists_fpx_channels(): void
    {
        $this->fakeChip();
        app(ChipGateway::class)->createPayment($this->makePayment('fpx'));

        Http::assertSent(fn ($r) => $r['payment_method_whitelist'] === ['fpx', 'fpx_b2b1']);
    }

    public function test_card_whitelists_card_networks(): void
    {
        $this->fakeChip();
        app(ChipGateway::class)->createPayment($this->makePayment('card'));

        Http::assertSent(fn ($r) => $r['payment_method_whitelist'] === ['visa', 'mastercard', 'maestro']);
    }

    public function test_spaylater_whitelists_shopee_pay(): void
    {
        $this->fakeChip();
        app(ChipGateway::class)->createPayment($this->makePayment('spaylater'));

        Http::assertSent(fn ($r) => $r['payment_method_whitelist'] === ['shopee_pay']);
    }

    public function test_no_method_shows_the_full_picker(): void
    {
        $this->fakeChip();
        app(ChipGateway::class)->createPayment($this->makePayment(null));

        Http::assertSent(fn ($r) => ! array_key_exists('payment_method_whitelist', $r->data()));
    }
}
