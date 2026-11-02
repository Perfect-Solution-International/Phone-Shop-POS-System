<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Match customer by email or create profile if absent
        $customer = Customer::where('email', $user->email)
            ->when($user->shop_id, fn ($q) => $q->where('shop_id', $user->shop_id))
            ->first();

        if (! $customer && $user->shop_id) {
            $customer = Customer::create([
                'shop_id' => $user->shop_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '0700000000',
            ]);
        }

        $repairs = collect();
        $warranties = collect();
        $sales = collect();

        if ($customer) {
            $repairs = RepairJob::where('customer_id', $customer->id)
                ->with(['partsUsed.sparePart'])
                ->latest()
                ->get();

            $warranties = Warranty::where('customer_id', $customer->id)
                ->with(['claims'])
                ->latest()
                ->get();

            $sales = Sale::where('customer_id', $customer->id)
                ->with(['items', 'payments'])
                ->latest()
                ->get();
        }

        $availablePhones = collect();
        $availableProducts = collect();
        if ($user->shop_id) {
            $availablePhones = PhoneDevice::withoutGlobalScopes()
                ->where('shop_id', $user->shop_id)
                ->whereIn('status', ['in_stock', 'In Stock'])
                ->latest()
                ->get();

            $availableProducts = Product::withoutGlobalScopes()
                ->where('shop_id', $user->shop_id)
                ->where('stock_quantity', '>', 0)
                ->with('category')
                ->latest()
                ->get();
        }

        $metrics = [
            'active_repairs' => $repairs->whereNotIn('status', ['delivered'])->count(),
            'active_warranties' => $warranties->filter->isValid()->count(),
            'total_invoices' => $sales->count(),
            'available_products' => $availablePhones->count() + $availableProducts->count(),
            'balance' => $customer?->balance ?? 0.00,
        ];

        return view('customer-portal.index', compact('customer', 'repairs', 'warranties', 'sales', 'metrics', 'availablePhones', 'availableProducts'));
    }

    public function buy(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $shopId = (int) $user->shop_id;

        $customer = Customer::withoutGlobalScopes()
            ->where('email', $user->email)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->first();

        if (! $customer && $shopId) {
            app()->instance('shop.id', $shopId);
            $customer = Customer::create([
                'shop_id' => $shopId,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '0700000000',
            ]);
            app()->forgetInstance('shop.id');
        }

        abort_unless($customer, 403, 'Customer profile required.');

        $data = $request->validate([
            'item_type' => ['required', 'in:phone,product'],
            'phone_device_id' => ['nullable', 'required_if:item_type,phone', Rule::exists('phone_devices', 'id')->where('shop_id', $shopId)],
            'product_id' => ['nullable', 'required_if:item_type,product', Rule::exists('products', 'id')->where('shop_id', $shopId)],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,card,bank_transfer'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($data['contact_phone']) && $data['contact_phone'] !== $customer->phone) {
            $customer->update(['phone' => $data['contact_phone']]);
        }
        if (! empty($data['delivery_address']) && $data['delivery_address'] !== $customer->address) {
            $customer->update(['address' => $data['delivery_address']]);
        }

        try {
            $sale = DB::transaction(function () use ($shopId, $customer, $user, $data) {
                app()->instance('shop.id', $shopId);

                $cashierId = $user->id;
                $invoiceNumber = 'INV-' . strtoupper(substr(uniqid(), -6));
                $unitPrice = 0.00;
                $itemName = '';
                $qty = 1;
                $phone = null;
                $product = null;

                if ($data['item_type'] === 'phone') {
                    $phone = PhoneDevice::lockForUpdate()->findOrFail($data['phone_device_id']);
                    if (! in_array(strtolower($phone->status), ['in_stock', 'in stock'])) {
                        throw new \Exception('Sorry, this device is no longer in stock.');
                    }
                    $unitPrice = (float) $phone->selling_price;
                    $itemName = "{$phone->brand} {$phone->model} (IMEI: {$phone->imei_1})";
                    $qty = 1;
                } else {
                    $product = Product::lockForUpdate()->findOrFail($data['product_id']);
                    $qty = (int) ($data['quantity'] ?? 1);
                    if ($product->stock_quantity < $qty) {
                        throw new \Exception("Sorry, only {$product->stock_quantity} units available in stock.");
                    }
                    $unitPrice = (float) $product->selling_price;
                    $itemName = $product->name;
                }

                $total = $unitPrice * $qty;

                $sale = Sale::create([
                    'shop_id' => $shopId,
                    'customer_id' => $customer->id,
                    'user_id' => $cashierId,
                    'invoice_number' => $invoiceNumber,
                    'sale_date' => now()->toDateString(),
                    'subtotal' => $total,
                    'discount_amount' => 0.00,
                    'tax_amount' => 0.00,
                    'total_amount' => $total,
                    'status' => 'completed',
                    'notes' => 'Customer self-purchase via Portal. Address: ' . ($data['delivery_address'] ?? 'Store Pickup'),
                ]);

                SaleItem::create([
                    'shop_id' => $shopId,
                    'sale_id' => $sale->id,
                    'product_id' => $product?->id,
                    'phone_device_id' => $phone?->id,
                    'item_name' => $itemName,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $total,
                ]);

                SalePayment::create([
                    'shop_id' => $shopId,
                    'sale_id' => $sale->id,
                    'payment_method' => $data['payment_method'],
                    'amount' => $total,
                    'reference' => 'ONLINE-' . strtoupper(substr(uniqid(), -6)),
                    'status' => 'completed',
                ]);

                if ($phone) {
                    $phone->update([
                        'status' => 'sold',
                        'sold_at' => now(),
                        'sale_id' => $sale->id,
                    ]);
                }

                if ($product) {
                    $product->decrement('stock_quantity', $qty);
                    InventoryMovement::create([
                        'shop_id' => $shopId,
                        'product_id' => $product->id,
                        'user_id' => $cashierId,
                        'type' => 'sale',
                        'quantity' => -$qty,
                        'reference' => $invoiceNumber,
                        'notes' => "Customer purchase by {$customer->name} ({$customer->phone})",
                    ]);
                }

                app()->forgetInstance('shop.id');

                return $sale;
            });

            return redirect()->route('customer.portal', ['tab' => 'invoices'])
                ->with('success', "Order placed successfully! Invoice #{$sale->invoice_number}. Stock has been deducted.");
        } catch (\Throwable $e) {
            app()->forgetInstance('shop.id');
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function repair(Request $request, RepairJob $repair): View
    {
        $user = $request->user();
        $customer = Customer::where('email', $user->email)->first();

        abort_unless($customer && $repair->customer_id === $customer->id, 403, 'Unauthorized access to this repair job.');

        return view('customer-portal.repairs.show', compact('repair', 'customer'));
    }

    public function invoice(Request $request, Sale $sale): View
    {
        $user = $request->user();
        $customer = Customer::where('email', $user->email)->first();

        abort_unless($customer && $sale->customer_id === $customer->id, 403, 'Unauthorized access to this invoice.');

        return view('customer-portal.invoices.show', compact('sale', 'customer'));
    }

    public function approveEstimate(Request $request, RepairJob $repair): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $customer = Customer::where('email', $user->email)->first();

        abort_unless($customer && $repair->customer_id === $customer->id, 403, 'Unauthorized access to this repair job.');

        $notes = $request->input('notes');
        $approvalNote = '[Customer Approved Estimate on ' . now()->toDayDateTimeString() . ($notes ? ': ' . $notes : '') . ']';

        $repair->update([
            'status' => 'in_repair',
            'technician_notes' => trim(($repair->technician_notes ? $repair->technician_notes . "\n" : '') . $approvalNote),
        ]);

        return back()->with('success', "Estimate for repair #{$repair->job_number} has been approved. Our technicians will begin repair work immediately!");
    }

    public function declineEstimate(Request $request, RepairJob $repair): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $customer = Customer::where('email', $user->email)->first();

        abort_unless($customer && $repair->customer_id === $customer->id, 403, 'Unauthorized access to this repair job.');

        $reason = $request->input('reason', 'Customer declined estimate');
        $declineNote = '[Customer Declined Estimate on ' . now()->toDayDateTimeString() . ': ' . $reason . ']';

        $repair->update([
            'technician_notes' => trim(($repair->technician_notes ? $repair->technician_notes . "\n" : '') . $declineNote),
        ]);

        return back()->with('info', 'Repair estimate response recorded. Our workshop representative will contact you shortly.');
    }
}

