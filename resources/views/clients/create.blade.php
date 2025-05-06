<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Client') }}
            </h2>
            <a href="{{ route('clients.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-12 gap-6">
                            <!-- Basic Information -->
                            <div class="col-span-12">
                                <div class="bg-white p-6 rounded-lg shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                    <div class="grid grid-cols-12 gap-6">
                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="name" :value="__('Name')" />
                                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="phone" :value="__('Phone (without -)')" />
                                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" placeholder="0123456789" />
                                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="mykad_companyno" :value="__('MyKad/Company No. (with -)')" />
                                            <x-text-input id="mykad_companyno" name="mykad_companyno" type="text" class="mt-1 block w-full" :value="old('mykad_companyno')" placeholder="000000-00-0000" />
                                            <x-input-error class="mt-2" :messages="$errors->get('mykad_companyno')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="status" :value="__('Status')" />
                                            <x-text-input id="status" name="status" type="text" class="mt-1 block w-full bg-gray-100" value="Active" readonly />
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
                                            <x-text-input id="address1" name="address1" type="text" class="mt-1 block w-full" :value="old('address1')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('address1')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="address2" :value="__('Address Line 2')" />
                                            <x-text-input id="address2" name="address2" type="text" class="mt-1 block w-full" :value="old('address2')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('address2')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <x-input-label for="city" :value="__('City')" />
                                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <x-input-label for="state" :value="__('State')" />
                                            <select id="state" name="state" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                <option value="">Select State</option>
                                                <option value="WP KUALA LUMPUR" {{ old('state') == 'WP KUALA LUMPUR' ? 'selected' : '' }}>WP KUALA LUMPUR</option>
                                                <option value="WP LABUAN" {{ old('state') == 'WP LABUAN' ? 'selected' : '' }}>WP LABUAN</option>
                                                <option value="WP PUTRAJAYA" {{ old('state') == 'WP PUTRAJAYA' ? 'selected' : '' }}>WP PUTRAJAYA</option>
                                                <option value="JOHOR" {{ old('state') == 'JOHOR' ? 'selected' : '' }}>JOHOR</option>
                                                <option value="KEDAH" {{ old('state') == 'KEDAH' ? 'selected' : '' }}>KEDAH</option>
                                                <option value="KELANTAN" {{ old('state') == 'KELANTAN' ? 'selected' : '' }}>KELANTAN</option>
                                                <option value="MELAKA" {{ old('state') == 'MELAKA' ? 'selected' : '' }}>MELAKA</option>
                                                <option value="NEGERI SEMBILAN" {{ old('state') == 'NEGERI SEMBILAN' ? 'selected' : '' }}>NEGERI SEMBILAN</option>
                                                <option value="PAHANG" {{ old('state') == 'PAHANG' ? 'selected' : '' }}>PAHANG</option>
                                                <option value="PERAK" {{ old('state') == 'PERAK' ? 'selected' : '' }}>PERAK</option>
                                                <option value="PERLIS" {{ old('state') == 'PERLIS' ? 'selected' : '' }}>PERLIS</option>
                                                <option value="PENANG" {{ old('state') == 'PENANG' ? 'selected' : '' }}>PENANG</option>
                                                <option value="SABAH" {{ old('state') == 'SABAH' ? 'selected' : '' }}>SABAH</option>
                                                <option value="SARAWAK" {{ old('state') == 'SARAWAK' ? 'selected' : '' }}>SARAWAK</option>
                                                <option value="SELANGOR" {{ old('state') == 'SELANGOR' ? 'selected' : '' }}>SELANGOR</option>
                                                <option value="TERENGGANU" {{ old('state') == 'TERENGGANU' ? 'selected' : '' }}>TERENGGANU</option>
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get('state')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <x-input-label for="postcode" :value="__('Postcode')" />
                                            <x-text-input id="postcode" name="postcode" type="text" class="mt-1 block w-full" :value="old('postcode')" />
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
                                        <div class="col-span-12 md:col-span-4">
                                            <div>
                                                <x-input-label for="category" :value="__('Category')" />
                                                <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                    <option value="">Select Category</option>
                                                    <option value="KERETA" {{ old('category') == 'KERETA' ? 'selected' : '' }}>KERETA</option>
                                                    <option value="MOTOR" {{ old('category') == 'MOTOR' ? 'selected' : '' }}>MOTOR</option>
                                                    <option value="SPIKPA" {{ old('category') == 'SPIKPA' ? 'selected' : '' }}>SPIKPA</option>
                                                    <option value="FIRE" {{ old('category') == 'FIRE' ? 'selected' : '' }}>FIRE (KEBAKARAN RUMAH & KEDAI)</option>
                                                    <option value="PERSONAL ACCIDENT" {{ old('category') == 'PERSONAL ACCIDENT' ? 'selected' : '' }}>PERSONAL ACCIDENT</option>
                                                    <option value="MEDICAL CARD" {{ old('category') == 'MEDICAL CARD' ? 'selected' : '' }}>MEDICAL CARD</option>
                                                    <option value="HIBAH TAKAFUL" {{ old('category') == 'HIBAH TAKAFUL' ? 'selected' : '' }}>HIBAH TAKAFUL</option>
                                                    <option value="TRAVEL" {{ old('category') == 'TRAVEL' ? 'selected' : '' }}>TRAVEL</option>
                                                    <option value="CONTRACTOR" {{ old('category') == 'CONTRACTOR' ? 'selected' : '' }}>CONTRACTOR</option>
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('category')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <div>
                                                <x-input-label for="insurance_company" :value="__('Insurance Company')" />
                                                <select id="insurance_company" name="insurance_company" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                    <option value="">Select Insurance Company</option>
                                                    <option value="AIA" {{ old('insurance_company') == 'AIA' ? 'selected' : '' }}>AIA General Berhad</option>
                                                    <option value="AIG" {{ old('insurance_company') == 'AIG' ? 'selected' : '' }}>AIG Malaysia Insurance Berhad</option>
                                                    <option value="ALLIANZ" {{ old('insurance_company') == 'ALLIANZ' ? 'selected' : '' }}>Allianz General Insurance Company (Malaysia) Berhad</option>
                                                    <option value="BERJAYA SOMPO" {{ old('insurance_company') == 'BERJAYA SOMPO' ? 'selected' : '' }}>Berjaya Sompo Insurance Berhad</option>
                                                    <option value="CHUBB" {{ old('insurance_company') == 'CHUBB' ? 'selected' : '' }}>Chubb Insurance Malaysia Berhad</option>
                                                    <option value="ETIQA INSURANCE" {{ old('insurance_company') == 'ETIQA INSURANCE' ? 'selected' : '' }}>Etiqa General Insurance Berhad</option>
                                                    <option value="GENERALI" {{ old('insurance_company') == 'GENERALI' ? 'selected' : '' }}>Generali Insurance Malaysia Berhad</option>
                                                    <option value="GREAT EASTERN" {{ old('insurance_company') == 'GREAT EASTERN' ? 'selected' : '' }}>Great Eastern General Insurance (Malaysia) Berhad</option>
                                                    <option value="LIBERTY GENERAL" {{ old('insurance_company') == 'LIBERTY GENERAL' ? 'selected' : '' }}>Liberty General Insurance Berhad</option>
                                                    <option value="LONPAC" {{ old('insurance_company') == 'LONPAC' ? 'selected' : '' }}>Lonpac Insurance Berhad</option>
                                                    <option value="MSIG" {{ old('insurance_company') == 'MSIG' ? 'selected' : '' }}>MSIG Insurance (Malaysia) Bhd</option>
                                                    <option value="PACIFIC ORIENT" {{ old('insurance_company') == 'PACIFIC ORIENT' ? 'selected' : '' }}>Pacific & Orient Insurance Co. Berhad</option>
                                                    <option value="PACIFIC INSURANCE" {{ old('insurance_company') == 'PACIFIC INSURANCE' ? 'selected' : '' }}>Pacific Insurance Berhad</option>
                                                    <option value="PROGRESSIVE" {{ old('insurance_company') == 'PROGRESSIVE' ? 'selected' : '' }}>Progressive Insurance Berhad</option>
                                                    <option value="QBE" {{ old('insurance_company') == 'QBE' ? 'selected' : '' }}>QBE Insurance (Malaysia) Berhad</option>
                                                    <option value="RHB" {{ old('insurance_company') == 'RHB' ? 'selected' : '' }}>RHB Insurance Berhad</option>
                                                    <option value="TOKIO MARINE" {{ old('insurance_company') == 'TOKIO MARINE' ? 'selected' : '' }}>Tokio Marine Insurance (Malaysia) Berhad</option>
                                                    <option value="TUNE" {{ old('insurance_company') == 'TUNE' ? 'selected' : '' }}>Tune Insurance Malaysia Berhad</option>
                                                    <option value="ZURICH GENERAL" {{ old('insurance_company') == 'ZURICH GENERAL' ? 'selected' : '' }}>Zurich General Insurance Malaysia Berhad</option>
                                                    <option value="STMB" {{ old('insurance_company') == 'STMB' ? 'selected' : '' }}>Syarikat Takaful Malaysia Am Berhad</option>
                                                    <option value="IKHLAS" {{ old('insurance_company') == 'IKHLAS' ? 'selected' : '' }}>Takaful Ikhlas General Berhad</option>
                                                    <option value="ZTMB" {{ old('insurance_company') == 'ZTMB' ? 'selected' : '' }}>Zurich General Takaful Malaysia Berhad</option>
                                                    <option value="EGTB" {{ old('insurance_company') == 'EGTB' ? 'selected' : '' }}>Etiqa General Takaful Berhad</option>
                                                    <option value="OTHERS" {{ old('insurance_company') == 'OTHERS' ? 'selected' : '' }}>Others</option>
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('insurance_company')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <div>
                                                <x-input-label for="nettpremium" :value="__('Nett Premium')" />
                                                <x-text-input id="nettpremium" name="nettpremium" type="number" step="0.01" class="mt-1 block w-full" :value="old('nettpremium')" onchange="calculatePremium()" />
                                                <x-input-error class="mt-2" :messages="$errors->get('nettpremium')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-4">
                                            <div>
                                                <x-input-label for="premium" :value="__('Premium')" />
                                                <x-text-input id="premium" name="premium" type="number" step="0.01" class="mt-1 block w-full bg-gray-100" readonly />
                                                <x-input-error class="mt-2" :messages="$errors->get('premium')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-3">
                                            <div>
                                                <x-input-label for="inception_date" :value="__('Inception Date')" />
                                                <x-text-input id="inception_date" name="inception_date" type="date" class="mt-1 block w-full" :value="old('inception_date')" required onchange="calculateDates()" />
                                                <x-input-error class="mt-2" :messages="$errors->get('inception_date')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-3">
                                            <div>
                                                <x-input-label for="expiry_date" :value="__('Expiry Date')" />
                                                <x-text-input id="expiry_date" name="expiry_date" type="date" class="mt-1 block w-full bg-gray-100" readonly />
                                                <x-input-error class="mt-2" :messages="$errors->get('expiry_date')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-3">
                                            <div>
                                                <x-input-label for="renewal_date" :value="__('Renewal Date')" />
                                                <x-text-input id="renewal_date" name="renewal_date" type="date" class="mt-1 block w-full bg-gray-100" readonly />
                                                <x-input-error class="mt-2" :messages="$errors->get('renewal_date')" />
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-3">
                                            <div>
                                                <x-input-label for="reminder_date" :value="__('Reminder Date')" />
                                                <x-text-input id="reminder_date" name="reminder_date" type="date" class="mt-1 block w-full bg-gray-100" readonly />
                                                <x-input-error class="mt-2" :messages="$errors->get('reminder_date')" />
                                            </div>
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
                                            <x-text-input id="plate" name="plate" type="text" class="mt-1 block w-full" :value="old('plate')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('plate')" />
                                        </div>

                                        <div class="col-span-12 md:col-span-6">
                                            <x-input-label for="vehicle_model" :value="__('Vehicle Model')" />
                                            <x-text-input id="vehicle_model" name="vehicle_model" type="text" class="mt-1 block w-full" :value="old('vehicle_model')" />
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
                                        <div>
                                            <x-input-label for="document" :value="__('Document')" />
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
                                {{ __('Create Client') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
function calculateDates() {
    const inceptionDate = document.getElementById('inception_date').value;
    if (!inceptionDate) return;

    const inception = new Date(inceptionDate);
    
    // Calculate expiry date (inception + 1 year - 1 day)
    const expiry = new Date(inception);
    expiry.setFullYear(expiry.getFullYear() + 1);
    expiry.setDate(expiry.getDate() - 1);
    
    // Calculate renewal date (inception + 1 year)
    const renewal = new Date(inception);
    renewal.setFullYear(renewal.getFullYear() + 1);
    
    // Calculate reminder date (expiry - 1 month)
    const reminder = new Date(expiry);
    reminder.setMonth(reminder.getMonth() - 1);

    // Format dates as YYYY-MM-DD
    document.getElementById('expiry_date').value = expiry.toISOString().split('T')[0];
    document.getElementById('renewal_date').value = renewal.toISOString().split('T')[0];
    document.getElementById('reminder_date').value = reminder.toISOString().split('T')[0];
}

function calculatePremium() {
    const nettpremium = parseFloat(document.getElementById('nettpremium').value) || 0;
    const premium = nettpremium + (Math.round(nettpremium * 0.08)) + 10;
    document.getElementById('premium').value = premium.toFixed(2);
}

// Calculate dates on page load if inception date is already set
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('inception_date').value) {
        calculateDates();
    }
    if (document.getElementById('nettpremium').value) {
        calculatePremium();
    }
});
</script> 