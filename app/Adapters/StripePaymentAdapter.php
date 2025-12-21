<?php
//  Author: Ng Ian Kai

namespace App\Adapters;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\PaymentResult;
use App\Contracts\RefundResult;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Exception;

/**
 * =============================================================================
 * ADAPTER PATTERN - Adapter Class
 * =============================================================================
 *
 * This class is the ADAPTER in the Adapter Pattern.
 * It adapts the Stripe SDK (Adaptee) to our application's PaymentGatewayInterface (Target).
 *
 * Pattern Roles:
 * - TARGET: PaymentGatewayInterface (the interface our application expects)
 * - ADAPTEE: Stripe SDK (PaymentIntent, Refund, Webhook classes)
 * - ADAPTER: This class (StripePaymentAdapter)
 * - CLIENT: PaymentController (uses the Target interface)
 *
 * How it works:
 * 1. The client (PaymentController) calls methods on PaymentGatewayInterface
 * 2. This adapter receives those calls and translates them to Stripe SDK calls
 * 3. Stripe SDK responses are converted to our standardized DTOs (PaymentResult, etc.)
 *
 * Benefits:
 * - PaymentController doesn't know about Stripe specifics
 * - Easy to swap payment gateways by creating a new adapter (e.g., PayPalAdapter)
 * - Stripe SDK changes don't affect client code, only this adapter
 *
 * @see \App\Contracts\PaymentGatewayInterface - The Target interface
 * @see \Stripe\PaymentIntent - The Adaptee (Stripe SDK)
 */
class StripePaymentAdapter implements PaymentGatewayInterface
{
    /**
     * Initialize the Stripe SDK with API key.
     *
     * This is where we configure the Adaptee (Stripe SDK).
     */
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent.
     *
     * ADAPTER METHOD: Translates our createPayment() to Stripe's PaymentIntent::create()
     *
     * @param int $amount Amount in smallest currency unit (cents)
     * @param string $currency Currency code
     * @param array $metadata Additional metadata
     * @return PaymentResult Our standardized payment result
     */
    public function createPayment(int $amount, string $currency, array $metadata = []): PaymentResult
    {
        try {
            // Call the ADAPTEE (Stripe SDK) with its specific interface
            $stripePaymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Convert Stripe's response to our TARGET interface format
            return $this->convertToPaymentResult($stripePaymentIntent);

        } catch (Exception $e) {
            // Return error result in our standardized format
            return new PaymentResult(
                id: '',
                status: 'failed',
                amount: $amount,
                currency: $currency,
                clientSecret: null,
                metadata: $metadata,
                errorMessage: $e->getMessage()
            );
        }
    }

    /**
     * Retrieve a payment by ID.
     *
     * ADAPTER METHOD: Translates our getPayment() to Stripe's PaymentIntent::retrieve()
     *
     * @param string $paymentId The Stripe Payment Intent ID
     * @return PaymentResult Our standardized payment result
     */
    public function getPayment(string $paymentId): PaymentResult
    {
        try {
            // Call the ADAPTEE
            $stripePaymentIntent = PaymentIntent::retrieve($paymentId);

            // Convert to TARGET format
            return $this->convertToPaymentResult($stripePaymentIntent);

        } catch (Exception $e) {
            return new PaymentResult(
                id: $paymentId,
                status: 'failed',
                amount: 0,
                currency: 'myr',
                errorMessage: $e->getMessage()
            );
        }
    }

    /**
     * Process a refund.
     *
     * ADAPTER METHOD: Translates our refund() to Stripe's Refund::create()
     *
     * @param string $paymentId The Stripe Payment Intent ID
     * @param int|null $amount Amount to refund (null for full refund)
     * @return RefundResult Our standardized refund result
     */
    public function refund(string $paymentId, ?int $amount = null): RefundResult
    {
        try {
            $params = ['payment_intent' => $paymentId];

            if ($amount !== null) {
                $params['amount'] = $amount;
            }

            // Call the ADAPTEE
            $stripeRefund = Refund::create($params);

            // Convert to TARGET format
            return new RefundResult(
                id: $stripeRefund->id,
                status: $stripeRefund->status,
                amount: $stripeRefund->amount,
                currency: $stripeRefund->currency,
                paymentId: $paymentId
            );

        } catch (Exception $e) {
            return new RefundResult(
                id: '',
                status: 'failed',
                amount: $amount ?? 0,
                currency: 'myr',
                paymentId: $paymentId,
                errorMessage: $e->getMessage()
            );
        }
    }

    /**
     * Helper method to convert Stripe PaymentIntent to our PaymentResult.
     *
     * This is part of the adaptation process - converting Adaptee responses
     * to Target interface format.
     *
     * @param PaymentIntent $stripePaymentIntent
     * @return PaymentResult
     */
    private function convertToPaymentResult(PaymentIntent $stripePaymentIntent): PaymentResult
    {
        return new PaymentResult(
            id: $stripePaymentIntent->id,
            status: $stripePaymentIntent->status,
            amount: $stripePaymentIntent->amount,
            currency: $stripePaymentIntent->currency,
            clientSecret: $stripePaymentIntent->client_secret,
            metadata: $stripePaymentIntent->metadata?->toArray() ?? []
        );
    }
}
