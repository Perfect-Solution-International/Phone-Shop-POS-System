<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Shop::with(['subscription.plan', 'users' => fn ($q) => $q->oldest()]);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhereHas('users', fn ($q) => $q->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            $query->whereHas('subscription', fn ($q) => $q->where('status', $status));
        }

        $shops = $query->latest()->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($shops);
        }

        return view('super-admin.shops.index', compact('shops'));
    }

    public function show(Shop $shop): View|JsonResponse
    {
        $shop->load(['users', 'subscriptions.plan', 'subscriptionPayments.plan', 'setting']);

        if (request()->wantsJson()) {
            return response()->json($shop);
        }

        return view('super-admin.shops.show', compact('shop'));
    }

    public function toggleSuspension(Shop $shop): RedirectResponse|JsonResponse
    {
        $sub = $shop->subscription;
        if ($sub) {
            $oldStatus = $sub->status;
            $newStatus = $sub->status === 'suspended' ? 'active' : 'suspended';
            $sub->update(['status' => $newStatus]);
            $msg = $newStatus === 'suspended' ? "Shop '{$shop->name}' has been suspended." : "Shop '{$shop->name}' has been reactivated.";

            \App\Models\Platform\SystemActivity::log(
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

