<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">Kategori Blog</h2>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 sm:grid-cols-3">
        <div class="sm:col-span-1">
            <form method="POST" action="{{ route('blog-categories.store') }}" class="bg-white shadow rounded-lg p-5 space-y-3">
                @csrf
                <label class="block text-sm font-medium text-gray-700">Kategori Baru</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="cth. Insurans Kereta"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                @error('name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                <button class="w-full rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Tambah</button>
            </form>
        </div>

        <div class="sm:col-span-2">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Artikel</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($categories as $cat)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $cat->name }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $cat->posts_count }}</td>
                                <td class="px-4 py-3 text-center">
                                    <form method="POST" action="{{ route('blog-categories.destroy', $cat) }}" class="inline"
                                          onsubmit="return confirm('Padam kategori “{{ addslashes($cat->name) }}”? Artikel kekal tanpa kategori.')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-600 hover:text-red-700">Padam</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Tiada kategori lagi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
