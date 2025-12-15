<?php

namespace App\Contracts;

/**
 * =============================================================================
 * ADAPTER PATTERN - Data Transfer Object
 * =============================================================================
 *
 * This class represents a standardized payment result that our application uses.
 * It abstracts the payment gateway's response format into our application's format.
 *
 * This ensures the client code doesn't need to know about Stripe-specific response structures.
 */
class PaymentResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly int $amount,
        public readonly string $currency,
        public readonly ?string $clientSecret = null,
        public readonly array $metadata = [],
        public readonly ?string $errorMessage = null
    ) {}

    /**
     * Check if the payment was successful.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['succeeded', 'requires_capture']);
    }

    /**
     * Check if the payment requires further action.
     */
    public function requiresAction(): bool
    {
        return $this->status === 'requires_action';
    }

    /**
     * Check if the payment is pending.
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['processing', 'requires_payment_method', 'requires_confirmation']);
    }
}
