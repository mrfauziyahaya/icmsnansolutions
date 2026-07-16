<x-public-layout>
    <div class="max-w-lg mx-auto my-12 sm:my-20">
        <div class="bg-white shadow rounded-xl px-6 sm:px-8 py-10 sm:py-12 flex flex-col items-center text-center">

            @if($ok)
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-6">
                    <svg class="h-9 w-9 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Pembayaran Diterima</h1>
                <p class="text-gray-600 text-sm max-w-sm">
                    Terima kasih. Pembayaran anda sedang diproses dan resit akan dihantar ke e-mel anda.
                </p>
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-6">
                    <svg class="h-9 w-9 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Pembayaran Tidak Berjaya</h1>
                <p class="text-gray-600 text-sm max-w-sm">
                    Pembayaran anda tidak dapat diproses atau telah dibatalkan. Tiada caj dikenakan.
                </p>
            @endif

            @if($payment)
                <dl class="mt-8 w-full rounded-lg bg-gray-50 divide-y divide-gray-200 text-sm text-left">
                    <div class="flex justify-between gap-4 px-4 py-3">
                        <dt class="text-gray-500">Rujukan</dt>
                        <dd class="font-mono font-semibold text-gray-900">{{ $payment->reference }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 px-4 py-3">
                        <dt class="text-gray-500">Kenderaan</dt>
                        <dd class="text-gray-900">{{ $payment->vehicle_plate }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 px-4 py-3">
                        <dt class="text-gray-500">Jumlah</dt>
                        <dd class="font-bold text-gray-900">RM {{ number_format($payment->amount, 2) }}</dd>
                    </div>
                </dl>

                <p class="mt-4 text-xs text-gray-400">
                    Sila simpan nombor rujukan ini untuk sebarang pertanyaan.
                </p>
            @endif

            <div class="mt-8 flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                @if(! $ok)
                    <a href="{{ route('pay.create') }}"
                       class="rounded-md bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-700">
                        Cuba Lagi
                    </a>
                @endif
                <a href="https://wa.link/cikhnz" target="_blank" rel="noopener"
                   class="rounded-md bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</x-public-layout>
