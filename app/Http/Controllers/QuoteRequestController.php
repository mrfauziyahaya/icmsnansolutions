<?php

namespace App\Http\Controllers;

use App\Mail\QuoteRequestMail;
use App\Models\QuoteRequest;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QuoteRequestController extends Controller
{
    // ── Public ───────────────────────────────────────────────────────────────

    public function create()
    {
        return view('quote.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemilik'               => 'required|string|max:255',
            'no_ic'                      => 'required|string|max:20',
            'poskod'                     => 'required|string|max:10',
            'no_plate'                   => 'required|string|max:20',
            'ehailing'                   => 'required|in:Ya,Tidak',
            'ehailing_usage'             => 'required_if:ehailing,Ya|nullable|in:Harian,Tahunan',
            'tukar_milik'                => 'required_if:ehailing,Tidak|nullable|in:Ya,Tidak',
            'whatsapp'                   => 'required|string|max:20',
            'jenis_perlindungan'         => 'required|string|max:255',
            'perlindungan_tambahan'      => 'nullable',
            'jumlah_perlindungan_cermin' => 'nullable|numeric',
            'jenis_pembayaran'           => 'required|string|max:255',
        ]);

        // Normalize perlindungan_tambahan to an array (checkboxes = array, radio = string)
        $tambahan = $request->input('perlindungan_tambahan', []);
        if (is_string($tambahan)) {
            $tambahan = [$tambahan];
        }
        $tambahan = array_values(array_filter((array) $tambahan));

        // Jumlah cermin is required when Cermin add-on is selected
        if (in_array('Cermin', $tambahan) && ! filled($validated['jumlah_perlindungan_cermin'] ?? null)) {
            return back()->withInput()->withErrors([
                'jumlah_perlindungan_cermin' => 'Sila masukkan jumlah perlindungan cermin diperlukan.',
            ]);
        }

        // Guard against duplicate submissions (double/triple click, network retry).
        // Treat an identical IC + plate submitted within the last 2 minutes as the same request.
        $duplicate = QuoteRequest::where('no_ic', $validated['no_ic'])
            ->where('no_plate', strtoupper($validated['no_plate']))
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();

        if ($duplicate) {
            return redirect()->route('quote.success');
        }

        $quote = QuoteRequest::create([
            'nama_pemilik'               => strtoupper($validated['nama_pemilik']),
            'no_ic'                      => $validated['no_ic'],
            'poskod'                     => $validated['poskod'],
            'no_plate'                   => strtoupper($validated['no_plate']),
            'ehailing'                   => $validated['ehailing'] === 'Ya',
            'ehailing_usage'             => $validated['ehailing'] === 'Ya' ? ($validated['ehailing_usage'] ?? null) : null,
            'tukar_milik'                => $validated['ehailing'] === 'Tidak' ? (($validated['tukar_milik'] ?? null) === 'Ya') : null,
            'whatsapp'                   => $validated['whatsapp'],
            'jenis_perlindungan'         => $validated['jenis_perlindungan'],
            'perlindungan_tambahan'      => $tambahan ?: null,
            'jumlah_perlindungan_cermin' => in_array('Cermin', $tambahan) ? ($validated['jumlah_perlindungan_cermin'] ?? null) : null,
            'jenis_pembayaran'           => $validated['jenis_pembayaran'],
        ]);

        // Send email to sales team
        try {
            Mail::to(['sales.nansolutions@gmail.com', 'sales@nansolutions.com.my'])
                ->send(new QuoteRequestMail($quote));
        } catch (\Throwable $e) {
            Log::error('Quote request email failed: ' . $e->getMessage());
        }

        // Notify admin via WhatsApp
        try {
            app(WhatsAppService::class)->notifyQuoteRequest($quote->nama_pemilik, $quote->whatsapp);
        } catch (\Throwable $e) {
            Log::error('Quote request WhatsApp failed: ' . $e->getMessage());
        }

        return redirect()->route('quote.success');
    }

    public function success()
    {
        return view('quote.success');
    }

    // ── Admin ────────────────────────────────────────────────────────────────

    public function index()
    {
        $quotes = QuoteRequest::latest()->paginate(25);

        return view('quote.index', compact('quotes'));
    }

    public function show(QuoteRequest $quoteRequest)
    {
        return view('quote.show', ['quote' => $quoteRequest]);
    }

    public function toggleRead(QuoteRequest $quoteRequest)
    {
        $quoteRequest->update(['is_read' => ! $quoteRequest->is_read]);

        return back();
    }
}
