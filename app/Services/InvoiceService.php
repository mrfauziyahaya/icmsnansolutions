<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generate(Client $client, string $type): Invoice
    {
        $nett    = (float) $client->nettpremium;
        $premium = (float) $client->premium;
        $roadTax = (float) ($client->road_tax_price ?? 0);
        $total   = $premium + $roadTax;

        $invoice = Invoice::create([
            'invoice_number' => $this->nextInvoiceNumber(),
            'client_id'      => $client->client_id,
            'type'           => $type,
            'nett_premium'   => $nett,
            'premium'        => $premium,
            'road_tax_price' => $roadTax,
            'total_amount'   => $total,
            'issued_at'      => now(),
        ]);

        $pdf  = $this->buildPdf($invoice, $client);
        $path = 'invoices/' . $invoice->invoice_number . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $invoice->update(['pdf_path' => $path]);

        return $invoice;
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->year;
        $last = Invoice::whereYear('issued_at', $year)->lockForUpdate()->count();
        return 'INV-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    private function buildPdf(Invoice $invoice, Client $client)
    {
        $setting = Setting::instance();
        return Pdf::loadView('invoices.pdf', compact('invoice', 'client', 'setting'))
                  ->setPaper('a4', 'portrait');
    }
}
