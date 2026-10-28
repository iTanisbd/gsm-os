<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Device;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        $shopId = auth()->user()->shop_id;

        // ইনপুট ফাঁকা থাকলে এম্পটি রেজাল্ট রিটার্ন করবে
        if (empty($query)) {
            return response()->json(['customers' => [], 'devices' => []]);
        }

        // কাস্টমার সার্চ (নাম অথবা ফোন নাম্বার দিয়ে)
        $customers = Customer::where('shop_id', $shopId)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%");
            })
            ->take(5) // সর্বোচ্চ ৫টি রেজাল্ট আনবে
            ->get();

        // ডিভাইস সার্চ (ট্র্যাকিং কোড অথবা মডেল দিয়ে)
        $devices = Device::with('customer') // ডিভাইসের সাথে কাস্টমারের ইনফোও আনবে
            ->where('shop_id', $shopId)
            ->where(function($q) use ($query) {
                $q->where('tracking_code', 'LIKE', "%{$query}%")
                  ->orWhere('model', 'LIKE', "%{$query}%");
            })
            ->take(5)
            ->get();

        return response()->json([
            'customers' => $customers,
            'devices' => $devices
        ]);
    }
}
