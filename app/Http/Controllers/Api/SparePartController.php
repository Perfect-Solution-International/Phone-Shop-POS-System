<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:parts.use')->only(['index', 'show']);
        $this->middleware('can:stock.adjust')->except(['index', 'show']);
    }

    public function index(Request $request): JsonResponse
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

        $parts = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json($parts);
    }

    public function store(Request $request): JsonResponse
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

        $part = SparePart::create($validated);

        return response()->json($part, 201);
    }

    public function show(SparePart $sparePart): JsonResponse
    {
        return response()->json($sparePart->load('repairPartsUsed.repairJob'));
    }

    public function update(Request $request, SparePart $sparePart): JsonResponse
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

        $sparePart->update($validated);

        return response()->json($sparePart);
    }
}

