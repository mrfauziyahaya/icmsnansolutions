<x-public-layout>
    <div class="max-w-lg mx-auto my-16 sm:my-24" x-data x-init="setTimeout(() => window.location.href = 'https://wa.link/cikhnz', 3000)">
        <div class="bg-white shadow rounded-xl px-8 py-12 flex flex-col items-center text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-6">
                <svg class="h-9 w-9 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-3">Berjaya!</h1>
            <p class="text-gray-600 text-sm max-w-xs">
                Permohonan sebut harga anda telah dihantar.<br>
                Kami akan menghubungi anda tidak lama lagi.
            </p>
            <p class="text-gray-400 text-xs mt-8">Anda akan diarahkan semula dalam masa 3 saat&hellip;</p>
        </div>
    </div>

    <noscript>
        <meta http-equiv="refresh" content="3;url=https://wa.link/cikhnz">
    </noscript>
</x-public-layout>
