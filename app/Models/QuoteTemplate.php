<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteTemplate extends Model
{
    protected $fillable = [
        'title',
        'vehicle_reg_number',
        'vehicle_model',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /** Fixed title (no longer entered per quote). */
    public const TITLE = 'First Party Comprehensive';

    /**
     * Every insurance company that can be compared, mapped to its logo under
     * public/ (null = no logo yet, show the name as text). The admin picks which
     * of these appear as columns via the multi-select on the form.
     */
    public const ALL_COMPANIES = [
        'ZURICH TAKAFUL'   => 'images/zurich-takaful.png',
        'ETIQA TAKAFUL'    => 'images/Logo-Insuran-3.webp',
        'TAKAFUL IKHLAS'   => 'images/Logo-Insuran-5.webp',
        'TAKAFUL MALAYSIA' => 'images/takaful-malaysia-logo.png',
        'KURNIA INSURANS'  => 'images/kurnia-insurance-logo.png',
    ];

    /** Companies pre-ticked on a new quote. */
    public const DEFAULT_SELECTED = ['ZURICH TAKAFUL', 'ETIQA TAKAFUL', 'TAKAFUL IKHLAS'];

    /** Asset path for a company's logo, or null when the file isn't present. */
    public static function logoFor(?string $company): ?string
    {
        $path = self::ALL_COMPANIES[$company] ?? null;

        return $path && is_file(public_path($path)) ? $path : null;
    }

    /** Default column values for a company — first towing/PA option it offers. */
    public static function defaultColumn(string $company): array
    {
        return [
            'company'            => $company,
            'sum_covered'        => null,
            'value'              => 'market_value',
            'towing'             => self::COMPANY_TOWING[$company][0] ?? 'unlimited',
            'accident_assist'    => 'yes',
            'ncd'                => '0',
            'all_driver'         => 'yes',
            'personal_accident'  => self::COMPANY_PA[$company][0] ?? 'no',
            'vehicle_inspection' => 'no',
            'insurance_takaful'  => null,
        ];
    }

    // ── Option lists (form dropdowns) + display labels (preview) ──────────────

    public const VALUE_OPTIONS = [
        'market_value' => 'MARKET VALUE',
        'agreed_value' => 'AGREED VALUE',
    ];

    /** Every towing label. Which apply per company is COMPANY_TOWING below. */
    public const TOWING_OPTIONS = [
        '100km'     => '100 KM',
        '150km'     => '150 KM',
        '200km'     => '200 KM',
        '300km'     => '300 KM',
        'unlimited' => 'UNLIMITED',
        'no'        => 'NO',
    ];

    /** Towing options offered per company (keys of TOWING_OPTIONS, in order). */
    public const COMPANY_TOWING = [
        'ZURICH TAKAFUL'   => ['150km', '300km', 'unlimited'],
        'ETIQA TAKAFUL'    => ['200km', 'unlimited', 'no'],
        'TAKAFUL IKHLAS'   => ['150km', 'unlimited', 'no'],
        'TAKAFUL MALAYSIA' => ['300km', 'unlimited', 'no'],
        'KURNIA INSURANS'  => ['100km', 'unlimited'],
    ];

    public const YESNO_OPTIONS = [
        'yes' => 'YES',
        'no'  => 'NO',
    ];

    /** No Claim Discount — the same fixed set for every company. */
    public const NCD_OPTIONS = [
        '0'     => '0%',
        '25'    => '25%',
        '35'    => '35%',
        '38.33' => '38.33%',
        '45'    => '45%',
        '55'    => '55%',
    ];

    /** Every personal-accident label. Which apply per company: COMPANY_PA. */
    public const PA_OPTIONS = [
        'no'              => 'NO',
        'yes'             => 'YES',
        'cash_care'       => 'CASH CARE P.A.',
        'z_drive'         => 'Z-DRIVE',
        'motorist_plan_3' => 'MOTORIST PLAN 3',
        'motorist_plan_4' => 'MOTORIST PLAN 4',
        'auto365'         => 'AUTO365',
    ];

    /** Personal-accident options offered per company (keys of PA_OPTIONS). */
    public const COMPANY_PA = [
        'ZURICH TAKAFUL'   => ['yes', 'no', 'cash_care', 'z_drive'],
        'ETIQA TAKAFUL'    => ['yes', 'no', 'cash_care'],
        'TAKAFUL IKHLAS'   => ['yes', 'no', 'cash_care'],
        'TAKAFUL MALAYSIA' => ['yes', 'no', 'cash_care', 'motorist_plan_3', 'motorist_plan_4'],
        'KURNIA INSURANS'  => ['auto365'],
    ];

    /**
     * Installment providers and how each marks up the grand total.
     * @var array<string, array{label: string, apply: callable}>
     */
    public const INSTALMENTS = [
        'atome'         => ['label' => 'ATOME / GRAB PAYLATER'],
        'ahapay'        => ['label' => 'AHAPAY'],
        'spaylater'     => ['label' => 'SPAYLATER'],
        'directlending' => ['label' => 'DIRECT LENDING'],
    ];

    /** Roadtax coverage period (label reflects the choice on the preview). */
    public const ROADTAX_PERIOD_OPTIONS = [
        '1_year'   => '1 TAHUN',
        '6_months' => '6 BULAN',
    ];

    /** Fields entered once and shown across all columns. */
    public const SHARED_FIELDS = ['cermin', 'bencana_alam', 'digital_copy', 'roadtax', 'roadtax_period'];

    /**
     * A blank template ready for the create form.
     */
    public static function blankData(): array
    {
        return [
            'shared' => [
                'cermin'         => null,
                'bencana_alam'   => 'no',
                'digital_copy'   => 'yes',
                'roadtax'        => 70,
                'roadtax_period' => '1_year',
            ],
            'columns' => array_map(fn ($company) => self::defaultColumn($company), self::DEFAULT_SELECTED),
        ];
    }

    // ── Calculations ──────────────────────────────────────────────────────────

    /**
     * Grand total for one column: insurance + roadtax + RM5 if a digital MyJPJ
     * copy is taken.
     */
    public function columnTotal(array $column): float
    {
        $shared   = $this->data['shared'] ?? [];
        $roadtax  = (float) ($shared['roadtax'] ?? 0);
        $digital  = ($shared['digital_copy'] ?? 'no') === 'yes' ? 5 : 0;

        return round((float) ($column['insurance_takaful'] ?? 0) + $roadtax + $digital, 2);
    }

    /**
     * Instalment amount for a given provider on a grand total.
     */
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

    /**
     * Everything the preview needs, computed from the stored inputs.
     *
     * @return array<int, array<string, mixed>>
     */
    public function computedColumns(): array
    {
        $shared  = $this->data['shared'] ?? [];
        $columns = $this->data['columns'] ?? [];
        $out     = [];

        foreach ($columns as $col) {
            $total = $this->columnTotal($col);

            $instalments = [];
            foreach (self::INSTALMENTS as $key => $meta) {
                $instalments[$key] = self::instalment($key, $total);
            }

            $out[] = [
                'company'            => $col['company'] ?? '',
                'sum_covered'        => $col['sum_covered'] ?? null,
                'value'              => self::VALUE_OPTIONS[$col['value'] ?? ''] ?? '-',
                'towing'             => self::TOWING_OPTIONS[$col['towing'] ?? ''] ?? '-',
                'accident_assist'    => self::YESNO_OPTIONS[$col['accident_assist'] ?? ''] ?? '-',
                'ncd'                => self::NCD_OPTIONS[(string) ($col['ncd'] ?? '0')] ?? (number_format((float) ($col['ncd'] ?? 0), 2) . '%'),
                'cermin'             => $shared['cermin'] ?? null,
                'bencana_alam'       => self::YESNO_OPTIONS[$shared['bencana_alam'] ?? ''] ?? '-',
                'all_driver'         => self::YESNO_OPTIONS[$col['all_driver'] ?? ''] ?? '-',
                'personal_accident'  => self::PA_OPTIONS[$col['personal_accident'] ?? ''] ?? '-',
                'digital_copy'       => self::YESNO_OPTIONS[$shared['digital_copy'] ?? ''] ?? '-',
                'vehicle_inspection' => self::YESNO_OPTIONS[$col['vehicle_inspection'] ?? ''] ?? '-',
                'insurance_takaful'  => (float) ($col['insurance_takaful'] ?? 0),
                'roadtax'            => (float) ($shared['roadtax'] ?? 0),
                'roadtax_period'     => self::ROADTAX_PERIOD_OPTIONS[$shared['roadtax_period'] ?? '1_year'] ?? '1 TAHUN',
                'total'              => $total,
                'instalments'        => $instalments,
            ];
        }

        return $out;
    }
}
