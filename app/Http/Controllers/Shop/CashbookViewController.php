<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\RepairJob;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashbookViewController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate = $request->input('date', now()->toDateString());

        // Cash Inflow for the date
        $cashSales = SalePayment::with('sale.customer')
            ->where('payment_method', 'cash')
            ->whereDate('created_at', $selectedDate)
            ->latest()
            ->get();
        $totalSalesCashIn = (float) $cashSales->sum('amount');

        $repairAdvances = RepairJob::with('customer')
            ->where('advance_amount', '>', 0)
            ->whereDate('created_at', $selectedDate)
            ->get();
        $totalRepairCashIn = (float) $repairAdvances->sum('advance_amount');

        // Repair delivery final balance cash collected
        $repairDeliveries = RepairJob::with('customer')
            ->whereNotNull('delivered_at')
            ->whereDate('delivered_at', $selectedDate)
            ->where('final_cost', '>', 0)
            ->get()
            ->filter(function ($job) {
                return (float) $job->final_cost > (float) $job->advance_amount;
            });
        $totalRepairDeliveriesCashIn = (float) $repairDeliveries->sum(function ($job) {
            return max(0, (float) $job->final_cost - (float) $job->advance_amount);
        });

        // Customer Debt settlement cash collected
        $debtPayments = \App\Models\CustomerDebtPayment::with('customer')
            ->where('payment_method', 'cash')
            ->whereDate('payment_date', $selectedDate)
            ->latest()
            ->get();
        $totalDebtPaymentsCashIn = (float) $debtPayments->sum('amount');

        $totalCashIn = $totalSalesCashIn + $totalRepairCashIn + $totalRepairDeliveriesCashIn + $totalDebtPaymentsCashIn;

        // Cash Outflow for the date
        $cashExpenses = Expense::where('payment_method', 'cash')
            ->whereDate('expense_date', $selectedDate)
            ->latest()
            ->get();
        $totalCashExpenses = (float) $cashExpenses->sum('amount');

        $cashPurchases = Purchase::with('supplier')
            ->where('paid_amount', '>', 0)
            ->whereDate('purchase_date', $selectedDate)
            ->latest()
            ->get();
        $totalPurchasesCashOut = (float) $cashPurchases->sum('paid_amount');

        $totalCashOut = $totalCashExpenses + $totalPurchasesCashOut;

        $netCashBalance = $totalCashIn - $totalCashOut;

        return view('shop.cashbook.index', compact(
            'selectedDate',
            'cashSales',
            'totalSalesCashIn',
            'repairAdvances',
            'totalRepairCashIn',
            'repairDeliveries',
            'totalRepairDeliveriesCashIn',
            'debtPayments',
            'totalDebtPaymentsCashIn',
            'totalCashIn',
            'cashExpenses',
            'totalCashExpenses',
            'cashPurchases',
            'totalPurchasesCashOut',
            'totalCashOut',
            'netCashBalance'
        ));
    }
}

