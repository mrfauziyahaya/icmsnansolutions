<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">Request Sebut Harga</h2>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <p class="text-sm text-gray-500">{{ $quotes->total() }} permohonan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tarikh</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">No. Telefon</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">No. Plate</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($quotes as $quote)
                        <tr class="hover:bg-gray-50 {{ $quote->is_read ? '' : 'bg-orange-50/40' }}">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500">{{ $quote->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 {{ $quote->is_read ? '' : 'font-bold' }}">{{ $quote->nama_pemilik }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $quote->whatsapp }}</td>
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $quote->no_plate }}</td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('quote-requests.toggle-read', $quote) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" role="switch" aria-checked="{{ $quote->is_read ? 'true' : 'false' }}"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $quote->is_read ? 'bg-green-500' : 'bg-gray-300' }}"
                                        title="{{ $quote->is_read ? 'Read — klik untuk tanda Unread' : 'Unread — klik untuk tanda Read' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $quote->is_read ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                    <div class="text-xs mt-1 {{ $quote->is_read ? 'text-green-600' : 'text-gray-400' }}">{{ $quote->is_read ? 'Read' : 'Unread' }}</div>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('quote-requests.show', $quote) }}"
                                   class="inline-flex items-center rounded-md bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-700">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-400">Tiada permohonan sebut harga.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($quotes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
