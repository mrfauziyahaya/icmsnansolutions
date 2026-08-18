@props(['title', 'description' => null])

@php
    $setting = \App\Models\Setting::instance();
    $company = $setting->company_name ?? 'NAN Solutions';
    $logo = $setting->logo_path && is_file(storage_path('app/public/' . $setting->logo_path))
        ? \Illuminate\Support\Facades\Storage::url($setting->logo_path)
        : (is_file(public_path('images/logo.png')) ? asset('images/logo.png') : null);
@endphp

<!DOCTYPE html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ $company }}</title>
    @if($description)<meta name="description" content="{{ $description }}">@endif
    <meta name="robots" content="index,follow">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|oswald:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Rendered post body (Trix output). */
        .article-body { color: #374151; line-height: 1.75; font-size: 1.02rem; }
        .article-body h1, .article-body h2, .article-body h3 { font-weight: 700; color: #1A202C; margin: 1.6rem 0 .6rem; line-height: 1.3; }
        .article-body h1 { font-size: 1.6rem; }
        .article-body h2 { font-size: 1.35rem; }
        .article-body h3 { font-size: 1.15rem; }
        .article-body p { margin-bottom: 1rem; }
        .article-body ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .article-body ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
        .article-body li { margin-bottom: .35rem; }
        .article-body a { color: #E2661F; font-weight: 600; text-decoration: underline; }
        .article-body strong { color: #1A202C; }
        .article-body blockquote { border-left: 3px solid #E2661F; padding-left: 1rem; color: #4A5568; font-style: italic; margin: 1rem 0; }
        .article-body img { border-radius: .5rem; margin: 1rem 0; max-width: 100%; height: auto; }
        .article-body a[href]:has(> img) { text-decoration: none; }
    </style>
</head>
<body class="font-sans antialiased text-brand-body bg-white min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="border-b border-brand-tint bg-white shrink-0">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $company }}" class="h-9 sm:h-11 w-auto object-contain">
                @else
                    <span class="font-display font-bold text-xl text-brand tracking-wide">NAN SOLUTIONS</span>
                @endif
            </a>
            <div class="flex items-center gap-4 text-sm font-semibold">
                <a href="{{ route('blog.index') }}" class="text-brand hover:text-brand-dark">Blog</a>
                <a href="{{ url('/') }}" class="text-brand hover:text-brand-dark">← Laman Utama</a>
            </div>
        </div>
    </header>

    <div class="flex-1">
        {{ $slot }}
    </div>

    {{-- Footer --}}
    <footer class="bg-brand-ink text-white/70 shrink-0">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <span>&copy; {{ date('Y') }} {{ $company }}. Hak cipta terpelihara.</span>
            <nav class="flex flex-wrap items-center gap-x-4 gap-y-1">
                <a href="{{ route('legal.privacy') }}" class="hover:text-[#F0813A]">Dasar Privasi</a>
                <a href="{{ route('legal.refund') }}" class="hover:text-[#F0813A]">Pembatalan &amp; Bayaran Balik</a>
                <a href="{{ route('legal.delivery') }}" class="hover:text-[#F0813A]">Penghantaran Perkhidmatan</a>
            </nav>
        </div>
    </footer>
</body>
</html>
