<x-public-layout>
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">SEBUT HARGA CUKAI KENDERAAN</h1>
            <p class="text-sm text-gray-500 mt-1">Sila lengkapkan borang di bawah untuk mendapatkan sebut harga.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-4 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div x-data="quoteForm()" class="bg-white shadow rounded-xl overflow-hidden">

            <!-- Step indicator -->
            <div class="flex border-b border-gray-100">
                <template x-for="(label, i) in steps" :key="i">
                    <div class="flex-1 px-2 py-3 text-center text-xs font-medium border-b-2"
                         :class="step === (i+1) ? 'border-orange-600 text-orange-600' : (step > (i+1) ? 'border-green-500 text-green-600' : 'border-transparent text-gray-400')">
                        <span class="hidden sm:inline" x-text="(i+1) + '. ' + label"></span>
                        <span class="sm:hidden" x-text="(i+1)"></span>
                    </div>
                </template>
            </div>

            <form method="POST" action="{{ route('quote.store') }}" class="p-6 sm:p-8">
                @csrf

                <!-- ── STEP 1: Maklumat Pemilik Kenderaan ──────────────────── -->
                <div x-show="step === 1" x-cloak class="space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900">Maklumat Pemilik Kenderaan</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemilik Kenderaan <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_pemilik" x-model="form.nama_pemilik" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. IC Pemilik <span class="text-red-500">*</span></label>
                        <input type="text" name="no_ic" x-model="form.no_ic" required placeholder="900726145203"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Poskod <span class="text-red-500">*</span></label>
                        <input type="text" name="poskod" x-model="form.poskod" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Plate Kenderaan <span class="text-red-500">*</span></label>
                        <input type="text" name="no_plate" x-model="form.no_plate" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm uppercase">
                    </div>
                </div>

                <!-- ── STEP 2: Maklumat Kenderaan ───────────────────────────── -->
                <div x-show="step === 2" x-cloak class="space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900">Maklumat Kenderaan</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adakah Kenderaan Anda Digunakan Untuk E-Hailing? <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <template x-for="opt in ['Ya','Tidak']" :key="opt">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="ehailing" :value="opt" x-model="form.ehailing" class="peer sr-only">
                                    <span class="block text-center rounded-md border border-gray-300 py-2 text-sm peer-checked:border-orange-600 peer-checked:bg-orange-50 peer-checked:text-orange-700 peer-checked:font-semibold"
                                          x-text="opt"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- E-Hailing = Ya -->
                    <div x-show="form.ehailing === 'Ya'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Digunakan Untuk <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <template x-for="opt in ['Harian','Tahunan']" :key="opt">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="ehailing_usage" :value="opt" x-model="form.ehailing_usage" class="peer sr-only">
                                    <span class="block text-center rounded-md border border-gray-300 py-2 text-sm peer-checked:border-orange-600 peer-checked:bg-orange-50 peer-checked:text-orange-700 peer-checked:font-semibold"
                                          x-text="opt"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- E-Hailing = Tidak -->
                    <div x-show="form.ehailing === 'Tidak'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kenderaan Baru Tukar Milik <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <template x-for="opt in ['Ya','Tidak']" :key="opt">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="tukar_milik" :value="opt" x-model="form.tukar_milik" class="peer sr-only">
                                    <span class="block text-center rounded-md border border-gray-300 py-2 text-sm peer-checked:border-orange-600 peer-checked:bg-orange-50 peer-checked:text-orange-700 peer-checked:font-semibold"
                                          x-text="opt"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombor Whatsapp <span class="text-red-500">*</span></label>
                        <input type="text" name="whatsapp" x-model="form.whatsapp" required placeholder="0129622878"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>
                </div>

                <!-- ── STEP 3: Perlindungan ─────────────────────────────────── -->
                <div x-show="step === 3" x-cloak class="space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900">Perlindungan</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Perlindungan <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <template x-for="opt in perlindunganTypes" :key="opt">
                                <label class="flex items-center gap-3 cursor-pointer rounded-md border border-gray-300 px-4 py-3 text-sm"
                                       :class="form.jenis_perlindungan === opt ? 'border-orange-600 bg-orange-50' : ''">
                                    <input type="radio" name="jenis_perlindungan" :value="opt" x-model="form.jenis_perlindungan"
                                           class="text-orange-600 focus:ring-orange-500">
                                    <span x-text="opt" :class="form.jenis_perlindungan === opt ? 'text-orange-700 font-semibold' : 'text-gray-700'"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- 1st Party Comprehensive → multi-select add-ons -->
                    <template x-if="form.jenis_perlindungan === '1st Party Comprehensive'">
                        <div class="space-y-3 rounded-lg bg-gray-50 p-4">
                            <label class="block text-sm font-medium text-gray-700">Perlindungan Tambahan</label>
                            <template x-for="opt in addonsComprehensive" :key="opt">
                                <label class="flex items-start gap-3 cursor-pointer text-sm">
                                    <input type="checkbox" name="perlindungan_tambahan[]" :value="opt" x-model="form.perlindungan_tambahan"
                                           class="mt-0.5 rounded text-orange-600 focus:ring-orange-500">
                                    <span class="text-gray-700" x-text="opt"></span>
                                </label>
                            </template>

                            <!-- Cermin → jumlah -->
                            <div x-show="form.perlindungan_tambahan.includes('Cermin')" x-cloak class="pt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Perlindungan Cermin Diperlukan (RM)</label>
                                <input type="number" step="0.01" name="jumlah_perlindungan_cermin" x-model="form.jumlah_perlindungan_cermin"
                                    placeholder="1500"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                        </div>
                    </template>

                    <!-- 3rd Party Fire & Theft → single-select add-on -->
                    <template x-if="form.jenis_perlindungan === '3rd Party Fire & Theft (Selain dari motorsikal)'">
                        <div class="space-y-2 rounded-lg bg-gray-50 p-4">
                            <label class="block text-sm font-medium text-gray-700">Perlindungan Tambahan</label>
                            <template x-for="opt in ['Unlimited Towing','Tak Perlu Tambahan']" :key="opt">
                                <label class="flex items-center gap-3 cursor-pointer text-sm">
                                    <input type="radio" name="perlindungan_tambahan" :value="opt" x-model="form.perlindungan_tambahan_3rd"
                                           class="text-orange-600 focus:ring-orange-500">
                                    <span class="text-gray-700" x-text="opt"></span>
                                </label>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- ── STEP 4: Jenis Pembayaran ─────────────────────────────── -->
                <div x-show="step === 4" x-cloak class="space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900">Jenis Pembayaran</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Jenis Pembayaran <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="opt in paymentTypes" :key="opt">
                                <label class="flex items-center gap-3 cursor-pointer rounded-md border border-gray-300 px-4 py-3 text-sm"
                                       :class="form.jenis_pembayaran === opt ? 'border-orange-600 bg-orange-50' : ''">
                                    <input type="radio" name="jenis_pembayaran" :value="opt" x-model="form.jenis_pembayaran"
                                           class="text-orange-600 focus:ring-orange-500">
                                    <span x-text="opt" :class="form.jenis_pembayaran === opt ? 'text-orange-700 font-semibold' : 'text-gray-700'"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ── Navigation ───────────────────────────────────────────── -->
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                    <button type="button" x-show="step > 1" @click="prev()"
                        class="rounded-md bg-gray-100 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                        Kembali
                    </button>
                    <span x-show="step === 1"></span>

                    <button type="button" x-show="step < 4" @click="next()"
                        class="rounded-md bg-orange-600 px-6 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                        Seterusnya
                    </button>
                    <button type="submit" x-show="step === 4" x-cloak
                        class="rounded-md bg-green-600 px-6 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        Hantar Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function quoteForm() {
            return {
                step: 1,
                steps: ['Pemilik', 'Kenderaan', 'Perlindungan', 'Pembayaran'],
                perlindunganTypes: [
                    '1st Party Comprehensive',
                    '3rd Party (Motorsikal sahaja)',
                    '3rd Party Fire & Theft (Selain dari motorsikal)',
                ],
                addonsComprehensive: [
                    'Cermin',
                    'Bencana alam',
                    'Unlimited towing',
                    'Legal Liability of Passengers for Acts of Negligence (LLOP)',
                    'Legal Liability to Passengers (LLTP)',
                    'Key Replacement',
                    'Towing and Cleaning due to Water Damage',
                    'Waiver of Betterment',
                    'Compensation for Assessed Repair Time (CART)',
                ],
                paymentTypes: [
                    'Atome',
                    'Shopee SPayLater',
                    'Grab PayLater',
                    'Boost PayFlex',
                    'AhaPay',
                    'Direct Lending',
                    'Credit Card',
                    'Bayaran Penuh (Online Transfer)',
                ],
                form: {
                    nama_pemilik: '', no_ic: '', poskod: '', no_plate: '',
                    ehailing: '', ehailing_usage: '', tukar_milik: '', whatsapp: '',
                    jenis_perlindungan: '', perlindungan_tambahan: [],
                    perlindungan_tambahan_3rd: '', jumlah_perlindungan_cermin: '',
                    jenis_pembayaran: '',
                },
                validate() {
                    if (this.step === 1) {
                        if (!this.form.nama_pemilik || !this.form.no_ic || !this.form.poskod || !this.form.no_plate) {
                            alert('Sila lengkapkan semua maklumat pemilik kenderaan.'); return false;
                        }
                    }
                    if (this.step === 2) {
                        if (!this.form.ehailing) { alert('Sila pilih sama ada kenderaan digunakan untuk e-hailing.'); return false; }
                        if (this.form.ehailing === 'Ya' && !this.form.ehailing_usage) { alert('Sila pilih kegunaan.'); return false; }
                        if (this.form.ehailing === 'Tidak' && !this.form.tukar_milik) { alert('Sila pilih sama ada kenderaan baru tukar milik.'); return false; }
                        if (!this.form.whatsapp) { alert('Sila masukkan nombor Whatsapp.'); return false; }
                    }
                    if (this.step === 3) {
                        if (!this.form.jenis_perlindungan) { alert('Sila pilih jenis perlindungan.'); return false; }
                        if (this.form.jenis_perlindungan === '1st Party Comprehensive'
                            && this.form.perlindungan_tambahan.includes('Cermin')
                            && !this.form.jumlah_perlindungan_cermin) {
                            alert('Sila masukkan jumlah perlindungan cermin diperlukan.'); return false;
                        }
                    }
                    return true;
                },
                next() { if (this.validate()) this.step++; },
                prev() { if (this.step > 1) this.step--; },
            }
        }
    </script>

    <style>[x-cloak]{display:none!important}</style>
</x-public-layout>
