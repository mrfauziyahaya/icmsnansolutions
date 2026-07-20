@props(['src' => null, 'alt' => ''])

{{--
    Image slot for the landing page.

    Pass `src` to render a real image; with no `src` it renders a labelled
    dashed placeholder so the layout holds its shape while assets are pending.
    The slot content is used as the placeholder label (and as alt text when
    `alt` isn't given).

        <x-img-slot class="aspect-[4/3]">Imej Kiri</x-img-slot>
        <x-img-slot class="aspect-[4/3]" src="{{ asset('img/hero.jpg') }}" alt="Hero" />

    NOTE: the name must not start with "slot" — Blade reserves <x-slot...> for slots.
--}}

@if($src)
    <img src="{{ $src }}"
         alt="{{ $alt ?: trim($slot) }}"
         {{ $attributes->merge(['class' => 'w-full h-full object-cover rounded-xl']) }}>
@else
    <div {{ $attributes->merge([
            'class' => 'w-full rounded-xl border-2 border-dashed border-brand-muted/40 bg-brand-tint/60 flex items-center justify-center text-center p-3',
        ]) }}>
        <span class="font-display text-xs sm:text-sm uppercase tracking-wide text-brand-muted">{{ $slot }}</span>
    </div>
@endif
