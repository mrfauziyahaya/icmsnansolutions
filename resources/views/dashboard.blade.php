<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <!-- Quick Stats Section -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900">Quick Stats</h3>
                            <div class="mt-4 grid grid-cols-3 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="text-sm text-gray-500">Total Clients</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Client::count() }}</p>
                                </div>
                                <div class="bg-green-500 p-4 rounded-lg border border-gray-200">
                                    <p class="text-sm text-white">Active Policies</p>
                                    <p class="text-2xl font-semibold text-white">{{ \App\Models\Client::where('status', 'Active')->count() }}</p>
                                </div>
                                <div class="bg-red-500 p-4 rounded-lg border border-gray-200">
                                    <p class="text-sm text-white">Total Expiring Policies</p>
                                    <p class="text-2xl font-semibold text-white">{{ \App\Models\Client::where('status', 'Expiring')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
