<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Device (ডিভাইস এডিট করুন)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('devices.update', $device->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Device Status (কাজের বর্তমান অবস্থা) -->
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <x-input-label for="status" :value="__('Device Status (কাজের বর্তমান অবস্থা) *')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm font-bold text-lg" required>
                                <option value="pending" {{ old('status', $device->status) == 'pending' ? 'selected' : '' }}>Pending (পেন্ডিং)</option>
                                <option value="processing" {{ old('status', $device->status) == 'processing' ? 'selected' : '' }}>Processing (কাজ চলছে)</option>
                                <option value="completed" {{ old('status', $device->status) == 'completed' ? 'selected' : '' }}>Completed (কাজ শেষ)</option>
                                <option value="delivered" {{ old('status', $device->status) == 'delivered' ? 'selected' : '' }}>Delivered (ডেলিভারি সম্পন্ন)</option>
                            </select>
                        </div>

                        <!-- Select Customer -->
                        <!-- Select Customer -->
                        <div class="mb-6">
                            <x-input-label for="customer_id" :value="__('Customer')" />
                            <!-- Select ট্যাগটিকে disabled করা হলো যাতে ক্লিক করা না যায় -->
                            <select id="customer_id" class="block mt-1 w-full bg-gray-100 border-gray-300 rounded-md" disabled>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $device->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <!-- ফর্ম সাবমিট করার সময় ডাটা পাঠানোর জন্য একটি hidden ইনপুট ফিল্ড -->
                            <input type="hidden" name="customer_id" value="{{ $device->customer_id }}">
                            <p class="text-xs text-red-500 mt-1">একবার রিসিভ হওয়ার পর কাস্টমার পরিবর্তন করা যাবে না।</p>
                        </div>

                        <!-- Basic Device Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
                            <div>
                                <x-input-label for="brand" :value="__('Brand (ব্র্যান্ড) *')" />
                                <x-text-input id="brand" class="block mt-1 w-full" type="text" name="brand" :value="old('brand', $device->brand)" required />
                            </div>
                            <div>
                                <x-input-label for="model" :value="__('Model (মডেল) *')" />
                                <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model', $device->model)" required />
                            </div>
                            <div>
                                <x-input-label for="imei" :value="__('IMEI / Serial')" />
                                <x-text-input id="imei" class="block mt-1 w-full" type="text" name="imei" :value="old('imei', $device->imei)" />
                            </div>
                        </div>

                        <!-- Problems -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 border-b pb-4">
                            <div>
                                <x-input-label for="problem_description" :value="__('Customer Complaint (কাস্টমার কী বলেছে) *')" />
                                <textarea id="problem_description" name="problem_description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md bg-gray-100" rows="2" readonly>{{ old('problem_description', $device->problem_description) }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="actual_fault" :value="__('Actual Fault (আসল সমস্যা যা আপনি পেলেন)')" />
                                <textarea id="actual_fault" name="actual_fault" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md" rows="2">{{ old('actual_fault', $device->actual_fault) }}</textarea>
                            </div>
                        </div>

                        <!-- Pre-repair Checklist & Location (Modern Full-width Layout) -->
                        <div class="mt-6 bg-gray-50 p-6 rounded-md border border-gray-200">

                            <!-- Pre-repair Checklist (Read-only on edit) -->
                            <div class="mb-6">
                                <p class="font-bold text-gray-700 mb-4 border-b pb-2">Pre-repair Checklist (রিসিভ করার সময় অবস্থা):</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-3 gap-x-4 text-sm text-gray-500">
                                    @php
                                        $checklist = is_array($device->pre_repair_checklist) ? $device->pre_repair_checklist : [];
                                        $options = [
                                            'Display Scratches', 'Display Broken', 'Back Glass Broken', 'Camera Glass Broken',
                                            'Battery Swollen', 'Dead Condition', 'Water Damaged', 'No SIM Tray',
                                            'SIM Card Inside', 'SD Card Inside', 'Phone Case/Cover', 'Charger Included', 'Box with IMEI'
                                        ];
                                    @endphp

                                    @foreach($options as $option)
                                    <label class="inline-flex items-center opacity-70">
                                        <input type="checkbox" disabled {{ in_array($option, $checklist) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 bg-gray-100">
                                        <span class="ml-2">{{ $option }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-red-500 mt-3">রিসিভ করার পর চেকলিস্ট পরিবর্তন করা যায় না।</p>
                            </div>

                            <!-- Smart Drawer Tracking -->
                            <div>
                                <p class="font-bold text-gray-700 mb-4 border-b pb-2">Storage Location (কোথায় রাখলেন):</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="rack_number" :value="__('Rack Number (র‍্যাক)')" />
                                        <x-text-input id="rack_number" class="block mt-1 w-full" type="text" name="rack_number" :value="old('rack_number', $device->rack_number)" />
                                    </div>
                                    <div>
                                        <x-input-label for="drawer_number" :value="__('Drawer Number (ড্রয়ার)')" />
                                        <x-text-input id="drawer_number" class="block mt-1 w-full" type="text" name="drawer_number" :value="old('drawer_number', $device->drawer_number)" />
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('devices.index') }}">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4 bg-indigo-600 hover:bg-indigo-700">
                                {{ __('Update Device') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
