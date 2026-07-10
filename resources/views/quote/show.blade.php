<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Sebut Harga &mdash; {{ $quote->no_plate }}</h2>
            <a href="{{ route('quote-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
        </div>
    </x-slot>

    @php
        $tambahan = $quote->perlindungan_tambahan ?? [];
    @endphp

    <div class="max-w-3xl space-y-6">

        <!-- Maklumat Pemilik -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-orange-600 px-6 py-3"><h3 class="text-white font-semibold text-sm tracking-wide">MAKLUMAT PEMILIK KENDERAAN</h3></div>
            <dl class="divide-y divide-gray-100">
                <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Nama Pemilik Kenderaan</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->nama_pemilik }}</dd></div>
                <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">No IC Pemilik</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->no_ic }}</dd></div>
                <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Poskod</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->poskod }}</dd></div>
                <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">No Plate Kenderaan</dt><dd class="text-sm text-gray-900 col-span-2 font-semibold">{{ $quote->no_plate }}</dd></div>
            </dl>
        </div>

        <!-- Maklumat Kenderaan -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-orange-600 px-6 py-3"><h3 class="text-white font-semibold text-sm tracking-wide">MAKLUMAT KENDERAAN</h3></div>
            <dl class="divide-y divide-gray-100">
                <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Digunakan Untuk E-Hailing?</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->ehailing ? 'Ya' : 'Tidak' }}</dd></div>
                @if($quote->ehailing)
                    <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Digunakan Untuk</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->ehailing_usage ?? '-' }}</dd></div>
                @else
                    <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Kenderaan Baru Tukar Milik</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->tukar_milik ? 'Ya' : 'Tidak' }}</dd></div>
                @endif
                <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Nombor Whatsapp</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->whatsapp }}</dd></div>
            </dl>
        </div>

        <!-- Perlindungan -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-orange-600 px-6 py-3"><h3 class="text-white font-semibold text-sm tracking-wide">PERLINDUNGAN</h3></div>
            <dl class="divide-y divide-gray-100">
                <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Jenis Perlindungan</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->jenis_perlindungan }}</dd></div>
                @if(!empty($tambahan))
                    <div class="px-6 py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 col-span-1">Perlindungan Tambahan</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($tambahan as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                    @if($quote->jumlah_perlindungan_cermin)
                        <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Jumlah Perlindungan Cermin Diperlukan</dt><dd class="text-sm text-gray-900 col-span-2">RM {{ number_format($quote->jumlah_perlindungan_cermin, 2) }}</dd></div>
                    @endif
                @endif
            </dl>
        </div>

        <!-- Jenis Pembayaran -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-orange-600 px-6 py-3"><h3 class="text-white font-semibold text-sm tracking-wide">JENIS PEMBAYARAN</h3></div>
            <dl class="divide-y divide-gray-100">
                <div class="px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm font-medium text-gray-500 col-span-1">Pilihan Jenis Pembayaran</dt><dd class="text-sm text-gray-900 col-span-2">{{ $quote->jenis_pembayaran }}</dd></div>
            </dl>
        </div>

        <!-- Status toggle -->
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('quote-requests.toggle-read', $quote) }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="rounded-md px-5 py-2 text-sm font-semibold text-white {{ $quote->is_read ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-600 hover:bg-green-700' }}">
                    {{ $quote->is_read ? 'Tanda sebagai Unread' : 'Tanda sebagai Read' }}
                </button>
            </form>
            <span class="text-sm {{ $quote->is_read ? 'text-green-600' : 'text-gray-400' }}">
                Status: {{ $quote->is_read ? 'Read' : 'Unread' }}
            </span>
        </div>
    </div>
</x-app-layout>
