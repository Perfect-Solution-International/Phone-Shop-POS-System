<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'brand']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::withCount('products')->orderBy('name')->get();
        $brands = Brand::withCount('products')->orderBy('name')->get();

        return view('shop.products.index', compact('products', 'categories', 'brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $shopId = (int) auth()->user()?->shop_id;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('shop_id', $shopId)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('shop_id', $shopId)],
            'barcode' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
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

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('shop.products')->with('success', 'Product registered to inventory successfully!');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $shopId = (int) auth()->user()?->shop_id;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('shop_id', $shopId)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('shop_id', $shopId)],
            'barcode' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'remove_image' => 'nullable|boolean',
        ]);

        if (! empty($validated['barcode'])) {
            $validated['code'] = $validated['barcode'];
        }
        $validated['retail_price'] = $validated['selling_price'];

        // Handle image removal
        if ($request->boolean('remove_image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('shop.products')->with('success', "Product '{$product->name}' updated successfully!");
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $productName = $product->name;
        $product->delete();

        return redirect()->route('shop.products')->with('success', "Product '{$productName}' removed from inventory.");
    }

    public function deleteImage(Product $product): JsonResponse|RedirectResponse
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->update(['image_path' => null]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Product image removed successfully.']);
        }

        return redirect()->back()->with('success', 'Product image removed successfully.');
    }
}
