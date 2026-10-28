<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inventory & Stock (স্টক ম্যানেজমেন্ট)') }}
        </h2>
    </x-slot>

    <!-- Changed from py-8 to py-12 for consistent top spacing -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Top Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-indigo-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Total Parts Type</p>
                        <h3 class="text-2xl font-black text-indigo-600">{{ $totalItems }} <span class="text-sm font-normal text-gray-400">Items</span></h3>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Total Stock Value</p>
                        <h3 class="text-2xl font-black text-emerald-600">৳{{ number_format($totalStockValue, 2) }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border {{ $lowStockCount > 0 ? 'border-red-200' : 'border-gray-200' }} p-5 flex items-center gap-4">
                    <div class="w-12 h-12 {{ $lowStockCount > 0 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500' }} rounded-full flex items-center justify-center relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        @if($lowStockCount > 0)
                            <span class="absolute top-0 right-0 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span></span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Low Stock Alerts</p>
                        <h3 class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-red-600' : 'text-gray-600' }}">{{ $lowStockCount }} <span class="text-sm font-normal text-gray-400">Items</span></h3>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                <!-- Left Column: Add Parts Form -->
                <div class="xl:col-span-1">
                    <div class="bg-white shadow-sm sm:rounded-xl border-t-4 border-indigo-500 overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Add New Parts
                            </h3>

                            <form action="{{ route('inventory.store') }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Part Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-gray-700 text-sm" placeholder="Ex: Original Display, Type-C Port" required>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
                                            <input type="text" id="brand_input" name="brand" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-gray-700 text-sm" placeholder="Samsung, Universal..." required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Device Model <span class="text-red-500">*</span></label>
                                            <input type="text" id="model_input" name="model" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-gray-700 text-sm" placeholder="Galaxy A51, All..." required oninput="stripBrandFromModel()">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                                        <select name="category" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-gray-700 text-sm cursor-pointer" required>
                                            <option value="" disabled selected>Select Category</option>
                                            <option value="Display">Display</option>
                                            <option value="Touchpad / Glass">Touchpad / Glass</option>
                                            <option value="Battery">Battery</option>
                                            <option value="Power Button">Power Button</option>
                                            <option value="Volume Button">Volume Button (Up/Down)</option>
                                            <option value="Triple Button">Triple Button</option>
                                            <option value="Charging Port (Type-C)">Charging Port (Type-C)</option>
                                            <option value="Charging Port (Type-A/B)">Charging Port (Type-A/B)</option>
                                            <option value="Charging Port (Micro USB)">Charging Port (Micro USB)</option>
                                            <option value="Charging Port with Data Transfer">Charging Port with Data Transfer</option>
                                            <option value="USB Connection Repair">USB Connection Repair</option>
                                            <option value="Backshell / Casing">Backshell / Casing</option>
                                            <option value="Camera">Camera</option>
                                            <option value="Speaker / Microphone">Speaker / Microphone</option>
                                            <option value="Flex Cable / Ribbon">Flex Cable / Ribbon</option>
                                            <option value="Charging Board">Charging Board / Sub-board</option>
                                            <option value="IC / Hardware">IC / Mainboard Hardware</option>
                                            <option value="Accessories">Accessories</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Buy Price <span class="text-red-500">*</span></label>
                                            <div class="flex shadow-sm rounded-md">
                                                <span class="inline-flex items-center px-2.5 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 font-bold text-sm">৳</span>
                                                <input type="number" name="purchase_price" step="0.01" min="0" class="block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 font-black text-rose-600 text-sm" placeholder="0.00" required>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Sell Price <span class="text-red-500">*</span></label>
                                            <div class="flex shadow-sm rounded-md">
                                                <span class="inline-flex items-center px-2.5 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 font-bold text-sm">৳</span>
                                                <input type="number" name="selling_price" step="0.01" min="0" class="block w-full rounded-none rounded-r-md border-gray-300 focus:border-emerald-500 font-black text-emerald-600 text-sm" placeholder="0.00" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                                            <input type="number" name="stock" min="1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-gray-700 text-sm" placeholder="Total pieces" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-1" title="এই পরিমাণের নিচে নামলে অ্যালার্ট দেখাবে">Alert Limit</label>
                                            <input type="number" name="low_stock_threshold" min="0" value="5" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-gray-700 text-sm">
                                        </div>
                                    </div>

                                    <div class="pt-3">
                                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition flex justify-center items-center gap-2 uppercase tracking-widest text-xs cursor-pointer">
                                            Add to Stock
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Inventory List -->
                <div class="xl:col-span-2">
                    <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-200">
                        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                Available Stock List
                            </h3>
                        </div>

                        <div class="overflow-x-auto min-h-[400px]">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="text-[10px] text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 font-bold">Parts Name & Details</th>
                                        <th class="px-6 py-4 font-bold text-center">In Stock</th>
                                        <th class="px-6 py-4 font-bold text-right">Purchase Price</th>
                                        <th class="px-6 py-4 font-bold text-right">Selling Price</th>
                                        <th class="px-6 py-4 font-bold text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inventories as $item)
                                        <tr class="border-b border-gray-100 hover:bg-indigo-50/30 transition">
                                            <td class="px-6 py-4">
                                                <span class="font-bold text-gray-900 block">{{ $item->name }}</span>
                                                <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                                    <span class="font-semibold text-indigo-600 text-[9px] uppercase bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">{{ $item->category }}</span>
                                                    <span class="font-medium text-gray-600 text-[9px] uppercase bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                                        {{ $item->brand }} {{ $item->model ? '| '.$item->model : '' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                @if($item->is_low_stock)
                                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full font-bold text-xs flex items-center justify-center w-max mx-auto gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                        {{ $item->stock }} Pcs
                                                    </span>
                                                @else
                                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full font-bold text-xs inline-block">
                                                        {{ $item->stock }} Pcs
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 font-bold text-rose-600 text-right whitespace-nowrap">
                                                ৳{{ number_format($item->purchase_price, 2) }}
                                            </td>
                                            <td class="px-6 py-4 font-black text-emerald-600 text-right whitespace-nowrap">
                                                ৳{{ number_format($item->selling_price, 2) }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this part from stock?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-rose-600 bg-gray-50 hover:bg-rose-100 p-2 rounded-lg transition border border-gray-200 hover:border-rose-200 shadow-sm cursor-pointer" title="Delete Part">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 bg-gray-50/50">
                                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                                <p class="text-sm font-bold text-gray-500">No items in stock yet!</p>
                                                <p class="text-xs mt-1">Add new parts using the form on the left.</p>
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

    <!-- Script for Auto-Stripping Brand from Model -->
    <script>
        function stripBrandFromModel() {
            let brandInput = document.getElementById('brand_input');
            let modelInput = document.getElementById('model_input');

            if(brandInput.value.trim() !== '' && modelInput.value.trim() !== '') {
                let brandRegex = new RegExp('^' + brandInput.value.trim() + '\\s+', 'i');
                modelInput.value = modelInput.value.replace(brandRegex, '');
            }
        }
    </script>
</x-app-layout>
