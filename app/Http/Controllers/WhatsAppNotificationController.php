<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppNotification;

class WhatsAppNotificationController extends Controller
{
    public function index()
    {
        $notifications = WhatsAppNotification::with('client')
            ->latest('sent_at')
            ->paginate(25);

        return view('whatsapp.index', compact('notifications'));
    }
}
