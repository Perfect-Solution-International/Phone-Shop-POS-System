<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SparePartViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = SparePart::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('compatible_models', 'like', "%{$search}%");
            });
        }

        if ($quality = $request->input('quality_type')) {
            $query->where('quality_type', $quality);
        }

        $parts = $query->latest()->paginate(20)->withQueryString();

        return view('shop.spare-parts.index', compact('parts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'part_number' => 'required|string|max:100',
            'compatible_models' => 'nullable|string|max:500',
            'quality_type' => 'required|string|in:original,oem,compatible',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('spare_parts', 'public');
        }

        SparePart::create($validated);

        return redirect()->route('shop.spare-parts')->with('success', 'Spare part added to inventory successfully!');
    }

    public function update(Request $request, SparePart $sparePart): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'part_number' => 'required|string|max:100',
            'compatible_models' => 'nullable|string|max:500',
            'quality_type' => 'required|string|in:original,oem,compatible',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'remove_image' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_image')) {
            if ($sparePart->image_path && Storage::disk('public')->exists($sparePart->image_path)) {
                Storage::disk('public')->delete($sparePart->image_path);
            }
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($sparePart->image_path && Storage::disk('public')->exists($sparePart->image_path)) {
                Storage::disk('public')->delete($sparePart->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('spare_parts', 'public');
        }

        $sparePart->update($validated);

        return redirect()->route('shop.spare-parts')->with('success', "Spare part '{$sparePart->name}' updated successfully!");
    }

    public function destroy(SparePart $sparePart): RedirectResponse
    {
        if ($sparePart->image_path && Storage::disk('public')->exists($sparePart->image_path)) {
            Storage::disk('public')->delete($sparePart->image_path);
        }

        $partName = $sparePart->name;
        $sparePart->delete();

        return redirect()->route('shop.spare-parts')->with('success', "Spare part '{$partName}' deleted from inventory.");
    }

    public function deleteImage(SparePart $sparePart): JsonResponse|RedirectResponse
    {
        if ($sparePart->image_path && Storage::disk('public')->exists($sparePart->image_path)) {
            Storage::disk('public')->delete($sparePart->image_path);
        }

        $sparePart->update(['image_path' => null]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Spare part image removed successfully.']);
        }

        return redirect()->back()->with('success', 'Spare part image removed successfully.');
    }
}

