<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\SparePart;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryMovementViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = StockMovement::with(['product', 'phoneDevice']);

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($p) use ($search): void {
                        $p->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('phoneDevice', function ($d) use ($search): void {
                        $d->where('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('imei_1', 'like', "%{$search}%");
                    });
            });
        }

        $movements = $query->latest()->paginate(20)->withQueryString();

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $spareParts = SparePart::orderBy('name')->get();

        // Stock valuation calculations
        $productCostValuation = (float) Product::selectRaw('SUM(stock_quantity * cost_price) as total')->value('total');
        $phoneCostValuation = (float) PhoneDevice::where('status', 'in_stock')->sum('purchase_cost');
        $partsCostValuation = (float) SparePart::selectRaw('SUM(stock_quantity * cost_price) as total')->value('total');
        $totalInventoryCostValuation = $productCostValuation + $phoneCostValuation + $partsCostValuation;

        $inStockPhonesCount = PhoneDevice::where('status', 'in_stock')->count();
        $totalProductsCount = Product::sum('stock_quantity');
        $totalSparePartsCount = SparePart::sum('stock_quantity');

        return view('shop.inventory.index', compact(
            'movements',
            'products',
            'spareParts',
            'totalInventoryCostValuation',
            'inStockPhonesCount',
            'totalProductsCount',
            'totalSparePartsCount'
        ));
    }

    public function adjust(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isOwnerOrAdmin(), 403, 'Only shop owners and administrators can adjust inventory.');

        $data = $request->validate([
            'item_type' => ['required', 'in:product,spare_part'],
            'item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'in:damaged,inventory_count,shrinkage,correction,other'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $qty = (int) $data['quantity'];
        $notes = "Manual adjustment: " . ucfirst(str_replace('_', ' ', $data['reason'])) . ($data['notes'] ? " ({$data['notes']})" : "");

        if ($data['item_type'] === 'product') {
            $product = Product::findOrFail($data['item_id']);
            $product->increment('stock_quantity', $qty);

            StockMovement::create([
                'shop_id' => $shopId,
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => $qty,
                'reference_type' => 'Manual Adjustment',
                'notes' => $notes,
            ]);

            return redirect()->route('shop.inventory')->with('success', "Stock for '{$product->name}' adjusted by {$qty}.");
        } else {
            $sparePart = SparePart::findOrFail($data['item_id']);
            $sparePart->increment('stock_quantity', $qty);

            return redirect()->route('shop.inventory')->with('success', "Stock for '{$sparePart->name}' adjusted by {$qty}.");
        }
    }
}

