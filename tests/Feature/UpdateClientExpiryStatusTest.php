<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UpdateClientExpiryStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeClient(?string $status, ?string $expiryDate): Client
    {
        return Client::create([
            'name' => 'Test Client',
            'inception_date' => now()->subYear(),
            'status' => $status,
            'expiry_date' => $expiryDate,
        ]);
    }

    public function test_active_client_within_30_days_becomes_expiring(): void
    {
        $client = $this->makeClient('Active', now()->addDays(20)->toDateString());

        $this->artisan('clients:update-expiry-status');

        $this->assertSame('Expiring', $client->fresh()->status);
    }

    public function test_active_client_beyond_30_days_stays_active(): void
    {
        $client = $this->makeClient('Active', now()->addDays(45)->toDateString());

        $this->artisan('clients:update-expiry-status');

        $this->assertSame('Active', $client->fresh()->status);
    }

    public function test_expiry_exactly_30_days_out_is_inclusive(): void
    {
        $client = $this->makeClient('Active', now()->addDays(30)->toDateString());

        $this->artisan('clients:update-expiry-status');

        $this->assertSame('Expiring', $client->fresh()->status);
    }

    public function test_expiry_today_becomes_expiring_not_expired(): void
    {
        $client = $this->makeClient('Active', now()->toDateString());

        $this->artisan('clients:update-expiry-status');

        $this->assertSame('Expiring', $client->fresh()->status);
    }

    public function test_active_client_past_expiry_becomes_expired(): void
    {
        $client = $this->makeClient('Active', now()->subDay()->toDateString());

        $this->artisan('clients:update-expiry-status');

        $this->assertSame('Expired', $client->fresh()->status);
    }

    public function test_expiring_client_past_expiry_becomes_expired(): void
    {
        $client = $this->makeClient('Expiring', now()->subDays(5)->toDateString());

        $this->artisan('clients:update-expiry-status');

        $this->assertSame('Expired', $client->fresh()->status);
    }

    public function test_already_expired_client_is_left_alone(): void
    {
        $client = $this->makeClient('Expired', now()->subDays(100)->toDateString());

        $this->artisan('clients:update-expiry-status');

        $this->assertSame('Expired', $client->fresh()->status);
    }

    public function test_client_with_null_expiry_date_is_untouched(): void
    {
        $client = $this->makeClient('Active', null);

        $this->artisan('clients:update-expiry-status');

        $this->assertSame('Active', $client->fresh()->status);
    }
}
