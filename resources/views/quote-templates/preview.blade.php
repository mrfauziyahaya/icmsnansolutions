<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 print:hidden">
            <h2 class="text-2xl font-bold text-gray-900">Pratonton Sebut Harga</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('quote-templates.edit', $template) }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Edit</a>
                <button onclick="window.print()" class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Cetak</button>
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

        $money = fn($v) => is_null($v) || $v === '' || (float) $v == 0
            ? ['RM', '-']
            : ['RM', number_format((float) $v, 2)];

        $inst = \App\Models\QuoteTemplate::INSTALMENTS;
        $n    = count($columns);
    @endphp

    <div class="mx-auto max-w-4xl bg-white shadow print:shadow-none">
        <table class="w-full border-collapse text-[11px] sm:text-xs">
            {{-- logo --}}
            <tbody>
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
                        {{ $template->title }}
                    </td>
                </tr>

                {{-- reg + model --}}
                <tr class="font-bold uppercase">
                    <td colspan="2" class="border border-gray-300 bg-yellow-300 px-3 py-2">Vehicle Reg Num: {{ $template->vehicle_reg_number }}</td>
                    <td colspan="{{ max($n - 1, 1) }}" class="border border-gray-300 bg-yellow-300 px-3 py-2">Model: {{ $template->vehicle_model ?: '—' }}</td>
                </tr>

                {{-- company header --}}
                <tr>
                    <td class="border border-gray-300 bg-white px-3 py-2"></td>
                    @php $tints = ['bg-sky-200', 'bg-yellow-200', 'bg-green-200']; @endphp
                    @foreach($columns as $i => $c)
                        <td class="border border-gray-300 {{ $tints[$i % 3] }} px-3 py-2 text-center font-bold uppercase">{{ $c['company'] ?: '—' }}</td>
                    @endforeach
                </tr>

                @php
                    // helper to print a currency cell
                    $rm = function ($v) use ($money) {
                        [$pre, $num] = $money($v);
                        return '<div class="flex justify-between gap-2"><span>' . $pre . '</span><span>' . $num . '</span></div>';
                    };
                @endphp

                {{-- ── SEBUT HARGA ── --}}
                <x-quote-preview-head :span="$n + 1">Sebut Harga</x-quote-preview-head>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">SUM COVERED</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-right">{!! $rm($c['sum_covered']) !!}</td>@endforeach
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">VALUE</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['value'] }}</td>@endforeach
                </tr>

                {{-- ── INSURANCE BENEFITS ── --}}
                <x-quote-preview-head :span="$n + 1">Insurance Benefits</x-quote-preview-head>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">TOWING</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['towing'] }}</td>@endforeach
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">ACCIDENT / BREAKDOWN ASSIST</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['accident_assist'] }}</td>@endforeach
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">NO CLAIM DISCOUNT (NCD)</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['ncd'] }}</td>@endforeach
                </tr>

                {{-- ── ADD ON ── --}}
                <x-quote-preview-head :span="$n + 1">Add On</x-quote-preview-head>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">CERMIN</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-right">{!! $rm($c['cermin']) !!}</td>@endforeach
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">BENCANA ALAM</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['bencana_alam'] }}</td>@endforeach
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">ALL DRIVER</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['all_driver'] }}</td>@endforeach
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">PERSONAL ACCIDENT</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['personal_accident'] }}</td>@endforeach
                </tr>

                {{-- ── ROADTAX ── --}}
                <x-quote-preview-head :span="$n + 1">Roadtax</x-quote-preview-head>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">DIGITAL COPY (MYJPJ)</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['digital_copy'] }}</td>@endforeach
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">VEHICLE INSPECTION REQUIRED</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-center">{{ $c['vehicle_inspection'] }}</td>@endforeach
                </tr>

                {{-- ── TOTALS ── --}}
                <tr><td colspan="{{ $n + 1 }}" class="p-1"></td></tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">INSURANCE - TAKAFUL</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-right">{!! $rm($c['insurance_takaful']) !!}</td>@endforeach
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-1.5 font-semibold">ROADTAX (1 TAHUN)</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-right">{!! $rm($c['roadtax']) !!}</td>@endforeach
                </tr>
                <tr class="font-bold">
                    <td class="border border-gray-300 px-3 py-2">JUMLAH KESELURUHAN</td>
                    @foreach($columns as $c)<td class="border border-gray-300 px-3 py-2 text-right">{!! $rm($c['total']) !!}</td>@endforeach
                </tr>

                {{-- ── INSTALMENTS ── --}}
                <x-quote-preview-head :span="$n + 1">Jumlah Keseluruhan Bayaran Ansuran</x-quote-preview-head>
                @foreach($inst as $key => $meta)
                    <tr>
                        <td class="border border-gray-300 px-3 py-1.5 font-semibold">{{ $meta['label'] }}</td>
                        @foreach($columns as $c)<td class="border border-gray-300 px-3 py-1.5 text-right">{!! $rm($c['instalments'][$key]) !!}</td>@endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
