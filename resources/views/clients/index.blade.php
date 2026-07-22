<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Clients') }}
            </h2>
            <a href="{{ route('clients.create') }}" class="inline-flex justify-center items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Add New Client
            </a>
        </div>
    </x-slot>

    <style>
        .container {
            overflow-x: auto;
        }

        tr>th:first-child,tr>td:first-child {
            position: sticky;
            left: 0;
            border-right: 1px solid #e5e7eb;
            background-color: #f3f4f6;
            z-index: 1;
        }

        tr:nth-child(odd) td {
            background: white;
        }

        tr:nth-child(even) td {
            background: #f3f4f6;
        }
    </style>

    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Search Bar -->
                    <div class="mb-4">
                        <form action="{{ route('clients.index') }}" method="GET" class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                            <div class="flex items-center space-x-2 shrink-0">
                                <label for="perPage" class="text-sm text-gray-700">Show</label>
                                <select name="perPage"
                                        id="perPage"
                                        onchange="this.form.submit()"
                                        class="rounded-md border-gray-300 shadow-sm text-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                                    <option value="20" {{ request('perPage', 20) == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('perPage', 20) == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('perPage', 20) == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="text-sm text-gray-700">entries</span>
                            </div>

                            <!-- Search Bar -->
                            <div class="w-full sm:w-5/12" x-data="{ search: '{{ request('search') }}' }">
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                           name="search"
                                           x-model="search"
                                           @input.debounce.500ms="$el.form.submit()"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                                           placeholder="Search name, phone, plate, insurer...">
                                    @if(request('search'))
                                        <a href="{{ route('clients.index', ['perPage' => request('perPage', 20)]) }}" class="shrink-0 inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Clear
                                        </a>
                                    @endif
                                    <a href="{{ route('clients.download') }}" title="Download CSV"
                                       class="shrink-0 inline-flex items-center px-2 py-2 bg-gray-600 border border-transparent rounded-md text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Mobile: cards. The full table has 18 columns and is unusable on a phone. -->
                    <div class="sm:hidden divide-y divide-gray-100 border-t border-gray-100">
                        @forelse ($clients as $client)
                            <a href="{{ route('clients.show', $client) }}" class="block py-3 hover:bg-gray-50">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-orange-600 truncate">{{ $client->name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $client->plate }} &middot; {{ $client->vehicle_model }}</p>
                                        <p class="text-xs text-gray-500">{{ $client->phone }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        @php
                                            $statusColor = match ($client->status) {
                                                'Active'   => 'bg-green-100 text-green-700',
                                                'Expiring' => 'bg-yellow-100 text-yellow-700',
                                                'Expired'  => 'bg-red-100 text-red-700',
                                                default    => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColor }}">{{ $client->status }}</span>
                                        <p class="text-xs text-gray-500 mt-1">Tamat: {{ $client->expiry_date?->format('d/m/Y') ?? '—' }}</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1 truncate">{{ $client->insurance_company }}</p>
                            </a>
                        @empty
                            <p class="py-10 text-center text-gray-400 text-sm">No clients found.</p>
                        @endforelse
                    </div>

                    <!-- Desktop: full table, scrolls horizontally -->
                    <div class="hidden sm:block bg-white rounded-lg shadow">
                        <div class="w-full">
                            <div class=" overflow-hidden border-b border-gray-200 sm:rounded-lg overflow-x-auto relative">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="sticky left-0 bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">My Kad No. / SSM</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Phone</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Category</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Plate</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vehicle Model</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Insurance Company</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nett Premium</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Premium</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Inception Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Expiry Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Renewal Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Reminder Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Address 1</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Address 2</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">City</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">State</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Postcode</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Attachment</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($clients as $client)
                                            <tr>
                                                <td class="sticky left-0 bg-white px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <a href="{{ route('clients.show', $client) }}" class="text-orange-600 hover:text-orange-900">
                                                        {{ $client->name }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->mykad_companyno }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->phone }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->category }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->plate }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->vehicle_model }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->insurance_company }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    RM {{ number_format($client->nettpremium, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    RM {{ number_format($client->premium, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->inception_date?->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->expiry_date?->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->renewal_date?->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->reminder_date?->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->address1 }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->address2 }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->city }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->state }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    {{ $client->postcode }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $client->status == 'Active' ? 'bg-green-100 text-green-800' : 
                                                           ($client->status == 'Expiring' ? 'bg-yellow-100 text-yellow-800' : 
                                                           'bg-red-100 text-red-800') }}">
                                                        {{ $client->status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    @if($client->document_path)
                                                        <a href="{{ Storage::url($client->document_path) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                            File
                                                        </a>
                                                    @else
                                                        {{ $client->document_name }}
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('clients.show', $client) }}" class="text-orange-600 hover:text-orange-900 mr-3">View</a>
                                                    <a href="{{ route('clients.edit', $client) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this client?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="18" class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    No clients found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


</x-app-layout> 