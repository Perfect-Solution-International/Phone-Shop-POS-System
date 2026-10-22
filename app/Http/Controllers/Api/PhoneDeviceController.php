<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhoneDevice;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneDeviceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:imei.view')->only(['index', 'lookup']);
        $this->middleware('can:imei.manage')->except(['index', 'lookup']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = PhoneDevice::with(['product']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('imei_1', 'like', "%{$search}%")
                    ->orWhere('imei_2', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($condition = $request->input('condition')) {
            $query->where('condition', $condition);
        }

        $devices = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json($devices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'storage' => 'nullable|string|max:50',
            'ram' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'condition' => 'required|string|in:new,used,refurbished',
            'imei_1' => 'required|string|max:50',
            'imei_2' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:100',
            'purchase_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check duplicate IMEI in this shop
        $exists = PhoneDevice::where('imei_1', $validated['imei_1'])
            ->orWhere(fn ($q) => $q->whereNotNull('imei_2')->where('imei_2', $validated['imei_1']))
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => "A device with IMEI {$validated['imei_1']} is already registered in this workspace.",
            ], 422);
        }

        $validated['status'] = $validated['status'] ?? 'in_stock';
        $device = PhoneDevice::create($validated);

        StockMovement::create([
            'product_id' => $device->product_id,
            'phone_device_id' => $device->id,
            'type' => 'purchase',
            'quantity' => 1,
            'reference_type' => 'InitialRegistration',
            'reference_id' => $device->id,
            'notes' => "Device registered ({$device->brand} {$device->model}) IMEI: {$device->imei_1}",
        ]);

        return response()->json($device, 201);
    }

    /**
     * Rapid lookup by IMEI 1 or IMEI 2 for barcode/QR scanner integration at POS counter.
     */
    public function lookup(Request $request): JsonResponse
    {
        $imei = trim($request->input('imei', ''));
        if (! $imei) {
            return response()->json(['message' => 'IMEI number is required for lookup.'], 400);
        }

        $device = PhoneDevice::with('product')
            ->where(function ($q) use ($imei): void {
                $q->where('imei_1', $imei)
                    ->orWhere('imei_2', $imei)
                    ->orWhere('serial_number', $imei);
            })
            ->first();

        if (! $device) {
            return response()->json(['message' => "No device found matching IMEI/Serial '{$imei}'."], 404);
        }

        return response()->json($device);
    }
}

