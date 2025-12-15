<?php

namespace App\Contracts;

/**
 * =============================================================================
 * ADAPTER PATTERN - Data Transfer Object
 * =============================================================================
 *
 * This class represents a standardized refund result that our application uses.
 * It abstracts the payment gateway's refund response format.
 */
class RefundResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $paymentId,
        public readonly ?string $errorMessage = null
    ) {}

    /**
     * Check if the refund was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'succeeded';
    }

    /**
     * Check if the refund is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
