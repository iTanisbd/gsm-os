<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoice #INV-') }}{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}
            </h2>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                🖨️ Print Invoice
            </button>
        </div>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg print:shadow-none">
                <div class="p-8 text-gray-900">

                    <!-- Header -->
                    <div class="flex justify-between border-b pb-6">
                        <div>
                            <!-- ডাইনামিক দোকানের নাম না পেলে ডিফল্ট Anis Telecom দেখাবে -->
                            <h1 class="text-3xl font-bold uppercase">{{ auth()->user()->shop->name ?? 'Anis Telecom' }}</h1>
                            <p class="text-gray-500 mt-1">Mobile Repair & Accessories</p>
                            <p class="text-gray-500">Phone: {{ auth()->user()->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <h2 class="text-2xl font-bold text-gray-700 uppercase">INVOICE</h2>
                            <p class="font-bold mt-2 text-lg">#INV-{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-gray-500">Date: {{ $bill->created_at->format('d M, Y') }}</p>
                            <p class="text-gray-500 mt-1">Status:
                                <span class="uppercase font-bold
                                    {{ $bill->payment_status == 'paid' ? 'text-green-600' : '' }}
                                    {{ $bill->payment_status == 'due' ? 'text-red-600' : '' }}
                                    {{ $bill->payment_status == 'partial' ? 'text-yellow-600' : '' }}">
                                    {{ $bill->payment_status }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Customer & Device Info -->
                    <div class="flex justify-between mt-6 pb-6 border-b">
                        <div>
                            <h3 class="font-bold text-gray-700 mb-2">Billed To:</h3>
                            <p class="font-semibold text-lg">{{ $bill->customer->name }}</p>
                            <p class="text-gray-600">{{ $bill->customer->phone }}</p>
                            <p class="text-gray-600">{{ $bill->customer->address ?? '' }}</p>
                        </div>
                        <div class="text-right">
                            <h3 class="font-bold text-gray-700 mb-2">Device Details:</h3>
                            <p class="font-semibold text-lg">{{ $bill->device->brand }} {{ $bill->device->model }}</p>
                            @if($bill->device->imei)
                                <p class="text-gray-600">IMEI: {{ $bill->device->imei }}</p>
                            @endif
                            <p class="text-gray-600">Problem: {{ $bill->device->problem_description }}</p>
                        </div>
                    </div>

                    <!-- Amounts Table -->
                    <div class="mt-8">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-3 border-y font-bold uppercase text-gray-600">Description</th>
                                    <th class="p-3 border-y font-bold uppercase text-gray-600 text-right w-40">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-4 border-b">Mobile Repair Service ({{ $bill->device->brand }} {{ $bill->device->model }})</td>
                                    <td class="p-4 border-b text-right font-semibold">৳{{ number_format($bill->total_amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="flex justify-end mt-6">
                        <div class="w-72">
                            <div class="flex justify-between py-1">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold">৳{{ number_format($bill->total_amount, 2) }}</span>
                            </div>
                            @if($bill->discount > 0)
                            <div class="flex justify-between py-1 text-red-500">
                                <span>Discount:</span>
                                <span>- ৳{{ number_format($bill->discount, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between py-3 border-t border-b my-2 font-bold text-xl">
                                <span>Grand Total:</span>
                                <span>৳{{ number_format($bill->total_amount - $bill->discount, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-1 text-green-600 font-bold text-lg">
                                <span>Paid Amount:</span>
                                <span>৳{{ number_format($bill->paid_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-1 text-red-600 font-bold text-lg mt-1">
                                <span>Due Amount:</span>
                                <span>৳{{ number_format($bill->due_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Note -->
                    <div class="mt-16 text-center text-sm text-gray-500 border-t pt-6">
                        @if($bill->note)
                            <p class="mb-3 text-gray-700"><strong>Note:</strong> {{ $bill->note }}</p>
                        @endif
                        <p class="font-semibold">Thank you for your business!</p>
                        <p class="mt-1 text-xs">Software Developed by Anisur Rahman</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
