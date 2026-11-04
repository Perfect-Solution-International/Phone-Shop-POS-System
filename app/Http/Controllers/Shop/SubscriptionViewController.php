<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionViewController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->isOwnerOrAdmin(), 403, 'Unauthorized. Subscription billing is restricted to Shop Owner / Administrator.');

        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::with(['subscription.plan', 'activeSubscription.plan'])->findOrFail($shopId);

        $subscription = $shop->activeSubscription ?? $shop->subscription;
        $plans = SubscriptionPlan::where('is_active', true)->get();

        $payments = SubscriptionPayment::where('shop_id', $shop->id)
            ->with('plan')
            ->latest()
            ->paginate(10);

        $daysRemaining = 0;
        $isExpiringSoon = false;
        if ($subscription && $subscription->current_period_ends_at) {
            $daysRemaining = (int) ceil(now()->diffInDays($subscription->current_period_ends_at, false));
            $isExpiringSoon = $daysRemaining <= 7;
        }

        return view('shop.subscription.index', compact(
            'shop',
            'subscription',
            'plans',
            'payments',
            'daysRemaining',
            'isExpiringSoon'
        ));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::findOrFail($shopId);

        $data = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,annual'],
            'cardholder_name' => ['required', 'string', 'max:100'],
            'card_number' => ['required', 'string', 'min:13', 'max:19'],
            'card_expiry' => ['required', 'string', 'max:7'], // MM/YY or MM/YYYY
            'card_cvv' => ['required', 'string', 'min:3', 'max:4'],
        ]);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        $amount = $data['billing_cycle'] === 'annual' ? $plan->annual_price : $plan->monthly_price;

        $cleanCardNumber = preg_replace('/[^0-9]/', '', $data['card_number']);
        $last4 = substr($cleanCardNumber, -4);
        $transactionId = 'TXN-CARD-' . strtoupper(Str::random(12));

        // Determine subscription dates
        $now = now();
        $currentSub = $shop->activeSubscription;
        $startAt = ($currentSub && $currentSub->current_period_ends_at && $currentSub->current_period_ends_at->isFuture())
            ? $currentSub->current_period_ends_at
            : $now;

        $endsAt = $data['billing_cycle'] === 'annual'
            ? $startAt->copy()->addYear()
            : $startAt->copy()->addDays(30);

        // Update or create active subscription
        $subscription = Subscription::updateOrCreate(
            ['shop_id' => $shop->id],
            [
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => $data['billing_cycle'],
                'recurring_amount' => $amount,
                'current_period_starts_at' => $now,
                'current_period_ends_at' => $endsAt,
                'last_paid_at' => $now,
                'cancelled_at' => null,
            ]
        );

        // Record instant card payment
        SubscriptionPayment::create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'discount_amount' => 0.00,
            'refunded_amount' => 0.00,
            'payment_method' => 'card',
            'billing_cycle' => $data['billing_cycle'],
            'status' => 'approved',
            'transaction_id' => $transactionId,
            'reference_number' => 'CARD-' . $last4,
            'verified_at' => $now,
            'verified_by' => auth()->id(),
        ]);

        return redirect()->route('shop.subscription.index')
            ->with('success', "Card payment of LKR " . number_format($amount, 2) . " approved! Your {$plan->name} subscription is active until " . $endsAt->format('M d, Y') . ".");
    }

    public function bankTransfer(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::findOrFail($shopId);

        $data = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,annual'],
            'reference_number' => ['required', 'string', 'max:100'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        $amount = $data['billing_cycle'] === 'annual' ? $plan->annual_price : $plan->monthly_price;

        $receiptUrl = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $receiptUrl = '/storage/' . $path;
        }

        SubscriptionPayment::create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'amount' => $amount,
            'discount_amount' => 0.00,
            'refunded_amount' => 0.00,
            'payment_method' => 'bank_transfer',
            'billing_cycle' => $data['billing_cycle'],
            'status' => 'pending',
            'transaction_id' => 'TXN-BANK-' . strtoupper(Str::random(10)),
            'reference_number' => $data['reference_number'],
            'receipt_url' => $receiptUrl,
        ]);

        return redirect()->route('shop.subscription.index')
            ->with('success', 'Bank transfer receipt uploaded successfully. Super Admin will verify and activate your plan shortly.');
    }
}

