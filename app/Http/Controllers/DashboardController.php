<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $shopId = auth()->user()->shop_id;
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 1. Overall Basic Stats
        $totalRevenue = Bill::where('shop_id', $shopId)->sum('paid_amount');
        $totalDue = Bill::where('shop_id', $shopId)->sum('due_amount');
        $totalCustomers = Customer::where('shop_id', $shopId)->count();
        $pendingDevices = Device::where('shop_id', $shopId)->whereIn('status', ['pending', 'processing'])->count();

        // 2. Secondary Operational Stats
        $totalDevices = Device::where('shop_id', $shopId)->count();
        $completedDevices = Device::where('shop_id', $shopId)->where('status', 'completed')->count();
        $deliveredDevices = Device::where('shop_id', $shopId)->where('status', 'delivered')->count();
        $partialDevices = Device::where('shop_id', $shopId)->where('repair_outcome', 'partially_repaired')->count();
        $returnedDevices = Device::where('shop_id', $shopId)->where('status', 'returned')->count();

        // 3. Current Month Financials (Profit/Loss)
        $monthlyIncome = Bill::where('shop_id', $shopId)
                             ->whereMonth('created_at', $currentMonth)
                             ->whereYear('created_at', $currentYear)
                             ->sum('paid_amount');

        $monthlyExpense = Expense::where('shop_id', $shopId)
                               ->whereMonth('expense_date', $currentMonth)
                               ->whereYear('expense_date', $currentYear)
                               ->sum('amount');

        $netProfit = $monthlyIncome - $monthlyExpense;

        // 4. Chart 1: Repair Status Distribution (For Pie Chart)
        $repairStatusData = [
            'Pending' => Device::where('shop_id', $shopId)->where('status', 'pending')->count(),
            'Processing' => Device::where('shop_id', $shopId)->where('status', 'processing')->count(),
            'Completed' => $completedDevices,
            'Delivered' => $deliveredDevices,
            'Returned' => $returnedDevices,
        ];

        // 5. Chart 2: Dynamic Income vs Expense Filter Logic
        $filter = $request->input('chart_filter', '7days');

        $chartLabels = collect();
        $incomeData = collect();
        $expenseData = collect();

        if ($filter == 'this_year') {
            // Group by 12 Months
            for ($i = 1; $i <= 12; $i++) {
                $date = Carbon::create($currentYear, $i, 1);
                $chartLabels->push($date->format('M'));

                $incomeData->push(Bill::where('shop_id', $shopId)->whereMonth('created_at', $i)->whereYear('created_at', $currentYear)->sum('paid_amount'));
                $expenseData->push(Expense::where('shop_id', $shopId)->whereMonth('expense_date', $i)->whereYear('expense_date', $currentYear)->sum('amount'));
            }
        } elseif ($filter == 'this_month') {
            // Group by Days of Current Month
            $daysInMonth = Carbon::now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i += 2) { // Skipping every alternate day for clean X-axis display
                $chartLabels->push($i);
                $dateString = Carbon::create($currentYear, $currentMonth, $i)->format('Y-m-d');

                // Get data for this specific day and the next day to combine them (so we don't lose data by skipping labels)
                $nextDateString = Carbon::create($currentYear, $currentMonth, $i + 1)->format('Y-m-d');

                $inc1 = Bill::where('shop_id', $shopId)->whereDate('created_at', $dateString)->sum('paid_amount');
                $inc2 = ($i < $daysInMonth) ? Bill::where('shop_id', $shopId)->whereDate('created_at', $nextDateString)->sum('paid_amount') : 0;

                $exp1 = Expense::where('shop_id', $shopId)->whereDate('expense_date', $dateString)->sum('amount');
                $exp2 = ($i < $daysInMonth) ? Expense::where('shop_id', $shopId)->whereDate('expense_date', $nextDateString)->sum('amount') : 0;

                $incomeData->push($inc1 + $inc2);
                $expenseData->push($exp1 + $exp2);
            }
        } else {
            // Default: Last 7 Days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $displayDate = Carbon::now()->subDays($i)->format('d M');

                $chartLabels->push($displayDate);
                $incomeData->push(Bill::where('shop_id', $shopId)->whereDate('created_at', $date)->sum('paid_amount'));
                $expenseData->push(Expense::where('shop_id', $shopId)->whereDate('expense_date', $date)->sum('amount'));
            }
        }

        $chartData = [
            'labels' => $chartLabels,
            'income' => $incomeData,
            'expense' => $expenseData,
            'current_filter' => $filter
        ];

        return view('dashboard', compact(
            'totalRevenue', 'totalDue', 'totalCustomers', 'pendingDevices',
            'totalDevices', 'completedDevices', 'deliveredDevices', 'partialDevices', 'returnedDevices',
            'monthlyIncome', 'monthlyExpense', 'netProfit', 'repairStatusData', 'chartData'
        ));
    }
}
