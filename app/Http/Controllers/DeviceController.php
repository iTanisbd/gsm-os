<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Customer;
use App\Models\Bill;
use App\Models\Transaction;
use App\Models\RepairLog;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        // Added 'bills' relationship to eager load due amounts for WhatsApp logic
        $devices = Device::with(['customer', 'bills'])
            ->where('shop_id', auth()->user()->shop_id)
            ->latest()
            ->get();

        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        $customers = Customer::where('shop_id', auth()->user()->shop_id)->latest()->get();
        return view('devices.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei' => 'nullable|string|max:100',
            'device_password' => 'nullable|string|max:50',
            'problem_description' => 'required|string',
            'actual_fault' => 'nullable|string',
            'internal_tech_notes' => 'nullable|string',
            'rack_number' => 'required|string|max:50',
            'drawer_number' => 'required|string|max:50',
            'pre_repair_checklist' => 'nullable|array',
            'terms' => 'nullable|array',
            'estimated_delivery_date' => 'required|date|after_or_equal:today',
            'pass_type' => 'nullable|string',
            'reported_issues' => 'nullable|array',
            'reported_issues.*.type' => 'required|in:hardware,software',
            'reported_issues.*.name' => 'required|string',
        ]);

        $brand = trim($request->brand);
        $model = trim($request->model);
        $cleanModel = trim(preg_replace('/^' . preg_quote($brand, '/') . '\s+/i', '', $model));
        if ($cleanModel === '') {
            $cleanModel = $model;
        }

        $finalPassword = $request->device_password;
        if ($request->filled('device_password') && $request->filled('pass_type')) {
            $finalPassword = $request->pass_type . ': ' . $request->device_password;
        }

        $fallbackDesc = '';
        if($request->has('reported_issues') && is_array($request->reported_issues)) {
            foreach($request->reported_issues as $idx => $issue) {
                $typeTag = $issue['type'] == 'hardware' ? '[HW]' : '[SW]';
                $fallbackDesc .= ($idx + 1) . '. ' . $typeTag . ' ' . trim($issue['name']) . "\n";
            }
        } else {
            $fallbackDesc = $request->problem_description;
        }

        Device::create([
            'shop_id' => auth()->user()->shop_id,
            'customer_id' => $request->customer_id,
            'brand' => $brand,
            'model' => $cleanModel,
            'imei' => $request->imei,
            'device_password' => $finalPassword,
            'problem_description' => $fallbackDesc,
            'reported_issues' => $request->reported_issues,
            'status' => 'pending',
            'actual_fault' => $request->actual_fault,
            'internal_tech_notes' => $request->internal_tech_notes,
            'rack_number' => $request->rack_number,
            'drawer_number' => $request->drawer_number,
            'pre_repair_checklist' => $request->pre_repair_checklist,
            'terms' => $request->terms,
            'estimated_delivery_date' => $request->estimated_delivery_date,
            'risk_agreement' => $request->has('risk_agreement'),
        ]);

        return redirect()->route('devices.index')->with('success', 'Device received successfully!');
    }

    public function show(string $id)
    {
        $device = Device::with(['customer', 'repairLogs.user', 'bills.items'])
            ->where(function($query) use ($id) {
                $query->where('tracking_code', $id)->orWhere('id', $id);
            })
            ->firstOrFail();

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $bill = $device->bills->first();

        return view('devices.show', compact('device', 'bill'));
    }

    public function edit(string $id)
    {
        $device = Device::where('tracking_code', $id)->firstOrFail();

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $customers = Customer::where('shop_id', auth()->user()->shop_id)->latest()->get();
        $hasBill = $device->bills()->exists();

        return view('devices.edit', compact('device', 'customers', 'hasBill'));
    }

    public function update(Request $request, string $id)
    {
        $device = Device::where('tracking_code', $id)->firstOrFail();

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        if (in_array($device->getOriginal('status'), ['delivered', 'returned'])) {
            return redirect()->route('devices.index')->with('error', 'Security Alert: Editing information for a delivered device is strictly prohibited!');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,completed,delivered',
            'additional_complaint' => 'nullable|string',
            'actual_fault' => 'nullable|string',
            'internal_tech_notes' => 'nullable|string',
            'rack_number' => 'required|string|max:50',
            'drawer_number' => 'required|string|max:50',
            'estimated_delivery_date' => 'required|date',
        ]);

        $device->update([
            'status' => $request->status,
            'additional_complaint' => $request->additional_complaint,
            'actual_fault' => $request->actual_fault,
            'internal_tech_notes' => $request->internal_tech_notes,
            'rack_number' => $request->rack_number,
            'drawer_number' => $request->drawer_number,
            'estimated_delivery_date' => $request->estimated_delivery_date,
        ]);

        return redirect()->route('devices.index')->with('success', 'Device updated successfully!');
    }

    // Quick Status Update from Index Page
    public function updateStatus(Request $request, string $id)
    {
        $device = Device::where('tracking_code', $id)->firstOrFail();

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        if (in_array($device->getOriginal('status'), ['delivered', 'returned'])) {
            return back()->with('error', 'Cannot change status of a delivered or returned device.');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,completed',
        ]);

        $device->update([
            'status' => $request->status,
        ]);

        RepairLog::create([
            'device_id' => $device->id,
            'user_id' => auth()->id(),
            'note' => 'Quick Status Update: Changed to ' . ucfirst($request->status),
        ]);

        return back()->with('success', 'Status updated to ' . ucfirst($request->status) . '!');
    }

    public function destroy(string $id)
    {
        $device = Device::where('tracking_code', $id)->firstOrFail();

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device deleted successfully!');
    }

    public function addRepairLog(Request $request, string $id)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $device = Device::where('tracking_code', $id)->firstOrFail();

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        RepairLog::create([
            'device_id' => $device->id,
            'user_id' => auth()->id(),
            'note' => $request->note,
        ]);

        return back()->with('success', 'Timeline updated successfully!');
    }

    public function markAsDelivered(Request $request, string $id)
    {
        $request->validate([
            'delivered_by_name' => 'required|string|max:100',
            'received_by_name' => 'required|string|max:100',
            'promise_date' => 'nullable|date',
            'repair_outcome' => 'required|in:success,partially_repaired,returned_unrepaired',
            'repair_note' => 'required_if:repair_outcome,partially_repaired|nullable|string',
        ]);

        $device = Device::with('bills.items')->where('tracking_code', $id)->firstOrFail();

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $bill = $device->bills->first();

        if ($request->repair_outcome === 'returned_unrepaired' && $request->has('auto_refund_and_cancel')) {
            if ($bill) {
                foreach ($bill->items as $item) {
                    if (!str_starts_with($item->description, '[Cancelled] ')) {
                        $item->description = '[Cancelled] ' . $item->description;
                        $item->save();
                    }
                }

                $oldTotal = $bill->total_amount;
                $bill->total_amount = 0;
                $bill->due_amount = 0;
                $bill->save();

                $refundAmount = $bill->paid_amount;

                if ($refundAmount > 0) {
                    Transaction::create([
                        'bill_id' => $bill->id,
                        'shop_id' => $device->shop_id,
                        'type' => 'refund',
                        'amount' => $refundAmount,
                        'method' => 'cash',
                        'note' => 'Auto-refund: Device returned unrepaired.',
                        'created_by' => auth()->id(),
                    ]);

                    $bill->paid_amount = 0;
                    $bill->save();
                }

                RepairLog::create([
                    'device_id' => $device->id,
                    'user_id' => auth()->id(),
                    'note' => "System Action: All bill items cancelled. ৳{$oldTotal} adjusted to ৳0. Refunded ৳{$refundAmount} to customer due to 'Returned Unrepaired'."
                ]);
            }
        }

        if ($request->filled('repair_note')) {
            RepairLog::create([
                'device_id' => $device->id,
                'user_id' => auth()->id(),
                'note' => "Repair Outcome: " . ucwords(str_replace('_', ' ', $request->repair_outcome)) . "\nNote: " . $request->repair_note,
            ]);
        } else {
             RepairLog::create([
                'device_id' => $device->id,
                'user_id' => auth()->id(),
                'note' => "Device marked as " . ucwords(str_replace('_', ' ', $request->repair_outcome)) . " and prepared for delivery.",
            ]);
        }

        $newStatus = in_array($request->repair_outcome, ['success', 'partially_repaired']) ? 'delivered' : 'returned';

        $device->update([
            'status' => $newStatus,
            'repair_outcome' => $request->repair_outcome,
            'completed_at' => (in_array($request->repair_outcome, ['success', 'partially_repaired']) && is_null($device->completed_at)) ? now() : $device->completed_at,
            'delivered_by_name' => $request->delivered_by_name,
            'received_by_name' => $request->received_by_name,
        ]);

        if ($request->has('has_due_commitment') && $bill) {
            $bill->promise_date = $request->promise_date;
            $bill->save();
        }

        return back()->with('success', 'Device marked as ' . ucfirst($newStatus) . ' successfully!');
    }

    public function track($code)
    {
        $device = \App\Models\Device::with(['customer', 'bills'])
                    ->where('tracking_code', $code)
                    ->firstOrFail();

        $bill = $device->bills->first();

        return view('track', compact('device', 'bill'));
    }
}
