<!-- resources/views/bills/print.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoice #INV-') }}{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}
            </h2>
            <div class="flex gap-3">
                <button onclick="window.history.back()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1.5 px-4 rounded shadow-sm text-sm">
                    &larr; Back
                </button>
                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-4 rounded shadow-sm text-sm">
                    🖨️ Print Receipt
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $rawTerms = is_array($bill->device->terms) ? json_encode($bill->device->terms) : (string) $bill->device->terms;
        $deadRiskAccepted = str_contains($rawTerms, 'Dead Risk Accepted') && !str_contains($rawTerms, 'Dead Risk Not Accepted');
        $dataLossAccepted = str_contains($rawTerms, 'Data Loss Accepted') && !str_contains($rawTerms, 'Data Loss Not Accepted');
        $cleanModel = trim(preg_replace('/^' . preg_quote($bill->device->brand, '/') . '\s+/i', '', $bill->device->model));

        $rawNote = $bill->note ?? '';
        $resolved = []; $skipped = [];
        if (preg_match_all('/\[ADDITIONAL_RESOLVED:\s*(.*?)\]/i', $rawNote, $matches)) {
            $resolved = $matches[1]; $rawNote = preg_replace('/\[ADDITIONAL_RESOLVED:\s*.*?\]/i', '', $rawNote);
        }
        if (preg_match_all('/\[(?:ADDITIONAL_SKIPPED|Additional Issue Not Repaired|অতিরিক্ত কাজ করা হয়নি):\s*(.*?)\s*(?:\||-)\s*(?:REASON|Reason|কারণ):\s*(.*?)\]/iu', $rawNote, $matches)) {
            foreach ($matches[1] as $index => $issue) $skipped[] = trim($issue) . ' (' . trim($matches[2][$index]) . ')';
            $rawNote = preg_replace('/\[(?:ADDITIONAL_SKIPPED|Additional Issue Not Repaired|অতিরিক্ত কাজ করা হয়নি):\s*.*?\]/iu', '', $rawNote);
        }
        $rawNote = trim($rawNote);

        $trackingUrl = url('/track/' . $bill->device->tracking_code);
    @endphp

    <div class="py-4 bg-gray-200 min-h-screen flex justify-center items-start print-wrapper">
        <div id="printable-receipt" class="w-80 max-w-full bg-white p-3 text-[11px] font-mono text-gray-900 border border-gray-300 shadow-xl relative pb-8">
            <!-- Header -->
            <div class="text-center border-b border-dashed border-gray-400 pb-2 mb-2">
                <h1 class="text-lg font-bold uppercase leading-tight">{{ auth()->user()->shop->name ?? 'Anis Telecom' }}</h1>
                <p class="text-[10px]">Mobile Repair & Accessories</p>
                <p class="text-[10px]">Phone: {{ auth()->user()->phone ?? 'N/A' }}</p>
            </div>

            <!-- Receipt Info -->
            <div class="mb-2 text-left">
                <h2 class="font-bold text-center text-xs uppercase mb-1 bg-gray-100 print:bg-transparent print:border-y print:border-gray-400">CASH RECEIPT</h2>
                <div class="flex justify-between"><p><strong>INV:</strong> #{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}</p><p>{{ $bill->created_at->format('d/m/y h:iA') }}</p></div>
                <p><strong>Track:</strong> {{ $bill->device->tracking_code }}</p>
            </div>

            <!-- Customer & Device Full Name -->
            <div class="mb-2 border-t border-b border-dashed border-gray-400 py-1.5">
                <p><strong>Customer:</strong> {{ $bill->customer->name }} ({{ $bill->customer->phone }})</p>
                <p><strong>Device:</strong> {{ $bill->device->brand }} {{ $cleanModel }}</p>
                @if($bill->device->imei) <p><strong>IMEI:</strong> {{ $bill->device->imei }}</p> @endif
            </div>

            <!-- Agreements -->
            <div class="mb-2 text-[10px]">
                <p class="font-bold uppercase text-center mb-0.5 underline">Agreements</p>
                <div class="flex justify-between"><span>1. Dead Risk:</span><span class="font-bold">{{ $deadRiskAccepted ? 'ACCEPTED' : 'Not Accepted' }}</span></div>
                <div class="flex justify-between"><span>2. Data Loss:</span><span class="font-bold">{{ $dataLossAccepted ? 'ACCEPTED' : 'Not Accepted' }}</span></div>
            </div>

            <!-- Itemized Bill (Warranty Center Aligned) -->
            <table class="w-full text-left mb-2 border-t border-b border-dashed border-gray-400 py-1.5">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="py-0.5 w-[50%]">Description</th>
                        <th class="py-0.5 text-center w-[25%] font-normal text-[9px] text-gray-500">Warranty</th>
                        <th class="py-0.5 text-right w-[25%]">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if($bill->items && $bill->items->count() > 0)
                        @foreach($bill->items as $item)
                            @if(!str_starts_with($item->description, '[Cancelled] '))
                                <tr>
                                    <td class="py-1 pr-1 leading-tight break-words align-top font-bold">
                                        {{ $item->description }}
                                    </td>
                                    <td class="py-1 text-center align-top text-[9px] text-gray-500 italic">
                                        {{ ($item->warranty && strtolower($item->warranty) !== 'none') ? $item->warranty : '-' }}
                                    </td>
                                    <td class="py-1 text-right font-bold align-top">
                                        ৳{{ number_format($item->amount, 0) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td class="py-1 pr-1 font-bold">Repair Service</td>
                            <td class="py-1 text-center text-[9px] text-gray-500">-</td>
                            <td class="py-1 text-right font-bold">৳{{ number_format($bill->total_amount, 0) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- Totals -->
            <div class="space-y-0.5 text-right font-bold text-xs">
                <div class="flex justify-between"><span>Subtotal:</span><span>৳{{ number_format($bill->total_amount, 0) }}</span></div>
                @if($bill->discount > 0)
                <div class="flex justify-between text-[10px] font-normal text-gray-600"><span>Discount:</span><span>- ৳{{ number_format($bill->discount, 0) }}</span></div>
                <div class="flex justify-between border-t border-gray-300 pt-0.5 text-[13px]"><span>Grand Total:</span><span>৳{{ number_format($bill->total_amount - $bill->discount, 0) }}</span></div>
                @else
                <div class="flex justify-between border-t border-gray-300 pt-0.5 text-[13px]"><span>Grand Total:</span><span>৳{{ number_format($bill->total_amount, 0) }}</span></div>
                @endif
                <div class="flex justify-between pt-0.5"><span>Paid:</span><span>৳{{ number_format($bill->paid_amount, 0) }}</span></div>
                <div class="flex justify-between border-t border-dashed border-gray-400 pt-0.5 mt-0.5 {{ $bill->due_amount > 0 ? 'text-[13px]' : '' }}"><span>Due:</span><span>৳{{ number_format($bill->due_amount, 0) }}</span></div>
            </div>

            <!-- Notes -->
            <div class="mt-3 border-t border-dashed border-gray-400 pt-2 pb-2 text-center text-[9px]">
                @if(!empty($rawNote)) <div class="mb-2 text-left whitespace-pre-line leading-tight"><strong>Note:</strong> {{ $rawNote }}</div> @endif
                @if(count($resolved) > 0 || count($skipped) > 0)
                    <div class="mb-2 text-left border border-gray-300 p-1.5 rounded">
                        @if(count($resolved) > 0) <div class="mb-0.5"><span class="font-bold">✓ Added:</span> {{ implode(', ', $resolved) }}</div> @endif
                        @if(count($skipped) > 0) <div><span class="font-bold">✕ Not Repaired:</span> {{ implode(', ', $skipped) }}</div> @endif
                    </div>
                @endif
                <p>Goods once sold will not be returned.</p>
                <p class="font-bold mt-1 text-[10px]">Thank you for your visit!</p>
            </div>

            <!-- Customer QR Code Section -->
            <div class="mt-2 text-center flex flex-col items-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($trackingUrl) }}" alt="Tracking QR Code" class="w-20 h-20 mb-1 border border-gray-200 p-0.5 grayscale">
                <p class="text-[9px] font-bold">Scan to Track Status</p>
                <p class="text-[8px] text-gray-500 font-mono">{{ $trackingUrl }}</p>
            </div>

            <div class="absolute bottom-2 left-0 right-0 text-center text-[7px] text-gray-400">
                System by Anisur Rahman
            </div>
        </div>
    </div>

    <style>
        @media print {
            nav, header, footer, .print\:hidden { display: none !important; }
            body, html, #app, main, .min-h-screen, .bg-gray-200 { background-color: white !important; margin: 0 !important; padding: 0 !important; }
            .print-wrapper { display: block !important; padding: 0 !important; }
            #printable-receipt { width: 80mm !important; margin: 0 !important; padding: 3mm !important; border: none !important; box-shadow: none !important; font-size: 10px !important;}
            @page { size: 80mm auto; margin: 0mm; }
        }
    </style>
</x-app-layout>
