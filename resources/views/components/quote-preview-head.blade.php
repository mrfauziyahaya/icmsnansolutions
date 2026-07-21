@props(['span' => 4])

<tr>
    <td colspan="{{ $span }}" class="border border-gray-300 bg-orange-500 px-3 py-1.5 text-center text-xs font-bold uppercase tracking-wide text-white">
        {{ $slot }}
    </td>
</tr>
