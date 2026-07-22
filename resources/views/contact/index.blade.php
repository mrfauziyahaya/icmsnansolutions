<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold text-gray-900">Mesej Hubungi</h2>
            @if($unread > 0)
                <span class="rounded-full bg-orange-100 text-orange-700 text-xs font-semibold px-2.5 py-1">{{ $unread }} belum dibaca</span>
            @endif
        </div>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-100 flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / e-mel / telefon / perkara"
                   class="block w-full sm:max-w-sm rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
            <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900">Cari</button>
            @if($search)
                <a href="{{ route('contact.index') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Reset</a>
            @endif
        </form>

        <div class="divide-y divide-gray-100">
            @forelse($messages as $m)
                <div class="p-4 sm:p-5 {{ $m->is_read ? '' : 'bg-orange-50/40' }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                @unless($m->is_read)<span class="h-2 w-2 rounded-full bg-orange-500 shrink-0"></span>@endunless
                                <p class="font-semibold text-gray-900">{{ $m->name }}</p>
                            </div>
                            <p class="mt-0.5 text-sm text-gray-600">
                                @if($m->email)<a href="mailto:{{ $m->email }}" class="hover:text-orange-600">{{ $m->email }}</a>@endif
                                @if($m->phone) · <a href="tel:{{ $m->phone }}" class="hover:text-orange-600">{{ $m->phone }}</a>@endif
                            </p>
                            @if($m->subject)<p class="mt-2 text-sm font-medium text-gray-800">{{ $m->subject }}</p>@endif
                            <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $m->message }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs text-gray-400">{{ $m->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</p>
                            <div class="mt-2 flex items-center justify-end gap-2 text-xs">
                                <form method="POST" action="{{ route('contact.toggle-read', $m) }}">
                                    @csrf @method('PATCH')
                                    <button class="font-semibold text-gray-600 hover:text-gray-900">{{ $m->is_read ? 'Tanda belum baca' : 'Tanda dibaca' }}</button>
                                </form>
                                <span class="text-gray-300">|</span>
                                <form method="POST" action="{{ route('contact.destroy', $m) }}" onsubmit="return confirm('Padam mesej ini?')">
                                    @csrf @method('DELETE')
                                    <button class="font-semibold text-red-600 hover:text-red-700">Padam</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-gray-400">Tiada mesej lagi.</div>
            @endforelse
        </div>

        @if($messages->hasPages())
            <div class="p-4 border-t border-gray-100">{{ $messages->links() }}</div>
        @endif
    </div>
</x-app-layout>
