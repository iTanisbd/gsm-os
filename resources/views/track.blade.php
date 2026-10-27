<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Device | {{ $device->tracking_code }}</title>
    <!-- Tailwind CSS via CDN for Public Page -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Bengali', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-gray-800 antialiased pb-12">

    <!-- Top Navigation / Banner -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 shadow-md">
        <div class="max-w-lg mx-auto px-4 py-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-white/20 rounded-full mb-3 text-white backdrop-blur-sm border border-white/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h1 class="text-xl font-bold text-white tracking-wide uppercase">Live Repair Status</h1>
            <p class="text-indigo-100 text-sm mt-1">{{ $device->shop->name ?? 'Anis Telecom' }}</p>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 -mt-4 relative z-10">

        <!-- Device Info Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-5 mb-5">
            <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Tracking ID</p>
                    <p class="text-lg font-black text-indigo-700 font-mono">{{ $device->tracking_code }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Received Date</p>
                    <p class="text-sm font-bold text-gray-800">{{ $device->created_at->format('d M, Y') }}</p>
                </div>
            </div>

            <div class="flex gap-4 items-center">
                <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-500 flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $device->brand }} {{ preg_replace('/^' . preg_quote($device->brand, '/') . '\s+/i', '', $device->model) }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Customer: <strong>{{ Str::limit($device->customer->name, 15) }}</strong></p>
                </div>
            </div>
        </div>

        @php
            $status = $device->status;
            // Determine active step (1 to 4)
            $step = 1;
            if ($status === 'processing') $step = 2;
            if ($status === 'completed') $step = 3;
            if (in_array($status, ['delivered', 'returned'])) $step = 4;

            $isReturned = $status === 'returned';
        @endphp

        <!-- Status Timeline Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-5">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-6 border-l-4 border-indigo-500 pl-2">Tracking Timeline</h3>

            <div class="relative">
                <!-- Vertical Line -->
                <div class="absolute left-[15px] top-2 bottom-2 w-0.5 bg-gray-200"></div>

                <!-- Step 1: Received -->
                <div class="flex items-start mb-6 relative">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 {{ $step >= 1 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-gray-200 text-gray-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="ml-4 pt-1">
                        <h4 class="text-sm font-bold {{ $step >= 1 ? 'text-gray-900' : 'text-gray-400' }}">Device Received</h4>
                        <p class="text-xs text-gray-500 mt-1">আপনার ডিভাইসটি আমরা বুঝে পেয়েছি।</p>
                    </div>
                </div>

                <!-- Step 2: Processing -->
                <div class="flex items-start mb-6 relative">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 {{ $step >= 2 ? 'bg-blue-500 text-white shadow-md shadow-blue-200' : 'bg-gray-200 text-gray-400' }}">
                        @if($step >= 2) <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> @endif
                    </div>
                    <div class="ml-4 pt-1">
                        <h4 class="text-sm font-bold {{ $step >= 2 ? 'text-blue-600' : 'text-gray-400' }}">Processing / Repairing</h4>
                        <p class="text-xs {{ $step == 2 ? 'text-blue-500' : 'text-gray-500' }} mt-1">টেকনিশিয়ান আপনার ডিভাইসের কাজ করছেন।</p>
                    </div>
                </div>

                <!-- Step 3: Completed / Ready -->
                <div class="flex items-start mb-6 relative">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 {{ $step >= 3 ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : 'bg-gray-200 text-gray-400' }}">
                        @if($step >= 3) <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> @endif
                    </div>
                    <div class="ml-4 pt-1">
                        <h4 class="text-sm font-bold {{ $step >= 3 ? 'text-emerald-600' : 'text-gray-400' }}">Repair Completed</h4>
                        <p class="text-xs {{ $step == 3 ? 'text-emerald-500 font-semibold' : 'text-gray-500' }} mt-1">কাজ শেষ! আপনি ডিভাইসটি নিতে আসতে পারেন।</p>
                    </div>
                </div>

                <!-- Step 4: Delivered or Returned -->
                <div class="flex items-start relative">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 {{ $step == 4 ? ($isReturned ? 'bg-rose-500 shadow-rose-200' : 'bg-gray-800 shadow-gray-300') : 'bg-gray-200' }} text-white shadow-md">
                        @if($step == 4 && !$isReturned) <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> @endif
                        @if($step == 4 && $isReturned) <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> @endif
                    </div>
                    <div class="ml-4 pt-1">
                        @if($isReturned)
                            <h4 class="text-sm font-bold text-rose-600">Returned Unrepaired</h4>
                            <p class="text-xs text-rose-500 mt-1 font-semibold">ডিভাইসটি কাজ ছাড়া ফেরত দেওয়া হয়েছে।</p>
                        @else
                            <h4 class="text-sm font-bold {{ $step == 4 ? 'text-gray-900' : 'text-gray-400' }}">Delivered</h4>
                            <p class="text-xs text-gray-500 mt-1">ডিভাইসটি কাস্টমারকে বুঝিয়ে দেওয়া হয়েছে।</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Info Card (Visible only if Bill is generated) -->
        @if($bill)
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 overflow-hidden relative">
            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-green-100 to-transparent flex items-start justify-end p-2 rounded-bl-3xl">
                <span class="text-xl">💰</span>
            </div>

            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-l-4 border-green-500 pl-2">Financial Summary</h3>

            <div class="space-y-3">
                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Total Bill</span>
                    <span class="text-base font-bold text-gray-800">৳{{ number_format($bill->total_amount, 0) }}</span>
                </div>

                @if($bill->discount > 0)
                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Discount</span>
                    <span class="text-sm font-bold text-red-500">- ৳{{ number_format($bill->discount, 0) }}</span>
                </div>
                @endif

                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Advance Paid</span>
                    <span class="text-sm font-bold text-emerald-600">৳{{ number_format($bill->paid_amount, 0) }}</span>
                </div>

                <div class="flex justify-between items-center pt-1">
                    <span class="text-sm font-black text-gray-900 uppercase">Total Due</span>
                    <span class="text-xl font-black {{ $bill->due_amount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        ৳{{ number_format($bill->due_amount, 0) }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        <!-- Footer Notice -->
        <div class="text-center mt-8 text-xs text-gray-400">
            <p>Thank you for choosing Anis Telecom!</p>
            <p class="mt-1 opacity-60">System by Anisur Rahman</p>
        </div>
    </div>
</body>
</html>
