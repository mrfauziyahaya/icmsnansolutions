<x-public-layout>

    <!-- Search card -->
    <div class="max-w-lg mx-auto">
        <div class="bg-white shadow rounded-xl p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">View Your Policy</h2>
            <p class="text-sm text-gray-500 mb-6">Enter your IC / Company No. to look up your insurance policy.</p>

            @if(!empty($error))
                <div class="mb-5 rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    {{ $error }}
                </div>
            @endif

            <form method="GET" action="{{ route('lookup') }}" class="space-y-4">
                <div>
                    <label for="ic" class="block text-sm font-medium text-gray-700 mb-1">IC No. / Company Registration No.</label>
                    <input type="text" id="ic" name="ic" value="{{ $ic ?? '' }}"
                        placeholder="e.g. 900101011234"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                </div>

                <div>
                    <label for="plate" class="block text-sm font-medium text-gray-700 mb-1">Vehicle Plate Number <span class="text-gray-400 font-normal">(optional — narrows results)</span></label>
                    <input type="text" id="plate" name="plate" value="{{ $plate ?? '' }}"
                        placeholder="e.g. ABC1234"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                </div>

                <button type="submit"
                    class="w-full inline-flex justify-center items-center rounded-md bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                    Search Policy
                </button>
            </form>
        </div>
    </div>

    {{-- Multiple vehicles for the same IC --}}
    @isset($clients)
    @if($clients->count() > 0)
    <div class="max-w-lg mx-auto mt-8">
        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Multiple policies found — select your vehicle:</h3>
            <ul class="divide-y divide-gray-100">
                @foreach($clients as $client)
                <li>
                    <a href="{{ route('lookup', ['ic' => $ic, 'plate' => $client->plate]) }}"
                       class="flex items-center justify-between py-4 hover:bg-orange-50 px-3 rounded-lg transition">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $client->plate }}</p>
                            <p class="text-sm text-gray-500">{{ $client->vehicle_model }} &bull; {{ $client->insurance_company }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            @if($client->status === 'Active') bg-green-100 text-green-800
                            @elseif($client->status === 'Expiring') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $client->status }}
                        </span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
    @endisset

</x-public-layout>
