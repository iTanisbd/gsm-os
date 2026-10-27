<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Device Details & Repair Log') }}
            </h2>
            <div class="flex items-center gap-3">
                <!-- PRINT BUTTON ADDED HERE -->
                @if($bill)
                    <a href="{{ route('bills.print', $bill->id) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-bold shadow-sm transition flex items-center gap-2">
                        🖨️ Print Invoice
                    </a>
                @endif
                <a href="{{ route('devices.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-semibold transition">
                    &larr; Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Smart PHP Logic -->
    @php
        // Global helper for generating dynamic WhatsApp message URL
        if (!function_exists('getWhatsAppUrl')) {
            function getWhatsAppUrl($device) {
                $phone = preg_replace('/[^0-9]/', '', $device->customer->phone ?? '');
                if (str_starts_with($phone, '01') && strlen($phone) == 11) {
                    $phone = '88' . $phone;
                }
                $bill = $device->bills->first();
                $dueText = ($bill && $bill->due_amount > 0) ? " আপনার বকেয়া বিল ৳" . number_format($bill->due_amount, 0) . "।" : "";
                $cleanModel = trim(preg_replace('/^' . preg_quote($device->brand, '/') . '\s+/i', '', $device->model));
                $shopName = auth()->user()->shop->name ?? 'Anis Telecom';
                $trackingUrl = url('/track/' . $device->tracking_code);

                $msg = "আসসালামু আলাইকুম " . ($device->customer->name ?? '') . " ভাই,\n\nআপনার {$device->brand} {$cleanModel} ফোনটির মেরামত সফলভাবে সম্পন্ন হয়েছে।{$dueText} দয়া করে আপনার সুবিধামতো সময়ে ফোনটি দোকান থেকে সংগ্রহ করুন।\n\n📌 আপনার ডিভাইসের লাইভ স্ট্যাটাস দেখতে নিচের লিংকে ক্লিক করুন:\n{$trackingUrl}\n\nধন্যবাদ!\n- {$shopName}";

                return "https://api.whatsapp.com/send?phone={$phone}&text=" . urlencode($msg);
            }
        }

        $cancelledItems = [];
        $hardwareItems = [];
        $softwareItems = [];
        $totalItemsCount = 0;

        $softwareKeywords = [
            'unlock', 'frp', 'bypass', 'mi account', 'icloud', 'flashing', 'flash',
            'dead boot', 'firmware', 'software', 'update', 'reset', 'country', 'hang', 'lock'
        ];

        if($bill && $bill->items) {
            $totalItemsCount = $bill->items->count();
            foreach($bill->items as $item) {
                $cleanDesc = str_replace('[Cancelled] ', '', $item->description);

                if(str_starts_with($item->description, '[Cancelled] ')) {
                    $cancelledItems[] = $cleanDesc;
                }

                $itemType = strtolower($item->type ?? $item->category ?? '');
                $isSw = false;

                if ($itemType === 'software') {
                    $isSw = true;
                } elseif ($itemType === 'hardware') {
                    $isSw = false;
                } else {
                    if (!empty($item->warranty) && strtolower($item->warranty) !== 'none') {
                        $isSw = false;
                    } else {
                        foreach ($softwareKeywords as $kw) {
                            if (stripos($cleanDesc, $kw) !== false) {
                                $isSw = true;
                                break;
                            }
                        }
                    }
                }

                if ($isSw) {
                    $softwareItems[] = $item;
                } else {
                    $hardwareItems[] = $item;
                }
            }
        }

        $cancelledCount = count($cancelledItems);
        $activeCount = $totalItemsCount - $cancelledCount;
        $hasCancelled = $cancelledCount > 0;

        if ($totalItemsCount > 0 && $cancelledCount === $totalItemsCount) {
            $defaultOutcome = 'returned_unrepaired';
            $autoNote = "All Services Cancelled (Returned Unrepaired): " . implode(', ', $cancelledItems);
        } elseif ($cancelledCount > 0 && $activeCount > 0) {
            $defaultOutcome = 'partially_repaired';
            $autoNote = "Cancelled Services: " . implode(', ', $cancelledItems);
        } else {
            $defaultOutcome = 'success';
            $autoNote = '';
        }

        // Clean Duplicate Brand from Model
        $cleanModelName = trim(preg_replace('/^' . preg_quote($device->brand, '/') . '\s+/i', '', $device->model));

        // Clean Duplicate Password Prefixes
        $rawPass = $device->device_password ?? 'NONE';
        $rawPass = preg_replace('/^(Pattern:\s*)+/i', 'Pattern: ', $rawPass);
        $rawPass = preg_replace('/^(PIN:\s*)+/i', 'PIN: ', $rawPass);

        // Smart Note Parsing (Extracting Additional Updates & Merges)
        $cleanBillNote = $bill && $bill->note ? trim($bill->note) : '';
        $resolvedUpdates = [];
        $skippedUpdates = [];

        if (preg_match_all('/\[ADDITIONAL_RESOLVED:\s*(.*?)\]/i', $cleanBillNote, $matches)) {
            $resolvedUpdates = array_merge($resolvedUpdates, $matches[1]);
            $cleanBillNote = preg_replace('/\[ADDITIONAL_RESOLVED:\s*.*?\]/i', '', $cleanBillNote);
        }
        if (preg_match_all('/\[(?:ADDITIONAL_SKIPPED|Additional Issue Not Repaired|অতিরিক্ত কাজ করা হয়নি):\s*(.*?)\s*(?:\||-)\s*(?:REASON|Reason|কারণ):\s*(.*?)\]/iu', $cleanBillNote, $matches)) {
            foreach ($matches[1] as $index => $issue) {
                $skippedUpdates[] = trim($issue) . ' (Reason: ' . trim($matches[2][$index]) . ')';
            }
            $cleanBillNote = preg_replace('/\[(?:ADDITIONAL_SKIPPED|Additional Issue Not Repaired|অতিরিক্ত কাজ করা হয়নি):\s*.*?\]/iu', '', $cleanBillNote);
        }
        $cleanBillNote = trim($cleanBillNote);

        // -------------------------------------------------------------
        // Actual Fault Sync Logic (Detects Unbilled Faults)
        // -------------------------------------------------------------
        $unbilledFaults = [];
        if ($bill && !empty($device->actual_fault)) {
            $parsedFaults = json_decode($device->actual_fault, true);
            $existingBillItemNames = $bill->items->pluck('description')->map(function($name) {
                return strtolower(str_replace('[Cancelled] ', '', trim($name)));
            })->toArray();

            $hwFaults = $parsedFaults['hw'] ?? [];
            $swFaults = $parsedFaults['sw'] ?? [];

            foreach ($hwFaults as $hwFault) {
                if (!in_array(strtolower(trim($hwFault)), $existingBillItemNames)) {
                    $unbilledFaults[] = ['name' => $hwFault, 'type' => 'hardware'];
                }
            }
            foreach ($swFaults as $swFault) {
                if (!in_array(strtolower(trim($swFault)), $existingBillItemNames)) {
                    $unbilledFaults[] = ['name' => $swFault, 'type' => 'software'];
                }
            }
        }

        // JSON Parser for formatToSerialList
        if (!function_exists('escapeHtml')) {
            function escapeHtml($str) {
                return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
            }
        }

        if (!function_exists('formatToSerialList')) {
            function formatToSerialList($rawText, $emptyFallback = 'N/A') {
                if (!$rawText || trim($rawText) === '') {
                    return '<span class="text-gray-400 italic font-normal">' . escapeHtml($emptyFallback) . '</span>';
                }

                $hw = []; $sw = [];
                $data = json_decode($rawText, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $hw = $data['hw'] ?? [];
                    $sw = $data['sw'] ?? [];
                } else {
                    $lines = explode("\n", $rawText);
                    foreach($lines as $line) {
                        $cleanLine = trim(preg_replace('/^\d+[\.\)\-]\s*/', '', $line));
                        if(!$cleanLine) continue;

                        if (stripos($cleanLine, '[SW]') !== false || stripos($cleanLine, '[Software]') !== false) {
                            $sw[] = trim(str_ireplace(['[SW]', '[Software]'], '', $cleanLine));
                        } elseif (stripos($cleanLine, '[HW]') !== false || stripos($cleanLine, '[Hardware]') !== false) {
                            $hw[] = trim(str_ireplace(['[HW]', '[Hardware]'], '', $cleanLine));
                        } else {
                            $hw[] = $cleanLine;
                        }
                    }
                }

                $output = '';
                if (count($hw) > 0) {
                    $output .= '<div class="mb-1.5"><span class="font-bold text-blue-700 text-[11px] block mb-0.5 uppercase tracking-wider">A. Hardware :-</span>';
                    foreach($hw as $idx => $item) {
                        $output .= '<div class="ml-2 flex items-start gap-1 text-[13px] text-gray-800 leading-snug"><span class="font-bold select-none">'.($idx+1).'.</span><span>'.escapeHtml($item).'</span></div>';
                    }
                    $output .= '</div>';
                }
                if (count($sw) > 0) {
                    $output .= '<div><span class="font-bold text-green-700 text-[11px] block mb-0.5 uppercase tracking-wider">B. Software :-</span>';
                    foreach($sw as $idx => $item) {
                        $output .= '<div class="ml-2 flex items-start gap-1 text-[13px] text-gray-800 leading-snug"><span class="font-bold select-none">'.($idx+1).'.</span><span>'.escapeHtml($item).'</span></div>';
                    }
                    $output .= '</div>';
                }

                if ($output === '') {
                    return '<span class="text-gray-400 italic font-normal">' . escapeHtml($emptyFallback) . '</span>';
                }

                return $output;
            }
        }

        $rawTerms = is_array($device->terms) ? json_encode($device->terms) : (string) $device->terms;
        $deadRiskAccepted = str_contains($rawTerms, 'Dead Risk Accepted') && !str_contains($rawTerms, 'Dead Risk Not Accepted');
        $dataLossAccepted = str_contains($rawTerms, 'Data Loss Accepted') && !str_contains($rawTerms, 'Data Loss Not Accepted');
    @endphp

    <div class="py-8 relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start mt-8">

                <!-- Left Column: Device & Customer Info -->
                <div class="space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-indigo-500">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Customer Info</h3>
                        <p class="text-gray-700"><strong>Name:</strong> {{ $device->customer->name }}</p>
                        <p class="text-gray-700"><strong>Phone:</strong> {{ $device->customer->phone }}</p>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-blue-500">
                        <!-- WhatsApp Button and Device Info Header -->
                        <div class="flex justify-between items-center border-b pb-2 mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Device Info</h3>
                            @if($device->status == 'completed')
                                <a href="{{ getWhatsAppUrl($device) }}" target="_blank" title="Notify Customer via WhatsApp" class="text-xs bg-emerald-50 text-emerald-600 hover:text-emerald-900 hover:bg-emerald-100 px-2.5 py-1.5 rounded-md font-bold border border-emerald-200 transition flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                    WhatsApp
                                </a>
                            @else
                                <span title="Enabled after 'Completed'" class="text-xs bg-gray-50 text-gray-400 px-2.5 py-1.5 rounded-md font-bold border border-gray-200 flex items-center gap-1.5 shadow-sm cursor-not-allowed opacity-60">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                    WhatsApp
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-700"><strong>Brand & Model:</strong> {{ $device->brand }} {{ $cleanModelName }}</p>
                        <p class="text-gray-700"><strong>IMEI:</strong> {{ $device->imei ?? 'N/A' }}</p>
                        <p class="text-gray-700 mt-2 text-red-600"><strong>Password:</strong> <span class="font-mono bg-red-50 px-1 rounded border border-red-100">{{ $rawPass }}</span></p>
                        <p class="text-gray-700 mt-2"><strong>Rack/Drawer:</strong> {{ $device->rack_number ?? 'N/A' }} / {{ $device->drawer_number ?? 'N/A' }}</p>
                        @if($device->estimated_delivery_date)
                            <p class="text-gray-700 mt-2"><strong>Delivery Date:</strong> <span class="text-indigo-600 font-bold">{{ \Carbon\Carbon::parse($device->estimated_delivery_date)->format('d M, Y') }}</span></p>
                        @endif
                        <p class="text-gray-700 mt-2 flex items-center gap-2"><strong>Status:</strong>
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full uppercase
                                {{ $device->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $device->status == 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $device->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $device->status == 'delivered' ? 'bg-gray-200 text-gray-800' : '' }}
                                {{ $device->status == 'returned' ? 'bg-red-100 text-red-800' : '' }}
                            ">
                                {{ $device->status }}
                            </span>
                        </p>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-5 border-t-4 border-amber-500">
                        <h3 class="text-md font-bold text-gray-800 border-b pb-2 mb-3">Pre-Repair Agreements</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded border border-gray-100">
                                <span class="text-[11px] font-bold text-gray-700 uppercase">1. Dead Risk:</span>
                                @if($deadRiskAccepted)
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">ACCEPTED</span>
                                @else
                                    <span class="text-[10px] font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded">NOT ACCEPTED</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded border border-gray-100">
                                <span class="text-[11px] font-bold text-gray-700 uppercase">2. Data Loss:</span>
                                @if($dataLossAccepted)
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">ACCEPTED</span>
                                @else
                                    <span class="text-[10px] font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded">NOT ACCEPTED</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-red-500">
                        <h3 class="text-md font-bold text-gray-800 border-b pb-2 mb-3">Initial Faults & Complaints</h3>
                        <div class="mb-4">
                            <span class="text-xs text-gray-500 font-bold uppercase mb-1 block">Customer Complaint:</span>
                            <div class="bg-gray-50 p-3 rounded border text-left">
                                {!! formatToSerialList($device->problem_description) !!}
                            </div>
                        </div>
                        @if($device->additional_complaint && $device->additional_complaint !== '{"hw":[],"sw":[]}')
                            <div class="mb-4">
                                <span class="text-xs text-indigo-600 font-bold uppercase mb-1 block">Additional Info:</span>
                                <div class="bg-indigo-50 p-3 rounded border border-indigo-200 text-left">
                                    {!! formatToSerialList($device->additional_complaint) !!}
                                </div>
                            </div>
                        @endif
                        <div>
                            <span class="text-xs text-gray-500 font-bold uppercase mb-1 block">Actual Fault:</span>
                            <div class="bg-gray-50 p-3 rounded border text-left">
                                {!! formatToSerialList($device->actual_fault, 'Not specified') !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Column: Repair Log -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 h-full">
                    <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-6">
                        <svg class="w-5 h-5 inline-block text-indigo-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Repair Log
                    </h3>

                    @if(!in_array($device->status, ['delivered', 'returned']))
                        <div class="mb-8 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <form method="POST" action="{{ route('devices.repair-log', $device->tracking_code) }}">
                                @csrf
                                <x-input-label for="note" :value="__('Add Update')" class="text-indigo-600 font-bold" />
                                <textarea id="note" name="note" class="block mt-2 w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm" rows="2" required placeholder="Write update here..."></textarea>
                                <div class="mt-3 flex justify-end">
                                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 text-xs py-1.5">Post</x-primary-button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="mb-8 bg-gray-50 p-4 rounded-lg border border-gray-200 text-center shadow-sm">
                            <svg class="w-8 h-8 {{ $device->status == 'returned' ? 'text-red-500' : 'text-green-500' }} mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h4 class="text-md font-bold {{ $device->status == 'returned' ? 'text-red-800' : 'text-green-800' }} capitalize">{{ $device->status }}</h4>
                            <p class="text-xs text-gray-600 mt-1">Delivery/Return is completed. Adding new repair logs is closed.</p>
                        </div>
                    @endif

                    <div class="space-y-4">
                        @forelse($device->repairLogs as $log)
                            <div class="flex items-start p-3 bg-white border border-gray-100 shadow-sm rounded-lg relative">
                                <div class="absolute -left-2 top-4 w-4 h-4 bg-indigo-500 rounded-full border-2 border-white shadow"></div>
                                <div class="border-l-2 border-indigo-200 pl-4 w-full ml-2">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-bold text-gray-800 text-sm">{{ $log->user->name }}</span>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $log->created_at->format('d M, Y - h:i A') }}</span>
                                    </div>
                                    <p class="text-gray-700 text-sm mt-1 whitespace-pre-wrap text-left">{{ $log->note }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300 text-sm">
                                No updates yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Right Column: Financial & Delivery -->
                <div class="space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-green-500">
                        <div class="flex justify-between items-center border-b pb-2 mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Financial Summary</h3>
                            @if($bill && !in_array($device->status, ['delivered', 'returned']))
                                <button type="button" onclick="openEditBillModal()" class="text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-2.5 py-1 rounded font-bold border border-indigo-200 transition cursor-pointer relative">
                                    Edit Bill
                                    @if(count($unbilledFaults) > 0)
                                        <span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span></span>
                                    @endif
                                </button>
                            @endif
                        </div>

                        @if($bill)
                            @php
                                $isRefundable = $bill->paid_amount > $bill->total_amount;
                                $refundAmount = $isRefundable ? ($bill->paid_amount - $bill->total_amount) : 0;
                            @endphp

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
                                    <span class="text-gray-600">Paid Amount:</span>
                                    <span class="font-bold text-green-600">৳{{ number_format($bill->paid_amount, 2) }}</span>
                                </div>

                                <!-- Post-Billing Additional Info Updates Display -->
                                @if(count($resolvedUpdates) > 0 || count($skippedUpdates) > 0)
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-xs text-left mt-2 space-y-2">
                                        <div class="border-b border-gray-200 pb-1">
                                            <span class="font-bold text-gray-700 uppercase tracking-wider text-[10px]">Additional Info Update</span>
                                        </div>

                                        @if(count($resolvedUpdates) > 0)
                                            <div class="mt-1">
                                                <span class="font-bold text-emerald-700 block mb-0.5">✓ Resolved (ঠিক করা হয়েছে):</span>
                                                <ul class="list-disc pl-4 text-gray-700">
                                                    @foreach($resolvedUpdates as $res)
                                                        <li>{{ $res }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if(count($skippedUpdates) > 0)
                                            <div class="mt-1">
                                                <span class="font-bold text-red-700 block mb-0.5">✕ Not Repaired (করা হয়নি):</span>
                                                <ul class="list-disc pl-4 text-gray-700">
                                                    @foreach($skippedUpdates as $skip)
                                                        <li>{{ $skip }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($cleanBillNote !== '')
                                    <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-md text-xs text-amber-900 mt-2 text-left">
                                        <span class="font-bold block mb-0.5 text-amber-800 uppercase tracking-wide text-[10px]">Special Note:</span>
                                        <div class="whitespace-pre-line text-left leading-relaxed">{{ $cleanBillNote }}</div>
                                    </div>
                                @endif

                                @if($isRefundable)
                                    <div class="flex justify-between bg-blue-50 p-2 rounded mt-2 items-center border border-blue-200">
                                        <span class="text-blue-800 font-bold">Refundable:</span>
                                        <span class="font-bold text-lg text-blue-700">৳{{ number_format($refundAmount, 2) }}</span>
                                    </div>
                                    <button type="button" onclick="openRefundModal()" class="w-full mt-4 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition shadow-sm cursor-pointer">
                                        Refund Money
                                    </button>
                                @else
                                    <div class="flex justify-between bg-gray-50 p-2 rounded mt-2 items-center">
                                        <span class="text-gray-800 font-bold">Due Amount:</span>
                                        <span class="font-bold text-lg {{ $bill->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                            ৳{{ number_format($bill->due_amount, 2) }}
                                        </span>
                                    </div>
                                    @if($bill->due_amount > 0)
                                        <div class="flex justify-between items-center border-t pt-2 mt-2">
                                            <span class="text-xs text-gray-500 font-bold uppercase">Promise Date:</span>
                                            @if($bill->promise_date)
                                                <span class="text-xs font-bold px-2 py-1 rounded bg-gray-100 text-gray-700">
                                                    {{ \Carbon\Carbon::parse($bill->promise_date)->format('d M, Y') }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 font-bold">Not Set</span>
                                            @endif
                                        </div>
                                        <button type="button" onclick="openCollectDueModal()" class="w-full mt-4 flex items-center justify-center gap-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold py-2 px-4 rounded border border-emerald-300 transition cursor-pointer">
                                            Collect Due
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @else
                            <div class="text-center py-4 bg-gray-50 rounded text-gray-500 text-sm">
                                No bill generated yet.<br>
                                <a href="{{ route('bills.create') }}" class="text-indigo-600 hover:underline mt-2 inline-block font-bold">Create Bill &rarr;</a>
                            </div>
                        @endif
                    </div>

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

                    @if(!in_array($device->status, ['delivered', 'returned']))
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-purple-500">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Complete Delivery</h3>
                            <form method="POST" action="{{ route('devices.mark-delivered', $device->tracking_code) }}" id="mainDeliveryForm">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-xs font-bold uppercase text-gray-600 mb-2">Repair Outcome *</label>
                                    <div class="flex flex-col gap-2">
                                        <label class="flex items-center gap-3 p-2.5 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-green-600 has-[:checked]:bg-green-50 transition">
                                            <input type="radio" name="repair_outcome" value="success" id="radio_success" class="text-green-600 focus:ring-green-500" {{ $defaultOutcome === 'success' ? 'checked' : '' }} onchange="syncOutcome(this.value)">
                                            <div>
                                                <span class="block text-sm font-bold text-gray-800">Repaired Successfully</span>
                                            </div>
                                        </label>

                                        <label class="flex items-center gap-3 p-2.5 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50 transition">
                                            <input type="radio" name="repair_outcome" value="partially_repaired" id="radio_partially" class="text-yellow-500 focus:ring-yellow-500" {{ $defaultOutcome === 'partially_repaired' ? 'checked' : '' }} onchange="syncOutcome(this.value)">
                                            <div>
                                                <span class="block text-sm font-bold text-gray-800">Partially Repaired</span>
                                            </div>
                                        </label>

                                        <label class="flex items-center gap-3 p-2.5 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-red-600 has-[:checked]:bg-red-50 transition">
                                            <input type="radio" name="repair_outcome" value="returned_unrepaired" id="radio_returned" class="text-red-600 focus:ring-red-500" {{ $defaultOutcome === 'returned_unrepaired' ? 'checked' : '' }} onchange="syncOutcome(this.value)">
                                            <div>
                                                <span class="block text-sm font-bold text-gray-800">Returned Unrepaired</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4 {{ $defaultOutcome === 'returned_unrepaired' && $bill && ($bill->total_amount > 0 || $bill->paid_amount > 0) ? '' : 'hidden' }}" id="return_unrepaired_action_container">
                                    <label class="flex items-center gap-3 p-3 bg-red-50 border border-red-200 rounded-lg cursor-pointer">
                                        <input type="checkbox" name="auto_refund_and_cancel" id="auto_refund_and_cancel" value="1" class="text-red-600 focus:ring-red-500 w-5 h-5">
                                        <div class="flex-1">
                                            <span class="block text-sm font-bold text-red-800">Cancel all works & refund advance balance</span>
                                            <span class="block text-xs text-red-600 mt-0.5">Automatically sets bill to ৳0 and records full refund</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="mb-4 {{ $defaultOutcome !== 'success' ? '' : 'hidden' }}" id="repair_note_container">
                                    <label class="block text-xs font-bold uppercase text-red-600 mb-1.5">
                                        Repair / Return Details Note <span class="text-red-500 {{ $defaultOutcome === 'partially_repaired' ? '' : 'hidden' }}" id="repair_note_asterisk">*</span>
                                    </label>
                                    <textarea name="repair_note" id="repair_note" rows="2" class="block w-full text-sm rounded-md border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-left" placeholder="Write details here..." {{ $defaultOutcome === 'partially_repaired' ? 'required' : '' }}>{{ old('repair_note', $autoNote) }}</textarea>
                                </div>

                                <div class="flex flex-wrap gap-4 mb-4">
                                    <div class="flex-1 min-w-[140px]">
                                        <x-input-label for="delivered_by_name" :value="__('Delivered By *')" class="font-bold text-gray-700 text-sm" />
                                        <x-text-input id="delivered_by_name" name="delivered_by_name" type="text" class="block w-full text-sm font-semibold mt-1" value="Admin" required />
                                    </div>
                                    <div class="flex-1 min-w-[140px]">
                                        <x-input-label for="received_by_name" :value="__('Received By *')" class="font-bold text-gray-700 text-sm" />
                                        <x-text-input id="received_by_name" name="received_by_name" type="text" class="block w-full text-sm font-semibold mt-1" value="{{ $device->customer->name }}" required />
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition shadow-md cursor-pointer">
                                        {{ __('SUBMIT & DELIVER') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-gray-300 text-center">
                            <h3 class="text-lg font-bold text-gray-800">Delivery Completed</h3>
                            <p class="text-sm text-gray-500 mt-2">Delivered to: <strong>{{ $device->received_by_name }}</strong></p>
                            <p class="text-sm text-gray-500">By: <strong>{{ $device->delivered_by_name }}</strong></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modals (Smart Alert, Due, Collect, Refund, Edit Bill) -->
    <div id="smartAlertModal" class="fixed inset-0 z-[110] hidden overflow-y-auto bg-gray-900 bg-opacity-60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full sm:my-8" style="max-width: 28rem !important; margin: 0 auto;">
                <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-200 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-yellow-800">Warning!</h3>
                </div>
                <div class="p-6 text-gray-700 font-medium text-sm whitespace-pre-wrap leading-relaxed" id="smartAlertMessage"></div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-xl border-t border-gray-100">
                    <button type="button" onclick="closeSmartAlert()" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 font-bold hover:bg-gray-50 text-sm cursor-pointer">Cancel</button>
                    <button type="button" id="smartAlertConfirmBtn" class="px-4 py-2 bg-yellow-500 text-white rounded-md font-bold hover:bg-yellow-600 shadow-sm text-sm cursor-pointer">Proceed</button>
                </div>
            </div>
        </div>
    </div>

    @if($bill && $bill->due_amount > 0)
    <div id="dueCommitmentModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-gray-900 bg-opacity-60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full sm:my-8" style="max-width: 28rem !important; margin: 0 auto;">
                <div class="bg-red-50 px-6 py-4 border-b border-red-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-red-800 flex items-center gap-2">Unpaid Bill Alert!</h3>
                    <button type="button" onclick="closeDueModal()" class="text-red-400 hover:text-red-700 bg-transparent rounded-lg p-1.5 ml-auto cursor-pointer">✕</button>
                </div>
                <form id="dueDeliveryForm" method="POST" action="{{ route('devices.mark-delivered', $device->tracking_code) }}" class="px-6 pt-5 pb-6">
                    @csrf
                    <input type="hidden" name="delivered_by_name" id="modal_delivered_by">
                    <input type="hidden" name="received_by_name" id="modal_received_by">
                    <input type="hidden" name="repair_outcome" id="modal_repair_outcome" value="success">
                    <input type="hidden" name="repair_note" id="modal_repair_note" value="">
                    <input type="hidden" name="has_due_commitment" value="1">
                    <div class="text-center mb-6">
                        <p class="text-sm text-gray-600 mb-2">This device has an unpaid due balance of:</p>
                        <p class="text-3xl font-black text-red-600 tracking-tight">৳{{ number_format($bill->due_amount, 2) }}</p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Promised Payment Date <span class="text-red-500">*</span></label>
                            <input type="date" name="promise_date" min="{{ date('Y-m-d') }}" class="block w-full rounded-md border border-gray-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 sm:text-sm" required>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-col gap-3">
                        <button type="submit" class="w-full justify-center rounded-md bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition-all cursor-pointer">Deliver with Due Balance</button>
                        <button type="button" onclick="closeDueModal()" class="w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 transition-all cursor-pointer">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="collectDueModal" class="fixed inset-0 z-[105] hidden overflow-y-auto bg-gray-900 bg-opacity-60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full sm:my-8" style="max-width: 28rem !important; margin: 0 auto;">
                <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-emerald-800 flex items-center gap-2">Collect Due Payment</h3>
                    <button type="button" onclick="closeCollectDueModal()" class="text-emerald-400 hover:text-emerald-700 bg-transparent rounded-lg p-1.5 ml-auto cursor-pointer">✕</button>
                </div>
                <form method="POST" action="{{ route('bills.add-transaction', $bill->id) }}" class="px-6 pt-5 pb-6">
                    @csrf
                    <input type="hidden" name="type" value="due_payment">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Amount to Collect <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><span class="text-gray-500 font-bold">৳</span></div>
                            <input type="number" name="amount" step="0.01" min="1" max="{{ $bill->due_amount }}" value="{{ $bill->due_amount }}" class="block w-full pl-8 pr-3 py-2.5 rounded-md border border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-bold text-lg text-gray-900" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Payment Method</label>
                        <select name="method" id="collect_payment_method" onchange="toggleTrxId('collect')" class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-emerald-500 text-sm font-semibold">
                            <option value="cash">Cash</option><option value="bkash">bKash</option><option value="nagad">Nagad</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Transaction ID / Reference <span id="collect_trx_asterisk" class="text-red-500 hidden">*</span></label>
                        <input type="text" name="transaction_id" id="collect_transaction_id" placeholder="Ex: bKash TrxID" class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-emerald-500 text-sm">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Note (Optional)</label>
                        <input type="text" name="note" placeholder="Ex: Paid in full by customer..." class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-emerald-500 text-sm">
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button type="submit" class="flex-1 justify-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition-all cursor-pointer">Receive Payment</button>
                        <button type="button" onclick="closeCollectDueModal()" class="px-4 py-2.5 rounded-md border border-gray-300 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 transition-all cursor-pointer">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($bill && $bill->paid_amount > $bill->total_amount)
    <div id="refundModal" class="fixed inset-0 z-[105] hidden overflow-y-auto bg-gray-900 bg-opacity-60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full sm:my-8" style="max-width: 28rem !important; margin: 0 auto;">
                <div class="bg-blue-50 px-6 py-4 border-b border-blue-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-blue-800 flex items-center gap-2">Refund Money</h3>
                    <button type="button" onclick="closeRefundModal()" class="text-blue-400 hover:text-blue-700 bg-transparent rounded-lg p-1.5 ml-auto cursor-pointer">✕</button>
                </div>
                <form method="POST" action="{{ route('bills.add-transaction', $bill->id) }}" class="px-6 pt-5 pb-6">
                    @csrf
                    <input type="hidden" name="type" value="refund">
                    <div class="mb-4 text-center border-b pb-4">
                        <p class="text-sm font-bold text-gray-600 mb-1">Refundable to Customer:</p>
                        <p class="text-3xl font-black text-blue-600">৳{{ number_format($bill->paid_amount - $bill->total_amount, 2) }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Refund Amount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><span class="text-gray-500 font-bold">৳</span></div>
                            <input type="number" name="amount" step="0.01" min="1" max="{{ $bill->paid_amount - $bill->total_amount }}" value="{{ $bill->paid_amount - $bill->total_amount }}" class="block w-full pl-8 pr-3 py-2.5 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 font-bold text-lg text-gray-900" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Refund Method</label>
                        <select name="method" id="refund_payment_method" onchange="toggleTrxId('refund')" class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-blue-500 text-sm font-semibold">
                            <option value="cash">Cash</option><option value="bkash">bKash</option><option value="nagad">Nagad</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Transaction ID / Reference <span id="refund_trx_asterisk" class="text-red-500 hidden">*</span></label>
                        <input type="text" name="transaction_id" id="refund_transaction_id" placeholder="Ex: bKash TrxID" class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-blue-500 text-sm">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Note (Optional)</label>
                        <input type="text" name="note" placeholder="Ex: Advance refunded..." class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-blue-500 text-sm">
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button type="submit" class="flex-1 justify-center rounded-md bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-all cursor-pointer">Confirm Refund</button>
                        <button type="button" onclick="closeRefundModal()" class="px-4 py-2.5 rounded-md border border-gray-300 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 transition-all cursor-pointer">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($bill && !in_array($device->status, ['delivered', 'returned']))
    <!-- 100% OVERHAULED EDIT BILL MODAL (Post-Billing Services via Dynamic Rows) -->
    <div id="editBillModal" class="fixed inset-0 z-[105] hidden overflow-y-auto bg-gray-900 bg-opacity-60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full sm:my-8" style="max-width: 50rem !important; margin: 0 auto;">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-200 flex justify-between items-start">
                    <div class="flex flex-col text-left">
                        <h3 class="text-lg font-bold text-indigo-800">Edit or Adjust Bill</h3>
                        <p class="text-xs text-indigo-600 mt-1 font-semibold">Check/uncheck existing items or dynamically add post-billing services</p>
                    </div>
                    <button type="button" onclick="closeEditBillModal()" class="text-indigo-400 hover:text-indigo-700 bg-transparent rounded-lg p-1.5 ml-auto cursor-pointer">✕</button>
                </div>

                <form method="POST" action="{{ route('bills.adjust', $bill->id) }}" class="px-6 pt-5 pb-6" id="editBillForm">
                    @csrf

                    <!-- UNBILLED FAULT DETECTOR ALERT -->
                    @if(count($unbilledFaults) > 0)
                        <div class="mb-5 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-md shadow-sm" id="unbilledFaultAlertBox">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-sm font-bold text-amber-800 flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Unbilled Actual Faults Detected!
                                    </h4>
                                    <p class="text-xs text-amber-700 mt-1">টেকনিশিয়ান নতুন 'Actual Fault' যুক্ত করেছেন যা এখনো বিলে যোগ করা হয়নি।</p>
                                    <ul class="list-disc pl-5 mt-2 text-xs font-semibold text-amber-900">
                                        @foreach($unbilledFaults as $fault)
                                            <li>{{ $fault['name'] }} <span class="text-[9px] px-1 py-0.5 rounded bg-amber-200 text-amber-800 ml-1">{{ strtoupper($fault['type']) }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" onclick="syncUnbilledFaults()" class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-3 py-2 rounded shadow transition whitespace-nowrap">
                                    ✨ Auto-Add to Bill
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Existing Items Panel -->
                    @if($bill->items && $bill->items->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                            <div class="bg-blue-50/40 p-4 rounded-lg border border-blue-200">
                                <h4 class="text-xs font-bold text-blue-800 mb-3 uppercase tracking-wider border-b border-blue-200 pb-2">Hardware Repairs</h4>
                                <div class="space-y-2">
                                    @forelse($hardwareItems as $item)
                                        @php
                                            $isCancelled = str_starts_with($item->description, '[Cancelled] ');
                                            $cleanName = str_replace('[Cancelled] ', '', $item->description);
                                        @endphp
                                        <label class="bill-item-wrapper flex items-center gap-3 p-2.5 border rounded-md cursor-pointer transition select-none {{ $isCancelled ? 'bg-red-50 border-red-300 opacity-75' : 'bg-white border-gray-200 hover:bg-indigo-50' }}">
                                            <input type="checkbox" name="kept_items[]" value="{{ $item->id }}" class="bill-item-checkbox w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" data-amount="{{ $item->amount }}" data-name="{{ $cleanName }}" {{ $isCancelled ? '' : 'checked' }} onchange="autoCalculateBill(this)">
                                            <span class="item-name text-sm font-bold flex-1 {{ $isCancelled ? 'line-through text-red-600' : 'text-gray-800' }}">{{ $cleanName }}</span>
                                            <span class="item-amount text-sm font-black {{ $isCancelled ? 'line-through text-red-500' : 'text-indigo-600' }}">৳{{ $item->amount }}</span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-gray-500 text-center py-2">No hardware items.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div class="bg-green-50/40 p-4 rounded-lg border border-green-200">
                                <h4 class="text-xs font-bold text-green-800 mb-3 uppercase tracking-wider border-b border-green-200 pb-2">Software Services</h4>
                                <div class="space-y-2">
                                    @forelse($softwareItems as $item)
                                        @php
                                            $isCancelled = str_starts_with($item->description, '[Cancelled] ');
                                            $cleanName = str_replace('[Cancelled] ', '', $item->description);
                                        @endphp
                                        <label class="bill-item-wrapper flex items-center gap-3 p-2.5 border rounded-md cursor-pointer transition select-none {{ $isCancelled ? 'bg-red-50 border-red-300 opacity-75' : 'bg-white border-gray-200 hover:bg-indigo-50' }}">
                                            <input type="checkbox" name="kept_items[]" value="{{ $item->id }}" class="bill-item-checkbox w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" data-amount="{{ $item->amount }}" data-name="{{ $cleanName }}" {{ $isCancelled ? '' : 'checked' }} onchange="autoCalculateBill(this)">
                                            <span class="item-name text-sm font-bold flex-1 {{ $isCancelled ? 'line-through text-red-600' : 'text-gray-800' }}">{{ $cleanName }}</span>
                                            <span class="item-amount text-sm font-black {{ $isCancelled ? 'line-through text-red-500' : 'text-indigo-600' }}">৳{{ $item->amount }}</span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-gray-500 text-center py-2">No software items.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- NEW DYNAMIC ROW ADDER -->
                    <div class="mb-5 border border-indigo-200 rounded-lg overflow-hidden shadow-sm">
                        <div class="bg-indigo-50 px-4 py-3 border-b border-indigo-200 flex justify-between items-center">
                            <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Add New Services (Post-Billing)</h4>
                            <div class="flex gap-2">
                                <button type="button" onclick="addModalHardware()" class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded shadow-sm hover:bg-blue-700 font-bold transition">+ Add HW</button>
                                <button type="button" onclick="addModalSoftware()" class="text-xs bg-green-600 text-white px-3 py-1.5 rounded shadow-sm hover:bg-green-700 font-bold transition">+ Add SW</button>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 flex flex-col gap-3 min-h-[60px]" id="modalNewServicesContainer">
                            <p class="text-xs text-gray-400 text-center italic py-2" id="modalEmptyState">No new services added yet. Click the buttons above.</p>
                        </div>
                    </div>

                    <!-- Totals & Notes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-1">New Total Bill <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><span class="text-gray-500 font-bold">৳</span></div>
                                <input type="number" name="new_total_amount" id="new_total_amount" step="0.01" min="0" value="{{ $bill->total_amount }}" class="block w-full pl-8 pr-3 py-2.5 rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 font-bold text-lg text-gray-900 bg-gray-100 cursor-not-allowed" readonly required>
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Reason for Change <span class="text-red-500">*</span></label>
                            <textarea name="adjust_note" id="adjust_note" rows="2" class="block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 text-sm" placeholder="Ex: Added new hardware repair..." required></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-2 border-t pt-4">
                        <button type="submit" class="flex-1 justify-center rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-all cursor-pointer">Update Bill & Save Changes</button>
                        <button type="button" onclick="closeEditBillModal()" class="px-4 py-2.5 rounded-md border border-gray-300 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 transition-all cursor-pointer">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        const allCancelledNote = @json($cancelledCount > 0 ? "All Services Cancelled (Returned Unrepaired): " . implode(', ', $cancelledItems) : "");
        const partialCancelledNote = @json($cancelledCount > 0 ? "Cancelled Services: " . implode(', ', $cancelledItems) : "");

        function toggleTrxId(prefix) {
            const method = document.getElementById(prefix + '_payment_method').value;
            const trxInput = document.getElementById(prefix + '_transaction_id');
            const trxAsterisk = document.getElementById(prefix + '_trx_asterisk');
            if (['bkash', 'nagad', 'bank'].includes(method)) {
                trxInput.required = true; trxAsterisk.classList.remove('hidden');
            } else {
                trxInput.required = false; trxAsterisk.classList.add('hidden');
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        const trashIcon = `<svg class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>`;

        function checkEmptyState() {
            const container = document.getElementById('modalNewServicesContainer');
            const emptyState = document.getElementById('modalEmptyState');
            if(container && emptyState) {
                if (container.querySelectorAll('.item-row').length > 0) emptyState.style.display = 'none';
                else emptyState.style.display = 'block';
            }
        }

        function addModalHardware(defaultName = '') {
            const container = document.getElementById('modalNewServicesContainer');
            const safeName = escapeHtml(defaultName);
            const html = `
                <div class="p-3 bg-white border border-blue-200 rounded-md shadow-sm item-row flex gap-2 items-end transition">
                    <input type="hidden" name="new_service_categories[]" value="hardware">
                    <div style="flex: 55%;">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Service (HW) *</label>
                        <input type="text" name="new_service_descriptions[]" class="modal-new-desc block w-full py-1.5 px-2 border-gray-300 rounded shadow-sm text-xs focus:border-blue-500 placeholder-gray-400" placeholder="Ex: Speaker Fix" value="${safeName}" required oninput="autoCalculateBill(true)">
                    </div>
                    <div style="flex: 20%;">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Warranty *</label>
                        <input type="text" name="new_service_warranties[]" class="block w-full py-1.5 px-2 border-gray-300 rounded shadow-sm text-xs focus:border-blue-500 placeholder-gray-400" placeholder="Ex: None" value="None" required>
                    </div>
                    <div style="flex: 25%;">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Amount *</label>
                        <div class="flex shadow-sm rounded">
                            <span class="inline-flex items-center px-2 py-1.5 rounded-l border border-r-0 border-gray-300 bg-gray-100 text-gray-500 font-bold text-xs">৳</span>
                            <input type="number" name="new_service_amounts[]" step="0.01" min="0" class="modal-new-amt block w-full px-2 py-1.5 rounded-none rounded-r text-xs border-gray-300 focus:border-blue-500 font-semibold" required oninput="autoCalculateBill(true)">
                        </div>
                    </div>
                    <div style="flex: 0 0 32px; text-align: center; padding-bottom: 4px;">
                        <button type="button" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded transition cursor-pointer" onclick="this.closest('.item-row').remove(); checkEmptyState(); autoCalculateBill(true);">${trashIcon}</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            checkEmptyState();
        }

        function addModalSoftware(defaultName = '') {
            const container = document.getElementById('modalNewServicesContainer');
            const safeName = escapeHtml(defaultName);
            const html = `
                <div class="p-3 bg-white border border-green-200 rounded-md shadow-sm item-row flex gap-2 items-end transition">
                    <input type="hidden" name="new_service_categories[]" value="software">
                    <input type="hidden" name="new_service_warranties[]" value="None">
                    <div style="flex: 75%;">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Service (SW) *</label>
                        <input type="text" name="new_service_descriptions[]" class="modal-new-desc block w-full py-1.5 px-2 border-gray-300 rounded shadow-sm text-xs focus:border-green-500 placeholder-gray-400" placeholder="Ex: FRP Bypass" value="${safeName}" required oninput="autoCalculateBill(true)">
                    </div>
                    <div style="flex: 25%;">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Amount *</label>
                        <div class="flex shadow-sm rounded">
                            <span class="inline-flex items-center px-2 py-1.5 rounded-l border border-r-0 border-gray-300 bg-gray-100 text-gray-500 font-bold text-xs">৳</span>
                            <input type="number" name="new_service_amounts[]" step="0.01" min="0" class="modal-new-amt block w-full px-2 py-1.5 rounded-none rounded-r text-xs border-gray-300 focus:border-green-500 font-semibold" required oninput="autoCalculateBill(true)">
                        </div>
                    </div>
                    <div style="flex: 0 0 32px; text-align: center; padding-bottom: 4px;">
                        <button type="button" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded transition cursor-pointer" onclick="this.closest('.item-row').remove(); checkEmptyState(); autoCalculateBill(true);">${trashIcon}</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            checkEmptyState();
        }

        function autoCalculateBill(triggeredByUser = null) {
            let newSubtotal = 0; let removedNames = []; let checkedCount = 0;
            let checkboxes = document.querySelectorAll('.bill-item-checkbox');

            if (checkboxes.length > 0) {
                checkboxes.forEach(cb => {
                    let wrapper = cb.closest('.bill-item-wrapper');
                    let nameSpan = wrapper.querySelector('.item-name');
                    let amountSpan = wrapper.querySelector('.item-amount');
                    if(cb.checked) {
                        checkedCount++; newSubtotal += parseFloat(cb.dataset.amount);
                        wrapper.classList.remove('opacity-75', 'bg-red-50', 'border-red-300');
                        wrapper.classList.add('bg-white', 'border-gray-200');
                        nameSpan.classList.remove('line-through', 'text-red-600');
                        nameSpan.classList.add('text-gray-800');
                        amountSpan.classList.remove('line-through', 'text-red-500');
                        amountSpan.classList.add('text-indigo-600');
                    } else {
                        removedNames.push(cb.dataset.name);
                        wrapper.classList.add('opacity-75', 'bg-red-50', 'border-red-300');
                        wrapper.classList.remove('bg-white', 'border-gray-200');
                        nameSpan.classList.add('line-through', 'text-red-600');
                        nameSpan.classList.remove('text-gray-800');
                        amountSpan.classList.add('line-through', 'text-red-500');
                        amountSpan.classList.remove('text-indigo-600');
                    }
                });
            }

            let totalNewServiceAmt = 0; let addedServiceNames = [];
            const newAmts = document.querySelectorAll('.modal-new-amt');
            const newDescs = document.querySelectorAll('.modal-new-desc');

            if(newAmts.length > 0) {
                newAmts.forEach((input, index) => {
                    const amt = parseFloat(input.value) || 0;
                    totalNewServiceAmt += amt;
                    const desc = newDescs[index].value.trim();
                    if (desc !== '' && amt > 0) addedServiceNames.push(desc + " (৳" + amt + ")");
                });
                newSubtotal += totalNewServiceAmt;
            }

            document.getElementById('new_total_amount').value = newSubtotal;

            if (triggeredByUser) {
                let noteParts = [];
                if (checkboxes.length > 0 && checkedCount === 0 && removedNames.length > 0 && totalNewServiceAmt === 0) noteParts.push("All Cancelled: " + removedNames.join(', '));
                else if (removedNames.length > 0) noteParts.push("Cancelled: " + removedNames.join(', '));

                if (addedServiceNames.length > 0) noteParts.push("Added: " + addedServiceNames.join(', '));

                let noteBox = document.getElementById('adjust_note');
                noteBox.value = noteParts.length > 0 ? noteParts.join(' | ') : "Bill details updated.";
            }
        }

        const unbilledFaultsData = @json($unbilledFaults ?? []);

        function syncUnbilledFaults() {
            unbilledFaultsData.forEach(fault => {
                if (fault.type === 'hardware') {
                    addModalHardware(fault.name);
                } else {
                    addModalSoftware(fault.name);
                }
            });
            const alertBox = document.getElementById('unbilledFaultAlertBox');
            if (alertBox) alertBox.style.display = 'none';
        }

        let pendingSubmitAction = null;
        function showSmartAlert(message, actionCallback) {
            document.getElementById('smartAlertMessage').innerText = message;
            document.getElementById('smartAlertModal').classList.remove('hidden');
            pendingSubmitAction = actionCallback;
        }
        function closeSmartAlert() {
            document.getElementById('smartAlertModal').classList.add('hidden');
            pendingSubmitAction = null;
        }
        document.getElementById('smartAlertConfirmBtn')?.addEventListener('click', function() {
            if(pendingSubmitAction) pendingSubmitAction();
            closeSmartAlert();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const mainDeliveryForm = document.getElementById('mainDeliveryForm');
            const hasDue = {{ ($bill &&$bill->due_amount > 0) ? 'true' : 'false' }};
            const isRefundable = {{ ($bill && $bill->paid_amount >$bill->total_amount) ? 'true' : 'false' }};
            const paidAmount = {{ $bill ? $bill->paid_amount : 0 }};
            const totalAmount = {{ $bill ? $bill->total_amount : 0 }};
            const hasCancelledItems = {{ $hasCancelled ? 'true' : 'false' }};
            const autoRefundCheck = document.getElementById('auto_refund_and_cancel');

            if(mainDeliveryForm) {
                mainDeliveryForm.onsubmit = function(e) {
                    e.preventDefault();
                    const selectedOutcome = document.querySelector('input[name="repair_outcome"]:checked')?.value;
                    const isAutoCancelEnabled = autoRefundCheck && autoRefundCheck.checked;

                    if (selectedOutcome === 'returned_unrepaired' && isAutoCancelEnabled) {
                         HTMLFormElement.prototype.submit.call(mainDeliveryForm); return true;
                    }
                    if (selectedOutcome === 'returned_unrepaired' && (paidAmount > 0 || totalAmount > 0)) {
                        showSmartAlert('Warning: Bill still has a balance.\nClick "Proceed" to open Edit Bill / Refund.', function() {
                            if (isRefundable) { openRefundModal(); } else { openEditBillModal(); }
                        });
                        return false;
                    }
                    if (isRefundable) {
                        showSmartAlert('Customer has overpaid! Please refund before completing delivery.', function() { openRefundModal(); });
                        return false;
                    }
                    if (hasDue && selectedOutcome === 'partially_repaired' && !hasCancelledItems) {
                        showSmartAlert('Warning: Delivering as "Partially Repaired" with a due.\nPlease uncheck cancelled items in "Edit Bill". Proceed anyway?', function() { proceedToDelivery(selectedOutcome); });
                        return false;
                    }
                    proceedToDelivery(selectedOutcome);
                };
            }
        });

        function proceedToDelivery(selectedOutcome) {
            const hasDue = {{ ($bill &&$bill->due_amount > 0) ? 'true' : 'false' }};
            if (hasDue && selectedOutcome !== 'returned_unrepaired') {
                document.getElementById('modal_delivered_by').value = document.getElementById('delivered_by_name').value;
                document.getElementById('modal_received_by').value = document.getElementById('received_by_name').value;
                document.getElementById('modal_repair_outcome').value = selectedOutcome || 'success';
                document.getElementById('modal_repair_note').value = document.getElementById('repair_note')?.value || '';
                openDueModal();
            } else {
                HTMLFormElement.prototype.submit.call(document.getElementById('mainDeliveryForm'));
            }
        }

        function syncOutcome(value) {
            const modalOutcome = document.getElementById('modal_repair_outcome');
            if (modalOutcome) modalOutcome.value = value;

            const noteContainer = document.getElementById('repair_note_container');
            const noteInput = document.getElementById('repair_note');
            const noteAsterisk = document.getElementById('repair_note_asterisk');
            const autoRefundContainer = document.getElementById('return_unrepaired_action_container');
            const autoRefundCheck = document.getElementById('auto_refund_and_cancel');

            noteContainer.classList.add('hidden'); noteInput.required = false; noteAsterisk.classList.add('hidden');
            if (autoRefundContainer) autoRefundContainer.classList.add('hidden');
            if (autoRefundCheck) autoRefundCheck.checked = false;

            if (value === 'partially_repaired') {
                noteContainer.classList.remove('hidden'); noteInput.required = true; noteAsterisk.classList.remove('hidden');
                if (partialCancelledNote && !noteInput.value.trim()) noteInput.value = partialCancelledNote;
            } else if (value === 'returned_unrepaired') {
                if (autoRefundContainer) autoRefundContainer.classList.remove('hidden');
                noteContainer.classList.remove('hidden');
                if (allCancelledNote) noteInput.value = allCancelledNote;
                else if (partialCancelledNote && !noteInput.value.trim()) noteInput.value = partialCancelledNote;
            }
        }

        function openDueModal() { document.getElementById('dueCommitmentModal')?.classList.remove('hidden'); }
        function closeDueModal() { document.getElementById('dueCommitmentModal')?.classList.add('hidden'); }
        function openCollectDueModal() { document.getElementById('collectDueModal')?.classList.remove('hidden'); }
        function closeCollectDueModal() { document.getElementById('collectDueModal')?.classList.add('hidden'); }
        function openRefundModal() { document.getElementById('refundModal')?.classList.remove('hidden'); }
        function closeRefundModal() { document.getElementById('refundModal')?.classList.add('hidden'); }

        function openEditBillModal() {
            document.getElementById('editBillModal')?.classList.remove('hidden');
        }

        function closeEditBillModal() {
            document.getElementById('editBillModal')?.classList.add('hidden');
            const container = document.getElementById('modalNewServicesContainer');
            if (container) {
                container.querySelectorAll('.item-row').forEach(row => row.remove());
                checkEmptyState();
            }
            const alertBox = document.getElementById('unbilledFaultAlertBox');
            if (alertBox && unbilledFaultsData.length > 0) alertBox.style.display = 'block';
            document.getElementById('editBillForm').reset();
            autoCalculateBill(false);
        }
    </script>
</x-app-layout>
