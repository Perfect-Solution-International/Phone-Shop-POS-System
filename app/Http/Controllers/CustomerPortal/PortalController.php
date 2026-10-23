<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\Warranty;
use Illuminate\Http\Request;
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

        $metrics = [
            'active_repairs' => $repairs->whereNotIn('status', ['delivered'])->count(),
            'active_warranties' => $warranties->filter->isValid()->count(),
            'total_invoices' => $sales->count(),
            'balance' => $customer?->balance ?? 0.00,
        ];

        return view('customer-portal.index', compact('customer', 'repairs', 'warranties', 'sales', 'metrics'));
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
}

