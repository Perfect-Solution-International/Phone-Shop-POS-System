<?php

namespace App\Services\Payment\Contracts;

use App\Models\Platform\Shop;
use App\Models\Platform\SubscriptionPlan;
use App\Services\Payment\DTO\GatewayCallbackResult;

interface PaymentGatewayInterface
{
    /**
     * Unique identifier for the gateway adapter (e.g. 'sandbox', 'payhere', 'stripe').
     */
    public function getIdentifier(): string;

    /**
     * Initiate a subscription payment checkout session.
     *
     * @return array{
     *     order_id: string,
     *     amount: float,
     *     currency: string,
     *     checkout_url: string,
     *     params: array<string, mixed>,
     *     signature: string
     * }
     */
    public function initiateSubscriptionCheckout(
        Shop $shop,
        SubscriptionPlan $plan,
        string $billingCycle,
        string $returnUrl,
        string $cancelUrl,
        string $notifyUrl
    ): array;

    /**
     * Generate the cryptographic HMAC/hash signature for outgoing or incoming payloads.
     */
    public function generateSignature(array $params): string;

    /**
     * Verify whether an incoming webhook/callback signature matches the expected hash.
     */
    public function verifyCallbackSignature(array $payload): bool;

    /**
     * Parse raw callback payload into a typed, verified GatewayCallbackResult DTO.
     */
    public function parseCallback(array $payload): GatewayCallbackResult;
}

