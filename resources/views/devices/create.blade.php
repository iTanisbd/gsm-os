<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Receive New Device (নতুন ডিভাইস গ্রহণ করুন)') }}
        </h2>
    </x-slot>

    <!-- Auto-Suggestion Datalists (Updated with Modern Tech Terms) -->
    <datalist id="hwSuggestions">
        <option value="Display Replacement">
        <option value="Touch Glass Change">
        <option value="Battery Replacement">
        <option value="Charging Port Repair">
        <option value="Speaker/Mic Fix">
        <option value="Motherboard Short">
        <option value="IC Replacement">
    </datalist>
    <datalist id="swSuggestions">
        <option value="FRP Bypass">
        <option value="Mi Account Unlock">
        <option value="Stock Firmware Flash">
        <option value="Dead Boot Repair">
        <option value="Network Unlock">
        <option value="Baseband Repair">
        <option value="Auto Restart Fix">
    </datalist>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">

                    <form method="POST" action="{{ route('devices.store') }}" id="deviceCreateForm" onsubmit="return prepareSubmit()">
                        @csrf

                        <!-- Hidden Inputs for Pure JSON Data Structure -->
                        <textarea id="hidden_problem_description" name="problem_description" class="hidden"></textarea>
                        <textarea id="hidden_actual_fault" name="actual_fault" class="hidden"></textarea>
                        <textarea id="hidden_additional_complaint" name="additional_complaint" class="hidden"></textarea>

                        <!-- Fixed Array Inputs for Agreements (Bulletproof Logic) -->
                        <input type="hidden" name="terms[]" id="term_dead_hidden" value="Dead Risk Not Accepted">
                        <input type="hidden" name="terms[]" id="term_data_hidden" value="Data Loss Not Accepted">

                        <!-- Select Customer (Typography Synced) -->
                        <div class="mb-6 p-5 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                            <x-input-label for="customer_id" class="text-gray-700 font-bold mb-2" :value="__('Select Customer *')" />
                            <select id="customer_id" name="customer_id" class="block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm text-sm font-semibold text-gray-900 transition-none" required>
                                <option value="" disabled selected>-- কাস্টমার নির্বাচন করুন --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{$customer->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Basic Device Info -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <x-input-label for="brand" :value="__('Brand * (Single Word)')" />
                                <x-text-input id="brand" class="block mt-1 w-full text-sm placeholder-gray-400" type="text" name="brand" :value="old('brand')" required placeholder="Ex: Samsung" oninput="enforceSingleWordBrand(this); cleanModelName();" />
                            </div>
                            <div>
                                <x-input-label for="model" :value="__('Model *')" />
                                <x-text-input id="model" class="block mt-1 w-full text-sm placeholder-gray-400" type="text" name="model" :value="old('model')" required placeholder="Ex: Galaxy S23" oninput="cleanModelName();" />
                            </div>
                            <div>
                                <x-input-label for="imei" :value="__('IMEI / Serial')" />
                                <x-text-input id="imei" class="block mt-1 w-full text-sm placeholder-gray-400" type="text" name="imei" :value="old('imei')" placeholder="Full IMEI or Last 4 digits" />
                            </div>
                            <!-- Smart Password Box -->
                            <div class="relative flex flex-col justify-end" id="password_container">
                                <div class="flex items-center justify-between mb-1">
                                    <x-input-label for="device_password" :value="__('Lock Type')" class="mb-0" />
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-1 text-[10px] font-bold cursor-pointer text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100 hover:bg-indigo-100">
                                            <input type="radio" name="pass_type" value="PIN" checked onchange="togglePattern(false)" class="w-3 h-3 text-indigo-600 focus:ring-indigo-500"> PIN/Pass
                                        </label>
                                        <label class="flex items-center gap-1 text-[10px] font-bold cursor-pointer text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-100 hover:bg-purple-100">
                                            <input type="radio" name="pass_type" value="Pattern" id="radio_pattern" onchange="togglePattern(true)" class="w-3 h-3 text-purple-600 focus:ring-purple-500"> Pattern
                                        </label>
                                    </div>
                                </div>
                                <x-text-input id="device_password" class="block w-full font-mono text-sm text-red-600 bg-red-50 focus:border-red-500 focus:ring-red-500 placeholder-red-300" type="text" name="device_password" :value="old('device_password')" placeholder="Pin / e.g. 14789 for L" />

                                <div id="pattern_grid_box" class="hidden absolute right-0 top-full mt-1 w-32 bg-gray-800 text-white text-xs rounded shadow-lg p-2 z-50 text-center border border-gray-600">
                                    <p class="mb-1 text-gray-300 font-bold border-b border-gray-600 pb-1">Pattern Grid</p>
                                    <div class="grid grid-cols-3 gap-1 font-mono text-lg font-bold">
                                        <span>1</span><span>2</span><span>3</span>
                                        <span>4</span><span>5</span><span>6</span>
                                        <span>7</span><span>8</span><span>9</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- UNIFORM HEIGHT LAYOUT: Initial Complaint & Actual Fault -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-stretch">
                            <!-- Initial Complaint (Left) -->
                            <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col h-full">
                                <x-input-label class="font-bold text-red-600 mb-3 border-b border-gray-100 pb-2" :value="__('1. Initial Complaint (কাস্টমার যা বলেছে) *')" />

                                <div class="grid grid-cols-2 gap-4 flex-1">
                                    <!-- HW -->
                                    <div class="flex flex-col bg-blue-50/30 p-3 rounded border border-blue-100 h-full">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input type="text" id="initial_hw_input" list="hwSuggestions" class="block w-full border-gray-300 focus:border-blue-500 rounded text-xs shadow-sm py-1.5" placeholder="HW Issue...">
                                            <button type="button" onclick="addBadge('initial', 'hw')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                        </div>
                                        <p class="text-[10px] font-bold text-blue-800 uppercase mb-2 border-b border-blue-200 pb-1">Hardware Issues</p>
                                        <div id="list_initial_hw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[80px]"></div>
                                    </div>
                                    <!-- SW -->
                                    <div class="flex flex-col bg-green-50/30 p-3 rounded border border-green-100 h-full">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input type="text" id="initial_sw_input" list="swSuggestions" class="block w-full border-gray-300 focus:border-green-500 rounded text-xs shadow-sm py-1.5" placeholder="SW Issue...">
                                            <button type="button" onclick="addBadge('initial', 'sw')" class="bg-green-600 hover:bg-green-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                        </div>
                                        <p class="text-[10px] font-bold text-green-800 uppercase mb-2 border-b border-green-200 pb-1">Software Issues</p>
                                        <div id="list_initial_sw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[80px]"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actual Fault (Right) -->
                            <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col h-full">
                                <x-input-label class="font-bold text-emerald-600 mb-3 border-b border-gray-100 pb-2" :value="__('2. Actual Fault (টেকনিশিয়ান যা পেলেন)')" />

                                <div class="grid grid-cols-2 gap-4 flex-1">
                                    <!-- HW -->
                                    <div class="flex flex-col bg-emerald-50/30 p-3 rounded border border-emerald-100 h-full">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input type="text" id="fault_hw_input" list="hwSuggestions" class="block w-full border-gray-300 focus:border-emerald-500 rounded text-xs shadow-sm py-1.5" placeholder="+ HW Fault...">
                                            <button type="button" onclick="addBadge('fault', 'hw')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                        </div>
                                        <p class="text-[10px] font-bold text-emerald-800 uppercase mb-2 border-b border-emerald-200 pb-1">Hardware Faults</p>
                                        <div id="list_fault_hw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[80px]"></div>
                                    </div>
                                    <!-- SW -->
                                    <div class="flex flex-col bg-emerald-50/30 p-3 rounded border border-emerald-100 h-full">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input type="text" id="fault_sw_input" list="swSuggestions" class="block w-full border-gray-300 focus:border-emerald-500 rounded text-xs shadow-sm py-1.5" placeholder="+ SW Fault...">
                                            <button type="button" onclick="addBadge('fault', 'sw')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                        </div>
                                        <p class="text-[10px] font-bold text-emerald-800 uppercase mb-2 border-b border-emerald-200 pb-1">Software Faults</p>
                                        <div id="list_fault_sw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[80px]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- UNIFORM HEIGHT LAYOUT: Additional Info & Tech Notes -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-stretch">
                            <!-- Additional Info (Left) -->
                            <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col h-full">
                                <x-input-label class="font-bold text-indigo-600 mb-3 border-b border-gray-100 pb-2" :value="__('3. Additional Info (পরে জানালে)')" />

                                <div class="grid grid-cols-2 gap-4 flex-1">
                                    <div class="flex flex-col bg-indigo-50/30 p-3 rounded border border-indigo-100 h-full">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input type="text" id="additional_hw_input" list="hwSuggestions" class="block w-full border-gray-300 focus:border-indigo-500 rounded text-xs shadow-sm py-1.5" placeholder="HW Info...">
                                            <button type="button" onclick="addBadge('additional', 'hw')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                        </div>
                                        <p class="text-[10px] font-bold text-indigo-800 uppercase mb-2 border-b border-indigo-200 pb-1">Hardware Additions</p>
                                        <div id="list_additional_hw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[80px]"></div>
                                    </div>

                                    <div class="flex flex-col bg-indigo-50/30 p-3 rounded border border-indigo-100 h-full">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input type="text" id="additional_sw_input" list="swSuggestions" class="block w-full border-gray-300 focus:border-indigo-500 rounded text-xs shadow-sm py-1.5" placeholder="SW Info...">
                                            <button type="button" onclick="addBadge('additional', 'sw')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                        </div>
                                        <p class="text-[10px] font-bold text-indigo-800 uppercase mb-2 border-b border-indigo-200 pb-1">Software Additions</p>
                                        <div id="list_additional_sw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[80px]"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tech Notes (Right) -->
                            <div class="bg-purple-50/50 p-5 rounded-lg border border-purple-200 shadow-sm flex flex-col h-full">
                                <x-input-label for="internal_tech_notes" class="font-bold text-purple-700 mb-2 border-b border-purple-200 pb-2" :value="__('4. Internal Tech Notes (গোপন নোট)')" />
                                <textarea id="internal_tech_notes" name="internal_tech_notes" class="block w-full flex-1 min-h-[120px] border-gray-300 focus:border-purple-500 rounded-md shadow-sm resize-none text-sm bg-white placeholder-gray-400" placeholder="Secret notes for staff...">{{ old('internal_tech_notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Pre-repair Checklist & Location -->
                        <div class="mb-6 bg-gray-50 p-6 rounded-md border border-gray-200 shadow-sm">
                            <div class="mb-6">
                                <p class="font-bold text-gray-700 mb-4 border-b pb-2">Pre-repair Checklist (রিসিভ করার সময় অবস্থা):</p>
                                @php
                                    $checklistItems = ['Display Scratches', 'Display Broken', 'Back Glass Broken', 'Camera Glass Broken', 'Battery Swollen', 'Dead Condition', 'Water Damaged', 'No SIM Tray', 'SIM Card Inside', 'SD Card Inside', 'Phone Case/Cover', 'Charger Included', 'Box with IMEI'];
                                @endphp
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-3 gap-x-4 text-sm text-gray-700">
                                    @foreach($checklistItems as $item)
                                        <label class="inline-flex items-center cursor-pointer hover:bg-gray-100 p-1 rounded">
                                            <input type="checkbox" name="pre_repair_checklist[]" value="{{ $item }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <span class="ml-2 font-medium">{{ $item }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <p class="font-bold text-gray-700 mb-4 border-b pb-2">Storage Location:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="rack_number" class="font-semibold text-gray-600 text-xs uppercase" :value="__('Rack Number *')" />
                                        <x-text-input id="rack_number" class="block mt-1 w-full text-sm placeholder-gray-400" type="text" name="rack_number" placeholder="Ex: R-01" required />
                                    </div>
                                    <div>
                                        <x-input-label for="drawer_number" class="font-bold text-gray-600 text-xs uppercase" :value="__('Drawer Number *')" />
                                        <x-text-input id="drawer_number" class="block mt-1 w-full text-sm placeholder-gray-400" type="text" name="drawer_number" placeholder="Ex: D-05" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Risk Agreements & Delivery Date -->
                        <div class="mb-6 bg-indigo-50 p-5 rounded-md border border-indigo-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                            <div class="w-full">
                                <p class="font-bold text-red-800 mb-2 text-sm uppercase tracking-wider">Pre-Repair Agreements</p>
                                <div class="space-y-3 bg-white p-3 rounded border border-indigo-100 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-700 font-bold">1. Dead Risk (No guarantee)</span>
                                        <div class="flex items-center gap-3">
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="radio" name="ui_dead_risk" value="Accepted" onchange="document.getElementById('term_dead_hidden').value = 'Dead Risk Accepted';" class="text-emerald-500 focus:ring-emerald-500 w-3 h-3">
                                                <span class="text-[10px] font-bold text-emerald-700">Accepted</span>
                                            </label>
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="radio" name="ui_dead_risk" value="Not Accepted" checked onchange="document.getElementById('term_dead_hidden').value = 'Dead Risk Not Accepted';" class="text-red-500 focus:ring-red-500 w-3 h-3">
                                                <span class="text-[10px] font-bold text-red-700">Not Accepted</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between border-t border-gray-100 pt-2">
                                        <span class="text-xs text-gray-700 font-bold">2. Data Loss (Not responsible)</span>
                                        <div class="flex items-center gap-3">
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="radio" name="ui_data_loss" value="Accepted" onchange="document.getElementById('term_data_hidden').value = 'Data Loss Accepted';" class="text-emerald-500 focus:ring-emerald-500 w-3 h-3">
                                                <span class="text-[10px] font-bold text-emerald-700">Accepted</span>
                                            </label>
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="radio" name="ui_data_loss" value="Not Accepted" checked onchange="document.getElementById('term_data_hidden').value = 'Data Loss Not Accepted';" class="text-red-500 focus:ring-red-500 w-3 h-3">
                                                <span class="text-[10px] font-bold text-red-700">Not Accepted</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full">
                                <x-input-label for="estimated_delivery_date" class="font-bold text-indigo-900 mb-1" :value="__('Estimated Delivery Date *')" />
                                <x-text-input id="estimated_delivery_date" class="block w-full font-bold text-indigo-700 border-indigo-200 focus:border-indigo-500 shadow-sm transition-none" type="date" name="estimated_delivery_date" :value="old('estimated_delivery_date')" min="{{ date('Y-m-d') }}" required />
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end mt-6 mb-2 gap-4">
                            <a class="text-sm text-gray-600 hover:text-gray-900 font-bold px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition" href="{{ route('devices.index') }}">Cancel</a>
                            <button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700 shadow-md px-6 py-2.5 text-sm font-bold tracking-wider rounded-md transition">Save & Receive Device</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Validation Modal -->
    <div id="customValidationModal" class="fixed inset-0 z-[120] hidden overflow-y-auto bg-gray-900 bg-opacity-60 backdrop-blur-sm">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl w-full sm:my-8 transition-all" style="max-width: 28rem !important; margin: 0 auto;">
                <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 flex-shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-yellow-800" id="customModalTitle">সতর্কতা!</h3>
                    </div>
                    <button type="button" onclick="closeCustomModal()" class="text-yellow-500 hover:text-yellow-700 rounded-lg p-1.5 cursor-pointer transition">✕</button>
                </div>
                <div class="p-6 text-gray-700 font-semibold text-sm whitespace-pre-wrap leading-relaxed" id="customModalMessage"></div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-xl border-t border-gray-100">
                    <button type="button" onclick="closeCustomModal()" class="px-5 py-2.5 bg-yellow-500 text-white rounded-md font-bold hover:bg-yellow-600 shadow-sm text-sm cursor-pointer transition">OK, Got It</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        function enforceSingleWordBrand(el) {
            let words = el.value.trim().split(/\s+/);
            if(words.length > 1) {
                el.value = words[0];
            }
        }

        function cleanModelName() {
            let brandInput = document.getElementById('brand').value.trim();
            let modelInput = document.getElementById('model');

            if(brandInput !== '' && modelInput.value !== '') {
                let regex = new RegExp(brandInput, "ig");
                if(regex.test(modelInput.value)) {
                    modelInput.value = modelInput.value.replace(regex, '').trim();
                }
            }
        }

        let dbState = {
            initial: { hw: [], sw: [] },
            fault: { hw: [], sw: [] },
            additional: { hw: [], sw: [] }
        };

        const passInput = document.getElementById('device_password');
        const patternGrid = document.getElementById('pattern_grid_box');
        const passContainer = document.getElementById('password_container');

        passInput.addEventListener('focus', function() {
            if(document.getElementById('radio_pattern').checked) patternGrid.classList.remove('hidden');
        });

        document.addEventListener('click', function(event) {
            if (!passContainer.contains(event.target)) patternGrid.classList.add('hidden');
        });

        function togglePattern(show) {
            if(show && document.activeElement === passInput) patternGrid.classList.remove('hidden');
            else patternGrid.classList.add('hidden');
        }

        ['initial_hw_input', 'initial_sw_input', 'fault_hw_input', 'fault_sw_input', 'additional_hw_input', 'additional_sw_input'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const parts = id.split('_');
                        addBadge(parts[0], parts[1]);
                    }
                });
            }
        });

        document.getElementById('deviceCreateForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit') e.preventDefault();
        });

        // 100% Bulletproof Format on Add
        function smartFormatText(text) {
            if (!text) return text;

            const acronyms = ['frp', 'os', 'lcd', 'ic', 'cpu', 'emmc', 'ufs', 'usb', 'mic', 'hw', 'sw', 'pcb', 'fpc'];

            let words = text.split(/\s+/);
            for (let i = 0; i < words.length; i++) {
                let word = words[i];
                if (word.length === 0) continue;

                if (acronyms.includes(word.toLowerCase())) {
                    words[i] = word.toUpperCase();
                } else if (i === 0) {
                    words[i] = word.charAt(0).toUpperCase() + word.slice(1);
                }
            }
            return words.join(' ');
        }

        // Custom Modal Logic
        let focusTargetAfterModal = null;
        function showCustomModal(title, message, focusElId = null) {
            document.getElementById('customModalTitle').innerText = title;
            document.getElementById('customModalMessage').innerText = message;
            focusTargetAfterModal = focusElId ? document.getElementById(focusElId) : null;
            document.getElementById('customValidationModal').classList.remove('hidden');
        }
        function closeCustomModal() {
            document.getElementById('customValidationModal').classList.add('hidden');
            if (focusTargetAfterModal) {
                setTimeout(() => focusTargetAfterModal.focus(), 100);
                focusTargetAfterModal = null;
            }
        }

        function addBadge(category, type) {
            const inputEl = document.getElementById(`${category}_${type}_input`);
            const val = smartFormatText(inputEl.value.trim());

            if (!val) return;

            // Smart Duplicate Prevention Check (Using Custom Modal)
            const exists = dbState[category][type].some(item => item.toLowerCase() === val.toLowerCase());
            if (exists) {
                showCustomModal('Duplicate Entry Detected!', `The issue "${val}" is already added to this list. Please enter a different issue.`, `${category}_${type}_input`);
                inputEl.value = '';
                return;
            }

            dbState[category][type].push(val);
            renderBadges(category, type);
            inputEl.value = '';
            inputEl.focus();
        }

        function removeBadge(category, type, idx) {
            dbState[category][type].splice(idx, 1);
            renderBadges(category, type);
        }

        function renderBadges(category, type) {
            const list = document.getElementById(`list_${category}_${type}`);
            let html = '';
            let classes = type === 'hw' ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-green-100 text-green-800 border-green-200';

            if (dbState[category][type].length === 0) {
                list.innerHTML = ''; return;
            }

            dbState[category][type].forEach((val, idx) => {
                html += `
                    <div class="flex items-center justify-between px-2 py-1.5 rounded border text-[11px] font-bold shadow-sm ${classes} w-full transition-all">
                        <span class="truncate">${escapeHtml(val)}</span>
                        <button type="button" onclick="removeBadge('${category}', '${type}', ${idx})" class="ml-2 hover:bg-white rounded-full w-4 h-4 flex items-center justify-center cursor-pointer text-red-500 transition">✕</button>
                    </div>
                `;
            });
            list.innerHTML = html;
        }

        function escapeHtml(str) { return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

        function prepareSubmit() {
            if (dbState.initial.hw.length === 0 && dbState.initial.sw.length === 0) {
                showCustomModal('Action Required!', "দয়া করে কমপক্ষে একটি Initial Complaint (HW or SW) যুক্ত করুন। কাস্টমার অন্তত কি সমস্যা বলেছে তা লিখে 'Add' বাটনে ক্লিক করুন।", 'initial_hw_input');
                return false;
            }

            document.getElementById('hidden_problem_description').value = JSON.stringify(dbState.initial);
            document.getElementById('hidden_actual_fault').value = JSON.stringify(dbState.fault);
            document.getElementById('hidden_additional_complaint').value = JSON.stringify(dbState.additional);

            const passInput = document.getElementById('device_password');
            const passType = document.querySelector('input[name="pass_type"]:checked').value;
            if (passInput.value.trim() !== '') passInput.value = passType + ': ' + passInput.value.trim();

            return true;
        }
    </script>
</x-app-layout>
