<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class QuoteTemplate extends Model
{
    protected $fillable = [
        'type',
        'title',
        'vehicle_reg_number',
        'vehicle_model',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public const DEFAULT_TYPE = 'comprehensive';

    /**
     * Every insurance company, mapped to its logo under public/ (null = no logo,
     * show the name). Which appear on a quote is the admin's multi-select.
     */
    public const ALL_COMPANIES = [
        'ZURICH TAKAFUL'   => 'images/zurich-takaful.png',
        'ETIQA TAKAFUL'    => 'images/Logo-Insuran-3.webp',
        'TAKAFUL IKHLAS'   => 'images/Logo-Insuran-5.webp',
        'TAKAFUL MALAYSIA' => 'images/takaful-malaysia-logo.png',
        'KURNIA INSURANS'  => 'images/kurnia-insurance-logo.png',
    ];

    /** The motor quote types only compare these three. */
    public const MOTOR_COMPANIES = ['ZURICH TAKAFUL', 'ETIQA TAKAFUL', 'TAKAFUL MALAYSIA'];

    // ── Option maps ───────────────────────────────────────────────────────────

    public const VALUE_OPTIONS = [
        'market_value' => 'MARKET VALUE',
        'agreed_value' => 'AGREED VALUE',
    ];

    public const YESNO_OPTIONS = ['yes' => 'YES', 'no' => 'NO'];

    public const ROADTAX_PERIOD_OPTIONS = ['1_year' => '1 TAHUN', '6_months' => '6 BULAN'];

    /** All towing labels; which apply is per type / per company below. */
    public const TOWING_OPTIONS = [
        'no_towing' => 'NO TOWING',
        '30km'      => '30 KM',
        '50km'      => '50 KM',
        '100km'     => '100 KM',
        '150km'     => '150 KM',
        '200km'     => '200 KM',
        '300km'     => '300 KM',
        'unlimited' => 'UNLIMITED',
        'no'        => 'NO',
    ];

    /** Towing per company for the comprehensive type (others use a fixed list). */
    public const COMPANY_TOWING = [
        'ZURICH TAKAFUL'   => ['150km', '300km', 'unlimited'],
        'ETIQA TAKAFUL'    => ['200km', 'unlimited', 'no'],
        'TAKAFUL IKHLAS'   => ['150km', 'unlimited', 'no'],
        'TAKAFUL MALAYSIA' => ['300km', 'unlimited', 'no'],
        'KURNIA INSURANS'  => ['100km', 'unlimited'],
    ];

    public const NCD_OPTIONS = [
        '0' => '0%', '25' => '25%', '35' => '35%', '38.33' => '38.33%', '45' => '45%', '55' => '55%',
    ];

    public const NCD_MOTOR_OPTIONS = [
        '0' => '0%', '15' => '15%', '20' => '20%', '25' => '25%', '30' => '30%', '38.33' => '38.33%', '45' => '45%', '55' => '55%',
    ];

    public const PA_OPTIONS = [
        'no'              => 'NO',
        'yes'             => 'YES',
        'cash_care'       => 'CASH CARE P.A.',
        'z_drive'         => 'Z-DRIVE',
        'motorist_plan_3' => 'MOTORIST PLAN 3',
        'motorist_plan_4' => 'MOTORIST PLAN 4',
        'auto365'         => 'AUTO365',
    ];

    public const COMPANY_PA = [
        'ZURICH TAKAFUL'   => ['yes', 'no', 'cash_care', 'z_drive'],
        'ETIQA TAKAFUL'    => ['yes', 'no', 'cash_care'],
        'TAKAFUL IKHLAS'   => ['yes', 'no', 'cash_care'],
        'TAKAFUL MALAYSIA' => ['yes', 'no', 'cash_care', 'motorist_plan_3', 'motorist_plan_4'],
        'KURNIA INSURANS'  => ['auto365'],
    ];

    /** Multi-select benefits on the motor quotes (each shows as its own line). */
    public const ADDITIONAL_BENEFITS = [
        'helmet'      => 'Helmet Replacement RM50',
        'pa5500'      => 'Personal Accident RM5500',
        'perils'      => 'Limited Special Perils Allowance RM1000',
        'towing_cost' => 'Breakdown/Emergency Towing Cost RM160',
        'none'        => 'None',
    ];

    /** @var array<string, array{label: string}> */
    public const INSTALMENTS = [
        'atome'         => ['label' => 'ATOME / GRAB PAYLATER'],
        'ahapay'        => ['label' => 'AHAPAY'],
        'spaylater'     => ['label' => 'SPAYLATER'],
        'directlending' => ['label' => 'DIRECT LENDING'],
    ];

    /**
     * Field registry — scope (shared once vs per company), input type, label,
     * and which option map to use. Per-company/per-type option lists are
     * resolved by optionsFor().
     */
    public const FIELDS = [
        'sum_covered'         => ['scope' => 'company', 'input' => 'number',      'label' => 'Sum Covered (RM)'],
        'value'               => ['scope' => 'company', 'input' => 'select',      'label' => 'Value',                       'options' => 'value'],
        'towing'              => ['scope' => 'company', 'input' => 'select',      'label' => 'Towing',                      'options' => 'towing'],
        'accident_assist'     => ['scope' => 'company', 'input' => 'select',      'label' => 'Accident / Breakdown Assist', 'options' => 'yesno'],
        'ncd'                 => ['scope' => 'company', 'input' => 'select',      'label' => 'No Claim Discount (NCD) %',   'options' => 'ncd'],
        'all_driver'          => ['scope' => 'company', 'input' => 'select',      'label' => 'All Driver',                  'options' => 'yesno'],
        'all_rider'           => ['scope' => 'company', 'input' => 'select',      'label' => 'All Rider',                   'options' => 'yesno'],
        'personal_accident'   => ['scope' => 'company', 'input' => 'select',      'label' => 'Personal Accident',           'options' => 'pa'],
        'additional_benefits' => ['scope' => 'company', 'input' => 'multiselect', 'label' => 'Additional Benefits',         'options' => 'additional_benefits'],
        'add_on_benefit'      => ['scope' => 'company', 'input' => 'select',      'label' => 'Add On Benefit',              'options' => 'yesno'],
        'cermin'              => ['scope' => 'shared',  'input' => 'number',      'label' => 'Cermin (RM)'],
        'bencana_alam'        => ['scope' => 'shared',  'input' => 'select',      'label' => 'Bencana Alam',                'options' => 'yesno'],
        'digital_copy'        => ['scope' => 'shared',  'input' => 'select',      'label' => 'Digital Copy (MyJPJ)',        'options' => 'yesno'],
        'vehicle_inspection'  => ['scope' => 'company', 'input' => 'select',      'label' => 'Vehicle Inspection Required', 'options' => 'yesno'],
        'insurance_takaful'   => ['scope' => 'company', 'input' => 'number',      'label' => 'Insurance / Takaful (RM)'],
        'roadtax_period'      => ['scope' => 'shared',  'input' => 'select',      'label' => 'Tempoh Roadtax',              'options' => 'roadtax_period'],
        'roadtax'             => ['scope' => 'shared',  'input' => 'number',      'label' => 'Roadtax (RM)'],
    ];

    /**
     * Select fields the agent may leave unanswered. They start blank rather than
     * guessing an option, render an empty choice in the form, and show "-" on the
     * quote. Number fields are always optional, so they are not listed here.
     */
    public const OPTIONAL_FIELDS = ['towing', 'personal_accident', 'vehicle_inspection'];

    public static function isOptional(string $field): bool
    {
        return in_array($field, self::OPTIONAL_FIELDS, true);
    }

    // ── Types ───────────────────────────────────────────────────────────────

    /** @return array<string, array<string, mixed>> */
    public static function types(): array
    {
        $all   = array_keys(self::ALL_COMPANIES);
        $motor = self::MOTOR_COMPANIES;

        return [
            'comprehensive' => [
                'label'     => '1st Party Comprehensive',
                'title'     => 'First Party Comprehensive',
                'companies' => $all,
                'ncd'       => self::NCD_OPTIONS,
                'sections'  => [
                    'Sebut Harga'        => ['sum_covered', 'value'],
                    'Insurance Benefits' => ['towing', 'accident_assist', 'ncd'],
                    'Add On'             => ['cermin', 'bencana_alam', 'all_driver', 'personal_accident'],
                    'Roadtax'            => ['digital_copy', 'vehicle_inspection'],
                    'Jumlah'             => ['insurance_takaful', 'roadtax_period', 'roadtax'],
                ],
            ],
            'third_party_fire_theft' => [
                'label'     => '3rd Party',
                'title'     => '3rd Party Fire & Theft',
                'companies' => $all,
                'ncd'       => self::NCD_OPTIONS,
                'towing'    => ['no_towing', 'unlimited'],
                'sections'  => [
                    'Sebut Harga'        => ['sum_covered', 'value'],
                    'Insurance Benefits' => ['towing', 'accident_assist', 'ncd'],
                    'Add On'             => ['personal_accident'],
                    'Roadtax'            => ['digital_copy', 'vehicle_inspection'],
                    'Jumlah'             => ['insurance_takaful', 'roadtax_period', 'roadtax'],
                ],
            ],
            'motor_first_party' => [
                'label'     => '1st Party (Motor)',
                'title'     => 'First Party Comprehensive',
                'companies' => $motor,
                'ncd'       => self::NCD_MOTOR_OPTIONS,
                'towing'    => ['no_towing', 'unlimited', '50km', '30km'],
                'sections'  => [
                    'Sebut Harga'        => ['sum_covered', 'value'],
                    'Insurance Benefits' => ['all_rider', 'accident_assist', 'ncd', 'additional_benefits'],
                    'Add On'             => ['add_on_benefit', 'towing'],
                    'Roadtax'            => ['digital_copy', 'vehicle_inspection'],
                    'Jumlah'             => ['insurance_takaful', 'roadtax_period', 'roadtax'],
                ],
            ],
            'motor_third_party' => [
                'label'     => '3rd Party (Motor)',
                'title'     => 'Motor Third Party',
                'companies' => $motor,
                'ncd'       => self::NCD_MOTOR_OPTIONS,
                'sections'  => [
                    'Sebut Harga'        => ['sum_covered'],
                    'Insurance Benefits' => ['all_rider', 'accident_assist', 'ncd', 'additional_benefits'],
                    'Add On'             => ['add_on_benefit'],
                    'Roadtax'            => ['digital_copy', 'vehicle_inspection'],
                    'Jumlah'             => ['insurance_takaful', 'roadtax_period', 'roadtax'],
                ],
            ],
        ];
    }

    public static function typeExists(string $type): bool
    {
        return array_key_exists($type, self::types());
    }

    public static function typeConfig(?string $type): array
    {
        return self::types()[$type] ?? self::types()[self::DEFAULT_TYPE];
    }

    public function title(): string
    {
        return $this->title ?: self::typeConfig($this->type)['title'];
    }

    /** Companies this quote type compares. */
    public static function companiesForType(string $type): array
    {
        return self::typeConfig($type)['companies'];
    }

    /** Field keys of the given scope used anywhere in a type's sections. */
    public static function fieldKeys(string $type, string $scope): array
    {
        $keys = [];
        foreach (self::typeConfig($type)['sections'] as $rows) {
            foreach ($rows as $key) {
                if ((self::FIELDS[$key]['scope'] ?? null) === $scope) {
                    $keys[] = $key;
                }
            }
        }

        return array_values(array_unique($keys));
    }

    // ── Options ───────────────────────────────────────────────────────────────

    /**
     * The [value => label] option map for a select/multiselect field, resolved
     * for the given type and (for per-company fields) company.
     */
    public static function optionsFor(string $field, string $type, ?string $company = null): array
    {
        $source = self::FIELDS[$field]['options'] ?? null;
        $config = self::typeConfig($type);

        // Pull labels in the exact order the keys are listed (not the label map's).
        $ordered = fn (array $keys, array $labels) => array_reduce(
            $keys,
            fn ($carry, $k) => $carry + [$k => $labels[$k] ?? $k],
            []
        );

        return match ($source) {
            'value'          => self::VALUE_OPTIONS,
            'yesno'          => self::YESNO_OPTIONS,
            'roadtax_period' => self::ROADTAX_PERIOD_OPTIONS,
            'ncd'            => $config['ncd'] ?? self::NCD_OPTIONS,
            'additional_benefits' => self::ADDITIONAL_BENEFITS,
            'pa'             => $ordered(self::COMPANY_PA[$company] ?? [], self::PA_OPTIONS),
            'towing'         => $ordered($config['towing'] ?? self::COMPANY_TOWING[$company] ?? [], self::TOWING_OPTIONS),
            default          => [],
        };
    }

    /** Options as an ordered [{v,l}] list (for Alpine x-for). */
    public static function optionList(string $field, string $type, ?string $company = null): array
    {
        $out = [];
        foreach (self::optionsFor($field, $type, $company) as $v => $l) {
            $out[] = ['v' => (string) $v, 'l' => $l];
        }

        return $out;
    }

    // ── Defaults ────────────────────────────────────────────────────────────

    public static function logoFor(?string $company): ?string
    {
        $path = self::ALL_COMPANIES[$company] ?? null;

        return $path && is_file(public_path($path)) ? $path : null;
    }

    /** Default value for one company field. */
    public static function fieldDefault(string $field): mixed
    {
        // Optional selects (towing, personal_accident, vehicle_inspection) fall
        // through to null so the agent picks them deliberately.
        return match ($field) {
            'value'               => 'market_value',
            'ncd'                 => '0',
            'accident_assist',
            'all_driver',
            'all_rider'           => 'yes',
            'add_on_benefit'      => 'no',
            'additional_benefits' => [],
            default               => null,   // sum_covered, insurance_takaful, optional selects
        };
    }

    /** Default company column for a type. */
    public static function defaultColumn(string $type, string $company): array
    {
        $col = ['company' => $company];
        foreach (self::fieldKeys($type, 'company') as $field) {
            $col[$field] = self::fieldDefault($field);
        }

        return $col;
    }

    public static function sharedDefault(string $field): mixed
    {
        return match ($field) {
            'bencana_alam'   => 'no',
            'digital_copy'   => 'yes',
            'roadtax_period' => '1_year',
            default          => null,   // cermin, roadtax
        };
    }

    /**
     * A blank template for a type. No company is pre-ticked — the agent chooses
     * which insurers this quote compares, so an unticked list is the honest
     * starting point rather than three arbitrary defaults they must undo.
     */
    public static function blankData(string $type = self::DEFAULT_TYPE): array
    {
        $shared = [];
        foreach (self::fieldKeys($type, 'shared') as $field) {
            $shared[$field] = self::sharedDefault($field);
        }

        return [
            'shared'  => $shared,
            'columns' => [],
        ];
    }

    // ── Calculations ──────────────────────────────────────────────────────────

    public function columnTotal(array $column): float
    {
        $shared  = $this->data['shared'] ?? [];
        $roadtax = (float) ($shared['roadtax'] ?? 0);
        $digital = ($shared['digital_copy'] ?? 'no') === 'yes' ? 5 : 0;

        return round((float) ($column['insurance_takaful'] ?? 0) + $roadtax + $digital, 2);
    }

    public static function instalment(string $provider, float $total): float
    {
        return match ($provider) {
            'atome'         => round($total * 1.08, 2),
            'ahapay'        => round($total * 1.035, 0),
            'spaylater'     => round($total * 1.02, 2),
            'directlending' => round($total, 2),
            default         => $total,
        };
    }

    // ── Preview ───────────────────────────────────────────────────────────────

    /** Display string(s) for one field on one column/shared value. */
    public function display(string $field, string $type, array $col, array $shared): string|array
    {
        $def   = self::FIELDS[$field];
        $scope = $def['scope'];
        $raw   = $scope === 'shared' ? ($shared[$field] ?? null) : ($col[$field] ?? null);

        if ($def['input'] === 'number') {
            return is_null($raw) || $raw === '' || (float) $raw == 0 ? '-' : 'RM ' . number_format((float) $raw, 2);
        }

        if ($def['input'] === 'multiselect') {
            $labels = array_map(fn ($k) => self::ADDITIONAL_BENEFITS[$k] ?? $k, array_values((array) $raw));
            $labels = array_filter($labels, fn ($l) => $l !== 'None');

            return $labels ?: ['-'];
        }

        $company = $col['company'] ?? null;

        return self::optionsFor($field, $type, $company)[$raw] ?? '-';
    }

    /**
     * Everything the preview needs, laid out as sections → rows → per-company
     * cells, plus company headers, totals and instalments.
     */
    public function previewData(): array
    {
        $type    = $this->type ?: self::DEFAULT_TYPE;
        $shared  = $this->data['shared'] ?? [];
        $columns = $this->data['columns'] ?? [];

        $companies = array_map(fn ($c) => [
            'company' => $c['company'] ?? '',
            'logo'    => self::logoFor($c['company'] ?? null),
        ], $columns);

        $sections = [];
        foreach (self::typeConfig($type)['sections'] as $name => $rows) {
            $renderedRows = [];
            foreach ($rows as $field) {
                $def = self::FIELDS[$field];
                $row = [
                    'label' => $def['label'],
                    'scope' => $def['scope'],
                    'input' => $def['input'],
                ];
                if ($def['scope'] === 'shared') {
                    $row['value'] = $this->display($field, $type, [], $shared);
                } else {
                    $row['cells'] = array_map(fn ($col) => $this->display($field, $type, $col, $shared), $columns);
                }
                $renderedRows[] = $row;
            }
            $sections[] = ['name' => $name, 'rows' => $renderedRows];
        }

        $totals      = array_map(fn ($col) => $this->columnTotal($col), $columns);
        $instalments = [];
        foreach (self::INSTALMENTS as $key => $meta) {
            $instalments[] = [
                'label'  => $meta['label'],
                'values' => array_map(fn ($t) => self::instalment($key, $t), $totals),
            ];
        }

        return [
            'type'        => $type,
            'title'       => $this->title(),
            'companies'   => $companies,
            'sections'    => $sections,
            'totals'      => $totals,
            'instalments' => $instalments,
            'roadtax_period' => self::ROADTAX_PERIOD_OPTIONS[$shared['roadtax_period'] ?? '1_year'] ?? '1 TAHUN',
        ];
    }
}
