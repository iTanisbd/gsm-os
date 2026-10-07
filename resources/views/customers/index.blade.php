<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customer List (কাস্টমার তালিকা)') }}
            </h2>
            <a href="{{ route('customers.create') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                + Add New Customer
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
                                <th scope="col" class="px-6 py-3">SL</th>
                                <th scope="col" class="px-6 py-3">Customer Name</th>
                                <th scope="col" class="px-6 py-3">Phone Number</th>
                                <th scope="col" class="px-6 py-3">Address</th>
                                <th scope="col" class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $key => $customer)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $key + 1 }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $customer->name }}</td>
                                    <td class="px-6 py-4">{{ $customer->phone }}</td>
                                    <td class="px-6 py-4">{{ $customer->address ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <!-- Edit Button -->
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই কাস্টমারকে ডিলিট করতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        কোনো কাস্টমার পাওয়া যায়নি। নতুন কাস্টমার যুক্ত করুন।
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
