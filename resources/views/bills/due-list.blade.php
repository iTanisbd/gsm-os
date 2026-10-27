<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ __('Due List (বাকির খাতা ও রিমাইন্ডার)') }}
            </h2>
            <a href="{{ route('bills.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-semibold transition">
                &larr; Back to Bills
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-500">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-3">Bill ID</th>
                                <th scope="col" class="px-6 py-3">Customer Info</th>
                                <th scope="col" class="px-6 py-3 text-right">Total Bill</th>
                                <th scope="col" class="px-6 py-3 text-right">Due Amount</th>
                                <th scope="col" class="px-6 py-3 text-center">Promise Date</th>
                                <th scope="col" class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bills as $bill)
                                @php
                                    $invoiceNo = $bill->invoice_number ?? ('#INV-' . str_pad($bill->id, 4, '0', STR_PAD_LEFT));
                                    $isOverdue = $bill->promise_date && \Carbon\Carbon::parse($bill->promise_date)->isPast() && !\Carbon\Carbon::parse($bill->promise_date)->isToday();
                                    $isToday = $bill->promise_date && \Carbon\Carbon::parse($bill->promise_date)->isToday();
                                @endphp
                                <tr class="bg-white border-b hover:bg-gray-50 transition {{ $isOverdue ? 'bg-red-50' : '' }}">
                                    <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">{{ $invoiceNo }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900">{{ $bill->customer->name ?? 'N/A' }}</span>
                                        <span class="text-xs text-gray-500 block">{{ $bill->customer->phone ?? '' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">৳{{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-red-600 text-lg">৳{{ number_format($bill->due_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($bill->promise_date)
                                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                                {{ $isOverdue ? 'bg-red-100 text-red-800 border border-red-200' : ($isToday ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                {{ \Carbon\Carbon::parse($bill->promise_date)->format('d M, Y') }}
                                                @if($isOverdue) (Overdue) @elseif($isToday) (Today) @endif
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">Not Set</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <!-- FIXED: এখন আর ID না গিয়ে সরাসরি tracking_code যাবে -->
                                        @if($bill->device)
                                            <a href="{{ route('devices.show', $bill->device->tracking_code) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded font-bold text-xs transition-colors">Collect Due &rarr;</a>
                                        @else
                                            <span class="text-xs text-gray-400">Device Deleted</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        আলহামদুলিল্লাহ! বর্তমানে কোনো কাস্টমারের কাছে বকেয়া নেই।
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
