<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $totalShops = Shop::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $trialShops = Subscription::where('status', 'trial')->count();
        $expiredSubscriptions = Subscription::whereIn('status', ['expired', 'suspended'])->count();

        $pendingApprovals = SubscriptionPayment::where('status', 'under_review')
            ->orWhere(fn ($q) => $q->where('payment_method', 'bank_transfer')->where('status', 'pending'))
            ->count();

        // Calculate Monthly Recurring Revenue (MRR) from active paid subscriptions
        $mrr = Subscription::where('status', 'active')
            ->join('subscription_plans', 'subscriptions.plan_id', '=', 'subscription_plans.id')
            ->sum('subscription_plans.monthly_price');

        $recentShops = Shop::with(['users' => fn ($q) => $q->oldest(), 'subscription.plan'])
            ->latest()
            ->limit(8)
            ->get();

        $recentPayments = SubscriptionPayment::with(['shop', 'plan'])
            ->latest()
            ->limit(8)
            ->get();

        $expiringSoon = Subscription::with(['shop', 'plan'])
            ->whereIn('status', ['active', 'trial'])
            ->where('current_period_ends_at', '<=', now()->addDays(3))
            ->where('current_period_ends_at', '>=', now())
            ->orderBy('current_period_ends_at')
            ->limit(5)
            ->get();

        $metrics = [
            'total_shops' => $totalShops,
            'active_subscriptions' => $activeSubscriptions,
            'trial_shops' => $trialShops,
            'expired_subscriptions' => $expiredSubscriptions,
            'pending_approvals' => $pendingApprovals,
            'mrr' => (float) $mrr,
            'recent_shops' => $recentShops,
            'recent_payments' => $recentPayments,
            'expiring_soon' => $expiringSoon,
        ];

        if ($request->wantsJson()) {
            return response()->json($metrics);
        }

        return view('super-admin.dashboard.index', $metrics);
    }
}

