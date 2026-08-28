<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class UpdateClientExpiryStatus extends Command
{
    protected $signature = 'clients:update-expiry-status';
    protected $description = 'Promote client status to Expiring at 30 days out, and to Expired once past expiry_date';

    public function handle(): void
    {
        $expiring = Client::where('status', 'Active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->update(['status' => 'Expiring']);

        $expired = Client::whereIn('status', ['Active', 'Expiring'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->update(['status' => 'Expired']);

        $this->info("Promoted {$expiring} client(s) to Expiring, {$expired} to Expired.");
    }
}
