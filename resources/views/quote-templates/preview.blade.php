<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 print:hidden">
            <h2 class="text-2xl font-bold text-gray-900">Pratonton Sebut Harga</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('quote-templates.edit', $template) }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Edit</a>
                <button onclick="window.printQuote()" class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Cetak</button>
                <a href="{{ route('quote-templates.index') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Kembali</a>
            </div>
        </div>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 print:hidden">{{ session('status') }}</div>
    @endif

    @php
        $logo = ($setting = \App\Models\Setting::instance())->logo_path && is_file(storage_path('app/public/' . $setting->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($setting->logo_path)
            : (is_file(public_path('images/logo.png')) ? asset('images/logo.png') : null);

        $rm = fn($v) => is_null($v) || $v === '' || (float) $v == 0 ? 'RM -' : 'RM ' . number_format((float) $v, 2);

        $companies = $preview['companies'];
        $n         = count($companies);
        $tints     = ['bg-sky-200', 'bg-yellow-200', 'bg-green-200'];
    @endphp

    <div id="quote-print" class="mx-auto max-w-4xl bg-white shadow print:shadow-none">
        <table class="w-full border-collapse text-[11px] sm:text-xs">
            <tbody>
                {{-- logo --}}
                <tr>
                    <td colspan="{{ $n + 1 }}" class="border border-gray-300 p-3 text-center">
                        @if($logo)
                            <img src="{{ $logo }}" alt="{{ $setting->company_name }}" class="mx-auto h-16 w-auto object-contain">
                        @else
                            <span class="font-display text-xl font-bold text-gray-800">NAN SOLUTIONS</span>
                        @endif
                    </td>
                </tr>

                {{-- title --}}
                <tr>
                    <td colspan="{{ $n + 1 }}" class="border border-gray-300 bg-yellow-400 px-3 py-2 text-center text-sm font-bold uppercase tracking-wide">
                        {{ $preview['title'] }}
                    </td>
                </tr>

                {{-- reg + model --}}
                <tr class="font-bold uppercase">
                    <td colspan="2" class="border border-gray-300 bg-yellow-300 px-3 py-2">Vehicle Reg Num: {{ $template->vehicle_reg_number }}</td>
                    <td colspan="{{ max($n - 1, 1) }}" class="border border-gray-300 bg-yellow-300 px-3 py-2">Model: {{ $template->vehicle_model ?: '—' }}</td>
                </tr>

                {{-- company logos (on white) --}}
                <tr>
                    <td class="border border-gray-300 bg-white px-3 py-2"></td>
                    @foreach($companies as $c)
                        <td class="border border-gray-300 bg-white px-3 py-2 text-center align-middle">
                            @if($c['logo'])
                                <img src="{{ asset($c['logo']) }}" alt="{{ $c['company'] }}" class="mx-auto h-20 w-auto object-contain">
                            @endif
                        </td>
                    @endforeach
                </tr>

                {{-- company names --}}
                <tr>
                    <td class="border border-gray-300 bg-white px-3 py-2"></td>
                    @foreach($companies as $i => $c)
                        <td class="border border-gray-300 {{ $tints[$i % 3] }} px-3 py-2 text-center font-bold uppercase">{{ $c['company'] ?: '—' }}</td>
                    @endforeach
                </tr>

                {{-- sections --}}
                @foreach($preview['sections'] as $section)
                    <x-quote-preview-head :span="$n + 1">{{ $section['name'] }}</x-quote-preview-head>
                    @foreach($section['rows'] as $row)
                        <tr>
                            <td class="border border-gray-300 px-3 py-1.5 font-semibold uppercase">{{ $row['label'] }}</td>
                            @if($row['scope'] === 'shared')
                                <td colspan="{{ $n }}" class="border border-gray-300 px-3 py-1.5 text-center">
                                    {{ $row['input'] === 'number' ? $rm($row['value']) : $row['value'] }}
                                </td>
                            @else
                                @foreach($row['cells'] as $cell)
                                    <td class="border border-gray-300 px-3 py-1.5 {{ $row['input'] === 'number' ? 'text-right' : 'text-center' }}">
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
                <tr class="font-bold">
                    <td class="border border-gray-300 px-3 py-2 uppercase">Jumlah Keseluruhan</td>
                    @foreach($preview['totals'] as $t)<td class="border border-gray-300 px-3 py-2 text-right">{{ $rm($t) }}</td>@endforeach
                </tr>

                {{-- instalments --}}
                <x-quote-preview-head :span="$n + 1">Jumlah Keseluruhan Bayaran Ansuran</x-quote-preview-head>
                @foreach($preview['instalments'] as $row)
                    <tr>
                        <td class="border border-gray-300 px-3 py-1.5 font-semibold">{{ $row['label'] }}</td>
                        @foreach($row['values'] as $v)<td class="border border-gray-300 px-3 py-1.5 text-right">{{ $rm($v) }}</td>@endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Print to a single A4 landscape page. --}}
    <style>
        @media print {
            @page { size: A4 landscape; margin: 8mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            #quote-print { box-shadow: none !important; }
        }
    </style>
    <script>
        (function () {
            const el = document.getElementById('quote-print');
            const PX_PER_MM = 96 / 25.4;
            const pageW = (297 - 16) * PX_PER_MM;
            const pageH = (210 - 16) * PX_PER_MM;

            function fit() {
                if (!el) return;
                el.style.transform = '';
                const r = el.getBoundingClientRect();
                const scale = Math.min(pageW / r.width, pageH / r.height, 1);
                el.style.transformOrigin = 'top left';
                el.style.transform = 'scale(' + scale.toFixed(3) + ')';
            }
            function reset() { if (el) el.style.transform = ''; }

            window.addEventListener('beforeprint', fit);
            window.addEventListener('afterprint', reset);
            window.printQuote = function () { fit(); window.print(); reset(); };
        })();
    </script>
</x-app-layout>
