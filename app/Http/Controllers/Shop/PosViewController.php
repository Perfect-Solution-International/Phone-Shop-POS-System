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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosViewController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::all();
        $products = Product::where('is_active', true)
            ->with('category')
            ->latest()
            ->limit(100)
            ->get();

        $activeOutletId = session('active_outlet_id', $request->user()->outlet_id);

        $phonesQuery = PhoneDevice::with(['sale.customer']);
        if ($activeOutletId) {
            $phonesQuery->where(function ($q) use ($activeOutletId) {
                $q->where('outlet_id', $activeOutletId)
                    ->orWhereNull('outlet_id');
            });
        }
        $phones = $phonesQuery->latest()->limit(100)->get();

        $customers = Customer::latest()->limit(100)->get();

        $recentSales = Sale::with(['customer', 'cashier', 'items.phoneDevice', 'items.product', 'payments'])
            ->latest()
            ->limit(30)
            ->get();

        return view('shop.pos.index', compact('categories', 'products', 'phones', 'customers', 'recentSales'));
    }

    public function checkout(Request $request, PosCheckoutService $checkoutService): RedirectResponse|JsonResponse
    {
        $shopId = (int) auth()->user()?->shop_id;
        $validated = $request->validate([
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('shop_id', $shopId)],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_nic' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('shop_id', $shopId)],
            'items.*.phone_device_id' => ['nullable', Rule::exists('phone_devices', 'id')->where('shop_id', $shopId)],
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method' => 'required|string|in:cash,card,bank_transfer',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.reference' => 'nullable|string|max:100',
            'bank_slip' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
            'card_slip' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ]);

        if ($request->hasFile('bank_slip')) {
            $validated['bank_slip_path'] = $request->file('bank_slip')->store('bank_slips', 'public');
        }
        if ($request->hasFile('card_slip')) {
            $validated['card_slip_path'] = $request->file('card_slip')->store('card_slips', 'public');
        }

        $validated['outlet_id'] = $request->input('outlet_id') ?: (session('active_outlet_id') ?: auth()->user()?->outlet_id);

        if (empty($validated['customer_id']) && ! empty($request->input('customer_name'))) {
            $custPhone = trim($request->input('customer_phone') ?: '0000000000');
            $customer = Customer::where('shop_id', $shopId)->where('phone', $custPhone)->first();
            if (! $customer) {
                $customer = Customer::create([
                    'shop_id' => $shopId,
                    'name' => trim($request->input('customer_name')),
                    'phone' => $custPhone,
                    'nic' => $request->input('customer_nic'),
                    'address' => $request->input('customer_address'),
                ]);
            } else {
                $customer->update([
                    'name' => trim($request->input('customer_name')),
                    'nic' => $request->input('customer_nic') ?: $customer->nic,
                    'address' => $request->input('customer_address') ?: $customer->address,
                ]);
            }
            $validated['customer_id'] = $customer->id;
        }

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
                    'sale' => $sale->load(['items.phoneDevice', 'items.product', 'payments', 'customer', 'cashier']),
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
