<x-public-layout>

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('lookup') }}" class="inline-flex items-center text-sm text-orange-600 hover:text-orange-800 font-medium">
            &larr; Search Again
        </a>
        <span class="px-3 py-1 rounded-full text-sm font-semibold
            @if($client->status === 'Active') bg-green-100 text-green-800
            @elseif($client->status === 'Expiring') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800 @endif">
            {{ $client->status }}
        </span>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

        <!-- Basic Info -->
        <div class="bg-white shadow rounded-xl p-6 space-y-4">
            <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2">Policy Holder</h3>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Name</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">IC / Reg No.</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->mykad_companyno }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->phone ?: '—' }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-gray-500">Address</dt>
                    <dd class="font-medium text-gray-900 mt-0.5 leading-relaxed">
                        {{ $client->address1 }}
                        @if($client->address2), {{ $client->address2 }}@endif<br>
                        {{ $client->postcode }} {{ $client->city }}
                        @if($client->state), {{ $client->state }}@endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Vehicle Info -->
        <div class="bg-white shadow rounded-xl p-6 space-y-4">
            <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2">Vehicle</h3>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Plate Number</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->plate }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Vehicle Model</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->vehicle_model }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Category</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->category }}</dd>
                </div>
            </dl>
        </div>

        <!-- Insurance Info -->
        <div class="bg-white shadow rounded-xl p-6 space-y-4 sm:col-span-2">
            <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2">Insurance Details</h3>
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Insurance Company</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->insurance_company }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Inception Date</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->inception_date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Expiry Date</dt>
                    <dd class="font-medium text-gray-900 mt-0.5 @if($client->status === 'Expiring') text-yellow-700 font-bold @elseif($client->status === 'Expired') text-red-700 font-bold @endif">
                        {{ $client->expiry_date?->format('d/m/Y') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Renewal Date</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->renewal_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Nett Premium</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">RM {{ number_format($client->nettpremium, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Premium</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">RM {{ number_format($client->premium, 2) }}</dd>
                </div>
                @if($client->road_tax_price)
                <div>
                    <dt class="text-gray-500">Road Tax</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">RM {{ number_format($client->road_tax_price, 2) }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Document download -->
        @if($client->document_path)
        <div class="bg-white shadow rounded-xl p-6 sm:col-span-2">
            <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">Policy Document</h3>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $client->document_name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Uploaded {{ $client->document_uploaded_at?->format('d/m/Y') ?? '' }}</p>
                </div>
                <a href="{{ asset('storage/' . $client->document_path) }}" target="_blank"
                   class="inline-flex items-center rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500">
                    Download Document
                </a>
            </div>
        </div>
        @endif

    </div>

    {{-- Expiry notice --}}
    @if($client->status === 'Expiring')
    <div class="mt-6 rounded-xl bg-yellow-50 border border-yellow-200 p-5 text-sm text-yellow-800">
        <strong>Your policy is expiring soon.</strong> Please contact us to renew your coverage before {{ $client->expiry_date?->format('d/m/Y') }}.
    </div>
    @elseif($client->status === 'Expired')
    <div class="mt-6 rounded-xl bg-red-50 border border-red-200 p-5 text-sm text-red-800">
        <strong>Your policy has expired.</strong> Please contact us immediately to renew your coverage.
    </div>
    @endif

</x-public-layout>
