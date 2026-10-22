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

        $msg = "Payment for shop '{$payment->shop->name}' was rejected.";

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg]);
        }

        return back()->with('status', $msg);
    }
}

