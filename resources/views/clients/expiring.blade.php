<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Expiring Clients') }}
            </h2>
            <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Search Bar -->
                    <div class="mb-4 flex justify-between items-center">
                        <form action="{{ route('clients.expiring') }}" method="GET" class="w-full flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <label for="perPage" class="text-sm text-gray-700">Show</label>
                                <select name="perPage" 
                                        id="perPage" 
                                        onchange="this.form.submit()"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="20" {{ request('perPage', 20) == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('perPage', 20) == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('perPage', 20) == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="text-sm text-gray-700">entries</span>
                            </div>

                            <div class="w-4/12" x-data="{ search: '{{ request('search') }}' }">
                                <div class="flex items-center">
                                    <input type="text" 
                                           name="search" 
                                           x-model="search"
                                           @input.debounce.500ms="$el.form.submit()"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                           placeholder="Search by name, phone, plate, insurance company, MyKad/SSM, or vehicle model...">
                                    @if(request('search'))
                                        <a href="{{ route('clients.expiring', ['perPage' => request('perPage', 20)]) }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Table Container with Scroll -->
                    <div class="bg-white rounded-lg shadow">
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
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Address 1</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Address 2</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">City</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">State</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Postcode</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($clients as $client)
                                            <tr>
                                                <td class="sticky left-0 bg-white px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <a href="{{ route('clients.show', $client) }}" class="text-indigo-600 hover:text-indigo-900">
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
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <!-- <a href="{{ route('clients.show', $client) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a> -->
                                                    <button type="button" onclick="openRenewModal({{ $client->client_id }})" class="text-green-600 hover:text-green-900 mr-3">Renew</button>
                                                    <a href="https://wa.me/+{{ $client->phone }}" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3">Remind</a>
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
                                                    No expiring clients found.
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

<!-- Renew Modal -->
<div id="renewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Renew Client</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to renew this client's insurance?
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="renewForm" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                        Yes, Renew
                    </button>
                </form>
                <button onclick="closeRenewModal()" class="ml-3 px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openRenewModal(clientId) {
    document.getElementById('renewModal').classList.remove('hidden');
    document.getElementById('renewForm').action = `/clients/${clientId}/renew`;
}

function closeRenewModal() {
    document.getElementById('renewModal').classList.add('hidden');
}
</script> 