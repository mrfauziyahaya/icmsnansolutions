<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $template->exists ? 'Edit Sebut Harga' : 'Sebut Harga Baru' }}
        </h2>
    </x-slot>

    @php
        $val   = \App\Models\QuoteTemplate::VALUE_OPTIONS;
        $tow   = \App\Models\QuoteTemplate::TOWING_OPTIONS;
        $yn    = \App\Models\QuoteTemplate::YESNO_OPTIONS;
        $pa    = \App\Models\QuoteTemplate::PA_OPTIONS;
        $inst  = \App\Models\QuoteTemplate::INSTALMENTS;
        $d     = $template->data;
    @endphp

    @if($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            Sila semak semula borang — ada medan yang tidak lengkap.
        </div>
    @endif

    <form method="POST"
          action="{{ $template->exists ? route('quote-templates.update', $template) : route('quote-templates.store') }}"
          x-data="quoteForm()">
        @csrf
        @if($template->exists) @method('PUT') @endif

        {{-- ── Header info ─────────────────────────────────────────────── --}}
        <div class="bg-white shadow rounded-lg p-5 sm:p-6 space-y-5">
            <p class="text-center text-sm font-bold uppercase tracking-wide text-gray-500">{{ \App\Models\QuoteTemplate::TITLE }}</p>
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Pendaftaran Kenderaan <span class="text-red-500">*</span></label>
                    <input type="text" name="vehicle_reg_number" x-model="f.reg" required placeholder="WVW7141"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                    <input type="text" name="vehicle_model" x-model="f.model" placeholder="Perodua Bezza Premium X"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                </div>
            </div>
        </div>

        {{-- ── Comparison grid ─────────────────────────────────────────── --}}
        <div class="bg-white shadow rounded-lg mt-6 overflow-x-auto">
            <div class="min-w-[720px] p-4 sm:p-6">

                {{-- fixed company headers (like the image) --}}
                @php $tints = ['bg-sky-200', 'bg-yellow-200', 'bg-green-200']; @endphp
                <div class="grid grid-cols-[160px_repeat(3,1fr)] gap-2 items-stretch sticky top-0 z-10 bg-white py-1">
                    <div class="flex items-center text-xs font-semibold uppercase tracking-wide text-gray-500">Sebut Harga</div>
                    @foreach(\App\Models\QuoteTemplate::COMPANIES as $i => $company)
                        <div class="{{ $tints[$i % 3] }} rounded-md px-2 py-2.5 text-center text-sm font-bold uppercase text-gray-800">
                            {{ $company }}
                        </div>
                    @endforeach
                </div>

                @php
                    // [label, section?] rows are rendered by the partials below.
                @endphp

                {{-- SEBUT HARGA --}}
                <x-quote-section>Sebut Harga</x-quote-section>
                <x-quote-row-shared label="Sum Covered (RM)">
                    <input type="number" step="0.01" min="0" name="shared[sum_covered]" x-model.number="f.shared.sum_covered"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm text-right">
                </x-quote-row-shared>
                <x-quote-row label="Value">
                    @foreach($d['columns'] as $i => $col)
                        <select name="columns[{{ $i }}][value]" x-model="f.columns[{{ $i }}].value" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($val as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    @endforeach
                </x-quote-row>

                {{-- INSURANCE BENEFITS --}}
                <x-quote-section>Insurance Benefits</x-quote-section>
                <x-quote-row label="Towing">
                    @foreach($d['columns'] as $i => $col)
                        <select name="columns[{{ $i }}][towing]" x-model="f.columns[{{ $i }}].towing" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($tow as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    @endforeach
                </x-quote-row>
                <x-quote-row label="Accident / Breakdown Assist">
                    @foreach($d['columns'] as $i => $col)
                        <select name="columns[{{ $i }}][accident_assist]" x-model="f.columns[{{ $i }}].accident_assist" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    @endforeach
                </x-quote-row>
                <x-quote-row label="No Claim Discount (NCD) %">
                    @foreach($d['columns'] as $i => $col)
                        <input type="number" step="0.01" min="0" max="100" name="columns[{{ $i }}][ncd]" x-model.number="f.columns[{{ $i }}].ncd"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                    @endforeach
                </x-quote-row>

                {{-- ADD ON --}}
                <x-quote-section>Add On</x-quote-section>
                <x-quote-row-shared label="Cermin (RM)">
                    <input type="number" step="0.01" min="0" name="shared[cermin]" x-model.number="f.shared.cermin"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                </x-quote-row-shared>
                <x-quote-row-shared label="Bencana Alam">
                    <select name="shared[bencana_alam]" x-model="f.shared.bencana_alam" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                    </select>
                </x-quote-row-shared>
                <x-quote-row label="All Driver">
                    @foreach($d['columns'] as $i => $col)
                        <select name="columns[{{ $i }}][all_driver]" x-model="f.columns[{{ $i }}].all_driver" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    @endforeach
                </x-quote-row>
                <x-quote-row label="Personal Accident">
                    @foreach($d['columns'] as $i => $col)
                        <select name="columns[{{ $i }}][personal_accident]" x-model="f.columns[{{ $i }}].personal_accident" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($pa as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    @endforeach
                </x-quote-row>

                {{-- ROADTAX --}}
                <x-quote-section>Roadtax</x-quote-section>
                <x-quote-row-shared label="Digital Copy (MyJPJ)">
                    <select name="shared[digital_copy]" x-model="f.shared.digital_copy" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                    </select>
                </x-quote-row-shared>
                <x-quote-row label="Vehicle Inspection Required">
                    @foreach($d['columns'] as $i => $col)
                        <select name="columns[{{ $i }}][vehicle_inspection]" x-model="f.columns[{{ $i }}].vehicle_inspection" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    @endforeach
                </x-quote-row>

                {{-- TOTALS --}}
                <x-quote-section>Jumlah</x-quote-section>
                <x-quote-row label="Insurance / Takaful (RM)">
                    @foreach($d['columns'] as $i => $col)
                        <input type="number" step="0.01" min="0" name="columns[{{ $i }}][insurance_takaful]" x-model.number="f.columns[{{ $i }}].insurance_takaful"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                    @endforeach
                </x-quote-row>
                <x-quote-row-shared label="Roadtax 1 Tahun (RM)">
                    <input type="number" step="0.01" min="0" name="shared[roadtax]" x-model.number="f.shared.roadtax"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                </x-quote-row-shared>

                {{-- live computed totals --}}
                <div class="grid grid-cols-[160px_repeat(3,1fr)] gap-2 mt-3 py-3 border-t-2 border-gray-200">
                    <div class="text-sm font-bold text-gray-900">Jumlah Keseluruhan</div>
                    <template x-for="i in [0,1,2]" :key="i">
                        <div class="text-sm font-bold text-orange-700 text-right" x-text="'RM ' + total(i).toFixed(2)"></div>
                    </template>
                </div>

                <div class="mt-2 rounded-lg bg-gray-50 p-3 space-y-1.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Bayaran Ansuran</p>
                    @foreach($inst as $key => $meta)
                        <div class="grid grid-cols-[160px_repeat(3,1fr)] gap-2 text-sm">
                            <div class="text-gray-600">{{ $meta['label'] }}</div>
                            <template x-for="i in [0,1,2]" :key="i">
                                <div class="text-right text-gray-800" x-text="'RM ' + instalment('{{ $key }}', total(i)).toFixed(2)"></div>
                            </template>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('quote-templates.index') }}" class="rounded-md bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200">Batal</a>
            <button type="submit" class="rounded-md bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-700">
                Simpan &amp; Pratonton
            </button>
        </div>
    </form>

    <script>
        function quoteForm() {
            return {
                f: @js([
                    'title'   => $template->title,
                    'reg'     => $template->vehicle_reg_number,
                    'model'   => $template->vehicle_model,
                    'shared'  => $d['shared'],
                    'columns' => $d['columns'],
                ]),
                total(i) {
                    const c = this.f.columns[i];
                    const digital = this.f.shared.digital_copy === 'yes' ? 5 : 0;
                    return (Number(c.insurance_takaful) || 0) + (Number(this.f.shared.roadtax) || 0) + digital;
                },
                instalment(provider, total) {
                    switch (provider) {
                        case 'atome':         return total * 1.08;
                        case 'ahapay':        return Math.round(total * 1.035);
                        case 'spaylater':     return total * 1.02;
                        default:              return total;   // directlending
                    }
                },
            }
        }
    </script>
</x-app-layout>
