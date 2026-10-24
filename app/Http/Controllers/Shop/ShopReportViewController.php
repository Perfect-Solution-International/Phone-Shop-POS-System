<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\RepairJob;
use App\Models\RepairPartUsed;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SparePart;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopReportViewController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // 1. Sales & Revenue
        $salesQuery = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'partial']);

        $totalSalesRevenue = (float) (clone $salesQuery)->sum('total_amount');
        $totalSalesCount = (clone $salesQuery)->count();
        $avgTicketValue = $totalSalesCount > 0 ? ($totalSalesRevenue / $totalSalesCount) : 0;

        // Payment Method breakdown
        $cashSalesTotal = (float) (clone $salesQuery)->where('payment_method', 'cash')->sum('total_amount');
        $cardSalesTotal = (float) (clone $salesQuery)->where('payment_method', 'card')->sum('total_amount');
        $splitSalesTotal = (float) (clone $salesQuery)->where('payment_method', 'split')->sum('total_amount');
        $otherSalesTotal = (float) (clone $salesQuery)->whereNotIn('payment_method', ['cash', 'card', 'split'])->sum('total_amount');

        // Top 5 Products sold
        $topProducts = SaleItem::selectRaw('item_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->groupBy('item_name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        // 2. Inventory Valuation
        $inventoryCostTotal = (float) (
            Product::selectRaw('SUM(stock_quantity * cost_price) as t')->value('t') +
            PhoneDevice::where('status', 'in_stock')->sum('purchase_cost') +
            SparePart::selectRaw('SUM(stock_quantity * cost_price) as t')->value('t')
        );

        $inventoryRetailTotal = (float) (
            Product::selectRaw('SUM(stock_quantity * retail_price) as t')->value('t') +
            PhoneDevice::where('status', 'in_stock')->sum('selling_price') +
            SparePart::selectRaw('SUM(stock_quantity * selling_price) as t')->value('t')
        );

        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'alert_quantity')->count();

        // 3. Repairs Analysis
        $repairsQuery = RepairJob::whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        $totalRepairsCount = (clone $repairsQuery)->count();
        $deliveredRepairsCount = (clone $repairsQuery)->where('status', 'delivered')->count();
        $repairRevenue = (float) (clone $repairsQuery)->where('status', 'delivered')->sum('final_cost');
        $repairPartsConsumedCost = (float) RepairPartUsed::whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->selectRaw('SUM(quantity * unit_cost) as total')
            ->value('total');

        // 4. Financial P&L
        $operatingExpenses = (float) Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $estimatedCogs = $totalSalesRevenue * 0.70; // Baseline estimated goods cost if line items cost is variable
        $grossMargin = $totalSalesRevenue - $estimatedCogs;
        $netEstimatedProfit = $grossMargin + ($repairRevenue - $repairPartsConsumedCost) - $operatingExpenses;

        return view('shop.reports.index', compact(
            'startDate',
            'endDate',
            'totalSalesRevenue',
            'totalSalesCount',
            'avgTicketValue',
            'cashSalesTotal',
            'cardSalesTotal',
            'splitSalesTotal',
            'otherSalesTotal',
            'topProducts',
            'inventoryCostTotal',
            'inventoryRetailTotal',
            'lowStockProducts',
            'totalRepairsCount',
            'deliveredRepairsCount',
            'repairRevenue',
            'repairPartsConsumedCost',
            'operatingExpenses',
            'estimatedCogs',
            'grossMargin',
            'netEstimatedProfit'
        ));
    }
}

