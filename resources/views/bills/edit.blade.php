<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Bill (বিল এডিট করুন)') }} #INV-{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('bills.update', $bill->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Device Info (Readonly) -->
                        <div class="mb-6 p-4 bg-gray-50 border rounded-md">
                            <h3 class="font-bold text-gray-700">Customer & Device Info</h3>
                            <p class="mt-1"><span class="font-semibold">Customer:</span> {{ $bill->customer->name }} ({{ $bill->customer->phone }})</p>
                            <p><span class="font-semibold">Device:</span> {{ $bill->device->brand }} {{ $bill->device->model }}</p>
                        </div>

                        <!-- Amount Fields in a Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Total Amount -->
                            <div>
                                <x-input-label for="total_amount" :value="__('Total Amount (মোট বিল) *')" />
                                <x-text-input id="total_amount" class="block mt-1 w-full font-bold text-lg text-blue-600" type="number" step="0.01" name="total_amount" :value="old('total_amount', $bill->total_amount)" required />
                                <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                            </div>

                            <!-- Discount -->
                            <div>
                                <x-input-label for="discount" :value="__('Discount (ছাড়)')" />
                                <x-text-input id="discount" class="block mt-1 w-full" type="number" step="0.01" name="discount" :value="old('discount', $bill->discount)" />
                                <x-input-error :messages="$errors->get('discount')" class="mt-2" />
                            </div>

                            <!-- Paid Amount -->
                            <div>
                                <x-input-label for="paid_amount" :value="__('Paid Amount (জমা) *')" />
                                <x-text-input id="paid_amount" class="block mt-1 w-full font-bold text-lg text-green-600" type="number" step="0.01" name="paid_amount" :value="old('paid_amount', $bill->paid_amount)" required />
                                <x-input-error :messages="$errors->get('paid_amount')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="mt-4">
                            <x-input-label for="note" :value="__('Special Note (Optional)')" />
                            <textarea id="note" name="note" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md" rows="2">{{ old('note', $bill->note) }}</textarea>
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('bills.index') }}">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4 bg-indigo-600 hover:bg-indigo-700">
                                {{ __('Update Bill') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
