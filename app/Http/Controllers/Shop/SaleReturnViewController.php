<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleReturnViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = SaleReturn::with(['sale.customer', 'items.saleItem']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('sale', function ($s) use ($search): void {
                        $s->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($c) use ($search): void {
                                $c->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $returns = $query->latest()->paginate(15)->withQueryString();
        $totalRefundsAmount = (float) SaleReturn::sum('total_refund_amount');

        // Allow picking recent completed sales for returns
        $recentSales = Sale::with(['customer', 'items.phoneDevice'])
            ->whereIn('status', ['completed', 'partial'])
            ->latest()
            ->take(50)
            ->get();

        return view('shop.sales.returns', compact('returns', 'totalRefundsAmount', 'recentSales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.refund_amount' => ['required', 'numeric', 'min:0'],
            'items.*.restock' => ['nullable', 'boolean'],
            'items.*.condition' => ['required', 'in:resalable,defective'],
        ]);

        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $sale = Sale::with('items')->findOrFail($request->input('sale_id'));

        DB::transaction(function () use ($request, $shopId, $sale): void {
            $returnNumber = 'RET-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $totalRefund = 0.00;
            foreach ($request->input('items') as $row) {
                $totalRefund += (float) $row['refund_amount'];
            }

            $saleReturn = SaleReturn::create([
                'shop_id' => $shopId,
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'return_number' => $returnNumber,
                'return_date' => $request->input('return_date'),
                'total_refund_amount' => $totalRefund,
                'reason' => $request->input('reason'),
                'notes' => $request->input('notes'),
            ]);

            foreach ($request->input('items') as $itemData) {
                $saleItem = SaleItem::find($itemData['sale_item_id']);
                if (! $saleItem) {
                    continue;
                }

                $qty = (int) $itemData['quantity'];
                $refundAmt = (float) $itemData['refund_amount'];
                $restock = ! empty($itemData['restock']);
                $condition = $itemData['condition'];

                SaleReturnItem::create([
                    'shop_id' => $shopId,
                    'sale_return_id' => $saleReturn->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'phone_device_id' => $saleItem->phone_device_id,
                    'quantity' => $qty,
                    'refund_amount' => $refundAmt,
                    'restock' => $restock,
                    'condition' => $condition,
                ]);

                // Handle inventory restock
                if ($saleItem->product_id && $restock && $condition === 'resalable') {
                    $prod = Product::find($saleItem->product_id);
                    if ($prod) {
                        $prod->increment('stock_quantity', $qty);
                        StockMovement::create([
                            'shop_id' => $shopId,
                            'product_id' => $prod->id,
                            'type' => 'return',
                            'quantity' => $qty,
                            'reference_type' => 'Sale Return',
                            'reference_id' => $saleReturn->id,
                            'notes' => "Returned on {$saleReturn->return_number} (Invoice: {$sale->invoice_number})",
                        ]);
                    }
                } elseif ($saleItem->phone_device_id) {
                    $phone = PhoneDevice::find($saleItem->phone_device_id);
                    if ($phone) {
                        $newStatus = ($condition === 'defective') ? 'defective' : 'in_stock';
                        $phone->update([
                            'status' => $newStatus,
                            'sold_at' => null,
                            'sale_id' => null,
                        ]);

                        StockMovement::create([
                            'shop_id' => $shopId,
                            'phone_device_id' => $phone->id,
                            'type' => 'return',
                            'quantity' => 1,
                            'reference_type' => 'Sale Return',
                            'reference_id' => $saleReturn->id,
                            'notes' => "Device returned: {$newStatus} (Invoice: {$sale->invoice_number})",
                        ]);
                    }
                }
            }

            // Update original sale status
            $sale->update(['status' => 'returned']);
        });

        return redirect()->route('shop.sales.returns')->with('success', 'Sales return processed and items restocked successfully.');
    }
}

