@props(['label'])

{{-- Label + a single input entered once, shown across all company columns.
     Two columns (label + one wide input) so it works for any column count. --}}
<div class="grid gap-2 items-center py-1.5" style="grid-template-columns:160px minmax(0,1fr)">
    <div class="text-sm text-gray-700">
        {{ $label }}
        <span class="ml-1 align-middle text-[10px] font-medium uppercase tracking-wide text-gray-400">(sama semua)</span>
    </div>
    <div>
        {{ $slot }}
    </div>
</div>
