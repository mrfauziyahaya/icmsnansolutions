<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\TurnstileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function __construct(private TurnstileService $turnstile) {}

    // ── Public ───────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'required|string|max:30',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if (! $this->turnstile->verify($request->input('cf-turnstile-response'), $request->ip())) {
            return back()
                ->withInput()
                ->withErrors(['captcha' => 'Pengesahan keselamatan gagal. Sila cuba lagi.'])
                ->with('contact_open', true);
        }

        $message = ContactMessage::create($validated + ['ip_address' => $request->ip()]);

        // Best-effort notification to the sales inbox.
        try {
            Mail::raw(
                "Mesej baru dari borang hubungi:\n\n"
                . "Nama: {$message->name}\n"
                . "E-mel: {$message->email}\n"
                . "Telefon: {$message->phone}\n"
                . "Perkara: " . ($message->subject ?: '-') . "\n\n"
                . "Mesej:\n{$message->message}\n",
                function ($mail) {
                    $mail->to(['sales.nansolutions@gmail.com', 'sales@nansolutions.com.my'])
                         ->subject('Mesej Hubungi Baru — NAN Solutions');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Contact message email failed: ' . $e->getMessage());
        }

        return redirect(url('/') . '#hubungi')
            ->with('contact_success', 'Terima kasih! Mesej anda telah dihantar. Kami akan menghubungi anda tidak lama lagi.');
    }

    // ── Admin ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $messages = ContactMessage::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $unread = ContactMessage::where('is_read', false)->count();

        return view('contact.index', compact('messages', 'search', 'unread'));
    }

    public function toggleRead(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_read' => ! $contactMessage->is_read]);

        return back();
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return back()->with('status', 'Mesej dipadam.');
    }
}
