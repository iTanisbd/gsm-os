<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Receive New Device (নতুন ডিভাইস গ্রহণ করুন)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('devices.store') }}">
                        @csrf

                        <!-- Select Customer -->
                        <div class="mb-6">
                            <x-input-label for="customer_id" :value="__('Select Customer *')" />
                            <select id="customer_id" name="customer_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md" required>
                                <option value="" disabled selected>-- কাস্টমার নির্বাচন করুন --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Basic Device Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
                            <div>
                                <x-input-label for="brand" :value="__('Brand (ব্র্যান্ড) *')" />
                                <x-text-input id="brand" class="block mt-1 w-full" type="text" name="brand" :value="old('brand')" required />
                            </div>
                            <div>
                                <x-input-label for="model" :value="__('Model (মডেল) *')" />
                                <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model')" required />
                            </div>
                            <div>
                                <x-input-label for="imei" :value="__('IMEI / Serial')" />
                                <x-text-input id="imei" class="block mt-1 w-full" type="text" name="imei" :value="old('imei')" />
                            </div>
                        </div>

                        <!-- Problems -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 border-b pb-4">
                            <div>
                                <x-input-label for="problem_description" :value="__('Customer Complaint (কাস্টমার কী বলেছে) *')" />
                                <textarea id="problem_description" name="problem_description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md" rows="2" required>{{ old('problem_description') }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="actual_fault" :value="__('Actual Fault (আসল সমস্যা যা আপনি পেলেন)')" />
                                <textarea id="actual_fault" name="actual_fault" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md" rows="2" placeholder="প্রাথমিক চেকআপে যা পেলেন">{{ old('actual_fault') }}</textarea>
                            </div>
                        </div>

                        <!-- Pre-repair Checklist & Location (Modern Full-width Layout) -->
                        <div class="mt-6 bg-gray-50 p-6 rounded-md border border-gray-200">

                            <!-- Pre-repair Checklist -->
                            <div class="mb-6">
                                <p class="font-bold text-gray-700 mb-4 border-b pb-2">Pre-repair Checklist (রিসিভ করার সময় অবস্থা):</p>
                                <!-- ৩ কলামের গ্রিড -->
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
                                <p class="font-bold text-gray-700 mb-4 border-b pb-2">Storage Location (কোথায় রাখলেন):</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="rack_number" :value="__('Rack Number (র‍্যাক)')" />
                                        <x-text-input id="rack_number" class="block mt-1 w-full" type="text" name="rack_number" placeholder="Ex: Rack A" />
                                    </div>
                                    <div>
                                        <x-input-label for="drawer_number" :value="__('Drawer Number (ড্রয়ার)')" />
                                        <x-text-input id="drawer_number" class="block mt-1 w-full" type="text" name="drawer_number" placeholder="Ex: Box 05" />
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('devices.index') }}">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Save Device Info') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
