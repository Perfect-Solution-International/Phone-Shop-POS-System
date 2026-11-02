<?php

namespace App\Services\Payment\DTO;

class GatewayCallbackResult
{
    /**
     * @param array<string, mixed> $rawPayload
     */
    public function __construct(
        public readonly bool $isSuccessful,
        public readonly string $transactionId,
        public readonly string $orderId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $status, // 'approved', 'failed', 'declined', 'cancelled'
        public readonly bool $signatureValid,
        public readonly ?string $failureReason = null,
        public readonly array $rawPayload = []
    ) {}

    public function isApproved(): bool
    {
        return $this->isSuccessful && $this->signatureValid && $this->status === 'approved';
    }
}

