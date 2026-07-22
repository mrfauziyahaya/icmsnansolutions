<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Payments\GatewayException;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\TurnstileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentGatewayManager $gateways,
        private TurnstileService $turnstile,
    ) {}

    // ── Public checkout ──────────────────────────────────────────────────────

    public function create()
    {
        return view('pay.create', [
            'options'   => $this->gateways->checkoutOptions(),
            'minAmount' => (float) config('services.payments.min_amount'),
            'maxAmount' => (float) config('services.payments.max_amount'),
            'bnplMin'   => (float) config('services.payments.bnpl_min'),
        ]);
    }

    public function store(Request $request)
    {
        $min = (float) config('services.payments.min_amount');
        $max = (float) config('services.payments.max_amount');

        $validated = $request->validate([
            'payer_name'  => 'required|string|max:255',
            'payer_email' => 'required|email|max:255',
            'payer_phone' => 'required|string|max:20',
            'address'     => 'required|string|max:500',
            'postcode'    => 'required|string|max:12',
            'amount'      => "required|numeric|min:{$min}|max:{$max}",
            'gateway'     => 'required|string',
        ]);

        if (! $this->turnstile->verify($request->input('cf-turnstile-response'), $request->ip())) {
            return back()->withInput()->withErrors([
                'captcha' => 'Pengesahan keselamatan gagal. Sila cuba lagi.',
            ]);
        }

        // Resolve the submitted option against what's actually available for this
        // amount; the client could have posted a hidden, unconfigured, or
        // BNPL-below-minimum option. A CHIP option also carries its method.
        $option = $this->gateways->resolveOption($validated['gateway'], (float) $validated['amount']);
        if (! $option) {
            return back()->withInput()->withErrors([
                'gateway' => 'Kaedah pembayaran ini tidak tersedia untuk jumlah tersebut.',
            ]);
        }

        // Recorded as pending first, so an abandoned or failed attempt is still on record.
        $payment = Payment::create([
            'reference'   => Payment::nextReference(),
            'payer_name'  => strtoupper($validated['payer_name']),
            'payer_email' => $validated['payer_email'],
            'payer_phone' => $validated['payer_phone'],
            'address'     => $validated['address'],
            'postcode'    => $validated['postcode'],
            'amount'      => $validated['amount'],
            'currency'    => 'MYR',
            'gateway'     => $option['gateway'],
            'method'      => $option['method'],
            'status'      => 'pending',
            'ip_address'  => $request->ip(),
        ]);

        try {
            $checkoutUrl = $this->gateways->driver($payment->gateway)->createPayment($payment);
        } catch (GatewayException $e) {
            $payment->update(['status' => 'failed', 'failure_reason' => $e->getMessage()]);
            Log::error("Payment {$payment->reference} could not start: {$e->getMessage()}");

            return back()->withInput()->withErrors([
                'gateway' => 'Kaedah pembayaran ini tidak dapat digunakan buat masa ini. Sila pilih yang lain.',
            ]);
        }

        return redirect()->away($checkoutUrl);
    }

    public function success(Request $request)
    {
        $payment = $this->findByReference($request->query('reference'));

        // Don't trust the redirect — confirm with the gateway. A webhook may not
        // have arrived yet, and /pay/success could be opened directly.
        $payment?->reconcile();

        return view('pay.result', [
            'payment' => $payment,
            'ok'      => $payment?->isPaid() ?? true,
        ]);
    }

    public function failed(Request $request)
    {
        $payment = $this->findByReference($request->query('reference'));
        $payment?->reconcile();

        return view('pay.result', [
            'payment' => $payment,
            'ok'      => $payment?->isPaid() ?? false,
        ]);
    }

    // ── Webhook ──────────────────────────────────────────────────────────────

    public function webhook(Request $request, string $gateway)
    {
        if (! $this->gateways->exists($gateway)) {
            return response()->json(['error' => 'unknown gateway'], 404);
        }

        try {
            $result = $this->gateways->driver($gateway)->verifyCallback($request);
        } catch (GatewayException $e) {
            Log::error("Webhook [{$gateway}] rejected: {$e->getMessage()}");
            return response()->json(['error' => 'invalid'], 400);
        }

        $payment = Payment::where('reference', $result['reference'] ?? '')->first();

        if (! $payment) {
            Log::warning("Webhook [{$gateway}] for unknown reference: " . ($result['reference'] ?? 'null'));
            return response()->json(['error' => 'not found'], 404);
        }

        // Idempotent: gateways retry, and a settled payment must not be reopened.
        // Still ACK so the gateway stops retrying.
        if ($payment->status !== 'paid') {
            $payment->update([
                'status'            => $result['status'],
                'gateway_reference' => $result['gateway_reference'] ?? $payment->gateway_reference,
                'paid_at'           => $result['status'] === 'paid' ? now() : null,
                'failure_reason'    => $result['reason'] ?? null,
                'callback_payload'  => $request->all(),
            ]);
        }

        return $this->webhookAck($gateway, $request, $payment->fresh());
    }

    /**
     * Build the response a gateway expects from its webhook. Most accept any 200;
     * Fiuu is special — its callback wants the literal "CBTOKEN:MPSTATOK", and its
     * browser "return" POST should redirect the payer to the result page.
     */
    private function webhookAck(string $gateway, Request $request, Payment $payment)
    {
        if ($gateway === 'fiuu') {
            $nbcb = (string) $request->input('nbcb', '');

            // No nbcb = browser return URL → send the payer to the result page.
            if ($nbcb === '') {
                return redirect()->route(
                    $payment->isPaid() ? 'pay.success' : 'pay.failed',
                    ['reference' => $payment->reference]
                );
            }

            // nbcb=1 = callback URL, must echo this exact token to ACK.
            if ($nbcb === '1') {
                return response('CBTOKEN:MPSTATOK');
            }

            // nbcb=2 = notification URL.
            return response('OK');
        }

        return response()->json(['status' => 'ok']);
    }

    // ── Admin ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $payments = Payment::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('reference', 'like', "%{$search}%")
                      ->orWhere('payer_name', 'like', "%{$search}%")
                      ->orWhere('payer_phone', 'like', "%{$search}%")
                      ->orWhere('payer_email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $totals = [
            'paid'    => Payment::where('status', 'paid')->sum('amount'),
            'count'   => Payment::count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed'  => Payment::where('status', 'failed')->count(),
        ];

        return view('payments.index', compact('payments', 'search', 'status', 'totals'));
    }

    public function show(Payment $payment)
    {
        return view('payments.show', compact('payment'));
    }

    private function findByReference(?string $reference): ?Payment
    {
        return $reference ? Payment::where('reference', $reference)->first() : null;
    }
}
