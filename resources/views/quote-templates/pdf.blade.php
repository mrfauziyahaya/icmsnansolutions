@php
    /**
     * Print/PDF rendering of a quote. Deliberately plain CSS — dompdf has no
     * Tailwind, so the preview's utility classes are restated here as literal
     * colours and widths.
     */
    $companies = $preview['companies'];
    $n         = count($companies);
    $cols      = $n + 1;

    // Same halving rule as the on-screen preview: spans must total the grid or
    // the row overflows, and the 50% widths keep both cells even on an odd grid.
    $regSpan   = (int) ceil($cols / 2);
    $modelSpan = max($cols - $regSpan, 1);

    // Fit to one A4 landscape page: the taller the quote, the tighter the type.
    $rowCount = 6 + 2 + count($preview['instalments']);
    foreach ($preview['sections'] as $s) {
        $rowCount += 1 + count($s['rows']);
    }
    [$fontSize, $rowPad, $logoH] = match (true) {
        $rowCount > 30 => ['6.5pt', '1.5px', '26px'],
        $rowCount > 26 => ['7.5pt', '2px',   '30px'],
        $rowCount > 22 => ['8.5pt', '3px',   '36px'],
        default        => ['9.5pt', '4px',   '42px'],
    };

    $tints = ['#bae6fd', '#fef08a', '#bbf7d0'];
    $rm    = fn ($v) => is_null($v) || $v === '' || (float) $v == 0 ? 'RM -' : 'RM ' . number_format((float) $v, 2);

    $cellW = $n > 0 ? round(50 / $n, 4) . '%' : '50%';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: {{ $fontSize }}; color: #1a1a1a; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { border: 1px solid #d1d5db; padding: {{ $rowPad }} 6px; vertical-align: middle; word-wrap: break-word; }

        .center { text-align: center; }
        .bold   { font-weight: bold; }
        .upper  { text-transform: uppercase; }

        .brand      { padding: 8px; }
        .brand img  { max-height: 52px; width: auto; }
        .brand span { font-size: 15pt; font-weight: bold; color: #1f2937; }

        .title    { background: #facc15; font-size: 11pt; letter-spacing: 0.4px; padding: 5px; }
        .vehicle  { background: #fde047; }
        .section  { background: #f97316; color: #fff; letter-spacing: 0.4px; padding: 3px; }
        .co-logo  { background: #fff; height: {{ $logoH }}; }
        .co-logo img { max-height: {{ $logoH }}; width: auto; }
        .co-name  { font-size: 8pt; }
        .total    { font-weight: bold; }
    </style>
</head>
<body>
<table>
    {{-- company logo --}}
    <tr>
        <td colspan="{{ $cols }}" class="center brand">
            @if($brandLogo)
                <img src="{{ $brandLogo }}" alt="">
            @else
                <span>{{ $setting->company_name ?: 'NAN SOLUTIONS' }}</span>
            @endif
        </td>
    </tr>

    {{-- quote type --}}
    <tr>
        <td colspan="{{ $cols }}" class="center bold upper title">{{ $preview['title'] }}</td>
    </tr>

    {{-- vehicle --}}
    <tr class="bold upper">
        <td colspan="{{ $regSpan }}" style="width:50%" class="vehicle">Vehicle Reg Num: {{ $template->vehicle_reg_number }}</td>
        <td colspan="{{ $modelSpan }}" style="width:50%" class="vehicle">Model: {{ $template->vehicle_model ?: '—' }}</td>
    </tr>

    {{-- insurer logos --}}
    <tr>
        <td style="width:50%"></td>
        @foreach($companies as $c)
            <td style="width:{{ $cellW }}" class="center co-logo">
                @if($c['pdf_logo'])<img src="{{ $c['pdf_logo'] }}" alt="">@endif
            </td>
        @endforeach
    </tr>

    {{-- insurer names --}}
    <tr>
        <td></td>
        @foreach($companies as $i => $c)
            <td class="center bold upper co-name" style="background: {{ $tints[$i % 3] }}">{{ $c['company'] ?: '—' }}</td>
        @endforeach
    </tr>

    {{-- sections --}}
    @foreach($preview['sections'] as $section)
        <tr><td colspan="{{ $cols }}" class="center bold upper section">{{ $section['name'] }}</td></tr>
        @foreach($section['rows'] as $row)
            <tr>
                <td class="bold upper">{{ $row['label'] }}</td>
                @if($row['scope'] === 'shared')
                    <td colspan="{{ $n }}" class="center">{{ $row['input'] === 'number' ? $rm($row['value']) : $row['value'] }}</td>
                @else
                    @foreach($row['cells'] as $cell)
                        <td class="center">
                            @if(is_array($cell))
                                @foreach($cell as $line)<div>{{ $line }}</div>@endforeach
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach
                @endif
            </tr>
        @endforeach
    @endforeach

    {{-- grand total --}}
    <tr class="total">
        <td class="upper">Jumlah Keseluruhan</td>
        @foreach($preview['totals'] as $t)<td class="center">{{ $rm($t) }}</td>@endforeach
    </tr>

    {{-- instalments --}}
    <tr><td colspan="{{ $cols }}" class="center bold upper section">Jumlah Keseluruhan Bayaran Ansuran</td></tr>
    @foreach($preview['instalments'] as $row)
        <tr>
            <td class="bold">{{ $row['label'] }}</td>
            @foreach($row['values'] as $v)<td class="center">{{ $rm($v) }}</td>@endforeach
        </tr>
    @endforeach
</table>
</body>
</html>
