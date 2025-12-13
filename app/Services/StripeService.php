<?php

namespace App\Services;

use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent.
     */
    public function createPaymentIntent(int $amount, string $currency, array $metadata = []): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'metadata' => $metadata,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    }

    /**
     * Retrieve a payment intent.
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Confirm a payment intent.
     */
    public function confirmPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        return $paymentIntent->confirm();
    }

    /**
     * Create a refund.
     */
    public function createRefund(string $paymentIntentId, ?int $amount = null): \Stripe\Refund
    {
        $params = ['payment_intent' => $paymentIntentId];

        if ($amount) {
            $params['amount'] = $amount;
        }

        return \Stripe\Refund::create($params);
    }

    /**
     * Construct webhook event.
     */
    public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );
    }
}
