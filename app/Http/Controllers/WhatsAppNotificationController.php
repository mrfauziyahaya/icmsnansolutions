<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppNotification;
use Illuminate\Http\Request;

class WhatsAppNotificationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $notifications = WhatsAppNotification::with('client')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('recipient_phone', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhereHas('client', function ($c) use ($search) {
                          $c->where('name', 'like', "%{$search}%")
                            ->orWhere('plate', 'like', "%{$search}%");
                      });
                });
            })
            ->latest('sent_at')
            ->paginate(25)
            ->withQueryString();

        return view('whatsapp.index', compact('notifications', 'search'));
    }
}
