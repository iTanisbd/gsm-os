<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer Profile & History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6 border border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">{{ $customer->name }}</h3>
                        <p class="text-sm font-bold text-indigo-600 mt-1">Phone: {{ $customer->phone }}</p>
                        @if($customer->address)
                            <p class="text-xs text-gray-500 mt-1">Address: {{ $customer->address }}</p>
                        @endif
                    </div>
                    <a href="{{ route('customers.index') }}" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-3 py-1.5 rounded transition">&larr; Back to Customers</a>
                </div>
            </div>

            <!-- Customer's Devices & Repair History -->
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-200 p-6">
                <h4 class="text-md font-bold text-gray-800 mb-4 border-b pb-2">Device Repair History</h4>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-[10px] text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 font-bold">Tracking Code</th>
                                <th class="px-4 py-3 font-bold">Device Model</th>
                                <th class="px-4 py-3 font-bold">Status</th>
                                <th class="px-4 py-3 font-bold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->devices as $device)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-bold text-indigo-600">{{ $device->tracking_code }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $device->brand }} {{ $device->model }}</td>
                                    <td class="px-4 py-3">
                                        <span class="text-[9px] uppercase px-2 py-0.5 rounded font-bold {{ $device->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $device->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('devices.show', $device->tracking_code) }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1 rounded shadow-sm">View Details</a>
                                    </td>
                                endforelse
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">No devices found for this customer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
