<x-public-layout>
    <div class="max-w-lg mx-auto text-center py-12" x-data x-init="setTimeout(() => window.location.href = 'https://nansolutions.com.my', 3000)">
        <div class="bg-white shadow rounded-xl p-10">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-6">
                <svg class="h-9 w-9 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Berjaya!</h1>
            <p class="text-gray-600 text-sm">
                Permohonan sebut harga anda telah dihantar.<br>
                Kami akan menghubungi anda tidak lama lagi.
            </p>
            <p class="text-gray-400 text-xs mt-6">Anda akan diarahkan semula dalam masa 3 saat&hellip;</p>
        </div>
    </div>

    <noscript>
        <meta http-equiv="refresh" content="3;url=https://nansolutions.com.my">
    </noscript>
</x-public-layout>
