@props(['src' => null, 'alt' => ''])

{{--
    Image slot for the landing page.

    `src` is a path relative to public/ (e.g. "img/hero-left.jpg"). If that file
    exists it renders as an <img>; if it's missing (or no src given) it falls back
    to a labelled dashed placeholder that holds the same box. That means images can
    be dropped in one at a time without the page breaking.

        <x-img-slot class="aspect-[4/3]">Imej Kiri</x-img-slot>
        <x-img-slot class="aspect-[4/3]" src="img/hero-left.jpg" alt="Hero">Imej Kiri</x-img-slot>

    NOTE: the component name must not start with "slot" — Blade reserves <x-slot>.
--}}

@php
    $file   = $src ? public_path(ltrim($src, '/')) : null;
    $exists = $file && is_file($file);

    // Only default to object-cover when the caller hasn't chosen a fit. Merging
    // it unconditionally would win over a passed object-contain, because
    // Tailwind emits object-cover later in the stylesheet.
    $fit = str_contains((string) $attributes->get('class'), 'object-') ? '' : 'object-cover';
@endphp

@if($exists)
    <img src="{{ asset($src) }}"
         alt="{{ $alt ?: trim($slot) }}"
         loading="lazy"
         {{ $attributes->merge(['class' => trim("w-full h-full {$fit} rounded-xl")]) }}>
@else
    <div {{ $attributes->merge([
            'class' => 'w-full rounded-xl border-2 border-dashed border-brand-muted/40 bg-brand-tint/60 flex items-center justify-center text-center p-3',
        ]) }}>
        <span class="font-display text-xs sm:text-sm uppercase tracking-wide text-brand-muted">{{ $slot }}</span>
    </div>
@endif
