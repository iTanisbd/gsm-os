<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Overview') }}
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-4 border border-gray-100">
                <div class="p-3 text-gray-800 font-medium text-sm">
                    স্বাগতম, <span class="font-bold text-indigo-700">{{ auth()->user()->name }}</span>! আপনার দোকানের (<span class="font-bold">{{ auth()->user()->shop->name ?? 'Anis Telecom' }}</span>) সার্বিক অবস্থার রিপোর্ট নিচে দেওয়া হলো:
                </div>
            </div>

            <!-- 1. Overall All-time Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg shadow-sm p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider opacity-80 mb-1">Total Revenue</p>
                            <h3 class="text-2xl font-black">৳{{ number_format($totalRevenue, 2) }}</h3>
                        </div>
                        <div class="p-2.5 bg-white/20 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-rose-500 to-rose-600 rounded-lg shadow-sm p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider opacity-80 mb-1">Total Due</p>
                            <h3 class="text-2xl font-black">৳{{ number_format($totalDue, 2) }}</h3>
                        </div>
                        <div class="p-2.5 bg-white/20 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-sm p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider opacity-80 mb-1">Total Customers</p>
                            <h3 class="text-2xl font-black">{{ number_format($totalCustomers) }}</h3>
                        </div>
                        <div class="p-2.5 bg-white/20 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-amber-400 to-orange-500 rounded-lg shadow-sm p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider opacity-80 mb-1">Pending Repairs</p>
                            <h3 class="text-2xl font-black">{{ number_format($pendingDevices) }}</h3>
                        </div>
                        <div class="p-2.5 bg-white/20 rounded-full">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Secondary Operational Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-indigo-500 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Total Received</p>
                            <h3 class="text-xl font-black text-indigo-600 tracking-tight">{{ number_format($totalDevices) }}</h3>
                            <span class="text-[10px] text-gray-400 font-medium mt-1 block">মোট ডিভাইস</span>
                        </div>
                        <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg></div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-emerald-500 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Repairs Completed</p>
                            <h3 class="text-xl font-black text-emerald-600 tracking-tight">{{ number_format($completedDevices) }}</h3>
                            <span class="text-[10px] text-gray-400 font-medium mt-1 block">মেরামত সম্পন্ন</span>
                        </div>
                        <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Delivered</p>
                            <h3 class="text-xl font-black text-blue-600 tracking-tight">{{ number_format($deliveredDevices) }}</h3>
                            <span class="text-[10px] text-gray-400 font-medium mt-1 block">ডেলিভারি সম্পন্ন</span>
                        </div>
                        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg></div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Partial Repaired</p>
                            <h3 class="text-xl font-black text-yellow-600 tracking-tight">{{ number_format($partialDevices) }}</h3>
                            <span class="text-[10px] text-gray-400 font-medium mt-1 block">আংশিক মেরামত</span>
                        </div>
                        <div class="p-2.5 bg-yellow-50 text-yellow-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-rose-500 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Returned</p>
                            <h3 class="text-xl font-black text-rose-600 tracking-tight">{{ number_format($returnedDevices) }}</h3>
                            <span class="text-[10px] text-gray-400 font-medium mt-1 block">কাজ ছাড়া ফেরত</span>
                        </div>
                        <div class="p-2.5 bg-rose-50 text-rose-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg></div>
                    </div>
                </div>
            </div>

            <!-- 3. Current Month Financials (Fixed Net Profit Icon & Alignment) -->
            <div class="flex justify-between items-end mb-3 pl-1">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Current Month Financials ({{ date('F Y') }})</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-emerald-100 p-4 flex items-center gap-4">
                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Monthly Income</p>
                        <h3 class="text-xl font-black text-emerald-600">৳{{ number_format($monthlyIncome, 2) }}</h3>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-rose-100 p-4 flex items-center gap-4">
                    <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center text-rose-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Monthly Expense</p>
                        <h3 class="text-xl font-black text-rose-600">৳{{ number_format($monthlyExpense, 2) }}</h3>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border {{ $netProfit >= 0 ? 'border-indigo-100' : 'border-red-100' }} p-4 flex items-center gap-4 relative overflow-hidden">

                    <!-- Fixed perfectly centered Watermark -->
                    <div class="absolute right-0 top-1/2 transform -translate-y-1/2 text-gray-100 opacity-60">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>

                    <div class="w-10 h-10 flex-shrink-0 {{ $netProfit >= 0 ? 'bg-indigo-100 text-indigo-600' : 'bg-red-100 text-red-600' }} rounded-full flex items-center justify-center relative z-10">
                        <!-- Thinner, standard Dollar Icon -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Net Profit</p>
                        <h3 class="text-xl font-black {{ $netProfit >= 0 ? 'text-indigo-600' : 'text-red-600' }}">৳{{ number_format($netProfit, 2) }}</h3>
                    </div>
                </div>
            </div>

            <!-- 4. Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                <!-- Bar Chart -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <div class="flex justify-between items-center mb-3 border-b pb-2">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Income vs Expense</h3>
                        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2 m-0 p-0">
                            <select name="chart_filter" onchange="this.form.submit()" class="text-[10px] font-bold border-gray-200 text-gray-700 bg-gray-50 rounded py-1 pl-2 pr-6 outline-none cursor-pointer">
                                <option value="7days" {{ $chartData['current_filter'] == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="this_month" {{ $chartData['current_filter'] == 'this_month' ? 'selected' : '' }}>This Month</option>
                                <option value="this_year" {{ $chartData['current_filter'] == 'this_year' ? 'selected' : '' }}>This Year</option>
                            </select>
                        </form>
                    </div>
                    <div class="relative h-56">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>

                <!-- Doughnut Chart (Fixed Tooltips on Hover) -->
                <div class="lg:col-span-1 bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3 border-b pb-2">Repair Status</h3>
                    <div class="relative h-56 flex justify-center items-center">
                        <canvas id="repairStatusChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js Initialization Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctxBar = document.getElementById('incomeExpenseChart').getContext('2d');
            const chartData = @json($chartData);

            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Income (৳)',
                            data: chartData.income,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderRadius: 3,
                            barPercentage: 0.6
                        },
                        {
                            label: 'Expense (৳)',
                            data: chartData.expense,
                            backgroundColor: 'rgba(244, 63, 94, 0.85)',
                            borderRadius: 3,
                            barPercentage: 0.6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10, weight: 'bold' } } },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { borderDash: [4, 4], color: '#f3f4f6' } },
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                    }
                }
            });

            const ctxPie = document.getElementById('repairStatusChart').getContext('2d');
            const statusData = @json($repairStatusData);

            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#FCD34D', '#60A5FA', '#34D399', '#9CA3AF', '#FB7185'],
                        // Keep colors exact same on hover so it doesn't darken
                        hoverBackgroundColor: ['#FCD34D', '#60A5FA', '#34D399', '#9CA3AF', '#FB7185'],
                        borderWidth: 0,
                        hoverBorderWidth: 0,
                        hoverOffset: 0 // Tooltips remain active, but shape doesn't expand
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 10, padding: 12, font: { size: 10, weight: 'bold' } } },
                        tooltip: { enabled: true } // Ensuring tooltips are ON
                    }
                }
            });
        });
    </script>
</x-app-layout>
