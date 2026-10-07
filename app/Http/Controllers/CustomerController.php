<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // বর্তমান ইউজারের দোকানের (shop) সব কাস্টমারদের ডাটাবেস থেকে নিয়ে আসা
        $customers = Customer::where('shop_id', auth()->user()->shop_id)->latest()->get();

        // ডাটাগুলো index ভিউ পেজে পাঠানো
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ১. ফর্মের ডাটা ভ্যালিডেশন
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        // ২. ডাটাবেসে কাস্টমার সেভ করা (বর্তমান ইউজারের shop_id সহ)
        Customer::create([
            'shop_id' => auth()->user()->shop_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'phone_secondary' => $request->phone_secondary,
            'address' => $request->address,
            'note' => $request->note,
        ]);

        // ৩. সেভ হওয়ার পর কাস্টমার লিস্ট পেজে ফিরে যাওয়া
        return redirect()->route('customers.index')->with('success', 'Customer added successfully!');
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
        $customer = Customer::findOrFail($id);

        // সিকিউরিটি চেক: অন্য দোকানের কেউ যেন এই কাস্টমার এডিট করতে না পারে
        if ($customer->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'phone_secondary' => $request->phone_secondary,
            'address' => $request->address,
            'note' => $request->note,
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->shop_id !== auth()->user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully!');
    }
}
