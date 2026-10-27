<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoice #INV-') }}{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}
            </h2>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                🖨️ Print Receipt (POS)
            </button>
        </div>
    </x-slot>

    @php
        // 1. Bulletproof parsing for Terms on the Invoice (Substring Bug Fixed)
        $rawTerms = is_array($bill->device->terms) ? json_encode($bill->device->terms) : (string) $bill->device->terms;

        $deadRiskAccepted = str_contains($rawTerms, 'Dead Risk Accepted') && !str_contains($rawTerms, 'Dead Risk Not Accepted');
        $dataLossAccepted = str_contains($rawTerms, 'Data Loss Accepted') && !str_contains($rawTerms, 'Data Loss Not Accepted');

        // 2. Clean Brand & Model Name
        $cleanModel = trim(preg_replace('/^' . preg_quote($bill->device->brand, '/') . '\s+/i', '', $bill->device->model));

        // 3. Parse Issue Data (Handle both JSON and Legacy Strings)
        $rawIssue = $bill->device->problem_description;
        $hwIssues = [];
        $swIssues = [];

        $parsedIssue = json_decode($rawIssue, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($parsedIssue)) {
            $hwIssues = $parsedIssue['hw'] ?? [];
            $swIssues = $parsedIssue['sw'] ?? [];
        } else {
            $lines = explode("\n", $rawIssue);
            foreach($lines as $line) {
                $text = trim(preg_replace('/^\d+[\.\)\-]\s*/', '', $line));
                if(!$text) continue;
                if (stripos($text, '[SW]') !== false || stripos($text, '[Software]') !== false) {
                    $swIssues[] = trim(str_ireplace(['[SW]', '[Software]'], '', $text));
                } else {
                    $hwIssues[] = trim(str_ireplace(['[HW]', '[Hardware]'], '', $text));
                }
            }
        }
        $allIssues = array_merge($hwIssues, $swIssues);
        $issueText = implode(', ', $allIssues);
        $issueText = \Illuminate\Support\Str::limit($issueText, 60);

        // 4. Smart Note Parsing (Extracting Additional Updates)
        $rawNote = $bill->note ?? '';
        $resolved = [];
        $skipped = [];

        if (preg_match_all('/\[ADDITIONAL_RESOLVED:\s*(.*?)\]/i', $rawNote, $matches)) {
            $resolved = $matches[1];
            $rawNote = preg_replace('/\[ADDITIONAL_RESOLVED:\s*.*?\]/i', '', $rawNote);
        }
        if (preg_match_all('/\[(?:ADDITIONAL_SKIPPED|Additional Issue Not Repaired|অতিরিক্ত কাজ করা হয়নি):\s*(.*?)\s*(?:\||-)\s*(?:REASON|Reason|কারণ):\s*(.*?)\]/iu', $rawNote, $matches)) {
            foreach ($matches[1] as $index => $issue) {
                $skipped[] = trim($issue) . ' (Reason: ' . trim($matches[2][$index]) . ')';
            }
            $rawNote = preg_replace('/\[(?:ADDITIONAL_SKIPPED|Additional Issue Not Repaired|অতিরিক্ত কাজ করা হয়নি):\s*.*?\]/iu', '', $rawNote);
        }
        $rawNote = trim($rawNote);
    @endphp

    <div class="py-8 bg-gray-200 min-h-screen flex justify-center items-start print-wrapper">

        <div id="printable-receipt" class="w-96 max-w-full bg-white p-5 text-xs font-mono text-gray-900 border border-gray-300 shadow-2xl">

            <!-- Header (Store Info) -->
            <div class="text-center border-b border-dashed border-gray-400 pb-3 mb-3">
                <h1 class="text-xl font-bold uppercase">{{ auth()->user()->shop->name ?? 'Anis Telecom' }}</h1>
                <p class="text-[11px] mt-0.5">Mobile Repair & Accessories</p>
                <p class="text-[11px]">Phone: {{ auth()->user()->phone ?? 'N/A' }}</p>
            </div>

            <!-- Receipt Info -->
            <div class="mb-3 text-left">
                <h2 class="font-bold text-center text-sm uppercase mb-2 bg-gray-100 py-1 print:bg-transparent print:border-y print:border-gray-400">CASH RECEIPT</h2>
                <p><strong>Receipt No:</strong> #INV-{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Date:</strong> {{ $bill->created_at->format('d M, Y h:i A') }}</p>
                <p><strong>Tracking:</strong> {{ $bill->device->tracking_code }}</p>
            </div>

            <!-- Customer & Device -->
            <div class="mb-3 border-t border-b border-dashed border-gray-400 py-2">
                <p><strong>Customer:</strong> {{ $bill->customer->name }}</p>
                <p><strong>Phone:</strong> {{ $bill->customer->phone }}</p>
                <p class="mt-1"><strong>Device:</strong> {{ $bill->device->brand }} {{ $cleanModel }}</p>
                @if($bill->device->imei)
                    <p><strong>IMEI:</strong> {{ $bill->device->imei }}</p>
                @endif
                <p class="mt-1 leading-snug"><strong>Issue:</strong> {{ $issueText ?: 'N/A' }}</p>
            </div>

            <!-- Pre-Repair Agreements (Terms) -->
            <div class="mb-3">
                <p class="font-bold uppercase text-center mb-1 underline">Agreements</p>
                <div class="flex justify-between">
                    <span>1. Dead Risk:</span>
                    <span class="font-bold {{ $deadRiskAccepted ? '' : 'text-[10px]' }}">{{ $deadRiskAccepted ? 'ACCEPTED' : 'Not Accepted' }}</span>
                </div>
                <div class="flex justify-between mt-0.5">
                    <span>2. Data Loss:</span>
                    <span class="font-bold {{ $dataLossAccepted ? '' : 'text-[10px]' }}">{{ $dataLossAccepted ? 'ACCEPTED' : 'Not Accepted' }}</span>
                </div>
            </div>

            <!-- Bill Items -->
            <table class="w-full text-left mb-3 border-t border-b border-dashed border-gray-400 py-2">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="py-1">Description</th>
                        <th class="py-1 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-2 pr-2 leading-snug">Mobile Repair Service</td>
                        <td class="py-2 text-right">৳{{ number_format($bill->total_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="space-y-1 text-right font-bold text-sm">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>৳{{ number_format($bill->total_amount, 2) }}</span>
                </div>
                @if($bill->discount > 0)
                <div class="flex justify-between text-[11px] font-normal text-gray-700">
                    <span>Discount:</span>
                    <span>- ৳{{ number_format($bill->discount, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-300 pt-1 text-[15px]">
                    <span>Grand Total:</span>
                    <span>৳{{ number_format($bill->total_amount - $bill->discount, 2) }}</span>
                </div>
                @else
                <div class="flex justify-between border-t border-gray-300 pt-1 text-[15px]">
                    <span>Grand Total:</span>
                    <span>৳{{ number_format($bill->total_amount, 2) }}</span>
                </div>
                @endif

                <div class="flex justify-between pt-1">
                    <span>Paid:</span>
                    <span>৳{{ number_format($bill->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-dashed border-gray-400 pt-1 mt-1 {{ $bill->due_amount > 0 ? 'text-[15px]' : '' }}">
                    <span>Due:</span>
                    <span>৳{{ number_format($bill->due_amount, 2) }}</span>
                </div>
            </div>

            <!-- Smart Footer Notes (No Raw Tags) -->
            <div class="mt-4 text-[10px] border-t border-dashed border-gray-400 pt-3 pb-4">
                @if(!empty($rawNote))
                    <div class="mb-3 text-left whitespace-pre-line leading-snug">
                        <strong>Note:</strong> {{ $rawNote }}
                    </div>
                @endif

                @if(count($resolved) > 0 || count($skipped) > 0)
                    <div class="mb-3 text-left border border-gray-300 p-2 rounded">
                        <p class="font-bold border-b border-gray-300 pb-1 mb-1 text-[11px] uppercase text-center">Update / Additional</p>
                        @if(count($resolved) > 0)
                            <div class="mb-1">
                                <span class="font-bold">✓ Resolved:</span>
                                <span>{{ implode(', ', $resolved) }}</span>
                            </div>
                        @endif
                        @if(count($skipped) > 0)
                            <div>
                                <span class="font-bold">✕ Not Repaired:</span>
                                <span>{{ implode(', ', $skipped) }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="text-center mt-4">
                    <p>Goods once sold will not be returned.</p>
                    <p class="font-bold mt-1 text-xs">Thank you for your visit!</p>
                    <p class="mt-3 text-[9px] text-gray-500">System by Anisur Rahman</p>
                </div>
            </div>

        </div>
    </div>

    <style>
        @media print {
            nav, header, footer, .print\:hidden { display: none !important; }
            body, html, #app, main, .min-h-screen, .bg-gray-100, .bg-gray-200 {
                margin: 0 !important; padding: 0 !important;
                background-color: white !important;
                min-height: 0 !important; height: auto !important;
            }
            .print-wrapper {
                display: block !important; margin: 0 !important;
                padding: 0 !important; background: transparent !important;
            }
            #printable-receipt {
                width: 80mm !important; max-width: 80mm !important;
                margin: 0 !important; padding: 5mm !important;
                border: none !important; box-shadow: none !important;
            }
            @page { size: 80mm auto; margin: 0mm; }
        }
    </style>
</x-app-layout>
