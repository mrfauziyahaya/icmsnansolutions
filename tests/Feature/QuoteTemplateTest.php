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
}
