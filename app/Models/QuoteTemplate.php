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

    /** Fixed insurance companies — the three comparison columns. */
    public const COMPANIES = ['Zurich Takaful', 'Etiqa Takaful', 'Takaful Ikhlas'];

    // ── Option lists (form dropdowns) + display labels (preview) ──────────────

    public const VALUE_OPTIONS = [
        'market_value' => 'MARKET VALUE',
        'agreed_value' => 'AGREED VALUE',
    ];

    public const TOWING_OPTIONS = [
        '150km'     => '150 KM',
        '200km'     => '200 KM',
        '300km'     => '300 KM',
        'unlimited' => 'UNLIMITED',
    ];

    public const YESNO_OPTIONS = [
        'yes' => 'YES',
        'no'  => 'NO',
    ];

    public const PA_OPTIONS = [
        'no'        => 'NO',
        'yes'       => 'YES',
        'cash_care' => 'CASH CARE P.A.',
        'z_drive'   => 'Z-DRIVE',
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

    /** Fields entered once and shown across all three columns. */
    public const SHARED_FIELDS = ['sum_covered', 'cermin', 'bencana_alam', 'digital_copy', 'roadtax'];

    /**
     * A blank template ready for the create form.
     */
    public static function blankData(): array
    {
        return [
            'shared' => [
                'sum_covered'  => null,
                'cermin'       => null,
                'bencana_alam' => 'no',
                'digital_copy' => 'yes',
                'roadtax'      => null,
            ],
            'columns' => array_map(fn ($company) => [
                'company'            => $company,
                'value'              => 'market_value',
                'towing'             => '300km',
                'accident_assist'    => 'yes',
                'ncd'                => 0,
                'all_driver'         => 'yes',
                'personal_accident'  => 'no',
                'vehicle_inspection' => 'no',
                'insurance_takaful'  => null,
            ], self::COMPANIES),
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
                'sum_covered'        => $shared['sum_covered'] ?? null,
                'value'              => self::VALUE_OPTIONS[$col['value'] ?? ''] ?? '-',
                'towing'             => self::TOWING_OPTIONS[$col['towing'] ?? ''] ?? '-',
                'accident_assist'    => self::YESNO_OPTIONS[$col['accident_assist'] ?? ''] ?? '-',
                'ncd'                => number_format((float) ($col['ncd'] ?? 0), 2) . '%',
                'cermin'             => $shared['cermin'] ?? null,
                'bencana_alam'       => self::YESNO_OPTIONS[$shared['bencana_alam'] ?? ''] ?? '-',
                'all_driver'         => self::YESNO_OPTIONS[$col['all_driver'] ?? ''] ?? '-',
                'personal_accident'  => self::PA_OPTIONS[$col['personal_accident'] ?? ''] ?? '-',
                'digital_copy'       => self::YESNO_OPTIONS[$shared['digital_copy'] ?? ''] ?? '-',
                'vehicle_inspection' => self::YESNO_OPTIONS[$col['vehicle_inspection'] ?? ''] ?? '-',
                'insurance_takaful'  => (float) ($col['insurance_takaful'] ?? 0),
                'roadtax'            => (float) ($shared['roadtax'] ?? 0),
                'total'              => $total,
                'instalments'        => $instalments,
            ];
        }

        return $out;
    }
}
