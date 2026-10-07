<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Device;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $shop_id = auth()->user()->shop_id;

        // ডাটাবেস থেকে রিয়েল-টাইম হিসাব বের করা
        $totalCustomers = Customer::where('shop_id', $shop_id)->count();

        $totalDevices = Device::where('shop_id', $shop_id)->count();
        $pendingDevices = Device::where('shop_id', $shop_id)->where('status', 'pending')->count();
        $completedDevices = Device::where('shop_id', $shop_id)->where('status', 'completed')->count();
        $deliveredDevices = Device::where('shop_id', $shop_id)->where('status', 'delivered')->count();

        // টাকার হিসাব
        $totalRevenue = Bill::where('shop_id', $shop_id)->sum('paid_amount');
        $totalDue = Bill::where('shop_id', $shop_id)->sum('due_amount');

        return view('dashboard', compact(
            'totalCustomers',
            'totalDevices',
            'pendingDevices',
            'completedDevices',
            'deliveredDevices',
            'totalRevenue',
            'totalDue'
        ));
    }
}
