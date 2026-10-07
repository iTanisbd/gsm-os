<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Receive New Device (নতুন ডিভাইস গ্রহণ করুন)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">

                    <form method="POST" action="{{ route('devices.store') }}">
                        @csrf

                        <!-- Select Customer -->
                        <div class="mb-6 p-5 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                            <x-input-label for="customer_id" class="text-gray-700 font-bold mb-2" :value="__('Select Customer *')" />
                            <select id="customer_id" name="customer_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm transition duration-150 ease-in-out" required>
                                <option value="" disabled selected>-- কাস্টমার নির্বাচন করুন --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Basic Device Info (4 Columns) -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <x-input-label for="brand" :value="__('Brand (ব্র্যান্ড) *')" />
                                <x-text-input id="brand" class="block mt-1 w-full" type="text" name="brand" :value="old('brand')" required placeholder="Ex: Samsung" />
                            </div>
                            <div>
                                <x-input-label for="model" :value="__('Model (মডেল) *')" />
                                <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model')" required placeholder="Ex: Galaxy S23" />
                            </div>
                            <div>
                                <x-input-label for="imei" :value="__('IMEI / Serial')" />
                                <x-text-input id="imei" class="block mt-1 w-full" type="text" name="imei" :value="old('imei')" placeholder="Last 4 digits or full" />
                            </div>
                            <div>
                                <x-input-label for="device_password" :value="__('Password / Pattern')" />
                                <x-text-input id="device_password" class="block mt-1 w-full font-mono text-blue-600" type="text" name="device_password" :value="old('device_password')" placeholder="Pin, Pattern, or None" />
                            </div>
                        </div>

                        <!-- Problems (Clean Layout without disturbing borders) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <x-input-label for="problem_description" class="font-bold text-red-600" :value="__('Customer Complaint (কাস্টমার কী বলেছে) *')" />
                                <textarea id="problem_description" name="problem_description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm" rows="3" required placeholder="কাস্টমারের ভাষায় সমস্যা লিখুন...">{{ old('problem_description') }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="actual_fault" class="font-bold text-indigo-600" :value="__('Actual Fault (আসল সমস্যা যা আপনি পেলেন)')" />
                                <textarea id="actual_fault" name="actual_fault" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm" rows="3" placeholder="প্রাথমিক চেকআপে আপনি কী পেলেন...">{{ old('actual_fault') }}</textarea>
                            </div>
                        </div>

                        <!-- Pre-repair Checklist & Location -->
                        <div class="mb-6 bg-gray-50 p-6 rounded-md border border-gray-200 shadow-sm">
                            <!-- Pre-repair Checklist -->
                            <div class="mb-6">
                                <p class="font-bold text-gray-700 mb-4 border-b pb-2">Pre-repair Checklist (রিসিভ করার সময় অবস্থা):</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-3 gap-x-4 text-sm text-gray-700">
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Display Scratches" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Display Scratches</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Display Broken" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Display Broken</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Back Glass Broken" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Back Glass Broken</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Camera Glass Broken" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Camera Glass Broken</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Battery Swollen" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Battery Swollen</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Dead Condition" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Dead Condition</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Water Damaged" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Water Damaged</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="No SIM Tray" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">No SIM Tray</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="SIM Card Inside" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">SIM Card Inside</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="SD Card Inside" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">SD Card Inside</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Phone Case/Cover" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Phone Case/Cover</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Charger Included" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Charger Included</span></label>
                                    <label class="inline-flex items-center"><input type="checkbox" name="pre_repair_checklist[]" value="Box with IMEI" class="rounded border-gray-300 text-indigo-600 shadow-sm"><span class="ml-2">Box with IMEI</span></label>
                                </div>
                            </div>

                            <!-- Smart Drawer Tracking -->
                            <div>
                                <p class="font-bold text-gray-700 mb-4 border-b pb-2">Storage Location (কোথায় রাখলেন):</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="rack_number" :value="__('Rack Number (র‍্যাক)')" />
                                        <x-text-input id="rack_number" class="block mt-1 w-full" type="text" name="rack_number" placeholder="Ex: Rack A" />
                                    </div>
                                    <div>
                                        <x-input-label for="drawer_number" :value="__('Drawer Number (ড্রয়ার)')" />
                                        <x-text-input id="drawer_number" class="block mt-1 w-full" type="text" name="drawer_number" placeholder="Ex: Box 05" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Delivery (Professional Touch) -->
                        <div class="mb-6 bg-indigo-50 p-5 rounded-md border border-indigo-100 flex flex-col md:flex-row items-center justify-between shadow-sm">
                            <div class="w-full md:w-1/2 mb-4 md:mb-0">
                                <label class="inline-flex items-start">
                                    <input type="checkbox" name="risk_agreement" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-700 font-medium">
                                        <strong>Risk Agreement:</strong> Customer accepts "Dead Risk" or "No Data Guarantee" during the repair process. (রিস্ক এগ্রিমেন্ট)
                                    </span>
                                </label>
                            </div>
                            <div class="w-full md:w-1/3">
                                <x-input-label for="estimated_delivery_date" class="font-bold text-gray-700" :value="__('Estimated Delivery Date')" />
                                <x-text-input id="estimated_delivery_date" class="block mt-1 w-full" type="date" name="estimated_delivery_date" :value="old('estimated_delivery_date')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-5">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md transition ease-in-out duration-150" href="{{ route('devices.index') }}">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button type="submit" class="ms-4 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-150 shadow-md">
                                {{ __('Save & Receive Device') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
