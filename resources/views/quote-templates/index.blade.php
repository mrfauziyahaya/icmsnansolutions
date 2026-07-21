<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-900">Quote Template</h2>
            <a href="{{ route('quote-templates.create') }}"
               class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                + Sebut Harga Baru
            </a>
        </div>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-100 flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari no. pendaftaran / model / tajuk"
                   class="block w-full sm:max-w-sm rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
            <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900">Cari</button>
            @if($search)
                <a href="{{ route('quote-templates.index') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No. Pendaftaran</th>
                        <th class="px-4 py-3">Model</th>
                        <th class="px-4 py-3">Tajuk</th>
                        <th class="px-4 py-3">Dikemaskini</th>
                        <th class="px-4 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($templates as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $t->vehicle_reg_number }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $t->vehicle_model ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $t->title }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $t->updated_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('quote-templates.show', $t) }}" class="font-semibold text-orange-600 hover:text-orange-700">Pratonton</a>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('quote-templates.edit', $t) }}" class="font-semibold text-gray-600 hover:text-gray-900">Edit</a>
                                    <span class="text-gray-300">|</span>
                                    <form method="POST" action="{{ route('quote-templates.destroy', $t) }}"
                                          onsubmit="return confirm('Padam sebut harga ini?')">
                                        @csrf @method('DELETE')
                                        <button class="font-semibold text-red-600 hover:text-red-700">Padam</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Tiada sebut harga lagi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="p-4 border-t border-gray-100">{{ $templates->links() }}</div>
        @endif
    </div>
</x-app-layout>
