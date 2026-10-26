<?php

namespace App\Services\Platform;

use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SystemActivity;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SuperAdminMetricsService
{
    /**
     * Get the primary 6 dashboard KPI metrics.
     *
     * @return array{
     *     total_shops: int,
     *     active_subscriptions: int,
     *     trial_shops: int,
     *     expired_subscriptions: int,
     *     pending_payments_count: int,
     *     pending_payments_amount: float,
     *     mrr: float,
     *     cash_received_this_month: float
     * }
     */
    public function getDashboardStatistics(): array
    {
        $now = now();

        // 1. Total Registered Shops
        $totalShops = Shop::count();

        // 2. Active Subscriptions: Count shops with a currently valid, paid, active subscription.
        // Excludes free trials, expired subscriptions, and unpaid/failed subscriptions.
        $activeShopIds = Subscription::where('status', 'active')
            ->where('current_period_ends_at', '>=', $now)
            ->whereHas('shop')
            ->pluck('shop_id')
            ->unique();

        $activeSubscriptions = $activeShopIds->count();

        // 3. Trial Shops: Count shops currently within their 7-day free trial period.
        $trialShops = Subscription::where('status', 'trial')
            ->where(function ($q) use ($now): void {
                $q->where('trial_ends_at', '>=', $now)
                    ->orWhere('current_period_ends_at', '>=', $now);
            })
            ->whereHas('shop')
            ->whereNotIn('shop_id', $activeShopIds)
            ->distinct('shop_id')
            ->count('shop_id');

        // 4. Expired Subscriptions: Count paid subscriptions whose validity period has ended
        // and are not currently covered by another valid subscription.
        $expiredSubscriptions = Subscription::where(function ($q) use ($now): void {
            $q->whereIn('status', ['expired', 'suspended'])
                ->orWhere(function ($subQ) use ($now): void {
                    $subQ->where('status', 'active')
                        ->where('current_period_ends_at', '<', $now);
                });
        })
            ->whereNotIn('shop_id', $activeShopIds)
            ->whereHas('shop')
            ->distinct('shop_id')
            ->count('shop_id');

        // 5. Pending Payments: Count bank-transfer payments awaiting admin verification.
        $pendingPaymentsQuery = SubscriptionPayment::where('payment_method', 'bank_transfer')
            ->whereIn('status', ['pending', 'under_review']);

        $pendingPaymentsCount = (int) $pendingPaymentsQuery->count();
        $pendingPaymentsAmount = (float) $pendingPaymentsQuery->sum('amount');

        // 6. Monthly Recurring Revenue (MRR)
        $mrr = $this->calculateMrr();

        // Cash Received This Month (Gross approved payments in current month minus refunds in current month)
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $approvedCashThisMonth = (float) SubscriptionPayment::where('status', 'approved')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $refundsThisMonth = (float) SubscriptionPayment::whereBetween('refunded_at', [$startOfMonth, $endOfMonth])
            ->sum('refunded_amount');

        $cashReceivedThisMonth = max(0.0, $approvedCashThisMonth - $refundsThisMonth);

        return [
            'total_shops' => $totalShops,
            'active_subscriptions' => $activeSubscriptions,
            'trial_shops' => $trialShops,
            'expired_subscriptions' => $expiredSubscriptions,
            'pending_payments_count' => $pendingPaymentsCount,
            'pending_payments_amount' => $pendingPaymentsAmount,
            'mrr' => $mrr,
            'cash_received_this_month' => round($cashReceivedThisMonth, 2),
        ];
    }

    /**
     * Calculate Monthly Recurring Revenue (MRR) strictly adhering to SaaS financial rules:
     * - Exclude free trials from MRR.
     * - Exclude failed and pending payments from recognized recurring revenue.
     * - Normalize annual subscription prices to monthly equivalents (annual / 12).
     * - Apply discounts using the actual recurring price charged.
     * - Handle partial refunds and full refunds consistently.
     * - Avoid double-counting subscriptions across multiple renewals for the same shop.
     * - Decimal precision rounded to 2 places.
     */
    public function calculateMrr(): float
    {
        $now = now();

        // Retrieve active, non-trial subscriptions with an end date in the future
        $subscriptions = Subscription::with(['plan', 'shop'])
            ->where('status', 'active')
            ->where('current_period_ends_at', '>=', $now)
            ->whereHas('shop')
            ->get();

        // Group by shop_id to prevent any double-counting; take the subscription with the furthest end date
        $uniqueSubscriptions = $subscriptions
            ->groupBy('shop_id')
            ->map(function ($subs) {
                return $subs->sortByDesc('current_period_ends_at')->first();
            });

        $mrrTotal = 0.00;

        foreach ($uniqueSubscriptions as $sub) {
            /** @var Subscription $sub */
            // Exclude free trials, suspended or cancelled accounts
            if ($sub->status === 'trial' || $sub->cancelled_at !== null) {
                continue;
            }

            // 1. Direct explicit recurring_amount if defined on subscription
            if ($sub->recurring_amount !== null && (float) $sub->recurring_amount > 0) {
                $amount = (float) $sub->recurring_amount;
                if ($sub->billing_cycle === 'annual') {
                    $mrrTotal += ($amount / 12.0);
                } else {
                    $mrrTotal += $amount;
                }
                continue;
            }

            // 2. Derive from plan pricing with discount support
            $plan = $sub->plan;
            if (! $plan) {
                continue;
            }

            $discount = (float) ($sub->discount_amount ?? 0.0);

            if ($sub->billing_cycle === 'annual') {
                $annualPrice = (float) ($plan->annual_price ?: ($plan->monthly_price * 12.0));
                $effectiveAnnual = max(0.0, $annualPrice - $discount);
                $mrrTotal += ($effectiveAnnual / 12.0);
            } else {
                $monthlyPrice = (float) $plan->monthly_price;
                $effectiveMonthly = max(0.0, $monthlyPrice - $discount);
                $mrrTotal += $effectiveMonthly;
            }
        }

        return round($mrrTotal, 2);
    }

    /**
     * Get Payment Analytics data with date filtering and monthly trend charts.
     */
    public function getPaymentAnalytics(string $dateRange = 'all'): array
    {
        $query = SubscriptionPayment::query();

        // Apply date range filter
        $now = now();
        $dateFilterLabel = 'All Time';

        switch ($dateRange) {
            case 'today':
                $query->whereBetween('created_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()]);
                $dateFilterLabel = 'Today';
                break;
            case 'this_month':
                $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                $dateFilterLabel = 'This Month';
                break;
            case 'last_30_days':
                $query->whereBetween('created_at', [$now->copy()->subDays(30)->startOfDay(), $now]);
                $dateFilterLabel = 'Last 30 Days';
                break;
            case 'this_year':
                $query->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
                $dateFilterLabel = 'This Year';
                break;
            default:
                $dateFilterLabel = 'All Time';
                break;
        }

        $allPayments = (clone $query)->get();

        // 1. Successful payments (approved)
        $successfulPayments = $allPayments->where('status', 'approved');
        $successfulCount = $successfulPayments->count();
        $successfulGross = (float) $successfulPayments->sum('amount');
        $successfulRefunded = (float) $successfulPayments->sum('refunded_amount');
        $successfulNet = max(0.0, $successfulGross - $successfulRefunded);

        // 2. Pending payments (pending, under_review)
        $pendingPayments = $allPayments->whereIn('status', ['pending', 'under_review']);
        $pendingCount = $pendingPayments->count();
        $pendingAmount = (float) $pendingPayments->sum('amount');

        // 3. Failed / Rejected payments
        $failedPayments = $allPayments->where('status', 'rejected');
        $failedCount = $failedPayments->count();
        $failedAmount = (float) $failedPayments->sum('amount');

        // 4. Refunded payments (status is refunded OR refunded_amount > 0)
        $refundedPayments = $allPayments->filter(function ($p) {
            return $p->status === 'refunded' || (float) $p->refunded_amount > 0;
        });
        $refundedCount = $refundedPayments->count();
        $refundedAmount = (float) $refundedPayments->sum(function ($p) {
            return (float) ($p->refunded_amount > 0 ? $p->refunded_amount : $p->amount);
        });

        // 5. Monthly Trends (Last 6 months) for chart visualization
        $monthlyTrends = $this->buildMonthlyPaymentTrends();

        $totalTransactions = $successfulCount + $pendingCount + $failedCount;

        return [
            'date_range' => $dateRange,
            'date_label' => $dateFilterLabel,
            'successful' => [
                'count' => $successfulCount,
                'gross_amount' => round($successfulGross, 2),
                'refunded_amount' => round($successfulRefunded, 2),
                'net_amount' => round($successfulNet, 2),
            ],
            'pending' => [
                'count' => $pendingCount,
                'amount' => round($pendingAmount, 2),
            ],
            'failed' => [
                'count' => $failedCount,
                'amount' => round($failedAmount, 2),
            ],
            'refunded' => [
                'count' => $refundedCount,
                'amount' => round($refundedAmount, 2),
            ],
            'total_transactions' => $totalTransactions,
            'monthly_trends' => $monthlyTrends,
        ];
    }

    /**
     * Build 6-month historical trend data for payment analytics chart.
     */
    protected function buildMonthlyPaymentTrends(): array
    {
        $months = [];
        $now = now();

        $maxMonthlyVolume = 1.0; // Avoid division by zero in charts

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $start = $monthDate->copy()->startOfMonth();
            $end = $monthDate->copy()->endOfMonth();

            $monthPayments = SubscriptionPayment::whereBetween('created_at', [$start, $end])->get();

            $approvedAmount = (float) $monthPayments->where('status', 'approved')->sum('amount');
            $pendingAmount = (float) $monthPayments->whereIn('status', ['pending', 'under_review'])->sum('amount');
            $failedAmount = (float) $monthPayments->where('status', 'rejected')->sum('amount');
            $refundedAmount = (float) $monthPayments->sum('refunded_amount');

            $monthTotal = $approvedAmount + $pendingAmount;
            if ($monthTotal > $maxMonthlyVolume) {
                $maxMonthlyVolume = $monthTotal;
            }

            $months[] = [
                'label' => $monthDate->format('M Y'),
                'short_label' => $monthDate->format('M'),
                'year' => $monthDate->format('Y'),
                'approved_amount' => round($approvedAmount, 2),
                'pending_amount' => round($pendingAmount, 2),
                'failed_amount' => round($failedAmount, 2),
                'refunded_amount' => round($refundedAmount, 2),
                'net_approved' => round(max(0.0, $approvedAmount - $refundedAmount), 2),
                'total_volume' => round($monthTotal, 2),
            ];
        }

        return [
            'months' => $months,
            'max_volume' => round($maxMonthlyVolume, 2),
        ];
    }

    /**
     * Get paginated recent shop registrations.
     */
    public function getRecentRegistrations(int $perPage = 8): LengthAwarePaginator
    {
        return Shop::with([
            'users' => fn ($q) => $q->oldest(),
            'subscription.plan',
        ])
            ->latest()
            ->paginate($perPage, ['*'], 'shops_page')
            ->withQueryString();
    }

    /**
     * Get subscriptions expiring within the next 7 days.
     * Excludes already expired and excludes shops covered by a newer active subscription.
     */
    public function getSubscriptionExpiryAlerts(int $days = 7): Collection
    {
        $now = now();
        $threshold = $now->copy()->addDays($days);

        // Find active shops with future coverage beyond threshold
        $coveredShopIds = Subscription::where('status', 'active')
            ->where('current_period_ends_at', '>', $threshold)
            ->pluck('shop_id');

        return Subscription::with(['shop', 'plan'])
            ->whereIn('status', ['active', 'trial', 'grace_period'])
            ->where('current_period_ends_at', '>=', $now)
            ->where('current_period_ends_at', '<=', $threshold)
            ->whereNotIn('shop_id', $coveredShopIds)
            ->orderBy('current_period_ends_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($sub) use ($now) {
                $hoursLeft = $now->diffInHours($sub->current_period_ends_at, false);
                $daysLeft = (int) max(0, ceil($hoursLeft / 24.0));
                $sub->remaining_days = $daysLeft;

                return $sub;
            });
    }

    /**
     * Get recent administrative and system activity logs.
     */
    public function getRecentActivities(int $limit = 10): Collection
    {
        return SystemActivity::with(['user', 'shop'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}

