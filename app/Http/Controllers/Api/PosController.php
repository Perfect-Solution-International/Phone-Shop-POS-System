<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\Sales\PosCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PosController extends Controller
{
    public function __construct(
        protected PosCheckoutService $checkoutService
    ) {
        $this->middleware('can:pos.sell')->only(['checkout']);
        $this->middleware('can:pos.view')->only(['invoices', 'invoice']);
    }

    public function checkout(Request $request): JsonResponse
    {
        $shopId = (int) auth()->user()?->shop_id;
        $validated = $request->validate([
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('shop_id', $shopId)],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('shop_id', $shopId)],
            'items.*.phone_device_id' => ['nullable', Rule::exists('phone_devices', 'id')->where('shop_id', $shopId)],
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method' => 'required|string|in:cash,card,bank_transfer,store_credit',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $sale = $this->checkoutService->checkout($validated, (int) auth()->id());

            return response()->json([
                'message' => 'Sale completed successfully.',
                'sale' => $sale,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function invoices(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'cashier']);

        if ($search = $request->input('search')) {
            $query->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
        }

        if ($date = $request->input('date')) {
            $query->whereDate('sale_date', $date);
        }

        $sales = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json($sales);
    }

    public function invoice(Sale $sale): JsonResponse
    {
        return response()->json($sale->load(['items.phoneDevice', 'items.product', 'payments', 'customer', 'cashier']));
    }
}

