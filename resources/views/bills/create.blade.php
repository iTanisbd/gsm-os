<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Bill (নতুন বিল তৈরি করুন)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('bills.store') }}">
                        @csrf

                        <!-- Select Device -->
                        <div>
                            <x-input-label for="device_id" :value="__('Select Device (ডিভাইস নির্বাচন করুন) *')" />
                            <select id="device_id" name="device_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md" required>
                                <option value="" disabled selected>-- ডিভাইস নির্বাচন করুন --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">
                                        {{ $device->customer->name }} | {{ $device->brand }} {{ $device->model }} (Problem: {{ \Illuminate\Support\Str::limit($device->problem_description, 20) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Amount Fields in a Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Total Amount -->
                            <div>
                                <x-input-label for="total_amount" :value="__('Total Amount (মোট বিল) *')" />
                                <x-text-input id="total_amount" class="block mt-1 w-full font-bold text-lg text-blue-600" type="number" step="0.01" name="total_amount" :value="old('total_amount')" required />
                            </div>

                            <!-- Discount -->
                            <div>
                                <x-input-label for="discount" :value="__('Discount (ছাড়)')" />
                                <x-text-input id="discount" class="block mt-1 w-full" type="number" step="0.01" name="discount" value="0" />
                            </div>

                            <!-- Paid Amount -->
                            <div>
                                <x-input-label for="paid_amount" :value="__('Paid Amount (জমা) *')" />
                                <x-text-input id="paid_amount" class="block mt-1 w-full font-bold text-lg text-green-600" type="number" step="0.01" name="paid_amount" value="0" required />
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="mt-4">
                            <x-input-label for="note" :value="__('Special Note (Optional)')" />
                            <textarea id="note" name="note" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md" rows="2">{{ old('note') }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('bills.index') }}">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4 bg-green-600 hover:bg-green-700">
                                {{ __('Create Bill & Save') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>