<?php

namespace App\Http\Controllers;

use App\Models\QuoteTemplate;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
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
            $company['pdf_logo'] = $this->pdfLogo($company['logo']);

            return $company;
        }, $preview['companies']);

        $stored = $setting->logo_path ? storage_path('app/public/' . $setting->logo_path) : null;
        $brandLogo = $this->downscaleForPdf(
            $stored && is_file($stored)
                ? $stored
                : (is_file(public_path('images/logo.png')) ? public_path('images/logo.png') : null)
        );

        // Str::slug() lowercases, so uppercase after slugging. The extension stays
        // lowercase — some clients match it case-sensitively.
        $filename = Str::upper('sebut-harga-' . Str::slug($quoteTemplate->vehicle_reg_number ?: 'quote')) . '.pdf';

        $output = $this->renderPdfToFitOnePage($quoteTemplate, $preview, $setting, $brandLogo);

        return new Response($output, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * dompdf has no real "shrink to fit the page" -- the font size/row padding
     * the table needs to stay on one A4 page depends on things a fixed guess
     * can't see ahead of time (how tall the insurer logos end up, how many
     * lines an Additional Benefits cell stacks, how many rows a type has).
     * A size guessed from row count alone was shown to produce a 2-page PDF
     * for an ordinary 2-company quote, because the guess never accounted for
     * the logos' fixed height at all.
     *
     * Rather than refine the guess further, render it, ask dompdf how many
     * pages it actually took (Canvas::get_page_count() -- the real, measured
     * answer, not an estimate), and step the font size down until it reports
     * exactly one. Falls back to the smallest preset if even that doesn't
     * fit, rather than failing the download.
     */
    private function renderPdfToFitOnePage(QuoteTemplate $quoteTemplate, array $preview, Setting $setting, ?string $brandLogo): string
    {
        // Largest/most readable first; font and row padding shrink together.
        $presets = [
            ['fontSize' => '10pt',  'rowPad' => '4px'],
            ['fontSize' => '9pt',   'rowPad' => '4px'],
            ['fontSize' => '8.5pt', 'rowPad' => '3px'],
            ['fontSize' => '8pt',   'rowPad' => '3px'],
            ['fontSize' => '7.5pt', 'rowPad' => '2.5px'],
            ['fontSize' => '7pt',   'rowPad' => '2.5px'],
            ['fontSize' => '6.5pt', 'rowPad' => '2px'],
            ['fontSize' => '6pt',   'rowPad' => '2px'],
        ];

        $lastOutput = '';

        foreach ($presets as $preset) {
            $pdf = Pdf::loadView('quote-templates.pdf', [
                'template'  => $quoteTemplate,
                'preview'   => $preview,
                'setting'   => $setting,
                'brandLogo' => $brandLogo,
                'fontSize'  => $preset['fontSize'],
                'rowPad'    => $preset['rowPad'],
            ])->setPaper('a4', 'portrait');

            $pdf->render();
            $pages      = $pdf->getDomPDF()->getCanvas()->get_page_count();
            $lastOutput = $pdf->output();

            if ($pages <= 1) {
                break;
            }
        }

        return $lastOutput;
    }

    /** Absolute path to a PDF-sized copy of a public logo, or null. */
    private function pdfLogo(?string $relative): ?string
    {
        $source = $relative ? public_path($relative) : null;

        return $source && is_file($source) ? $this->downscaleForPdf($source) : null;
    }

    /**
     * dompdf decodes an image at full resolution regardless of the size it
     * prints at, and the cost scales with pixel count: one 2250x1871 logo was
     * 6s of an 8s render. Logos print ~160px wide, so anything larger is
     * resampled once and cached. Falls back to the original if GD can't read it.
     */
    private function downscaleForPdf(?string $source, int $max = 480): ?string
    {
        if (! $source || ! is_file($source)) {
            return null;
        }

        $size = @getimagesize($source);
        if (! $size || empty($size[0]) || empty($size[1]) || ($size[0] <= $max && $size[1] <= $max)) {
            return $source;
        }

        $directory = storage_path('app/pdf-logos');
        $cached    = $directory . '/' . md5($source . filemtime($source) . $max) . '.png';
        if (is_file($cached)) {
            return $cached;
        }

        $image = @imagecreatefromstring((string) file_get_contents($source));
        if (! $image) {
            return $source;
        }

        $scale  = min($max / $size[0], $max / $size[1]);
        $width  = max((int) round($size[0] * $scale), 1);
        $height = max((int) round($size[1] * $scale), 1);

        $resized = imagecreatetruecolor($width, $height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

        File::ensureDirectoryExists($directory);
        $written = @imagepng($resized, $cached);

        imagedestroy($image);
        imagedestroy($resized);

        return $written ? $cached : $source;
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
                // Union of every company's possible list on this field (e.g.
                // Allianz's own Additional Benefits on 1st Party Motor are a
                // different set from everyone else's) -- loose by company,
                // same as towing/pa below; the form only ever offers each
                // company its own correct subset.
                $rules['columns.*.' . $field]         = 'nullable|array';
                $rules['columns.*.' . $field . '.*']  = 'in:' . implode(',', array_keys(
                    QuoteTemplate::ADDITIONAL_BENEFITS + QuoteTemplate::ALLIANZ_ADDITIONAL_BENEFITS
                ));
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

        // A type can redirect a field to a different option source (e.g. 1st
        // Party Motor's "Rider Type" instead of All Rider's own yesno) --
        // same override optionsFor() reads, so validation and the form agree.
        $source = QuoteTemplate::typeConfig($type)['field_options'][$field] ?? $def['options'] ?? null;

        $keys = match ($source) {
            'value'          => array_keys(QuoteTemplate::VALUE_OPTIONS),
            'yesno'          => array_keys(QuoteTemplate::YESNO_OPTIONS),
            'roadtax_period' => array_keys(QuoteTemplate::ROADTAX_PERIOD_OPTIONS),
            'ncd'            => array_keys(QuoteTemplate::typeConfig($type)['ncd'] ?? QuoteTemplate::NCD_OPTIONS),
            'towing'         => array_keys(QuoteTemplate::TOWING_OPTIONS),
            'pa'             => array_keys(QuoteTemplate::PA_OPTIONS),
            'rider_type'     => array_keys(QuoteTemplate::RIDER_TYPE_OPTIONS),
            default          => [],
        };

        return (QuoteTemplate::isOptional($field, $type) ? 'nullable|in:' : 'required|in:') . implode(',', $keys);
    }
}
