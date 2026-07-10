<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\WhatsAppNotification;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendExpiryReminders extends Command
{
    protected $signature   = 'whatsapp:send-expiry-reminders';
    protected $description = 'Send WhatsApp expiry reminders at 30 days, 14 days and 3 days before policy expiry';

    public function handle(WhatsAppService $wa): void
    {
        $targets = [
            'expiry_30d' => now()->addDays(30)->toDateString(),
            'expiry_14d' => now()->addDays(14)->toDateString(),
            'expiry_3d'  => now()->addDays(3)->toDateString(),
        ];

        foreach ($targets as $type => $date) {
            $clients = Client::whereDate('expiry_date', $date)
                ->whereIn('status', ['Active', 'Expiring'])
                ->get();

            foreach ($clients as $client) {
                // Skip if already sent today
                $alreadySent = WhatsAppNotification::where('client_id', $client->client_id)
                    ->where('type', $type)
                    ->whereDate('sent_at', today())
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $wa->sendExpiryReminder($client, $type);
                $this->info("Sent {$type} reminder to {$client->name} ({$client->plate})");
            }
        }

        $this->info('Expiry reminders done.');
    }
}
