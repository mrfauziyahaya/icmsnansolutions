<!DOCTYPE html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NAN Solutions — Insurans &amp; Cukai Jalan</title>
    <meta name="description" content="Pembaharuan insurans kenderaan dan cukai jalan dengan pilihan bayaran ansuran. Dapatkan sebut harga percuma daripada NAN Solutions.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|oswald:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-brand-body bg-white">

@php
    $setting = \App\Models\Setting::instance();
    $company = $setting->company_name ?? 'NAN Solutions';

    // Same logo the invoice/quotation PDF uses (admin Settings upload). Verify the
    // file exists — the DB column can outlive the file — then fall back to the
    // bundled logo, then to the text wordmark, so we never show a broken image.
    $logo = $setting->logo_path && is_file(storage_path('app/public/' . $setting->logo_path))
        ? Storage::url($setting->logo_path)
        : (is_file(public_path('images/logo.png')) ? asset('images/logo.png') : null);

    // ── CONTENT — [label, image path under public/] ──────────────────────
    // Labels are placeholder copy; swap freely. Images live in public/images/.
    $bnplLogos  = [                                                                       // §2 hero
        ['SPayLater',      'images/hero-SPaylater.webp'],
        ['Grab PayLater',  'images/hero-grabpaylater.webp'],
        ['Boost',          'images/hero-Boost.webp'],
        ['Atome',          'images/hero-Atome.webp'],
        ['AhaPay',         'images/hero-unnamed.png'],
        ['Direct Lending', 'images/hero-direct_lending_holdings_logo.jpg'],
    ];
    $whyCards   = [                                                                       // §4 [tajuk, teks, imej]
        ['Kepakaran & Pengalaman', 'Berpengalaman dalam menguruskan pelbagai jenis tuntutan insurans & takaful.',      'images/Gambar-Why-Choose-Us-1.webp'],
        ['Khidmat Profesional',    'Memberikan bimbingan dan penyelesaian yang telus serta efisien.',                  'images/Gambar-Why-Choose-Us-2.webp'],
        ['Kepercayaan Pelanggan',  'Telah membantu ramai individu dan syarikat dalam urusan insurans & takaful.',      'images/Gambar-Why-Choose-Us-3.webp'],
        ['Komitmen Terbaik',       'Sentiasa memastikan pelanggan mendapat manfaat maksimum daripada perlindungan mereka.', 'images/Gambar-Why-Choose-Us-4.webp'],
    ];
    $insurers   = [                                                                       // §5
        ['Rakan Insurans 1', 'images/Logo-Insuran-1.webp'],
        ['Rakan Insurans 2', 'images/Logo-Insuran-2.webp'],
        ['Rakan Insurans 3', 'images/Logo-Insuran-3.webp'],
        ['Rakan Insurans 4', 'images/Logo-Insuran-4.webp'],
        ['Rakan Insurans 5', 'images/Logo-Insuran-5.webp'],
        ['Rakan Insurans 6', 'images/Logo-Insuran-6.webp'],
    ];
    $badges     = [                                                                       // §6 & §8
        ['Mengikut budget dan kesesuaian kos pelanggan',                     'images/why-01.png'],
        ['Perbandingan sebutharga dari pelbagai syarikat insurans & takaful', 'images/why-02.png'],
        ['Pegawai khidmat pelanggan yang mesra dan berkebolehan',            'images/why-03.png'],
        ['Membantu pelanggan dalam membuat tuntutan',                        'images/why-04.png'],
        ['Respon yang pantas dan cekap',                                     'images/why--5.png'],
    ];
    $products   = [                                                                       // §8 [nama, imej]
        ['Cukai Jalan (Roadtax)',    'images/product-01.png'],
        ['Kad Perubatan',            'images/product-02.png'],
        ['Hibah Takaful',            'images/product-03.png'],
        ['Insurans Pekerja Asing',   'images/product-04.png'],
        ['Insurans Perjalanan',      'images/product-05.png'],
        ['Takaful Haji & Umrah',     'images/product-06.png'],
        ['Insurans Kebakaran',       'images/product-07.png'],
        ['Contractor All Risk',      'images/product-08.png'],
        ['Kemalangan Diri',          'images/product-09.png'],
    ];
    // §7 [nama, bila, bintang, ulasan]
    $reviewCount = 496;
    $reviews    = [
        ['mesue bernas off...', '3 months ago', 5, 'terbaik...memang puas hati dah banyak kali buat dengan orang yang sama...sangat sangat puas hati...'],
        ['faizul Tokey',        '3 months ago', 5, 'Alhamdulillah terima kasih tuan.. jumpa iklan dekat fb ..just ws tekan link isi quotation 5minit da dapat ......'],
        ['khusaini kucai',      '3 months ago', 5, 'Mudah dan cepat proses membuat roadtax.... Sangat2 membantu.... Recommended Dan kali ke 2 buat.. Senang'],
        ['Nur Aisyah',          '4 months ago', 5, 'Cepat dan mesra. Renew roadtax dan insurans terus settle dalam masa singkat. Terima kasih NAN Solutions!'],
    ];
@endphp

<!-- ══ §2 HERO (with §1 menu header sitting on the background) ══════════ -->
<x-hero-background id="utama">

    <!-- ── §1 MENU HEADER — transparent, over the hero ── -->
    <header x-data="{ open: false }" class="relative z-20">
        <nav class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <a href="#utama" class="flex items-center gap-3 shrink-0">
                    @if($logo)
                        <img src="{{ $logo }}" alt="{{ $company }}" class="h-9 sm:h-11 w-auto object-contain">
                    @else
                        <span class="font-display font-bold text-xl sm:text-2xl text-white tracking-wide drop-shadow">NAN SOLUTIONS</span>
                    @endif
                </a>

                <div class="hidden lg:flex items-center gap-8 font-display text-sm font-medium uppercase tracking-wide text-white/90">
                    <a href="#utama" class="hover:text-white">Utama</a>
                    <a href="#tentang" class="hover:text-white">Tentang Kami</a>
                    <a href="#rakan" class="hover:text-white">Rakan Insurans</a>
                    <a href="#blog" class="hover:text-white">Blog</a>
                    <a href="#hubungi" class="hover:text-white">Hubungi Kami</a>
                </div>

                <div class="hidden lg:flex items-center gap-3">
                    <a href="{{ route('pay.create') }}" class="text-sm font-semibold text-white/90 hover:text-white">Bayaran</a>
                    <a href="{{ route('quote.create') }}"
                       class="rounded-md bg-white px-5 py-2.5 text-sm font-semibold text-[#D95A16] shadow-sm hover:bg-orange-50 transition">
                        Sebut Harga Percuma
                    </a>
                </div>

                <button @click="open = !open" class="lg:hidden p-2 -mr-2 text-white" aria-label="Menu">
                    <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="open" x-cloak @click="open = false"
                 class="lg:hidden mb-2 rounded-xl bg-black/25 backdrop-blur p-2 space-y-1 font-display uppercase text-sm tracking-wide text-white">
                <a href="#utama" class="block px-3 py-2.5 rounded hover:bg-white/15">Utama</a>
                <a href="#tentang" class="block px-3 py-2.5 rounded hover:bg-white/15">Tentang Kami</a>
                <a href="#rakan" class="block px-3 py-2.5 rounded hover:bg-white/15">Rakan Insurans</a>
                <a href="#blog" class="block px-3 py-2.5 rounded hover:bg-white/15">Blog</a>
                <a href="{{ route('pay.create') }}" class="block px-3 py-2.5 rounded hover:bg-white/15">Bayaran</a>
                <a href="#hubungi" class="block px-3 py-2.5 rounded hover:bg-white/15">Hubungi Kami</a>
                <a href="{{ route('quote.create') }}" class="block mt-2 rounded-md bg-white px-4 py-3 text-center font-semibold text-[#D95A16]">Sebut Harga Percuma</a>
            </div>
        </nav>
    </header>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 pb-32 sm:pb-40">

        <!-- row 1: headline -->
        <div class="grid grid-cols-12 mb-8 sm:mb-12">
            <div class="col-span-12 text-center">
                <h1 class="font-display font-bold uppercase text-white leading-[1.05]
                           text-4xl sm:text-6xl lg:text-7xl drop-shadow-md">
                    Renew Now,<br class="sm:hidden"> Pay Later
                </h1>
            </div>
        </div>

        <!-- row 2 -->
        <div class="grid grid-cols-12 gap-6 items-center">

            <!-- left: 3x2 grid of image cards -->
            <div class="col-span-12 md:col-span-6">
                <div class="grid grid-cols-3 gap-3 sm:gap-4">
                    @foreach($bnplLogos as [$label, $img])
                        {{-- relative + hover:z-10 so the enlarged card lifts above its neighbours --}}
                        <div class="relative bg-white rounded-lg sm:rounded-xl p-2 sm:p-4 flex items-center justify-center shadow-sm
                                    transition duration-300 ease-out hover:shadow-xl hover:z-10
                                    motion-safe:hover:scale-110">
                            <x-img-slot class="aspect-[3/2] object-contain" :src="$img" :alt="$label">{{ $label }}</x-img-slot>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- right -->
            <div class="col-span-12 md:col-span-6">
                <x-img-slot class="aspect-[4/3]" src="images/Background-Header-Image.webp" alt="NAN Solutions">Imej Kanan</x-img-slot>
            </div>
        </div>

        <!-- row 3 -->
        <div class="grid grid-cols-12 mt-8">
            <div class="col-span-12">
                <x-cta-button :href="route('quote.create')">Dapatkan Sebut Harga Percuma</x-cta-button>
            </div>
        </div>
    </div>
</x-hero-background>

<!-- ══ §3 INTRO — left: heading+subtext | right: image ══════════════════ -->
{{-- Gradient continues the hero's orange downward, lightening toward the bottom. --}}
<section id="tentang" class="py-16 sm:py-20 bg-gradient-to-b from-[#DA5813] to-[#F59A5B]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="col-span-12 md:col-span-6">
                <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl lg:text-4xl text-white leading-tight">
                    TENTANG KAMI
                </h2>
                <p class="mt-4 leading-relaxed text-white/75">
                    Nan Solutions (No. Pendaftaran: 202003286749 | SA0554424-W) telah beroperasi sejak tahun 2020 dan terus berkembang sebagai syarikat yang pakar dalam bidang tuntutan insurans & takaful. Dengan pengalaman luas, kami telah membantu ramai pelanggan mendapatkan penyelesaian terbaik dalam urusan insurans dan takaful mereka.
                </p>
            </div>
            <div class="col-span-12 md:col-span-6">
                <x-img-slot class="aspect-[4/3] object-contain" src="images/Gambar-Kereta-Payung.webp" alt="NAN Solutions">Imej Kanan</x-img-slot>
            </div>
        </div>
    </div>
</section>

<!-- ══ §4 WHY — row1 heading | row2 4 cards (img top/text btm) | row3 text ══ -->
<section class="py-14 sm:py-20 bg-gradient-to-b from-[#EFB088] via-[#F6D4BC] to-[#FDF6F1]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- row 1 -->
        <div class="grid grid-cols-12">
            <div class="col-span-12 text-center">
                <h2 class="font-display font-bold uppercase text-white leading-tight
                           text-3xl sm:text-5xl lg:text-6xl drop-shadow-sm">
                    Mengapa NAN Solutions?
                </h2>
            </div>
        </div>

        <!-- row 2 -->
        <div class="grid grid-cols-12 gap-4 sm:gap-6 mt-10 sm:mt-14">
            @foreach($whyCards as [$title, $text, $img])
                <div class="col-span-12 sm:col-span-6 lg:col-span-3">
                    <div class="h-full flex flex-col rounded-3xl overflow-hidden bg-black shadow-xl">
                        {{-- illustration sits on white --}}
                        <div class="bg-white p-3">
                            <x-img-slot class="aspect-square object-contain rounded-none" :src="$img" :alt="$title">Imej</x-img-slot>
                        </div>
                        {{-- black text panel --}}
                        <div class="flex-1 px-5 py-6 text-center">
                            <h3 class="font-display font-bold text-white text-lg sm:text-xl leading-snug">
                                {{ $title }}
                            </h3>
                            <p class="mt-4 leading-relaxed text-white/85 text-sm sm:text-base">
                                {{ $text }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- row 3 -->
        <div class="grid grid-cols-12 mt-10 sm:mt-14">
            <div class="col-span-12 text-center max-w-4xl mx-auto">
                <p class="font-semibold leading-relaxed text-[#33406B] text-base sm:text-lg">
                    Kami di NAN Solutions komited untuk membantu anda memahami hak serta manfaat insurans
                    &amp; takaful dengan lebih jelas, agar anda dapat membuat keputusan terbaik untuk
                    perlindungan masa depan.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ══ §5 INSURANCE COMPANIES — heading | auto-scroll logos | button ════ -->
<section id="rakan" class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12">
            <div class="col-span-12 text-center">
                <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Rakan Insurans Kami</h2>
            </div>
        </div>
    </div>

    <!-- row 2 — auto-scroll marquee (full-bleed) -->
    <div class="marquee mt-10 overflow-hidden" style="mask-image:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent)">
        <div class="marquee-track flex w-max gap-5">
            {{-- duplicated once for a seamless loop --}}
            @foreach(array_merge($insurers, $insurers) as [$ins, $img])
                <div class="w-40 sm:w-52 shrink-0">
                    <x-img-slot class="aspect-[3/2] bg-white object-contain p-4" :src="$img" :alt="$ins">{{ $ins }}</x-img-slot>
                </div>
            @endforeach
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12 mt-10">
            <div class="col-span-12">
                <x-cta-button :href="route('quote.create')">Dapatkan Sebut Harga Percuma</x-cta-button>
            </div>
        </div>
    </div>
</section>

<!-- ══ §6 WHY 2 — heading | 5 cards (logo + text under) ═════════════════ -->
<section class="relative overflow-hidden pt-24 sm:pt-32 pb-16 sm:pb-24
                bg-gradient-to-b from-[#E2661F] via-[#EFA079] to-white">

    {{-- decorative waves along the top edge --}}
    <div class="pointer-events-none absolute inset-x-0 top-0 z-0">
        <svg viewBox="0 0 1440 130" preserveAspectRatio="none" class="block w-full h-[70px] md:h-[110px]" aria-hidden="true">
            <path d="M0,0 L1440,0 L1440,30 C1290,86 1140,18 990,52
                     C840,86 700,26 550,58 C400,90 190,34 0,74 Z"
                  fill="#ffffff" />
            <path d="M0,0 L1440,0 L1440,10 C1300,58 1170,6 1030,30
                     C880,56 750,4 600,30 C440,58 210,12 0,44 Z"
                  fill="rgba(255,255,255,0.55)" />
        </svg>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- row 1 -->
        <div class="grid grid-cols-12">
            <div class="col-span-12 text-center">
                <h2 class="font-display font-bold uppercase text-white leading-tight
                           text-3xl sm:text-5xl lg:text-6xl drop-shadow-sm">
                    Kenapa Perlu Pilih NAN Solutions
                </h2>
            </div>
        </div>

        <!-- row 2 — 5 cards, wrapping 3 + 2 and staying centred -->
        <div class="mt-12 sm:mt-16 flex flex-wrap justify-center gap-5 sm:gap-6 max-w-5xl mx-auto">
            @foreach($badges as [$b, $img])
                <div class="w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1rem)]
                            rounded-2xl bg-white/35 px-6 py-8 text-center
                            transition duration-300 hover:bg-white/50">
                    {{-- fixed-height wrapper so every icon lines up regardless of its ratio --}}
                    <div class="h-16 sm:h-20 flex items-center justify-center">
                        <x-img-slot class="object-contain rounded-none" :src="$img" :alt="$b">Logo</x-img-slot>
                    </div>
                    <p class="mt-6 font-bold leading-snug text-[#2E3A5F] text-sm sm:text-base">{{ $b }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══ §7 GOOGLE REVIEWS — 4 (rating) + 8 (auto-scroll reviews) ═════════ -->
@php
    // Gold star, reused by the summary and each card.
    $star = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.9c.3-.9 1.6-.9 1.9 0l1.3 4a1 1 0 00.95.7h4.2c1 0 1.4 1.2.6 1.8l-3.4 2.5a1 1 0 00-.36 1.11l1.3 4c.3.9-.75 1.66-1.53 1.1l-3.4-2.47a1 1 0 00-1.18 0l-3.4 2.47c-.78.56-1.83-.2-1.53-1.1l1.3-4a1 1 0 00-.36-1.11L2.05 9.4c-.8-.6-.4-1.8.6-1.8h4.2a1 1 0 00.95-.7l1.3-4z"/></svg>';
@endphp

<section class="py-16 sm:py-20 bg-gradient-to-b from-[#FFF7F1] to-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12 gap-8 lg:gap-10 items-center">

            <!-- left: rating summary -->
            <div class="col-span-12 md:col-span-4 text-center">
                <p class="font-bold text-xl sm:text-2xl tracking-tight text-brand-ink">EXCELLENT</p>

                <div class="mt-2 flex justify-center gap-1 text-[#FBBC05]" aria-label="5 daripada 5 bintang">
                    @for($i = 0; $i < 5; $i++) {!! $star !!} @endfor
                </div>

                <p class="mt-2 text-sm text-brand-muted">
                    Based on <span class="font-bold text-brand-ink">{{ $reviewCount }} reviews</span>
                </p>

                {{-- Google wordmark --}}
                <p class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight" aria-label="Google">
                    <span class="text-[#4285F4]">G</span><span class="text-[#EA4335]">o</span><span class="text-[#FBBC05]">o</span><span class="text-[#4285F4]">g</span><span class="text-[#34A853]">l</span><span class="text-[#EA4335]">e</span>
                </p>
            </div>

            <!-- right: auto-scrolling review cards -->
            <div class="col-span-12 md:col-span-8">
                <div class="marquee overflow-hidden py-2" style="mask-image:linear-gradient(90deg,transparent,#000 5%,#000 95%,transparent)">
                    <div class="marquee-track marquee-slow flex w-max gap-4">
                        @foreach(array_merge($reviews, $reviews) as [$name, $when, $stars, $text])
                            <figure class="w-72 shrink-0 rounded-xl bg-white p-5 shadow-md ring-1 ring-black/5">
                                <div class="flex items-start gap-3">
                                    {{-- avatar: initial, until real photos are available --}}
                                    <span class="h-10 w-10 shrink-0 rounded-full bg-brand-tint text-brand-slate
                                                 flex items-center justify-center font-semibold">
                                        {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <figcaption class="font-semibold text-brand-ink truncate">{{ $name }}</figcaption>
                                        <p class="text-xs text-brand-muted">{{ $when }}</p>
                                    </div>
                                    {{-- Google "G" --}}
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 48 48" aria-hidden="true">
                                        <path fill="#4285F4" d="M45.1 24.5c0-1.6-.1-2.7-.4-3.9H24v7.1h12.1c-.2 1.8-1.6 4.5-4.5 6.3l6.9 5.3c4.1-3.8 6.6-9.3 6.6-14.8z"/>
                                        <path fill="#34A853" d="M24 46c5.9 0 10.9-2 14.5-5.3l-6.9-5.3c-1.8 1.3-4.3 2.2-7.6 2.2-5.8 0-10.8-3.8-12.5-9.1l-7.2 5.5C7.9 41.1 15.4 46 24 46z"/>
                                        <path fill="#FBBC05" d="M11.5 28.5c-.5-1.3-.7-2.8-.7-4.5s.3-3.2.7-4.5l-7.2-5.6C2.8 16.9 2 20.3 2 24s.8 7.1 2.3 10.1l7.2-5.6z"/>
                                        <path fill="#EA4335" d="M24 10.6c4.1 0 6.9 1.8 8.5 3.3l6.2-6C34.9 4.4 29.9 2 24 2 15.4 2 7.9 6.9 4.3 13.9l7.2 5.6C13.2 14.4 18.2 10.6 24 10.6z"/>
                                    </svg>
                                </div>

                                <div class="mt-3 flex items-center gap-1 text-[#FBBC05]" aria-label="{{ $stars }} bintang">
                                    @for($i = 0; $i < $stars; $i++) {!! $star !!} @endfor
                                    {{-- verified tick --}}
                                    <svg class="ml-1 h-4 w-4 text-[#4285F4]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 1.5l2 1.6 2.5-.3 1 2.3 2.3 1-.3 2.5 1.6 2-1.6 2 .3 2.5-2.3 1-1 2.3-2.5-.3-2 1.6-2-1.6-2.5.3-1-2.3-2.3-1 .3-2.5L1.5 10l1.6-2-.3-2.5 2.3-1 1-2.3 2.5.3 2-1.6zm3.6 6.1a.9.9 0 00-1.3-1.2L9 9.8 7.7 8.4a.9.9 0 10-1.3 1.2l2 2c.4.4.9.4 1.3 0l3.9-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>

                                <blockquote class="mt-3 text-sm leading-relaxed text-brand-body line-clamp-4">{{ $text }}</blockquote>
                                <p class="mt-3 text-xs font-medium text-brand-muted">Read more</p>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="grid grid-cols-12 mt-10 sm:mt-12">
            <div class="col-span-12">
                <x-cta-button :href="route('quote.create')">Dapatkan Sebut Harga Percuma</x-cta-button>
            </div>
        </div>
    </div>
</section>

<!-- ══ §8 PRODUK & PERKHIDMATAN — 9 cards, icon + 2 buttons ═════════════ -->
<section id="produk" class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- heading -->
        <div class="grid grid-cols-12">
            <div class="col-span-12 text-center">
                <h2 class="font-display font-bold uppercase leading-[1.1] text-[#8C8C8C] drop-shadow-md
                           text-2xl sm:text-5xl lg:text-6xl">
                    {{-- let it wrap naturally on phones; force the break from sm up --}}
                    Produk dan Perkhidmatan<br class="hidden sm:block">Insurans &amp; Takaful
                </h2>
            </div>
        </div>

        <!-- 9 cards, 3 across -->
        <div class="grid grid-cols-12 gap-5 sm:gap-6 mt-10 sm:mt-14">
            @foreach($products as [$name, $img])
                <div class="col-span-12 sm:col-span-6 lg:col-span-4">
                    <div class="h-full flex flex-col rounded-2xl bg-[#F7A76C] px-5 py-6 text-center shadow-md">
                        {{-- fixed-height wrapper keeps every icon on the same baseline --}}
                        <div class="h-24 flex items-center justify-center">
                            <x-img-slot class="object-contain rounded-none" :src="$img" :alt="$name">Ikon</x-img-slot>
                        </div>

                        <p class="mt-4 font-display font-bold uppercase tracking-wide text-white text-sm sm:text-base">
                            {{ $name }}
                        </p>

                        <div class="mt-auto pt-5 space-y-3">
                            <a href="{{ route('quote.create') }}"
                               class="block rounded-lg bg-[#E2661F] px-4 py-3 shadow
                                      font-display font-bold uppercase tracking-wide text-white text-sm sm:text-base
                                      transition hover:bg-[#CC5512]">
                                Renew Sekarang
                            </a>
                            <a href="{{ route('pay.create') }}"
                               class="block rounded-lg bg-[#E2661F] px-4 py-3 shadow
                                      font-display font-bold uppercase tracking-wide text-white text-sm sm:text-base
                                      transition hover:bg-[#CC5512]">
                                Pembayaran
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══ §9 BLOG — placeholder, to be implemented ═════════════════════════ -->
<section id="blog" class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12">
            <div class="col-span-12 text-center">
                <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Artikel Terkini</h2>
                <p class="mt-3 text-brand-muted">Seksyen blog akan dilaksanakan kemudian.</p>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-5 mt-10">
            @for($i = 0; $i < 3; $i++)
                <div class="col-span-12 sm:col-span-6 lg:col-span-4">
                    <div class="h-full rounded-xl border border-brand-tint overflow-hidden bg-white">
                        <x-img-slot class="aspect-[16/9] rounded-none border-0">Imej Artikel</x-img-slot>
                        <div class="p-5">
                            <p class="text-xs uppercase tracking-wide text-brand-muted">Tarikh</p>
                            <h3 class="mt-1 font-semibold text-brand-ink">Tajuk Artikel</h3>
                            <p class="mt-2 text-sm text-brand-muted">Petikan ringkas artikel.</p>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

<!-- ══ §10 CONTACT — left: info | right: form ═══════════════════════════ -->
@php
    // Shared field styling — visible border, orange focus ring.
    $field = 'block w-full rounded-md border border-gray-300 shadow-sm text-sm
              focus:border-[#E2661F] focus:ring-[#E2661F]';
@endphp

<section id="hubungi" class="bg-[#FFF7F1] py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12 gap-8 lg:gap-12">

            <!-- left -->
            <div class="col-span-12 md:col-span-5">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $company }}" class="h-14 w-auto object-contain">
                @else
                    <span class="font-display font-bold text-2xl text-[#E2661F] tracking-wide">NAN<span class="text-brand-ink"> SOLUTIONS</span></span>
                @endif

                <h2 class="mt-6 font-display font-bold uppercase text-2xl text-brand-ink">Hubungi Kami</h2>

                <dl class="mt-6 space-y-4 text-sm">
                    <div>
                        <dt class="font-display uppercase text-xs tracking-wide text-[#E2661F]">Alamat</dt>
                        <dd class="mt-1 leading-relaxed">{{ $setting->address ?? 'Alamat penuh syarikat di sini.' }}</dd>
                    </div>
                    <div>
                        <dt class="font-display uppercase text-xs tracking-wide text-[#E2661F]">Telefon</dt>
                        <dd class="mt-1"><a href="tel:+60123509257" class="font-semibold text-brand-ink hover:text-[#E2661F]">012-350 9257</a></dd>
                    </div>
                    <div>
                        <dt class="font-display uppercase text-xs tracking-wide text-[#E2661F]">E-mel</dt>
                        <dd class="mt-1"><a href="mailto:hello@nansolutions.com.my" class="font-semibold text-brand-ink hover:text-[#E2661F] break-all">hello@nansolutions.com.my</a></dd>
                    </div>
                    <div>
                        <dt class="font-display uppercase text-xs tracking-wide text-[#E2661F]">Waktu Operasi</dt>
                        <dd class="mt-1 space-y-0.5">
                            <div class="flex justify-between max-w-xs"><span>Isnin – Jumaat</span><span class="font-medium text-brand-ink">9:00 – 18:00</span></div>
                            <div class="flex justify-between max-w-xs"><span>Sabtu</span><span class="font-medium text-brand-ink">9:00 – 13:00</span></div>
                            <div class="flex justify-between max-w-xs"><span>Ahad</span><span class="text-brand-muted">Tutup</span></div>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- right — NOTE: markup only, backend not wired yet -->
            <div class="col-span-12 md:col-span-7">
                <form class="bg-white rounded-xl border border-gray-300 p-6 sm:p-8 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-brand-slate mb-1">Nama Penuh</label>
                        <input type="text" name="name" class="{{ $field }}">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-brand-slate mb-1">E-mel</label>
                            <input type="email" name="email" class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-slate mb-1">No. WhatsApp</label>
                            <input type="text" name="phone" placeholder="0129622878" class="{{ $field }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-slate mb-1">Perkara</label>
                        <input type="text" name="subject" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-slate mb-1">Mesej</label>
                        <textarea name="message" rows="5" class="{{ $field }}"></textarea>
                    </div>

                    <x-cta-button type="button">Hantar Mesej</x-cta-button>

                    <p class="text-xs italic text-brand-muted">Borang ini belum disambungkan — akan dilaksanakan bersama ciri Contact Form.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ══ FOOTER ═══════════════════════════════════════════════════════════ -->
<footer class="bg-brand-ink text-white/70">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-12 gap-8 text-sm">
        <div class="col-span-12 sm:col-span-6">
            <span class="font-display font-bold text-xl tracking-wide"><span class="text-[#F0813A]">NAN</span> <span class="text-white">SOLUTIONS</span></span>
            <p class="mt-3 leading-relaxed max-w-sm">
                Perkhidmatan pembaharuan insurans kenderaan, cukai jalan dan takaful dengan pilihan bayaran ansuran.
            </p>
            <p class="mt-3 text-white/50 text-xs">No. Pendaftaran: 202003286749 | SA0554424-W</p>
        </div>
        <div class="col-span-6 sm:col-span-3">
            <h4 class="font-display uppercase text-[#F0813A] text-xs tracking-widest">Pautan</h4>
            <ul class="mt-3 space-y-2">
                <li><a href="#tentang" class="hover:text-[#F0813A]">Tentang Kami</a></li>
                <li><a href="#rakan" class="hover:text-[#F0813A]">Rakan Insurans</a></li>
                <li><a href="#blog" class="hover:text-[#F0813A]">Blog</a></li>
                <li><a href="#hubungi" class="hover:text-[#F0813A]">Hubungi Kami</a></li>
            </ul>
        </div>
        <div class="col-span-6 sm:col-span-3">
            <h4 class="font-display uppercase text-[#F0813A] text-xs tracking-widest">Perkhidmatan</h4>
            <ul class="mt-3 space-y-2">
                <li><a href="{{ route('quote.create') }}" class="hover:text-[#F0813A]">Sebut Harga</a></li>
                <li><a href="{{ route('pay.create') }}" class="hover:text-[#F0813A]">Bayaran</a></li>
                <li><a href="{{ route('lookup') }}" class="hover:text-[#F0813A]">Semak Polisi</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-xs text-white/50">
            &copy; {{ date('Y') }} {{ $company }}. Hak cipta terpelihara.
        </div>
    </div>
</footer>

<!-- ══ Floating WhatsApp ════════════════════════════════════════════════ -->
<a href="https://wa.link/cikhnz" target="_blank" rel="noopener"
   aria-label="Hubungi kami di WhatsApp"
   class="fixed bottom-5 right-5 z-50 flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center
          rounded-full bg-[#25D366] text-white shadow-lg ring-1 ring-black/5
          transition duration-300 hover:bg-[#1EBE57] hover:shadow-xl motion-safe:hover:scale-110">
    <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M17.5 14.4c-.3-.2-1.7-.9-2-1-.3-.1-.5-.2-.7.1-.2.3-.7 1-.9 1.2-.2.2-.3.2-.6.1-.3-.2-1.2-.5-2.3-1.4-.9-.8-1.4-1.7-1.6-2-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5 0-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.2-.3-.2-.6-.4M12 2a10 10 0 00-8.6 15L2 22l5.1-1.3A10 10 0 1012 2"/>
    </svg>
</a>

<style>
    [x-cloak]{display:none!important}

    /* Auto-scroll galleries (§5 logos, §7 reviews). Track holds the list twice,
       so translating -50% loops seamlessly. */
    @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    .marquee-track { animation: marquee 40s linear infinite; }
    .marquee-slow  { animation-duration: 60s; }
    .marquee:hover .marquee-track { animation-play-state: paused; }
    @media (prefers-reduced-motion: reduce) {
        .marquee-track { animation: none; }
        .marquee { overflow-x: auto; }
    }
</style>
</body>
</html>
