<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The private test link: /pay?preview=<token> reveals a hidden gateway to that
 * browser only, while everyone else still can't see or submit it.
 */
class PaymentPreviewLinkTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-preview-token';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.sites.nansolutions.gateways.rhb.config', [
            'collection_id' => 'MERCHANTTEST01',
            'app_key'       => 'appkey123',
            'request_key'   => 'request-secret',
            'response_key'  => 'response-secret',
            'base_url'      => 'https://gw.rhbpayuat.rhbgroup.com',
        ]);
        config()->set('services.payments.disabled', ['rhb']);
        config()->set('services.payments.preview_token', self::TOKEN);
        config()->set('services.payments.preview_gateways', ['rhb']);

        $this->mock(TurnstileService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
            $mock->shouldIgnoreMissing();
        });
    }

    private function checkout(array $overrides = [])
    {
        return $this->post('/pay', array_merge([
            'payer_name'  => 'Ahmad',
            'payer_email' => 'ahmad@example.com',
            'payer_phone' => '0123456789',
            'address'     => 'No 1, Jalan Test',
            'postcode'    => '50000',
            'amount'      => 100,
            'gateway'     => 'rhb',
        ], $overrides));
    }

    private function optionsOnPage(): array
    {
        return array_column($this->get('/pay')->assertOk()->viewData('options'), 'gateway');
    }

    public function test_hidden_gateway_is_not_offered_without_the_link(): void
    {
        $this->assertNotContains('rhb', $this->optionsOnPage());
    }

    public function test_hidden_gateway_cannot_be_submitted_without_the_link(): void
    {
        $this->checkout()->assertSessionHasErrors('gateway');

        $this->assertSame(0, Payment::count());
    }

    public function test_wrong_token_does_not_unlock(): void
    {
        $this->get('/pay?preview=wrong')->assertOk();

        $this->assertNotContains('rhb', $this->optionsOnPage());
    }

    public function test_empty_token_config_never_unlocks(): void
    {
        config()->set('services.payments.preview_token', '');

        $this->get('/pay?preview=')->assertOk();

        $this->assertNotContains('rhb', $this->optionsOnPage());
    }

    public function test_link_unlocks_the_browser_and_removes_the_token_from_the_url(): void
    {
        $this->get('/pay?preview=' . self::TOKEN)->assertRedirect('/pay');

        $this->assertContains('rhb', $this->optionsOnPage());
    }

    public function test_unlocked_browser_can_start_a_payment(): void
    {
        $this->get('/pay?preview=' . self::TOKEN);

        $this->checkout()->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('rhb', Payment::sole()->gateway);
    }

    public function test_link_only_reveals_the_listed_gateways(): void
    {
        config()->set('services.payments.preview_gateways', []);

        $this->get('/pay?preview=' . self::TOKEN);

        $this->assertNotContains('rhb', $this->optionsOnPage());
    }

    public function test_changing_the_token_locks_browsers_already_unlocked(): void
    {
        $this->get('/pay?preview=' . self::TOKEN);

        config()->set('services.payments.preview_token', 'rotated');

        $this->assertNotContains('rhb', $this->optionsOnPage());
    }
}
