<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send WhatsApp expiry reminders daily at 9am
Schedule::command('whatsapp:send-expiry-reminders')->dailyAt('09:00');
