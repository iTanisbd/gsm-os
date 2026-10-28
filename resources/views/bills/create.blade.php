<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Bill') }}
        </h2>
    </x-slot>

    <!-- Datalists for Smart Suggestions (Auto-Complete) -->
    @include('partials.suggestions')

    <script>
        const rawDevices = @json($devices);
        const deviceData = {};

        rawDevices.forEach(function(device) {
            deviceData[device.id] = {
                brand: device.brand ? device.brand.toLowerCase() : '',
                model: device.model ? device.model.toLowerCase() : '',
                complaint: device.problem_description,
                additional: device.additional_complaint ? device.additional_complaint : '',
                fault: device.actual_fault ? device.actual_fault : ''
            };
        });

        // Inventory Data Setup for Smart Dropdown
        const rawInventories = @json($inventories ?? []);
        window.currentInventoryOptions = '<option value="">Select Device First</option>';

        function updateInventoryDropdowns(selectedDeviceId) {
            let selectedDevice = deviceData[selectedDeviceId];
            let optionsHTML = '<option value="">None</option>';

            if (selectedDevice) {
                let cleanDeviceModel = selectedDevice.model.replace(/original|copy|tft|oled|incell/gi, '').trim();

                let filteredParts = rawInventories.filter(inv => {
                    // Show Universal parts
                    if (inv.brand && inv.brand.toLowerCase() === 'universal') return true;
                    if (inv.model && (inv.model.toLowerCase() === 'all' || inv.model.toLowerCase() === 'universal')) return true;

                    // Match Exact Brand and Model
                    let invBrandMatches = inv.brand ? (inv.brand.toLowerCase() === selectedDevice.brand) : true;
                    let invModelMatches = inv.model ? (cleanDeviceModel.includes(inv.model.toLowerCase()) || inv.model.toLowerCase().includes(cleanDeviceModel)) : true;

                    return invBrandMatches && invModelMatches;
                });

                if(filteredParts.length === 0) {
                    optionsHTML += `<option value="" disabled>No exact parts in stock</option>`;
                } else {
                    filteredParts.forEach(inv => {
                        optionsHTML += `<option value="${inv.id}" data-price="${inv.selling_price}">${inv.name} (Stock: ${inv.stock})</option>`;
                    });
                }
            } else {
                optionsHTML = '<option value="">Select Device First</option>';
            }

            document.querySelectorAll('.hw-part').forEach(select => {
                let currentValue = select.value;
                select.innerHTML = optionsHTML;
                if (select.querySelector(`option[value="${currentValue}"]`)) {
                    select.value = currentValue;
                }
            });

            window.currentInventoryOptions = optionsHTML;
        }

        function applyPartPrice(selectElement) {
            let selectedOption = selectElement.options[selectElement.selectedIndex];
            let price = selectedOption.getAttribute('data-price');

            if (price) {
                let row = selectElement.closest('.hw-row');
                let amountInput = row.querySelector('.hw-amount');
                let descInput = row.querySelector('.hw-desc');

                amountInput.value = parseFloat(price).toFixed(2);

                // Only fill if empty
                if (descInput.value.trim() === '') {
                    let partName = selectedOption.text.split(' (Stock')[0];
                    descInput.value = partName;
                }

                calculateTotal();
                syncRowRequirement(amountInput);
            }
        }
    </script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md border border-red-300">
                            <ul class="list-disc pl-5">
                                @forelse($errors->all() as $error) <li>{{ $error }}</li> @empty @endforelse
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bills.store') }}" id="billingForm">
                        @csrf

                        <!-- Select Device -->
                        <div class="mb-6 p-5 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                            <x-input-label for="device_id" class="text-gray-700 font-bold mb-2" :value="__('Select Device *')" />
                            <select id="device_id" name="device_id" class="block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm font-semibold text-gray-900" required onchange="showDeviceDetails(this.value)">
                                <option value="" disabled selected>Select Device</option>
                                @foreach($devices as $d)
                                    @php
                                        $cleanModel = trim(preg_replace('/^' . preg_quote($d->brand, '/') . '\s+/i', '', $d->model));
                                    @endphp
                                    <option value="{{ $d->id }}">
                                        {{ $d->customer->name }} ({{ $d->customer->phone }}) - {{ $d->brand }} {{ $cleanModel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Smart Info Box for Selected Device -->
                        <div id="smartInfoBox" class="hidden mb-6 bg-blue-50/50 border border-blue-200 rounded-lg p-5 shadow-sm">
                            <h3 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-4 border-b border-blue-200 pb-2">Auto-Detected Issues (Must Handle All)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div class="bg-white p-3 rounded shadow-sm border border-gray-100">
                                    <div class="flex justify-between items-center mb-2 border-b border-gray-50 pb-1">
                                        <span class="text-[11px] font-bold text-gray-700">1. Customer Complaint:</span>
                                        <button type="button" onclick="addAll_cmp()" class="text-[10px] bg-indigo-600 text-white px-2 py-1 rounded shadow-sm hover:bg-indigo-700 font-bold flex items-center gap-1">✨ Add All</button>
                                    </div>
                                    <div id="display_complaint" class="space-y-1 min-h-[40px]"></div>
                                </div>
                                <div class="bg-white p-3 rounded shadow-sm border border-gray-100 relative">
                                    <div class="flex justify-between items-center mb-2 border-b border-gray-50 pb-1">
                                        <span class="text-[11px] font-bold text-gray-700">2. Additional Info:</span>
                                        <button type="button" onclick="addAll_add()" class="text-[10px] bg-indigo-600 text-white px-2 py-1 rounded shadow-sm hover:bg-indigo-700 font-bold flex items-center gap-1">✨ Add All</button>
                                    </div>
                                    <div id="display_additional" class="space-y-1 min-h-[40px]"></div>
                                </div>
                                <div class="bg-white p-3 rounded shadow-sm border border-gray-100">
                                    <div class="flex justify-between items-center mb-2 border-b border-gray-50 pb-1">
                                        <span class="text-[11px] font-bold text-gray-700">3. Actual Fault:</span>
                                        <button type="button" onclick="addAll_flt()" class="text-[10px] bg-indigo-600 text-white px-2 py-1 rounded shadow-sm hover:bg-indigo-700 font-bold flex items-center gap-1">✨ Add All</button>
                                    </div>
                                    <div id="display_fault" class="space-y-1 min-h-[40px]"></div>
                                </div>
                            </div>
                        </div>

                        <!-- 2-Column Layout (Adjusted Widths: HW 68% / SW 32%) -->
                        <div class="flex flex-col xl:flex-row gap-6 mb-6 items-stretch">

                            <!-- Hardware Section (Increased outer width to 68%) -->
                            <div class="w-full xl:w-[68%] border border-blue-300 rounded-lg shadow-sm bg-white flex flex-col h-full overflow-hidden">
                                <div class="bg-blue-50 px-4 py-3 border-b border-blue-200 flex justify-between items-center">
                                    <h3 class="text-blue-800 font-bold text-sm uppercase">Hardware Repairs</h3>
                                    <button type="button" id="addHardwareBtn" class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded hover:bg-blue-700 shadow-sm font-bold flex items-center gap-1 cursor-pointer">
                                        + Add Hardware
                                    </button>
                                </div>
                                <div class="p-4 bg-gray-50 flex-grow rounded-b-lg" id="hardwareContainer">
                                    <div class="p-3 mb-3 bg-white border border-gray-200 rounded-md shadow-sm item-row hw-row flex flex-wrap lg:flex-nowrap gap-2 items-end">

                                        <!-- Service Name gets 34% -->
                                        <div class="w-full lg:w-auto" style="flex: 1 1 34%;">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Service Name *</label>
                                            <input type="text" name="hardware_items[0][description]" list="hardwareSuggestions" class="hw-desc block w-full py-2 px-3 border-gray-300 rounded shadow-sm text-sm focus:border-blue-500 placeholder-gray-400" placeholder="Ex: Display Change" oninput="syncRowRequirement(this)">
                                            <input type="hidden" class="linked-id" value="">
                                        </div>

                                        <!-- Select Part gets 33%, added pr-8 and truncate to fix arrow overlap -->
                                        <div class="w-full lg:w-auto" style="flex: 1 1 33%;">
                                            <label class="block text-[10px] font-bold text-indigo-500 uppercase mb-1">Select Part</label>
                                            <select name="hardware_items[0][inventory_id]" class="hw-part block w-full py-2 pl-3 pr-8 border-indigo-200 bg-indigo-50 rounded shadow-sm text-sm focus:border-indigo-500 text-indigo-700 font-semibold truncate" onchange="applyPartPrice(this)">
                                                <option value="">Select Device First</option>
                                            </select>
                                        </div>

                                        <!-- Warranty gets 15% -->
                                        <div class="w-full lg:w-auto" style="flex: 1 1 15%;">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Warranty *</label>
                                            <input type="text" name="hardware_items[0][warranty]" list="warrantySuggestions" class="hw-warranty block w-full py-2 px-3 border-gray-300 rounded shadow-sm text-sm focus:border-blue-500 placeholder-gray-400" placeholder="Ex: None" oninput="syncRowRequirement(this)">
                                        </div>

                                        <!-- Amount gets 18% -->
                                        <div class="w-full lg:w-auto" style="flex: 1 1 18%;">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Amount *</label>
                                            <div class="flex shadow-sm rounded-md">
                                                <span class="inline-flex items-center px-2 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-500 font-bold text-sm">৳</span>
                                                <input type="number" name="hardware_items[0][amount]" step="0.01" min="0" class="hw-amount item-amount flex-1 block w-full px-2 py-2 rounded-none rounded-r-md text-sm border-gray-300 focus:border-blue-500 font-semibold" oninput="calculateTotal(); syncRowRequirement(this);">
                                            </div>
                                        </div>

                                        <div class="w-full lg:w-auto flex justify-center lg:justify-end" style="flex: 0 0 32px; padding-bottom: 4px;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Software Section (Decreased outer width to 32%) -->
                            <div class="w-full xl:w-[32%] border border-green-300 rounded-lg shadow-sm bg-white flex flex-col h-full overflow-hidden">
                                <div class="bg-green-50 px-4 py-3 border-b border-green-200 flex justify-between items-center">
                                    <h3 class="text-green-800 font-bold text-sm uppercase">Software Services</h3>
                                    <button type="button" id="addSoftwareBtn" class="text-xs bg-green-600 text-white px-3 py-1.5 rounded hover:bg-green-700 shadow-sm font-bold flex items-center gap-1 cursor-pointer">
                                        + Add Software
                                    </button>
                                </div>
                                <div class="p-4 bg-gray-50 flex-grow rounded-b-lg" id="softwareContainer">
                                    <div class="p-3 mb-3 bg-white border border-gray-200 rounded-md shadow-sm item-row sw-row flex gap-2 items-end">
                                        <div style="flex: 1 1 65%;">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Service Name *</label>
                                            <input type="text" name="software_items[0][description]" list="softwareSuggestions" class="sw-desc block w-full py-2 px-3 border-gray-300 rounded shadow-sm text-sm focus:border-green-500 placeholder-gray-400" placeholder="Ex: FRP Unlock" oninput="syncRowRequirement(this)">
                                            <input type="hidden" class="linked-id" value="">
                                        </div>
                                        <div style="flex: 1 1 35%;">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Amount *</label>
                                            <div class="flex shadow-sm rounded-md">
                                                <span class="inline-flex items-center px-2 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-500 font-bold text-sm">৳</span>
                                                <input type="number" name="software_items[0][amount]" step="0.01" min="0" class="sw-amount item-amount flex-1 block w-full px-2 py-2 rounded-none rounded-r-md text-sm border-gray-300 focus:border-green-500 font-semibold" oninput="calculateTotal(); syncRowRequirement(this);">
                                            </div>
                                        </div>
                                        <div style="flex: 0 0 32px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary -->
                        <div class="flex flex-wrap md:flex-nowrap gap-4 mb-6 bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm w-full">
                            <div class="flex-1 min-w-[150px]">
                                <x-input-label for="total_amount" :value="__('Total Bill *')" />
                                <div class="flex mt-1 shadow-sm rounded-md">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-200 text-gray-600 font-bold">৳</span>
                                    <input id="total_amount" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 font-semibold text-base text-blue-600 bg-gray-100 cursor-not-allowed" type="number" step="0.01" name="total_amount" value="0.00" readonly required />
                                </div>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <x-input-label for="discount" :value="__('Discount')" />
                                <div class="flex mt-1 shadow-sm rounded-md">
                                    <input id="discount" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border-gray-300 focus:border-indigo-500 font-semibold text-base" type="number" step="0.01" name="discount" value="0" oninput="calculateTotal()" />
                                    <select id="discount_type" name="discount_type" class="rounded-none rounded-r-md border-l-0 border-gray-300 bg-gray-100 text-gray-700 font-bold py-2 focus:border-indigo-500">
                                        <option value="flat">৳</option>
                                        <option value="percentage">%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <x-input-label for="net_payable" :value="__('Net Payable')" />
                                <div class="flex mt-1 shadow-sm rounded-md">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-200 text-gray-600 font-bold">৳</span>
                                    <input id="net_payable" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 font-semibold text-base text-indigo-600 bg-gray-100 cursor-not-allowed" type="number" step="0.01" value="0.00" readonly />
                                </div>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <x-input-label for="paid_amount" :value="__('Paid Amount *')" />
                                <div class="flex mt-1 shadow-sm rounded-md">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-md border border-r-0 border-green-300 bg-green-100 text-green-700 font-bold">৳</span>
                                    <input id="paid_amount" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:border-green-500 font-semibold text-base text-green-600" type="number" step="0.01" name="paid_amount" value="0" required oninput="calculateDue()" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <x-input-label for="due_amount" :value="__('Due Amount')" />
                                <div class="flex mt-1 shadow-sm rounded-md">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-md border border-r-0 border-red-200 bg-red-50 text-red-600 font-bold">৳</span>
                                    <input id="due_amount" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 font-semibold text-base text-red-500 bg-red-50 cursor-not-allowed" type="number" step="0.01" value="0.00" readonly />
                                </div>
                            </div>
                        </div>

                        <!-- Special Note -->
                        <div class="mb-4">
                            <x-input-label for="note" :value="__('Special Note (Optional)')" />
                            <textarea id="note" name="note" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm placeholder-gray-400" rows="2" placeholder="Write any special note here...">{{ old('note') }}</textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end mt-4 mb-2 gap-4">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 transition" href="{{ route('bills.index') }}">Cancel</a>
                            <x-primary-button type="submit" class="bg-indigo-600 hover:bg-indigo-700 shadow-md px-6 py-2.5 text-sm font-bold tracking-wider rounded-md">Create Bill & Deduct Stock</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Validation Modal -->
    <div id="billValidationModal" class="fixed inset-0 z-[120] hidden overflow-y-auto bg-gray-900 bg-opacity-60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl w-full sm:my-8" style="max-width: 28rem !important; margin: 0 auto;">
                <div class="bg-red-50 px-6 py-4 border-b border-red-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-red-800" id="billModalTitle">Action Required!</h3>
                    </div>
                    <button type="button" onclick="closeBillModal()" class="text-red-400 hover:text-red-700 rounded-lg p-1.5 cursor-pointer">✕</button>
                </div>
                <div class="p-6 text-gray-700 font-medium text-sm whitespace-pre-wrap leading-relaxed" id="billModalMessage"></div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-xl border-t border-gray-100">
                    <button type="button" onclick="closeBillModal()" class="px-5 py-2 bg-red-600 text-white rounded-md font-bold hover:bg-red-700 shadow-sm text-sm cursor-pointer">OK, Got It</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script for Full Automagic Validation & Inventory Sync -->
    <script>
        let issueStatuses = {};
        let deviceIssues = { cmp: [], add: [], flt: [] };

        window.addEventListener('DOMContentLoaded', () => {
            let initialDevice = document.getElementById('device_id').value;
            if(initialDevice) {
                updateInventoryDropdowns(initialDevice);
            }
        });

        function showDeviceDetails(deviceId) {
            updateInventoryDropdowns(deviceId);

            const infoBox = document.getElementById('smartInfoBox');
            deviceIssues = { cmp: [], add: [], flt: [] };
            issueStatuses = {};

            if (deviceId && deviceData[deviceId]) {
                document.getElementById('display_complaint').innerHTML = formatInteractiveList(deviceData[deviceId].complaint, 'cmp');
                document.getElementById('display_additional').innerHTML = formatInteractiveList(deviceData[deviceId].additional, 'add');
                document.getElementById('display_fault').innerHTML = formatInteractiveList(deviceData[deviceId].fault, 'flt');
                infoBox.classList.remove('hidden');
            } else {
                infoBox.classList.add('hidden');
            }
        }

        function showBillModal(title, message, focusEl = null) {
            document.getElementById('billModalTitle').innerText = title;
            document.getElementById('billModalMessage').innerText = message;
            window.focusTargetAfterModal = focusEl;
            document.getElementById('billValidationModal').classList.remove('hidden');
        }

        function closeBillModal() {
            document.getElementById('billValidationModal').classList.add('hidden');
            if (window.focusTargetAfterModal) setTimeout(() => window.focusTargetAfterModal.focus(), 100);
        }

        function escapeHtml(str) { return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

        function generateRowHtml(prefix, uniqueId, indexCount, typeBadge, cleanName, mode) {
            let extraAction = `
                <button type="button" onclick="showMergeBox('${uniqueId}')" class="text-[10px] bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-600 px-1.5 py-0.5 rounded font-bold border border-blue-200 transition" title="Merge / Covered by another service">🔗</button>
                <button type="button" onclick="showSkipReasonBox('${uniqueId}')" class="text-[10px] bg-red-50 hover:bg-red-600 hover:text-white text-red-600 px-1.5 py-0.5 rounded font-bold border border-red-200 transition" title="Skip / Cannot Repair">❌</button>
            `;
            return `
            <div id="row_${uniqueId}" class="transition-opacity duration-300">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-1.5 mb-1.5 last:border-0 last:pb-0 last:mb-0">
                    <span class="font-semibold text-xs flex-1 truncate" title="${escapeHtml(cleanName)}">
                        ${indexCount}. ${typeBadge}${escapeHtml(cleanName)}
                    </span>
                    <div class="flex gap-1 items-center" id="action_group_${uniqueId}">
                        <button type="button" onclick="quickAddService('${escapeHtml(cleanName)}', '${mode}', '${uniqueId}')" class="text-[10px] bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 px-2 py-1 rounded font-bold border border-indigo-200 shadow-sm whitespace-nowrap transition">+ Add</button>
                        ${extraAction}
                    </div>
                </div>
                <div id="skip_reason_box_${uniqueId}" class="hidden mb-2 pt-1 border-t border-amber-100 flex items-center gap-2">
                    <input type="text" id="skip_reason_input_${uniqueId}" class="block w-full text-[10px] border-red-300 rounded focus:border-red-500 py-1" placeholder="Reason for skipping...">
                    <button type="button" onclick="confirmSkip('${uniqueId}', '${escapeHtml(cleanName)}')" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-[10px] font-bold">Save</button>
                </div>
                <div id="merge_box_${uniqueId}" class="hidden mb-2 pt-1 border-t border-blue-100 flex items-center gap-2">
                    <input type="text" id="merge_input_${uniqueId}" class="block w-full text-[10px] border-blue-300 rounded focus:border-blue-500 py-1" placeholder="Covered by (ex: Flashing)...">
                    <button type="button" onclick="confirmMerge('${uniqueId}', '${escapeHtml(cleanName)}')" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-[10px] font-bold">Merge</button>
                </div>
            </div>`;
        }

        function formatInteractiveList(rawText, prefix) {
            if (!rawText || !rawText.trim()) return `<span class="text-gray-400 italic font-normal">N/A</span>`;

            let hwList = [];
            let swList = [];

            try {
                let parsed = JSON.parse(rawText);
                if (parsed.hw) hwList = parsed.hw;
                if (parsed.sw) swList = parsed.sw;
            } catch(e) {
                let lines = rawText.split(/\r?\n/);
                let currentMode = 'hw';
                lines.forEach((line) => {
                    let lower = line.toLowerCase();
                    if(lower.includes('[hardware]')) { currentMode = 'hw'; return; }
                    if(lower.includes('[software]')) { currentMode = 'sw'; return; }

                    let cleanName = line.replace(/^\d+[\.\)\-]\s*/, '').replace(/\[HW\] |\[SW\] /ig, '').trim();
                    if(cleanName) {
                        if (currentMode === 'hw') hwList.push(cleanName);
                        else swList.push(cleanName);
                    }
                });
            }

            deviceIssues[prefix] = [];
            let html = '';
            let indexCount = 1;

            hwList.forEach(cleanName => {
                const uniqueId = `${prefix}_${indexCount}`;
                let typeBadge = '<span class="bg-blue-100 text-blue-700 px-1 py-0.5 rounded text-[9px] mr-1 border border-blue-200">HW</span>';
                deviceIssues[prefix].push({ id: uniqueId, name: cleanName, type: 'hardware' });
                html += generateRowHtml(prefix, uniqueId, indexCount, typeBadge, cleanName, 'hardware');
                indexCount++;
            });

            swList.forEach(cleanName => {
                const uniqueId = `${prefix}_${indexCount}`;
                let typeBadge = '<span class="bg-green-100 text-green-700 px-1 py-0.5 rounded text-[9px] mr-1 border border-green-200">SW</span>';
                deviceIssues[prefix].push({ id: uniqueId, name: cleanName, type: 'software' });
                html += generateRowHtml(prefix, uniqueId, indexCount, typeBadge, cleanName, 'software');
                indexCount++;
            });

            return html || `<span class="text-gray-400 italic font-normal">N/A</span>`;
        }

        function addAll_cmp() { deviceIssues.cmp.forEach(issue => { if(!issueStatuses[issue.id]) quickAddService(issue.name, issue.type, issue.id); }); }
        function addAll_add() { deviceIssues.add.forEach(issue => { if(!issueStatuses[issue.id]) quickAddService(issue.name, issue.type, issue.id); }); }
        function addAll_flt() { deviceIssues.flt.forEach(issue => { if(!issueStatuses[issue.id]) quickAddService(issue.name, issue.type, issue.id); }); }

        function quickAddService(issueName, type, uniqueId) {
            let targetInput = null;
            if (type === 'hardware') {
                const hwRows = document.querySelectorAll('.hw-row');
                const lastRow = hwRows[hwRows.length - 1];
                const lastInput = lastRow.querySelector('.hw-desc');

                if (lastInput.value.trim() === '' && lastRow.querySelector('.hw-amount').value.trim() === '') targetInput = lastInput;
                else {
                    document.getElementById('addHardwareBtn').click();
                    const newRows = document.querySelectorAll('.hw-row');
                    targetInput = newRows[newRows.length - 1].querySelector('.hw-desc');
                }
            } else {
                const swRows = document.querySelectorAll('.sw-row');
                const lastRow = swRows[swRows.length - 1];
                const lastInput = lastRow.querySelector('.sw-desc');

                if (lastInput.value.trim() === '' && lastRow.querySelector('.sw-amount').value.trim() === '') targetInput = lastInput;
                else {
                    document.getElementById('addSoftwareBtn').click();
                    const newRows = document.querySelectorAll('.sw-row');
                    targetInput = newRows[newRows.length - 1].querySelector('.sw-desc');
                }
            }

            if (targetInput) {
                targetInput.value = issueName;
                targetInput.nextElementSibling.value = uniqueId;
                syncRowRequirement(targetInput);

                document.getElementById(`row_${uniqueId}`).classList.add('opacity-50');
                const btnGroup = document.getElementById(`action_group_${uniqueId}`);
                if (btnGroup) btnGroup.innerHTML = `<span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-1 rounded font-bold border border-emerald-200">✓ Added</span>`;
                issueStatuses[uniqueId] = 'added';

                document.getElementById(`skip_reason_box_${uniqueId}`).classList.add('hidden');
                document.getElementById(`merge_box_${uniqueId}`).classList.add('hidden');
            }
        }

        function showSkipReasonBox(uniqueId) {
            document.getElementById(`merge_box_${uniqueId}`).classList.add('hidden');
            document.getElementById(`skip_reason_box_${uniqueId}`).classList.remove('hidden');
            document.getElementById(`skip_reason_input_${uniqueId}`).focus();
        }

        function showMergeBox(uniqueId) {
            document.getElementById(`skip_reason_box_${uniqueId}`).classList.add('hidden');
            document.getElementById(`merge_box_${uniqueId}`).classList.remove('hidden');
            document.getElementById(`merge_input_${uniqueId}`).focus();
        }

        function confirmSkip(uniqueId, issueName) {
            const reason = document.getElementById(`skip_reason_input_${uniqueId}`).value.trim();
            if (!reason) { showBillModal('Reason Required', 'Please enter a reason for skipping this item.'); return; }

            issueStatuses[uniqueId] = 'skipped_' + reason;
            document.getElementById(`row_${uniqueId}`).classList.add('opacity-50');
            const btnGroup = document.getElementById(`action_group_${uniqueId}`);
            if (btnGroup) btnGroup.innerHTML = `<span class="text-[10px] bg-red-100 text-red-800 px-2 py-1 rounded font-bold border border-red-200">❌ Skipped</span>`;
            document.getElementById(`skip_reason_box_${uniqueId}`).classList.add('hidden');
        }

        function confirmMerge(uniqueId, issueName) {
            const reason = document.getElementById(`merge_input_${uniqueId}`).value.trim();
            if (!reason) { showBillModal('Merge Reference Required', 'Please mention which service covers this issue (ex: Flashing).'); return; }

            issueStatuses[uniqueId] = 'merged_' + reason;
            document.getElementById(`row_${uniqueId}`).classList.add('opacity-50');
            const btnGroup = document.getElementById(`action_group_${uniqueId}`);
            if (btnGroup) btnGroup.innerHTML = `<span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-1 rounded font-bold border border-blue-200">🔗 Merged</span>`;
            document.getElementById(`merge_box_${uniqueId}`).classList.add('hidden');
        }

        function removeRow(button) {
            const row = button.closest('.item-row');
            let linkedId = row.querySelector('.linked-id').value;

            if (linkedId && issueStatuses[linkedId]) {
                document.getElementById(`row_${linkedId}`).classList.remove('opacity-50');
                const btnGroup = document.getElementById(`action_group_${linkedId}`);
                let issue = deviceIssues.cmp.find(i=>i.id===linkedId) || deviceIssues.add.find(i=>i.id===linkedId) || deviceIssues.flt.find(i=>i.id===linkedId);

                if (btnGroup && issue) {
                    let extra = `
                        <button type="button" onclick="showMergeBox('${linkedId}')" class="text-[10px] bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-600 px-1.5 py-0.5 rounded font-bold border border-blue-200 transition">🔗</button>
                        <button type="button" onclick="showSkipReasonBox('${linkedId}')" class="text-[10px] bg-red-50 hover:bg-red-600 hover:text-white text-red-600 px-1.5 py-0.5 rounded font-bold border border-red-200 transition">❌</button>
                    `;
                    btnGroup.innerHTML = `<button type="button" onclick="quickAddService('${escapeHtml(issue.name)}', '${issue.type}', '${linkedId}')" class="text-[10px] bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 px-2 py-1 rounded font-bold border border-indigo-200">+ Add</button>${extra}`;
                }
                issueStatuses[linkedId] = false;
            }
            row.remove(); calculateTotal();
        }

        const trashIcon = `<svg class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>`;

        let hwIndex = 1;
        document.getElementById('addHardwareBtn').addEventListener('click', function() {
            const container = document.getElementById('hardwareContainer');
            const rowHTML = `
            <div class="p-3 mb-3 bg-white border border-gray-200 rounded-md shadow-sm item-row hw-row flex flex-wrap lg:flex-nowrap gap-2 items-end">
                <div class="w-full lg:w-auto" style="flex: 1 1 34%;">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Service Name *</label>
                    <input type="text" name="hardware_items[${hwIndex}][description]" list="hardwareSuggestions" class="hw-desc block w-full py-2 px-3 border-gray-300 rounded shadow-sm text-sm focus:border-blue-500 placeholder-gray-400" oninput="syncRowRequirement(this)">
                    <input type="hidden" class="linked-id" value="">
                </div>
                <div class="w-full lg:w-auto" style="flex: 1 1 33%;">
                    <label class="block text-[10px] font-bold text-indigo-500 uppercase mb-1">Select Part</label>
                    <select name="hardware_items[${hwIndex}][inventory_id]" class="hw-part block w-full py-2 pl-3 pr-8 border-indigo-200 bg-indigo-50 rounded shadow-sm text-sm focus:border-indigo-500 text-indigo-700 font-semibold truncate" onchange="applyPartPrice(this)">
                        ${window.currentInventoryOptions}
                    </select>
                </div>
                <div class="w-full lg:w-auto" style="flex: 1 1 15%;">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Warranty *</label>
                    <input type="text" name="hardware_items[${hwIndex}][warranty]" list="warrantySuggestions" class="hw-warranty block w-full py-2 px-3 border-gray-300 rounded shadow-sm text-sm focus:border-blue-500 placeholder-gray-400" oninput="syncRowRequirement(this)">
                </div>
                <div class="w-full lg:w-auto" style="flex: 1 1 18%;">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Amount *</label>
                    <div class="flex shadow-sm rounded-md">
                        <span class="inline-flex items-center px-2 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-500 font-bold text-sm">৳</span>
                        <input type="number" name="hardware_items[${hwIndex}][amount]" step="0.01" min="0" class="hw-amount item-amount flex-1 block w-full px-2 py-2 rounded-none rounded-r-md text-sm border-gray-300 focus:border-blue-500 font-semibold" oninput="calculateTotal(); syncRowRequirement(this);">
                    </div>
                </div>
                <div class="w-full lg:w-auto flex justify-center lg:justify-end" style="flex: 0 0 32px; padding-bottom: 4px;">
                    <button type="button" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded transition cursor-pointer" onclick="removeRow(this)">${trashIcon}</button>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', rowHTML); hwIndex++;
        });

        let swIndex = 1;
        document.getElementById('addSoftwareBtn').addEventListener('click', function() {
            const container = document.getElementById('softwareContainer');
            const rowHTML = `
            <div class="p-3 mb-3 bg-white border border-gray-200 rounded-md shadow-sm item-row sw-row flex gap-2 items-end">
                <div style="flex: 1 1 65%;">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Service Name *</label>
                    <input type="text" name="software_items[${swIndex}][description]" list="softwareSuggestions" class="sw-desc block w-full py-2 px-3 border-gray-300 rounded shadow-sm text-sm focus:border-green-500 placeholder-gray-400" oninput="syncRowRequirement(this)">
                    <input type="hidden" class="linked-id" value="">
                </div>
                <div style="flex: 1 1 35%;">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Amount *</label>
                    <div class="flex shadow-sm rounded-md">
                        <span class="inline-flex items-center px-2 py-2 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-500 font-bold text-sm">৳</span>
                        <input type="number" name="software_items[${swIndex}][amount]" step="0.01" min="0" class="sw-amount item-amount flex-1 block w-full px-2 py-2 rounded-none rounded-r-md text-sm border-gray-300 focus:border-green-500 font-semibold" oninput="calculateTotal(); syncRowRequirement(this);">
                    </div>
                </div>
                <div style="flex: 0 0 32px; text-align: center; padding-bottom: 4px;">
                    <button type="button" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded transition cursor-pointer" onclick="removeRow(this)">${trashIcon}</button>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', rowHTML); swIndex++;
        });

        function syncRowRequirement(input) {
            const row = input.closest('.item-row');
            if (!row) return;
            if (row.classList.contains('hw-row')) {
                const desc = row.querySelector('.hw-desc');
                const warranty = row.querySelector('.hw-warranty');
                const amount = row.querySelector('.hw-amount');
                const hasData = desc.value.trim() !== '' || warranty.value.trim() !== '' || amount.value.trim() !== '';
                desc.required = hasData; warranty.required = hasData; amount.required = hasData;
            } else if (row.classList.contains('sw-row')) {
                const desc = row.querySelector('.sw-desc');
                const amount = row.querySelector('.sw-amount');
                const hasData = desc.value.trim() !== '' || amount.value.trim() !== '';
                desc.required = hasData; amount.required = hasData;
            }
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.item-amount').forEach(input => { const val = parseFloat(input.value); if(!isNaN(val)) total += val; });
            document.getElementById('total_amount').value = total.toFixed(2);

            const discountInput = document.getElementById('discount');
            let discount = parseFloat(discountInput.value) || 0;
            let finalDiscount = 0;

            if(document.getElementById('discount_type').value === 'percentage') {
                if(discount > 100) { discountInput.value = 100; discount = 100; }
                finalDiscount = (total * discount) / 100;
            } else {
                if(discount > total) { discountInput.value = total; discount = total; }
                finalDiscount = discount;
            }
            document.getElementById('net_payable').value = (total - finalDiscount).toFixed(2);
            calculateDue();
        }

        function calculateDue() {
            let netPayable = parseFloat(document.getElementById('net_payable').value) || 0;
            let paidInput = document.getElementById('paid_amount');
            let paidAmount = parseFloat(paidInput.value) || 0;
            if(paidAmount > netPayable) { paidInput.value = netPayable; paidAmount = netPayable; }
            document.getElementById('due_amount').value = (netPayable - paidAmount).toFixed(2);
        }
        document.getElementById('discount_type').addEventListener('change', calculateTotal);

        document.getElementById('billingForm').addEventListener('submit', function(e) {
            const deviceSelect = document.getElementById('device_id');
            if (!deviceSelect.value) { e.preventDefault(); showBillModal('Select a Device', 'Please select a device.'); return; }

            let unresolved = [];
            ['cmp', 'add', 'flt'].forEach(cat => {
                deviceIssues[cat].forEach(issue => {
                    if (!issueStatuses[issue.id]) unresolved.push(issue.name);
                });
            });

            if (unresolved.length > 0) {
                e.preventDefault();
                showBillModal('Strict Validation Failed!', `You have ${unresolved.length} unhandled issue(s) from the Auto-Detected list.\n\nPlease "+ Add", "🔗 Merge", or "❌ Skip" them.\n\nMissing Items: ${unresolved.join(', ')}`);
                return;
            }

            let hasAtLeastOneService = false;
            document.querySelectorAll('.hw-row').forEach(row => { if(row.querySelector('.hw-desc').value.trim() !== '') hasAtLeastOneService = true; });
            document.querySelectorAll('.sw-row').forEach(row => { if(row.querySelector('.sw-desc').value.trim() !== '') hasAtLeastOneService = true; });

            if (!hasAtLeastOneService) { e.preventDefault(); showBillModal('No Services Added', 'Please add at least one hardware or software service to generate this bill.'); return; }

            const noteField = document.getElementById('note');
            let cleanUserNote = noteField.value.replace(/\[ADDITIONAL_.*?\]\n?/g, '').trim();
            let additionalTags = [];

            ['cmp', 'add', 'flt'].forEach(cat => {
                deviceIssues[cat].forEach((issue) => {
                    const status = issueStatuses[issue.id];
                    if (status && status.startsWith('skipped_')) {
                        additionalTags.push(`[ADDITIONAL_SKIPPED: ${issue.name} | REASON: ${status.split('skipped_')[1]}]`);
                    } else if (status && status.startsWith('merged_')) {
                        additionalTags.push(`[ADDITIONAL_RESOLVED: ${issue.name} (Covered by: ${status.split('merged_')[1]})]`);
                    } else if (status === 'added' && cat === 'add') {
                        additionalTags.push(`[ADDITIONAL_RESOLVED: ${issue.name}]`);
                    }
                });
            });

            if (additionalTags.length > 0) {
                noteField.value = additionalTags.join("\n") + (cleanUserNote ? "\n" + cleanUserNote : '');
            }
        });
    </script>
</x-app-layout>
