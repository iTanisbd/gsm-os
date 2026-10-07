<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Device List (ডিভাইস তালিকা)') }}
            </h2>
            <a href="{{ route('devices.create') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out shadow-sm">
                + Receive New Device
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Success Message Alert -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-0 text-gray-900 overflow-x-auto">

                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">SL</th>
                                <th scope="col" class="px-6 py-4 font-bold">Customer</th>
                                <th scope="col" class="px-6 py-4 font-bold">Device (Model)</th>
                                <th scope="col" class="px-6 py-4 font-bold">Problem</th>
                                <th scope="col" class="px-6 py-4 font-bold text-center">Status</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($devices as $key => $device)
                                <tr class="bg-white border-b hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4">{{ $key + 1 }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $device->customer->name }} <br>
                                        <span class="text-xs text-gray-500 font-normal">{{ $device->customer->phone }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-800">{{ $device->brand }}</span> - {{ $device->model }}
                                        @if($device->imei)
                                            <br><span class="text-xs text-gray-500">IMEI: {{ $device->imei }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ \Illuminate\Support\Str::limit($device->problem_description, 40) }}</td>

                                    <!-- Status Column with Inline Colors -->
                                    <td class="px-6 py-4 text-center">
                                        @if($device->status == 'pending')
                                            <span style="background-color: #fef08a; color: #854d0e;" class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-wider">
                                                {{ $device->status }}
                                            </span>
                                        @elseif($device->status == 'processing')
                                            <span style="background-color: #bfdbfe; color: #1e40af;" class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-wider">
                                                {{ $device->status }}
                                            </span>
                                        @elseif($device->status == 'completed')
                                            <span style="background-color: #bbf7d0; color: #166534;" class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-wider">
                                                {{ $device->status }}
                                            </span>
                                        @elseif($device->status == 'delivered')
                                            <span style="background-color: #e5e7eb; color: #374151;" class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-wider">
                                                {{ $device->status }}
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-wider bg-gray-200 text-gray-800">
                                                {{ $device->status }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end items-center space-x-2">
                                            <a href="{{ route('devices.show', $device->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md text-xs font-bold transition">View</a>
                                            <a href="{{ route('devices.edit', $device->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md text-xs font-bold transition">Edit</a>
                                            <form action="{{ route('devices.destroy', $device->id) }}" method="POST" class="inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ডিভাইসটি ডিলিট করতে চান?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md text-xs font-bold transition">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 bg-gray-50">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                            <p class="text-base font-medium">কোনো ডিভাইস পাওয়া যায়নি।</p>
                                            <p class="text-sm mt-1">উপরের বাটন থেকে নতুন ডিভাইস গ্রহণ করুন।</p>
                                        </div>
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
