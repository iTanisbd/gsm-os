<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Customer (কাস্টমার এডিট করুন)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- এডিট ফর্মে PUT মেথড ব্যবহার করতে হয় -->
                    <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Customer Name -->
                        <div>
                            <x-input-label for="name" :value="__('Customer Name *')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $customer->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Phone Number -->
                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Phone Number *')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $customer->phone)" required />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <!-- Secondary Phone -->
                        <div class="mt-4">
                            <x-input-label for="phone_secondary" :value="__('Alternative Phone (Optional)')" />
                            <x-text-input id="phone_secondary" class="block mt-1 w-full" type="text" name="phone_secondary" :value="old('phone_secondary', $customer->phone_secondary)" />
                            <x-input-error :messages="$errors->get('phone_secondary')" class="mt-2" />
                        </div>

                        <!-- Address -->
                        <div class="mt-4">
                            <x-input-label for="address" :value="__('Address (Optional)')" />
                            <textarea id="address" name="address" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('address', $customer->address) }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <!-- Note -->
                        <div class="mt-4">
                            <x-input-label for="note" :value="__('Note (Optional)')" />
                            <textarea id="note" name="note" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2">{{ old('note', $customer->note) }}</textarea>
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('customers.index') }}">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Update Customer') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
