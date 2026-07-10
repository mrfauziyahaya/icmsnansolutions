<x-public-layout>
    <div class="max-w-lg mx-auto my-16 sm:my-24" x-data
         x-init="setTimeout(() => $refs.walink.click(), 3000)">
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
            <p class="text-gray-400 text-xs mt-8">Anda akan disambungkan ke WhatsApp&hellip;</p>

            <a x-ref="walink" href="https://wa.link/cikhnz" target="_blank" rel="noopener"
               class="mt-6 inline-flex items-center gap-2 rounded-md bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.521-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Buka WhatsApp
            </a>
        </div>
    </div>
</x-public-layout>
