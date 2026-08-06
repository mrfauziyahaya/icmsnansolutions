@props(['label'])

{{-- Label + one input per selected company (an Alpine x-for in the slot).
     Grid width is bound to the live column count via :style="gridStyle". --}}
<div class="grid gap-2 items-center py-1.5" :style="gridStyle">
    <div class="text-sm text-gray-700">{{ $label }}</div>
    {{ $slot }}
</div>
