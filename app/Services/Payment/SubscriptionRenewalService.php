<?php

namespace App\Services\Payment;

use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Platform\SystemActivity;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionRenewalService
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager
    ) {}

    /**
     * Process an incoming payment gateway callback/webhook.
     *
     * @param array<string, mixed> $payload
     * @return array{
     *     success: bool,
     *     code: int,
     *     message: string,
     *     duplicate: bool,
     *     transaction_id?: string,
     *     payment_id?: int,
     *     subscription_id?: int,
     *     reason?: string
     * }
     */
    public function handleGatewayCallback(array $payload, ?string $driver = null): array
    {
        $gateway = $this->gatewayManager->driver($driver);
        $result = $gateway->parseCallback($payload);

        // 1. Cryptographic Signature Verification
        if (! $result->signatureValid) {
            SystemActivity::log(
                'payment.gateway_signature_failed',
                "Payment gateway callback rejected: Invalid signature for order '{$result->orderId}'",
                isset($payload['shop_id']) ? (int) $payload['shop_id'] : null,
                null,
                [
                    'gateway' => $gateway->getIdentifier(),
                    'order_id' => $result->orderId,
                    'transaction_id' => $result->transactionId,
                ]
            );

            return [
                'success' => false,
                'code' => 400,
                'message' => 'Invalid cryptographic signature from payment gateway.',
                'duplicate' => false,
            ];
        }

        // 2. Duplicate Renewal Prevention (Idempotency Check)
        // Check if this transaction_id or order_id has already been processed and approved
        $existingApprovedPayment = SubscriptionPayment::where(function ($q) use ($result) {
            $q->where('transaction_id', $result->transactionId)
                ->orWhere('reference_number', $result->orderId);
        })
        ->where('status', 'approved')
        ->first();

        if ($existingApprovedPayment) {
            SystemActivity::log(
                'payment.duplicate_callback_ignored',
                "Duplicate payment callback ignored for transaction '{$result->transactionId}' on shop #{$existingApprovedPayment->shop_id}",
                $existingApprovedPayment->shop_id,
                null,
                [
                    'gateway' => $gateway->getIdentifier(),
                    'transaction_id' => $result->transactionId,
                    'order_id' => $result->orderId,
                    'payment_id' => $existingApprovedPayment->id,
                ]
            );

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Duplicate callback ignored. Transaction already processed and subscription renewed.',
                'duplicate' => true,
                'transaction_id' => $result->transactionId,
                'payment_id' => $existingApprovedPayment->id,
                'subscription_id' => $existingApprovedPayment->subscription_id,
            ];
        }

        // 3. Resolve Shop and Plan
        $shopId = $payload['shop_id'] ?? null;
        $planId = $payload['plan_id'] ?? null;
        $billingCycle = ($payload['billing_cycle'] ?? 'monthly') === 'annual' ? 'annual' : 'monthly';

        // Check if there was a pending payment record created during checkout initiation
        $pendingPayment = SubscriptionPayment::where('reference_number', $result->orderId)->first();
        if ($pendingPayment) {
            $shopId = $shopId ?: $pendingPayment->shop_id;
            $planId = $planId ?: $pendingPayment->plan_id;
            $billingCycle = $pendingPayment->billing_cycle ?: $billingCycle;
        }

        $shop = $shopId ? Shop::find($shopId) : null;
        $plan = $planId ? SubscriptionPlan::find($planId) : null;

        if (! $shop || ! $plan) {
            return [
                'success' => false,
                'code' => 422,
                'message' => 'Unable to resolve associated shop tenant or subscription plan from payment payload.',
                'duplicate' => false,
            ];
        }

        // 4. Handle Gateway Payment Failures & Declines
        if (! $result->isApproved()) {
            $failurePayment = SubscriptionPayment::updateOrCreate(
                ['reference_number' => $result->orderId],
                [
                    'shop_id' => $shop->id,
                    'plan_id' => $plan->id,
                    'amount' => $result->amount,
                    'payment_method' => $gateway->getIdentifier(),
                    'billing_cycle' => $billingCycle,
                    'status' => 'failed',
                    'transaction_id' => $result->transactionId,
                    'rejection_reason' => $result->failureReason ?? 'Payment declined or failed.',
                    'verified_at' => now(),
                ]
            );

            SystemActivity::log(
                'payment.gateway_failed',
                "Payment of LKR " . number_format($result->amount, 2) . " failed for shop '{$shop->name}': " . ($result->failureReason ?? 'Declined'),
                $shop->id,
                null,
                [
                    'gateway' => $gateway->getIdentifier(),
                    'transaction_id' => $result->transactionId,
                    'order_id' => $result->orderId,
                    'reason' => $result->failureReason,
                ]
            );

            return [
                'success' => false,
                'code' => 422,
                'message' => 'Payment transaction was declined or failed.',
                'reason' => $result->failureReason ?? 'Payment declined.',
                'duplicate' => false,
                'payment_id' => $failurePayment->id,
            ];
        }

        // 5. Successful Payment & Subscription Renewal
        return DB::transaction(function () use ($shop, $plan, $billingCycle, $result, $gateway) {
            $now = now();
            $currentSub = $shop->activeSubscription;

            // Calculate subscription renewal period
            $startAt = ($currentSub && $currentSub->current_period_ends_at && $currentSub->current_period_ends_at->isFuture())
                ? $currentSub->current_period_ends_at
                : $now;

            $endsAt = $billingCycle === 'annual'
                ? $startAt->copy()->addYear()
                : $startAt->copy()->addDays(30);

            // Activate or renew subscription
            $subscription = Subscription::updateOrCreate(
                ['shop_id' => $shop->id],
                [
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'billing_cycle' => $billingCycle,
                    'recurring_amount' => $result->amount,
                    'current_period_starts_at' => $now,
                    'current_period_ends_at' => $endsAt,
                    'grace_period_ends_at' => $endsAt->copy()->addDays(3),
                    'last_paid_at' => $now,
                    'cancelled_at' => null,
                ]
            );

            // Record approved payment
            $payment = SubscriptionPayment::updateOrCreate(
                ['reference_number' => $result->orderId],
                [
                    'shop_id' => $shop->id,
                    'plan_id' => $plan->id,
                    'subscription_id' => $subscription->id,
                    'amount' => $result->amount,
                    'discount_amount' => 0.00,
                    'refunded_amount' => 0.00,
                    'payment_method' => $gateway->getIdentifier(),
                    'billing_cycle' => $billingCycle,
                    'status' => 'approved',
                    'transaction_id' => $result->transactionId,
                    'verified_at' => $now,
                ]
            );

            SystemActivity::log(
                'payment.gateway_approved',
                "Gateway payment of LKR " . number_format($result->amount, 2) . " approved for shop '{$shop->name}'",
                $shop->id,
                null,
                [
                    'gateway' => $gateway->getIdentifier(),
                    'transaction_id' => $result->transactionId,
                    'order_id' => $result->orderId,
                    'payment_id' => $payment->id,
                ]
            );

            SystemActivity::log(
                'subscription.renewed',
                "Subscription for shop '{$shop->name}' renewed under plan '{$plan->name}' until " . $endsAt->format('Y-m-d'),
                $shop->id,
                null,
                [
                    'subscription_id' => $subscription->id,
                    'plan' => $plan->name,
                    'billing_cycle' => $billingCycle,
                    'period_ends_at' => $endsAt->toIso8601String(),
                ]
            );

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Payment approved and subscription renewed successfully.',
                'duplicate' => false,
                'transaction_id' => $result->transactionId,
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
            ];
        });
    }
}

