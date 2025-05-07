<x-app-layout>
    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Client Details') }}
                </h2>
                
                <div class="flex space-x-2">    
                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this client? This action cannot be undone.')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Delete Client
                        </button>
                    </form>
                    <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Edit Client
                    </a>
                    <a href="{{ route('clients.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to List
                    </a>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Name</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Phone</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->phone }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">MyKad/Company No.</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->mykad_companyno }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($client->status == 'Active') bg-green-100 text-green-800
                                        @elseif($client->status == 'Expiring') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $client->status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Vehicle Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Plate Number</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->plate }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Vehicle Model</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->vehicle_model }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Insurance Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Insurance Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Category</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->category }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Insurance Company</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->insurance_company }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Nett Premium</p>
                                    <p class="text-sm font-medium text-gray-900">RM {{ number_format($client->nettpremium, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Premium</p>
                                    <p class="text-sm font-medium text-gray-900">RM {{ number_format($client->premium, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Inception Date</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->inception_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Expiry Date</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->expiry_date?->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Renewal Date</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->renewal_date?->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Reminder Date</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->reminder_date?->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Address Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Address Line 1</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->address1 }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Address Line 2</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->address2 }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">City</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->city }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">State</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->state }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Postcode</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->postcode }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Document Information -->
                        @if($client->document_path)
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Document Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Document Name</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $client->document_name }}</p>
                                </div>
                                <!-- <div>
                                    <p class="text-sm text-gray-500">Uploaded At</p>
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($client->document_uploaded_at)
                                            {{ $client->document_uploaded_at->format('d/m/Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div> -->
                                <div>
                                    <p class="text-sm text-gray-500">Download</p>
                                    <a href="{{ asset('storage/' . $client->document_path) }}" target="_blank" class="text-orange-600 hover:text-orange-900">
                                        Download Document
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 