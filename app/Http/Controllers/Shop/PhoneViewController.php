<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\PhoneDevice;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PhoneViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = PhoneDevice::with(['supplier', 'sale.customer']);

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

        $inStockCount = PhoneDevice::where('status', 'in_stock')->count();
        $soldCount = PhoneDevice::where('status', 'sold')->count();
        $totalCount = PhoneDevice::count();

        return view('shop.phones.index', compact('phones', 'brands', 'suppliers', 'inStockCount', 'soldCount', 'totalCount'));
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
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')->where('shop_id', auth()->user()->shop_id)],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'notes' => 'nullable|string|max:500',
            'quantity' => 'nullable|integer|min:1|max:500',
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        unset($validated['quantity']);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('phones', 'public');
        }

        $validated['status'] = 'in_stock';
        $shopId = (int) auth()->user()->shop_id;

        // Register first device
        PhoneDevice::create($validated);

        // If quantity > 1, register remaining batch units with unique IMEIs
        $baseImei1 = $validated['imei_1'];
        $baseImei2 = $validated['imei_2'] ?? null;
        $baseSerial = $validated['serial_number'] ?? null;

        for ($i = 2; $i <= $quantity; $i++) {
            $extra = $validated;

            if (is_numeric($baseImei1) && strlen($baseImei1) >= 8) {
                $candidateImei1 = bcadd($baseImei1, (string) ($i - 1));
            } else {
                $candidateImei1 = $baseImei1 . '-' . $i;
            }

            $suffix = 1;
            while (PhoneDevice::where('shop_id', $shopId)->where('imei_1', $candidateImei1)->exists()) {
                $candidateImei1 = $baseImei1 . '-B' . $i . ($suffix > 1 ? "-{$suffix}" : '');
                $suffix++;
            }
            $extra['imei_1'] = $candidateImei1;

            if (! empty($baseImei2)) {
                if (is_numeric($baseImei2) && strlen($baseImei2) >= 8) {
                    $candidateImei2 = bcadd($baseImei2, (string) ($i - 1));
                } else {
                    $candidateImei2 = $baseImei2 . '-' . $i;
                }
                $suffix2 = 1;
                while (PhoneDevice::where('shop_id', $shopId)->where('imei_2', $candidateImei2)->exists()) {
                    $candidateImei2 = $baseImei2 . '-B' . $i . ($suffix2 > 1 ? "-{$suffix2}" : '');
                    $suffix2++;
                }
                $extra['imei_2'] = $candidateImei2;
            } else {
                $extra['imei_2'] = null;
            }

            if (! empty($baseSerial)) {
                $extra['serial_number'] = $baseSerial . '-' . $i;
            }

            PhoneDevice::create($extra);
        }

        $message = $quantity > 1
            ? "{$quantity} units of {$validated['brand']} {$validated['model']} registered to stock successfully!"
            : 'Phone device registered successfully with Dual IMEI!';

        return redirect()->route('shop.phones')->with('success', $message);
    }

    public function update(Request $request, PhoneDevice $phone): RedirectResponse
    {
        $shopId = auth()->user()->shop_id;

        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'storage' => 'nullable|string|max:50',
            'ram' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'condition' => 'required|string|in:new,used,refurbished',
            'status' => 'required|string|in:in_stock,reserved,sold,defective,returned,in_transfer',
            'imei_1' => 'required|string|max:50|unique:phone_devices,imei_1,' . $phone->id . ',id,shop_id,' . $shopId,
            'imei_2' => 'nullable|string|max:50|unique:phone_devices,imei_2,' . $phone->id . ',id,shop_id,' . $shopId,
            'serial_number' => 'nullable|string|max:100',
            'purchase_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')->where('shop_id', auth()->user()->shop_id)],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'remove_image' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($request->boolean('remove_image')) {
            if ($phone->image_path && Storage::disk('public')->exists($phone->image_path)) {
                Storage::disk('public')->delete($phone->image_path);
            }
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($phone->image_path && Storage::disk('public')->exists($phone->image_path)) {
                Storage::disk('public')->delete($phone->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('phones', 'public');
        }

        if ($validated['status'] === 'sold' && ! $phone->sold_at) {
            $validated['sold_at'] = now();
        } elseif ($validated['status'] !== 'sold' && $phone->status === 'sold') {
            $validated['sold_at'] = null;
            $validated['sale_id'] = null;
        }

        $phone->update($validated);

        return redirect()->route('shop.phones')->with('success', "Device {$phone->brand} {$phone->model} updated successfully!");
    }

    public function destroy(PhoneDevice $phone): RedirectResponse
    {
        if ($phone->isSold() || $phone->sale_id) {
            return back()->with('error', 'Cannot delete a phone device that has already been sold. Mark it as returned or defective instead.');
        }

        if (\App\Models\Warranty::where('phone_device_id', $phone->id)->exists()) {
            return back()->with('error', 'Cannot delete this device because it has warranty certificates issued.');
        }

        if ($phone->image_path && Storage::disk('public')->exists($phone->image_path)) {
            Storage::disk('public')->delete($phone->image_path);
        }

        $deviceName = "{$phone->brand} {$phone->model} (IMEI: {$phone->imei_1})";
        $phone->delete();

        return redirect()->route('shop.phones')->with('success', "Device {$deviceName} removed from inventory successfully.");
    }

    public function deleteImage(PhoneDevice $phone): JsonResponse|RedirectResponse
    {
        if ($phone->image_path && Storage::disk('public')->exists($phone->image_path)) {
            Storage::disk('public')->delete($phone->image_path);
        }

        $phone->update(['image_path' => null]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Phone image removed successfully.']);
        }

        return redirect()->back()->with('success', 'Phone image removed successfully.');
    }
}

