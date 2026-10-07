<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Customer;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // বর্তমান ইউজারের দোকানের সব ডিভাইস এবং কাস্টমারের ডাটা একসাথে নিয়ে আসা (Eager Loading)
        $devices = Device::with('customer')
            ->where('shop_id', auth()->user()->shop_id)
            ->latest()
            ->get();

        return view('devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ড্রপডাউনে দেখানোর জন্য বর্তমান দোকানের সব কাস্টমারদের লিস্ট নিয়ে আসা
        $customers = Customer::where('shop_id', auth()->user()->shop_id)->latest()->get();
        return view('devices.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei' => 'nullable|string|max:100',
            'device_password' => 'nullable|string|max:50', // নতুন ফিল্ড
            'problem_description' => 'required|string',
            'actual_fault' => 'nullable|string',
            'rack_number' => 'nullable|string|max:50',
            'drawer_number' => 'nullable|string|max:50',
            'pre_repair_checklist' => 'nullable|array',
            'estimated_delivery_date' => 'nullable|date', // নতুন ফিল্ড
        ]);

        Device::create([
            'shop_id' => auth()->user()->shop_id,
            'customer_id' => $request->customer_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'imei' => $request->imei,
            'device_password' => $request->device_password, // সেভ হচ্ছে
            'problem_description' => $request->problem_description,
            'status' => 'pending',
            'actual_fault' => $request->actual_fault,
            'rack_number' => $request->rack_number,
            'drawer_number' => $request->drawer_number,
            'pre_repair_checklist' => $request->pre_repair_checklist,
            'estimated_delivery_date' => $request->estimated_delivery_date, // সেভ হচ্ছে
            'risk_agreement' => $request->has('risk_agreement'), // চেকবক্স ট্র্যাকিং
        ]);

        return redirect()->route('devices.index')->with('success', 'Device received successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // ডিভাইস, কাস্টমার, রিপেয়ার লগ এবং বিল কল করা হলো
        $device = Device::with(['customer', 'repairLogs.user', 'bills'])->findOrFail($id);

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        // এই ডিভাইসের সর্বশেষ বিলটি বের করা হচ্ছে
        $bill = $device->bills->first();

        return view('devices.show', compact('device', 'bill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $device = Device::findOrFail($id);

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        // ড্রপডাউনের জন্য কাস্টমারদের লিস্টও পাঠাতে হবে
        $customers = Customer::where('shop_id', auth()->user()->shop_id)->latest()->get();

        return view('devices.edit', compact('device', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $device = Device::findOrFail($id);

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei' => 'nullable|string|max:100',
            'problem_description' => 'required|string',
            'status' => 'required|in:pending,processing,completed,delivered', // স্ট্যাটাস ভ্যালিডেশন
        ]);

        $device->update([
            'customer_id' => $request->customer_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'imei' => $request->imei,
            'problem_description' => $request->problem_description,
            'status' => $request->status, // আপডেট করা স্ট্যাটাস
        ]);

        return redirect()->route('devices.index')->with('success', 'Device updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $device = Device::findOrFail($id);

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device deleted successfully!');
    }

    // নতুন মেথড: রিপেয়ার লগ বা নোট সেভ করার জন্য
    public function addRepairLog(Request $request, string $id)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $device = Device::findOrFail($id);

        if ($device->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        \App\Models\RepairLog::create([
            'device_id' => $device->id,
            'user_id' => auth()->id(), // যে টেকনিশিয়ান লগ ইন করা আছে
            'note' => $request->note,
        ]);

        return back()->with('success', 'Timeline updated successfully!');
    }
}
