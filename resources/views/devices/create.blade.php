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

                        <!-- Select Customer Dropdown -->
                        <div>
                            <x-input-label for="customer_id" :value="__('Select Customer (কাস্টমার নির্বাচন করুন) *')" />
                            <select id="customer_id" name="customer_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled selected>-- কাস্টমার নির্বাচন করুন --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                        </div>

                        <!-- Brand and Model in a Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="brand" :value="__('Brand (ব্র্যান্ড) *')" />
                                <x-text-input id="brand" class="block mt-1 w-full" type="text" name="brand" :value="old('brand')" placeholder="e.g. Samsung, Apple, Xiaomi" required />
                                <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="model" :value="__('Model (মডেল) *')" />
                                <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model')" placeholder="e.g. Galaxy S23, iPhone 14" required />
                                <x-input-error :messages="$errors->get('model')" class="mt-2" />
                            </div>
                        </div>

                        <!-- IMEI / Serial Number -->
                        <div class="mt-4">
                            <x-input-label for="imei" :value="__('IMEI / Serial Number (Optional)')" />
                            <x-text-input id="imei" class="block mt-1 w-full" type="text" name="imei" :value="old('imei')" />
                            <x-input-error :messages="$errors->get('imei')" class="mt-2" />
                        </div>

                        <!-- Problem Description -->
                        <div class="mt-4">
                            <x-input-label for="problem_description" :value="__('Problem Description (সমস্যার বিবরণ) *')" />
                            <textarea id="problem_description" name="problem_description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" required>{{ old('problem_description') }}</textarea>
                            <x-input-error :messages="$errors->get('problem_description')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('devices.index') }}">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Save Device') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
