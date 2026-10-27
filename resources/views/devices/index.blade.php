<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Device List (ডিভাইস তালিকা)') }}
            </h2>
            <a href="{{ route('devices.create') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out shadow-sm">
                + Receive New Device
            </a>
        </div>
    </x-slot>

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

        if (!function_exists('formatDeviceIssuesForIndex')) {
            function formatDeviceIssuesForIndex($rawText) {
                if (!$rawText || trim($rawText) === '') return '<span class="text-gray-400 italic">N/A</span>';
                $hw = []; $sw = [];
                $data = json_decode($rawText, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $hw = $data['hw'] ?? [];
                    $sw = $data['sw'] ?? [];
                } else {
                    $lines = explode("\n", $rawText);
                    foreach($lines as $line) {
                        $text = trim(preg_replace('/^\d+[\.\)\-]\s*/', '', $line));
                        if(!$text) continue;
                        if (stripos($text, '[SW]') !== false || stripos($text, '[Software]') !== false) {
                            $sw[] = trim(str_ireplace(['[SW]', '[Software]'], '', $text));
                        } else {
                            $hw[] = trim(str_ireplace(['[HW]', '[Hardware]'], '', $text));
                        }
                    }
                }
                $output = '';
                if (count($hw) > 0) {
                    $output .= '<div class="mb-1"><span class="font-bold text-blue-700 text-[11px] block mb-0.5">A. Hardware :-</span>';
                    foreach($hw as $idx => $item) {
                        $output .= '<div class="ml-2 flex items-start gap-1 text-[11px] text-gray-700 leading-tight"><span class="font-bold">'.($idx+1).'.</span><span>'.htmlspecialchars($item).'</span></div>';
                    }
                    $output .= '</div>';
                }
                if (count($sw) > 0) {
                    $output .= '<div><span class="font-bold text-green-700 text-[11px] block mb-0.5">B. Software :-</span>';
                    foreach($sw as $idx => $item) {
                        $output .= '<div class="ml-2 flex items-start gap-1 text-[11px] text-gray-700 leading-tight"><span class="font-bold">'.($idx+1).'.</span><span>'.htmlspecialchars($item).'</span></div>';
                    }
                    $output .= '</div>';
                }
                return $output === '' ? '<span class="text-gray-400 italic">N/A</span>' : $output;
            }
        }
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-0 text-gray-900 overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">SL</th>
                                <th scope="col" class="px-6 py-4 font-bold">Customer</th>
                                <th scope="col" class="px-6 py-4 font-bold">Device (Model)</th>
                                <th scope="col" class="px-6 py-4 font-bold">Problem</th>
                                <th scope="col" class="px-6 py-4 font-bold text-center">Status</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($devices as $key => $device)
                                <tr class="bg-white border-b hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4">{{ $key + 1 }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $device->customer->name }} <br>
                                        <span class="text-xs text-gray-500 font-normal">{{ $device->customer->phone }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $cleanModelName = trim(preg_replace('/^' . preg_quote($device->brand, '/') . '\s+/i', '', $device->model));
                                        @endphp
                                        <span class="font-bold text-gray-800">{{ $device->brand }}</span>
                                        <span class="text-gray-600">{{ $cleanModelName }}</span>
                                        @if($device->imei)
                                            <br><span class="text-xs text-gray-500">IMEI: {{ $device->imei }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {!! formatDeviceIssuesForIndex($device->problem_description) !!}
                                    </td>

                                    <!-- Status Column (Alignment Fixed) -->
                                    <td class="px-6 py-4 text-center">
                                        @if(in_array($device->status, ['pending', 'processing', 'completed']))
                                            <form action="{{ route('devices.update-status', $device->tracking_code) }}" method="POST" class="m-0 p-0">
                                                @csrf
                                                @method('PATCH')
                                                <!-- pt-1.5 pb-0.5 leading-tight added for vertical alignment fix -->
                                                <select name="status" onchange="this.form.submit()" class="text-[11px] font-bold rounded-full pl-3 pr-8 pt-1.5 pb-0.5 leading-tight border-0 shadow-sm cursor-pointer outline-none focus:ring-2 focus:ring-indigo-500 uppercase tracking-wider appearance-none bg-no-repeat bg-[right_0.5rem_center] bg-[length:1em_1em]
                                                    {{ $device->status == 'pending' ? 'bg-yellow-100 text-yellow-800 bg-[url(\'data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23854d0e%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E\')]' : '' }}
                                                    {{ $device->status == 'processing' ? 'bg-blue-100 text-blue-800 bg-[url(\'data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231e40af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E\')]' : '' }}
                                                    {{ $device->status == 'completed' ? 'bg-green-100 text-green-800 bg-[url(\'data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23166534%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E\')]' : '' }}
                                                ">
                                                    <option value="pending" {{ $device->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="processing" {{ $device->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                                    <option value="completed" {{ $device->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                </select>
                                            </form>
                                        @else
                                            <span class="px-3 pt-1.5 pb-0.5 leading-tight text-[11px] font-bold rounded-full uppercase tracking-wider
                                                {{ $device->status == 'delivered' ? 'bg-gray-200 text-gray-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $device->status }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action Buttons (WhatsApp Disabled State Added) -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end items-center space-x-2">
                                            @if($device->status == 'completed')
                                                <a href="{{ getWhatsAppUrl($device) }}" target="_blank" title="Notify Customer via WhatsApp" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-2 py-1.5 rounded-md transition flex items-center justify-center shadow-sm border border-emerald-200">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                                </a>
                                            @else
                                                <span title="Enabled after 'Completed'" class="text-gray-400 bg-gray-50 px-2 py-1.5 rounded-md flex items-center justify-center border border-gray-200 cursor-not-allowed opacity-60">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                                </span>
                                            @endif

                                            <a href="{{ route('devices.show', $device->tracking_code) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md text-xs font-bold transition border border-transparent">View</a>
                                            <a href="{{ route('devices.edit', $device->tracking_code) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md text-xs font-bold transition border border-transparent">Edit</a>
                                            <form action="{{ route('devices.destroy', $device->tracking_code) }}" method="POST" class="inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ডিভাইসটি ডিলিট করতে চান?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md text-xs font-bold transition">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 bg-gray-50">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                            <p class="text-base font-medium">কোনো ডিভাইস পাওয়া যায়নি।</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
