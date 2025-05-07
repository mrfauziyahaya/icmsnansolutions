<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Client') }}
            </h2>
            <a href="{{ route('clients.show', $client) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back to Details
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('clients.update', $client) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-12 gap-6">
                            <!-- Basic Information -->
                            <div class="col-span-12">
                                <div class="bg-white p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                    <div class="grid grid-cols-12 gap-6">
                                        <div class="col-span-12 md:col-span-6">
                                        <x-input-label for="name" :value="__('Name')" />
                                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $client->name)" required autofocus />
                                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                    </div>

                                        <div class="col-span-12 md:col-span-6">
                                        <x-input-label for="phone" :value="__('Phone')" />
                                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $client->phone)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                                    </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="mykad_companyno" :value="__('MyKad/Company No. (without -)')" />
                                            <x-text-input id="mykad_companyno" name="mykad_companyno" type="text" class="mt-1 block w-full" :value="old('mykad_companyno', $client->mykad_companyno)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('mykad_companyno')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="status" :value="__('Status')" />
                                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm">
                                                <option value="Active" {{ old('status', $client->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                                <option value="Expiring" {{ old('status', $client->status) == 'Expiring' ? 'selected' : '' }}>Expiring</option>
                                                <option value="Done" {{ old('status', $client->status) == 'Done' ? 'selected' : '' }}>Done</option>
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="col-span-12">
                                <div class="bg-white p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                                    <div class="grid grid-cols-12 gap-6">
                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="address1" :value="__('Address Line 1')" />
                                            <x-text-input id="address1" name="address1" type="text" class="mt-1 block w-full" :value="old('address1', $client->address1)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('address1')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="address2" :value="__('Address Line 2')" />
                                            <x-text-input id="address2" name="address2" type="text" class="mt-1 block w-full" :value="old('address2', $client->address2)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('address2')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <x-input-label for="city" :value="__('City')" />
                                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $client->city)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <x-input-label for="state" :value="__('State')" />
                                            <select id="state" name="state" class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm">
                                            <option value="">Select State</option>
                                                <option value="WP KUALA LUMPUR" {{ old('state', $client->state) == 'WP KUALA LUMPUR' ? 'selected' : '' }}>WP KUALA LUMPUR</option>
                                                <option value="WP LABUAN" {{ old('state', $client->state) == 'WP LABUAN' ? 'selected' : '' }}>WP LABUAN</option>
                                                <option value="WP PUTRAJAYA" {{ old('state', $client->state) == 'WP PUTRAJAYA' ? 'selected' : '' }}>WP PUTRAJAYA</option>
                                                <option value="JOHOR" {{ old('state', $client->state) == 'JOHOR' ? 'selected' : '' }}>JOHOR</option>
                                                <option value="KEDAH" {{ old('state', $client->state) == 'KEDAH' ? 'selected' : '' }}>KEDAH</option>
                                                <option value="KELANTAN" {{ old('state', $client->state) == 'KELANTAN' ? 'selected' : '' }}>KELANTAN</option>
                                                <option value="MELAKA" {{ old('state', $client->state) == 'MELAKA' ? 'selected' : '' }}>MELAKA</option>
                                                <option value="NEGERI SEMBILAN" {{ old('state', $client->state) == 'NEGERI SEMBILAN' ? 'selected' : '' }}>NEGERI SEMBILAN</option>
                                                <option value="PAHANG" {{ old('state', $client->state) == 'PAHANG' ? 'selected' : '' }}>PAHANG</option>
                                                <option value="PERAK" {{ old('state', $client->state) == 'PERAK' ? 'selected' : '' }}>PERAK</option>
                                                <option value="PERLIS" {{ old('state', $client->state) == 'PERLIS' ? 'selected' : '' }}>PERLIS</option>
                                                <option value="PENANG" {{ old('state', $client->state) == 'PENANG' ? 'selected' : '' }}>PENANG</option>
                                                <option value="SABAH" {{ old('state', $client->state) == 'SABAH' ? 'selected' : '' }}>SABAH</option>
                                                <option value="SARAWAK" {{ old('state', $client->state) == 'SARAWAK' ? 'selected' : '' }}>SARAWAK</option>
                                                <option value="SELANGOR" {{ old('state', $client->state) == 'SELANGOR' ? 'selected' : '' }}>SELANGOR</option>
                                                <option value="TERENGGANU" {{ old('state', $client->state) == 'TERENGGANU' ? 'selected' : '' }}>TERENGGANU</option>
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get('state')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <x-input-label for="postcode" :value="__('Postcode')" />
                                            <x-text-input id="postcode" name="postcode" type="text" class="mt-1 block w-full" :value="old('postcode', $client->postcode)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('postcode')" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Insurance Information -->
                            <div class="col-span-12">
                                <div class="bg-white p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Insurance Information</h3>
                                    <div class="grid grid-cols-12 gap-6">
                                        <div class="col-span-12 md:col-span-6">
                                        <x-input-label for="category" :value="__('Category')" />
                                        <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm">
                                        <option value="">Select Category</option>
                                            <option value="KERETA" {{ old('category', $client->category) == 'KERETA' ? 'selected' : '' }}>KERETA</option>
                                            <option value="MOTOR" {{ old('category', $client->category) == 'MOTOR' ? 'selected' : '' }}>MOTOR</option>
                                            <option value="SPIKPA" {{ old('category', $client->category) == 'SPIKPA' ? 'selected' : '' }}>SPIKPA</option>
                                            <option value="FIRE" {{ old('category', $client->category) == 'FIRE' ? 'selected' : '' }}>FIRE (KEBAKARAN RUMAH & KEDAI)</option>
                                            <option value="PERSONAL ACCIDENT" {{ old('category', $client->category) == 'PERSONAL ACCIDENT' ? 'selected' : '' }}>PERSONAL ACCIDENT</option>
                                            <option value="MEDICAL CARD" {{ old('category', $client->category) == 'MEDICAL CARD' ? 'selected' : '' }}>MEDICAL CARD</option>
                                            <option value="HIBAH TAKAFUL" {{ old('category', $client->category) == 'HIBAH TAKAFUL' ? 'selected' : '' }}>HIBAH TAKAFUL</option>
                                            <option value="TRAVEL" {{ old('category', $client->category) == 'TRAVEL' ? 'selected' : '' }}>TRAVEL</option>
                                            <option value="CONTRACTOR" {{ old('category', $client->category) == 'CONTRACTOR' ? 'selected' : '' }}>KONTRAKTOR</option>
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('category')" />
                                    </div>

                                        <div class="col-span-12 md:col-span-6">
                                        <x-input-label for="insurance_company" :value="__('Insurance Company')" />
                                        <select id="insurance_company" name="insurance_company" class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm">
                                            <option value="">Select Insurance Company</option>
                                            <option value="AIA" {{ old('insurance_company', $client->insurance_company) == 'AIA' ? 'selected' : '' }}>AIA GENERAL BERHAD</option>
                                            <option value="AIG" {{ old('insurance_company', $client->insurance_company) == 'AIG' ? 'selected' : '' }}>AIG MALAYSIA INSURANCE BERHAD</option>
                                            <option value="ALLIANZ" {{ old('insurance_company', $client->insurance_company) == 'ALLIANZ' ? 'selected' : '' }}>ALLIANZ GENERAL INSURANCE COMPANY (MALAYSIA) BERHAD</option>
                                            <option value="BERJAYA SOMPO" {{ old('insurance_company', $client->insurance_company) == 'BERJAYA SOMPO' ? 'selected' : '' }}>BERJAYA SOMPO INSURANCE BERHAD</option>
                                            <option value="CHUBB" {{ old('insurance_company', $client->insurance_company) == 'CHUBB' ? 'selected' : '' }}>CHUBB INSURANCE MALAYSIA BERHAD</option>
                                            <option value="ETIQA INSURANCE" {{ old('insurance_company', $client->insurance_company) == 'ETIQA INSURANCE' ? 'selected' : '' }}>ETIQA GENERAL INSURANCE BERHAD</option>
                                            <option value="GENERALI" {{ old('insurance_company', $client->insurance_company) == 'GENERALI' ? 'selected' : '' }}>GENERALI INSURANCE MALAYSIA BERHAD</option>
                                            <option value="GREAT EASTERN" {{ old('insurance_company', $client->insurance_company) == 'GREAT EASTERN' ? 'selected' : '' }}>GREAT EASTERN GENERAL INSURANCE (MALAYSIA) BERHAD</option>
                                            <option value="LIBERTY GENERAL" {{ old('insurance_company', $client->insurance_company) == 'LIBERTY GENERAL' ? 'selected' : '' }}>LIBERTY GENERAL INSURANCE BERHAD</option>
                                            <option value="LONPAC" {{ old('insurance_company', $client->insurance_company) == 'LONPAC' ? 'selected' : '' }}>LONPAC INSURANCE BERHAD</option>
                                            <option value="MSIG" {{ old('insurance_company', $client->insurance_company) == 'MSIG' ? 'selected' : '' }}>MSIG INSURANCE (MALAYSIA) BHD</option>
                                            <option value="PACIFIC ORIENT" {{ old('insurance_company', $client->insurance_company) == 'PACIFIC ORIENT' ? 'selected' : '' }}>PACIFIC & ORIENT INSURANCE CO. BERHAD</option>
                                            <option value="PACIFIC INSURANCE" {{ old('insurance_company', $client->insurance_company) == 'PACIFIC INSURANCE' ? 'selected' : '' }}>PACIFIC INSURANCE BERHAD</option>
                                            <option value="PROGRESSIVE" {{ old('insurance_company', $client->insurance_company) == 'PROGRESSIVE' ? 'selected' : '' }}>PROGRESSIVE INSURANCE BERHAD</option>
                                            <option value="QBE" {{ old('insurance_company', $client->insurance_company) == 'QBE' ? 'selected' : '' }}>QBE INSURANCE (MALAYSIA) BERHAD</option>
                                            <option value="RHB" {{ old('insurance_company', $client->insurance_company) == 'RHB' ? 'selected' : '' }}>RHB INSURANCE BERHAD</option>
                                            <option value="TOKIO MARINE" {{ old('insurance_company', $client->insurance_company) == 'TOKIO MARINE' ? 'selected' : '' }}>TOKIO MARINE INSURANCE (MALAYSIA) BERHAD</option>
                                            <option value="TUNE" {{ old('insurance_company', $client->insurance_company) == 'TUNE' ? 'selected' : '' }}>TUNE INSURANCE MALAYSIA BERHAD</option>
                                            <option value="ZURICH GENERAL" {{ old('insurance_company', $client->insurance_company) == 'ZURICH GENERAL' ? 'selected' : '' }}>ZURICH GENERAL INSURANCE MALAYSIA BERHAD</option>
                                            <option value="STMB" {{ old('insurance_company', $client->insurance_company) == 'STMB' ? 'selected' : '' }}>SYARIKAT TAKAFUL MALAYSIA AM BERHAD</option>
                                            <option value="IKHLAS" {{ old('insurance_company', $client->insurance_company) == 'IKHLAS' ? 'selected' : '' }}>TAKAFUL IKHLAS GENERAL BERHAD</option>
                                            <option value="ZTMB" {{ old('insurance_company', $client->insurance_company) == 'ZTMB' ? 'selected' : '' }}>ZURICH GENERAL TAKAFUL MALAYSIA BERHAD</option>
                                            <option value="EGTB" {{ old('insurance_company', $client->insurance_company) == 'EGTB' ? 'selected' : '' }}>ETIQUA GENERAL TAKAFUL BERHAD</option>
                                            <option value="OTHERS" {{ old('insurance_company', $client->insurance_company) == 'OTHERS' ? 'selected' : '' }}>OTHERS</option>
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('insurance_company')" />
                                    </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <div>
                                                <x-input-label for="nettpremium" :value="__('Nett Premium')" />
                                                <x-text-input id="nettpremium" name="nettpremium" type="number" step="0.01" class="mt-1 block w-full" :value="old('nettpremium', $client->nettpremium)" onchange="calculatePremium()" />
                                                <x-input-error class="mt-2" :messages="$errors->get('nettpremium')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <div>
                                                <x-input-label for="premium" :value="__('Premium')" />
                                                <x-text-input id="premium" name="premium" type="number" step="0.01" class="mt-1 block w-full bg-gray-100" readonly />
                                                <x-input-error class="mt-2" :messages="$errors->get('premium')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-3">
                                        <x-input-label for="inception_date" :value="__('Inception Date')" />
                                            <x-text-input id="inception_date" name="inception_date" type="date" class="mt-1 block w-full" :value="old('inception_date', $client->inception_date?->format('Y-m-d'))" required onchange="calculateDates()" />
                                        <x-input-error class="mt-2" :messages="$errors->get('inception_date')" />
                                    </div>

                                        <div class="col-span-12 md:col-span-3">
                                        <x-input-label for="expiry_date" :value="__('Expiry Date')" />
                                            <x-text-input id="expiry_date" name="expiry_date" type="date" class="mt-1 block w-full bg-gray-100" :value="old('expiry_date', $client->expiry_date?->format('Y-m-d'))" readonly />
                                        <x-input-error class="mt-2" :messages="$errors->get('expiry_date')" />
                                    </div>

                                        <div class="col-span-12 md:col-span-3">
                                        <x-input-label for="renewal_date" :value="__('Renewal Date')" />
                                            <x-text-input id="renewal_date" name="renewal_date" type="date" class="mt-1 block w-full bg-gray-100" :value="old('renewal_date', $client->renewal_date?->format('Y-m-d'))" readonly />
                                        <x-input-error class="mt-2" :messages="$errors->get('renewal_date')" />
                                    </div>

                                        <div class="col-span-12 md:col-span-3">
                                        <x-input-label for="reminder_date" :value="__('Reminder Date')" />
                                            <x-text-input id="reminder_date" name="reminder_date" type="date" class="mt-1 block w-full bg-gray-100" :value="old('reminder_date', $client->reminder_date?->format('Y-m-d'))" readonly />
                                        <x-input-error class="mt-2" :messages="$errors->get('reminder_date')" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Information -->
                            <div class="col-span-12">
                                <div class="bg-white p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Vehicle Information</h3>
                                    <div class="grid grid-cols-12 gap-6">
                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="plate" :value="__('Plate Number')" />
                                            <x-text-input id="plate" name="plate" type="text" class="mt-1 block w-full" :value="old('plate', $client->plate)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('plate')" />
                                    </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="vehicle_model" :value="__('Vehicle Model')" />
                                            <x-text-input id="vehicle_model" name="vehicle_model" type="text" class="mt-1 block w-full" :value="old('vehicle_model', $client->vehicle_model)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('vehicle_model')" />
                                    </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Information -->
                            <div class="col-span-12">
                                <div class="bg-white p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Document Information</h3>
                                    <div class="space-y-4">
                                        @if($client->document_path)
                                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                            <p class="text-sm text-gray-500 mb-2">Current Document</p>
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $client->document_name }}</p>
                                                    <a href="{{ asset('storage/' . $client->document_path) }}" target="_blank" class="text-orange-600 hover:text-orange-900 text-sm">
                                                        View Current Document
                                                    </a>
                                                </div>
                                                <div id="delete-document-container">
                                                    <!-- Delete button will be moved here by JavaScript -->
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="p-4 bg-gray-50 rounded-lg">
                                            <x-input-label for="document" :value="__('Upload New Document')" />
                                            <x-text-input id="document" name="document" type="file" class="mt-1 block w-full" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                                            <x-input-error class="mt-2" :messages="$errors->get('document')" />
                                            <p class="mt-1 text-sm text-gray-500">
                                                Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG<br>
                                                Maximum file size: 5MB
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Update Client') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @if($client->document_path)
                    <div class="hidden">
                        <form id="delete-document-form" action="{{ route('clients.delete-document', $client) }}" method="POST">
                            @csrf
                            @method('DELETE')
                        </form>
                        <button type="button" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            onclick="if(confirm('Are you sure you want to delete this document?')) { document.getElementById('delete-document-form').submit(); }">
                            Delete Document
                        </button>
                    </div>
                    @endif

                    <script>
                        // Function to calculate dates based on inception date
                        function calculateDates() {
                            const inceptionDate = new Date(document.getElementById('inception_date').value);
                            if (!isNaN(inceptionDate.getTime())) {
                                // Calculate expiry date (1 year from inception, minus 1 day)
                                const expiryDate = new Date(inceptionDate);
                                expiryDate.setFullYear(expiryDate.getFullYear() + 1);
                                expiryDate.setDate(expiryDate.getDate() - 1);
                                document.getElementById('expiry_date').value = expiryDate.toISOString().split('T')[0];

                                // Calculate renewal date (1 year from inception)
                                const renewalDate = new Date(inceptionDate);
                                renewalDate.setFullYear(renewalDate.getFullYear() + 1);
                                document.getElementById('renewal_date').value = renewalDate.toISOString().split('T')[0];

                                // Calculate reminder date (1 month before expiry)
                                const reminderDate = new Date(expiryDate);
                                reminderDate.setMonth(reminderDate.getMonth() - 1);
                                document.getElementById('reminder_date').value = reminderDate.toISOString().split('T')[0];
                            }
                        }

                        // Move delete button to the correct container
                        document.addEventListener('DOMContentLoaded', function() {
                            const deleteButton = document.createElement('form');
                            deleteButton.action = "{{ route('clients.delete-document', $client) }}";
                            deleteButton.method = 'POST';
                            deleteButton.innerHTML = `
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('Are you sure you want to delete this document?')">
                                    Delete Document
                                </button>
                            `;
                            document.getElementById('delete-document-container').appendChild(deleteButton);
                        });

                        function calculatePremium() {
                            const nettpremium = parseFloat(document.getElementById('nettpremium').value) || 0;
                            const premium = nettpremium + (Math.round(nettpremium * 0.08)) + 10;
                            document.getElementById('premium').value = premium.toFixed(2);
                        }

                        // Calculate premium on page load if nettpremium exists
                        document.addEventListener('DOMContentLoaded', function() {
                            if (document.getElementById('nettpremium').value) {
                                calculatePremium();
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 