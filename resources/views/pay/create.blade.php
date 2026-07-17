<x-public-layout>
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-6 sm:mb-8">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">PEMBAYARAN CUKAI JALAN &amp; INSURANS</h1>
            <p class="text-sm text-gray-500 mt-1">Masukkan maklumat anda dan jumlah bayaran.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(empty($gateways))
            <div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-6 text-center text-yellow-800 text-sm">
                <p class="font-semibold mb-1">Pembayaran dalam talian belum tersedia.</p>
                <p>Sila hubungi kami untuk membuat pembayaran.</p>
            </div>
        @else
        <div x-data="payForm()" class="bg-white shadow rounded-xl overflow-hidden">

            <!-- Step indicator -->
            <div class="flex border-b border-gray-100">
                <template x-for="(label, i) in steps" :key="i">
                    <div class="flex-1 px-1 py-3 text-center text-xs font-medium border-b-2"
                         :class="step === (i+1) ? 'border-orange-600 text-orange-600' : (step > (i+1) ? 'border-green-500 text-green-600' : 'border-transparent text-gray-400')">
                        <span class="hidden sm:inline" x-text="(i+1) + '. ' + label"></span>
                        <span class="sm:hidden" x-text="(i+1)"></span>
                    </div>
                </template>
            </div>

            <form method="POST" action="{{ route('pay.store') }}" class="p-5 sm:p-8" @submit="submitting = true">
                @csrf

                <!-- ── STEP 1: Butiran ──────────────────────────────────────── -->
                <div x-show="step === 1" x-cloak class="space-y-5">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Butiran Pembayaran</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penuh <span class="text-red-500">*</span></label>
                        <input type="text" name="payer_name" x-model="form.payer_name" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mel <span class="text-red-500">*</span></label>
                            <input type="email" name="payer_email" x-model="form.payer_email" required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Telefon <span class="text-red-500">*</span></label>
                            <input type="text" name="payer_phone" x-model="form.payer_phone" required placeholder="0129622878"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                        <textarea name="address" x-model="form.address" rows="3" maxlength="500" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bayaran (RM) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" :min="minAmount" :max="maxAmount" name="amount" x-model="form.amount" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-lg font-semibold">
                        <p class="mt-1 text-xs italic text-gray-500">
                            Jumlah antara RM<span x-text="minAmount"></span> dan RM<span x-text="Number(maxAmount).toLocaleString()"></span>
                        </p>
                    </div>
                </div>

                <!-- ── STEP 2: Kaedah Pembayaran ────────────────────────────── -->
                <div x-show="step === 2" x-cloak class="space-y-5">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Kaedah Pembayaran</h2>

                    <div class="space-y-2">
                        <template x-for="(label, key) in gateways" :key="key">
                            <label x-show="isSelectable(key)"
                                   class="flex items-center gap-3 cursor-pointer rounded-md border border-gray-300 px-4 py-3.5 text-sm"
                                   :class="form.gateway === key ? 'border-orange-600 bg-orange-50' : ''">
                                <input type="radio" name="gateway" :value="key" x-model="form.gateway"
                                       class="text-orange-600 focus:ring-orange-500">
                                <span x-text="label" :class="form.gateway === key ? 'text-orange-700 font-semibold' : 'text-gray-700'"></span>
                            </label>
                        </template>
                    </div>

                    <p x-show="hasHiddenBnpl()" x-cloak class="text-xs italic text-gray-500">
                        Sebahagian pilihan ansuran hanya tersedia untuk jumlah RM<span x-text="bnplMin"></span> ke atas.
                    </p>
                </div>

                <!-- ── STEP 3: Semakan ──────────────────────────────────────── -->
                <div x-show="step === 3" x-cloak class="space-y-5">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Semakan</h2>

                    <dl class="rounded-lg bg-gray-50 divide-y divide-gray-200 text-sm">
                        <div class="flex justify-between gap-4 px-4 py-3">
                            <dt class="text-gray-500">Nama</dt><dd class="text-gray-900 text-right" x-text="form.payer_name"></dd>
                        </div>
                        <div class="flex justify-between gap-4 px-4 py-3">
                            <dt class="text-gray-500">E-mel</dt><dd class="text-gray-900 text-right break-all" x-text="form.payer_email"></dd>
                        </div>
                        <div class="flex justify-between gap-4 px-4 py-3">
                            <dt class="text-gray-500">Telefon</dt><dd class="text-gray-900 text-right" x-text="form.payer_phone"></dd>
                        </div>
                        <div class="flex justify-between gap-4 px-4 py-3">
                            <dt class="text-gray-500">Alamat</dt><dd class="text-gray-900 text-right" x-text="form.address"></dd>
                        </div>
                        <div class="flex justify-between gap-4 px-4 py-3">
                            <dt class="text-gray-500">Kaedah</dt><dd class="text-gray-900 text-right" x-text="gateways[form.gateway]"></dd>
                        </div>
                        <div class="flex justify-between gap-4 px-4 py-3 bg-orange-50">
                            <dt class="font-semibold text-gray-700">Jumlah</dt>
                            <dd class="font-bold text-orange-700 text-base">RM <span x-text="Number(form.amount || 0).toFixed(2)"></span></dd>
                        </div>
                    </dl>

                    @if(config('services.turnstile.site_key'))
                        <div>
                            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                        </div>
                    @endif

                    <p class="text-xs text-gray-500">
                        Dengan menekan butang di bawah, anda akan dibawa ke laman pembayaran yang selamat.
                    </p>
                </div>

                <!-- ── Navigation ───────────────────────────────────────────── -->
                <div class="flex items-center justify-between gap-3 mt-8 pt-6 border-t border-gray-100">
                    <button type="button" x-show="step > 1" @click="prev()"
                        class="rounded-md bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                        Kembali
                    </button>
                    <span x-show="step === 1"></span>

                    <button type="button" x-show="step < 3" @click="next()"
                        class="rounded-md bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-700">
                        Seterusnya
                    </button>
                    <button type="submit" x-show="step === 3" x-cloak :disabled="submitting"
                        class="rounded-md bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="!submitting">Bayar Sekarang</span>
                        <span x-show="submitting" x-cloak>Menghantar&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

    @if(config('services.turnstile.site_key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif

    <script>
        function payForm() {
            return {
                step: 1,
                submitting: false,
                steps: ['Butiran', 'Kaedah', 'Semakan'],
                minAmount: @json($minAmount),
                maxAmount: @json($maxAmount),
                bnplMin: @json($bnplMin),
                bnplKeys: @json($bnpl),
                gateways: @json($gateways),
                form: {
                    payer_name: @json(old('payer_name', '')),
                    payer_email: @json(old('payer_email', '')),
                    payer_phone: @json(old('payer_phone', '')),
                    address: @json(old('address', '')),
                    amount: @json(old('amount', '')),
                    gateway: @json(old('gateway', '')),
                },
                isSelectable(key) {
                    if (!this.bnplKeys.includes(key)) return true;
                    return Number(this.form.amount || 0) >= Number(this.bnplMin);
                },
                hasHiddenBnpl() {
                    return Object.keys(this.gateways).some(k => this.bnplKeys.includes(k) && !this.isSelectable(k));
                },
                validate() {
                    if (this.step === 1) {
                        const f = this.form;
                        if (!f.payer_name || !f.payer_email || !f.payer_phone || !f.address) { alert('Sila lengkapkan semua maklumat.'); return false; }
                        const amt = Number(f.amount || 0);
                        if (!amt || amt < this.minAmount || amt > this.maxAmount) {
                            alert('Jumlah bayaran mesti antara RM' + this.minAmount + ' dan RM' + this.maxAmount + '.');
                            return false;
                        }
                        // A method chosen earlier may no longer be valid for a changed amount.
                        if (this.form.gateway && !this.isSelectable(this.form.gateway)) this.form.gateway = '';
                    }
                    if (this.step === 2 && !this.form.gateway) { alert('Sila pilih kaedah pembayaran.'); return false; }
                    return true;
                },
                next() { if (this.validate()) this.step++; },
                prev() { if (this.step > 1) this.step--; },
            }
        }
    </script>

    <style>[x-cloak]{display:none!important}</style>
</x-public-layout>
