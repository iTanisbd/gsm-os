<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Device Details & Repair Log') }}
            </h2>
            <a href="{{ route('devices.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-semibold transition">
                &larr; Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md border border-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <!-- 3 Columns Professional Layout -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

                <!-- Left Column (1/3): Device & Customer Info -->
                <div class="space-y-6">
                    <!-- Customer Info -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-indigo-500">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Customer Info</h3>
                        <p class="text-gray-700"><strong>Name:</strong> {{ $device->customer->name }}</p>
                        <p class="text-gray-700"><strong>Phone:</strong> {{ $device->customer->phone }}</p>
                    </div>

                    <!-- Device Info -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-blue-500">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Device Info</h3>
                        <p class="text-gray-700"><strong>Brand & Model:</strong> {{ $device->brand }} {{ $device->model }}</p>
                        <p class="text-gray-700"><strong>IMEI:</strong> {{ $device->imei ?? 'N/A' }}</p>
                        <p class="text-gray-700 mt-2 text-red-600"><strong>Password:</strong> {{ $device->device_password ?? 'None' }}</p>
                        <p class="text-gray-700 mt-2"><strong>Rack/Drawer:</strong> {{ $device->rack_number ?? 'N/A' }} / {{ $device->drawer_number ?? 'N/A' }}</p>
                        <p class="text-gray-700 mt-2"><strong>Status:</strong>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full uppercase">{{ $device->status }}</span>
                        </p>
                    </div>

                    <!-- Initial Faults -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-red-500">
                        <h3 class="text-md font-bold text-gray-800 border-b pb-2 mb-3">Initial Faults</h3>
                        <div class="mb-3">
                            <span class="text-xs text-gray-500 font-bold uppercase">Customer Complaint:</span>
                            <p class="text-sm text-gray-800 bg-gray-50 p-2 rounded border">{{ $device->problem_description }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 font-bold uppercase">Actual Fault:</span>
                            <p class="text-sm text-gray-800 bg-gray-50 p-2 rounded border">{{ $device->actual_fault ?? 'No note provided.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Middle Column (1/3): Repair Log / Timeline -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 h-full">
                    <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-6"><i class="fas fa-history text-indigo-500"></i> Repair Log</h3>

                    @if($device->status !== 'delivered')
                        <!-- Add New Log Form -->
                        <div class="mb-8 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <form method="POST" action="{{ route('devices.repair-log', $device->id) }}">
                                @csrf
                                <x-input-label for="note" :value="__('Add Update')" class="text-indigo-600 font-bold" />
                                <textarea id="note" name="note" class="block mt-2 w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm" rows="2" required placeholder="Write update here..."></textarea>
                                <div class="mt-3 flex justify-end">
                                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 text-xs py-1.5">Post</x-primary-button>
                                </div>
                            </form>
                        </div>
                    @else
                        <!-- Delivered Message -->
                        <div class="mb-8 bg-green-50 p-4 rounded-lg border border-green-200 text-center shadow-sm">
                            <svg class="w-8 h-8 text-green-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h4 class="text-md font-bold text-green-800">Delivered</h4>
                            <p class="text-xs text-green-600 mt-1">এই ডিভাইসটি কাস্টমারকে ডেলিভারি দেওয়া হয়েছে। তাই নতুন করে কোনো রিপেয়ার লগ যোগ করা বন্ধ রাখা হয়েছে।</p>
                        </div>
                    @endif

                    <!-- Timeline Display -->
                    <div class="space-y-4">
                        @forelse($device->repairLogs as $log)
                            <div class="flex items-start p-3 bg-white border border-gray-100 shadow-sm rounded-lg relative">
                                <div class="absolute -left-2 top-4 w-4 h-4 bg-indigo-500 rounded-full border-2 border-white shadow"></div>
                                <div class="border-l-2 border-indigo-200 pl-4 w-full ml-2">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-bold text-gray-800 text-sm">{{ $log->user->name }}</span>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $log->created_at->format('d M, Y - h:i A') }}</span>
                                    </div>
                                    <p class="text-gray-700 text-sm mt-1 whitespace-pre-wrap">{{ $log->note }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300 text-sm">
                                No updates yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Right Column (1/3): Financials, Checklist & Delivery info -->
                <div class="space-y-6">

                    <!-- Financial Summary -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-green-500">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Financial Summary</h3>
                        @if($bill)
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between border-b pb-1">
                                    <span class="text-gray-600">Total Bill:</span>
                                    <span class="font-bold">৳{{ number_format($bill->total_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-1">
                                    <span class="text-gray-600">Discount:</span>
                                    <span class="font-bold text-red-500">৳{{ number_format($bill->discount, 2) }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-1">
                                    <span class="text-gray-600">Paid:</span>
                                    <span class="font-bold text-green-600">৳{{ number_format($bill->paid_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between bg-gray-50 p-2 rounded mt-2">
                                    <span class="text-gray-800 font-bold">Due Amount:</span>
                                    <span class="font-bold {{ $bill->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">৳{{ number_format($bill->due_amount, 2) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4 bg-gray-50 rounded text-gray-500 text-sm">
                                No bill generated yet.
                                <br>
                                <a href="{{ route('bills.create') }}" class="text-indigo-600 hover:underline mt-2 inline-block font-bold">Create Bill &rarr;</a>
                            </div>
                        @endif
                    </div>

                    <!-- Pre-repair Checklist -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-yellow-500">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Condition at Receive</h3>
                        @if(!empty($device->pre_repair_checklist) && is_array($device->pre_repair_checklist))
                            <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">
                                @foreach($device->pre_repair_checklist as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 text-sm text-center py-4 bg-gray-50 rounded">No checklist selected.</p>
                        @endif
                    </div>

                    <!-- Risk Agreement & Delivery Date -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-gray-500 space-y-3">
                         <div>
                            <span class="text-xs text-gray-500 font-bold uppercase block mb-1">Expected Delivery:</span>
                            <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded font-semibold text-sm inline-block">
                                {{ $device->estimated_delivery_date ? \Carbon\Carbon::parse($device->estimated_delivery_date)->format('d M, Y') : 'Not Set' }}
                            </span>
                        </div>
                        <div>
                             <span class="text-xs text-gray-500 font-bold uppercase block mb-1">Risk Agreement:</span>
                             @if($device->risk_agreement)
                                <span class="bg-red-100 text-red-800 px-2.5 py-1 rounded text-xs font-bold inline-block">Accepted (Dead Risk/No Data)</span>
                             @else
                                <span class="bg-green-100 text-green-800 px-2.5 py-1 rounded text-xs font-bold inline-block">Normal Conditions</span>
                             @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
