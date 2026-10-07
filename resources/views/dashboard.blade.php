<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Overview') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Welcome Banner -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 font-semibold text-lg">
                    স্বাগতম, {{ auth()->user()->name }}! আপনার দোকানের ({{ auth()->user()->shop->name ?? 'Anis Telecom' }}) আজকের সারসংক্ষেপ নিচে দেওয়া হলো:
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Total Revenue -->
                <div class="bg-gradient-to-r from-green-400 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase font-bold tracking-wider opacity-80">Total Revenue</p>
                            <h3 class="text-3xl font-bold mt-1">৳{{ number_format($totalRevenue, 2) }}</h3>
                        </div>
                        <div class="text-4xl opacity-50">💰</div>
                    </div>
                </div>

                <!-- Total Due -->
                <div class="bg-gradient-to-r from-red-400 to-red-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase font-bold tracking-wider opacity-80">Total Due</p>
                            <h3 class="text-3xl font-bold mt-1">৳{{ number_format($totalDue, 2) }}</h3>
                        </div>
                        <div class="text-4xl opacity-50">⚠️</div>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="bg-gradient-to-r from-blue-400 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase font-bold tracking-wider opacity-80">Total Customers</p>
                            <h3 class="text-3xl font-bold mt-1">{{ $totalCustomers }}</h3>
                        </div>
                        <div class="text-4xl opacity-50">👥</div>
                    </div>
                </div>

                <!-- Pending Repairs -->
                <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase font-bold tracking-wider opacity-80">Pending Repairs</p>
                            <h3 class="text-3xl font-bold mt-1">{{ $pendingDevices }}</h3>
                        </div>
                        <div class="text-4xl opacity-50">⏳</div>
                    </div>
                </div>

            </div>

            <!-- Secondary Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500 font-bold uppercase">Total Devices Received</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalDevices }}</h3>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500 font-bold uppercase">Repairs Completed</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $completedDevices }}</h3>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-500">
                    <p class="text-sm text-gray-500 font-bold uppercase">Devices Delivered</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $deliveredDevices }}</h3>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
