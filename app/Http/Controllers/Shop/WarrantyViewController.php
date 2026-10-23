<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WarrantyViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Warranty::with(['customer', 'product', 'phoneDevice', 'claims']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('warranty_number', 'like', "%{$search}%")
                    ->orWhere('imei', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($type = $request->input('warranty_type')) {
            $query->where('warranty_type', $type);
        }

        $warranties = $query->latest()->paginate(15)->withQueryString();
        $customers = Customer::latest()->limit(100)->get();
        $products = Product::where('status', 'active')->limit(50)->get();

        return view('shop.warranties.index', compact('warranties', 'customers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'nullable|exists:products,id',
            'warranty_type' => 'required|string|in:manufacturer,shop,supplier,repair',
            'imei' => 'nullable|string|max:50',
            'duration_months' => 'required|integer|min:1|max:60',
            'starts_on' => 'nullable|date',
            'terms' => 'nullable|string|max:1000',
        ]);

        $startsOn = isset($validated['starts_on']) ? now()->parse($validated['starts_on']) : now();
        $endsOn = $startsOn->copy()->addMonths((int) $validated['duration_months']);

        $validated['starts_on'] = $startsOn->toDateString();
        $validated['ends_on'] = $endsOn->toDateString();
        $validated['warranty_number'] = 'WAR-'.date('Y').'-'.strtoupper(Str::random(6));
        $validated['status'] = 'active';

        $warranty = Warranty::create($validated);

        return redirect()->route('shop.warranties')->with('success', "Warranty certificate #{$warranty->warranty_number} registered successfully!");
    }

    public function claim(Request $request, Warranty $warranty): RedirectResponse
    {
        if (! $warranty->isValid()) {
            return back()->with('error', 'Cannot submit claim against an expired or voided warranty.');
        }

        $validated = $request->validate([
            'issue_description' => 'required|string|max:1000',
            'settlement_type' => 'nullable|string|in:repair,replacement,refund',
            'action_notes' => 'nullable|string|max:1000',
        ]);

        $validated['warranty_id'] = $warranty->id;
        $validated['claim_number'] = 'CLM-'.date('Ymd').'-'.strtoupper(Str::random(4));
        $validated['claim_date'] = now()->toDateString();
        $validated['status'] = 'pending';

        $claim = WarrantyClaim::create($validated);

        return redirect()->route('shop.warranties')->with('success', "Warranty Claim #{$claim->claim_number} recorded successfully!");
    }
}

