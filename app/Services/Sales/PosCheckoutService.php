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

                    $deviceStatus = strtolower(trim(str_replace(' ', '_', (string) $device->status)));
                    if ($deviceStatus !== 'in_stock') {
                        throw new InvalidArgumentException("Device with IMEI {$device->imei_1} ({$device->model}) is already marked as '{$device->status}' and cannot be sold.");
                    }

                    $activeOutlet = $payload['outlet_id'] ?? session('active_outlet_id') ?? auth()->user()?->outlet_id;
                    if ($activeOutlet && $device->outlet_id && (int) $device->outlet_id !== (int) $activeOutlet) {
                        $outletName = $device->outlet?->name ?? "Outlet #{$device->outlet_id}";
                        throw new InvalidArgumentException("Device with IMEI {$device->imei_1} belongs to '{$outletName}' and cannot be sold from this branch.");
                    }

                    $phonesToMarkSold[] = $device;
                } elseif (! empty($item['product_id'])) {
                    // Standard product quantity check
                    /** @var Product|null $product */
                    $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                    if (! $product) {
                        throw new InvalidArgumentException("Product ID #{$item['product_id']} does not exist in inventory.");
                    }

                    if ($product->stock_quantity < $qty) {
                        throw new InvalidArgumentException("Insufficient stock for product '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$qty}.");
                    }

                    $productsToDecrement[] = ['product' => $product, 'qty' => $qty];
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
            $customer = null;
            $effectivePaid = min($totalPaid, $totalAmount);

            if ($totalPaid > $totalAmount) {
                $changeDue = $totalPaid - $totalAmount;
                foreach ($paymentsData as &$p) {
                    if ($p['payment_method'] === 'cash') {
                        $p['amount'] = max(0.00, (float) $p['amount'] - $changeDue);
                        break;
                    }
                }
                unset($p);
            }

            if ($totalPaid < $totalAmount) {
                $status = $totalPaid > 0 ? 'partial' : 'credit';

                if (empty($payload['customer_id'])) {
                    throw new InvalidArgumentException('Credit / Partial payment sale requires a registered customer to record the outstanding balance. Please select or register a customer profile.');
                }

                $customer = Customer::where('id', $payload['customer_id'])->lockForUpdate()->first();
                if (! $customer) {
                    throw new InvalidArgumentException('Selected customer could not be found.');
                }

                $due = $totalAmount - $totalPaid;

                // Enforce customer credit limit if configured
                if ($customer->credit_limit !== null && (float) $customer->credit_limit > 0) {
                    $projectedBalance = (float) $customer->balance + $due;
                    if ($projectedBalance > (float) $customer->credit_limit) {
                        $limitFmt = number_format($customer->credit_limit, 2);
                        $currentFmt = number_format($customer->balance, 2);
                        $dueFmt = number_format($due, 2);
                        $maxAllowed = number_format(max(0, (float) $customer->credit_limit - (float) $customer->balance), 2);
                        throw new InvalidArgumentException("Customer credit limit exceeded! Limit: LKR {$limitFmt}, Current Debt: LKR {$currentFmt}, New Debt: LKR {$dueFmt}. Max allowed new credit: LKR {$maxAllowed}.");
                    }
                }
            }

            // 3. Generate sequential invoice number
            $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(5));

            // 4. Create Sale Record
            $sale = Sale::create([
                'outlet_id' => $payload['outlet_id'] ?? (session('active_outlet_id') ?: auth()->user()?->outlet_id),
                'customer_id' => $payload['customer_id'] ?? null,
                'user_id' => $cashierId,
                'invoice_number' => $invoiceNumber,
                'sale_date' => now()->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $effectivePaid,
                'payment_method' => $primaryMethod,
                'bank_slip_path' => $payload['bank_slip_path'] ?? $payload['card_slip_path'] ?? null,
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
                $slipPath = $pay['slip_path'] ?? null;
                if (! $slipPath) {
                    if ($pay['payment_method'] === 'bank_transfer') {
                        $slipPath = $payload['bank_slip_path'] ?? null;
                    } elseif ($pay['payment_method'] === 'card') {
                        $slipPath = $payload['card_slip_path'] ?? null;
                    }
                }

                SalePayment::create([
                    'shop_id' => $sale->shop_id,
                    'sale_id' => $sale->id,
                    'payment_method' => $pay['payment_method'],
                    'amount' => (float) $pay['amount'],
                    'reference' => $pay['reference'] ?? null,
                    'slip_path' => $slipPath,
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
            if ($customer && $totalPaid < $totalAmount) {
                $due = $totalAmount - $totalPaid;
                $customer->increment('balance', $due);
            }

            return $sale->load(['items.phoneDevice', 'items.product', 'payments', 'customer', 'cashier']);
        });
    }
}

