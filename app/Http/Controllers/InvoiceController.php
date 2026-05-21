<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !Storage::disk('public')->exists($invoice->pdf_path)) {
            abort(404, 'Invoice PDF not found.');
        }

        return Storage::disk('public')->download(
            $invoice->pdf_path,
            $invoice->invoice_number . '.pdf'
        );
    }
}
