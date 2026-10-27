<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Billing & Invoices (বিল এবং ইনভয়েস)') }}
            </h2>
            <a href="{{ route('bills.create') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition">
                + Create New Bill
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Success Message Alert -->
            <!-- @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif -->

            <!-- Error Message Alert -->
            <!-- @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('error') }}</span>
                </div>
            @endif -->


            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">

                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-3">Bill ID</th>
                                <th scope="col" class="px-6 py-3">Customer & Device</th>
                                <th scope="col" class="px-6 py-3 text-right">Total</th>
                                <th scope="col" class="px-6 py-3 text-right">Discount</th>
                                <th scope="col" class="px-6 py-3 text-right">Paid</th>
                                <th scope="col" class="px-6 py-3 text-right">Due</th>
                                <th scope="col" class="px-6 py-3 text-center">Status</th>
                                <th scope="col" class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($bills->count() > 0)
                                @foreach($bills as $bill)
                                    @php
                                        $invoiceNo = $bill->invoice_number ?? ('#INV-' . str_pad($bill->id, 4, '0', STR_PAD_LEFT));
                                    @endphp
                                    <tr class="bg-white border-b hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">{{ $invoiceNo }}</td>
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-gray-900">{{ $bill->customer->name ?? 'N/A' }}</span> <br>
                                            <span class="text-xs text-gray-500">{{ $bill->device->brand ?? '' }} {{ $bill->device->model ?? '' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">৳{{ number_format($bill->total_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-gray-500">৳{{ number_format($bill->discount, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-green-600">৳{{ number_format($bill->paid_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-bold {{ $bill->due_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">৳{{ number_format($bill->due_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 text-xs rounded-full font-bold uppercase
                                                {{ $bill->payment_status == 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $bill->payment_status == 'due' ? 'bg-red-100 text-red-800' : '' }}                                                 {{$bill->payment_status == 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            ">
                                                {{ ucfirst($bill->payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                            <!-- Print Button -->
                                            <a href="{{ route('bills.show', $bill->id) }}" target="_blank" class="text-blue-600 hover:text-blue-900 font-semibold">
                                                Print
                                            </a>

                                            <!-- Pay Due / Refund Button -->
                                            @if($bill->due_amount > 0)
                                                <button type="button" onclick="openPaymentModal({{ $bill->id }}, '{{ $invoiceNo }}', {{$bill->due_amount }}, 'due_payment')" class="text-emerald-600 hover:text-emerald-900 font-semibold inline-block">
                                                    Pay Due
                                                </button>
                                            @else
                                                <button type="button" onclick="openPaymentModal({{ $bill->id }}, '{{ $invoiceNo }}', {{$bill->paid_amount }}, 'refund')" class="text-amber-600 hover:text-amber-900 font-semibold inline-block">
                                                    Refund
                                                </button>
                                            @endif

                                            <!-- Delete Button -->
                                            <form action="{{ route('bills.destroy', $bill->id) }}" method="POST" class="inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই বিলটি ডিলিট করতে চান?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-semibold ml-1">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500 bg-gray-50">
                                        কোনো বিল পাওয়া যায়নি। নতুন বিল তৈরি করুন।
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <!-- Payment / Refund Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900 bg-opacity-50" style="display: none;">

        <!-- Modal Wrapper for Centering -->
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">

            <!-- Modal Content (এখানে style দিয়ে কাস্টম ইনলাইন উইডথ দেওয়া হয়েছে) -->
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full sm:my-8" style="max-width: 32rem !important; margin: 0 auto;">

                <!-- Modal Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Payment Adjustment</h3>
                    <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 bg-transparent rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="paymentForm" method="POST" action="" class="px-6 pt-5 pb-6">
                    @csrf
                    <input type="hidden" id="transactionType" name="type" value="due_payment">

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5" id="amountLabel">Amount (পরিমাণ) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">৳</span>
                            </div>
                            <input type="number" step="0.01" min="1" id="transactionAmount" name="amount" class="block w-full rounded-md border border-gray-300 pl-8 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        </div>
                        <span id="maxLimitText" class="text-xs font-medium text-amber-600 mt-1.5 block"></span>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Payment Method (পরিশোধের মাধ্যম)</label>
                        <select name="method" class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            <option value="cash">Cash (নগদ)</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Note (বিবরণ / কারণ)</label>
                        <textarea name="note" rows="2" placeholder="যেমন: কাস্টমার বকেয়া পরিশোধ করেছে" class="block w-full rounded-md border border-gray-300 py-2 px-3 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="closePaymentModal()" class="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn" class="inline-flex w-full justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:w-auto sm:text-sm">
                            Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openPaymentModal(billId, invoiceNumber, maxAmount, type) {
            const modal = document.getElementById('paymentModal');
            const form = document.getElementById('paymentForm');
            const title = document.getElementById('modalTitle');
            const typeInput = document.getElementById('transactionType');
            const amountInput = document.getElementById('transactionAmount');
            const maxLimit = document.getElementById('maxLimitText');
            const submitBtn = document.getElementById('submitBtn');

            form.action = `/bills/${billId}/add-transaction`;
            typeInput.value = type;
            amountInput.max = maxAmount;
            amountInput.value = maxAmount;

            if (type === 'due_payment') {
                title.innerText = `Collect Due Payment (${invoiceNumber})`;
                maxLimit.innerText = `সর্বোচ্চ বকেয়া: ৳${parseFloat(maxAmount).toFixed(2)}`;
                submitBtn.innerText = 'Collect Payment';
                submitBtn.className = 'inline-flex w-full justify-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto sm:text-sm';
            } else {
                title.innerText = `Refund / Adjustment (${invoiceNumber})`;
                maxLimit.innerText = `সর্বোচ্চ ফেরতযোগ্য: ৳${parseFloat(maxAmount).toFixed(2)}`;
                submitBtn.innerText = 'Process Refund';
                submitBtn.className = 'inline-flex w-full justify-center rounded-md border border-transparent bg-amber-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 sm:w-auto sm:text-sm';
            }

            modal.style.display = 'block';
            modal.classList.remove('hidden');
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    </script>
</x-app-layout>
