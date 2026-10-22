<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Sale;
use App\Services\Sales\PosCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosViewController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::all();
        $products = Product::where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->latest()
            ->limit(50)
            ->get();

        $phones = PhoneDevice::where('status', 'in_stock')
            ->latest()
            ->limit(50)
            ->get();

        $customers = Customer::latest()->limit(100)->get();

        $recentSales = Sale::with(['customer', 'cashier'])
            ->latest()
            ->limit(10)
            ->get();

        return view('shop.pos.index', compact('categories', 'products', 'phones', 'customers', 'recentSales'));
    }

    public function checkout(Request $request, PosCheckoutService $checkoutService): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.phone_device_id' => 'nullable|exists:phone_devices,id',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method' => 'required|string|in:cash,card,bank_transfer',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.reference' => 'nullable|string|max:100',
        ]);

        foreach ($validated['items'] as &$item) {
            if (empty($item['item_name'])) {
                if (! empty($item['product_id'])) {
                    $item['item_name'] = Product::find($item['product_id'])?->name ?? 'Product Item';
                } elseif (! empty($item['phone_device_id'])) {
                    $device = PhoneDevice::find($item['phone_device_id']);
                    $item['item_name'] = $device ? ($device->brand.' '.$device->model) : 'Phone Device';
                } else {
                    $item['item_name'] = 'General Item';
                }
            }
        }
        unset($item);

        try {
            $sale = $checkoutService->checkout($validated, (int) auth()->id());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'sale' => $sale->load(['items', 'payments', 'customer']),
                    'message' => 'Sale completed successfully! Invoice #'.$sale->invoice_number,
                ], 201);
            }

            return redirect()->route('shop.pos')->with('success', 'Sale completed successfully! Invoice #'.$sale->invoice_number);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
