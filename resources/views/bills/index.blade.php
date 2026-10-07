<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Billing & Invoices (বিল এবং ইনভয়েস)') }}
            </h2>
            <a href="{{ route('bills.create') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                + Create New Bill
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Success Message Alert -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-3">Bill ID</th>
                                <th scope="col" class="px-6 py-3">Customer & Device</th>
                                <th scope="col" class="px-6 py-3 text-right">Total</th>
                                <th scope="col" class="px-6 py-3 text-right">Discount</th>
                                <th scope="col" class="px-6 py-3 text-right">Paid</th>
                                <th scope="col" class="px-6 py-3 text-right">Due</th>
                                <th scope="col" class="px-6 py-3 text-center">Status</th>
                                <th scope="col" class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bills as $bill)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-bold text-gray-900">#INV-{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900">{{ $bill->customer->name }}</span> <br>
                                        <span class="text-xs text-gray-500">{{ $bill->device->brand }} {{ $bill->device->model }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">৳{{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-right text-gray-500">৳{{ number_format($bill->discount, 2) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-green-600">৳{{ number_format($bill->paid_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-red-600">৳{{ number_format($bill->due_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 text-xs rounded text-white font-semibold
                                            {{ $bill->payment_status == 'paid' ? 'bg-green-500' : '' }}
                                            {{ $bill->payment_status == 'due' ? 'bg-red-500' : '' }}
                                            {{ $bill->payment_status == 'partial' ? 'bg-yellow-500' : '' }}
                                        ">
                                            {{ ucfirst($bill->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <!-- Print Button -->
                                        <a href="{{ route('bills.show', $bill->id) }}" target="_blank" class="text-blue-600 hover:text-blue-900 mr-2 font-bold">Print</a>

                                        <!-- Edit Button -->
                                        <a href="{{ route('bills.edit', $bill->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('bills.destroy', $bill->id) }}" method="POST" class="inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই বিলটি ডিলিট করতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        কোনো বিল পাওয়া যায়নি। নতুন বিল তৈরি করুন।
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
