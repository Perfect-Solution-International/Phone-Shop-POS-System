<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sale::with(['customer', 'cashier', 'items.phoneDevice', 'payments']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search): void {
                        $c->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($i) use ($search): void {
                        $i->where('item_name', 'like', "%{$search}%")
                            ->orWhereHas('phoneDevice', function ($p) use ($search): void {
                                $p->where('imei_1', 'like', "%{$search}%")
                                    ->orWhere('imei_2', 'like', "%{$search}%")
                                    ->orWhere('serial_number', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($date = $request->input('date')) {
            $query->whereDate('sale_date', $date);
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sales = $query->latest()->paginate(20)->withQueryString();

        $today = now()->toDateString();
        $todayTotal = (float) Sale::whereDate('sale_date', $today)->sum('total_amount');
        $todayCount = Sale::whereDate('sale_date', $today)->count();
        $totalSalesAmount = (float) Sale::sum('total_amount');
        $totalSalesCount = Sale::count();

        return view('shop.sales.index', compact(
            'sales',
            'todayTotal',
            'todayCount',
            'totalSalesAmount',
            'totalSalesCount'
        ));
    }

    public function show(Request $request, Sale $sale): View|JsonResponse
    {
        $sale->load(['customer', 'cashier', 'items.phoneDevice', 'items.product', 'payments']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($sale);
        }

        return view('shop.sales.show', compact('sale'));
    }
}

