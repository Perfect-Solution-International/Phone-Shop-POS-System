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
        $query = User::role('Shop Owner')
            ->with(['shop.subscription.plan', 'roles']);

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

        $owners = $query->latest()->paginate(15)->withQueryString();

        // Metrics for summary cards
        $totalOwners = User::role('Shop Owner')->count();
        $verifiedOwners = User::role('Shop Owner')->whereNotNull('email_verified_at')->count();
        $unverifiedOwners = $totalOwners - $verifiedOwners;
        $totalShops = Shop::count();

        // All shops for dropdown assignment
        $shops = Shop::orderBy('name')->get(['id', 'name']);

        if ($request->wantsJson()) {
            return response()->json([
                'owners' => $owners,
                'metrics' => compact('totalOwners', 'verifiedOwners', 'unverifiedOwners', 'totalShops'),
            ]);
        }

        return view('super-admin.owners.index', compact(
            'owners',
            'shops',
            'totalOwners',
            'verifiedOwners',
            'unverifiedOwners',
            'totalShops'
        ));
    }

    /**
     * Store a newly created Shop Owner.
     */
    public function store(Request $request, ShopOnboardingService $onboardingService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
            'shop_assignment_type' => 'required|in:existing,new',
            'shop_id' => 'required_if:shop_assignment_type,existing|nullable|exists:shops,id',
            'new_shop_name' => 'required_if:shop_assignment_type,new|nullable|string|max:255',
            'business_address' => 'nullable|string|max:255',
            'is_verified' => 'nullable|boolean',
        ]);

        $shopId = null;

        if ($validated['shop_assignment_type'] === 'new' && ! empty($validated['new_shop_name'])) {
            // Provision new shop workspace via onboarding service
            $onboardResult = $onboardingService->onboard([
                'shop_name' => $validated['new_shop_name'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'business_address' => $validated['business_address'] ?? null,
            ]);

            $user = $onboardResult['user'];
            if ($request->boolean('is_verified')) {
                $user->update(['email_verified_at' => now()]);
            }

            SystemActivity::log(
                'owner.created',
                "Super Admin provisioned new shop '{$validated['new_shop_name']}' and created owner '{$user->name}' ({$user->email})",
                $user->shop_id,
                auth()->id(),
                ['user_id' => $user->id, 'shop_id' => $user->shop_id]
            );

            return redirect()->route('superadmin.owners.index')
                ->with('status', "Shop Owner '{$user->name}' and new store '{$validated['new_shop_name']}' created successfully!");
        }

        // Assign to existing shop
        $shopId = $validated['shop_id'];
        $shop = Shop::findOrFail($shopId);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'shop_id' => $shop->id,
            'email_verified_at' => $request->boolean('is_verified') ? now() : null,
        ]);

        $user->assignRole('Shop Owner');

        SystemActivity::log(
            'owner.created',
            "Super Admin assigned new owner '{$user->name}' ({$user->email}) to shop '{$shop->name}'",
            $shop->id,
            auth()->id(),
            ['user_id' => $user->id, 'shop_id' => $shop->id]
        );

        return redirect()->route('superadmin.owners.index')
            ->with('status', "Shop Owner '{$user->name}' registered and assigned to store '{$shop->name}' successfully!");
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
            'shop_id' => 'required|exists:shops,id',
            'password' => 'nullable|string|min:8|confirmed',
            'is_verified' => 'nullable|boolean',
        ]);

        $shop = Shop::findOrFail($validated['shop_id']);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'shop_id' => $shop->id,
        ];

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
            $shop->id,
            auth()->id(),
            ['user_id' => $user->id, 'shop_id' => $shop->id]
        );

        return redirect()->route('superadmin.owners.index')
            ->with('status', "Shop Owner '{$user->name}' updated successfully!");
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

