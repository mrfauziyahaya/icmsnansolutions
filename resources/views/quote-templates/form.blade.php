<x-app-layout>
    @php
        $QT   = \App\Models\QuoteTemplate::class;
        $type = $template->type ?: $QT::DEFAULT_TYPE;
        $cfg  = $QT::typeConfig($type);
        $d    = $template->data;
        $F    = $QT::FIELDS;

        // Every selectable company for this type, carrying saved values (or
        // defaults), plus its own resolved select/multiselect option lists.
        $companyFields = $QT::fieldKeys($type, 'company');
        $saved         = collect($d['columns'])->keyBy('company');
        $companies     = [];
        foreach ($QT::companiesForType($type) as $name) {
            $col     = $saved->get($name);
            $options = [];
            foreach ($companyFields as $field) {
                if (in_array($F[$field]['input'], ['select', 'multiselect'])) {
                    $options[$field] = $QT::optionList($field, $type, $name);
                }
            }
            $values = \Illuminate\Support\Arr::except($col ?? $QT::defaultColumn($type, $name), ['company']);

            // An unanswered select must bind to the blank <option value="">, and
            // x-model cannot match null against it.
            foreach ($values as $key => $value) {
                if ($value === null && ($F[$key]['input'] ?? null) === 'select') {
                    $values[$key] = '';
                }
            }

            $companies[] = [
                'name'     => $name,
                'logo'     => $QT::logoFor($name) ? asset($QT::ALL_COMPANIES[$name]) : null,
                'selected' => $col !== null,
                'col'      => $values,
                'options'  => $options,
            ];
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $template->exists ? 'Edit' : 'Baru' }} — {{ $cfg['label'] }}
        </h2>
    </x-slot>

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
        <input type="hidden" name="type" value="{{ $type }}">

        {{-- ── Header info ─────────────────────────────────────────────── --}}
        <div class="bg-white shadow rounded-lg p-5 sm:p-6 space-y-5">
            <p class="text-center text-sm font-bold uppercase tracking-wide text-gray-500">{{ $cfg['title'] }}</p>
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

        {{-- Hidden company name per selected column, in order. --}}
        <template x-for="(c, i) in selected()" :key="'company-' + c.name">
            <input type="hidden" :name="`columns[${i}][company]`" :value="c.name">
        </template>

        {{-- ── Comparison grid ─────────────────────────────────────────── --}}
        <div class="bg-white shadow rounded-lg mt-6 overflow-x-auto">
            <div class="p-4 sm:p-6" x-show="selected().length > 0" x-cloak>

                {{-- company headers: logo on white above, name in the coloured card --}}
                <div class="grid gap-2 items-end sticky top-0 z-10 bg-white py-1" :style="gridStyle">
                    <div class="flex items-end text-xs font-semibold uppercase tracking-wide text-gray-500 pb-2">Sebut Harga</div>
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <div>
                            <div class="h-16 flex items-center justify-center">
                                <template x-if="c.logo"><img :src="c.logo" :alt="c.name" class="max-h-14 w-auto object-contain"></template>
                            </div>
                            <div class="rounded-md px-2 py-2 text-center text-sm font-bold uppercase text-gray-800" :class="tint(i)">
                                <span x-text="c.name"></span>
                            </div>
                        </div>
                    </template>
                </div>

                @foreach($cfg['sections'] as $section => $rows)
                    <x-quote-section>{{ $section }}</x-quote-section>
                    @foreach($rows as $field)
                        @php $def = $F[$field]; @endphp

                        @if($def['scope'] === 'shared')
                            <x-quote-row-shared label="{{ $def['label'] }}">
                                @if($def['input'] === 'number')
                                    <input type="number" step="0.01" min="0" name="shared[{{ $field }}]" x-model.number="f.shared.{{ $field }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                                @else
                                    <select name="shared[{{ $field }}]" x-model="f.shared.{{ $field }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        @if($QT::isOptional($field))<option value="">—</option>@endif
                                        @foreach($QT::optionsFor($field, $type) as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                                    </select>
                                @endif
                            </x-quote-row-shared>
                        @else
                            <x-quote-row label="{{ $def['label'] }}">
                                <template x-for="(c, i) in selected()" :key="c.name">
                                    @if($def['input'] === 'number')
                                        <input type="number" step="0.01" min="0" :name="`columns[${i}][{{ $field }}]`" x-model.number="c.col.{{ $field }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                                    @elseif($def['input'] === 'multiselect')
                                        <div class="space-y-1">
                                            <template x-for="opt in c.options.{{ $field }}" :key="opt.v">
                                                <label class="flex items-start gap-1.5 text-xs text-gray-700">
                                                    <input type="checkbox" :value="opt.v" x-model="c.col.{{ $field }}"
                                                           :name="`columns[${i}][{{ $field }}][]`"
                                                           class="mt-0.5 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                                    <span x-text="opt.l"></span>
                                                </label>
                                            </template>
                                        </div>
                                    @else
                                        <select :name="`columns[${i}][{{ $field }}]`" x-model="c.col.{{ $field }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            @if($QT::isOptional($field))<option value="">—</option>@endif
                                            <template x-for="opt in c.options.{{ $field }}" :key="opt.v">
                                                <option :value="opt.v" x-text="opt.l"></option>
                                            </template>
                                        </select>
                                    @endif
                                </template>
                            </x-quote-row>
                        @endif
                    @endforeach
                @endforeach

                {{-- live computed totals --}}
                <div class="grid gap-2 mt-3 py-3 border-t-2 border-gray-200" :style="gridStyle">
                    <div class="text-sm font-bold text-gray-900">Jumlah Keseluruhan</div>
                    <template x-for="(c, i) in selected()" :key="c.name">
                        <div class="text-sm font-bold text-orange-700 text-right" x-text="'RM ' + total(c).toFixed(2)"></div>
                    </template>
                </div>

                <div class="mt-2 rounded-lg bg-gray-50 p-3 space-y-1.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Bayaran Ansuran</p>
                    @foreach($QT::INSTALMENTS as $key => $meta)
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

                selected() { return this.f.companies.filter(c => c.selected); },
                get cols() { return Math.max(this.selected().length, 1); },
                get gridStyle() { return `grid-template-columns:160px repeat(${this.cols},minmax(120px,1fr))`; },
                tint(i) { return this.tints[i % this.tints.length]; },
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
