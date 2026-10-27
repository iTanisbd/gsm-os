<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Customer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('customers.update', $customer->id) }}" id="customerEditForm">
                        @csrf
                        @method('PUT')

                        <!-- 2-Column Grid Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Customer Name -->
                            <div>
                                <x-input-label for="name" :value="__('Customer Name *')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $customer->name)" required autofocus placeholder="Enter customer full name" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Phone Number (+880 fixed, user enters 10 digits starting with 1) -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone Number *')" />
                                @php
                                    $cleanPhone = preg_replace('/^(\+?880|0)/', '', old('phone', $customer->phone ?? ''));
                                @endphp
                                <div class="flex mt-1 shadow-sm rounded-md">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-600 text-sm font-bold select-none">
                                        +880
                                    </span>
                                    <input id="phone" type="tel" name="phone" value="{{ $cleanPhone }}"
                                        class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-semibold"
                                        placeholder="17XXXXXXXX" pattern="1[3-9][0-9]{8}" maxlength="10" minlength="10"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, ''); this.setCustomValidity('');"
                                        oninvalid="this.setCustomValidity('Please check the number! Enter 10 digits starting with 1 (e.g. 1712345678).')" required />
                                </div>
                                <span class="text-[11px] text-gray-500 mt-1 block">Enter 10 digits starting with 1 (e.g. 1712345678)</span>
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Alternative Phone (Optional, if filled must be 10 digits starting with 1) -->
                            <div>
                                <x-input-label for="phone_secondary" :value="__('Alternative Phone (Optional)')" />
                                @php
                                    $cleanPhoneSec = preg_replace('/^(\+?880|0)/', '', old('phone_secondary', $customer->phone_secondary ?? ''));
                                @endphp
                                <div class="flex mt-1 shadow-sm rounded-md">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-600 text-sm font-bold select-none">
                                        +880
                                    </span>
                                    <input id="phone_secondary" type="tel" name="phone_secondary" value="{{ $cleanPhoneSec }}"
                                        class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-semibold"
                                        placeholder="18XXXXXXXX" pattern="1[3-9][0-9]{8}" maxlength="10" minlength="10"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, ''); this.setCustomValidity('');"
                                        oninvalid="this.setCustomValidity('Invalid alternative number! Enter 10 digits starting with 1, or leave it blank.')" />
                                </div>
                                <span class="text-[11px] text-gray-500 mt-1 block">Enter 10 digits starting with 1, or leave blank</span>
                                <x-input-error :messages="$errors->get('phone_secondary')" class="mt-2" />
                            </div>

                            <!-- Address (Optional) -->
                            <div>
                                <x-input-label for="address" :value="__('Address (Optional)')" />
                                <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address', $customer->address)" placeholder="Enter customer address" />
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                        </div>

                        <!-- Note (Full Width) -->
                        <div class="mt-6">
                            <x-input-label for="note" :value="__('Note (Optional)')" />
                            <textarea id="note" name="note" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2" placeholder="Write any additional note here...">{{ old('note', $customer->note) }}</textarea>
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition" href="{{ route('customers.index') }}">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button type="submit" class="ms-4 bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                {{ __('UPDATE CUSTOMER') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Prepend '0' before submitting so standard 11-digit format (017XXXXXXXX) is saved in database
        document.getElementById('customerEditForm').addEventListener('submit', function() {
            const phoneInput = document.getElementById('phone');
            const phoneSecInput = document.getElementById('phone_secondary');

            if (phoneInput.value.length === 10 && !phoneInput.value.startsWith('0')) {
                phoneInput.removeAttribute('maxlength');
                phoneInput.removeAttribute('pattern');
                phoneInput.value = '0' + phoneInput.value;
            }
            if (phoneSecInput.value.length === 10 && !phoneSecInput.value.startsWith('0')) {
                phoneSecInput.removeAttribute('maxlength');
                phoneSecInput.removeAttribute('pattern');
                phoneSecInput.value = '0' + phoneSecInput.value;
            }
        });
    </script>
</x-app-layout>
