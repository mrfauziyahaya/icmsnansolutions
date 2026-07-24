<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\WhatsAppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The payment_received WhatsApp.
 *
 * This is an internal alert: it tells the admin that money arrived. It shipped
 * pointing at the payer's own number by mistake, so every successful payer got
 * a message meant for staff. These lock the recipient down.
 */
class PaymentWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = '60123456789';
    private const PAYER = '0176295764';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.whatsapp', [
            'phone_number_id' => '1234567890',
            'access_token'    => 'test-token',
            'admin_number'    => self::ADMIN,
        ]);

        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);
    }

    private function pendingPayment(): Payment
    {
        return Payment::create([
            'reference'   => 'PAY-2026-0025',
            'payer_name'  => 'NURAZLINDA BINTI OTHMAN',
            'payer_email' => 'azlynn35@example.com',
            'payer_phone' => self::PAYER,
            'address'     => 'No 1, Jalan Test',
            'amount'      => 1002.12,
            'currency'    => 'MYR',
            'gateway'     => 'senangpay',
            'status'      => 'pending',
        ]);
    }

    public function test_it_notifies_the_admin_and_never_the_payer(): void
    {
        $this->pendingPayment()->update(['status' => 'paid', 'paid_at' => now()]);

        Http::assertSent(function ($request) {
            $to = $request['to'];

            // The payer's number must not appear in any form.
            $this->assertNotSame(self::PAYER, $to);
            $this->assertNotSame('6' . ltrim(self::PAYER, '0'), $to);

            return $to === self::ADMIN
                && $request['template']['name'] === 'payment_received';
        });
    }

    /** {{1}} name, {{2}} amount, {{3}} payment method — as the template expects. */
    public function test_it_sends_the_payer_name_amount_and_method_as_parameters(): void
    {
        $this->pendingPayment()->update(['status' => 'paid', 'paid_at' => now()]);

        Http::assertSent(function ($request) {
            $values = array_column($request['template']['components'][0]['parameters'], 'text');

            return $values === ['NURAZLINDA BINTI OTHMAN', '1002.12', 'Grab PayLater'];
        });
    }

    public function test_the_log_records_the_admin_as_recipient(): void
    {
        $this->pendingPayment()->update(['status' => 'paid', 'paid_at' => now()]);

        $log = WhatsAppNotification::where('type', 'payment_received')->sole();

        $this->assertSame(self::ADMIN, $log->recipient_phone);
        $this->assertSame('sent', $log->status);
    }

    /** Only the transition to paid fires it — not every save on a paid payment. */
    public function test_it_fires_once_and_only_on_becoming_paid(): void
    {
        $payment = $this->pendingPayment();

        $payment->update(['status' => 'paid', 'paid_at' => now()]);
        $payment->update(['failure_reason' => 'touched again']);

        $this->assertSame(1, WhatsAppNotification::where('type', 'payment_received')->count());
    }

    public function test_a_payment_that_fails_sends_nothing(): void
    {
        $this->pendingPayment()->update(['status' => 'failed']);

        Http::assertNothingSent();
        $this->assertSame(0, WhatsAppNotification::count());
    }
}
