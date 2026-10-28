<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Device;
use App\Models\Transaction;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index()
    {
        $bills = Bill::with(['customer', 'device'])
            ->where('shop_id', auth()->user()->shop_id)
            ->latest()
            ->get();

        return view('bills.index', compact('bills'));
    }

    public function create()
    {
        $billedDeviceIds = Bill::where('shop_id', auth()->user()->shop_id)->pluck('device_id');

        $devices = Device::with('customer')
            ->where('shop_id', auth()->user()->shop_id)
            ->whereNotIn('id', $billedDeviceIds)
            ->latest()
            ->get();

        // Fetch All Available Inventory Parts
        $inventories = Inventory::where('shop_id', auth()->user()->shop_id)
            ->where('stock', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        return view('bills.create', compact('devices', 'inventories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'hardware_items' => 'nullable|array',
            'software_items' => 'nullable|array',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:flat,percentage',
            'paid_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'terms' => 'nullable|array',
        ]);

        $hardwareItems = array_filter($request->hardware_items ?? [], function($item) {
            return !empty($item['description']) && $item['amount'] !== null;
        });

        $softwareItems = array_filter($request->software_items ?? [], function($item) {
            return !empty($item['description']) && $item['amount'] !== null;
        });

        if (empty($hardwareItems) && empty($softwareItems)) {
            return back()->withInput()->withErrors(['items' => 'You must add at least one hardware or software service!']);
        }

        $total = (float) $request->total_amount;
        $discountVal = (float) ($request->discount ?? 0);
        $discountType = $request->discount_type;
        $paid = (float) ($request->paid_amount ?? 0);

        $calculatedDiscount = ($discountType === 'percentage') ? (($total * $discountVal) / 100) : $discountVal;
        $netPayable = max(0, $total - $calculatedDiscount);

        if ($paid > $netPayable) {
            return back()->withInput()->withErrors(['paid_amount' => 'Paid amount cannot be greater than the total bill (৳'.$netPayable.')!']);
        }

        $device = Device::with('customer')->findOrFail($request->device_id);
        $due = max(0, $netPayable - $paid);
        $paymentStatus = ($paid <= 0) ? 'due' : (($paid < $netPayable) ? 'partial' : 'paid');

        $lastBill = Bill::latest()->first();
        $nextNumber = $lastBill ? ((int) str_replace('INV-', '', $lastBill->invoice_number) + 1) : 1;
        $invoiceNumber = 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $bill = Bill::create([
                'shop_id' => auth()->user()->shop_id,
                'customer_id' => $device->customer_id,
                'device_id' => $device->id,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $total,
                'discount' => $discountVal,
                'discount_type' => $discountType,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $paymentStatus,
                'note' => $request->note,
                'terms' => $request->filled('terms') ? json_encode($request->terms) : null,
                'created_by' => auth()->id(),
            ]);

            // Save Hardware Items & Deduct Auto-Stock
            foreach ($hardwareItems as $item) {
                $partLabel = '';

                // Inventory Auto-Deduction Logic
                if (!empty($item['inventory_id'])) {
                    $inventoryPart = Inventory::find($item['inventory_id']);
                    if ($inventoryPart && $inventoryPart->stock > 0) {
                        $inventoryPart->decrement('stock', 1); // Deduct 1 item from stock
                    }
                }

                $bill->items()->create([
                    'category' => 'hardware',
                    'description' => $item['description'], // Will save as you typed or selected
                    'amount' => $item['amount'],
                    'warranty' => $item['warranty'] ?? null,
                ]);
            }

            // Save Software Items
            foreach ($softwareItems as $item) {
                $bill->items()->create([
                    'category' => 'software',
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'warranty' => null,
                ]);
            }

            if ($paid > 0) {
                Transaction::create([
                    'shop_id' => auth()->user()->shop_id,
                    'bill_id' => $bill->id,
                    'user_id' => auth()->id(),
                    'type' => 'income',
                    'amount' => $paid,
                    'method' => 'cash',
                    'description' => 'Initial payment at billing (' . $invoiceNumber . ')',
                ]);
            }

            DB::commit();
            return redirect()->route('bills.index')->with('success', 'Bill generated & Stock adjusted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $bill = Bill::with(['customer', 'device', 'items'])->findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }
        return view('bills.show', compact('bill'));
    }

    public function print(string $id)
    {
        $bill = Bill::with(['customer', 'device', 'items'])->findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }
        return view('bills.print', compact('bill'));
    }

    public function edit(string $id)
    {
        $bill = Bill::with(['customer', 'device'])->findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }
        return view('bills.edit', compact('bill'));
    }

    public function update(Request $request, string $id)
    {
        abort(403, 'Editing bills is blocked. Use Refunds/Adjustments instead.');
    }

    public function destroy(string $id)
    {
        $bill = Bill::findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }
        $bill->delete();
        return redirect()->route('bills.index')->with('success', 'Bill deleted successfully!');
    }

    public function addTransaction(Request $request, string $id)
    {
        $request->validate([
            'type' => 'required|in:due_payment,refund',
            'amount' => 'required|numeric|min:1',
            'method' => 'nullable|string',
            'note' => 'nullable|string',
            'transaction_id' => 'nullable|string',
        ]);

        $bill = Bill::findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }

        $amount = (float) $request->amount;
        $calculatedDiscount = ($bill->discount_type === 'percentage') ? (($bill->total_amount * $bill->discount) / 100) : $bill->discount;
        $netPayable = max(0, (float)$bill->total_amount - $calculatedDiscount);

        if ($request->type === 'due_payment') {
            if ($amount > (float)$bill->due_amount) {
                return back()->with('error', 'Payment amount cannot exceed the due amount!');
            }
            $newPaid = (float)$bill->paid_amount + $amount;
            $newDue = max(0, $netPayable - $newPaid);
            $newStatus = ($newDue <= 0) ? 'paid' : 'partial';

            $bill->update(['paid_amount' => $newPaid, 'due_amount' => $newDue, 'payment_status' => $newStatus]);
            Transaction::create([
                'shop_id' => auth()->user()->shop_id, 'bill_id' => $bill->id, 'user_id' => auth()->id(),
                'type' => 'income', 'amount' => $amount, 'method' => $request->method ?? 'cash',
                'description' => 'Due payment: ' . ($request->note ?? 'Customer paid due amount'),
            ]);
            return back()->with('success', 'Due payment collected successfully!');
        } elseif ($request->type === 'refund') {
            $maxRefundable = max(0, (float)$bill->paid_amount - $netPayable);
            if ($amount > $maxRefundable) {
                return back()->with('error', 'Refund amount cannot exceed the refundable amount!');
            }
            $newPaid = max(0, (float)$bill->paid_amount - $amount);
            $newDue = max(0, $netPayable - $newPaid);
            $newStatus = ($newPaid <= 0) ? 'due' : (($newDue <= 0) ? 'paid' : 'partial');

            $bill->update(['paid_amount' => $newPaid, 'due_amount' => $newDue, 'payment_status' => $newStatus]);
            Transaction::create([
                'shop_id' => auth()->user()->shop_id, 'bill_id' => $bill->id, 'user_id' => auth()->id(),
                'type' => 'expense', 'amount' => $amount, 'method' => $request->method ?? 'cash',
                'description' => 'Refund/Adjustment: ' . ($request->note ?? 'Amount refunded to customer'),
            ]);
            return back()->with('success', 'Amount refunded/adjusted successfully!');
        }
    }

    public function dueList()
    {
        $bills = Bill::with(['customer', 'device'])->where('due_amount', '>', 0)->orderBy('created_at', 'desc')->get();
        return view('bills.due-list', compact('bills'));
    }

    public function adjustBill(Request $request, string $id)
    {
        $request->validate([
            'new_total_amount' => 'required|numeric|min:0',
            'adjust_note' => 'required|string',
            'kept_items' => 'nullable|array',
            'new_service_categories' => 'nullable|array',
            'new_service_descriptions' => 'nullable|array',
            'new_service_warranties' => 'nullable|array',
            'new_service_amounts' => 'nullable|array',
        ]);

        $bill = Bill::with(['items', 'device'])->findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }
        if ($bill->device && in_array($bill->device->status, ['delivered', 'returned'])) {
            return back()->with('error', 'Security Alert: Cannot adjust bill for a delivered or returned device!');
        }

        $keptItems = $request->input('kept_items', []);

        foreach ($bill->items as $item) {
            $isKept = in_array($item->id, $keptItems);
            $hasCancelledTag = str_starts_with($item->description, '[Cancelled] ');

            if ($isKept && $hasCancelledTag) {
                $item->description = str_replace('[Cancelled] ', '', $item->description);
                $item->save();
            } elseif (!$isKept && !$hasCancelledTag) {
                $item->description = '[Cancelled] ' . $item->description;
                $item->save();
            }
        }

        $newCategories = $request->input('new_service_categories', []);
        $newDescriptions = $request->input('new_service_descriptions', []);
        $newWarranties = $request->input('new_service_warranties', []);
        $newAmounts = $request->input('new_service_amounts', []);

        if (!empty($newDescriptions)) {
            foreach ($newDescriptions as $index => $desc) {
                $cleanDesc = trim($desc);
                if ($cleanDesc !== '') {
                    $cat = $newCategories[$index] ?? 'hardware';
                    $amt = (float) ($newAmounts[$index] ?? 0);
                    $warranty = ($cat === 'hardware') ? ($newWarranties[$index] ?? null) : null;

                    $bill->items()->create([
                        'category' => $cat,
                        'description' => $cleanDesc,
                        'amount' => $amt,
                        'warranty' => $warranty,
                    ]);
                }
            }
        }

        $oldAmount = (float) $bill->total_amount;
        $newAmount = (float) $request->new_total_amount;

        $calculatedDiscount = ($bill->discount_type === 'percentage') ? (($newAmount * $bill->discount) / 100) : $bill->discount;
        $netPayable = max(0, $newAmount - $calculatedDiscount);
        $dueAmount = max(0, $netPayable - $bill->paid_amount);
        $paymentStatus = ($bill->paid_amount <= 0) ? 'due' : (($bill->paid_amount < $netPayable) ? 'partial' : 'paid');

        $bill->update([
            'total_amount' => $newAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
        ]);

        \App\Models\RepairLog::create([
            'device_id' => $bill->device_id,
            'user_id' => auth()->id(),
            'note' => "Bill Adjusted: From ৳{$oldAmount} to ৳{$newAmount}\nReason: " . $request->adjust_note,
        ]);

        $difference = abs($newAmount - $oldAmount);
        $message = ($newAmount == $oldAmount) ? "Bill details updated successfully!" : "Bill amount " . (($newAmount > $oldAmount) ? "increased" : "decreased") . " by ৳{$difference}.";

        return back()->with('success', $message);
    }
}
