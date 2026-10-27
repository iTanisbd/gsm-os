<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Device (ডিভাইস এডিট করুন)') }}
        </h2>
    </x-slot>

    @php
        $termsArray = is_array($device->terms) ? $device->terms : (json_decode($device->terms, true) ?? []);
        $deadRiskAccepted = in_array('Dead Risk Accepted', $termsArray);
        $dataLossAccepted = in_array('Data Loss Accepted', $termsArray);
        $cleanModel = trim(preg_replace('/^' . preg_quote($device->brand, '/') . '\s+/i', '', $device->model));

        $savedPass = $device->device_password ?? '';
        $passType = 'PIN';
        $passValue = $savedPass;
        if(str_starts_with($savedPass, 'Pattern: ')) {
            $passType = 'Pattern';
            $passValue = trim(str_replace('Pattern:', '', $savedPass));
        } elseif(str_starts_with($savedPass, 'PIN: ')) {
            $passType = 'PIN';
            $passValue = trim(str_replace('PIN:', '', $savedPass));
        }
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">

                    @if($hasBill)
                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-md flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <h3 class="text-blue-800 font-bold text-sm">এই ডিভাইসের বিল ইতিমধ্যে তৈরি হয়ে গেছে!</h3>
                                <p class="text-blue-600 text-xs mt-1">ডেটার সামঞ্জস্য ঠিক রাখতে 'Additional Info' সেকশন লক করে দেওয়া হয়েছে। নতুন কোনো কাজ বা চার্জ যুক্ত করতে <span class="font-bold">ডেলিভারি পেজের "Edit Bill" মডালটি</span> ব্যবহার করুন। তবে টেকনিশিয়ান চাইলে 'Actual Fault' সেকশনে নতুন ফল্ট যুক্ত করতে পারবেন।</p>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('devices.update', $device->tracking_code) }}" id="deviceEditForm" onsubmit="return prepareSubmit()">
                        @csrf
                        @method('PUT')

                        <textarea id="hidden_actual_fault" name="actual_fault" class="hidden"></textarea>
                        <textarea id="hidden_additional_complaint" name="additional_complaint" class="hidden"></textarea>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Customer -->
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg shadow-sm relative flex flex-col justify-center">
                                <div class="absolute top-0 right-0 bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-bl-lg rounded-tr-lg uppercase tracking-widest border-b border-l border-red-200">🔒 Locked Profile</div>
                                <x-input-label class="text-gray-700 font-bold mb-1.5" :value="__('Customer Profile')" />
                                <div class="block w-full border border-gray-200 rounded-md shadow-sm font-bold bg-gray-200 px-3 py-2 text-gray-600 cursor-not-allowed text-sm">
                                    {{ $device->customer->name }} <span class="font-normal">({{ $device->customer->phone }})</span>
                                </div>
                            </div>
                            <!-- Status (CLEANED UP - No WhatsApp Checkbox) -->
                            <div class="p-4 bg-indigo-50/50 border border-indigo-100 rounded-lg shadow-sm flex flex-col justify-center">
                                <x-input-label class="text-indigo-900 font-bold mb-2" :value="__('Current Status *')" />
                                <div class="flex flex-wrap gap-2 items-center">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status" value="pending" class="peer hidden" {{ $device->status == 'pending' ? 'checked' : '' }}>
                                        <div class="px-5 py-2.5 rounded-md border border-gray-300 peer-checked:bg-yellow-100 peer-checked:border-yellow-400 peer-checked:text-yellow-800 text-sm font-bold text-gray-600 shadow-sm transition">Pending</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status" value="processing" class="peer hidden" {{ $device->status == 'processing' ? 'checked' : '' }}>
                                        <div class="px-5 py-2.5 rounded-md border border-gray-300 peer-checked:bg-blue-100 peer-checked:border-blue-400 peer-checked:text-blue-800 text-sm font-bold text-gray-600 shadow-sm transition">Processing</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status" value="completed" class="peer hidden" {{ $device->status == 'completed' ? 'checked' : '' }}>
                                        <div class="px-5 py-2.5 rounded-md border border-gray-300 peer-checked:bg-green-100 peer-checked:border-green-400 peer-checked:text-green-800 text-sm font-bold text-gray-600 shadow-sm transition">Completed</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Device Info -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="relative"><x-input-label class="mb-1 text-gray-600" :value="__('Brand 🔒')" /><x-text-input class="block w-full font-bold bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed shadow-none" type="text" value="{{ $device->brand }}" readonly /></div>
                            <div class="relative"><x-input-label class="mb-1 text-gray-600" :value="__('Model 🔒')" /><x-text-input class="block w-full font-bold bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed shadow-none" type="text" value="{{ $cleanModel }}" readonly /></div>
                            <div class="relative"><x-input-label class="mb-1 text-gray-600" :value="__('IMEI / Serial 🔒')" /><x-text-input class="block w-full font-bold bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed shadow-none" type="text" value="{{ $device->imei }}" readonly /></div>
                            <div class="relative flex flex-col justify-end">
                                <div class="flex items-center justify-between mb-1">
                                    <x-input-label class="mb-0 text-gray-600" :value="__('Lock Type 🔒')" />
                                    <div class="flex items-center gap-2 opacity-70">
                                        <label class="flex items-center gap-1 text-[10px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200"><input type="radio" disabled {{ $passType == 'PIN' ? 'checked' : '' }} class="w-3 h-3 text-gray-400"> PIN</label>
                                        <label class="flex items-center gap-1 text-[10px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200"><input type="radio" disabled {{ $passType == 'Pattern' ? 'checked' : '' }} class="w-3 h-3 text-gray-400"> Pattern</label>
                                    </div>
                                </div>
                                <x-text-input class="block w-full font-mono font-bold text-gray-500 bg-gray-100 border-gray-200 shadow-none cursor-not-allowed" type="text" value="{{ $passValue }}" readonly />
                            </div>
                        </div>

                        <!-- Initial & Actual Fault (Actual Fault Unlocked for Technicians) -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-stretch">
                            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm flex flex-col h-full relative">
                                <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-3">
                                    <x-input-label class="font-bold text-gray-600 mb-0" :value="__('1. Initial Complaint (লকড)')" />
                                    <span class="text-[9px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded font-bold uppercase tracking-widest border border-gray-300">Read Only</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 flex-1">
                                    <div class="flex flex-col bg-white p-3 rounded border border-gray-200 h-full"><p class="text-[10px] font-bold text-gray-500 uppercase mb-2 border-b border-gray-100 pb-1">Hardware Issues</p><div id="list_initial_hw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[60px]"></div></div>
                                    <div class="flex flex-col bg-white p-3 rounded border border-gray-200 h-full"><p class="text-[10px] font-bold text-gray-500 uppercase mb-2 border-b border-gray-100 pb-1">Software Issues</p><div id="list_initial_sw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[60px]"></div></div>
                                </div>
                            </div>

                            <div class="bg-white p-5 rounded-lg border border-emerald-200 shadow-sm flex flex-col h-full">
                                <div class="flex justify-between items-center border-b border-emerald-100 pb-2 mb-3">
                                    <x-input-label class="font-bold text-emerald-600 mb-0" :value="__('2. Actual Fault (টেকনিশিয়ান যা পেলেন)')" />
                                    <span class="text-[9px] bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded font-bold uppercase tracking-widest border border-emerald-100">Editable</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 flex-1">
                                    <div class="flex flex-col bg-emerald-50/30 p-3 rounded border border-emerald-100 h-full">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input type="text" id="fault_hw_input" class="block w-full border-gray-300 rounded text-xs shadow-sm font-semibold py-1.5 focus:border-emerald-500" placeholder="+ HW Fault...">
                                            <button type="button" onclick="addBadge('fault', 'hw')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                        </div>
                                        <p class="text-[10px] font-bold text-emerald-800 uppercase mb-2 border-b border-emerald-200 pb-1">Hardware Faults</p>
                                        <div id="list_fault_hw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[60px]"></div>
                                    </div>
                                    <div class="flex flex-col bg-emerald-50/30 p-3 rounded border border-emerald-100 h-full">
                                        <div class="flex items-center gap-2 mb-3">
                                            <input type="text" id="fault_sw_input" class="block w-full border-gray-300 rounded text-xs shadow-sm font-semibold py-1.5 focus:border-emerald-500" placeholder="+ SW Fault...">
                                            <button type="button" onclick="addBadge('fault', 'sw')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                        </div>
                                        <p class="text-[10px] font-bold text-emerald-800 uppercase mb-2 border-b border-emerald-200 pb-1">Software Faults</p>
                                        <div id="list_fault_sw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[60px]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info & Tech Notes -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-stretch">
                            <div class="{{ $hasBill ? 'bg-gray-50 border-gray-200' : 'bg-white border-indigo-200' }} p-5 rounded-lg border shadow-sm flex flex-col h-full">
                                <div class="flex justify-between items-center border-b {{ $hasBill ? 'border-gray-200' : 'border-indigo-100' }} pb-2 mb-3">
                                    <x-input-label class="font-bold {{ $hasBill ? 'text-gray-600' : 'text-indigo-600' }} mb-0" :value="__('3. Additional Info (পরে জানালে)')" />
                                    @if($hasBill)
                                        <span class="text-[9px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded font-bold uppercase tracking-widest border border-gray-300">🔒 Security Locked</span>
                                    @else
                                        <span class="text-[9px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded font-bold uppercase tracking-widest border border-indigo-100">Editable</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-4 flex-1">
                                    <div class="flex flex-col {{ $hasBill ? 'bg-white border-gray-200' : 'bg-indigo-50/30 border-indigo-100' }} p-3 rounded border h-full">
                                        @if(!$hasBill)
                                            <div class="flex items-center gap-2 mb-3">
                                                <input type="text" id="additional_hw_input" class="block w-full border-gray-300 rounded text-xs shadow-sm font-semibold py-1.5 focus:border-indigo-500" placeholder="Add HW Info...">
                                                <button type="button" onclick="addBadge('additional', 'hw')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                            </div>
                                        @endif
                                        <p class="text-[10px] font-bold {{ $hasBill ? 'text-gray-500 border-gray-100' : 'text-indigo-800 border-indigo-200' }} uppercase mb-2 border-b pb-1">Hardware Additions</p>
                                        <div id="list_additional_hw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[60px]"></div>
                                    </div>
                                    <div class="flex flex-col {{ $hasBill ? 'bg-white border-gray-200' : 'bg-indigo-50/30 border-indigo-100' }} p-3 rounded border h-full">
                                        @if(!$hasBill)
                                            <div class="flex items-center gap-2 mb-3">
                                                <input type="text" id="additional_sw_input" class="block w-full border-gray-300 rounded text-xs shadow-sm font-semibold py-1.5 focus:border-indigo-500" placeholder="Add SW Info...">
                                                <button type="button" onclick="addBadge('additional', 'sw')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded shadow-sm text-xs transition">Add</button>
                                            </div>
                                        @endif
                                        <p class="text-[10px] font-bold {{ $hasBill ? 'text-gray-500 border-gray-100' : 'text-indigo-800 border-indigo-200' }} uppercase mb-2 border-b pb-1">Software Additions</p>
                                        <div id="list_additional_sw" class="flex-1 overflow-y-auto space-y-1 content-start min-h-[60px]"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-purple-50/50 p-5 rounded-lg border border-purple-200 shadow-sm flex flex-col h-full">
                                <x-input-label for="internal_tech_notes" class="font-bold text-purple-700 mb-2 border-b border-purple-200 pb-2" :value="__('4. Internal Tech Notes (গোপন নোট)')" />
                                <textarea id="internal_tech_notes" name="internal_tech_notes" class="block w-full flex-1 min-h-[120px] border-gray-300 focus:border-purple-500 rounded-md shadow-sm resize-none text-sm font-semibold bg-white" placeholder="Secret notes for internal staff...">{{ old('internal_tech_notes', $device->internal_tech_notes) }}</textarea>
                            </div>
                        </div>

                        <!-- Storage & Rack Info -->
                        <div class="mb-6 p-5 bg-gray-50 border border-gray-200 rounded-lg shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><x-input-label for="rack_number" class="font-bold text-gray-700 text-xs uppercase mb-1" :value="__('Rack Number')" /><x-text-input id="rack_number" class="block w-full border-gray-300" type="text" name="rack_number" :value="old('rack_number', $device->rack_number)" required /></div>
                            <div><x-input-label for="drawer_number" class="font-bold text-gray-700 text-xs uppercase mb-1" :value="__('Drawer Number')" /><x-text-input id="drawer_number" class="block w-full border-gray-300" type="text" name="drawer_number" :value="old('drawer_number', $device->drawer_number)" required /></div>
                        </div>

                        <!-- LOCKED Risk Agreements & Delivery Date -->
                        <div class="mb-6 bg-indigo-50 p-5 rounded-md border border-indigo-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-8 items-start relative">
                            <!-- Locked Badge for Agreements -->
                            <div class="absolute top-0 right-0 bg-red-100 text-red-600 text-[10px] font-bold px-3 py-1 rounded-bl-lg rounded-tr-md uppercase tracking-widest border-b border-l border-red-200">
                                🔒 Security Locked
                            </div>

                            <!-- Left: Agreements (Locked View) -->
                            <div class="w-full mt-2">
                                <p class="font-bold text-red-800 mb-2 text-sm uppercase tracking-wider">Pre-Repair Agreements</p>
                                <div class="space-y-3 bg-white p-3 rounded border border-indigo-100 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-700 font-bold">1. Dead Risk (No guarantee)</span>
                                        @if($deadRiskAccepted)
                                            <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-3 py-1.5 rounded flex items-center gap-1 border border-emerald-200">✓ ACCEPTED</span>
                                        @else
                                            <span class="text-[10px] font-bold bg-red-100 text-red-800 px-3 py-1.5 rounded flex items-center gap-1 border border-red-200">✕ NOT ACCEPTED</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between border-t border-gray-100 pt-2">
                                        <span class="text-xs text-gray-700 font-bold">2. Data Loss</span>
                                        @if($dataLossAccepted)
                                            <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-3 py-1.5 rounded flex items-center gap-1 border border-emerald-200">✓ ACCEPTED</span>
                                        @else
                                            <span class="text-[10px] font-bold bg-red-100 text-red-800 px-3 py-1.5 rounded flex items-center gap-1 border border-red-200">✕ NOT ACCEPTED</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Delivery Date (Editable) -->
                            <div class="w-full mt-2">
                                <x-input-label for="estimated_delivery_date" class="font-bold text-indigo-900 mb-1" :value="__('Estimated Delivery Date *')" />
                                <x-text-input id="estimated_delivery_date" class="block w-full font-bold text-indigo-700 border-indigo-200 rounded-md shadow-sm transition-none" type="date" name="estimated_delivery_date" :value="old('estimated_delivery_date', $device->estimated_delivery_date)" min="{{ date('Y-m-d') }}" required />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 mb-2 gap-4">
                            <a class="text-sm text-gray-600 hover:text-gray-900 font-bold px-5 py-2.5 border border-gray-300 rounded-md hover:bg-gray-50 transition" href="{{ route('devices.index') }}">Cancel</a>
                            <button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700 shadow-md px-8 py-2.5 text-sm font-bold tracking-wider rounded-md transition">Update Device Info</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- DB Parsing to Badges Script -->
    <script>
        const isBillGenerated = {{ $hasBill ? 'true' : 'false' }};

        let dbState = {
            initial: { hw: [], sw: [] },
            fault: { hw: [], sw: [] },
            additional: { hw: [], sw: [] }
        };

        const initialStr = `{!! addslashes($device->problem_description) !!}`;
        const faultStr = `{!! addslashes($device->actual_fault) !!}`;
        const addStr = `{!! addslashes($device->additional_complaint) !!}`;

        function parseToState(str, category) {
            if(!str) return;
            try {
                const data = JSON.parse(str);
                if(data.hw) data.hw.forEach(v => dbState[category].hw.push({ text: v, isOld: true }));
                if(data.sw) data.sw.forEach(v => dbState[category].sw.push({ text: v, isOld: true }));
            } catch(e) {
                let currentMode = 'hw';
                str.split(/\r?\n/).forEach(line => {
                    let lower = line.toLowerCase();
                    if(lower.includes('[hardware]')) { currentMode = 'hw'; return; }
                    if(lower.includes('[software]')) { currentMode = 'sw'; return; }

                    let text = line.replace(/^\d+[\.\)\-]\s*/, '').replace(/\[HW\] |\[SW\] /ig, '').trim();
                    if(text) dbState[category][currentMode].push({ text: text, isOld: true });
                });
            }
        }

        parseToState(initialStr, 'initial');
        parseToState(faultStr, 'fault');
        parseToState(addStr, 'additional');

        document.addEventListener('DOMContentLoaded', function() {
            renderBadges('initial', 'hw'); renderBadges('initial', 'sw');
            renderBadges('fault', 'hw'); renderBadges('fault', 'sw');
            renderBadges('additional', 'hw'); renderBadges('additional', 'sw');
        });

        ['fault_hw_input', 'fault_sw_input', 'additional_hw_input', 'additional_sw_input'].forEach(id => {
            let el = document.getElementById(id);
            if(el) {
                el.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { e.preventDefault(); const parts = id.split('_'); addBadge(parts[0], parts[1]); }
                });
            }
        });

        document.getElementById('deviceEditForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit') e.preventDefault();
        });

        function addBadge(category, type) {
            if (isBillGenerated && category === 'additional') return;
            const inputEl = document.getElementById(`${category}_${type}_input`);
            if(!inputEl) return;
            const val = inputEl.value.trim();
            if (!val) return;
            dbState[category][type].push({ text: val, isOld: false });
            renderBadges(category, type);
            inputEl.value = ''; inputEl.focus();
        }

        function removeBadge(category, type, idx) {
            if (isBillGenerated && category === 'additional') return;
            dbState[category][type].splice(idx, 1);
            renderBadges(category, type);
        }

        function renderBadges(category, type) {
            const list = document.getElementById(`list_${category}_${type}`);
            if(!list) return;
            let html = '';

            let classes = (category === 'initial' || (isBillGenerated && category === 'additional'))
                ? 'bg-gray-100 text-gray-600 border-gray-200'
                : (type === 'hw' ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-green-100 text-green-800 border-green-200');

            if (dbState[category][type].length === 0) { list.innerHTML = ''; return; }

            dbState[category][type].forEach((obj, idx) => {
                let canRemove = (!obj.isOld && category !== 'initial' && !(isBillGenerated && category === 'additional'));
                html += `
                    <div class="flex items-center justify-between px-2 py-1.5 rounded border text-[11px] font-bold shadow-sm ${classes} w-full transition-all">
                        <span class="truncate">${escapeHtml(obj.text)}</span>
                        ${canRemove ? `<button type="button" onclick="removeBadge('${category}', '${type}', ${idx})" class="ml-2 hover:bg-white rounded-full w-4 h-4 flex items-center justify-center cursor-pointer text-red-500 transition">✕</button>` : `<span class="ml-2 w-4 h-4 flex items-center justify-center text-gray-400" title="Locked Item">🔒</span>`}
                    </div>
                `;
            });
            list.innerHTML = html;
        }

        function escapeHtml(str) { return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

        function prepareSubmit() {
            let compileToJSON = (cat) => {
                let obj = { hw: [], sw: [] };
                dbState[cat].hw.forEach(v => obj.hw.push(v.text));
                dbState[cat].sw.forEach(v => obj.sw.push(v.text));
                return JSON.stringify(obj);
            };

            document.getElementById('hidden_actual_fault').value = compileToJSON('fault');
            document.getElementById('hidden_additional_complaint').value = compileToJSON('additional');
            return true;
        }
    </script>
</x-app-layout>
