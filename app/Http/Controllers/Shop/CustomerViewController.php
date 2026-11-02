<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDebtPayment;
use App\Models\PhoneTradeIn;
use App\Models\RepairJob;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('has_balance')) {
            $query->where('balance', '>', 0);
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $customers */
        $customers = $query->latest()->paginate(20);
        $customers->withQueryString();

        return view('shop.customers.index', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'nic' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        Customer::create($validated);

        return redirect()->route('shop.customers')->with('success', 'Customer profile registered successfully!');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'nic' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update($validated);

        if (! empty($validated['nic'])) {
            RepairJob::withoutGlobalScopes()->where('customer_id', $customer->id)->update([
                'customer_nic' => $validated['nic'],
            ]);
        }

        return redirect()->route('shop.customers')->with('success', "Customer profile '{$customer->name}' updated successfully!");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ((float) $customer->balance > 0) {
            return back()->with('error', "Cannot delete customer '{$customer->name}' because they have an outstanding debt balance of LKR " . number_format($customer->balance, 2) . ". Please clear their balance first.");
        }

        $hasHistory = $customer->sales()->exists()
            || RepairJob::where('customer_id', $customer->id)->exists()
            || PhoneTradeIn::where('customer_id', $customer->id)->exists();

        if ($hasHistory) {
            return back()->with('error', "Cannot delete customer '{$customer->name}' because they have active sales, repairs, or trade-in transaction records.");
        }

        $name = $customer->name;
        $customer->delete();

        return redirect()->route('shop.customers')->with('success', "Customer profile '{$name}' deleted successfully.");
    }

    public function settleDebt(Request $request, Customer $customer): RedirectResponse
    {
        $currentBalance = (float) $customer->balance;
        if ($currentBalance <= 0) {
            return back()->with('error', "Customer '{$customer->name}' does not have any outstanding debt.");
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $currentBalance],
            'payment_method' => ['required', 'string', 'in:cash,card,bank_transfer'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_date' => ['nullable', 'date'],
        ]);

        $amount = (float) $validated['amount'];
        $paymentDate = $validated['payment_date'] ?? now()->toDateString();

        DB::transaction(function () use ($customer, $amount, $validated, $paymentDate): void {
            // 1. Record debt payment ledger entry
            CustomerDebtPayment::create([
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'payment_date' => $paymentDate,
            ]);

            // 2. Reduce customer outstanding debt balance
            $customer->decrement('balance', $amount);

            // 3. Reconcile against customer's unpaid / partial sales (oldest first)
            $unpaidSales = $customer->sales()
                ->whereIn('status', ['credit', 'partial'])
                ->oldest()
                ->get();

            $remainingAmountToAllocate = $amount;
            foreach ($unpaidSales as $sale) {
                if ($remainingAmountToAllocate <= 0) {
                    break;
                }

                $unpaidPortion = (float) $sale->total_amount - (float) $sale->paid_amount;
                if ($unpaidPortion > 0) {
                    $allocated = min($remainingAmountToAllocate, $unpaidPortion);
                    $newPaid = (float) $sale->paid_amount + $allocated;
                    $newStatus = $newPaid >= (float) $sale->total_amount ? 'completed' : 'partial';

                    $sale->update([
                        'paid_amount' => $newPaid,
                        'status' => $newStatus,
                    ]);

                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'payment_method' => $validated['payment_method'],
                        'amount' => $allocated,
                        'reference' => 'Debt Settlement: ' . ($validated['reference'] ?? 'Counter Receipt'),
                    ]);

                    $remainingAmountToAllocate -= $allocated;
                }
            }
        });

        $remainingDebt = max(0, $currentBalance - $amount);
        return redirect()->back()
            ->with('success', "Debt payment of LKR " . number_format($amount, 2) . " received successfully from '{$customer->name}'! Remaining Debt: LKR " . number_format($remainingDebt, 2));
    }
}

