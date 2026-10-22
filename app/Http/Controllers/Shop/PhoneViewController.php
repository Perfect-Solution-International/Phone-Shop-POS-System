<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\PhoneDevice;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = PhoneDevice::with(['supplier']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('imei_1', 'like', "%{$search}%")
                    ->orWhere('imei_2', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($condition = $request->input('condition')) {
            $query->where('condition', $condition);
        }

        $phones = $query->latest()->paginate(20)->withQueryString();
        $brands = Brand::all();
        $suppliers = Supplier::all();

        return view('shop.phones.index', compact('phones', 'brands', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'storage' => 'nullable|string|max:50',
            'ram' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'condition' => 'required|string|in:new,used,refurbished',
            'imei_1' => 'required|string|max:50|unique:phone_devices,imei_1,NULL,id,shop_id,'.auth()->user()->shop_id,
            'imei_2' => 'nullable|string|max:50|unique:phone_devices,imei_2,NULL,id,shop_id,'.auth()->user()->shop_id,
            'serial_number' => 'nullable|string|max:100',
            'purchase_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['status'] = 'in_stock';
        PhoneDevice::create($validated);

        return redirect()->route('shop.phones')->with('success', 'Phone device registered successfully with Dual IMEI!');
    }
}

