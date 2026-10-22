<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'brand']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::all();
        $brands = Brand::all();

        return view('shop.products.index', compact('products', 'categories', 'brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'barcode' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['barcode'])) {
            $validated['barcode'] = '890'.mt_rand(100000000, 999999999);
        }

        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-'.strtoupper(Str::random(6));
        }

        $validated['code'] = $validated['barcode'];
        $validated['retail_price'] = $validated['selling_price'];
        $validated['is_active'] = true;

        Product::create($validated);

        return redirect()->route('shop.products')->with('success', 'Product registered to inventory successfully!');
    }
}
