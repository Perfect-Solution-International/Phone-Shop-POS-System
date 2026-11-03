<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuotationViewController extends Controller
{
    public function index(Request $request): View
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Quotation::with(['outlet', 'customer', 'user', 'items', 'convertedSale'])
            ->where('shop_id', $shopId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $quotations = $query->latest()->paginate(15);

        $pendingCount = Quotation::where('shop_id', $shopId)->where('status', 'pending')->count();
        $convertedCount = Quotation::where('shop_id', $shopId)->where('status', 'converted')->count();
        $totalQuotationValue = Quotation::where('shop_id', $shopId)->where('status', 'pending')->sum('total_amount');

        $customers = Customer::where('shop_id', $shopId)->orderBy('name')->get();
        $products = Product::where('shop_id', $shopId)->where('stock_quantity', '>', 0)->orderBy('name')->get();
        $phones = PhoneDevice::where('shop_id', $shopId)->whereIn('status', ['in_stock', 'In Stock'])->orderBy('brand')->orderBy('model')->get();
        $outlets = Outlet::where('shop_id', $shopId)->orderBy('name')->get();

        return view('shop.quotations.index', compact(
            'quotations',
            'pendingCount',
            'convertedCount',
            'totalQuotationValue',
            'customers',
            'products',
            'phones',
            'outlets',
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
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('shop_id', $shopId)],
            'valid_until' => ['required', 'date', 'after_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:product,phone,custom'],
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('shop_id', $shopId)],
            'items.*.phone_device_id' => ['nullable', Rule::exists('phone_devices', 'id')->where('shop_id', $shopId)],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'terms' => ['nullable', 'string', 'max:500'],
        ]);

        $quotationNumber = 'QT-' . strtoupper(substr(uniqid(), -5));
        $subtotal = 0;

        foreach ($data['items'] as $item) {
            $subtotal += ($item['quantity'] * $item['unit_price']);
        }

        $discount = (float) ($data['discount'] ?? 0);
        $totalAmount = max(0, $subtotal - $discount);

        DB::transaction(function () use ($shopId, $outletId, $data, $quotationNumber, $subtotal, $discount, $totalAmount) {
            $quotation = Quotation::create([
                'shop_id' => $shopId,
                'outlet_id' => $outletId,
                'user_id' => auth()->id(),
                'customer_id' => $data['customer_id'] ?? null,
                'quotation_number' => $quotationNumber,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'quotation_date' => now()->toDateString(),
                'valid_until' => $data['valid_until'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? 'Valid for 14 days from issue date. Subject to stock availability.',
            ]);

            foreach ($data['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];

                QuotationItem::create([
                    'shop_id' => $shopId,
                    'quotation_id' => $quotation->id,
                    'item_type' => $item['item_type'],
                    'product_id' => $item['product_id'] ?? null,
                    'phone_device_id' => $item['phone_device_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);
            }
        });

        return redirect()->route('shop.quotations.index')
            ->with('success', "Quotation {$quotationNumber} for {$data['customer_name']} created successfully.");
    }

    public function convertToSale(Request $request, Quotation $quotation): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');

        if ($quotation->status !== 'pending') {
            return redirect()->route('shop.quotations.index')->with('error', 'Only pending quotations can be converted to sales.');
        }

        $paymentMethod = $request->input('payment_method', 'cash');
        $invoiceNumber = 'INV-' . strtoupper(substr(uniqid(), -6));

        $sale = DB::transaction(function () use ($shopId, $quotation, $paymentMethod, $invoiceNumber) {
            $sale = Sale::create([
                'shop_id' => $shopId,
                'outlet_id' => $quotation->outlet_id,
                'user_id' => auth()->id(),
                'customer_id' => $quotation->customer_id,
                'invoice_number' => $invoiceNumber,
                'sale_date' => now()->toDateString(),
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount,
                'total_amount' => $quotation->total_amount,
                'paid_amount' => $quotation->total_amount,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'notes' => "Converted from Quotation {$quotation->quotation_number}",
            ]);

            foreach ($quotation->items as $item) {
                SaleItem::create([
                    'shop_id' => $shopId,
                    'sale_id' => $sale->id,
                    'product_id' => $item->product_id,
                    'phone_device_id' => $item->phone_device_id,
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);

                // Decrement stock
                if ($item->product_id && $item->product) {
                    $item->product->decrement('stock_quantity', $item->quantity);
                } elseif ($item->phone_device_id && $item->phoneDevice) {
                    $item->phoneDevice->update([
                        'status' => 'Sold',
                        'sale_id' => $sale->id,
                    ]);
                }
            }

            SalePayment::create([
                'shop_id' => $shopId,
                'sale_id' => $sale->id,
                'payment_method' => $paymentMethod,
                'amount' => $quotation->total_amount,
                'reference' => "QUOTE-{$quotation->quotation_number}",
            ]);

            $quotation->update([
                'status' => 'converted',
                'converted_sale_id' => $sale->id,
            ]);

            return $sale;
        });

        return redirect()->route('shop.sales.show', $sale)
            ->with('success', "Quotation {$quotation->quotation_number} converted directly into Invoice {$invoiceNumber}.");
    }
}
