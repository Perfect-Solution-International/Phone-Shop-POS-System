<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\PhoneDevice;
use App\Models\PhoneTradeIn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TradeInViewController extends Controller
{
    public function index(Request $request): View
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $search = $request->input('search');
        $status = $request->input('status');

        $query = PhoneTradeIn::with(['phoneDevice', 'outlet', 'customer'])
            ->where('shop_id', $shopId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_nic', 'like', "%{$search}%")
                    ->orWhere('imei_1', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $tradeIns = $query->latest()->paginate(15);

        $totalValuation = PhoneTradeIn::where('shop_id', $shopId)->sum('valuation_amount');
        $totalDevices = PhoneTradeIn::where('shop_id', $shopId)->count();
        $inStockUsed = PhoneDevice::where('shop_id', $shopId)->where('condition', 'Used')->where('status', 'In Stock')->count();

        $outlets = Outlet::where('shop_id', $shopId)->orderBy('name')->get();
        $customers = Customer::where('shop_id', $shopId)->orderBy('name')->get();

        return view('shop.trade-ins.index', compact(
            'tradeIns',
            'totalValuation',
            'totalDevices',
            'inStockUsed',
            'outlets',
            'customers',
            'search',
            'status'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $outletId = $request->input('outlet_id') ?: (session('active_outlet_id') ?: Outlet::where('shop_id', $shopId)->value('id'));

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_nic' => ['nullable', 'string', 'max:50'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'storage' => ['nullable', 'string', 'max:50'],
            'ram' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'imei_1' => ['required', 'string', 'max:50'],
            'imei_2' => ['nullable', 'string', 'max:50'],
            'valuation_amount' => ['required', 'numeric', 'min:0'],
            'resale_price' => ['nullable', 'numeric', 'min:0'],
            'payout_type' => ['required', 'in:cash,trade_in_credit,bank_transfer'],
            'screen_condition' => ['nullable', 'string'],
            'battery_health' => ['nullable', 'string'],
            'cameras_working' => ['nullable', 'boolean'],
            'biometrics_working' => ['nullable', 'boolean'],
            'icloud_unlocked' => ['nullable', 'boolean'],
            'body_condition' => ['nullable', 'string'],
            'accessories_included' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // Check if IMEI 1 already in active stock
        $imeiExists = PhoneDevice::where('shop_id', $shopId)
            ->where(function ($q) use ($data) {
                $q->where('imei_1', $data['imei_1'])
                    ->orWhere('imei_2', $data['imei_1']);
            })
            ->where('status', '!=', 'Archived')
            ->exists();

        if ($imeiExists) {
            return back()->withInput()->with('error', "Device with IMEI {$data['imei_1']} is already registered in stock.");
        }

        $conditionChecklist = [
            'screen' => $data['screen_condition'] ?? 'Good / Minor scratches',
            'battery_health' => $data['battery_health'] ?? '85%',
            'cameras' => ! empty($data['cameras_working']) ? 'Working' : 'Faulty',
            'biometrics' => ! empty($data['biometrics_working']) ? 'Functional' : 'Defective',
            'account_lock' => ! empty($data['icloud_unlocked']) ? 'Unlocked / Removed' : 'Locked',
            'body' => $data['body_condition'] ?? 'Normal wear',
            'accessories' => $data['accessories_included'] ?? 'Device only',
        ];

        DB::transaction(function () use ($shopId, $outletId, $data, $conditionChecklist) {
            // 1. Create Used Phone Device in Inventory
            $resalePrice = ! empty($data['resale_price']) ? $data['resale_price'] : ($data['valuation_amount'] * 1.25);

            $phoneDevice = PhoneDevice::create([
                'shop_id' => $shopId,
                'outlet_id' => $outletId,
                'brand' => $data['brand'],
                'model' => $data['model'],
                'storage' => $data['storage'] ?? null,
                'ram' => $data['ram'] ?? null,
                'color' => $data['color'] ?? 'Standard',
                'imei_1' => $data['imei_1'],
                'imei_2' => $data['imei_2'] ?? null,
                'condition' => 'Used',
                'status' => 'In Stock',
                'purchase_cost' => $data['valuation_amount'],
                'selling_price' => $resalePrice,
                'notes' => "Used Phone Trade-In from {$data['customer_name']} (NIC: " . ($data['customer_nic'] ?? 'N/A') . ")",
            ]);

            // 2. Create Trade-In Record
            PhoneTradeIn::create([
                'shop_id' => $shopId,
                'outlet_id' => $outletId,
                'customer_id' => $data['customer_id'] ?? null,
                'phone_device_id' => $phoneDevice->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_nic' => $data['customer_nic'] ?? null,
                'brand' => $data['brand'],
                'model' => $data['model'],
                'storage' => $data['storage'] ?? null,
                'ram' => $data['ram'] ?? null,
                'color' => $data['color'] ?? null,
                'imei_1' => $data['imei_1'],
                'imei_2' => $data['imei_2'] ?? null,
                'condition_checklist' => $conditionChecklist,
                'valuation_amount' => $data['valuation_amount'],
                'payout_type' => $data['payout_type'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('shop.trade-ins.index')
            ->with('success', "Used phone '{$data['brand']} {$data['model']}' evaluated at LKR " . number_format($data['valuation_amount'], 2) . " and registered directly into Used Phone stock.");
    }
}
