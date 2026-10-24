<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = SubscriptionPayment::with(['shop', 'plan', 'verifier']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($method = $request->input('method')) {
            $query->where('payment_method', $method);
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($payments);
        }

        return view('super-admin.payments.index', compact('payments'));
    }

    public function approvals(Request $request): View|JsonResponse
    {
        $pendingPayments = SubscriptionPayment::with(['shop', 'plan'])
            ->where('payment_method', 'bank_transfer')
            ->whereIn('status', ['pending', 'under_review'])
            ->latest()
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($pendingPayments);
        }

        return view('super-admin.payments.approvals', compact('pendingPayments'));
    }

    public function approve(SubscriptionPayment $payment): RedirectResponse|JsonResponse
    {
        abort_if($payment->status === 'approved', 422, 'Payment is already approved.');

        $payment->update([
            'status' => 'approved',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        // Activate / Extend the shop's subscription
        $shop = $payment->shop;
        $now = now();
        $endsAt = $now->copy()->addDays(30);

        $subscription = Subscription::updateOrCreate(
            ['shop_id' => $shop->id],
            [
                'plan_id' => $payment->plan_id,
                'status' => 'active',
                'current_period_starts_at' => $now,
                'current_period_ends_at' => $endsAt,
                'grace_period_ends_at' => $endsAt->copy()->addDays(3),
            ]
        );

        $payment->update(['subscription_id' => $subscription->id]);

        \App\Models\Platform\SystemActivity::log(
            'payment.approved',
            "Approved payment of LKR " . number_format($payment->amount, 2) . " for shop '{$shop->name}'",
            $shop->id,
            auth()->id(),
            [
                'payment_id' => $payment->id,
                'amount' => (float) $payment->amount,
                'plan_id' => $payment->plan_id,
            ]
        );

        \App\Models\Platform\SystemActivity::log(
            'subscription.activated',
            "Subscription activated for shop '{$shop->name}' under plan '{$payment->plan->name}'",
            $shop->id,
            auth()->id(),
            [
                'subscription_id' => $subscription->id,
                'plan' => $payment->plan->name,
                'current_period_ends_at' => $endsAt->toIso8601String(),
            ]
        );

        $msg = "Payment of LKR ".number_format($payment->amount, 2)." approved. Subscription activated for '{$shop->name}'.";

        if (request()->wantsJson()) {
            return response()->json(['message' => $msg, 'subscription' => $subscription]);
        }

        return back()->with('status', $msg);
    }

    public function reject(Request $request, SubscriptionPayment $payment): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $payment->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        \App\Models\Platform\SystemActivity::log(
            'payment.rejected',
            "Rejected payment of LKR " . number_format($payment->amount, 2) . " for shop '{$payment->shop->name}'",
            $payment->shop_id,
            auth()->id(),
            [
                'payment_id' => $payment->id,
                'reason' => $validated['rejection_reason'],
            ]
        );

        $msg = "Payment for shop '{$payment->shop->name}' was rejected.";

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg]);
        }

        return back()->with('status', $msg);
    }

    public function refund(Request $request, SubscriptionPayment $payment): RedirectResponse|JsonResponse
    {
        abort_unless($payment->status === 'approved', 422, 'Only approved payments can be refunded.');

        $maxRefundable = max(0.0, (float) $payment->amount - (float) $payment->refunded_amount);
        abort_if($maxRefundable <= 0.0, 422, 'This payment is already fully refunded.');

        $validated = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01', "max:{$maxRefundable}"],
            'refund_reason' => ['required', 'string', 'max:500'],
        ]);

        $newRefundedAmount = round((float) $payment->refunded_amount + (float) $validated['refund_amount'], 2);
        $isFullRefund = $newRefundedAmount >= (float) $payment->amount;

        $payment->update([
            'refunded_amount' => $newRefundedAmount,
            'refunded_at' => now(),
            'refund_reason' => $validated['refund_reason'],
            'status' => $isFullRefund ? 'refunded' : 'approved',
        ]);

        \App\Models\Platform\SystemActivity::log(
            'payment.refunded',
            "Processed " . ($isFullRefund ? 'full' : 'partial') . " refund of LKR " . number_format($validated['refund_amount'], 2) . " for payment #{$payment->id} (Shop '{$payment->shop->name}')",
            $payment->shop_id,
            auth()->id(),
            [
                'payment_id' => $payment->id,
                'refund_amount' => (float) $validated['refund_amount'],
                'total_refunded' => $newRefundedAmount,
                'reason' => $validated['refund_reason'],
                'is_full_refund' => $isFullRefund,
            ]
        );

        $msg = "Refund of LKR " . number_format($validated['refund_amount'], 2) . " processed successfully.";

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg, 'payment' => $payment]);
        }

        return back()->with('status', $msg);
    }
}

