<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daily Expenses (দৈনিক ক্যাশ-বুক ও খরচ)') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Top Summary & Filter Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-rose-100 rounded-full flex items-center justify-center text-rose-600 flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Expenses ({{ \Carbon\Carbon::parse($month)->format('F Y') }})</p>
                        <h3 class="text-3xl font-black text-rose-600 tracking-tight">৳{{ number_format($totalExpense, 2) }}</h3>
                    </div>
                </div>

                <form method="GET" action="{{ route('expenses.index') }}" class="flex items-center gap-2 bg-gray-50 p-2 rounded-lg border border-gray-200">
                    <span class="text-xs font-bold text-gray-500 uppercase ml-2 hidden sm:block">Filter Month:</span>
                    <input type="month" name="month" value="{{ $month }}" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-gray-700 text-sm h-9 cursor-pointer" onchange="this.form.submit()">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-3 py-1.5 rounded-md font-bold shadow-sm transition text-sm h-9 cursor-pointer">Search</button>
                </form>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Add Expense Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white shadow-sm sm:rounded-xl border-t-4 border-indigo-500 overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Add New Expense
                            </h3>

                            <form action="{{ route('expenses.store') }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-gray-700 text-sm cursor-pointer" required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                                        <select name="category" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-gray-700 text-sm cursor-pointer" required>
                                            <option value="" disabled selected>Select Category</option>
                                            <option value="Parts Purchase">Parts Purchase (পার্টস ক্রয়)</option>
                                            <option value="Shop Rent">Shop Rent (দোকান ভাড়া)</option>
                                            <option value="Electricity Bill">Electricity Bill (বিদ্যুৎ বিল)</option>
                                            <option value="Staff Salary">Staff Salary (স্টাফ বেতন)</option>
                                            <option value="Snacks & Food">Snacks & Food (চা-নাস্তা)</option>
                                            <option value="Internet Bill">Internet Bill (ইন্টারনেট বিল)</option>
                                            <option value="Marketing/Ads">Marketing & Ads (বিজ্ঞাপন)</option>
                                            <option value="Others">Others (অন্যান্য)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><span class="text-gray-500 font-bold">৳</span></div>
                                            <input type="number" name="amount" step="0.01" min="1" class="block w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-black text-rose-600" placeholder="0.00" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Note / Details <span class="text-xs font-normal text-gray-400">(Optional)</span></label>
                                        <input type="text" name="note" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 text-sm font-medium" placeholder="Ex: Bought 5 displays...">
                                    </div>

                                    <div class="pt-3">
                                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition flex justify-center items-center gap-2 uppercase tracking-widest text-xs cursor-pointer">
                                            Save Expense
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Expense List -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-200">
                        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Expense History
                            </h3>
                        </div>

                        <div class="overflow-x-auto min-h-[400px]">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="text-[10px] text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 font-bold">Date</th>
                                        <th class="px-6 py-4 font-bold">Category & Details</th>
                                        <th class="px-6 py-4 font-bold">Added By</th>
                                        <th class="px-6 py-4 font-bold text-right">Amount</th>
                                        <th class="px-6 py-4 font-bold text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $expense)
                                        <tr class="border-b border-gray-100 hover:bg-rose-50/30 transition">
                                            <td class="px-6 py-4 font-bold text-gray-700 whitespace-nowrap">
                                                {{ $expense->expense_date->format('d M, Y') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="font-bold text-gray-900 block bg-gray-100 w-max px-2 py-0.5 rounded text-xs mb-1">{{ $expense->category }}</span>
                                                @if($expense->note)
                                                    <span class="text-xs font-semibold text-gray-500 block">{{ $expense->note }}</span>
                                                @else
                                                    <span class="text-xs font-normal text-gray-400 italic block">No details provided</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                                {{ $expense->user->name ?? 'Admin' }}
                                            </td>
                                            <td class="px-6 py-4 font-black text-rose-600 text-right whitespace-nowrap text-base">
                                                ৳{{ number_format($expense->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই খরচের রেকর্ডটি ডিলিট করতে চান?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-rose-600 bg-gray-50 hover:bg-rose-100 p-2 rounded-lg transition border border-gray-200 hover:border-rose-200 shadow-sm cursor-pointer" title="Delete Expense">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 bg-gray-50/50">
                                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <p class="text-sm font-bold text-gray-500">এই মাসে এখনও কোনো খরচের এন্ট্রি নেই!</p>
                                                <p class="text-xs mt-1">বাম পাশের ফর্ম থেকে নতুন খরচ যুক্ত করুন।</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
