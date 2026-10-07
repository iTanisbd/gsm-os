<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Device;
use App\Models\Transaction; // নতুন ট্রানজেকশন মডেল যুক্ত করা হলো
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ডাটাবেস ট্রানজেকশনের জন্য

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
        // যেসব ডিভাইসের বিল আগে থেকেই তৈরি হয়ে গেছে, তাদের আইডিগুলো খুঁজে বের করা
        $billedDeviceIds = Bill::where('shop_id', auth()->user()->shop_id)->pluck('device_id');

        // ড্রপডাউনের জন্য শুধু সেই ডিভাইসগুলোই আনব, যেগুলোর এখনো কোনো বিল হয়নি
        $devices = Device::with('customer')
            ->where('shop_id', auth()->user()->shop_id)
            ->whereNotIn('id', $billedDeviceIds) // প্রফেশনাল লজিক: ফিল্টার করে দেওয়া হলো
            ->latest()
            ->get();

        return view('bills.create', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $device = Device::findOrFail($request->device_id);

        $total = $request->total_amount;
        $discount = $request->discount ?? 0;
        $paid = $request->paid_amount;

        $payable = $total - $discount;
        $due = $payable - $paid;

        if ($due <= 0) {
            $status = 'paid';
            $due = 0;
        } elseif ($paid == 0) {
            $status = 'unpaid'; // ডাটাবেসের enum অনুযায়ী unpaid
        } else {
            $status = 'partial';
        }

        // ব্যাংকিং গ্রেড সিকিউরিটি: DB Transaction শুরু
        DB::beginTransaction();

        try {
            // ১. বিল তৈরি
            // ১. বিল তৈরি
            $bill = Bill::create([
                'shop_id' => auth()->user()->shop_id,
                'customer_id' => $device->customer_id,
                'device_id' => $device->id,
                'total_amount' => $total,
                'discount' => $discount,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => $request->note,
            ]);

            // ২. যদি কোনো অ্যাডভান্স পেমেন্ট থাকে, তবে তা Transactions টেবিলে সেভ হবে
            if ($paid > 0) {
                Transaction::create([
                    'shop_id' => auth()->user()->shop_id,
                    'bill_id' => $bill->id,
                    'customer_id' => $device->customer_id,
                    'type' => 'payment',
                    'amount' => $paid,
                    'note' => 'Advance payment during bill creation',
                ]);
            }

            // ৩. যদি কোনো ডিসকাউন্ট থাকে, তবে সেটিরও রেকর্ড থাকবে
            if ($discount > 0) {
                Transaction::create([
                    'shop_id' => auth()->user()->shop_id,
                    'bill_id' => $bill->id,
                    'customer_id' => $device->customer_id,
                    'type' => 'discount',
                    'amount' => $discount,
                    'note' => 'Discount applied during bill creation',
                ]);
            }

            // সব ঠিক থাকলে ডাটাবেসে সেভ করো
            DB::commit();

            return redirect()->route('bills.index')->with('success', 'Bill created successfully with transaction records!');

        } catch (\Exception $e) {
            // কোনো এরর হলে কোনো ডাটাই সেভ হবে না, আগের অবস্থায় ফিরে যাবে
            DB::rollBack();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $bill = Bill::with(['customer', 'device'])->findOrFail($id);

        if ($bill->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('bills.show', compact('bill'));
    }

    public function edit(string $id)
    {
        $bill = Bill::with(['customer', 'device'])->findOrFail($id);

        if ($bill->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('bills.edit', compact('bill'));
    }

    public function update(Request $request, string $id)
    {
        // যেহেতু আমাদের কোর ফিলোসফি হলো বিল এডিট করা যাবে না,
        // তাই আপাতত এই আপডেট মেথডটি ব্লক করে রাখছি।
        // ভুল হলে রিফান্ড বা অ্যাডজাস্টমেন্ট করতে হবে।
        abort(403, 'Editing bills is blocked by Master Blueprint v1.1 policy. Use Refunds/Adjustments instead.');
    }

    public function destroy(string $id)
    {
        $bill = Bill::findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }

        $bill->delete();

        return redirect()->route('bills.index')->with('success', 'Bill deleted successfully!');
    }
}
