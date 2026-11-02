<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Grn;
use App\Models\Product;
use App\Models\SparePart;
use App\Models\Supplier;
use App\Services\Inventory\GoodsReceivingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GrnViewController extends Controller
{
    public function __construct(
        protected GoodsReceivingService $receivingService
    ) {}

    public function index(Request $request): View
    {
        $query = Grn::with(['supplier', 'receivedBy', 'items']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('grn_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_ref', 'like', "%{$search}%")
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
            $query->whereDate('received_date', $date);
        }

        $grns = $query->latest()->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $spareParts = SparePart::orderBy('name')->get();

        $totalGrnCount = Grn::count();
        $totalReceivedValue = (float) Grn::sum('total_cost');

        return view('shop.inventory.grns.index', compact(
            'grns',
            'suppliers',
            'products',
            'spareParts',
            'totalGrnCount',
            'totalReceivedValue'
        ));
    }

    public function show(Grn $grn): View
    {
        $grn->load(['supplier', 'receivedBy', 'items.product', 'items.sparePart', 'items.phoneDevice']);

        return view('shop.inventory.grns.show', compact('grn'));
    }

    public function store(Request $request): RedirectResponse
    {
        $shopId = (int) auth()->user()?->shop_id;
        $validated = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('shop_id', $shopId)],
            'supplier_invoice_ref' => ['nullable', 'string', 'max:100'],
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:product,spare_part,phone'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('shop_id', $shopId)],
            'items.*.spare_part_id' => ['nullable', Rule::exists('spare_parts', 'id')->where('shop_id', $shopId)],
            'items.*.brand' => ['nullable', 'string', 'max:255'],
            'items.*.model' => ['nullable', 'string', 'max:255'],
            'items.*.imei_1' => ['nullable', 'string', 'max:50'],
            'items.*.imei_2' => ['nullable', 'string', 'max:50'],
            'items.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
        ]);

        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $userId = auth()->id();

        $grn = $this->receivingService->receiveGoods($validated, $shopId, $userId);

        return redirect()->route('shop.inventory.grns.show', $grn)
            ->with('success', "Goods Received Note #{$grn->grn_number} created and stock balances updated successfully!");
    }
}

