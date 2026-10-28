<?php

namespace App\Services\Platform;

use App\Models\Platform\Subscription;
use App\Models\Platform\SystemActivity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionLifecycleService
{
    /**
     * Process trial expirations and transitions to grace_period or expired.
     */
    public function processTrials(): array
    {
        $now = Carbon::now();
        $graceDays = (int) config('subscriptions.grace_period_days', 3);

        $trialToGrace = 0;
        $trialExpired = 0;

        // Subscriptions currently in 'trial' where trial period has ended
        $expiredTrials = Subscription::with('shop')
            ->where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', $now)
            ->get();

        foreach ($expiredTrials as $sub) {
            $shopName = $sub->shop?->name ?? "Shop #{$sub->shop_id}";

            if ($graceDays > 0) {
                // Move to grace_period
                $sub->status = 'grace_period';
                $sub->grace_period_ends_at = $now->copy()->addDays($graceDays);
                $sub->save();

                SystemActivity::log(
                    'subscription.trial_expired_grace',
                    "Shop '{$shopName}' free trial ended. Placed in {$graceDays}-day grace period until {$sub->grace_period_ends_at->toDateString()}.",
                    $sub->shop_id,
                    null,
                    [
                        'previous_status' => 'trial',
                        'new_status' => 'grace_period',
                        'grace_period_ends_at' => $sub->grace_period_ends_at->toIso8601String(),
                    ]
                );

                $trialToGrace++;
            } else {
                // Immediate expiration
                $sub->status = 'expired';
                $sub->save();

                SystemActivity::log(
                    'subscription.trial_expired',
                    "Shop '{$shopName}' free trial ended. Subscription expired.",
                    $sub->shop_id,
                    null,
                    ['previous_status' => 'trial', 'new_status' => 'expired']
                );

                $trialExpired++;
            }
        }

        // Subscriptions in 'grace_period' originating from trial where grace period has ended
        $lapsedGraceSubscriptions = Subscription::with('shop')
            ->where('status', 'grace_period')
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '<=', $now)
            ->whereNotNull('trial_ends_at')
            ->whereNull('last_paid_at')
            ->get();

        foreach ($lapsedGraceSubscriptions as $sub) {
            $shopName = $sub->shop?->name ?? "Shop #{$sub->shop_id}";
            $sub->status = 'expired';
            $sub->save();

            SystemActivity::log(
                'subscription.trial_expired',
                "Shop '{$shopName}' trial grace period elapsed. Subscription is now expired.",
                $sub->shop_id,
                null,
                ['previous_status' => 'grace_period', 'new_status' => 'expired']
            );

            $trialExpired++;
        }

        return [
            'trial_to_grace' => $trialToGrace,
            'trial_expired' => $trialExpired,
        ];
    }

    /**
     * Process active subscriptions whose billing period has ended.
     */
    public function processRenewals(): array
    {
        $now = Carbon::now();
        $graceDays = (int) config('subscriptions.grace_period_days', 3);

        $renewalToGrace = 0;
        $renewalExpired = 0;

        // Active subscriptions whose period has ended without renewal payment
        $overdueSubscriptions = Subscription::with('shop')
            ->where('status', 'active')
            ->whereNotNull('current_period_ends_at')
            ->where('current_period_ends_at', '<=', $now)
            ->get();

        foreach ($overdueSubscriptions as $sub) {
            $shopName = $sub->shop?->name ?? "Shop #{$sub->shop_id}";

            if ($graceDays > 0) {
                $sub->status = 'grace_period';
                $sub->grace_period_ends_at = $now->copy()->addDays($graceDays);
                $sub->save();

                SystemActivity::log(
                    'subscription.renewal_overdue_grace',
                    "Shop '{$shopName}' billing period ended without renewal. Placed in {$graceDays}-day grace period.",
                    $sub->shop_id,
                    null,
                    [
                        'previous_status' => 'active',
                        'new_status' => 'grace_period',
                        'grace_period_ends_at' => $sub->grace_period_ends_at->toIso8601String(),
                    ]
                );

                $renewalToGrace++;
            } else {
                $sub->status = 'expired';
                $sub->save();

                SystemActivity::log(
                    'subscription.expired',
                    "Shop '{$shopName}' billing period ended. Subscription marked expired.",
                    $sub->shop_id,
                    null,
                    ['previous_status' => 'active', 'new_status' => 'expired']
                );

                $renewalExpired++;
            }
        }

        // Paid subscriptions in grace_period whose grace period has now elapsed
        $lapsedRenewals = Subscription::with('shop')
            ->where('status', 'grace_period')
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '<=', $now)
            ->whereNotNull('last_paid_at')
            ->get();

        foreach ($lapsedRenewals as $sub) {
            $shopName = $sub->shop?->name ?? "Shop #{$sub->shop_id}";
            $sub->status = 'expired';
            $sub->save();

            SystemActivity::log(
                'subscription.expired',
                "Shop '{$shopName}' grace period ended without payment renewal. Subscription marked expired.",
                $sub->shop_id,
                null,
                ['previous_status' => 'grace_period', 'new_status' => 'expired']
            );

            $renewalExpired++;
        }

        return [
            'renewal_to_grace' => $renewalToGrace,
            'renewal_expired' => $renewalExpired,
        ];
    }

    /**
     * Dispatch renewal reminder notifications for subscriptions expiring soon.
     */
    public function processRenewalNotices(): array
    {
        $now = Carbon::now();
        $noticeDays = (int) config('subscriptions.renewal_notice_days', 3);
        $windowEnd = $now->copy()->addDays($noticeDays);

        $noticesSent = 0;

        $expiringSoon = Subscription::with('shop')
            ->where('status', 'active')
            ->whereNotNull('current_period_ends_at')
            ->whereBetween('current_period_ends_at', [$now, $windowEnd])
            ->get();

        foreach ($expiringSoon as $sub) {
            $shopName = $sub->shop?->name ?? "Shop #{$sub->shop_id}";

            // Ensure we don't log duplicate notices on the same day for this subscription
            $recentNotice = SystemActivity::where('action', 'subscription.renewal_notice')
                ->where('shop_id', $sub->shop_id)
                ->where('created_at', '>=', $now->copy()->subHours(24))
                ->exists();

            if (! $recentNotice) {
                SystemActivity::log(
                    'subscription.renewal_notice',
                    "Upcoming subscription renewal notice for Shop '{$shopName}'. Expires on {$sub->current_period_ends_at->toDateString()}.",
                    $sub->shop_id,
                    null,
                    [
                        'current_period_ends_at' => $sub->current_period_ends_at->toIso8601String(),
                        'days_remaining' => (int) $now->diffInDays($sub->current_period_ends_at, false),
                    ]
                );

                $noticesSent++;
            }
        }

        return [
            'renewal_notices' => $noticesSent,
        ];
    }

    /**
     * Run all subscription lifecycle processes.
     */
    public function processAll(): array
    {
        $trialResults = $this->processTrials();
        $renewalResults = $this->processRenewals();
        $noticeResults = $this->processRenewalNotices();

        $merged = array_merge($trialResults, $renewalResults, $noticeResults);
        $merged['total_actions'] = array_sum($merged);

        Log::info('Subscription lifecycle processing completed.', $merged);

        return $merged;
    }
}

