<?php

namespace App\Http\Controllers;

use App\Models\QuoteTemplate;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function create(Request $request)
    {
        $type = $request->query('type', QuoteTemplate::DEFAULT_TYPE);
        if (! QuoteTemplate::typeExists($type)) {
            $type = QuoteTemplate::DEFAULT_TYPE;
        }

        $template = new QuoteTemplate([
            'type'  => $type,
            'title' => QuoteTemplate::typeConfig($type)['title'],
            'data'  => QuoteTemplate::blankData($type),
        ]);

        return view('quote-templates.form', ['template' => $template]);
    }

    public function store(Request $request)
    {
        $type = $request->input('type', QuoteTemplate::DEFAULT_TYPE);
        if (! QuoteTemplate::typeExists($type)) {
            $type = QuoteTemplate::DEFAULT_TYPE;
        }

        $template = QuoteTemplate::create($this->validated($request, $type));

        return redirect()->route('quote-templates.show', $template)
            ->with('status', 'Sebut harga berjaya disimpan.');
    }

    public function edit(QuoteTemplate $quoteTemplate)
    {
        return view('quote-templates.form', ['template' => $quoteTemplate]);
    }

    public function update(Request $request, QuoteTemplate $quoteTemplate)
    {
        $quoteTemplate->update($this->validated($request, $quoteTemplate->type ?: QuoteTemplate::DEFAULT_TYPE));

        return redirect()->route('quote-templates.show', $quoteTemplate)
            ->with('status', 'Sebut harga berjaya dikemaskini.');
    }

    public function show(QuoteTemplate $quoteTemplate)
    {
        return view('quote-templates.preview', [
            'template' => $quoteTemplate,
            'preview'  => $quoteTemplate->previewData(),
        ]);
    }

    /**
     * The quote as a downloadable PDF, sized to one A4 portrait page.
     *
     * dompdf loads images off the filesystem rather than by URL, so the logos
     * are re-pointed at absolute paths here instead of in previewData(), which
     * the on-screen preview shares and needs as public URLs.
     */
    public function pdf(QuoteTemplate $quoteTemplate)
    {
        $preview = $quoteTemplate->previewData();
        $setting = Setting::instance();

        $preview['companies'] = array_map(function (array $company) {
            $company['pdf_logo'] = $company['logo'] && is_file(public_path($company['logo']))
                ? public_path($company['logo'])
                : null;

            return $company;
        }, $preview['companies']);

        $stored = $setting->logo_path ? storage_path('app/public/' . $setting->logo_path) : null;
        $brandLogo = $stored && is_file($stored)
            ? $stored
            : (is_file(public_path('images/logo.png')) ? public_path('images/logo.png') : null);

        $filename = 'sebut-harga-' . Str::slug($quoteTemplate->vehicle_reg_number ?: 'quote') . '.pdf';

        return Pdf::loadView('quote-templates.pdf', [
            'template'  => $quoteTemplate,
            'preview'   => $preview,
            'setting'   => $setting,
            'brandLogo' => $brandLogo,
        ])->setPaper('a4', 'portrait')->download($filename);
    }

    public function destroy(QuoteTemplate $quoteTemplate)
    {
        $quoteTemplate->delete();

        return redirect()->route('quote-templates.index')
            ->with('status', 'Sebut harga dipadam.');
    }

    /**
     * Build validation rules from the type's schema, then reshape the flat form
     * input into the stored data blob.
     */
    private function validated(Request $request, string $type): array
    {
        $companies = QuoteTemplate::companiesForType($type);

        $rules = [
            'vehicle_reg_number' => 'required|string|max:30',
            'vehicle_model'      => 'nullable|string|max:100',
            'columns'            => 'required|array|min:1|max:' . count($companies),
            'columns.*.company'  => 'required|string|in:' . implode(',', $companies),
        ];

        foreach (QuoteTemplate::fieldKeys($type, 'shared') as $field) {
            $rules['shared.' . $field] = $this->fieldRule($field, $type);
        }
        foreach (QuoteTemplate::fieldKeys($type, 'company') as $field) {
            if (QuoteTemplate::FIELDS[$field]['input'] === 'multiselect') {
                $rules['columns.*.' . $field]         = 'nullable|array';
                $rules['columns.*.' . $field . '.*']  = 'in:' . implode(',', array_keys(QuoteTemplate::ADDITIONAL_BENEFITS));
            } else {
                $rules['columns.*.' . $field] = $this->fieldRule($field, $type);
            }
        }

        $validated = $request->validate($rules);

        // Keep the submitted order, drop duplicate company selections.
        $columns = collect($validated['columns'])->unique('company')->values()->all();

        return [
            'type'               => $type,
            'title'              => QuoteTemplate::typeConfig($type)['title'],
            'vehicle_reg_number' => strtoupper($validated['vehicle_reg_number']),
            'vehicle_model'      => $validated['vehicle_model'] ?? null,
            'data'               => [
                'shared'  => $validated['shared'] ?? [],
                'columns' => $columns,
            ],
        ];
    }

    private function fieldRule(string $field, string $type): string
    {
        $def = QuoteTemplate::FIELDS[$field];

        if ($def['input'] === 'number') {
            return 'nullable|numeric|min:0';
        }

        $keys = match ($def['options'] ?? null) {
            'value'          => array_keys(QuoteTemplate::VALUE_OPTIONS),
            'yesno'          => array_keys(QuoteTemplate::YESNO_OPTIONS),
            'roadtax_period' => array_keys(QuoteTemplate::ROADTAX_PERIOD_OPTIONS),
            'ncd'            => array_keys(QuoteTemplate::typeConfig($type)['ncd'] ?? QuoteTemplate::NCD_OPTIONS),
            'towing'         => array_keys(QuoteTemplate::TOWING_OPTIONS),
            'pa'             => array_keys(QuoteTemplate::PA_OPTIONS),
            default          => [],
        };

        return (QuoteTemplate::isOptional($field) ? 'nullable|in:' : 'required|in:') . implode(',', $keys);
    }
}
