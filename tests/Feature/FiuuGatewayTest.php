<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\Payments\FiuuGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fiuu (Razer) hosted-page request building — the vcode signature and the
 * optional channel whitelist. Fiuu itself is never called; the URL it builds
 * is the whole contract.
 */
class FiuuGatewayTest extends TestCase
{
    use RefreshDatabase;

    private const MERCHANT = 'myreniuagency';
    private const VERIFY   = '89be2f13b528b72b870f054be6ce467f';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.sites.nansolutions.gateways.fiuu.config', [
            'merchant_id'         => self::MERCHANT,
            'verify_key'          => self::VERIFY,
            'secret_key'          => 'secret',
            'sandbox'             => false,
            'vcode_with_currency' => false,
        ]);
    }

    private function payment(?string $method = null): Payment
    {
        return Payment::create([
            'reference'   => 'RNU-2026-0002',
            'payer_name'  => 'MUHAMAD FAUZI BIN YAHAYA',
            'payer_email' => 'test@example.com',
            'payer_phone' => '0194639163',
            'address'     => 'No 1, Jalan Test',
            'amount'      => 100.00,
            'currency'    => 'MYR',
            'gateway'     => 'fiuu',
            'method'      => $method,
            'status'      => 'pending',
        ]);
    }

    public function test_without_a_method_the_hosted_page_shows_all_channels(): void
    {
        $url = app(FiuuGateway::class)->createPayment($this->payment());

        // No channel segment: /RMS/pay/{merchant}/?...
        $this->assertStringContainsString('/RMS/pay/' . self::MERCHANT . '/?', $url);
    }

    public function test_a_selected_method_pins_the_channel_in_the_url_path(): void
    {
        $url = app(FiuuGateway::class)->createPayment($this->payment('ShopeePay'));

        // Channel segment present: /RMS/pay/{merchant}/ShopeePay?...
        $this->assertStringContainsString('/RMS/pay/' . self::MERCHANT . '/ShopeePay?', $url);
    }

    /** The channel must not change the vcode — signing is over amount/merchant/order/key only. */
    public function test_the_channel_does_not_affect_the_vcode(): void
    {
        parse_str(parse_url(app(FiuuGateway::class)->createPayment($this->payment('ShopeePay')), PHP_URL_QUERY), $q);

        // vcode is over amount + merchant + orderid + verify key — no channel.
        $expected = md5('100.00' . self::MERCHANT . 'RNU-2026-0002' . self::VERIFY);

        $this->assertSame($expected, $q['vcode']);
    }
}
