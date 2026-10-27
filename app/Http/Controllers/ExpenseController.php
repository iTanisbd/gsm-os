<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        // ডিফল্টভাবে চলতি মাসের খরচ দেখাবে
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $expenses = Expense::with('user')
            ->where('shop_id', auth()->user()->shop_id)
            ->whereMonth('expense_date', Carbon::parse($month)->month)
            ->whereYear('expense_date', Carbon::parse($month)->year)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalExpense = $expenses->sum('amount');

        return view('expenses.index', compact('expenses', 'month', 'totalExpense'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        Expense::create([
            'shop_id' => auth()->user()->shop_id,
            'user_id' => auth()->id(),
            'category' => $request->category,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'note' => $request->note,
        ]);

        return back()->with('success', 'Expense added successfully!');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->shop_id !== auth()->user()->shop_id) {
            abort(403);
        }

        $expense->delete();
        return back()->with('success', 'Expense record deleted.');
    }
}
