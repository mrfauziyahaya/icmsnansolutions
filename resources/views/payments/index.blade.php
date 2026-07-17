<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">Payments</h2>
    </x-slot>

    @php
        $badge = [
            'paid'      => 'bg-green-100 text-green-700',
            'pending'   => 'bg-yellow-100 text-yellow-700',
            'failed'    => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-100 text-gray-600',
        ];
    @endphp

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-xs text-gray-500">Jumlah Diterima</p>
            <p class="mt-1 text-lg sm:text-2xl font-bold text-green-600">RM {{ number_format($totals['paid'], 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-xs text-gray-500">Semua Transaksi</p>
            <p class="mt-1 text-lg sm:text-2xl font-bold text-gray-900">{{ number_format($totals['count']) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="mt-1 text-lg sm:text-2xl font-bold text-yellow-600">{{ number_format($totals['pending']) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-xs text-gray-500">Gagal</p>
            <p class="mt-1 text-lg sm:text-2xl font-bold text-red-600">{{ number_format($totals['failed']) }}</p>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Search + filter -->
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <form method="GET" action="{{ route('payments.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-2">
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Cari rujukan, nama, plate, telefon..."
                    class="w-full sm:flex-1 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                <select name="status" class="w-full sm:w-40 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                    <option value="">Semua status</option>
                    @foreach(['paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed', 'cancelled' => 'Cancelled'] as $k => $v)
                        <option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Cari</button>
                    @if($search !== '' || $status !== '')
                        <a href="{{ route('payments.index') }}" class="flex-1 sm:flex-none text-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Mobile: cards -->
        <div class="divide-y divide-gray-100 sm:hidden">
            @forelse($payments as $payment)
                <a href="{{ route('payments.show', $payment) }}" class="block p-4 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-xs text-gray-400">{{ $payment->reference }}</p>
                            <p class="font-semibold text-gray-900 truncate">{{ $payment->payer_name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $payment->payer_phone }} &middot; {{ $payment->gatewayLabel() }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-bold text-gray-900">RM {{ number_format($payment->amount, 2) }}</p>
                            <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $badge[$payment->status] }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">{{ $payment->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</p>
                </a>
            @empty
                <p class="p-10 text-center text-gray-400 text-sm">Tiada rekod pembayaran.</p>
            @endforelse
        </div>

        <!-- Desktop: table -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tarikh</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Rujukan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Telefon</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Kaedah</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Jumlah</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500">{{ $payment->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $payment->reference }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $payment->payer_name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $payment->payer_phone }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $payment->gatewayLabel() }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">RM {{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge[$payment->status] }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('payments.show', $payment) }}"
                                   class="inline-flex rounded-md bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-700">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Tiada rekod pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">{{ $payments->links() }}</div>
        @endif
    </div>
</x-app-layout>
