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
        // ১. ফর্মের ডাটা ভ্যালিডেশন
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei' => 'nullable|string|max:100',
            'problem_description' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // ২. ডাটাবেসে ডিভাইস সেভ করা
        Device::create([
            'shop_id' => $user->shop_id,
            'customer_id' => $request->customer_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'imei' => $request->imei,
            'problem_description' => $request->problem_description,
            'status' => 'pending', // ডিফল্ট স্ট্যাটাস
        ]);

        // ৩. সেভ হওয়ার পর লিস্ট পেজে ফিরে যাওয়া
        return redirect()->route('devices.index')->with('success', 'Device received successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
