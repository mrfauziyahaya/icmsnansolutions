<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $template->exists ? 'Edit Sebut Harga' : 'Sebut Harga Baru' }}
        </h2>
    </x-slot>

    @php
        $val   = \App\Models\QuoteTemplate::VALUE_OPTIONS;
        $yn    = \App\Models\QuoteTemplate::YESNO_OPTIONS;
        $ncd   = \App\Models\QuoteTemplate::NCD_OPTIONS;
        $inst  = \App\Models\QuoteTemplate::INSTALMENTS;
        $d     = $template->data;

        // Build every selectable company, carrying its saved values when this
        // quote already includes it, otherwise sensible defaults. A company is
        // pre-ticked only if it's part of the saved (or blank) column set.
        $saved     = collect($d['columns'])->keyBy('company');
        $companies = [];
        $mapOptions = fn (array $keys, array $labels) => array_map(fn ($k) => ['v' => $k, 'l' => $labels[$k]], $keys);
        foreach (\App\Models\QuoteTemplate::ALL_COMPANIES as $name => $logoPath) {
            $col = $saved->get($name);
            $companies[] = [
                'name'     => $name,
                'logo'     => \App\Models\QuoteTemplate::logoFor($name) ? asset($logoPath) : null,
                'selected' => $col !== null,
                'col'      => \Illuminate\Support\Arr::except($col ?? \App\Models\QuoteTemplate::defaultColumn($name), ['company']),
                // Towing and Personal Accident dropdowns differ per company.
                'towingOptions' => $mapOptions(\App\Models\QuoteTemplate::COMPANY_TOWING[$name] ?? [], \App\Models\QuoteTemplate::TOWING_OPTIONS),
                'paOptions'     => $mapOptions(\App\Models\QuoteTemplate::COMPANY_PA[$name] ?? [], \App\Models\QuoteTemplate::PA_OPTIONS),
            ];
        }
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

        {{-- ── Company multi-select ────────────────────────────────────── --}}
        <div class="bg-white shadow rounded-lg mt-6 p-5 sm:p-6">
            <p class="text-sm font-medium text-gray-700 mb-3">
                Pilih Syarikat Insurans
                <span class="text-gray-400 font-normal">(pilih satu atau lebih — setiap satu jadi satu kolum)</span>
            </p>
            <div class="flex flex-wrap gap-2">
                <template x-for="c in f.companies" :key="c.name">
                    <label class="inline-flex items-center gap-2 rounded-md border px-3 py-2 cursor-pointer select-none transition"
                           :class="c.selected ? 'border-orange-500 bg-orange-50 text-orange-800' : 'border-gray-300 text-gray-600 hover:border-gray-400'">
                        <input type="checkbox" x-model="c.selected" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        <span class="text-sm font-medium" x-text="c.name"></span>
                    </label>
                </template>
            </div>
            <p x-show="selected().length === 0" x-cloak class="mt-2 text-xs text-red-600">Sila pilih sekurang-kurangnya satu syarikat.</p>
        </div>

        {{-- Hidden company name per selected column, in the same order. --}}
        <template x-for="(c, i) in selected()" :key="'company-' + c.name">
            <input type="hidden" :name="`columns[${i}][company]`" :value="c.name">
        </template>

        {{-- ── Comparison grid ─────────────────────────────────────────── --}}
        <div class="bg-white shadow rounded-lg mt-6 overflow-x-auto">
            <div class="p-4 sm:p-6" x-show="selected().length > 0" x-cloak>

                {{-- company headers --}}
                <div class="grid gap-2 items-stretch sticky top-0 z-10 bg-white py-1" :style="gridStyle">
                    <div class="flex items-center text-xs font-semibold uppercase tracking-wide text-gray-500">Sebut Harga</div>
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <div class="rounded-md px-2 py-2 text-center text-sm font-bold uppercase text-gray-800" :class="tint(i)">
                            <template x-if="c.logo"><img :src="c.logo" :alt="c.name" class="mx-auto mb-1 h-7 w-auto object-contain"></template>
                            <span x-text="c.name"></span>
                        </div>
                    </template>
                </div>

                {{-- SEBUT HARGA --}}
                <x-quote-section>Sebut Harga</x-quote-section>
                <x-quote-row-shared label="Sum Covered (RM)">
                    <input type="number" step="0.01" min="0" name="shared[sum_covered]" x-model.number="f.shared.sum_covered"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm text-right">
                </x-quote-row-shared>
                <x-quote-row label="Value">
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <select :name="`columns[${i}][value]`" x-model="c.col.value" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($val as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    </template>
                </x-quote-row>

                {{-- INSURANCE BENEFITS --}}
                <x-quote-section>Insurance Benefits</x-quote-section>
                <x-quote-row label="Towing">
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <select :name="`columns[${i}][towing]`" x-model="c.col.towing" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <template x-for="opt in c.towingOptions" :key="opt.v">
                                <option :value="opt.v" x-text="opt.l"></option>
                            </template>
                        </select>
                    </template>
                </x-quote-row>
                <x-quote-row label="Accident / Breakdown Assist">
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <select :name="`columns[${i}][accident_assist]`" x-model="c.col.accident_assist" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    </template>
                </x-quote-row>
                <x-quote-row label="No Claim Discount (NCD) %">
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <select :name="`columns[${i}][ncd]`" x-model="c.col.ncd" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($ncd as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    </template>
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
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <select :name="`columns[${i}][all_driver]`" x-model="c.col.all_driver" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    </template>
                </x-quote-row>
                <x-quote-row label="Personal Accident">
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <select :name="`columns[${i}][personal_accident]`" x-model="c.col.personal_accident" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <template x-for="opt in c.paOptions" :key="opt.v">
                                <option :value="opt.v" x-text="opt.l"></option>
                            </template>
                        </select>
                    </template>
                </x-quote-row>

                {{-- ROADTAX --}}
                <x-quote-section>Roadtax</x-quote-section>
                <x-quote-row-shared label="Digital Copy (MyJPJ)">
                    <select name="shared[digital_copy]" x-model="f.shared.digital_copy" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                    </select>
                </x-quote-row-shared>
                <x-quote-row label="Vehicle Inspection Required">
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <select :name="`columns[${i}][vehicle_inspection]`" x-model="c.col.vehicle_inspection" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach($yn as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                        </select>
                    </template>
                </x-quote-row>

                {{-- TOTALS --}}
                <x-quote-section>Jumlah</x-quote-section>
                <x-quote-row label="Insurance / Takaful (RM)">
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <input type="number" step="0.01" min="0" :name="`columns[${i}][insurance_takaful]`" x-model.number="c.col.insurance_takaful"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                    </template>
                </x-quote-row>
                <x-quote-row-shared label="Roadtax 1 Tahun (RM)">
                    <input type="number" step="0.01" min="0" name="shared[roadtax]" x-model.number="f.shared.roadtax"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                </x-quote-row-shared>

                {{-- live computed totals --}}
                <div class="grid gap-2 mt-3 py-3 border-t-2 border-gray-200" :style="gridStyle">
                    <div class="text-sm font-bold text-gray-900">Jumlah Keseluruhan</div>
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <div class="text-sm font-bold text-orange-700 text-right" x-text="'RM ' + total(c).toFixed(2)"></div>
                    </template>
                </div>

                <div class="mt-2 rounded-lg bg-gray-50 p-3 space-y-1.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Bayaran Ansuran</p>
                    @foreach($inst as $key => $meta)
                        <div class="grid gap-2 text-sm" :style="gridStyle">
                            <div class="text-gray-600">{{ $meta['label'] }}</div>
                            <template x-for="(c, i) in selected()" :key="c.name">
                                <div class="text-right text-gray-800" x-text="'RM ' + instalment('{{ $key }}', total(c)).toFixed(2)"></div>
                            </template>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('quote-templates.index') }}" class="rounded-md bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200">Batal</a>
            <button type="submit" :disabled="selected().length === 0"
                    class="rounded-md bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Simpan &amp; Pratonton
            </button>
        </div>
    </form>

    <script>
        function quoteForm() {
            return {
                f: @js([
                    'reg'       => $template->vehicle_reg_number,
                    'model'     => $template->vehicle_model,
                    'shared'    => $d['shared'],
                    'companies' => $companies,
                ]),
                tints: ['bg-sky-200', 'bg-yellow-200', 'bg-green-200'],

                selected() {
                    return this.f.companies.filter(c => c.selected);
                },
                get cols() {
                    return Math.max(this.selected().length, 1);
                },
                get gridStyle() {
                    return `grid-template-columns:160px repeat(${this.cols},minmax(120px,1fr))`;
                },
                tint(i) {
                    return this.tints[i % this.tints.length];
                },
                total(c) {
                    const digital = this.f.shared.digital_copy === 'yes' ? 5 : 0;
                    return (Number(c.col.insurance_takaful) || 0) + (Number(this.f.shared.roadtax) || 0) + digital;
                },
                instalment(provider, total) {
                    switch (provider) {
                        case 'atome':     return total * 1.08;
                        case 'ahapay':    return Math.round(total * 1.035);
                        case 'spaylater': return total * 1.02;
                        default:          return total;   // directlending
                    }
                },
            }
        }
    </script>
</x-app-layout>
