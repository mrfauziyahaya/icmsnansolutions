<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Company Settings
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <!-- Logo -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Company Logo</h3>
                    <p class="mt-1 text-sm text-gray-500">Used on invoices and documents. PNG, JPG or SVG, max 2MB.</p>
                </div>
                <div class="px-6 py-5 flex items-center gap-x-6">
                    @if ($setting->logo_path)
                        <img src="{{ Storage::url($setting->logo_path) }}" alt="Company Logo" class="h-20 w-auto object-contain rounded border border-gray-200 p-1">
                    @else
                        <div class="h-20 w-32 flex items-center justify-center rounded border-2 border-dashed border-gray-300 text-gray-400 text-xs">No logo</div>
                    @endif
                    <div>
                        <input type="file" name="logo" id="logo" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                        @error('logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Company Information</h3>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 gap-y-5 sm:grid-cols-2 sm:gap-x-6">

                    <div class="sm:col-span-2">
                        <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" id="company_name"
                            value="{{ old('company_name', $setting->company_name) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="phone" id="phone"
                            value="{{ old('phone', $setting->phone) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email"
                            value="{{ old('email', $setting->email) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Address</h3>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 gap-y-5 sm:grid-cols-2 sm:gap-x-6">

                    <div class="sm:col-span-2">
                        <label for="address1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                        <input type="text" name="address1" id="address1"
                            value="{{ old('address1', $setting->address1) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        @error('address1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address2" class="block text-sm font-medium text-gray-700">Address Line 2</label>
                        <input type="text" name="address2" id="address2"
                            value="{{ old('address2', $setting->address2) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="city" id="city"
                            value="{{ old('city', $setting->city) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="postcode" class="block text-sm font-medium text-gray-700">Postcode</label>
                        <input type="text" name="postcode" id="postcode"
                            value="{{ old('postcode', $setting->postcode) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                        <select name="state" id="state"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            <option value="">-- Select State --</option>
                            @foreach (['Johor','Kedah','Kelantan','Kuala Lumpur','Labuan','Melaka','Negeri Sembilan','Pahang','Penang','Perak','Perlis','Putrajaya','Sabah','Sarawak','Selangor','Terengganu'] as $state)
                                <option value="{{ $state }}" {{ old('state', $setting->state) === $state ? 'selected' : '' }}>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pb-10">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
