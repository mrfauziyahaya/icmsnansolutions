<?php

namespace Tests\Feature;

use App\Models\QuoteTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin quote comparison builder. The columns shown are driven by a
 * multi-select of insurance companies, so the stored quote must hold exactly
 * the companies the admin ticked — in order, no more, no fewer.
 */
class QuoteTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function payload(array $companies): array
    {
        $columns = [];
        foreach ($companies as $name) {
            $columns[] = [
                'company'            => $name,
                'value'              => 'market_value',
                'towing'             => '300km',
                'accident_assist'    => 'yes',
                'ncd'                => 25,
                'all_driver'         => 'yes',
                'personal_accident'  => 'no',
                'vehicle_inspection' => 'no',
                'insurance_takaful'  => 1200,
            ];
        }

        return [
            'vehicle_reg_number' => 'wxy1234',
            'vehicle_model'      => 'Test Model',
            'shared'             => ['sum_covered' => 50000, 'cermin' => 0, 'bencana_alam' => 'no', 'digital_copy' => 'yes', 'roadtax' => 90],
            'columns'            => $columns,
        ];
    }

    public function test_it_stores_only_the_selected_companies(): void
    {
        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $this->payload(['ZURICH TAKAFUL', 'KURNIA INSURANS']))
            ->assertRedirect();

        $t = QuoteTemplate::sole();
        $this->assertCount(2, $t->data['columns']);
        $this->assertSame(['ZURICH TAKAFUL', 'KURNIA INSURANS'], array_column($t->data['columns'], 'company'));
        $this->assertSame('WXY1234', $t->vehicle_reg_number);   // upper-cased
    }

    public function test_it_accepts_a_single_company(): void
    {
        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $this->payload(['TAKAFUL MALAYSIA']))
            ->assertRedirect();

        $this->assertCount(1, QuoteTemplate::sole()->data['columns']);
    }

    public function test_it_rejects_zero_companies(): void
    {
        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $this->payload([]))
            ->assertSessionHasErrors('columns');

        $this->assertSame(0, QuoteTemplate::count());
    }

    public function test_it_rejects_an_unknown_company(): void
    {
        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $this->payload(['SOME RANDOM INSURER']))
            ->assertSessionHasErrors('columns.0.company');
    }

    public function test_duplicate_company_selections_are_collapsed(): void
    {
        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $this->payload(['ETIQA TAKAFUL', 'ETIQA TAKAFUL']))
            ->assertRedirect();

        $this->assertCount(1, QuoteTemplate::sole()->data['columns']);
    }

    public function test_the_create_form_renders_the_company_multiselect(): void
    {
        $html = $this->actingAs($this->admin())->get(route('quote-templates.create'))->assertOk()->getContent();

        foreach (array_keys(QuoteTemplate::ALL_COMPANIES) as $company) {
            $this->assertStringContainsString($company, $html);
        }
    }

    /** Each company defaults to the first towing / PA option it actually offers. */
    public function test_default_column_uses_each_companys_own_first_options(): void
    {
        $kurnia = QuoteTemplate::defaultColumn('KURNIA INSURANS');
        $this->assertSame('100km', $kurnia['towing']);
        $this->assertSame('auto365', $kurnia['personal_accident']);

        $zurich = QuoteTemplate::defaultColumn('ZURICH TAKAFUL');
        $this->assertSame('150km', $zurich['towing']);
        $this->assertSame('yes', $zurich['personal_accident']);
    }

    public function test_new_quotes_default_roadtax_to_70(): void
    {
        $this->assertSame(70, QuoteTemplate::blankData()['shared']['roadtax']);
    }

    /** The form ships each company's own towing/PA options, so the dropdowns differ. */
    public function test_form_carries_per_company_options(): void
    {
        $html = $this->actingAs($this->admin())->get(route('quote-templates.create'))->assertOk()->getContent();

        // Kurnia-only values and Takaful Malaysia-only values must be present.
        $this->assertStringContainsString('AUTO365', $html);
        $this->assertStringContainsString('MOTORIST PLAN 3', $html);
        $this->assertStringContainsString('100 KM', $html);
        // NCD is now a fixed dropdown.
        $this->assertStringContainsString('38.33%', $html);
    }

    /** The 38.33% NCD value must validate. */
    public function test_it_accepts_the_38_33_ncd_value(): void
    {
        $payload = $this->payload(['ZURICH TAKAFUL']);
        $payload['columns'][0]['ncd'] = '38.33';

        $this->actingAs($this->admin())
            ->post(route('quote-templates.store'), $payload)
            ->assertRedirect();

        $this->assertSame('38.33', (string) QuoteTemplate::sole()->data['columns'][0]['ncd']);
    }
}
