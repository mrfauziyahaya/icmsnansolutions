<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature   = 'payments:reconcile {--minutes=10 : Only touch payments pending at least this long}';
    protected $description = 'Ask the gateway for the real status of stuck pending payments and update them';

    public function handle(): int
    {
        $cutoff = now()->subMinutes((int) $this->option('minutes'));

        // Give a fresh payment time to complete or fire its webhook before we poll.
        $pending = Payment::where('status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending payments to reconcile.');
            return self::SUCCESS;
        }

        $changed = 0;
        foreach ($pending as $payment) {
            if ($payment->reconcile()) {
                $changed++;
                $this->info("{$payment->reference}: pending -> {$payment->fresh()->status}");
            }
        }

        $this->info("Reconciled {$changed} of {$pending->count()} pending payment(s).");

        return self::SUCCESS;
    }
}
