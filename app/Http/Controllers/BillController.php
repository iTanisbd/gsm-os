<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Device;
use Illuminate\Http\Request;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // বিলের সাথে কাস্টমার এবং ডিভাইসের ডাটা একসাথে নিয়ে আসা
        $bills = Bill::with(['customer', 'device'])
            ->where('shop_id', auth()->user()->shop_id)
            ->latest()
            ->get();

        return view('bills.index', compact('bills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // বিল করার জন্য বর্তমান দোকানের সব ডিভাইসগুলো নিয়ে আসা
        $devices = Device::with('customer')
            ->where('shop_id', auth()->user()->shop_id)
            ->latest()
            ->get();

        return view('bills.create', compact('devices'));
    }

    /**
     * Store a newly created resource in storage.
     */
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

        // হিসাব-নিকাশ
        $total = $request->total_amount;
        $discount = $request->discount ?? 0;
        $paid = $request->paid_amount;

        $payable = $total - $discount; // ডিসকাউন্টের পর কত দিতে হবে
        $due = $payable - $paid; // কত বাকি থাকল

        // পেমেন্ট স্ট্যাটাস লজিক
        if ($due <= 0) {
            $status = 'paid';
            $due = 0; // যদি বেশি টাকা দেয়, তবে বকেয়া যেন মাইনাস না হয়
        } elseif ($paid == 0) {
            $status = 'due';
        } else {
            $status = 'partial'; // আংশিক পরিশোধ
        }

        // ডাটাবেসে বিল সেভ করা
        Bill::create([
            'shop_id' => auth()->user()->shop_id,
            'customer_id' => $device->customer_id, // ডিভাইস থেকে অটো কাস্টমার আইডি পেয়ে যাব
            'device_id' => $device->id,
            'total_amount' => $total,
            'discount' => $discount,
            'paid_amount' => $paid,
            'due_amount' => $due,
            'payment_status' => $status,
            'note' => $request->note,
        ]);

        return redirect()->route('bills.index')->with('success', 'Bill created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bill = Bill::with(['customer', 'device'])->findOrFail($id);

        if ($bill->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bill = Bill::with(['customer', 'device'])->findOrFail($id);

        if ($bill->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('bills.edit', compact('bill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bill = Bill::findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }

        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $total = $request->total_amount;
        $discount = $request->discount ?? 0;
        $paid = $request->paid_amount;

        $payable = $total - $discount;
        $due = $payable - $paid;

        if ($due <= 0) {
            $status = 'paid';
            $due = 0;
        } elseif ($paid == 0) {
            $status = 'due';
        } else {
            $status = 'partial';
        }

        $bill->update([
            'total_amount' => $total,
            'discount' => $discount,
            'paid_amount' => $paid,
            'due_amount' => $due,
            'payment_status' => $status,
            'note' => $request->note,
        ]);

        return redirect()->route('bills.index')->with('success', 'Bill updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bill = Bill::findOrFail($id);
        if ($bill->shop_id !== auth()->user()->shop_id) { abort(403); }

        $bill->delete();

        return redirect()->route('bills.index')->with('success', 'Bill deleted successfully!');
    }
}
