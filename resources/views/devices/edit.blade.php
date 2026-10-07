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

                        <!-- Device Status (নতুন অপশন) -->
                        <div class="mb-4 p-4 bg-gray-50 border rounded-md">
                            <x-input-label for="status" :value="__('Device Status (কাজের বর্তমান অবস্থা) *')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-bold" required>
                                <option value="pending" {{ old('status', $device->status) == 'pending' ? 'selected' : '' }}>Pending (পেন্ডিং)</option>
                                <option value="processing" {{ old('status', $device->status) == 'processing' ? 'selected' : '' }}>Processing (কাজ চলছে)</option>
                                <option value="completed" {{ old('status', $device->status) == 'completed' ? 'selected' : '' }}>Completed (কাজ শেষ)</option>
                                <option value="delivered" {{ old('status', $device->status) == 'delivered' ? 'selected' : '' }}>Delivered (ডেলিভারি সম্পন্ন)</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Select Customer -->
                        <div>
                            <x-input-label for="customer_id" :value="__('Select Customer *')" />
                            <select id="customer_id" name="customer_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $device->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                        </div>

                        <!-- Brand and Model -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="brand" :value="__('Brand *')" />
                                <x-text-input id="brand" class="block mt-1 w-full" type="text" name="brand" :value="old('brand', $device->brand)" required />
                            </div>
                            <div>
                                <x-input-label for="model" :value="__('Model *')" />
                                <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model', $device->model)" required />
                            </div>
                        </div>

                        <!-- IMEI -->
                        <div class="mt-4">
                            <x-input-label for="imei" :value="__('IMEI / Serial Number')" />
                            <x-text-input id="imei" class="block mt-1 w-full" type="text" name="imei" :value="old('imei', $device->imei)" />
                        </div>

                        <!-- Problem Description -->
                        <div class="mt-4">
                            <x-input-label for="problem_description" :value="__('Problem Description *')" />
                            <textarea id="problem_description" name="problem_description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md" rows="3" required>{{ old('problem_description', $device->problem_description) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('devices.index') }}">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Update Device') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
