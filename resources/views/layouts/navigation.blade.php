<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-indigo-600" />
                    </a>
                </div>

                <!-- Navigation Links (Fixed Double Inventory Issue) -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('devices.index')" :active="request()->routeIs('devices.*')">
                        {{ __('Repairs') }}
                    </x-nav-link>
                    <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                        {{ __('Expenses') }}
                    </x-nav-link>
                    <x-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')">
                        {{ __('Inventory') }}
                    </x-nav-link>
                    <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                        {{ __('Customers') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- SMART SCANNER INPUT (Fixed Visibility for Medium Screens) -->
            <div class="hidden md:flex items-center flex-1 justify-center px-4 lg:px-6">
                <form id="globalScannerForm" class="w-full max-w-[320px] relative" onsubmit="return handleGlobalScan(event)">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    </div>
                    <input type="text" id="globalScannerInput"
                        class="block w-full pl-10 pr-14 py-1.5 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-xs font-semibold shadow-sm"
                        placeholder="Scan QR or Tracking ID"
                        autocomplete="off">
                    <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center pointer-events-none">
                        <span class="bg-gray-200 text-gray-500 rounded px-1.5 py-0.5 text-[9px] font-bold border border-gray-300">Ctrl+Q</span>
                    </div>
                </form>
            </div>

            <!-- Notifications & Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">

                <!-- 0. Delivery Reminder Dropdown (Width: 270px) -->
                @php
                    $upcomingDeliveryLimit = \Carbon\Carbon::today()->addDays(7)->toDateString();
                    $deliveryNotifications = \App\Models\Device::with('customer')
                        ->where('shop_id', auth()->user()->shop_id ?? 1)
                        ->whereNotIn('status', ['delivered', 'returned'])
                        ->whereNotNull('estimated_delivery_date')
                        ->whereDate('estimated_delivery_date', '<=', $upcomingDeliveryLimit)
                        ->orderBy('estimated_delivery_date', 'asc')
                        ->get();
                    $deliveryCount = $deliveryNotifications->count();
                @endphp

                <x-dropdown align="right" contentClasses="py-1 bg-white min-w-max w-[270px]">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center p-2 border border-transparent rounded-md bg-white focus:outline-none transition ease-in-out duration-150 {{ $deliveryCount > 0 ? 'text-indigo-600 hover:text-indigo-800' : 'text-gray-400 hover:text-gray-600' }}">
                            <div class="relative flex items-center justify-center w-6 h-6">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                </svg>
                                @if($deliveryCount > 0)
                                    <span class="absolute -top-1.5 -right-2 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-indigo-600 border-2 border-white rounded-full box-content">{{ $deliveryCount }}</span>
                                @endif
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs font-bold uppercase tracking-wider border-b border-gray-100 {{ $deliveryCount > 0 ? 'text-indigo-600' : 'text-gray-500' }}">
                            ডেলিভারি শিডিউল
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            @forelse($deliveryNotifications as $notification)
                                @php
                                    $delDate = \Carbon\Carbon::parse($notification->estimated_delivery_date);
                                @endphp
                                <x-dropdown-link :href="route('devices.show', $notification->tracking_code)" class="border-b border-gray-50 last:border-0 !py-2 !px-3 hover:bg-gray-50">
                                    <div class="flex justify-between items-start gap-1">
                                        <div class="flex flex-col overflow-hidden">
                                            <span class="text-xs font-bold text-gray-800 truncate">{{ $notification->customer->name ?? 'Customer' }}</span>
                                            <span class="text-[9px] text-gray-500 truncate">{{ $notification->brand }} {{ trim(preg_replace('/^' . preg_quote($notification->brand, '/') . '\s+/i', '', $notification->model)) }}</span>
                                        </div>
                                        <div class="flex flex-col items-end flex-shrink-0">
                                            @if($delDate->isPast() && !$delDate->isToday()) <span class="text-[9px] text-white bg-red-500 px-1 py-0.5 rounded font-bold">ওভারডিউ</span>
                                            @elseif($delDate->isToday()) <span class="text-[9px] text-white bg-amber-500 px-1 py-0.5 rounded font-bold">আজকে</span>
                                            @elseif($delDate->isTomorrow()) <span class="text-[9px] text-indigo-700 bg-indigo-100 px-1 py-0.5 rounded font-bold">আগামীকাল</span>
                                            @else <span class="text-[9px] text-gray-700 bg-gray-100 px-1 py-0.5 rounded font-bold">{{ $delDate->format('d M') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </x-dropdown-link>
                            @empty
                                <div class="px-4 py-6 text-xs text-gray-500 text-center">কোনো পেন্ডিং ডেলিভারি নেই।</div>
                            @endforelse
                        </div>
                        <div class="border-t border-gray-100 bg-gray-50 rounded-b-md">
                            <x-dropdown-link :href="route('devices.index')" class="text-center text-xs font-bold text-indigo-600 !py-2 hover:bg-indigo-100">সব ডিভাইস &rarr;</x-dropdown-link>
                        </div>
                    </x-slot>
                </x-dropdown>

                <!-- 1. Notifications Dropdown (বকেয়া রিমাইন্ডার - Width: 220px) -->
                @php
                    $upcomingLimit = \Carbon\Carbon::today()->addDays(7)->toDateString();
                    $dueNotifications = \App\Models\Bill::with('customer')
                        ->where('shop_id', auth()->user()->shop_id ?? 1)
                        ->where('due_amount', '>', 0)
                        ->whereNotNull('promise_date')
                        ->whereDate('promise_date', '<=', $upcomingLimit)
                        ->orderBy('promise_date', 'asc')
                        ->get();
                    $dueCount = $dueNotifications->count();
                @endphp

                <x-dropdown align="right" contentClasses="py-1 bg-white min-w-max w-[220px]">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center p-2 border border-transparent rounded-md bg-white focus:outline-none transition ease-in-out duration-150 {{ $dueCount > 0 ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700' }}">
                            <div class="relative flex items-center justify-center w-6 h-6">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                @if($dueCount > 0)
                                    <span class="absolute -top-1.5 -right-2 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-600 border-2 border-white rounded-full box-content z-10">{{ $dueCount }}</span>
                                    <span class="absolute -top-1.5 -right-2 flex h-[18px] w-[18px]"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span></span>
                                @endif
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs font-bold uppercase tracking-wider border-b border-gray-100 {{ $dueCount > 0 ? 'text-red-600' : 'text-green-600' }}">
                            বকেয়া রিমাইন্ডার
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            @forelse($dueNotifications as $notification)
                                @php
                                    $promiseDate = \Carbon\Carbon::parse($notification->promise_date);
                                @endphp
                                <x-dropdown-link :href="route('devices.show', $notification->device_id)" class="border-b border-gray-50 last:border-0 !py-2 !px-3 hover:bg-gray-50">
                                    <div class="flex justify-between items-start gap-1">
                                        <div class="flex flex-col overflow-hidden">
                                            <span class="text-xs font-bold text-gray-800 truncate">{{ $notification->customer->name ?? 'Customer' }}</span>
                                            @if($promiseDate->isPast() && !$promiseDate->isToday()) <span class="text-[9px] text-red-500 font-bold">ওভারডিউ</span>
                                            @elseif($promiseDate->isToday()) <span class="text-[9px] text-amber-600 font-bold">আজকে</span>
                                            @elseif($promiseDate->isTomorrow()) <span class="text-[9px] text-blue-500 font-bold">আগামীকাল</span>
                                            @else <span class="text-[9px] text-gray-500 font-bold">{{ $promiseDate->format('d M') }}</span>
                                            @endif
                                        </div>
                                        <span class="text-xs font-bold text-red-600 flex-shrink-0">৳{{ number_format($notification->due_amount, 0) }}</span>
                                    </div>
                                </x-dropdown-link>
                            @empty
                                <div class="px-4 py-6 text-xs text-gray-500 text-center">কোনো রিমাইন্ডার নেই। আলহামদুলিল্লাহ!</div>
                            @endforelse
                        </div>
                        <div class="border-t border-gray-100 bg-gray-50 rounded-b-md">
                            <x-dropdown-link :href="route('bills.due-list')" class="text-center text-xs font-bold text-indigo-600 !py-2 hover:bg-indigo-100">সব বাকির খাতা &rarr;</x-dropdown-link>
                        </div>
                    </x-slot>
                </x-dropdown>

                <!-- 2. Settings Dropdown (আপনার প্রোফাইল) -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->shop?->name }} | {{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile Menu) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('devices.index')" :active="request()->routeIs('devices.*')">
                {{ __('Repairs') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                {{ __('Expenses') }}
            </x-responsive-nav-link>
            <!-- Missing Inventory Link in Mobile Menu has been Fixed Here -->
            <x-responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')">
                {{ __('Inventory') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                {{ __('Customers') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->shop?->name }} | {{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    function handleGlobalScan(e) {
        e.preventDefault();
        let input = document.getElementById('globalScannerInput');
        let val = input.value.trim();
        if(val) {
            let match = val.match(/DEV-[A-Z0-9]+/i);
            if(match) {
                let trackingCode = match[0].toUpperCase();
                window.location.href = '/devices/' + trackingCode;
            } else {
                alert('এই স্ক্যানারটি শুধুমাত্র QR Code স্ক্যান করার জন্য।');
                input.value = '';
                input.focus();
            }
        }
    }
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && (e.key === 'q' || e.key === 'Q')) {
            e.preventDefault();
            document.getElementById('globalScannerInput').focus();
        }
    });
</script>
