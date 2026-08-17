<x-blog-layout :title="$post->meta_title ?: $post->title"
               :description="$post->meta_description ?: $post->summary(25)">

    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">

        <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-[#E2661F] hover:underline">← Semua Artikel</a>

        <header class="mt-4">
            @if($post->category)
                <a href="{{ route('blog.index', ['kategori' => $post->category->slug]) }}"
                   class="text-xs font-semibold uppercase tracking-wide text-[#E2661F]">{{ $post->category->name }}</a>
            @endif
            <h1 class="mt-2 font-display font-bold text-gray-900 text-3xl sm:text-4xl leading-tight">{{ $post->title }}</h1>
            <p class="mt-3 text-sm text-gray-500">
                {{ $post->published_at?->timezone('Asia/Kuala_Lumpur')->translatedFormat('d F Y') }}
                @if($post->author) · {{ $post->author->name }} @endif
            </p>
        </header>

        @if($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}" class="mt-6 w-full rounded-xl object-cover">
        @endif

        {{-- Admin-authored HTML from Trix. --}}
        <div class="article-body trix-content mt-8">
            {!! $post->body !!}
        </div>
    </article>

    @if($related->isNotEmpty())
        <section class="bg-gray-50 border-t border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <h2 class="font-display font-bold uppercase text-xl text-gray-900 mb-6">Artikel Berkaitan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach($related as $r)
                        <a href="{{ route('blog.show', $r) }}" class="group flex flex-col rounded-xl border border-gray-200 overflow-hidden bg-white hover:shadow-lg transition">
                            <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                                @if($r->coverUrl())
                                    <img src="{{ $r->coverUrl() }}" alt="{{ $r->title }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 leading-snug group-hover:text-[#E2661F]">{{ $r->title }}</h3>
                                <p class="mt-1 text-xs text-gray-400">{{ $r->published_at?->timezone('Asia/Kuala_Lumpur')->translatedFormat('d F Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-blog-layout>
