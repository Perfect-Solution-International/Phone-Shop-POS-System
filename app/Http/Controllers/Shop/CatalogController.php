<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    /**
     * Store a new category for the current shop workspace.
     */
    public function storeCategory(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = 'cat-' . mt_rand(1000, 9999);
        }

        // Check if category with this name already exists for current tenant
        $category = Category::where('name', $name)->orWhere('slug', $slug)->first();

        if (! $category) {
            $category = Category::create([
                'name' => $name,
                'slug' => $slug,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Category '{$category->name}' added successfully!",
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
            ], 201);
        }

        return redirect()->back()->with('success', "Category '{$category->name}' added successfully!");
    }

    /**
     * Store a new brand for the current shop workspace.
     */
    public function storeBrand(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = 'brand-' . mt_rand(1000, 9999);
        }

        // Check if brand with this name already exists for current tenant
        $brand = Brand::where('name', $name)->orWhere('slug', $slug)->first();

        if (! $brand) {
            $brand = Brand::create([
                'name' => $name,
                'slug' => $slug,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Brand '{$brand->name}' added successfully!",
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ],
            ], 201);
        }

        return redirect()->back()->with('success', "Brand '{$brand->name}' added successfully!");
    }

    /**
     * Get all categories and brands for the current shop tenant.
     */
    public function getCatalog(): JsonResponse
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        $brands = Brand::withCount('products')->orderBy('name')->get();

        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    /**
     * Initialize standard phone shop categories & brands for this workspace.
     */
    public function seedDefaults(Request $request): JsonResponse|RedirectResponse
    {
        $defaultCategories = [
            'Smartphones',
            'Feature Phones',
            'Chargers & Cables',
            'Earphones & Audio',
            'Cases & Covers',
            'Screen Protectors',
            'Power Banks & Batteries',
            'Spare Parts & Displays',
        ];

        foreach ($defaultCategories as $catName) {
            Category::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName]
            );
        }

        $defaultBrands = [
            'Apple',
            'Samsung',
            'Xiaomi',
            'Vivo',
            'Oppo',
            'Realme',
            'OnePlus',
            'Huawei',
            'Google Pixel',
            'Anker',
        ];

        foreach ($defaultBrands as $brandName) {
            Brand::firstOrCreate(
                ['slug' => Str::slug($brandName)],
                ['name' => $brandName]
            );
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Default catalog categories and brands initialized successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Default categories and brands initialized successfully!');
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = 'cat-' . mt_rand(1000, 9999);
        }

        // Check if another category in the current shop has the same name or slug
        $exists = Category::where('id', '!=', $category->id)
            ->where(function ($q) use ($name, $slug) {
                $q->where('name', $name)->orWhere('slug', $slug);
            })
            ->exists();

        if ($exists) {
            $msg = "A category named '{$name}' already exists in your shop.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $category->update([
            'name' => $name,
            'slug' => $slug,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Category '{$category->name}' updated successfully!",
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
            ]);
        }

        return redirect()->back()->with('success', "Category '{$category->name}' updated successfully!");
    }

    /**
     * Update an existing brand.
     */
    public function updateBrand(Request $request, Brand $brand): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = 'brand-' . mt_rand(1000, 9999);
        }

        // Check if another brand in the current shop has the same name or slug
        $exists = Brand::where('id', '!=', $brand->id)
            ->where(function ($q) use ($name, $slug) {
                $q->where('name', $name)->orWhere('slug', $slug);
            })
            ->exists();

        if ($exists) {
            $msg = "A brand named '{$name}' already exists in your shop.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $brand->update([
            'name' => $name,
            'slug' => $slug,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Brand '{$brand->name}' updated successfully!",
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ],
            ]);
        }

        return redirect()->back()->with('success', "Brand '{$brand->name}' updated successfully!");
    }

    /**
     * Delete a category (safely unassigns associated products).
     */
    public function destroyCategory(Category $category): JsonResponse|RedirectResponse
    {
        $productCount = $category->products()->count();
        if ($productCount > 0) {
            $category->products()->update(['category_id' => null]);
        }

        $categoryName = $category->name;
        $category->delete();

        $msg = $productCount > 0
            ? "Category '{$categoryName}' removed. {$productCount} product(s) marked as uncategorized."
            : "Category '{$categoryName}' removed successfully.";

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Delete a brand (safely unassigns associated products).
     */
    public function destroyBrand(Brand $brand): JsonResponse|RedirectResponse
    {
        $productCount = $brand->products()->count();
        if ($productCount > 0) {
            $brand->products()->update(['brand_id' => null]);
        }

        $brandName = $brand->name;
        $brand->delete();

        $msg = $productCount > 0
            ? "Brand '{$brandName}' removed. {$productCount} product(s) marked without brand."
            : "Brand '{$brandName}' removed successfully.";

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }
}

