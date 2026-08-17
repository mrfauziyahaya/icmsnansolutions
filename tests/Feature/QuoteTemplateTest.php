<?php

namespace Tests\Feature;

use App\Models\QuoteTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The admin quote comparison builder. Four quote types, each with its own
 * companies, rows and option lists, all driven by the type schema.
 */
class QuoteTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    /** A valid payload for a type using each field's defaults. */
    private function payloadFor(string $type, array $companies): array
    {
        $shared = [];
        foreach (QuoteTemplate::fieldKeys($type, 'shared') as $f) {
            $shared[$f] = QuoteTemplate::sharedDefault($f) ?? ($f === 'cermin' ? 0 : '');
        }

        $columns = [];
        foreach ($companies as $name) {
            $col = QuoteTemplate::defaultColumn($type, $name);
            $col['insurance_takaful'] = 1200;
            $col['sum_covered']       = 50000;
            $columns[] = $col;
        }

        return [
            'type'               => $type,
            'vehicle_reg_number' => 'wxy1234',
            'vehicle_model'      => 'Test Model',
            'shared'             => $shared,
            'columns'            => $columns,
        ];
    }

    public function test_each_type_can_be_created(): void
    {
        foreach (QuoteTemplate::types() as $type => $cfg) {
            $companies = array_slice($cfg['companies'], 0, 2);

            $this->actingAs($this->admin())
                ->post(route('quote-templates.store'), $this->payloadFor($type, $companies))
                ->assertRedirect();

            $t = QuoteTemplate::where('type', $type)->latest()->first();
            $this->assertNotNull($t, "type {$type} was not stored");
            $this->assertSame($cfg['title'], $t->title);
            $this->assertCount(2, $t->data['columns']);
        }
    }

    public function test_create_form_renders_the_requested_type(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.create', ['type' => 'motor_first_party']))
            ->assertOk()->getContent();

        // Motor-only companies, and motor-only fields.
        $this->assertStringContainsString('ZURICH TAKAFUL', $html);
        $this->assertStringNotContainsString('KURNIA INSURANS', $html);   // not a motor company
        $this->assertStringContainsString('All Rider', $html);
        $this->assertStringContainsString('Additional Benefits', $html);
        $this->assertStringContainsString('Add On Benefit', $html);
    }

    public function test_motor_rejects_a_non_motor_company(): void
    {
        // Kurnia isn't offered on motor types.
        $payload = $this->payloadFor('motor_first_party', ['ZURICH TAKAFUL']);
        $payload['columns'][0]['company'] = 'KURNIA INSURANS';

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasErrors('columns.0.company');
    }

    public function test_additional_benefits_are_stored_per_company(): void
    {
        $payload = $this->payloadFor('motor_first_party', ['ZURICH TAKAFUL', 'ETIQA TAKAFUL']);
        $payload['columns'][0]['additional_benefits'] = ['helmet', 'pa5500'];
        $payload['columns'][1]['additional_benefits'] = ['towing_cost'];

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertRedirect();

        $cols = QuoteTemplate::where('type', 'motor_first_party')->sole()->data['columns'];
        $this->assertSame(['helmet', 'pa5500'], $cols[0]['additional_benefits']);
        $this->assertSame(['towing_cost'], $cols[1]['additional_benefits']);
    }

    public function test_additional_benefits_reject_an_unknown_value(): void
    {
        $payload = $this->payloadFor('motor_first_party', ['ZURICH TAKAFUL']);
        $payload['columns'][0]['additional_benefits'] = ['not_a_benefit'];

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasErrors('columns.0.additional_benefits.0');
    }

    /** 3rd Party (Motor) has no Value row and no Towing row. */
    public function test_motor_third_party_omits_value_and_towing(): void
    {
        $company = QuoteTemplate::fieldKeys('motor_third_party', 'company');
        $this->assertNotContains('value', $company);
        $this->assertNotContains('towing', $company);
        $this->assertContains('add_on_benefit', $company);
    }

    public function test_motor_towing_options_are_in_the_specified_order(): void
    {
        $keys = array_keys(QuoteTemplate::optionsFor('towing', 'motor_first_party', 'ZURICH TAKAFUL'));
        $this->assertSame(['no_towing', 'unlimited', '50km', '30km'], $keys);
    }

    /** 30% was missing from the non-motor NCD list. */
    public function test_every_type_offers_thirty_percent_ncd(): void
    {
        foreach (array_keys(QuoteTemplate::types()) as $type) {
            $options = QuoteTemplate::optionsFor('ncd', $type, 'ZURICH TAKAFUL');
            $this->assertArrayHasKey('30', $options, "30% NCD missing on {$type}");
            $this->assertSame('30%', $options['30']);
        }

        // Listed between 25% and 35%, not appended at the end. Compared on the
        // labels because PHP casts numeric string keys to integers.
        $this->assertSame(
            ['0%', '25%', '30%', '35%', '38.33%', '45%', '55%'],
            array_values(QuoteTemplate::optionsFor('ncd', 'comprehensive', 'ZURICH TAKAFUL'))
        );
    }

    public function test_a_thirty_percent_ncd_can_be_saved(): void
    {
        $payload = $this->payloadFor('third_party_fire_theft', ['ZURICH TAKAFUL']);
        $payload['columns'][0]['ncd'] = '30';

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertSame('30', QuoteTemplate::firstOrFail()->data['columns'][0]['ncd']);
    }

    public function test_motor_ncd_has_the_extended_set(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.create', ['type' => 'motor_first_party']))->getContent();

        $this->assertStringContainsString('15%', $html);
        $this->assertStringContainsString('20%', $html);
        $this->assertStringContainsString('30%', $html);
    }

    public function test_preview_lays_out_sections_and_benefit_lines(): void
    {
        $payload = $this->payloadFor('motor_first_party', ['ZURICH TAKAFUL']);
        $payload['columns'][0]['additional_benefits'] = ['helmet', 'perils'];

        $this->actingAs($this->admin())->post(route('quote-templates.store'), $payload)->assertRedirect();
        $t = QuoteTemplate::where('type', 'motor_first_party')->sole();

        $html = $this->actingAs($this->admin())->get(route('quote-templates.show', $t))->assertOk()->getContent();
        $this->assertStringContainsString('Helmet Replacement RM50', $html);
        $this->assertStringContainsString('Limited Special Perils Allowance RM1000', $html);
    }

    public function test_it_rejects_zero_companies(): void
    {
        $payload = $this->payloadFor('comprehensive', []);

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasErrors('columns');
    }

    public function test_a_new_quote_starts_blank_rather_than_guessing(): void
    {
        $blank = QuoteTemplate::blankData('comprehensive');

        // Roadtax is quoted per vehicle, so it must be entered, not assumed.
        $this->assertNull($blank['shared']['roadtax']);

        // No insurer is pre-ticked — the agent chooses who to compare.
        $this->assertSame([], $blank['columns']);
    }

    public function test_optional_selects_default_to_unanswered(): void
    {
        $column = QuoteTemplate::defaultColumn('comprehensive', 'ZURICH TAKAFUL');

        foreach (['towing', 'personal_accident', 'vehicle_inspection'] as $field) {
            $this->assertNull($column[$field], "{$field} should start unanswered");
        }

        // Fields outside the optional set keep their defaults.
        $this->assertSame('market_value', $column['value']);
        $this->assertSame('yes', $column['accident_assist']);
    }

    public function test_an_optional_select_may_be_submitted_blank(): void
    {
        $payload = $this->payloadFor('comprehensive', ['ZURICH TAKAFUL']);
        $payload['columns'][0]['towing']             = '';
        $payload['columns'][0]['personal_accident']  = '';
        $payload['columns'][0]['vehicle_inspection'] = '';

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasNoErrors();

        $column = QuoteTemplate::firstOrFail()->data['columns'][0];
        $this->assertNull($column['towing']);
    }

    public function test_a_required_select_still_cannot_be_blank(): void
    {
        $payload = $this->payloadFor('comprehensive', ['ZURICH TAKAFUL']);
        $payload['columns'][0]['value'] = '';

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasErrors('columns.0.value');
    }

    public function test_an_unanswered_select_shows_a_dash_on_the_quote(): void
    {
        $template = QuoteTemplate::create($this->storedPayload('comprehensive', ['ZURICH TAKAFUL']));

        $rows = collect($template->previewData()['sections'])
            ->flatMap(fn ($section) => $section['rows'])
            ->keyBy('label');

        $this->assertSame('-', $rows['Towing']['cells'][0]);
    }

    public function test_the_pdf_downloads(): void
    {
        $template = QuoteTemplate::create($this->storedPayload('comprehensive', ['ZURICH TAKAFUL']));

        $response = $this->actingAs($this->admin())
            ->get(route('quote-templates.pdf', $template));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));

        // Filename is uppercase; the reg number would otherwise be lowercased by slug().
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('SEBUT-HARGA-WXY1234.pdf', $disposition);
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    /**
     * A single-company quote used to spill a phantom column: the vehicle row
     * spanned a fixed 2 + 1 against a 2-column grid, pushing "Model" past the
     * table. Every row must span exactly the grid, at any company count.
     */
    public function test_the_preview_table_never_overflows_its_grid(): void
    {
        foreach ([1, 2, 3] as $count) {
            $companies = array_slice(array_keys(QuoteTemplate::ALL_COMPANIES), 0, $count);
            $template  = QuoteTemplate::create($this->storedPayload('comprehensive', $companies));

            $html = $this->actingAs($this->admin())
                ->get(route('quote-templates.show', $template))
                ->assertOk()
                ->getContent();

            $table = $this->quoteTable($html);
            preg_match_all('#<tr\b.*?</tr>#s', $table, $rows);
            $this->assertNotEmpty($rows[0], "no rows rendered for {$count} company/ies");

            foreach ($rows[0] as $row) {
                preg_match_all('#<td\b([^>]*)>#', $row, $cells);
                if (! $cells[1]) {
                    continue;
                }

                $span = 0;
                foreach ($cells[1] as $attributes) {
                    $span += preg_match('#colspan="(\d+)"#', $attributes, $m) ? (int) $m[1] : 1;
                }

                $this->assertSame($count + 1, $span, "row spans {$span} of " . ($count + 1) . " with {$count} company/ies");
            }
        }
    }

    /** The quote table only, so surrounding layout markup can't skew the count. */
    private function quoteTable(string $html): string
    {
        $start = strpos($html, 'id="quote-print"');
        $this->assertNotFalse($start, 'quote table not found in preview');

        $end = strpos($html, '</table>', $start);

        return substr($html, $start, $end - $start);
    }

    /**
     * Shared number rows are formatted once by display(). The views used to run
     * the money formatter over that result, casting "RM 90.00" to 0 and printing
     * "RM -", so Roadtax and Cermin never showed their value.
     */
    public function test_a_shared_number_shows_its_value_on_the_quote(): void
    {
        $payload = $this->storedPayload('comprehensive', ['ZURICH TAKAFUL']);
        $payload['data']['shared']['roadtax'] = 90;
        $payload['data']['shared']['cermin']  = 250;
        $template = QuoteTemplate::create($payload);

        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.show', $template))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('RM 90.00', $html);
        $this->assertStringContainsString('RM 250.00', $html);

        // The bug rendered every shared number as this instead.
        $rows = collect($template->previewData()['sections'])
            ->flatMap(fn ($section) => $section['rows'])
            ->keyBy('label');
        $this->assertSame('RM 90.00', $rows['Roadtax (RM)']['value']);
    }

    public function test_allianz_is_offered_on_every_quote_type(): void
    {
        foreach (array_keys(QuoteTemplate::types()) as $type) {
            $this->assertContains('ALLIANZ', QuoteTemplate::companiesForType($type), "missing on {$type}");
        }
    }

    public function test_allianz_option_lists_differ_by_quote_type(): void
    {
        // Comprehensive: Allianz's own lists, in the order given.
        $this->assertSame(
            ['150km' => '150 KM', '450km' => '450 KM', 'unlimited' => 'UNLIMITED'],
            QuoteTemplate::optionsFor('towing', 'comprehensive', 'ALLIANZ')
        );
        $this->assertSame(
            ['road_warrior' => 'ROAD WARRIOR', 'enhanced_road_warrior' => 'ENHANCED ROAD WARRIOR'],
            QuoteTemplate::optionsFor('personal_accident', 'comprehensive', 'ALLIANZ')
        );

        // Allianz keeps its own lists even on the types that set a towing list
        // for everyone else (3rd Party & Theft, 1st Party Motor).
        foreach (['third_party_fire_theft', 'motor_first_party'] as $type) {
            $this->assertSame(
                ['150km' => '150 KM', '450km' => '450 KM', 'unlimited' => 'UNLIMITED'],
                QuoteTemplate::optionsFor('towing', $type, 'ALLIANZ'),
                "towing wrong on {$type}"
            );
        }
        $this->assertSame(
            ['road_warrior' => 'ROAD WARRIOR', 'enhanced_road_warrior' => 'ENHANCED ROAD WARRIOR'],
            QuoteTemplate::optionsFor('personal_accident', 'motor_first_party', 'ALLIANZ')
        );

        // That must not leak to the other insurers on those types.
        $this->assertSame(
            ['no_towing' => 'NO TOWING', 'unlimited' => 'UNLIMITED', '50km' => '50 KM', '30km' => '30 KM'],
            QuoteTemplate::optionsFor('towing', 'motor_first_party', 'ZURICH TAKAFUL')
        );
        $this->assertSame(
            ['no_towing' => 'NO TOWING', 'unlimited' => 'UNLIMITED'],
            QuoteTemplate::optionsFor('towing', 'third_party_fire_theft', 'ETIQA TAKAFUL')
        );
    }

    public function test_a_quote_can_be_saved_with_allianz(): void
    {
        $payload = $this->payloadFor('motor_first_party', ['ALLIANZ']);
        $payload['columns'][0]['towing']            = 'unlimited';
        $payload['columns'][0]['personal_accident'] = 'road_warrior';

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasNoErrors();

        $column = QuoteTemplate::firstOrFail()->data['columns'][0];
        $this->assertSame('ALLIANZ', $column['company']);
        $this->assertSame('road_warrior', $column['personal_accident']);
    }

    /**
     * dompdf decodes images at full resolution whatever size they print at, so
     * one 2250x1871 logo cost 6s of an 8s render. Oversized logos are resampled
     * once into storage/app/pdf-logos and reused.
     */
    public function test_an_oversized_logo_is_downscaled_and_cached_for_the_pdf(): void
    {
        $cache = storage_path('app/pdf-logos');
        File::deleteDirectory($cache);

        // ZURICH TAKAFUL's logo is the oversized one.
        $template = QuoteTemplate::create($this->storedPayload('comprehensive', ['ZURICH TAKAFUL']));

        $this->actingAs($this->admin())
            ->get(route('quote-templates.pdf', $template))
            ->assertOk();

        $cached = File::exists($cache) ? File::files($cache) : [];
        $this->assertNotEmpty($cached, 'no downscaled logo was cached');

        foreach ($cached as $file) {
            [$width, $height] = getimagesize($file->getPathname());
            $this->assertLessThanOrEqual(480, max($width, $height), 'cached logo was not downscaled');
        }

        // A second render reuses the cache rather than resampling again.
        $before = count($cached);
        $this->actingAs($this->admin())->get(route('quote-templates.pdf', $template))->assertOk();
        $this->assertCount($before, File::files($cache));
    }

    /** The stored shape (what validated() writes), for model-level assertions. */
    private function storedPayload(string $type, array $companies): array
    {
        $form = $this->payloadFor($type, $companies);

        return [
            'type'               => $type,
            'title'              => QuoteTemplate::typeConfig($type)['title'],
            'vehicle_reg_number' => strtoupper($form['vehicle_reg_number']),
            'vehicle_model'      => $form['vehicle_model'],
            'data'               => ['shared' => $form['shared'], 'columns' => $form['columns']],
        ];
    }
}
