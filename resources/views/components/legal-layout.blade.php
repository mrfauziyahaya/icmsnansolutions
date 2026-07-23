@props(['title'])

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
    <meta name="robots" content="index,follow">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|oswald:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .legal h2 { font-family: 'Oswald', sans-serif; text-transform: uppercase; letter-spacing: .02em; color: #1A202C; font-weight: 700; font-size: 1.25rem; margin-top: 2rem; margin-bottom: .75rem; }
        .legal h3 { font-weight: 700; color: #2D3748; margin-top: 1.5rem; margin-bottom: .5rem; text-transform: uppercase; font-size: .95rem; letter-spacing: .02em; }
        .legal p  { color: #4A5568; line-height: 1.7; margin-bottom: 1rem; }
        .legal ul { list-style: disc; padding-left: 1.4rem; margin-bottom: 1rem; color: #4A5568; }
        .legal ul li { margin-bottom: .4rem; line-height: 1.6; }
        .legal a  { color: #E2661F; font-weight: 600; }
        .legal strong { color: #1A202C; }
    </style>
</head>
<body class="font-sans antialiased text-brand-body bg-white">

    {{-- Header --}}
    <header class="border-b border-brand-tint bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $company }}" class="h-9 sm:h-11 w-auto object-contain">
                @else
                    <span class="font-display font-bold text-xl text-brand tracking-wide">NAN SOLUTIONS</span>
                @endif
            </a>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-brand hover:text-brand-dark">← Laman Utama</a>
        </div>
    </header>

    {{-- Title band --}}
    <div class="bg-gradient-to-b from-[#E2661F] to-[#D95A16]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <h1 class="font-display font-bold uppercase text-white text-3xl sm:text-4xl tracking-wide">{{ $title }}</h1>
        </div>
    </div>

    {{-- Content --}}
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="legal text-[15px]">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-brand-ink text-white/70">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-sm flex flex-col sm:flex-row items-center justify-between gap-4">
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
