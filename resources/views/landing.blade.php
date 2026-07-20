<!DOCTYPE html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NAN Solutions — Insurans & Cukai Jalan | Renew Now, Pay Later</title>
    <meta name="description" content="Pembaharuan insurans kenderaan dan cukai jalan dengan pilihan bayaran ansuran. Dapatkan sebut harga percuma daripada NAN Solutions.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|oswald:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-brand-body bg-white">

@php
    $setting = \App\Models\Setting::instance();
    $company = $setting->company_name ?? 'NAN Solutions';

    $pillars = [
        ['Kepakaran & Pengalaman', 'Beroperasi sejak 2020 dengan pengalaman luas dalam tuntutan insurans dan takaful.'],
        ['Perkhidmatan Profesional', 'Proses pembaharuan yang pantas, telus dan diuruskan sepenuhnya oleh kami.'],
        ['Kepercayaan Pelanggan', 'Ribuan pelanggan mempercayai kami untuk melindungi kenderaan mereka.'],
        ['Komitmen Terbaik', 'Kami bantu anda dari sebut harga sehingga polisi di tangan.'],
    ];

    $services = [
        ['Cukai Jalan', 'Pembaharuan roadtax kenderaan tanpa perlu beratur.'],
        ['Insurans Kenderaan', 'Perlindungan komprehensif untuk kereta dan motosikal.'],
        ['Kad Perubatan', 'Pelan perubatan untuk anda dan keluarga.'],
        ['Hibah Takaful', 'Perancangan kewangan keluarga secara patuh syariah.'],
        ['Insurans Pekerja Asing', 'Perlindungan wajib untuk pekerja asing anda.'],
        ['Insurans Perjalanan', 'Ketenangan fikiran setiap kali anda melancong.'],
        ['Takaful Haji & Umrah', 'Perlindungan khusus sepanjang ibadah anda.'],
        ['Insurans Kebakaran', 'Lindungi kediaman dan premis perniagaan anda.'],
        ['Kemalangan Diri', 'Perlindungan diri daripada risiko kemalangan.'],
    ];

    $bnpl = ['SPayLater', 'Grab PayLater', 'Boost', 'Atome', 'AhaPay', 'Direct Lending'];
@endphp

<!-- ── Nav ─────────────────────────────────────────────────────────────── -->
<header x-data="{ open: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-brand-tint">
    <nav class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            <a href="#" class="flex items-center gap-3 shrink-0">
                @if($setting->logo_path)
                    <img src="{{ Storage::url($setting->logo_path) }}" alt="{{ $company }}" class="h-9 sm:h-11 w-auto object-contain">
                @else
                    <span class="font-display font-bold text-xl sm:text-2xl text-brand tracking-wide">NAN<span class="text-brand-ink"> SOLUTIONS</span></span>
                @endif
            </a>

            <div class="hidden lg:flex items-center gap-8 font-display text-sm font-medium uppercase tracking-wide text-brand-slate">
                <a href="#utama" class="hover:text-brand">Utama</a>
                <a href="#tentang" class="hover:text-brand">Tentang Kami</a>
                <a href="#perkhidmatan" class="hover:text-brand">Perkhidmatan</a>
                <a href="{{ route('lookup') }}" class="hover:text-brand">Semak Polisi</a>
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
            <a href="#perkhidmatan" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Perkhidmatan</a>
            <a href="{{ route('lookup') }}" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Semak Polisi</a>
            <a href="{{ route('pay.create') }}" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Bayaran</a>
            <a href="#hubungi" class="block px-2 py-2.5 rounded hover:bg-brand-wash">Hubungi Kami</a>
            <a href="{{ route('quote.create') }}" class="block mt-2 rounded-md bg-brand px-4 py-3 text-center font-semibold text-white">Sebut Harga Percuma</a>
        </div>
    </nav>
</header>

<!-- ── Hero ────────────────────────────────────────────────────────────── -->
<section id="utama" class="relative overflow-hidden bg-gradient-to-br from-brand-dark via-brand to-brand-dark">
    <div class="absolute inset-0 opacity-10" aria-hidden="true"
         style="background-image:radial-gradient(circle at 20% 20%, #fff 1px, transparent 1px);background-size:32px 32px"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 lg:py-28">
        <div class="max-w-3xl">
            <p class="font-display uppercase tracking-[0.2em] text-white/70 text-xs sm:text-sm mb-4">
                Insurans &amp; Cukai Jalan
            </p>
            <h1 class="font-display font-bold text-white text-4xl sm:text-5xl lg:text-6xl leading-tight uppercase">
                Renew Now,<br class="hidden sm:block"> Pay Later
            </h1>
            <p class="mt-5 text-base sm:text-lg text-white/85 leading-relaxed max-w-2xl">
                Perbaharui insurans kenderaan dan cukai jalan anda hari ini — bayar secara ansuran
                mengikut kemampuan anda. Cepat, telus dan diuruskan sepenuhnya oleh kami.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 sm:gap-4">
                <a href="{{ route('quote.create') }}"
                   class="inline-flex items-center justify-center rounded-md bg-white px-7 py-4 text-base font-bold text-brand shadow-lg hover:bg-brand-wash transition">
                    Dapatkan Sebut Harga Percuma
                </a>
                <a href="https://wa.link/cikhnz" target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-md border-2 border-white/70 px-7 py-4 text-base font-semibold text-white hover:bg-white/10 transition">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.2-1.7-.9-2-1-.3-.1-.5-.2-.7.1-.2.3-.7 1-.9 1.2-.2.2-.3.2-.6.1-.3-.2-1.2-.5-2.3-1.4-.9-.8-1.4-1.7-1.6-2-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5 0-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.2-.3-.2-.6-.4M12 2a10 10 0 00-8.6 15L2 22l5.1-1.3A10 10 0 1012 2"/></svg>
                    WhatsApp Kami
                </a>
            </div>

            <p class="mt-8 text-xs uppercase tracking-widest text-white/60 font-display">Pilihan bayaran ansuran</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($bnpl as $b)
                    <span class="rounded-full bg-white/10 border border-white/20 px-3.5 py-1.5 text-xs sm:text-sm font-medium text-white">{{ $b }}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- ── Why choose us ───────────────────────────────────────────────────── -->
<section class="bg-brand-wash py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Kenapa Pilih Kami</h2>
            <p class="mt-3 text-brand-muted">Kami permudahkan urusan perlindungan anda dari awal hingga akhir.</p>
        </div>

        <div class="mt-10 sm:mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($pillars as $i => [$title, $desc])
                <div class="bg-white rounded-xl p-6 shadow-sm border border-brand-tint">
                    <div class="h-11 w-11 rounded-lg bg-brand/10 text-brand flex items-center justify-center font-display font-bold text-lg">
                        {{ $i + 1 }}
                    </div>
                    <h3 class="mt-4 font-semibold text-brand-ink">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-brand-muted">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ── Services ────────────────────────────────────────────────────────── -->
<section id="perkhidmatan" class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Produk &amp; Perkhidmatan</h2>
            <p class="mt-3 text-brand-muted">Satu tempat untuk semua keperluan insurans dan takaful anda.</p>
        </div>

        <div class="mt-10 sm:mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($services as [$title, $desc])
                <div class="group rounded-xl border border-brand-tint p-6 hover:border-brand hover:shadow-md transition">
                    <h3 class="font-display font-semibold uppercase tracking-wide text-brand-ink group-hover:text-brand">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-brand-muted">{{ $desc }}</p>
                    <div class="mt-5 flex items-center gap-4 text-sm font-semibold">
                        <a href="{{ route('quote.create') }}" class="text-brand hover:text-brand-dark">Sebut Harga →</a>
                        <a href="{{ route('pay.create') }}" class="text-brand-muted hover:text-brand-slate">Bayaran</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ── About ───────────────────────────────────────────────────────────── -->
<section id="tentang" class="bg-brand-wash py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
        <div>
            <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Tentang Kami</h2>
            <p class="mt-5 leading-relaxed">
                <strong class="text-brand-ink">{{ $company }}</strong> (No. Pendaftaran: 202003286749 | SA0554424-W)
                telah beroperasi sejak tahun <strong class="text-brand-ink">2020</strong>, memberikan khidmat
                pembaharuan insurans kenderaan, cukai jalan serta pelbagai produk takaful.
            </p>
            <p class="mt-4 leading-relaxed text-brand-muted">
                Kami berpengalaman menguruskan tuntutan insurans dan takaful, dan komited untuk memberikan
                penyelesaian yang mudah difahami serta mampu milik kepada setiap pelanggan.
            </p>
            <dl class="mt-8 grid grid-cols-2 gap-6 max-w-md">
                <div>
                    <dt class="font-display text-3xl font-bold text-brand">2020</dt>
                    <dd class="text-sm text-brand-muted mt-1">Mula beroperasi</dd>
                </div>
                <div>
                    <dt class="font-display text-3xl font-bold text-brand">9+</dt>
                    <dd class="text-sm text-brand-muted mt-1">Produk perlindungan</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-2xl border border-brand-tint shadow-sm p-8">
            <h3 class="font-display font-semibold uppercase text-brand-ink">Sedia untuk bermula?</h3>
            <p class="mt-3 text-sm leading-relaxed text-brand-muted">
                Hantar maklumat kenderaan anda dan kami akan hubungi anda dengan sebut harga terbaik — percuma,
                tanpa sebarang komitmen.
            </p>
            <a href="{{ route('quote.create') }}"
               class="mt-6 block rounded-md bg-brand px-6 py-4 text-center font-bold text-white hover:bg-brand-dark transition">
                Dapatkan Sebut Harga Percuma
            </a>
            <a href="{{ route('lookup') }}"
               class="mt-3 block rounded-md border border-brand-tint px-6 py-3.5 text-center font-semibold text-brand-slate hover:bg-brand-wash transition">
                Semak Status Polisi
            </a>
        </div>
    </div>
</section>

<!-- ── CTA band ────────────────────────────────────────────────────────── -->
<section class="bg-brand py-12 sm:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6 text-center sm:text-left">
        <div>
            <h2 class="font-display font-bold uppercase text-white text-xl sm:text-2xl">Cukai jalan hampir tamat tempoh?</h2>
            <p class="mt-2 text-white/80">Perbaharui hari ini dan bayar secara ansuran.</p>
        </div>
        <a href="{{ route('quote.create') }}"
           class="shrink-0 rounded-md bg-white px-7 py-4 font-bold text-brand shadow hover:bg-brand-wash transition">
            Dapatkan Sebut Harga Percuma
        </a>
    </div>
</section>

<!-- ── Contact ─────────────────────────────────────────────────────────── -->
<section id="hubungi" class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="font-display font-bold uppercase text-2xl sm:text-3xl text-brand-ink">Hubungi Kami</h2>
            <p class="mt-3 text-brand-muted">Ada pertanyaan? Kami sedia membantu.</p>
        </div>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="rounded-xl border border-brand-tint p-6">
                <h3 class="font-display font-semibold uppercase text-sm tracking-wide text-brand">Telefon</h3>
                <a href="tel:+60123509257" class="mt-2 block text-lg font-semibold text-brand-ink hover:text-brand">012-350 9257</a>
                <a href="https://wa.link/cikhnz" target="_blank" rel="noopener"
                   class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-green-600 hover:text-green-700">
                    Hubungi via WhatsApp →
                </a>
            </div>

            <div class="rounded-xl border border-brand-tint p-6">
                <h3 class="font-display font-semibold uppercase text-sm tracking-wide text-brand">E-mel</h3>
                <a href="mailto:hello@nansolutions.com.my" class="mt-2 block font-semibold text-brand-ink hover:text-brand break-all">
                    hello@nansolutions.com.my
                </a>
            </div>

            <div class="rounded-xl border border-brand-tint p-6">
                <h3 class="font-display font-semibold uppercase text-sm tracking-wide text-brand">Waktu Operasi</h3>
                <ul class="mt-2 space-y-1 text-sm">
                    <li class="flex justify-between gap-4"><span>Isnin – Jumaat</span><span class="font-medium text-brand-ink">9:00 – 18:00</span></li>
                    <li class="flex justify-between gap-4"><span>Sabtu</span><span class="font-medium text-brand-ink">9:00 – 13:00</span></li>
                    <li class="flex justify-between gap-4"><span>Ahad</span><span class="font-medium text-brand-muted">Tutup</span></li>
                </ul>
            </div>
        </div>

        @if($setting->address ?? false)
            <div class="mt-5 rounded-xl border border-brand-tint p-6">
                <h3 class="font-display font-semibold uppercase text-sm tracking-wide text-brand">Alamat</h3>
                <p class="mt-2 leading-relaxed">{{ $setting->address }}</p>
            </div>
        @endif
    </div>
</section>

<!-- ── Footer ──────────────────────────────────────────────────────────── -->
<footer class="bg-brand-ink text-white/70">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-8 text-sm">
        <div class="sm:col-span-2">
            <span class="font-display font-bold text-xl text-white tracking-wide">NAN SOLUTIONS</span>
            <p class="mt-3 leading-relaxed max-w-sm">
                Perkhidmatan pembaharuan insurans kenderaan, cukai jalan dan takaful dengan pilihan bayaran ansuran.
            </p>
            <p class="mt-3 text-white/50 text-xs">No. Pendaftaran: 202003286749 | SA0554424-W</p>
        </div>
        <div>
            <h4 class="font-display uppercase text-white text-xs tracking-widest">Pautan</h4>
            <ul class="mt-3 space-y-2">
                <li><a href="#tentang" class="hover:text-white">Tentang Kami</a></li>
                <li><a href="#perkhidmatan" class="hover:text-white">Perkhidmatan</a></li>
                <li><a href="#hubungi" class="hover:text-white">Hubungi Kami</a></li>
            </ul>
        </div>
        <div>
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

<style>[x-cloak]{display:none!important}</style>
</body>
</html>
