<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'items']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($s) use ($search): void {
                        $s->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($i) use ($search): void {
                        $i->where('item_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('purchase_date', $date);
        }

        if ($status = $request->input('payment_status')) {
            $query->where('payment_status', $status);
        }

        $purchases = $query->latest()->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $spareParts = SparePart::orderBy('name')->get();

        $totalPurchasesAmount = (float) Purchase::sum('total_amount');
        $totalPaidAmount = (float) Purchase::sum('paid_amount');
        $totalOutstandingPayables = (float) Supplier::sum('balance');

        return view('shop.purchases.index', compact(
            'purchases',
            'suppliers',
            'products',
            'spareParts',
            'totalPurchasesAmount',
            'totalPaidAmount',
            'totalOutstandingPayables'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:product,spare_part,phone'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.spare_part_id' => ['nullable', 'exists:spare_parts,id'],
            'items.*.brand' => ['nullable', 'string', 'max:255'],
            'items.*.model' => ['nullable', 'string', 'max:255'],
            'items.*.imei_1' => ['nullable', 'string', 'max:50'],
            'items.*.imei_2' => ['nullable', 'string', 'max:50'],
            'items.*.selling_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $supplier = Supplier::findOrFail($request->input('supplier_id'));
        $paidAmount = (float) ($request->input('paid_amount') ?? 0.00);

        DB::transaction(function () use ($request, $shopId, $supplier, $paidAmount): void {
            $purchaseNumber = 'PUR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $totalAmount = 0.00;
            foreach ($request->input('items') as $row) {
                $totalAmount += ((int) $row['quantity']) * ((float) $row['unit_cost']);
            }

            $paymentStatus = 'paid';
            if ($paidAmount <= 0) {
                $paymentStatus = 'unpaid';
            } elseif ($paidAmount < $totalAmount) {
                $paymentStatus = 'partial';
            }

            $purchase = Purchase::create([
                'shop_id' => $shopId,
                'supplier_id' => $supplier->id,
                'purchase_number' => $purchaseNumber,
                'purchase_date' => $request->input('purchase_date'),
                'total_amount' => $totalAmount,
                'paid_amount' => min($paidAmount, $totalAmount),
                'payment_status' => $paymentStatus,
                'notes' => $request->input('notes'),
            ]);

            foreach ($request->input('items') as $itemData) {
                $qty = (int) $itemData['quantity'];
                $unitCost = (float) $itemData['unit_cost'];
                $subtotal = $qty * $unitCost;

                $productId = $itemData['product_id'] ?? null;
                $sparePartId = $itemData['spare_part_id'] ?? null;
                $phoneDeviceId = null;

                if ($itemData['item_type'] === 'product' && $productId) {
                    $prod = Product::find($productId);
                    if ($prod) {
                        $prod->increment('stock_quantity', $qty);
                        StockMovement::create([
                            'shop_id' => $shopId,
                            'product_id' => $prod->id,
                            'type' => 'purchase',
                            'quantity' => $qty,
                            'reference_type' => 'Purchase',
                            'reference_id' => $purchase->id,
                            'notes' => "Purchased via {$purchase->purchase_number}",
                        ]);
                    }
                } elseif ($itemData['item_type'] === 'spare_part' && $sparePartId) {
                    $part = SparePart::find($sparePartId);
                    if ($part) {
                        $part->increment('stock_quantity', $qty);
                    }
                } elseif ($itemData['item_type'] === 'phone' && !empty($itemData['imei_1'])) {
                    $phone = PhoneDevice::create([
                        'shop_id' => $shopId,
                        'supplier_id' => $supplier->id,
                        'brand' => $itemData['brand'] ?? 'Smartphone',
                        'model' => $itemData['model'] ?? $itemData['item_name'],
                        'imei_1' => $itemData['imei_1'],
                        'imei_2' => $itemData['imei_2'] ?? null,
                        'purchase_cost' => $unitCost,
                        'selling_price' => (float) ($itemData['selling_price'] ?? ($unitCost * 1.15)),
                        'status' => 'in_stock',
                    ]);
                    $phoneDeviceId = $phone->id;

                    StockMovement::create([
                        'shop_id' => $shopId,
                        'phone_device_id' => $phone->id,
                        'type' => 'purchase',
                        'quantity' => 1,
                        'reference_type' => 'Purchase',
                        'reference_id' => $purchase->id,
                        'notes' => "Purchased phone {$phone->brand} {$phone->model} (IMEI: {$phone->imei_1})",
                    ]);
                }

                PurchaseItem::create([
                    'shop_id' => $shopId,
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'spare_part_id' => $sparePartId,
                    'phone_device_id' => $phoneDeviceId,
                    'item_name' => $itemData['item_name'],
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);
            }

            // Update supplier balance by uncollected difference
            $unpaidDiff = max(0, $totalAmount - $paidAmount);
            if ($unpaidDiff > 0) {
                $supplier->increment('balance', $unpaidDiff);
            }
        });

        return redirect()->route('shop.purchases')->with('success', 'Purchase record created and inventory stock updated successfully.');
    }
}

