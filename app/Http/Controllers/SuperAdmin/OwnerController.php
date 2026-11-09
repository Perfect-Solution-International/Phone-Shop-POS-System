<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Shop;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Platform\SystemActivity;
use App\Models\User;
use App\Services\Tenancy\ShopOnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerController extends Controller
{
    /**
     * Display a listing of all Shop Owners.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = User::where(function ($q) {
            $q->role('Shop Owner')
                ->orWhere(fn ($sq) => $sq->whereNotNull('shop_id')->whereHas('roles', fn ($rq) => $rq->whereIn('name', ['Shop Owner', 'Administrator'])));
        })->with(['shop.subscription.plan', 'shop.outlets', 'roles']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($shopId = $request->input('shop_id')) {
            $query->where('shop_id', $shopId);
        }

        if ($verification = $request->input('verification')) {
            if ($verification === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($verification === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $owners = $query->latest('id')->paginate(15)->withQueryString();

        // Metrics for summary cards
        $ownersQuery = User::where(function ($q) {
            $q->role('Shop Owner')
                ->orWhere(fn ($sq) => $sq->whereNotNull('shop_id')->whereHas('roles', fn ($rq) => $rq->whereIn('name', ['Shop Owner', 'Administrator'])));
        });
        $totalOwners = (clone $ownersQuery)->count();
        $verifiedOwners = (clone $ownersQuery)->whereNotNull('email_verified_at')->count();
        $unverifiedOwners = $totalOwners - $verifiedOwners;
        $totalShops = Shop::count();

        // All active plans for dropdown selection
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('monthly_price')->get();

        // All shops for dropdown assignment
        $shops = Shop::orderBy('name')->get(['id', 'name', 'address']);

        if ($request->wantsJson()) {
            return response()->json([
                'owners' => $owners,
                'metrics' => compact('totalOwners', 'verifiedOwners', 'unverifiedOwners', 'totalShops'),
            ]);
        }

        return view('super-admin.owners.index', compact(
            'owners',
            'shops',
            'plans',
            'totalOwners',
            'verifiedOwners',
            'unverifiedOwners',
            'totalShops'
        ));
    }

    /**
     * Store a newly created Shop Owner.
     */
    public function store(Request $request, ShopOnboardingService $onboardingService): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
            'shop_assignment_type' => 'nullable|in:existing,new',
            'shop_id' => 'required_if:shop_assignment_type,existing|nullable|exists:shops,id',
            'shop_name' => 'nullable|string|max:255',
            'new_shop_name' => 'nullable|string|max:255',
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'business_address' => 'nullable|string|max:255',
            'is_verified' => 'nullable|boolean',
            'payment_method' => 'nullable|in:card,bank_transfer,trial',
            'cardholder_name' => 'nullable|string|max:100',
            'card_number' => 'nullable|string|max:30',
            'card_expiry' => 'nullable|string|max:10',
            'card_cvv' => 'nullable|string|max:4',
            'reference_number' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $assignmentType = $validated['shop_assignment_type'] ?? 'new';
        $targetShopName = $validated['shop_name'] ?? $validated['new_shop_name'] ?? null;

        if ($assignmentType === 'new' || ($assignmentType !== 'existing' && ! empty($targetShopName))) {
            $shopName = $targetShopName ?: ($validated['name'] . "'s Phone Shop");

            // Provision new shop workspace via onboarding service directly under this owner
            $onboardResult = $onboardingService->onboard([
                'shop_name' => $shopName,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'business_address' => $validated['business_address'] ?? null,
                'plan_id' => $validated['plan_id'] ?? null,
                'payment_method' => $validated['payment_method'] ?? 'trial',
                'cardholder_name' => $validated['cardholder_name'] ?? null,
                'card_number' => $validated['card_number'] ?? null,
                'card_expiry' => $validated['card_expiry'] ?? null,
                'card_cvv' => $validated['card_cvv'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'receipt' => $request->file('receipt'),
            ]);

            $user = $onboardResult['user'];
            if ($request->boolean('is_verified', true)) {
                $user->update(['email_verified_at' => now()]);
            }

            SystemActivity::log(
                'owner.created',
                "Super Admin registered new Shop Owner '{$user->name}' ({$user->email}) and auto-provisioned store '{$shopName}'",
                $user->shop_id,
                auth()->id(),
                ['user_id' => $user->id, 'shop_id' => $user->shop_id]
            );

            $statusMsg = ($validated['payment_method'] ?? '') === 'bank_transfer'
                ? "Shop Owner '{$user->name}' registered successfully! Bank deposit slip submitted and is now awaiting verification under Bank Approvals."
                : "Shop Owner '{$user->name}' registered successfully with store '{$shopName}' under their account!";

            session()->flash('status', $statusMsg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $statusMsg,
                    'user' => $user->load(['shop.subscription.plan', 'shop.outlets', 'roles']),
                    'redirect' => route('superadmin.owners.index'),
                ], 201);
            }

            return redirect()->route('superadmin.owners.index')
                ->with('status', $statusMsg);
        }

        // Assign to existing shop
        $shopId = $validated['shop_id'];
        $shop = Shop::findOrFail($shopId);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'shop_id' => $shop->id,
            'email_verified_at' => $request->boolean('is_verified', true) ? now() : null,
        ]);

        $user->assignRole(['Shop Owner', 'Administrator']);

        SystemActivity::log(
            'owner.created',
            "Super Admin assigned new owner '{$user->name}' ({$user->email}) to shop '{$shop->name}'",
            $shop->id,
            auth()->id(),
            ['user_id' => $user->id, 'shop_id' => $shop->id]
        );

        $statusMsg = "Shop Owner '{$user->name}' registered and assigned to store '{$shop->name}' successfully!";

        session()->flash('status', $statusMsg);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $statusMsg,
                'user' => $user->load(['shop.subscription.plan', 'shop.outlets', 'roles']),
                'redirect' => route('superadmin.owners.index'),
            ], 201);
        }

        return redirect()->route('superadmin.owners.index')
            ->with('status', $statusMsg);
    }

    /**
     * Update an existing Shop Owner.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:50',
            'shop_id' => 'nullable|exists:shops,id',
            'shop_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'is_verified' => 'nullable|boolean',
        ]);

        $shop = null;
        if (! empty($validated['shop_id'])) {
            $shop = Shop::findOrFail($validated['shop_id']);
        } elseif ($user->shop) {
            $shop = $user->shop;
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if ($shop) {
            $updateData['shop_id'] = $shop->id;

            $shopUpdate = [];
            if (! empty($validated['shop_name'])) {
                $shopUpdate['name'] = $validated['shop_name'];
            }
            if (isset($validated['business_address'])) {
                $shopUpdate['address'] = $validated['business_address'];
            }
            if (! empty($shopUpdate)) {
                $shop->update($shopUpdate);
            }
        }

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if ($request->has('is_verified')) {
            $updateData['email_verified_at'] = $request->boolean('is_verified') ? ($user->email_verified_at ?? now()) : null;
        }

        $user->update($updateData);

        if (! $user->hasRole('Shop Owner')) {
            $user->assignRole('Shop Owner');
        }

        SystemActivity::log(
            'owner.updated',
            "Super Admin updated details for Shop Owner '{$user->name}' ({$user->email})",
            $user->shop_id,
            auth()->id(),
            ['user_id' => $user->id, 'shop_id' => $user->shop_id]
        );

        return redirect()->route('superadmin.owners.index')
            ->with('status', "Shop Owner '{$user->name}' and store details updated successfully!");
    }

    /**
     * Delete a Shop Owner.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own Super Administrator account.');
        }

        if ($user->hasRole('Super Administrator')) {
            return redirect()->back()->with('error', 'Cannot delete a platform Super Administrator from the Shop Owners directory.');
        }

        $name = $user->name;
        $email = $user->email;
        $shopId = $user->shop_id;
        $shopName = $user->shop?->name ?? 'Unknown Shop';

        SystemActivity::log(
            'owner.deleted',
            "Super Admin deleted Shop Owner '{$name}' ({$email}) from store '{$shopName}'",
            $shopId,
            auth()->id(),
            ['user_id' => $user->id, 'email' => $email]
        );

        $user->delete();

        return redirect()->route('superadmin.owners.index')
            ->with('status', "Shop Owner '{$name}' ({$email}) deleted successfully.");
    }

    /**
     * Access the shop workspace for this owner with Super Admin privileges.
     */
    public function loginAs(User $user): RedirectResponse
    {
        if (! $user->shop_id) {
            return redirect()->back()->with('error', "Owner '{$user->name}' does not have an assigned shop workspace.");
        }

        session(['superadmin_active_shop_id' => $user->shop_id]);

        return redirect()->route('shop.dashboard')
            ->with('success', "Managing store '{$user->shop?->name}' as owner {$user->name}. Full Super Admin privileges active.");
    }
}

