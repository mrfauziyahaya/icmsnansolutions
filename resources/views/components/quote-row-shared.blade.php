@props(['label'])

{{-- Label + a single input entered once, shown across all three columns. --}}
<div class="grid grid-cols-[160px_repeat(3,1fr)] gap-2 items-center py-1.5">
    <div class="text-sm text-gray-700">
        {{ $label }}
        <span class="ml-1 align-middle text-[10px] font-medium uppercase tracking-wide text-gray-400">(sama semua)</span>
    </div>
    <div class="col-span-3">
        {{ $slot }}
    </div>
</div>
