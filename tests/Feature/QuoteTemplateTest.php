<?php

namespace Tests\Feature;

use App\Models\QuoteTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_new_quotes_default_roadtax_to_70(): void
    {
        $this->assertSame(70, QuoteTemplate::blankData('comprehensive')['shared']['roadtax']);
    }
}
