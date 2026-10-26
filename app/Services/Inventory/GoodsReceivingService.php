<?php

namespace App\Services\Inventory;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoodsReceivingService
{
    /**
     * Process an inbound Goods Received Note with atomic transaction and stock ledger integration.
     *
     * @param array{
     *     supplier_id: int,
     *     purchase_id?: int|null,
     *     received_by_id?: int|null,
     *     supplier_invoice_ref?: string|null,
     *     received_date: string,
     *     notes?: string|null,
     *     items: array<int, array{
     *         item_type: string,
     *         item_name: string,
     *         quantity: int,
     *         unit_cost: float,
     *         product_id?: int|null,
     *         spare_part_id?: int|null,
     *         brand?: string|null,
     *         model?: string|null,
     *         imei_1?: string|null,
     *         imei_2?: string|null,
     *         selling_price?: float|null,
     *         batch_number?: string|null,
     *         notes?: string|null
     *     }>
     * } $data
     * @throws ValidationException
     */
    public function receiveGoods(array $data, int $shopId, ?int $userId = null): Grn
    {
        $supplier = Supplier::where('shop_id', $shopId)->findOrFail($data['supplier_id']);

        // 1. Duplicate Delivery / Invoice Ref Prevention
        if (! empty($data['supplier_invoice_ref'])) {
            $duplicate = Grn::where('shop_id', $shopId)
                ->where('supplier_id', $supplier->id)
                ->where('supplier_invoice_ref', $data['supplier_invoice_ref'])
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'supplier_invoice_ref' => "A Goods Received Note with Supplier Invoice Ref '{$data['supplier_invoice_ref']}' has already been processed for supplier '{$supplier->name}'. Duplicate receiving prevented.",
                ]);
            }
        }

        if (empty($data['items']) || ! is_array($data['items'])) {
            throw ValidationException::withMessages([
                'items' => 'At least one line item is required to record a Goods Received Note.',
            ]);
        }

        return DB::transaction(function () use ($data, $shopId, $supplier, $userId): Grn {
            $grnNumber = 'GRN-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $totalCost = 0.00;
            foreach ($data['items'] as $item) {
                $totalCost += ((int) $item['quantity']) * ((float) $item['unit_cost']);
            }

            $grn = Grn::create([
                'shop_id' => $shopId,
                'supplier_id' => $supplier->id,
                'purchase_id' => $data['purchase_id'] ?? null,
                'received_by_id' => $userId ?? ($data['received_by_id'] ?? null),
                'grn_number' => $grnNumber,
                'supplier_invoice_ref' => $data['supplier_invoice_ref'] ?? null,
                'received_date' => $data['received_date'],
                'status' => 'completed',
                'total_cost' => $totalCost,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $qty = (int) $itemData['quantity'];
                $unitCost = (float) $itemData['unit_cost'];
                $subtotal = $qty * $unitCost;

                $productId = $itemData['product_id'] ?? null;
                $sparePartId = $itemData['spare_part_id'] ?? null;
                $phoneDeviceId = null;

                // 2. Inbound Stock Increment & Stock Movement Ledger
                if ($itemData['item_type'] === 'product' && $productId) {
                    $prod = Product::where('shop_id', $shopId)->findOrFail($productId);
                    $prod->increment('stock_quantity', $qty);

                    StockMovement::create([
                        'shop_id' => $shopId,
                        'product_id' => $prod->id,
                        'type' => 'purchase',
                        'quantity' => $qty,
                        'reference_type' => 'GRN',
                        'reference_id' => $grn->id,
                        'notes' => "Received via {$grn->grn_number}" . ($grn->supplier_invoice_ref ? " (Supplier Ref: {$grn->supplier_invoice_ref})" : ''),
                    ]);
                } elseif ($itemData['item_type'] === 'spare_part' && $sparePartId) {
                    $part = SparePart::where('shop_id', $shopId)->findOrFail($sparePartId);
                    $part->increment('stock_quantity', $qty);
                } elseif ($itemData['item_type'] === 'phone' && ! empty($itemData['imei_1'])) {
                    $phone = PhoneDevice::create([
                        'shop_id' => $shopId,
                        'supplier_id' => $supplier->id,
                        'brand' => $itemData['brand'] ?? 'Smartphone',
                        'model' => $itemData['model'] ?? $itemData['item_name'],
                        'imei_1' => $itemData['imei_1'],
                        'imei_2' => $itemData['imei_2'] ?? null,
                        'purchase_cost' => $unitCost,
                        'selling_price' => (float) ($itemData['selling_price'] ?? ($unitCost * 1.15)),
                        'status' => 'in_stock',
                    ]);
                    $phoneDeviceId = $phone->id;

                    StockMovement::create([
                        'shop_id' => $shopId,
                        'phone_device_id' => $phone->id,
                        'type' => 'purchase',
                        'quantity' => 1,
                        'reference_type' => 'GRN',
                        'reference_id' => $grn->id,
                        'notes' => "Received phone {$phone->brand} {$phone->model} (IMEI: {$phone->imei_1}) via {$grn->grn_number}",
                    ]);
                }

                GrnItem::create([
                    'shop_id' => $shopId,
                    'grn_id' => $grn->id,
                    'product_id' => $productId,
                    'spare_part_id' => $sparePartId,
                    'phone_device_id' => $phoneDeviceId,
                    'item_type' => $itemData['item_type'],
                    'item_name' => $itemData['item_name'],
                    'quantity_received' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            return $grn;
        });
    }
}

