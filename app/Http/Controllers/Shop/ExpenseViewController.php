<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($cat = $request->input('category')) {
            $query->where('category', 'like', "%{$cat}%");
        }

        if ($date = $request->input('date')) {
            $query->whereDate('expense_date', $date);
        }

        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString();

        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $todayTotal = (float) Expense::whereDate('expense_date', $today)->sum('amount');
        $thisMonthTotal = (float) Expense::where('expense_date', 'like', "{$thisMonth}%")->sum('amount');
        $allTimeTotal = (float) Expense::sum('amount');

        return view('shop.expenses.index', compact(
            'expenses',
            'todayTotal',
            'thisMonthTotal',
            'allTimeTotal'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['user_id'] = auth()->id();

        Expense::create($data);

        return redirect()->route('shop.expenses')->with('success', "Expense '{$data['title']}' recorded successfully.");
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $title = $expense->title;
        $expense->delete();

        return redirect()->route('shop.expenses')->with('success', "Expense '{$title}' removed.");
    }
}

