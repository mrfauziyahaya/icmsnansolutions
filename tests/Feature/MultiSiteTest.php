<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\SiteManager;
use App\Services\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * One app, two domains (nansolutions.com.my + reniu.my) sharing one payments
 * table. These cover the parts that would silently misbehave: which site a
 * host maps to, which credentials/labels a driver picks up, which routes each
 * domain exposes, and — most importantly — that a webhook delivered to one
 * domain can never settle another site's payment.
 */
class MultiSiteTest extends TestCase
{
    use RefreshDatabase;

    private function sites(): SiteManager
    {
        return app(SiteManager::class);
    }

    /** Give reniu a full set of fake credentials so its gateways count as configured. */
    private function configureReniuGateways(): void
    {
        config()->set('sites.sites.reniu.gateways.atome.config', [
            'partner_id'      => 'reniu-partner',
            'secret_key'      => 'reniu-secret',
            'base_url'        => 'https://atome.test/v2',
            'callback_secret' => null,
        ]);
        config()->set('sites.sites.reniu.gateways.chip.config', [
            'api_key'            => 'reniu-chip-key',
            'brand_id'           => 'reniu-brand',
            'base_url'           => 'https://chip.test/api/v1',
            'webhook_public_key' => null,
        ]);
    }

    // ── Site resolution ──────────────────────────────────────────────────────

    public function test_hosts_map_to_their_site(): void
    {
        $this->assertSame('nansolutions', $this->sites()->keyForHost('nansolutions.com.my'));
        $this->assertSame('nansolutions', $this->sites()->keyForHost('icms.nansolutions.com.my'));
        $this->assertSame('reniu', $this->sites()->keyForHost('reniu.my'));
        $this->assertSame('reniu', $this->sites()->keyForHost('www.reniu.my'));
        $this->assertSame('reniu', $this->sites()->keyForHost('RENIU.MY:8080'), 'host match should ignore case and port');
    }

    public function test_unknown_host_is_not_claimed_by_any_site(): void
    {
        $this->assertNull($this->sites()->keyForHost('somewhere-else.test'));
    }

    // ── Per-site configuration ───────────────────────────────────────────────

    public function test_gateway_labels_differ_per_site(): void
    {
        $this->assertSame('Fiuu', $this->sites()->gatewayLabel('fiuu', 'nansolutions'));
        $this->assertSame('SPayLater', $this->sites()->gatewayLabel('fiuu', 'reniu'));

        $this->assertSame('CHIP', $this->sites()->gatewayLabel('chip', 'nansolutions'));
        $this->assertSame('Credit Card', $this->sites()->gatewayLabel('chip', 'reniu'));
    }

    public function test_drivers_resolve_their_own_sites_credentials(): void
    {
        $this->configureReniuGateways();
        config()->set('sites.sites.nansolutions.gateways.chip.config.api_key', 'nan-chip-key');

        $manager = app(PaymentGatewayManager::class);

        $this->assertSame('reniu', $manager->driver('chip', 'reniu')->site());
        $this->assertSame('nansolutions', $manager->driver('chip', 'nansolutions')->site());

        $this->assertSame('reniu-chip-key', $this->sites()->gatewayConfig('chip', 'api_key', null, 'reniu'));
        $this->assertSame('nan-chip-key', $this->sites()->gatewayConfig('chip', 'api_key', null, 'nansolutions'));
    }

    public function test_reniu_checkout_only_offers_its_own_gateways(): void
    {
        $this->configureReniuGateways();

        $labels = array_column(app(PaymentGatewayManager::class)->checkoutOptions(null, 'reniu'), 'label');

        $this->assertContains('Kad Kredit / Debit', $labels, 'reniu CHIP is card-only');
        $this->assertNotContains('CHIP — FPX (Maybank, CIMB, dll.)', $labels, 'FPX is a NAN Solutions option');
    }

    public function test_turnstile_keys_are_per_site(): void
    {
        config()->set('sites.sites.reniu.turnstile.site_key', 'reniu-site');
        config()->set('sites.sites.reniu.turnstile.secret_key', 'reniu-secret');
        config()->set('sites.sites.nansolutions.turnstile.site_key', 'nan-site');

        $turnstile = app(TurnstileService::class);

        $this->assertSame('reniu-site', $turnstile->siteKey('reniu'));
        $this->assertSame('nan-site', $turnstile->siteKey('nansolutions'));
    }

    public function test_reference_prefix_is_per_site(): void
    {
        $this->assertStringStartsWith('RNU-', Payment::nextReference('reniu'));
        $this->assertStringStartsWith('PAY-', Payment::nextReference('nansolutions'));
    }

    // ── Route exposure ───────────────────────────────────────────────────────

    public function test_reniu_exposes_the_checkout(): void
    {
        $this->get('http://reniu.my/pay')->assertOk();
    }

    public function test_reniu_root_redirects_to_its_checkout(): void
    {
        $this->get('http://reniu.my/')->assertRedirect(route('pay.create'));
    }

    public function test_reniu_does_not_expose_nansolutions_pages(): void
    {
        $this->get('http://reniu.my/privacy-policy')->assertNotFound();
        $this->get('http://reniu.my/lookup')->assertNotFound();
        $this->get('http://reniu.my/login')->assertNotFound();
    }

    public function test_nansolutions_still_exposes_everything(): void
    {
        $this->get('http://nansolutions.com.my/')->assertOk();
        $this->get('http://nansolutions.com.my/privacy-policy')->assertOk();
        $this->get('http://nansolutions.com.my/pay')->assertOk();
    }

    // ── Webhook isolation (the security-critical bit) ─────────────────────────

    public function test_webhook_settles_a_payment_on_its_own_site(): void
    {
        $this->configureReniuGateways();
        Http::fake(['atome.test/*' => Http::response(['status' => 'PAID'], 200)]);

        $payment = $this->makePayment('reniu', 'RNU-2026-9001', 'atome');

        $this->postJson('http://reniu.my/webhooks/payments/atome', ['referenceId' => $payment->reference])
            ->assertOk();

        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_webhook_on_one_domain_cannot_settle_another_sites_payment(): void
    {
        $this->configureReniuGateways();
        Http::fake(['atome.test/*' => Http::response(['status' => 'PAID'], 200)]);

        // Payment belongs to NAN Solutions, but the callback arrives on reniu.my.
        $payment = $this->makePayment('nansolutions', 'PAY-2026-9002', 'atome');

        $this->postJson('http://reniu.my/webhooks/payments/atome', ['referenceId' => $payment->reference])
            ->assertStatus(409);

        $this->assertSame('pending', $payment->fresh()->status, 'a cross-site callback must not settle the payment');
    }

    private function makePayment(string $site, string $reference, string $gateway): Payment
    {
        return Payment::create([
            'reference'   => $reference,
            'site'        => $site,
            'payer_name'  => 'TEST PAYER',
            'payer_email' => 'test@example.com',
            'payer_phone' => '0123456789',
            'address'     => 'No 1, Jalan Test',
            'postcode'    => '50000',
            'amount'      => 100.00,
            'currency'    => 'MYR',
            'gateway'     => $gateway,
            'status'      => 'pending',
        ]);
    }
}
