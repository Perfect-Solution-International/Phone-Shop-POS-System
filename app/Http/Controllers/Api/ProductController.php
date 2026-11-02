<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products.view')->only(['index', 'show']);
        $this->middleware('can:products.manage')->except(['index', 'show']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'brand']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'alert_quantity');
        }

        $products = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $shopId = (int) auth()->user()?->shop_id;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100',
            'type' => 'required|string|in:standard,phone_device,spare_part,service',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('shop_id', $shopId)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('shop_id', $shopId)],
            'cost_price' => 'nullable|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json($product->load(['category', 'brand']), 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load(['category', 'brand', 'phoneDevices']));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $shopId = (int) auth()->user()?->shop_id;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100',
            'type' => 'required|string|in:standard,phone_device,spare_part,service',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('shop_id', $shopId)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('shop_id', $shopId)],
            'cost_price' => 'nullable|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json($product->load(['category', 'brand']));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}

