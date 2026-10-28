<?php

namespace App\Services\Payment\Adapters;

use App\Models\Platform\Shop;
use App\Models\Platform\SubscriptionPlan;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTO\GatewayCallbackResult;
use Illuminate\Support\Str;

class SandboxPaymentGatewayAdapter implements PaymentGatewayInterface
{
    protected string $merchantId;
    protected string $secret;
    protected string $currency;

    public function __construct(?string $merchantId = null, ?string $secret = null, ?string $currency = null)
    {
        $this->merchantId = $merchantId ?? config('payment.gateways.sandbox.merchant_id', 'SANDBOX_MERCHANT_001');
        $this->secret = $secret ?? config('payment.gateways.sandbox.secret', 'sandbox-secret-key-phone-pos-system-secure-token');
        $this->currency = $currency ?? config('payment.currency', 'LKR');
    }

    public function getIdentifier(): string
    {
        return 'sandbox';
    }

    public function initiateSubscriptionCheckout(
        Shop $shop,
        SubscriptionPlan $plan,
        string $billingCycle,
        string $returnUrl,
        string $cancelUrl,
        string $notifyUrl
    ): array {
        $amount = $billingCycle === 'annual' ? (float) $plan->annual_price : (float) $plan->monthly_price;
        $orderId = sprintf('SUB-%d-%d-%s', $shop->id, now()->timestamp, strtoupper(Str::random(6)));

        $params = [
            'merchant_id' => $this->merchantId,
            'order_id' => $orderId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $this->currency,
            'shop_id' => $shop->id,
            'shop_name' => $shop->name,
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'billing_cycle' => $billingCycle,
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'notify_url' => $notifyUrl,
        ];

        $signature = $this->generateSignature($params);
        $params['signature'] = $signature;

        return [
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $this->currency,
            'checkout_url' => route('shop.subscription.gateway.sandbox-checkout', ['order_id' => $orderId]),
            'params' => $params,
            'signature' => $signature,
        ];
    }

    public function generateSignature(array $params): string
    {
        $orderId = (string) ($params['order_id'] ?? '');
        $amount = number_format((float) ($params['amount'] ?? 0), 2, '.', '');
        $currency = (string) ($params['currency'] ?? $this->currency);
        $merchantId = (string) ($params['merchant_id'] ?? $this->merchantId);

        $payload = "{$orderId}|{$amount}|{$currency}|{$merchantId}";

        return hash_hmac('sha256', $payload, $this->secret);
    }

    public function verifyCallbackSignature(array $payload): bool
    {
        $signature = $payload['signature'] ?? null;
        if (! $signature || ! is_string($signature)) {
            return false;
        }

        $expected = $this->generateSignature($payload);

        return hash_equals($expected, $signature);
    }

    public function parseCallback(array $payload): GatewayCallbackResult
    {
        $signatureValid = $this->verifyCallbackSignature($payload);

        $transactionId = (string) ($payload['transaction_id'] ?? 'TXN-GW-' . strtoupper(Str::random(10)));
        $orderId = (string) ($payload['order_id'] ?? '');
        $amount = (float) ($payload['amount'] ?? 0.0);
        $currency = (string) ($payload['currency'] ?? $this->currency);
        $rawStatus = strtolower((string) ($payload['status'] ?? 'pending'));

        $isSuccessful = in_array($rawStatus, ['success', 'approved', 'paid', 'completed'], true)
            || ((string) ($payload['status_code'] ?? '') === '2');

        $status = $isSuccessful ? 'approved' : ($rawStatus === 'cancelled' ? 'cancelled' : 'failed');
        $failureReason = ! $isSuccessful
            ? ($payload['failure_reason'] ?? $payload['error_message'] ?? 'Payment declined by sandbox gateway.')
            : null;

        return new GatewayCallbackResult(
            isSuccessful: $isSuccessful,
            transactionId: $transactionId,
            orderId: $orderId,
            amount: $amount,
            currency: $currency,
            status: $status,
            signatureValid: $signatureValid,
            failureReason: $failureReason,
            rawPayload: $payload
        );
    }
}

