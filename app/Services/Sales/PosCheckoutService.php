<?php

namespace App\Services\Sales;

use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PosCheckoutService
{
    /**
     * Process an atomic POS checkout transaction with IMEI verification and stock deduplication.
     *
     * @param array{
     *     customer_id?: int|null,
     *     items: array<int, array{
     *         product_id?: int|null,
     *         phone_device_id?: int|null,
     *         item_name: string,
     *         quantity: int,
     *         unit_price: float,
     *         discount?: float
     *     }>,
     *     payments: array<int, array{
     *         payment_method: string,
     *         amount: float,
     *         reference?: string|null
     *     }>,
     *     discount_amount?: float,
     *     tax_amount?: float,
     *     notes?: string|null
     * } $payload
     */
    public function checkout(array $payload, int $cashierId): Sale
    {
        return DB::transaction(function () use ($payload, $cashierId): Sale {
            $itemsData = $payload['items'] ?? [];
            if (empty($itemsData)) {
                throw new InvalidArgumentException('POS sale requires at least one line item.');
            }

            $subtotal = 0.00;
            $saleItemsToInsert = [];
            $phonesToMarkSold = [];
            $productsToDecrement = [];

            // 1. Validate line items and enforce concurrent IMEI locking
            foreach ($itemsData as $item) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $unitPrice = (float) $item['unit_price'];
                $itemDiscount = (float) ($item['discount'] ?? 0.00);
                $lineSubtotal = ($unitPrice * $qty) - $itemDiscount;
                $subtotal += $lineSubtotal;

                // If item is a serialized phone device (IMEI tracking)
                if (! empty($item['phone_device_id'])) {
                    /** @var PhoneDevice|null $device */
                    $device = PhoneDevice::where('id', $item['phone_device_id'])->lockForUpdate()->first();

                    if (! $device) {
                        throw new InvalidArgumentException("Device ID #{$item['phone_device_id']} does not exist in inventory.");
                    }

                    if ($device->status !== 'in_stock') {
                        throw new InvalidArgumentException("Device with IMEI {$device->imei_1} ({$device->model}) is already marked as '{$device->status}' and cannot be sold.");
                    }

                    $phonesToMarkSold[] = $device;
                } elseif (! empty($item['product_id'])) {
                    // Standard product quantity check
                    /** @var Product|null $product */
                    $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                    if ($product) {
                        $productsToDecrement[] = ['product' => $product, 'qty' => $qty];
                    }
                }

                $saleItemsToInsert[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'phone_device_id' => $item['phone_device_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount' => $itemDiscount,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discountAmount = (float) ($payload['discount_amount'] ?? 0.00);
            $taxAmount = (float) ($payload['tax_amount'] ?? 0.00);
            $totalAmount = max(0.00, $subtotal - $discountAmount + $taxAmount);

            // 2. Validate payments (Cash, Card, Bank, Split, or Credit)
            $paymentsData = $payload['payments'] ?? [];
            $totalPaid = 0.00;
            $primaryMethod = count($paymentsData) > 1 ? 'split' : ($paymentsData[0]['payment_method'] ?? 'cash');

            foreach ($paymentsData as $p) {
                $totalPaid += (float) ($p['amount'] ?? 0.00);
            }

            // Determine sale payment status
            $status = 'completed';
            if ($totalPaid < $totalAmount) {
                $status = $totalPaid > 0 ? 'partial' : 'credit';
            }

            // 3. Generate sequential invoice number
            $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(5));

            // 4. Create Sale Record
            $sale = Sale::create([
                'customer_id' => $payload['customer_id'] ?? null,
                'user_id' => $cashierId,
                'invoice_number' => $invoiceNumber,
                'sale_date' => now()->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalPaid,
                'payment_method' => $primaryMethod,
                'status' => $status,
                'notes' => $payload['notes'] ?? null,
            ]);

            // 5. Insert Sale Items and record Stock Movements
            foreach ($saleItemsToInsert as $line) {
                $line['sale_id'] = $sale->id;
                SaleItem::create($line);

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'phone_device_id' => $line['phone_device_id'],
                    'type' => 'sale',
                    'quantity' => -$line['quantity'],
                    'reference_type' => 'Sale',
                    'reference_id' => $sale->id,
                    'notes' => "Sold via Invoice {$invoiceNumber}",
                ]);
            }

            // 6. Record Split Payments
            foreach ($paymentsData as $pay) {
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $pay['payment_method'],
                    'amount' => (float) $pay['amount'],
                    'reference' => $pay['reference'] ?? null,
                ]);
            }

            // 7. Update Phone Devices to 'sold'
            foreach ($phonesToMarkSold as $phone) {
                $phone->markAsSold($sale->id);
            }

            // 8. Decrement standard product stocks
            foreach ($productsToDecrement as $entry) {
                $entry['product']->decrement('stock_quantity', $entry['qty']);
            }

            // 9. Update customer balance if credit sale
            if (! empty($payload['customer_id']) && $totalPaid < $totalAmount) {
                $due = $totalAmount - $totalPaid;
                Customer::where('id', $payload['customer_id'])->increment('balance', $due);
            }

            return $sale->load(['items.phoneDevice', 'items.product', 'payments', 'customer', 'cashier']);
        });
    }
}

