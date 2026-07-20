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
    $whyCards   = [                                                                       // §4
        ['Tajuk Satu',  'images/Gambar-Why-Choose-Us-1.webp'],
        ['Tajuk Dua',   'images/Gambar-Why-Choose-Us-2.webp'],
        ['Tajuk Tiga',  'images/Gambar-Why-Choose-Us-3.webp'],
        ['Tajuk Empat', 'images/Gambar-Why-Choose-Us-4.webp'],
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
        ['Kad Satu',  'images/why-01.png'],
        ['Kad Dua',   'images/why-02.png'],
        ['Kad Tiga',  'images/why-03.png'],
        ['Kad Empat', 'images/why-04.png'],
        ['Kad Lima',  'images/why--5.png'],
    ];
    $reviews    = [                                                                       // §7
        ['Nama Pelanggan', 5, 'Ulasan pelanggan akan dipaparkan di sini.'],
        ['Nama Pelanggan', 5, 'Ulasan pelanggan akan dipaparkan di sini.'],
        ['Nama Pelanggan', 5, 'Ulasan pelanggan akan dipaparkan di sini.'],
        ['Nama Pelanggan', 5, 'Ulasan pelanggan akan dipaparkan di sini.'],
    ];
@endphp

<!-- ══ §1 MENU HEADER ═══════════════════════════════════════════════════ -->
<header x-data="{ open: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-brand-tint">
    <nav class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            <a href="#utama" class="flex items-center gap-3 shrink-0">
                @if($setting->logo_path)
                    <img src="{{ Storage::url($setting->logo_path) }}" alt="{{ $company }}" class="h-9 sm:h-11 w-auto object-contain">
                @else
                    <span class="font-display font-bold text-xl sm:text-2xl text-brand tracking-wide">NAN<span class="text-brand-ink"> SOLUTIONS</span></span>
                @endif
            </a>

            <div class="hidden lg:flex items-center gap-8 font-display text-sm font-medium uppercase tracking-wide text-brand-slate">
                <a href="#utama" class="hover:text-brand">Utama</a>
                <a href="#tentang" class="hover:text-brand">Tentang Kami</a>
                <a href="#rakan" class="hover:text-brand">Rakan Insurans</a>
                <a href="#blog" class="hover:text-brand">Blog</a>
                <a href="#hubungi" class="hover:text-brand">Hubungi Kami</a>
            </div>

            <div class="hidden lg:flex items-center gap-3">
                <a href="{{ route('pay.create') }}" class="text-sm font-semibold text-brand hover:text-brand-dark">Bayaran</a>
                <a href="{{ route('quote.create') }}"
                   class="rounded-md bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark transition">
                    Sebut Harga Percuma
                </a>
            </div>

            <button @click="open = !open" class="lg:hidden p-2 -mr-2 text-brand-slate" aria-label="Menu">
                <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div x-show="open" x-cloak @click="open = false" class="lg:hidden pb-4 space-y-1 font-display uppercase text-sm tracking-wide">
            <a href="#utama" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Utama</a>
            <a href="#tentang" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Tentang Kami</a>
            <a href="#rakan" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Rakan Insurans</a>
            <a href="#blog" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Blog</a>
            <a href="{{ route('pay.create') }}" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Bayaran</a>
            <a href="#hubungi" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Hubungi Kami</a>
            <a href="{{ route('quote.create') }}" class="block mt-2 rounded-md bg-brand px-4 py-3 text-center font-semibold text-white">Sebut Harga Percuma</a>
        </div>
    </nav>
</header>

<!-- ══ §2 HERO — row1: 6+6 images | row2: button (12) ═══════════════════ -->
<x-hero-background id="utama" class="flex items-center">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 pb-32 sm:pb-40">

        <!-- row 1 -->
        <div class="grid grid-cols-12 gap-6 items-center">

            <!-- left: 3x2 grid of image cards -->
            <div class="col-span-12 md:col-span-6">
                <div class="grid grid-cols-3 gap-3 sm:gap-4">
                    @foreach($bnplLogos as [$label, $img])
                        {{-- relative + hover:z-10 so the enlarged card lifts above its neighbours --}}
                        <div class="relative bg-white rounded-xl p-3 sm:p-4 flex items-center justify-center shadow-sm
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

        <!-- row 2 -->
        <div class="grid grid-cols-12 mt-8">
            <div class="col-span-12 flex justify-center">
                <a href="{{ route('quote.create') }}"
                   class="inline-flex items-center justify-center rounded-md bg-white px-8 py-4 text-base sm:text-lg font-bold text-brand shadow-lg hover:bg-brand-wash transition">
                    Dapatkan Sebut Harga Percuma
                </a>
            </div>
        </div>
    </div>
</x-hero-background>

<!-- ══ §3 INTRO — left: heading+subtext | right: image ══════════════════ -->
<section id="tentang" class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="col-span-12 md:col-span-6">
                <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl lg:text-4xl text-brand-ink leading-tight">
                    Tajuk Seksyen Intro
                </h2>
                <p class="mt-5 leading-relaxed text-brand-body">
                    Subteks seksyen intro diletakkan di sini. Gantikan dengan penerangan ringkas
                    mengenai perkhidmatan NAN Solutions.
                </p>
                <p class="mt-4 leading-relaxed text-brand-muted">
                    Perenggan kedua (pilihan) untuk maklumat tambahan.
                </p>
            </div>
            <div class="col-span-12 md:col-span-6">
                <x-img-slot class="aspect-[4/3]" src="img/intro.jpg">Imej Intro</x-img-slot>
            </div>
        </div>
    </div>
</section>

<!-- ══ §4 WHY — row1 heading | row2 4 cards (img top/text btm) | row3 text ══ -->
<section class="bg-brand-wash py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- row 1 -->
        <div class="grid grid-cols-12">
            <div class="col-span-12 text-center">
                <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Kenapa Pilih Kami</h2>
            </div>
        </div>

        <!-- row 2 -->
        <div class="grid grid-cols-12 gap-5 mt-10">
            @foreach($whyCards as [$card, $img])
                <div class="col-span-12 sm:col-span-6 lg:col-span-3">
                    <div class="h-full bg-white rounded-xl border border-brand-tint overflow-hidden">
                        <x-img-slot class="aspect-[16/10] rounded-none border-0" :src="$img">Imej</x-img-slot>
                        <div class="p-5">
                            <h3 class="font-display font-semibold uppercase text-brand-ink">{{ $card }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-brand-muted">
                                Teks penerangan untuk kad ini.
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- row 3 -->
        <div class="grid grid-cols-12 mt-10">
            <div class="col-span-12 text-center max-w-3xl mx-auto">
                <p class="leading-relaxed text-brand-body">
                    Teks penutup seksyen ini (12 lebar). Gantikan dengan ayat sokongan atau kesimpulan.
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
            <div class="col-span-12 flex justify-center">
                <a href="{{ route('quote.create') }}"
                   class="rounded-md bg-brand px-7 py-3.5 font-semibold text-white hover:bg-brand-dark transition">
                    Dapatkan Sebut Harga Percuma
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ══ §6 WHY 2 — heading | 5 cards (logo + text under) ═════════════════ -->
<section class="bg-brand-wash py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12">
            <div class="col-span-12 text-center">
                <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Tajuk Seksyen Enam</h2>
            </div>
        </div>

        <div class="grid grid-cols-10 gap-5 mt-10">
            @foreach($badges as [$b, $img])
                {{-- 5 across on desktop (10-col grid / 2), 2 across on mobile --}}
                <div class="col-span-5 sm:col-span-2">
                    <div class="h-full bg-white rounded-xl border border-brand-tint p-5 text-center">
                        <x-img-slot class="aspect-square max-w-24 mx-auto object-contain" :src="$img" :alt="$b">Logo</x-img-slot>
                        <p class="mt-3 text-sm font-semibold text-brand-ink">{{ $b }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ══ §7 GOOGLE REVIEWS — 4 (logo) + 8 (auto-scroll reviews) ═══════════ -->
<section class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12 gap-8 items-center">

            <div class="col-span-12 md:col-span-4 text-center md:text-left">
                <x-img-slot class="aspect-[3/2] max-w-56 mx-auto md:mx-0 object-contain" src="img/google-review.png">Google Logo</x-img-slot>
                <h2 class="mt-4 font-display font-bold uppercase text-xl text-brand-ink">Ulasan Google</h2>
                <p class="mt-2 text-sm text-brand-muted">Apa kata pelanggan kami.</p>
            </div>

            <div class="col-span-12 md:col-span-8">
                <div class="marquee overflow-hidden" style="mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent)">
                    <div class="marquee-track marquee-slow flex w-max gap-4">
                        @foreach(array_merge($reviews, $reviews) as [$name, $stars, $text])
                            <figure class="w-72 shrink-0 rounded-xl border border-brand-tint bg-white p-5">
                                <div class="flex gap-0.5 text-amber-400" aria-label="{{ $stars }} bintang">
                                    @for($i = 0; $i < $stars; $i++)
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.9c.3-.9 1.6-.9 1.9 0l1.3 4a1 1 0 00.95.7h4.2c1 0 1.4 1.2.6 1.8l-3.4 2.5a1 1 0 00-.36 1.11l1.3 4c.3.9-.75 1.66-1.53 1.1l-3.4-2.47a1 1 0 00-1.18 0l-3.4 2.47c-.78.56-1.83-.2-1.53-1.1l1.3-4a1 1 0 00-.36-1.11L2.05 9.4c-.8-.6-.4-1.8.6-1.8h4.2a1 1 0 00.95-.7l1.3-4z"/></svg>
                                    @endfor
                                </div>
                                <blockquote class="mt-3 text-sm leading-relaxed text-brand-body">{{ $text }}</blockquote>
                                <figcaption class="mt-3 text-sm font-semibold text-brand-ink">{{ $name }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ §8 SAME AS §6 — but card text replaced with a button ═════════════ -->
<section class="bg-brand-wash py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12">
            <div class="col-span-12 text-center">
                <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Tajuk Seksyen Lapan</h2>
            </div>
        </div>

        <div class="grid grid-cols-10 gap-5 mt-10">
            @foreach($badges as [$b, $img])
                <div class="col-span-5 sm:col-span-2">
                    <div class="h-full flex flex-col bg-white rounded-xl border border-brand-tint p-5 text-center">
                        <x-img-slot class="aspect-square max-w-24 mx-auto object-contain" :src="$img" :alt="$b">Logo</x-img-slot>
                        <a href="{{ route('quote.create') }}"
                           class="mt-auto pt-4 rounded-md bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">
                            Renew Now
                        </a>
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
<section id="hubungi" class="bg-brand-wash py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-12 gap-8 lg:gap-12">

            <!-- left -->
            <div class="col-span-12 md:col-span-5">
                @if($setting->logo_path)
                    <img src="{{ Storage::url($setting->logo_path) }}" alt="{{ $company }}" class="h-14 w-auto object-contain">
                @else
                    <span class="font-display font-bold text-2xl text-brand tracking-wide">NAN<span class="text-brand-ink"> SOLUTIONS</span></span>
                @endif

                <h2 class="mt-6 font-display font-bold uppercase text-2xl text-brand-ink">Hubungi Kami</h2>

                <dl class="mt-6 space-y-4 text-sm">
                    <div>
                        <dt class="font-display uppercase text-xs tracking-wide text-brand">Alamat</dt>
                        <dd class="mt-1 leading-relaxed">{{ $setting->address ?? 'Alamat penuh syarikat di sini.' }}</dd>
                    </div>
                    <div>
                        <dt class="font-display uppercase text-xs tracking-wide text-brand">Telefon</dt>
                        <dd class="mt-1"><a href="tel:+60123509257" class="font-semibold text-brand-ink hover:text-brand">012-350 9257</a></dd>
                    </div>
                    <div>
                        <dt class="font-display uppercase text-xs tracking-wide text-brand">E-mel</dt>
                        <dd class="mt-1"><a href="mailto:hello@nansolutions.com.my" class="font-semibold text-brand-ink hover:text-brand break-all">hello@nansolutions.com.my</a></dd>
                    </div>
                    <div>
                        <dt class="font-display uppercase text-xs tracking-wide text-brand">Waktu Operasi</dt>
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
                <form class="bg-white rounded-xl border border-brand-tint p-6 sm:p-8 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-brand-slate mb-1">Nama Penuh</label>
                        <input type="text" name="name"
                               class="block w-full rounded-md border-brand-tint shadow-sm focus:border-brand focus:ring-brand text-sm">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-brand-slate mb-1">E-mel</label>
                            <input type="email" name="email"
                                   class="block w-full rounded-md border-brand-tint shadow-sm focus:border-brand focus:ring-brand text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-brand-slate mb-1">No. WhatsApp</label>
                            <input type="text" name="phone" placeholder="0129622878"
                                   class="block w-full rounded-md border-brand-tint shadow-sm focus:border-brand focus:ring-brand text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-slate mb-1">Perkara</label>
                        <input type="text" name="subject"
                               class="block w-full rounded-md border-brand-tint shadow-sm focus:border-brand focus:ring-brand text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-slate mb-1">Mesej</label>
                        <textarea name="message" rows="5"
                                  class="block w-full rounded-md border-brand-tint shadow-sm focus:border-brand focus:ring-brand text-sm"></textarea>
                    </div>
                    <button type="button"
                            class="w-full rounded-md bg-brand px-6 py-3.5 font-semibold text-white hover:bg-brand-dark transition">
                        Hantar Mesej
                    </button>
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
            <span class="font-display font-bold text-xl text-white tracking-wide">NAN SOLUTIONS</span>
            <p class="mt-3 leading-relaxed max-w-sm">
                Perkhidmatan pembaharuan insurans kenderaan, cukai jalan dan takaful dengan pilihan bayaran ansuran.
            </p>
            <p class="mt-3 text-white/50 text-xs">No. Pendaftaran: 202003286749 | SA0554424-W</p>
        </div>
        <div class="col-span-6 sm:col-span-3">
            <h4 class="font-display uppercase text-white text-xs tracking-widest">Pautan</h4>
            <ul class="mt-3 space-y-2">
                <li><a href="#tentang" class="hover:text-white">Tentang Kami</a></li>
                <li><a href="#rakan" class="hover:text-white">Rakan Insurans</a></li>
                <li><a href="#blog" class="hover:text-white">Blog</a></li>
                <li><a href="#hubungi" class="hover:text-white">Hubungi Kami</a></li>
            </ul>
        </div>
        <div class="col-span-6 sm:col-span-3">
            <h4 class="font-display uppercase text-white text-xs tracking-widest">Perkhidmatan</h4>
            <ul class="mt-3 space-y-2">
                <li><a href="{{ route('quote.create') }}" class="hover:text-white">Sebut Harga</a></li>
                <li><a href="{{ route('pay.create') }}" class="hover:text-white">Bayaran</a></li>
                <li><a href="{{ route('lookup') }}" class="hover:text-white">Semak Polisi</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-xs text-white/50">
            &copy; {{ date('Y') }} {{ $company }}. Hak cipta terpelihara.
        </div>
    </div>
</footer>

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
