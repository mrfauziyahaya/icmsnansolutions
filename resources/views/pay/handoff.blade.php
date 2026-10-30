<x-public-layout>
    <div class="max-w-lg mx-auto my-12 sm:my-20">
        <div class="bg-white shadow rounded-xl px-6 sm:px-8 py-10 sm:py-12 flex flex-col items-center text-center">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Mengalihkan ke halaman pembayaran…</h1>
            <p class="text-gray-600 text-sm max-w-sm">
                Sila tunggu sebentar. Jika halaman tidak beralih secara automatik, tekan butang di bawah.
            </p>

            {{-- The gateway expects a form POST; the fields carry a signed, 1-minute token. --}}
            <form id="gateway-handoff" method="POST" action="{{ $action }}" class="mt-8">
                @foreach($fields as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <button type="submit" class="inline-flex items-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">
                    Teruskan Pembayaran
                </button>
            </form>
        </div>
    </div>

    <script>document.getElementById('gateway-handoff').submit();</script>
</x-public-layout>
