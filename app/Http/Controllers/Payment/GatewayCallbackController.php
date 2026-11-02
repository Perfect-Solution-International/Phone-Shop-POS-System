<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Platform\Shop;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use App\Services\Payment\PaymentGatewayManager;
use App\Services\Payment\SubscriptionRenewalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewayCallbackController extends Controller
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected SubscriptionRenewalService $renewalService
    ) {}

    /**
     * Webhook / server-to-server callback endpoint.
     */
    public function handleCallback(Request $request, ?string $gateway = null): JsonResponse
    {
        $payload = $request->all();
        $response = $this->renewalService->handleGatewayCallback($payload, $gateway);

        return response()->json($response, $response['code'] ?? 200);
    }

    /**
     * Initiate online gateway checkout session for a shop tenant.
     */
    public function initiate(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,annual'],
            'gateway' => ['nullable', 'string'],
        ]);

        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::findOrFail($shopId);
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $gateway = $this->gatewayManager->driver($validated['gateway'] ?? null);

        $returnUrl = route('shop.subscription.gateway.return');
        $cancelUrl = route('shop.subscription.gateway.cancel');
        $notifyUrl = route('api.v1.payments.gateway.callback', ['gateway' => $gateway->getIdentifier()]);

        $checkoutData = $gateway->initiateSubscriptionCheckout(
            $shop,
            $plan,
            $validated['billing_cycle'],
            $returnUrl,
            $cancelUrl,
            $notifyUrl
        );

        // Store a pending payment record
        SubscriptionPayment::create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'amount' => $checkoutData['amount'],
            'discount_amount' => 0.00,
            'refunded_amount' => 0.00,
            'payment_method' => $gateway->getIdentifier(),
            'billing_cycle' => $validated['billing_cycle'],
            'status' => 'pending',
            'reference_number' => $checkoutData['order_id'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutData['checkout_url'],
                'data' => $checkoutData,
            ]);
        }

        return redirect($checkoutData['checkout_url']);
    }

    /**
     * Render sandbox checkout simulation page.
     */
    public function sandboxCheckout(Request $request): View|RedirectResponse
    {
        $orderId = $request->query('order_id');
        $payment = SubscriptionPayment::with(['shop', 'plan'])
            ->where('reference_number', $orderId)
            ->first();

        if (! $payment) {
            return redirect()->route('shop.subscription.index')
                ->with('error', 'Invalid or expired sandbox checkout session.');
        }

        $gateway = $this->gatewayManager->driver('sandbox');

        return view('shop.subscription.sandbox-checkout', compact('payment', 'gateway'));
    }

    /**
     * Endpoint to simulate a webhook callback directly from sandbox UI.
     */
    public function simulateSandboxAction(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string'],
            'action' => ['required', 'in:success,failure,tampered_signature,duplicate'],
        ]);

        $payment = SubscriptionPayment::with(['shop', 'plan'])
            ->where('reference_number', $validated['order_id'])
            ->firstOrFail();

        $gateway = $this->gatewayManager->driver('sandbox');
        $amount = (float) $payment->amount;

        $params = [
            'merchant_id' => config('payment.gateways.sandbox.merchant_id', 'SANDBOX_MERCHANT_001'),
            'order_id' => $payment->reference_number,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => config('payment.currency', 'LKR'),
            'shop_id' => $payment->shop_id,
            'plan_id' => $payment->plan_id,
            'billing_cycle' => $payment->billing_cycle,
            'transaction_id' => 'TXN-SANDBOX-' . strtoupper(\Illuminate\Support\Str::random(10)),
        ];

        switch ($validated['action']) {
            case 'success':
                $params['status'] = 'approved';
                $params['status_code'] = '2';
                $params['signature'] = $gateway->generateSignature($params);
                break;

            case 'failure':
                $params['status'] = 'failed';
                $params['status_code'] = '0';
                $params['failure_reason'] = 'Simulated card declined: Insufficient funds in sandbox card.';
                $params['signature'] = $gateway->generateSignature($params);
                break;

            case 'tampered_signature':
                $params['status'] = 'approved';
                $params['status_code'] = '2';
                $params['signature'] = 'invalid_tampered_fake_signature_hash';
                break;

            case 'duplicate':
                // Send the exact same transaction ID as a previously approved payment
                $lastApproved = SubscriptionPayment::where('shop_id', $payment->shop_id)
                    ->where('status', 'approved')
                    ->whereNotNull('transaction_id')
                    ->latest()
                    ->first();

                if ($lastApproved) {
                    $params['transaction_id'] = $lastApproved->transaction_id;
                    $params['order_id'] = $lastApproved->reference_number;
                }
                $params['status'] = 'approved';
                $params['status_code'] = '2';
                $params['signature'] = $gateway->generateSignature($params);
                break;
        }

        $result = $this->renewalService->handleGatewayCallback($params, 'sandbox');

        if ($request->wantsJson()) {
            return response()->json($result, $result['code'] ?? 200);
        }

        if ($result['success']) {
            return redirect()->route('shop.subscription.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('shop.subscription.index')
            ->with('error', $result['message'] . (isset($result['reason']) ? " ({$result['reason']})" : ''));
    }

    /**
     * Return landing page after customer completes payment.
     */
    public function handleReturn(Request $request): RedirectResponse
    {
        return redirect()->route('shop.subscription.index')
            ->with('success', 'Thank you! Your payment is being verified and your subscription has been updated.');
    }

    /**
     * Cancel landing page.
     */
    public function handleCancel(Request $request): RedirectResponse
    {
        return redirect()->route('shop.subscription.index')
            ->with('info', 'Subscription checkout was cancelled. No charges were made.');
    }
}

