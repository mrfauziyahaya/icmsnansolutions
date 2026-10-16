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
        // Stage 3: "All Rider" is relabelled "Rider Type" on this type only.
        $this->assertStringContainsString('Rider Type', $html);
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

    /** Stage 3: towing is fully per-company on 1st Party Motor now. */
    public function test_motor_towing_options_are_in_the_specified_order(): void
    {
        $expected = [
            'ZURICH TAKAFUL'   => ['unlimited', 'no'],
            'ETIQA TAKAFUL'    => ['50km', '3x_unlimited'],
            'TAKAFUL MALAYSIA' => ['50km'],
            'TAKAFUL IKHLAS'   => ['no_towing'],
            'ALLIANZ'          => ['50km', '600km'],
        ];

        foreach ($expected as $company => $keys) {
            $this->assertSame(
                $keys,
                array_keys(QuoteTemplate::optionsFor('towing', 'motor_first_party', $company)),
                "towing wrong for {$company}",
            );
        }
    }

    /** 30% was missing from the non-motor NCD list. */
    public function test_every_type_offers_thirty_percent_ncd(): void
    {
        foreach (array_keys(QuoteTemplate::types()) as $type) {
            $options = QuoteTemplate::optionsFor('ncd', $type, 'ZURICH TAKAFUL');
            $this->assertArrayHasKey('30', $options, "30% NCD missing on {$type}");
            $this->assertSame('30%', $options['30']);
        }

        // Listed in ascending order, not appended at the end. Compared on the
        // labels because PHP casts numeric string keys to integers. 15%/20%
        // were added to match the motor types' list (Stage 1).
        $this->assertSame(
            ['0%', '15%', '20%', '25%', '30%', '35%', '38.33%', '45%', '55%'],
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
     * Regression: a real motor_first_party quote (2 companies, one with every
     * Additional Benefit ticked) rendered to 2 pages before this fix -- the
     * font-size guess was based on row count alone and never accounted for
     * the insurer logos' fixed height, nor for a benefits cell stacking
     * several lines. The fix renders repeatedly at shrinking sizes and reads
     * dompdf's own page count, so this asserts the real output fits one page
     * rather than trusting the font-size math again.
     */
    public function test_pdf_fits_one_page_even_with_a_tall_benefits_cell(): void
    {
        $payload = $this->payloadFor('motor_first_party', ['ZURICH TAKAFUL', 'ETIQA TAKAFUL']);
        $payload['columns'][0]['additional_benefits'] = ['helmet', 'pa5500', 'perils', 'towing_cost'];
        $template = QuoteTemplate::create([
            'type'               => 'motor_first_party',
            'title'              => QuoteTemplate::typeConfig('motor_first_party')['title'],
            'vehicle_reg_number' => strtoupper($payload['vehicle_reg_number']),
            'vehicle_model'      => $payload['vehicle_model'],
            'data'               => ['shared' => $payload['shared'], 'columns' => $payload['columns']],
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('quote-templates.pdf', $template));

        $response->assertOk();

        preg_match_all('/\/Type\s*\/Page[^s]/', $response->getContent(), $matches);
        $this->assertCount(1, $matches[0], 'Expected the quote to print on exactly one A4 page.');
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

    /**
     * A = Digital Copy (MyJPJ), B = Physical Copy. B only applies once A is
     * Yes (the form hides it and forces it back to 'no' otherwise).
     */
    public function test_digital_copy_off_is_free_regardless_of_physical_copy(): void
    {
        $payload = $this->storedPayload('comprehensive', ['ZURICH TAKAFUL']);
        $payload['data']['shared']['digital_copy']  = 'no';
        $payload['data']['shared']['physical_copy'] = 'yes'; // stale/forced value -- must not matter
        $payload['data']['shared']['roadtax']       = 0;
        $template = QuoteTemplate::create($payload);

        $this->assertSame(1200.0, $template->columnTotal($template->data['columns'][0]));
    }

    public function test_digital_copy_alone_adds_rm10(): void
    {
        $payload = $this->storedPayload('comprehensive', ['ZURICH TAKAFUL']);
        $payload['data']['shared']['digital_copy']  = 'yes';
        $payload['data']['shared']['physical_copy'] = 'no';
        $payload['data']['shared']['roadtax']       = 0;
        $template = QuoteTemplate::create($payload);

        $this->assertSame(1210.0, $template->columnTotal($template->data['columns'][0]));
    }

    public function test_digital_and_physical_copy_together_add_rm25(): void
    {
        $payload = $this->storedPayload('comprehensive', ['ZURICH TAKAFUL']);
        $payload['data']['shared']['digital_copy']  = 'yes';
        $payload['data']['shared']['physical_copy'] = 'yes';
        $payload['data']['shared']['roadtax']       = 0;
        $template = QuoteTemplate::create($payload);

        $this->assertSame(1225.0, $template->columnTotal($template->data['columns'][0]));
    }

    public function test_physical_copy_is_in_the_roadtax_section_for_every_type(): void
    {
        foreach (array_keys(QuoteTemplate::types()) as $type) {
            $this->assertContains(
                'physical_copy',
                QuoteTemplate::typeConfig($type)['sections']['Roadtax'],
                "physical_copy missing from the Roadtax section for {$type}",
            );
        }
    }

    public function test_roadtax_period_offers_tidak_termasuk_roadtax(): void
    {
        $this->assertSame(
            ['1_year' => '1 TAHUN', '6_months' => '6 BULAN', 'not_included' => 'TIDAK TERMASUK ROADTAX'],
            QuoteTemplate::ROADTAX_PERIOD_OPTIONS,
        );
    }

    public function test_tidak_termasuk_roadtax_can_be_saved_and_shown(): void
    {
        $payload = $this->storedPayload('comprehensive', ['ZURICH TAKAFUL']);
        $payload['data']['shared']['roadtax_period'] = 'not_included';
        $template = QuoteTemplate::create($payload);

        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.show', $template))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('TIDAK TERMASUK ROADTAX', $html);
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

        // Allianz keeps its own list even on types that set a towing list for
        // everyone else -- and a different one per type where asked for (Stage
        // 2 removed 150km and added "no towing" on 3rd Party Fire & Theft;
        // Stage 3 replaced 1st Party Motor's entirely).
        $this->assertSame(
            ['300km' => '300 KM', 'unlimited' => 'UNLIMITED', 'no_towing' => 'NO TOWING'],
            QuoteTemplate::optionsFor('towing', 'third_party_fire_theft', 'ALLIANZ'),
        );
        // Stage 3 replaced Allianz's 1st Party Motor towing list entirely.
        $this->assertSame(
            ['50km' => '50 KM', '600km' => '600 KM'],
            QuoteTemplate::optionsFor('towing', 'motor_first_party', 'ALLIANZ'),
        );
        // Stage 3 replaced Allianz's 1st Party Motor PA list entirely (Azli's list).
        $this->assertSame(
            ['yes' => 'YES', 'no' => 'NO', 'bike_warrior' => 'BIKE WARRIOR'],
            QuoteTemplate::optionsFor('personal_accident', 'motor_first_party', 'ALLIANZ')
        );

        // That must not leak to the other insurers on those types.
        $this->assertSame(
            ['unlimited' => 'UNLIMITED', 'no' => 'NO'],
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

    // ── Stage 1: rename, tinting, merged fields ────────────────────────────

    public function test_allianz_displays_as_allianz_general_insurance_everywhere(): void
    {
        $this->assertSame('ALLIANZ GENERAL INSURANCE', QuoteTemplate::companyLabel('ALLIANZ'));

        $template = QuoteTemplate::create($this->storedPayload('comprehensive', ['ALLIANZ']));
        $preview  = $template->previewData();

        // The stored key is unchanged -- it still has to match COMPANY_TOWING,
        // COMPANY_PA and every quote already saved with this company.
        $this->assertSame('ALLIANZ', $preview['companies'][0]['company']);
        $this->assertSame('ALLIANZ GENERAL INSURANCE', $preview['companies'][0]['label']);

        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.show', $template))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('ALLIANZ GENERAL INSURANCE', $html);
    }

    /**
     * "Sebut Harga" is ambiguous in this app: it's both the admin form's
     * sticky header label over the company-name cards, and the unrelated
     * model section containing Sum Covered/Value. The brand colour belongs
     * on the header cards only -- Sum Covered/Value stay plain.
     */
    public function test_company_name_header_uses_its_own_brand_colour_on_every_type(): void
    {
        foreach (array_keys(QuoteTemplate::types()) as $type) {
            $companies = array_slice(QuoteTemplate::companiesForType($type), 0, 2);
            $template  = QuoteTemplate::create($this->storedPayload($type, $companies));

            $html = $this->actingAs($this->admin())
                ->get(route('quote-templates.show', $template))
                ->assertOk()
                ->getContent();

            foreach ($companies as $company) {
                $this->assertStringContainsString(
                    QuoteTemplate::companyColor($company),
                    $html,
                    "Expected {$company}'s brand colour on {$type}",
                );
            }
        }
    }

    public function test_sum_covered_and_value_cells_are_not_tinted(): void
    {
        $template = QuoteTemplate::create($this->storedPayload('comprehensive', ['ZURICH TAKAFUL', 'ALLIANZ']));

        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.show', $template))
            ->assertOk()
            ->getContent();

        // Zurich's and Allianz's colours appear once each (the header row) --
        // not a second time against a Sum Covered/Value cell.
        $this->assertSame(1, substr_count($html, QuoteTemplate::companyColor('ZURICH TAKAFUL')));
        $this->assertSame(1, substr_count($html, QuoteTemplate::companyColor('ALLIANZ')));
    }

    public function test_ncd_all_driver_and_accident_assist_are_merged_on_comprehensive(): void
    {
        $payload = $this->payloadFor('comprehensive', ['ZURICH TAKAFUL', 'ETIQA TAKAFUL']);
        $payload['columns'][0]['ncd']             = '30';
        $payload['columns'][1]['ncd']             = '30';
        $payload['columns'][0]['all_driver']      = 'yes';
        $payload['columns'][1]['all_driver']      = 'yes';
        $payload['columns'][0]['accident_assist'] = 'yes';
        $payload['columns'][1]['accident_assist'] = 'yes';

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasNoErrors();

        $template = QuoteTemplate::firstOrFail();

        // Stored identically under every company regardless -- the merge is a
        // form/display behaviour, not a change to how the data is kept.
        $this->assertSame('30', $template->data['columns'][0]['ncd']);
        $this->assertSame('30', $template->data['columns'][1]['ncd']);

        $rows = collect($template->previewData()['sections'])
            ->flatMap(fn ($section) => $section['rows'])
            ->keyBy('label');

        $this->assertTrue($rows['No Claim Discount (NCD) %']['merged']);
        $this->assertSame('30%', $rows['No Claim Discount (NCD) %']['value']);
        $this->assertArrayNotHasKey('cells', $rows['No Claim Discount (NCD) %']);

        $this->assertTrue($rows['All Driver']['merged']);
        $this->assertTrue($rows['Accident / Breakdown Assist']['merged']);

        // Unaffected -- not in comprehensive's merged_fields list.
        $this->assertFalse($rows['Sum Covered (RM)']['merged']);
    }

    public function test_blank_comprehensive_create_form_renders_the_merged_fields(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.create', ['type' => 'comprehensive']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('f.merged.ncd', $html);
        $this->assertStringContainsString('f.merged.all_driver', $html);
        $this->assertStringContainsString('f.merged.accident_assist', $html);
    }

    public function test_merged_fields_are_not_merged_on_other_types(): void
    {
        $payload  = $this->payloadFor('motor_first_party', ['ZURICH TAKAFUL', 'ETIQA TAKAFUL']);
        $template = QuoteTemplate::create([
            'type'               => 'motor_first_party',
            'title'              => QuoteTemplate::typeConfig('motor_first_party')['title'],
            'vehicle_reg_number' => strtoupper($payload['vehicle_reg_number']),
            'vehicle_model'      => $payload['vehicle_model'],
            'data'               => ['shared' => $payload['shared'], 'columns' => $payload['columns']],
        ]);

        $rows = collect($template->previewData()['sections'])
            ->flatMap(fn ($section) => $section['rows'])
            ->keyBy('label');

        $this->assertFalse($rows['No Claim Discount (NCD) %']['merged']);
        $this->assertArrayHasKey('cells', $rows['No Claim Discount (NCD) %']);
    }

    // ── Stage 2: 3rd Party Fire & Theft ─────────────────────────────────────

    public function test_fire_and_theft_gains_an_all_driver_field_that_is_merged(): void
    {
        $this->assertContains('all_driver', QuoteTemplate::typeConfig('third_party_fire_theft')['sections']['Add On']);

        $payload = $this->payloadFor('third_party_fire_theft', ['ZURICH TAKAFUL', 'ETIQA TAKAFUL']);
        $template = QuoteTemplate::create([
            'type'               => 'third_party_fire_theft',
            'title'              => QuoteTemplate::typeConfig('third_party_fire_theft')['title'],
            'vehicle_reg_number' => strtoupper($payload['vehicle_reg_number']),
            'vehicle_model'      => $payload['vehicle_model'],
            'data'               => ['shared' => $payload['shared'], 'columns' => $payload['columns']],
        ]);

        $rows = collect($template->previewData()['sections'])
            ->flatMap(fn ($section) => $section['rows'])
            ->keyBy('label');

        $this->assertTrue($rows['All Driver']['merged']);
        $this->assertTrue($rows['No Claim Discount (NCD) %']['merged']);
        $this->assertTrue($rows['Accident / Breakdown Assist']['merged']);
    }

    public function test_zurich_towing_gains_600km_on_fire_and_theft(): void
    {
        $this->assertSame(
            ['no_towing' => 'NO TOWING', 'unlimited' => 'UNLIMITED', '600km' => '600 KM'],
            QuoteTemplate::optionsFor('towing', 'third_party_fire_theft', 'ZURICH TAKAFUL'),
        );

        // Doesn't leak to the type-wide list other companies use.
        $this->assertSame(
            ['no_towing' => 'NO TOWING', 'unlimited' => 'UNLIMITED'],
            QuoteTemplate::optionsFor('towing', 'third_party_fire_theft', 'ETIQA TAKAFUL'),
        );
    }

    public function test_allianz_towing_drops_150km_and_gains_no_towing_on_fire_and_theft(): void
    {
        $options = QuoteTemplate::optionsFor('towing', 'third_party_fire_theft', 'ALLIANZ');

        $this->assertArrayNotHasKey('150km', $options);
        $this->assertSame(
            ['300km' => '300 KM', 'unlimited' => 'UNLIMITED', 'no_towing' => 'NO TOWING'],
            $options,
        );
    }

    public function test_blank_fire_and_theft_create_form_renders(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.create', ['type' => 'third_party_fire_theft']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('f.merged.all_driver', $html);
        $this->assertStringContainsString('600 KM', $html);
    }

    // ── Stage 3: 1st Party Motor ────────────────────────────────────────────

    public function test_all_rider_is_relabelled_rider_type_on_motor_first_party_only(): void
    {
        $this->assertSame('Rider Type', QuoteTemplate::fieldLabel('all_rider', 'motor_first_party'));
        // 3rd Party Motor is untouched by this rename.
        $this->assertSame('All Rider', QuoteTemplate::fieldLabel('all_rider', 'motor_third_party'));
    }

    public function test_rider_type_options_are_all_rider_only_except_allianz(): void
    {
        foreach (['ZURICH TAKAFUL', 'ETIQA TAKAFUL', 'TAKAFUL IKHLAS', 'TAKAFUL MALAYSIA'] as $company) {
            $this->assertSame(
                ['all' => 'ALL RIDER'],
                QuoteTemplate::optionsFor('all_rider', 'motor_first_party', $company),
                "{$company} should only offer ALL RIDER",
            );
        }

        $this->assertSame(
            ['single' => 'SINGLE RIDER', 'all' => 'ALL RIDER'],
            QuoteTemplate::optionsFor('all_rider', 'motor_first_party', 'ALLIANZ'),
        );

        // 3rd Party Motor keeps the plain yesno options -- unaffected.
        $this->assertSame(
            ['yes' => 'YES', 'no' => 'NO'],
            QuoteTemplate::optionsFor('all_rider', 'motor_third_party', 'ZURICH TAKAFUL'),
        );
    }

    public function test_a_new_motor_first_party_quote_defaults_rider_type_to_all_rider(): void
    {
        $this->assertSame('all', QuoteTemplate::defaultColumn('motor_first_party', 'ZURICH TAKAFUL')['all_rider']);
        $this->assertSame('all', QuoteTemplate::defaultColumn('motor_first_party', 'ALLIANZ')['all_rider']);
    }

    public function test_only_accident_assist_is_merged_on_motor_first_party(): void
    {
        $template = QuoteTemplate::create($this->storedPayload('motor_first_party', ['ZURICH TAKAFUL', 'ETIQA TAKAFUL']));

        $rows = collect($template->previewData()['sections'])
            ->flatMap(fn ($section) => $section['rows'])
            ->keyBy('label');

        $this->assertTrue($rows['Accident / Breakdown Assist']['merged']);
        // NCD % and Rider Type stay per-company on this type, unlike Comprehensive.
        $this->assertFalse($rows['No Claim Discount (NCD) %']['merged']);
        $this->assertFalse($rows['Rider Type']['merged']);
    }

    public function test_additional_benefits_only_apply_to_zurich_and_allianz_on_motor_first_party(): void
    {
        $this->assertTrue(QuoteTemplate::fieldAppliesTo('additional_benefits', 'motor_first_party', 'ZURICH TAKAFUL'));
        $this->assertTrue(QuoteTemplate::fieldAppliesTo('additional_benefits', 'motor_first_party', 'ALLIANZ'));
        foreach (['ETIQA TAKAFUL', 'TAKAFUL IKHLAS', 'TAKAFUL MALAYSIA'] as $company) {
            $this->assertFalse(
                QuoteTemplate::fieldAppliesTo('additional_benefits', 'motor_first_party', $company),
                "{$company} should not have Additional Benefits",
            );
        }

        // Preview renders a blank cell (null), not an "unanswered" dash, for a
        // company this field doesn't apply to at all.
        $template = QuoteTemplate::create($this->storedPayload('motor_first_party', ['ZURICH TAKAFUL', 'ETIQA TAKAFUL']));
        $row = collect($template->previewData()['sections'])
            ->flatMap(fn ($section) => $section['rows'])
            ->keyBy('label')['Additional Benefits'];

        $this->assertNotEquals(['-'], $row['cells'][0]); // Zurich: a real value
        $this->assertNull($row['cells'][1]); // Etiqa: field doesn't apply at all
    }

    public function test_zurich_additional_benefits_default_to_everything_except_none(): void
    {
        $this->assertSame(
            ['helmet', 'pa5500', 'perils', 'towing_cost'],
            QuoteTemplate::defaultColumn('motor_first_party', 'ZURICH TAKAFUL')['additional_benefits'],
        );
    }

    public function test_allianz_has_its_own_additional_benefits_list_and_default(): void
    {
        $this->assertSame(
            [
                'death_disablement' => 'DEATH / PERMANENT DISABLEMENT - AUTHORISED RIDER & PILLION SUM INSURED (RM)25,000',
                'hospital_income'   => 'HOSPITAL INCOME - AUTHORISED RIDER & PILLION RM100 PER DAY / UP TO 60 DAYS',
                'flood_relief'      => 'FLOOD RELIEF BENEFIT LIMIT RM1,000',
                'special_perils'    => 'INCLUSION OF SPECIAL PERILS',
                'none'              => 'None',
            ],
            QuoteTemplate::optionsFor('additional_benefits', 'motor_first_party', 'ALLIANZ'),
        );

        $this->assertSame(
            ['death_disablement', 'hospital_income', 'flood_relief', 'special_perils'],
            QuoteTemplate::defaultColumn('motor_first_party', 'ALLIANZ')['additional_benefits'],
        );
    }

    public function test_personal_accident_only_applies_to_zurich_etiqa_and_allianz_on_motor_first_party(): void
    {
        foreach (['ZURICH TAKAFUL', 'ETIQA TAKAFUL', 'ALLIANZ'] as $company) {
            $this->assertTrue(QuoteTemplate::fieldAppliesTo('personal_accident', 'motor_first_party', $company));
        }
        foreach (['TAKAFUL IKHLAS', 'TAKAFUL MALAYSIA'] as $company) {
            $this->assertFalse(QuoteTemplate::fieldAppliesTo('personal_accident', 'motor_first_party', $company));
        }

        $this->assertSame(['yes' => 'YES', 'no' => 'NO'], QuoteTemplate::optionsFor('personal_accident', 'motor_first_party', 'ZURICH TAKAFUL'));
        $this->assertSame(['yes' => 'YES', 'no' => 'NO', 'myrider' => 'MYRIDER'], QuoteTemplate::optionsFor('personal_accident', 'motor_first_party', 'ETIQA TAKAFUL'));
        $this->assertSame(['yes' => 'YES', 'no' => 'NO', 'bike_warrior' => 'BIKE WARRIOR'], QuoteTemplate::optionsFor('personal_accident', 'motor_first_party', 'ALLIANZ'));
    }

    public function test_blank_motor_first_party_create_form_renders(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.create', ['type' => 'motor_first_party']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Rider Type', $html);
        $this->assertStringContainsString('SINGLE RIDER', $html);
        $this->assertStringContainsString('MYRIDER', $html);
        $this->assertStringContainsString('BIKE WARRIOR', $html);
        $this->assertStringContainsString('3X UNLIMITED TOWING', $html);
        $this->assertStringContainsString('INCLUSION OF SPECIAL PERILS', $html);
    }

    public function test_motor_first_party_quote_can_be_saved_with_allianz_benefits_and_rider_type(): void
    {
        $payload = $this->payloadFor('motor_first_party', ['ALLIANZ']);
        $payload['columns'][0]['all_rider']            = 'single';
        $payload['columns'][0]['personal_accident']    = 'bike_warrior';
        $payload['columns'][0]['additional_benefits']  = ['death_disablement', 'special_perils'];

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasNoErrors();

        $column = QuoteTemplate::firstOrFail()->data['columns'][0];
        $this->assertSame('single', $column['all_rider']);
        $this->assertSame('bike_warrior', $column['personal_accident']);
        $this->assertSame(['death_disablement', 'special_perils'], $column['additional_benefits']);
    }

    // ── Stage 4: 3rd Party Motor ────────────────────────────────────────────

    public function test_all_rider_accident_assist_and_sum_covered_are_merged_on_motor_third_party(): void
    {
        $this->assertSame(
            ['sum_covered', 'all_rider', 'accident_assist'],
            QuoteTemplate::mergedFields('motor_third_party'),
        );

        $template = QuoteTemplate::create($this->storedPayload('motor_third_party', ['ZURICH TAKAFUL', 'ETIQA TAKAFUL']));
        $rows = collect($template->previewData()['sections'])
            ->flatMap(fn ($section) => $section['rows'])
            ->keyBy('label');

        $this->assertTrue($rows['All Rider']['merged']);
        $this->assertTrue($rows['Accident / Breakdown Assist']['merged']);
        $this->assertTrue($rows['Sum Covered (RM)']['merged']);
        // Unlike 1st Party Motor, this type doesn't rename the field.
        $this->assertSame('All Rider', $rows['All Rider']['label']);
    }

    public function test_accident_assist_starts_unanswered_on_motor_third_party_only(): void
    {
        $this->assertNull(QuoteTemplate::fieldDefaultFor('accident_assist', 'motor_third_party'));
        $this->assertTrue(QuoteTemplate::isOptional('accident_assist', 'motor_third_party'));

        // Every other type keeps the normal "yes" default and stays required.
        $this->assertSame('yes', QuoteTemplate::fieldDefaultFor('accident_assist', 'motor_first_party'));
        $this->assertFalse(QuoteTemplate::isOptional('accident_assist', 'motor_first_party'));
        $this->assertFalse(QuoteTemplate::isOptional('accident_assist')); // no type given at all
    }

    public function test_sum_covered_is_locked_at_zero_on_motor_third_party(): void
    {
        $this->assertSame(0, QuoteTemplate::fieldDefaultFor('sum_covered', 'motor_third_party'));
        $this->assertContains('sum_covered', QuoteTemplate::typeConfig('motor_third_party')['field_readonly']);

        // The real form always sends 0 here, via the merged field's hidden
        // per-company mirror input (readonly in the browser, so it's never
        // anything else) -- simulate that exact submission rather than
        // payloadFor()'s generic 50000 fixture, which isn't type-aware of
        // which fields are locked.
        $payload = $this->payloadFor('motor_third_party', ['ZURICH TAKAFUL']);
        $payload['columns'][0]['sum_covered'] = 0;

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertSame('0', (string) QuoteTemplate::firstOrFail()->data['columns'][0]['sum_covered']);
    }

    public function test_additional_benefits_is_removed_from_motor_third_party(): void
    {
        $this->assertNotContains('additional_benefits', QuoteTemplate::fieldKeys('motor_third_party', 'company'));
        foreach (QuoteTemplate::typeConfig('motor_third_party')['sections'] as $rows) {
            $this->assertNotContains('additional_benefits', $rows);
        }
    }

    public function test_add_on_benefit_is_unchanged_on_motor_third_party(): void
    {
        $this->assertContains('add_on_benefit', QuoteTemplate::fieldKeys('motor_third_party', 'company'));
        $this->assertSame('no', QuoteTemplate::fieldDefaultFor('add_on_benefit', 'motor_third_party'));
    }

    public function test_blank_motor_third_party_create_form_renders(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('quote-templates.create', ['type' => 'motor_third_party']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('f.merged.sum_covered', $html);
        $this->assertStringContainsString('readonly', $html);
        $this->assertStringNotContainsString('Additional Benefits', $html);
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

    /** Takaful Ikhlas is quoted on every type, motor included. */
    public function test_takaful_ikhlas_is_offered_on_every_type(): void
    {
        foreach (array_keys(QuoteTemplate::types()) as $type) {
            $this->assertContains('TAKAFUL IKHLAS', QuoteTemplate::companiesForType($type), "missing on {$type}");
        }

        // The other motor insurers are untouched.
        foreach (['ZURICH TAKAFUL', 'ETIQA TAKAFUL', 'TAKAFUL MALAYSIA', 'ALLIANZ'] as $company) {
            $this->assertContains($company, QuoteTemplate::companiesForType('motor_first_party'));
        }

        // Kurnia remains non-motor.
        $this->assertNotContains('KURNIA INSURANS', QuoteTemplate::companiesForType('motor_first_party'));
    }

    public function test_a_motor_third_party_quote_can_be_saved_with_takaful_ikhlas(): void
    {
        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $this->payloadFor('motor_third_party', ['TAKAFUL IKHLAS']))
            ->assertSessionHasNoErrors();

        $this->assertSame('TAKAFUL IKHLAS', QuoteTemplate::firstOrFail()->data['columns'][0]['company']);
    }

    public function test_a_motor_first_party_quote_can_be_saved_with_takaful_ikhlas(): void
    {
        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $this->payloadFor('motor_first_party', ['TAKAFUL IKHLAS']))
            ->assertSessionHasNoErrors();

        $this->assertSame('TAKAFUL IKHLAS', QuoteTemplate::firstOrFail()->data['columns'][0]['company']);
    }
}
