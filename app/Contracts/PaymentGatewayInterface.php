<?php
//  Author: Ng Ian Kai

namespace App\Contracts;

/**
 * =============================================================================
 * ADAPTER PATTERN - Target Interface
 * =============================================================================
 *
 * This interface defines the domain-specific interface that the client (PaymentController) uses.
 * It abstracts payment operations that our application needs, regardless of which
 * payment gateway is used (Stripe, PayPal, etc.)
 *
 * Pattern Role: TARGET
 * - Defines the domain-specific interface that the client uses
 * - The client (PaymentController) works with this interface, not directly with Stripe
 *
 * @see \App\Adapters\StripePaymentAdapter - The Adapter that implements this interface
 */
interface PaymentGatewayInterface
{
    /**
     * Create a payment intent/transaction.
     *
     * @param int $amount Amount in smallest currency unit (e.g., cents)
     * @param string $currency Currency code (e.g., 'myr')
     * @param array $metadata Additional metadata for the payment
     * @return PaymentResult
     */
    public function createPayment(int $amount, string $currency, array $metadata = []): PaymentResult;

    /**
     * Retrieve payment details by ID.
     *
     * @param string $paymentId The payment identifier
     * @return PaymentResult
     */
    public function getPayment(string $paymentId): PaymentResult;

    /**
     * Process a refund for a payment.
     *
     * @param string $paymentId The payment identifier
     * @param int|null $amount Amount to refund (null for full refund)
     * @return RefundResult
     */
    public function refund(string $paymentId, ?int $amount = null): RefundResult;
}
