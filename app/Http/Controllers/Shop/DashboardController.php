<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $today = now()->toDateString();

        $todaySalesTotal = (float) Sale::whereDate('sale_date', $today)->sum('total_amount');
        $todayTransactions = Sale::whereDate('sale_date', $today)->count();

        // Payment breakdown today
        $cashCollections = (float) SalePayment::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $today))
            ->where('payment_method', 'cash')
            ->sum('amount');

        $cardCollections = (float) SalePayment::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $today))
            ->where('payment_method', 'card')
            ->sum('amount');

        $bankCollections = (float) SalePayment::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $today))
            ->where('payment_method', 'bank_transfer')
            ->sum('amount');

        // Phone inventory metrics
        $availablePhones = PhoneDevice::where('status', 'in_stock')->count();
        $soldPhonesToday = PhoneDevice::where('status', 'sold')->whereDate('sold_at', $today)->count();

        // Inventory alerts
        $lowStockCount = Product::whereColumn('stock_quantity', '<=', 'alert_quantity')->count();
        $totalCustomers = Customer::count();
        $outstandingCredit = (float) Customer::where('balance', '>', 0)->sum('balance');

        $recentSales = Sale::with(['customer', 'cashier'])
            ->latest()
            ->limit(8)
            ->get();

        $recentPhones = PhoneDevice::where('status', 'in_stock')
            ->latest()
            ->limit(8)
            ->get();

        $metrics = [
            'today_sales_total' => $todaySalesTotal,
            'today_transactions' => $todayTransactions,
            'cash_collections' => $cashCollections,
            'card_collections' => $cardCollections,
            'bank_collections' => $bankCollections,
            'available_phones' => $availablePhones,
            'sold_phones_today' => $soldPhonesToday,
            'low_stock_count' => $lowStockCount,
            'total_customers' => $totalCustomers,
            'outstanding_credit' => $outstandingCredit,
            'recent_sales' => $recentSales,
            'recent_phones' => $recentPhones,
        ];

        if ($request->wantsJson()) {
            return response()->json($metrics);
        }

        return view('shop.dashboard.index', ['metrics' => $metrics]);
    }
}

