<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">WhatsApp Notification Log</h2>
    </x-slot>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-500">{{ $notifications->total() }} total notifications</p>
            <form method="GET" action="{{ route('whatsapp.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari nama, plate, telefon, jenis..."
                    class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                    Cari
                </button>
                @if(!empty($search))
                    <a href="{{ route('whatsapp.index') }}"
                       class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Date & Time</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Client</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Vehicle No.</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Client Phone</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Sent to Client</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Sent to Admin</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($notifications as $n)
                        @php
                            $adminAlso = in_array($n->type, ['expiry_30d', 'expiry_14d', 'expiry_3d', 'policy_created', 'policy_updated', 'policy_renewed']);
                            $typeLabels = [
                                'policy_created' => ['label' => 'Policy Created',  'color' => 'bg-blue-100 text-blue-700'],
                                'policy_updated' => ['label' => 'Policy Updated',  'color' => 'bg-yellow-100 text-yellow-700'],
                                'policy_renewed' => ['label' => 'Policy Renewed',  'color' => 'bg-green-100 text-green-700'],
                                'expiry_30d'     => ['label' => 'Expiry (30 days)', 'color' => 'bg-orange-100 text-orange-700'],
                                'expiry_14d'     => ['label' => 'Expiry (14 days)', 'color' => 'bg-red-100 text-red-700'],
                                'expiry_3d'      => ['label' => 'Expiry (3 days)',  'color' => 'bg-red-200 text-red-800'],
                                'payment_received' => ['label' => 'Payment Received', 'color' => 'bg-emerald-100 text-emerald-700'],
                            ];
                            $type = $typeLabels[$n->type] ?? ['label' => $n->type, 'color' => 'bg-gray-100 text-gray-700'];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                                {{ $n->sent_at?->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($n->client)
                                    <a href="{{ route('clients.show', $n->client) }}" class="font-medium text-orange-600 hover:underline">
                                        {{ $n->client->name }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-medium">
                                {{ $n->client?->plate ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $type['color'] }}">
                                    {{ $type['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $n->recipient_phone }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($n->status === 'sent')
                                    <span class="text-green-600 font-bold">&#10003;</span>
                                @else
                                    <span class="text-red-500 font-bold">&#10007;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($adminAlso)
                                    <span class="text-green-600 font-bold">&#10003;</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($n->status === 'sent')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Sent</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Failed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-red-500 max-w-xs truncate">
                                {{ $n->error ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-400">No notifications sent yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
