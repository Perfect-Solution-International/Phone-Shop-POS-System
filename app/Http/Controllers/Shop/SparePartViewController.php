<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);

        SparePart::create($validated);

        return redirect()->route('shop.spare-parts')->with('success', 'Spare part added to inventory successfully!');
    }
}

