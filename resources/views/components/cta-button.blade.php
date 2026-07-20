@props(['href' => null, 'type' => 'submit'])

{{--
    Full-width orange call-to-action used across the landing page.
    Renders an <a> when given an href, otherwise a <button> (for forms).

        <x-cta-button :href="route('quote.create')">Dapatkan Sebut Harga Percuma</x-cta-button>
        <x-cta-button type="submit">Hantar Mesej</x-cta-button>
--}}

@php
    $classes = 'block w-full rounded-xl border-2 border-orange-300/60
                bg-gradient-to-b from-[#E9701F] to-[#D95A16]
                px-4 sm:px-6 py-4 sm:py-5 text-center
                font-display text-base sm:text-xl md:text-2xl font-bold uppercase tracking-wide text-white
                shadow-lg transition duration-300
                hover:from-[#F27C28] hover:to-[#E2651B] hover:shadow-xl';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
