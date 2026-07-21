<?php

namespace App\Http\Controllers;

use App\Models\QuoteTemplate;
use Illuminate\Http\Request;

class QuoteTemplateController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $templates = QuoteTemplate::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('vehicle_reg_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_model', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('quote-templates.index', compact('templates', 'search'));
    }

    public function create()
    {
        $template = new QuoteTemplate([
            'title' => 'First Party Comprehensive',
            'data'  => QuoteTemplate::blankData(),
        ]);

        return view('quote-templates.form', ['template' => $template]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $template = QuoteTemplate::create($data);

        return redirect()->route('quote-templates.show', $template)
            ->with('status', 'Sebut harga berjaya disimpan.');
    }

    public function edit(QuoteTemplate $quoteTemplate)
    {
        return view('quote-templates.form', ['template' => $quoteTemplate]);
    }

    public function update(Request $request, QuoteTemplate $quoteTemplate)
    {
        $quoteTemplate->update($this->validated($request));

        return redirect()->route('quote-templates.show', $quoteTemplate)
            ->with('status', 'Sebut harga berjaya dikemaskini.');
    }

    public function show(QuoteTemplate $quoteTemplate)
    {
        return view('quote-templates.preview', [
            'template' => $quoteTemplate,
            'columns'  => $quoteTemplate->computedColumns(),
        ]);
    }

    public function destroy(QuoteTemplate $quoteTemplate)
    {
        $quoteTemplate->delete();

        return redirect()->route('quote-templates.index')
            ->with('status', 'Sebut harga dipadam.');
    }

    /**
     * Validate the flat form input and reshape it into the stored data blob.
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'vehicle_reg_number' => 'required|string|max:30',
            'vehicle_model'      => 'nullable|string|max:100',

            'shared.sum_covered'  => 'nullable|numeric|min:0',
            'shared.cermin'       => 'nullable|numeric|min:0',
            'shared.bencana_alam' => 'required|in:yes,no',
            'shared.digital_copy' => 'required|in:yes,no',
            'shared.roadtax'      => 'nullable|numeric|min:0',

            'columns'                       => 'required|array|size:3',
            'columns.*.company'             => 'nullable|string|max:100',
            'columns.*.value'               => 'required|in:market_value,agreed_value',
            'columns.*.towing'              => 'required|in:150km,200km,300km,unlimited',
            'columns.*.accident_assist'     => 'required|in:yes,no',
            'columns.*.ncd'                 => 'nullable|numeric|min:0|max:100',
            'columns.*.all_driver'          => 'required|in:yes,no',
            'columns.*.personal_accident'   => 'required|in:no,yes,cash_care,z_drive',
            'columns.*.vehicle_inspection'  => 'required|in:yes,no',
            'columns.*.insurance_takaful'   => 'nullable|numeric|min:0',
        ]);

        return [
            'title'              => $validated['title'],
            'vehicle_reg_number' => strtoupper($validated['vehicle_reg_number']),
            'vehicle_model'      => $validated['vehicle_model'] ?? null,
            'data'               => [
                'shared'  => $validated['shared'],
                'columns' => $validated['columns'],
            ],
        ];
    }
}
