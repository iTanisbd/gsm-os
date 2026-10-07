<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Device List (ডিভাইস তালিকা)') }}
            </h2>
            <a href="{{ route('devices.create') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                + Receive New Device
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
                                <th scope="col" class="px-6 py-3">Customer</th>
                                <th scope="col" class="px-6 py-3">Device (Model)</th>
                                <th scope="col" class="px-6 py-3">Problem</th>
                                <th scope="col" class="px-6 py-3 text-center">Status</th>
                                <th scope="col" class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($devices as $key => $device)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $key + 1 }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $device->customer->name }} <br>
                                        <span class="text-xs text-gray-400">{{ $device->customer->phone }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold">{{ $device->brand }}</span> - {{ $device->model }}
                                        @if($device->imei)
                                            <br><span class="text-xs text-gray-400">IMEI: {{ $device->imei }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ \Illuminate\Support\Str::limit($device->problem_description, 40) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 text-xs rounded text-white font-semibold
                                            {{ $device->status == 'pending' ? 'bg-yellow-500' : '' }}
                                            {{ $device->status == 'processing' ? 'bg-blue-500' : '' }}
                                            {{ $device->status == 'completed' ? 'bg-green-500' : '' }}
                                            {{ $device->status == 'delivered' ? 'bg-gray-500' : '' }}
                                        ">
                                            {{ ucfirst($device->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <!-- Edit Button -->
                                        <a href="{{ route('devices.edit', $device->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('devices.destroy', $device->id) }}" method="POST" class="inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ডিভাইসটি ডিলিট করতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        কোনো ডিভাইস পাওয়া যায়নি। নতুন ডিভাইস গ্রহণ করুন।
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
