<x-blog-layout :title="$active ? 'Blog — ' . $active->name : 'Blog'"
               description="Artikel dan tips insurans, takaful dan cukai jalan daripada NAN Solutions.">

    {{-- Title band --}}
    <div class="bg-gradient-to-b from-[#E2661F] to-[#D95A16]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <h1 class="font-display font-bold uppercase text-white text-3xl sm:text-4xl tracking-wide">Artikel Terkini</h1>
            <p class="mt-2 text-white/80">Tips &amp; panduan insurans, takaful dan cukai jalan.</p>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">

        {{-- Category filter --}}
        @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-8">
                <a href="{{ route('blog.index') }}"
                   class="rounded-full px-4 py-1.5 text-sm font-medium {{ ! $active ? 'bg-[#E2661F] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Semua
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('blog.index', ['kategori' => $cat->slug]) }}"
                       class="rounded-full px-4 py-1.5 text-sm font-medium {{ $active?->id === $cat->id ? 'bg-[#E2661F] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if($posts->isEmpty())
            <p class="text-gray-500 py-16 text-center">Tiada artikel buat masa ini.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                    <a href="{{ route('blog.show', $post) }}"
                       class="group flex flex-col rounded-xl border border-gray-200 overflow-hidden bg-white hover:shadow-lg transition">
                        <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                            @if($post->coverUrl())
                                <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}"
                                     class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="h-full w-full flex items-center justify-center text-gray-300">
                                    <svg class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 6.75h.008v.008H18V6.75z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 flex flex-col p-5">
                            @if($post->category)
                                <span class="text-xs font-semibold uppercase tracking-wide text-[#E2661F]">{{ $post->category->name }}</span>
                            @endif
                            <h2 class="mt-1 font-bold text-gray-900 leading-snug group-hover:text-[#E2661F]">{{ $post->title }}</h2>
                            <p class="mt-2 text-sm text-gray-500 line-clamp-3 flex-1">{{ $post->summary() }}</p>
                            <p class="mt-3 text-xs text-gray-400">{{ $post->published_at?->timezone('Asia/Kuala_Lumpur')->translatedFormat('d F Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    </main>
</x-blog-layout>
