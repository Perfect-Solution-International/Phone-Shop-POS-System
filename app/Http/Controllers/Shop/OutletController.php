<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Platform\SubscriptionPayment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OutletController extends Controller
{
    /**
     * Display the outlets / branches management page for the current shop.
     */
    public function index(Request $request): View
    {
        $shop = $request->user()->shop;

        // Auto-provision initial Main Outlet if the shop has 0 outlets
        if ($shop && Outlet::count() === 0) {
            Outlet::create([
                'shop_id' => $shop->id,
                'name' => 'Main Branch',
                'code' => 'OUT-01',
                'phone' => $request->user()->phone,
                'email' => $request->user()->email,
                'address' => $shop->address ?? 'Main Retail Store',
                'city' => 'Colombo',
                'manager_name' => $request->user()->name,
                'is_main' => true,
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);
        }

        $outlets = Outlet::orderByDesc('is_main')->orderBy('name')->get();

        // Calculate branch quota from subscription plan
        $subscription = $shop?->activeSubscription ?? $shop?->subscription;
        $plan = $subscription?->plan;
        $isTrial = ($subscription?->status === 'trial');
        $maxBranches = $isTrial ? max(5, (int) ($plan?->max_branches ?? 1)) : ($plan ? (int) $plan->max_branches : 1);
        $usedBranches = $outlets->count();
        $approvedCount = $outlets->where('approval_status', 'approved')->count();
        $remainingPlanSlots = max(0, $maxBranches - $approvedCount);
        $canAddMore = true; // Always allow adding branches; covered if slots remain, or as paid add-on
        $additionalBranchFee = 2500.00;

        $activeOutletId = session('active_outlet_id', $outlets->firstWhere('is_main', true)?->id ?? $outlets->first()?->id);

        return view('shop.outlets.index', compact(
            'outlets',
            'maxBranches',
            'usedBranches',
            'approvedCount',
            'remainingPlanSlots',
            'canAddMore',
            'additionalBranchFee',
            'plan',
            'activeOutletId'
        ));
    }

    /**
     * Store a new outlet for the current shop.
     */
    public function store(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop;
        $subscription = $shop?->activeSubscription ?? $shop?->subscription;
        $plan = $subscription?->plan;
        $isTrial = ($subscription?->status === 'trial');
        $maxBranches = $isTrial ? max(5, (int) ($plan?->max_branches ?? 1)) : ($plan ? (int) $plan->max_branches : 1);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:150',
            'manager_email' => 'nullable|email|max:150',
            'manager_password' => 'nullable|string|min:6',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'receipt_header' => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:255',
        ]);

        $managerEmail = ! empty($validated['manager_email']) ? strtolower(trim($validated['manager_email'])) : null;
        $managerPassword = $validated['manager_password'] ?? null;
        unset($validated['manager_password']);

        if ($managerEmail) {
            $existingUser = User::where('email', $managerEmail)->first();
            if ($existingUser && $existingUser->shop_id !== $shop->id) {
                return redirect()->back()->with('error', "User with email '{$managerEmail}' is already registered under another store workspace.");
            }
        }

        if (empty($validated['code'])) {
            $nextNum = Outlet::count() + 1;
            $validated['code'] = 'OUT-' . str_pad((string) $nextNum, 2, '0', STR_PAD_LEFT);
        }

        $validated['code'] = strtoupper(trim($validated['code']));

        // Check if code is already used within this shop
        if (Outlet::where('code', $validated['code'])->exists()) {
            return redirect()->back()->with('error', "An outlet with code '{$validated['code']}' already exists in your shop.");
        }

        $isFirst = (Outlet::count() === 0);
        $approvedCount = Outlet::withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->where('approval_status', 'approved')
            ->count();
        $isWithinPlanQuota = ($approvedCount < $maxBranches);

        if ($isFirst) {
            $validated['is_main'] = true;
            $validated['is_active'] = true;
            $validated['approval_status'] = 'approved';
            $validated['approved_at'] = now();
            $validated['billing_type'] = 'included';
            $validated['payment_status'] = 'paid';
            $validated['additional_fee'] = 0.00;
        } else {
            $validated['is_main'] = false;
            $validated['is_active'] = false; // Remains inactive until approved
            $validated['approval_status'] = 'pending';
            $validated['approved_at'] = null;
            $validated['approved_by'] = null;

            if ($isWithinPlanQuota) {
                $validated['billing_type'] = 'included';
                $validated['payment_status'] = 'paid';
                $validated['additional_fee'] = 0.00;
            } else {
                $validated['billing_type'] = 'additional';
                $validated['payment_status'] = 'unpaid';
                $validated['additional_fee'] = 2500.00;
            }
        }

        $validated['shop_id'] = $shop->id;

        $outlet = Outlet::create($validated);

        // Provision Branch Manager Login Account if manager email provided
        if ($managerEmail) {
            $managerUser = User::updateOrCreate(
                ['email' => $managerEmail],
                [
                    'shop_id' => $shop->id,
                    'outlet_id' => $outlet->id,
                    'name' => $validated['manager_name'] ?: ($outlet->name . ' Manager'),
                    'phone' => $validated['phone'] ?? null,
                    'password' => Hash::make($managerPassword ?: 'Manager123!'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $managerUser->hasRole('Shop Manager')) {
                $managerUser->assignRole('Shop Manager');
            }

            $outlet->update([
                'manager_user_id' => $managerUser->id,
                'manager_email' => $managerEmail,
            ]);
        }

        if ($outlet->approval_status === 'pending') {
            if ($outlet->billing_type === 'included') {
                return redirect()->route('shop.outlets.index')->with('success', "Branch '{$outlet->name}' ({$outlet->code}) submitted successfully! It is covered under your {$plan?->name} quota ({$approvedCount}/{$maxBranches} used) — pending Super Admin approval without extra payment.");
            } else {
                return redirect()->route('shop.outlets.index')->with('success', "Branch '{$outlet->name}' ({$outlet->code}) submitted! Your {$plan?->name} quota of {$maxBranches} branch(es) is already in use. This additional branch requires payment of LKR 2,500.00 before Super Admin approval.");
            }
        }

        return redirect()->route('shop.outlets.index')->with('success', "Outlet '{$outlet->name}' ({$outlet->code}) created successfully!");
    }

    /**
     * Update an existing outlet.
     */
    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:150',
            'manager_email' => 'nullable|email|max:150',
            'manager_password' => 'nullable|string|min:6',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'receipt_header' => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:255',
        ]);

        $managerEmail = ! empty($validated['manager_email']) ? strtolower(trim($validated['manager_email'])) : null;
        $managerPassword = $validated['manager_password'] ?? null;
        unset($validated['manager_password']);

        if ($managerEmail) {
            $existingUser = User::where('email', $managerEmail)->first();
            if ($existingUser && $existingUser->shop_id !== $outlet->shop_id) {
                return redirect()->back()->with('error', "User with email '{$managerEmail}' is already registered under another store workspace.");
            }

            $userData = [
                'shop_id' => $outlet->shop_id,
                'outlet_id' => $outlet->id,
                'name' => $validated['manager_name'] ?: ($outlet->name . ' Manager'),
                'phone' => $validated['phone'] ?? null,
                'email_verified_at' => now(),
            ];
            if (! empty($managerPassword)) {
                $userData['password'] = Hash::make($managerPassword);
            } elseif (! $existingUser) {
                $userData['password'] = Hash::make('Manager123!');
            }

            $managerUser = User::updateOrCreate(
                ['email' => $managerEmail],
                $userData
            );

            if (! $managerUser->hasRole('Shop Manager')) {
                $managerUser->assignRole('Shop Manager');
            }

            $validated['manager_user_id'] = $managerUser->id;
            $validated['manager_email'] = $managerEmail;
        } else {
            $validated['manager_email'] = null;
        }

        $validated['code'] = strtoupper(trim($validated['code']));

        // Ensure code uniqueness for other outlets in this shop
        $codeExists = Outlet::where('id', '!=', $outlet->id)
            ->where('code', $validated['code'])
            ->exists();

        if ($codeExists) {
            return redirect()->back()->with('error', "Branch code '{$validated['code']}' is already used by another outlet.");
        }

        $isMain = $request->boolean('is_main');
        if ($isMain) {
            Outlet::where('id', '!=', $outlet->id)->where('is_main', true)->update(['is_main' => false]);
            $validated['is_main'] = true;
        } else {
            // If this is currently the only main outlet and the owner is trying to uncheck main without another main
            if ($outlet->is_main && Outlet::where('is_main', true)->count() <= 1) {
                $validated['is_main'] = true; // Retain main branch status
            } else {
                $validated['is_main'] = false;
            }
        }

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $outlet->update($validated);

        // Update active session name if currently active
        if (session('active_outlet_id') == $outlet->id) {
            session(['active_outlet_name' => $outlet->name]);
        }

        return redirect()->route('shop.outlets.index')->with('success', "Outlet '{$outlet->name}' updated successfully!");
    }

    /**
     * Delete an outlet.
     */
    public function destroy(Outlet $outlet): RedirectResponse
    {
        if ($outlet->is_main) {
            return redirect()->back()->with('error', 'Cannot delete the primary Main Branch of your shop.');
        }

        $name = $outlet->name;

        if (session('active_outlet_id') == $outlet->id) {
            session()->forget(['active_outlet_id', 'active_outlet_name']);
        }

        $outlet->delete();

        return redirect()->route('shop.outlets.index')->with('success', "Outlet '{$name}' removed from your branches.");
    }

    /**
     * Switch the active outlet session context.
     */
    public function switchOutlet(Request $request, Outlet $outlet): JsonResponse|RedirectResponse
    {
        if ($outlet->approval_status !== 'approved') {
            $msg = "Cannot switch to branch '{$outlet->name}' because it is pending Super Admin approval.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        session([
            'active_outlet_id' => $outlet->id,
            'active_outlet_name' => $outlet->name,
            'active_outlet_code' => $outlet->code,
        ]);

        $msg = "Switched active workspace branch to '{$outlet->name}'.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg, 'outlet' => $outlet]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Set this outlet as the primary/main branch.
     */
    public function toggleMain(Outlet $outlet): RedirectResponse
    {
        if ($outlet->approval_status !== 'approved') {
            return redirect()->back()->with('error', "Cannot set '{$outlet->name}' as Main Branch because it is pending Super Admin approval.");
        }

        Outlet::where('is_main', true)->update(['is_main' => false]);
        $outlet->update(['is_main' => true]);

        return redirect()->route('shop.outlets.index')->with('success', "Outlet '{$outlet->name}' is now set as the Main Branch.");
    }

    /**
     * Pay the additional branch fee via Card or Bank Transfer deposit.
     */
    public function payAdditionalBranchFee(Request $request, Outlet $outlet): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:card,bank_transfer',
            'cardholder_name' => 'required_if:payment_method,card|nullable|string|max:100',
            'card_number' => 'required_if:payment_method,card|nullable|string|max:20',
            'card_expiry' => 'required_if:payment_method,card|nullable|string|max:7',
            'card_cvv' => 'required_if:payment_method,card|nullable|string|max:4',
            'reference_number' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $fee = (float) ($outlet->additional_fee ?: 2500.00);
        $shop = $request->user()->shop;

        if ($validated['payment_method'] === 'card') {
            $last4 = substr(preg_replace('/[^0-9]/', '', (string) ($validated['card_number'] ?? '0000')), -4) ?: '4242';
            $txnId = 'TXN-OUTLET-CARD-' . strtoupper(Str::random(10));

            $outlet->update([
                'payment_status' => 'paid',
                'payment_method' => 'card',
                'payment_reference' => 'CARD-' . $last4 . ' (' . $txnId . ')',
                'paid_at' => now(),
            ]);

            SubscriptionPayment::create([
                'shop_id' => $shop->id,
                'plan_id' => $shop->activeSubscription?->plan_id ?? $shop->subscription?->plan_id,
                'amount' => $fee,
                'discount_amount' => 0.00,
                'refunded_amount' => 0.00,
                'payment_method' => 'card',
                'billing_cycle' => 'monthly',
                'status' => 'approved',
                'transaction_id' => $txnId,
                'reference_number' => 'CARD-' . $last4 . ' (Branch: ' . $outlet->code . ')',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            return redirect()->route('shop.outlets.index')
                ->with('success', "Payment of LKR " . number_format($fee, 2) . " for branch '{$outlet->name}' successful! Super Admin can now verify and approve your branch.");
        } else {
            $receiptUrl = null;
            if ($request->hasFile('receipt')) {
                $path = $request->file('receipt')->store('receipts', 'public');
                $receiptUrl = '/storage/' . $path;
            }

            $ref = $validated['reference_number'] ?? 'DEP-' . strtoupper(Str::random(8));

            $outlet->update([
                'payment_status' => 'pending_verification',
                'payment_method' => 'bank_transfer',
                'payment_reference' => $ref,
                'payment_receipt_url' => $receiptUrl,
            ]);

            SubscriptionPayment::create([
                'shop_id' => $shop->id,
                'plan_id' => $shop->activeSubscription?->plan_id ?? $shop->subscription?->plan_id,
                'amount' => $fee,
                'discount_amount' => 0.00,
                'refunded_amount' => 0.00,
                'payment_method' => 'bank_transfer',
                'billing_cycle' => 'monthly',
                'status' => 'pending',
                'transaction_id' => 'TXN-OUTLET-BANK-' . strtoupper(Str::random(10)),
                'reference_number' => $ref . ' (Branch: ' . $outlet->code . ')',
                'receipt_url' => $receiptUrl,
            ]);

            return redirect()->route('shop.outlets.index')
                ->with('success', "Bank transfer slip uploaded for branch '{$outlet->name}'. Super Admin will verify the slip and approve your branch.");
        }
    }
}
