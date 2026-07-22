@props(['label'])

{{-- Label + three per-column inputs (passed as the slot). --}}
<div class="grid grid-cols-[160px_repeat(3,1fr)] gap-2 items-center py-1.5">
    <div class="text-sm text-gray-700">{{ $label }}</div>
    {{ $slot }}
</div>
