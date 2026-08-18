<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">Blog</h2>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-500">{{ $posts->total() }} artikel</p>
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('blog-posts.index') }}" class="flex items-center gap-2">
                    <input type="search" name="search" value="{{ $search }}" placeholder="Cari tajuk…"
                           class="w-full sm:w-64 rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    <button class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cari</button>
                </form>
                <a href="{{ route('blog-posts.create') }}" class="inline-flex items-center rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Artikel Baru</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tajuk</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Kategori</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tarikh</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($posts as $post)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $post->title }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $post->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($post->is_published && $post->published_at && $post->published_at->isPast())
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Terbit</span>
                                @elseif($post->is_published)
                                    <span class="inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Dijadual</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Draf</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ ($post->published_at ?? $post->updated_at)->timezone('Asia/Kuala_Lumpur')->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @if($post->is_published && $post->published_at && $post->published_at->isPast())
                                        <a href="{{ route('blog.show', $post) }}" target="_blank" class="text-xs font-semibold text-gray-500 hover:text-gray-800">Lihat</a>
                                        <span class="text-gray-300">|</span>
                                    @endif
                                    <a href="{{ route('blog-posts.edit', $post) }}" class="text-xs font-semibold text-orange-600 hover:text-orange-700">Edit</a>
                                    <span class="text-gray-300">|</span>
                                    <form method="POST" action="{{ route('blog-posts.destroy', $post) }}" class="inline"
                                          onsubmit="return confirm('Padam artikel “{{ addslashes($post->title) }}”?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Padam</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Tiada artikel lagi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $posts->links() }}</div>
        @endif
    </div>
</x-app-layout>
