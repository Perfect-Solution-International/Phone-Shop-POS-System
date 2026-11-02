<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Platform\SystemActivity;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Shop::with(['subscription.plan', 'users' => fn ($q) => $q->oldest(), 'setting']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('users', fn ($uq) => $uq->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->whereHas('subscription', fn ($q) => $q->where('status', $status));
        }

        $shops = $query->latest()->paginate(15)->withQueryString();
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json(compact('shops', 'plans'));
        }

        return view('super-admin.shops.index', compact('shops', 'plans'));
    }

    public function show(Shop $shop): View|JsonResponse
    {
        $shop->load(['users', 'subscriptions.plan', 'subscriptionPayments.plan', 'setting', 'outlets']);
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('name')->get();

        if (request()->wantsJson()) {
            return response()->json(compact('shop', 'plans'));
        }

        return view('super-admin.shops.show', compact('shop', 'plans'));
    }

    public function edit(Shop $shop): View|JsonResponse
    {
        $shop->load(['users', 'subscription.plan', 'setting']);
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('name')->get();

        if (request()->wantsJson()) {
            return response()->json(compact('shop', 'plans'));
        }

        return view('super-admin.shops.edit', compact('shop', 'plans'));
    }

    public function update(Request $request, Shop $shop): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'business_reg_number' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'subscription_status' => ['nullable', 'in:active,trial,suspended,expired,cancelled'],
            'current_period_ends_at' => ['nullable', 'date'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($shop, $validated) {
            $shop->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $shop->email,
                'phone' => $validated['phone'] ?? $shop->phone,
                'address' => $validated['address'] ?? $shop->address,
                'business_reg_number' => $validated['business_reg_number'] ?? $shop->business_reg_number,
            ]);

            // Update currency and timezone in settings
            if (isset($validated['currency']) || isset($validated['timezone'])) {
                $setting = $shop->setting ?? new Setting(['shop_id' => $shop->id]);
                if (isset($validated['currency'])) {
                    $setting->currency = $validated['currency'];
                }
                if (isset($validated['timezone'])) {
                    $setting->timezone = $validated['timezone'];
                }
                $setting->shop_id = $shop->id;
                $setting->save();
            }

            // Update primary owner user details if provided
            $primaryOwner = $shop->users()->first();
            if ($primaryOwner) {
                $userUpdate = [];
                if (!empty($validated['owner_name'])) {
                    $userUpdate['name'] = $validated['owner_name'];
                }
                if (!empty($validated['owner_email'])) {
                    $userUpdate['email'] = $validated['owner_email'];
                }
                if (isset($validated['owner_phone'])) {
                    $userUpdate['phone'] = $validated['owner_phone'];
                }
                if (!empty($userUpdate)) {
                    $primaryOwner->update($userUpdate);
                }
            }

            // Update subscription details if provided
            $sub = $shop->subscription;
            if ($sub) {
                $subData = [];
                if (!empty($validated['plan_id'])) {
                    $subData['plan_id'] = $validated['plan_id'];
                }
                if (!empty($validated['subscription_status'])) {
                    $subData['status'] = $validated['subscription_status'];
                }
                if (!empty($validated['current_period_ends_at'])) {
                    $subData['current_period_ends_at'] = $validated['current_period_ends_at'];
                }
                if (!empty($subData)) {
                    $sub->update($subData);
                }
            }

            SystemActivity::log(
                'shop.updated',
                "Shop '{$shop->name}' details updated by Super Admin.",
                $shop->id,
                auth()->id(),
                $validated
            );
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Shop '{$shop->name}' updated successfully.",
                'shop' => $shop->fresh(['subscription.plan', 'users', 'setting']),
            ]);
        }

        return redirect()->route('superadmin.shops.index')
            ->with('status', "Shop '{$shop->name}' details have been updated successfully!");
    }

    public function destroy(Request $request, Shop $shop): RedirectResponse|JsonResponse
    {
        $shopName = $shop->name;
        $shopId = $shop->id;

        DB::transaction(function () use ($shop, $shopId, $shopName) {
            if (session('superadmin_active_shop_id') == $shopId) {
                session()->forget('superadmin_active_shop_id');
            }

            // Delete dependent records in child-first sequence to prevent foreign key errors:
            $tablesToClean = [
                'warranty_claims',
                'warranties',
                'repair_parts_used',
                'repair_jobs',
                'customer_debt_payments',
                'sale_return_items',
                'sale_returns',
                'sale_payments',
                'sale_items',
                'sales',
                'quotation_items',
                'quotations',
                'stock_transfer_items',
                'stock_transfers',
                'phone_trade_ins',
                'grn_items',
                'grns',
                'purchase_items',
                'purchases',
                'expenses',
                'inventory_movements',
                'phone_devices',
                'products',
                'spare_parts',
                'customers',
                'suppliers',
                'categories',
                'brands',
                'sms_logs',
                'shop_notifications',
                'leaves',
                'attendances',
                'salary_advances',
                'payrolls',
                'employees',
                'hrm_settings',
                'outlets',
                'settings',
                'subscription_payments',
                'subscriptions',
                'system_activities',
            ];

            foreach ($tablesToClean as $tbl) {
                if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'shop_id')) {
                    DB::table($tbl)->where('shop_id', $shopId)->delete();
                }
            }

            // Delete non-superadmin users belonging to this shop
            $users = User::where('shop_id', $shopId)->get();
            foreach ($users as $user) {
                if (!$user->hasRole('Super Administrator') && !$user->can('superadmin.view')) {
                    $user->delete();
                } else {
                    $user->update(['shop_id' => null]);
                }
            }

            $shop->delete();

            SystemActivity::log(
                'shop.deleted',
                "Shop '{$shopName}' (ID #{$shopId}) permanently deleted by Super Admin.",
                null,
                auth()->id()
            );
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Shop '{$shopName}' has been permanently deleted.",
            ]);
        }

        return redirect()->route('superadmin.shops.index')
            ->with('status', "Shop '{$shopName}' has been permanently deleted along with its workspace data.");
    }

    public function toggleSuspension(Shop $shop): RedirectResponse|JsonResponse
    {
        $sub = $shop->subscription;
        if ($sub) {
            $oldStatus = $sub->status;
            $newStatus = $sub->status === 'suspended' ? 'active' : 'suspended';
            $sub->update(['status' => $newStatus]);
            $msg = $newStatus === 'suspended' ? "Shop '{$shop->name}' has been suspended." : "Shop '{$shop->name}' has been reactivated.";

            SystemActivity::log(
                $newStatus === 'suspended' ? 'shop.suspended' : 'shop.reactivated',
                $msg,
                $shop->id,
                auth()->id(),
                ['previous_status' => $oldStatus, 'new_status' => $newStatus]
            );
        } else {
            $msg = "No subscription found for this shop.";
        }

        if (request()->wantsJson()) {
            return response()->json(['message' => $msg]);
        }

        return back()->with('status', $msg);
    }
}
