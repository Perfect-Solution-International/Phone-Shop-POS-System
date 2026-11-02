<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Outlet;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockTransferViewController extends Controller
{
    public function index(Request $request): View
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $status = $request->input('status');

        $query = StockTransfer::with(['fromOutlet', 'toOutlet', 'dispatchedByUser', 'receivedByUser', 'items.phoneDevice', 'items.product'])
            ->where('shop_id', $shopId);

        if ($status) {
            $query->where('status', $status);
        }

        $transfers = $query->latest()->paginate(15);

        $outlets = Outlet::where('shop_id', $shopId)->orderBy('name')->get();

        // In-stock phones available for transfer
        $availablePhones = PhoneDevice::where('shop_id', $shopId)
            ->whereIn('status', ['in_stock', 'In Stock'])
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        $availableProducts = Product::where('shop_id', $shopId)
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $inTransitCount = StockTransfer::where('shop_id', $shopId)->where('status', 'in_transit')->count();
        $completedCount = StockTransfer::where('shop_id', $shopId)->where('status', 'received')->count();

        return view('shop.transfers.index', compact(
            'transfers',
            'outlets',
            'availablePhones',
            'availableProducts',
            'inTransitCount',
            'completedCount',
            'status'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');

        $data = $request->validate([
            'from_outlet_id' => ['required', Rule::exists('outlets', 'id')->where('shop_id', $shopId)],
            'to_outlet_id' => ['required', Rule::exists('outlets', 'id')->where('shop_id', $shopId), 'different:from_outlet_id'],
            'item_type' => ['required', 'in:phone,product'],
            'phone_device_id' => ['nullable', 'required_if:item_type,phone', Rule::exists('phone_devices', 'id')->where('shop_id', $shopId)],
            'product_id' => ['nullable', 'required_if:item_type,product', Rule::exists('products', 'id')->where('shop_id', $shopId)],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $transferNumber = 'TRF-' . strtoupper(substr(uniqid(), -5));
        $qty = $data['item_type'] === 'phone' ? 1 : ($data['quantity'] ?? 1);

        DB::transaction(function () use ($shopId, $data, $transferNumber, $qty) {
            $transfer = StockTransfer::create([
                'shop_id' => $shopId,
                'transfer_number' => $transferNumber,
                'from_outlet_id' => $data['from_outlet_id'],
                'to_outlet_id' => $data['to_outlet_id'],
                'dispatched_by' => auth()->id(),
                'status' => 'in_transit',
                'notes' => $data['notes'] ?? null,
                'dispatched_at' => now(),
            ]);

            $itemName = '';
            if ($data['item_type'] === 'phone') {
                $phone = PhoneDevice::findOrFail($data['phone_device_id']);
                $itemName = "{$phone->brand} {$phone->model} (IMEI: {$phone->imei_1})";
                $phone->update([
                    'status' => 'In Transit',
                ]);
            } else {
                $product = Product::findOrFail($data['product_id']);
                $itemName = $product->name;
                $product->decrement('stock_quantity', $qty);

                InventoryMovement::create([
                    'shop_id' => $shopId,
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'transfer_out',
                    'quantity' => -$qty,
                    'reference' => $transferNumber,
                    'notes' => "Dispatched for branch transfer to Outlet #{$data['to_outlet_id']}",
                ]);
            }

            StockTransferItem::create([
                'shop_id' => $shopId,
                'stock_transfer_id' => $transfer->id,
                'item_type' => $data['item_type'],
                'phone_device_id' => $data['item_type'] === 'phone' ? $data['phone_device_id'] : null,
                'product_id' => $data['item_type'] === 'product' ? $data['product_id'] : null,
                'item_name' => $itemName,
                'quantity' => $qty,
            ]);
        });

        return redirect()->route('shop.transfers.index')
            ->with('success', "Stock Transfer {$transferNumber} created and marked In Transit.");
    }

    public function receive(StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->status !== 'in_transit') {
            return redirect()->route('shop.transfers.index')->with('error', 'Only in-transit transfers can be confirmed.');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                if ($item->item_type === 'phone' && $item->phoneDevice) {
                    $item->phoneDevice->update([
                        'outlet_id' => $transfer->to_outlet_id,
                        'status' => 'In Stock',
                    ]);
                } elseif ($item->item_type === 'product' && $item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);

                    InventoryMovement::create([
                        'shop_id' => $transfer->shop_id,
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'type' => 'transfer_in',
                        'quantity' => $item->quantity,
                        'reference' => $transfer->transfer_number,
                        'notes' => "Received at Destination Outlet #{$transfer->to_outlet_id}",
                    ]);
                }
            }

            $transfer->update([
                'status' => 'received',
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);
        });

        return redirect()->route('shop.transfers.index')
            ->with('success', "Stock Transfer {$transfer->transfer_number} received and added to destination branch stock.");
    }

    public function cancel(StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->status !== 'in_transit') {
            return redirect()->route('shop.transfers.index')->with('error', 'Only in-transit transfers can be cancelled.');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                if ($item->item_type === 'phone' && $item->phoneDevice) {
                    $item->phoneDevice->update([
                        'status' => 'In Stock',
                    ]);
                } elseif ($item->item_type === 'product' && $item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            $transfer->update([
                'status' => 'cancelled',
            ]);
        });

        return redirect()->route('shop.transfers.index')
            ->with('success', "Stock Transfer {$transfer->transfer_number} cancelled and items reverted to source outlet.");
    }
}
