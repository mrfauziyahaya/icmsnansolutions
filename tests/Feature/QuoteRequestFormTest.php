<?php

namespace Tests\Feature;

use App\Models\QuoteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Public "Sebut Harga" form — the required-field rules the browser enforces
 * (Perlindungan Tambahan, Jenis Pembayaran) must also hold server-side, so a
 * bypassed form can't store an incomplete request.
 */
class QuoteRequestFormTest extends TestCase
{
    use RefreshDatabase;

    private const COMPREHENSIVE = '1st Party Comprehensive';
    private const FIRE_THEFT    = '3rd Party Fire & Theft (Selain dari motorsikal)';
    private const MOTORCYCLE    = '3rd Party (Motorsikal sahaja)';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        // Turnstile verification (when configured) and the WhatsApp notice.
        Http::fake(['*' => Http::response(['success' => true])]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nama_pemilik'       => 'Ali bin Ahmad',
            'no_ic'              => '900101015555',
            'poskod'             => '50000',
            'no_plate'           => 'abc1234',
            'ehailing'           => 'Tidak',
            'tukar_milik'        => 'Tidak',
            'whatsapp'           => '0123456789',
            'jenis_perlindungan' => self::COMPREHENSIVE,
            'perlindungan_tambahan' => ['Bencana alam'],
            'jenis_pembayaran'   => 'Credit Card',
            // A non-blank token; the Http fake above approves it.
            'cf-turnstile-response' => 'test-token',
        ], $overrides);
    }

    public function test_comprehensive_with_an_addon_is_accepted(): void
    {
        $this->post(route('quote.store'), $this->payload())
            ->assertRedirect(route('quote.success'));

        $this->assertSame(['Bencana alam'], QuoteRequest::first()->perlindungan_tambahan);
    }

    public function test_comprehensive_without_an_addon_is_rejected(): void
    {
        $this->post(route('quote.store'), $this->payload(['perlindungan_tambahan' => []]))
            ->assertSessionHasErrors('perlindungan_tambahan');

        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_fire_and_theft_without_an_addon_is_rejected(): void
    {
        $data = $this->payload(['jenis_perlindungan' => self::FIRE_THEFT]);
        unset($data['perlindungan_tambahan']);

        $this->post(route('quote.store'), $data)
            ->assertSessionHasErrors('perlindungan_tambahan');

        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_fire_and_theft_with_a_choice_is_accepted(): void
    {
        $this->post(route('quote.store'), $this->payload([
            'jenis_perlindungan'    => self::FIRE_THEFT,
            'perlindungan_tambahan' => 'Tak Perlu Tambahan',
        ]))->assertRedirect(route('quote.success'));

        $this->assertSame(1, QuoteRequest::count());
    }

    public function test_motorcycle_third_party_needs_no_addon(): void
    {
        $data = $this->payload(['jenis_perlindungan' => self::MOTORCYCLE]);
        unset($data['perlindungan_tambahan']);

        $this->post(route('quote.store'), $data)
            ->assertRedirect(route('quote.success'));

        $this->assertSame(1, QuoteRequest::count());
    }

    public function test_payment_type_is_required(): void
    {
        $data = $this->payload();
        unset($data['jenis_pembayaran']);

        $this->post(route('quote.store'), $data)
            ->assertSessionHasErrors('jenis_pembayaran');

        $this->assertSame(0, QuoteRequest::count());
    }
}
