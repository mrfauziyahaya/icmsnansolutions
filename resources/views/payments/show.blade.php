<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 font-mono">{{ $payment->reference }}</h2>
            <a href="{{ route('payments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
        </div>
    </x-slot>

    @php
        $badge = [
            'paid'      => 'bg-green-100 text-green-700',
            'pending'   => 'bg-yellow-100 text-yellow-700',
            'failed'    => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-100 text-gray-600',
        ];
    @endphp

    <div class="max-w-3xl space-y-6">

        <!-- Summary -->
        <div class="bg-white shadow rounded-lg p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500">Jumlah</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-900">RM {{ number_format($payment->amount, 2) }}</p>
            </div>
            <span class="self-start sm:self-auto inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $badge[$payment->status] }}">
                {{ ucfirst($payment->status) }}
            </span>
        </div>

        <!-- Payer -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-orange-600 px-5 sm:px-6 py-3"><h3 class="text-white font-semibold text-sm tracking-wide">MAKLUMAT PEMBAYAR</h3></div>
            <dl class="divide-y divide-gray-100">
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Nama</dt><dd class="text-sm text-gray-900 col-span-2">{{ $payment->payer_name }}</dd></div>
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">E-mel</dt><dd class="text-sm text-gray-900 col-span-2 break-all">{{ $payment->payer_email }}</dd></div>
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Telefon</dt><dd class="text-sm text-gray-900 col-span-2">{{ $payment->payer_phone }}</dd></div>
            </dl>
        </div>

        <!-- Payment detail -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-orange-600 px-5 sm:px-6 py-3"><h3 class="text-white font-semibold text-sm tracking-wide">BUTIRAN PEMBAYARAN</h3></div>
            <dl class="divide-y divide-gray-100">
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Bayaran Untuk</dt><dd class="text-sm text-gray-900 col-span-2">{{ $payment->purposeLabel() }}</dd></div>
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Kenderaan</dt><dd class="text-sm text-gray-900 col-span-2">{{ $payment->vehicle_plate }} ({{ $payment->vehicle_type }})</dd></div>
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Kaedah</dt><dd class="text-sm text-gray-900 col-span-2">{{ $payment->gatewayLabel() }}{{ $payment->method ? ' — ' . $payment->method : '' }}</dd></div>
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Rujukan Gateway</dt><dd class="text-sm text-gray-900 col-span-2 font-mono break-all">{{ $payment->gateway_reference ?? '—' }}</dd></div>
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Dicipta</dt><dd class="text-sm text-gray-900 col-span-2">{{ $payment->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</dd></div>
                @if($payment->paid_at)
                    <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Dibayar</dt><dd class="text-sm text-green-700 font-medium col-span-2">{{ $payment->paid_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</dd></div>
                @endif
                @if($payment->notes)
                    <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Catatan</dt><dd class="text-sm text-gray-900 col-span-2">{{ $payment->notes }}</dd></div>
                @endif
                @if($payment->failure_reason)
                    <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">Sebab Gagal</dt><dd class="text-sm text-red-600 col-span-2">{{ $payment->failure_reason }}</dd></div>
                @endif
                <div class="px-5 sm:px-6 py-3 grid grid-cols-3 gap-4"><dt class="text-sm text-gray-500">IP</dt><dd class="text-sm text-gray-500 col-span-2 font-mono">{{ $payment->ip_address ?? '—' }}</dd></div>
            </dl>
        </div>

        <!-- Raw callback, for disputes -->
        @if($payment->callback_payload)
            <details class="bg-white shadow rounded-lg overflow-hidden">
                <summary class="px-5 sm:px-6 py-3 cursor-pointer text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Payload Callback (mentah)
                </summary>
                <pre class="px-5 sm:px-6 py-4 bg-gray-900 text-gray-100 text-xs overflow-x-auto">{{ json_encode($payment->callback_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        @endif
    </div>
</x-app-layout>
