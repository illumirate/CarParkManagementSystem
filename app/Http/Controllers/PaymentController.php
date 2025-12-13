<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\CreditService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected StripeService $stripeService;
    protected CreditService $creditService;

    public function __construct(StripeService $stripeService, CreditService $creditService)
    {
        $this->stripeService = $stripeService;
        $this->creditService = $creditService;
    }

    /**
     * Display credit purchase page.
     */
    public function index(): View
    {
        $packages = $this->getCreditPackages();

        return view('payments.credits', [
            'packages' => $packages,
            'stripeKey' => config('services.stripe.key'),
            'currentBalance' => Auth::user()->credit_balance,
        ]);
    }

    /**
     * Create Stripe payment intent (AJAX).
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'package' => 'required|string',
        ]);

        $packages = $this->getCreditPackages();

        if (!isset($packages[$request->package])) {
            return response()->json(['error' => 'Invalid package selected.'], 400);
        }

        $package = $packages[$request->package];

        try {
            $paymentIntent = $this->stripeService->createPaymentIntent(
                $package['price'] * 100, // Convert to cents
                'myr',
                [
                    'user_id' => Auth::id(),
                    'package' => $request->package,
                    'credits' => $package['credits'],
                ]
            );

            // Return client secret - payment record will be created only after successful payment
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'amount' => $package['price'],
                'credits' => $package['credits'],
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create payment. Please try again.'], 500);
        }
    }

    /**
     * Process successful payment (called after Stripe confirms).
     */
    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        // Check if payment already processed
        $existingPayment = Payment::where('stripe_payment_intent_id', $request->payment_intent_id)
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => true,
                'message' => 'Payment already processed.',
                'redirect' => route('credits.success'),
            ]);
        }

        try {
            // Verify payment with Stripe
            $paymentIntent = $this->stripeService->retrievePaymentIntent($request->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                // Get package info from metadata
                $packages = $this->getCreditPackages();
                $packageKey = $paymentIntent->metadata->package ?? null;
                $package = $packages[$packageKey] ?? null;

                if (!$package) {
                    return response()->json(['error' => 'Invalid package information.'], 400);
                }

                // Create payment record only after successful payment
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'stripe_payment_method_id' => $paymentIntent->payment_method,
                    'amount' => $package['price'],
                    'currency' => 'MYR',
                    'status' => 'completed',
                    'credits_purchased' => $package['credits'],
                    'description' => "Credit purchase - {$package['name']} Package",
                    'metadata' => [
                        'package' => $packageKey,
                        'package_name' => $package['name'],
                    ],
                    'paid_at' => now(),
                ]);

                // Add credits to user
                $this->creditService->addCredits(
                    Auth::user(),
                    $package['credits'],
                    "Credit purchase - {$package['name']} Package",
                    'Payment',
                    $payment->id
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful!',
                    'redirect' => route('credits.success'),
                ]);
            }

            return response()->json(['error' => 'Payment not completed.'], 400);

        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process payment.'], 500);
        }
    }

    /**
     * Display payment success page.
     */
    public function success(): View
    {
        $latestPayment = Auth::user()->payments()
            ->where('status', 'completed')
            ->latest()
            ->first();

        return view('payments.success', [
            'payment' => $latestPayment,
            'currentBalance' => Auth::user()->credit_balance,
        ]);
    }

    /**
     * Display payment history.
     */
    public function history(): View
    {
        $payments = Auth::user()->payments()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $transactions = Auth::user()->creditTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('payments.history', [
            'payments' => $payments,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Display payment receipt.
     */
    public function receipt(Payment $payment): View|RedirectResponse
    {
        if ($payment->user_id !== Auth::id()) {
            return redirect()->route('payments.history')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        return view('payments.receipt', [
            'payment' => $payment,
        ]);
    }

    /**
     * Handle Stripe webhook.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
        }

        return response('OK', 200);
    }

    /**
     * Handle successful payment webhook.
     */
    protected function handlePaymentSucceeded($paymentIntent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment && $payment->status !== 'completed') {
            $payment->update([
                'status' => 'completed',
                'stripe_payment_method_id' => $paymentIntent->payment_method,
                'paid_at' => now(),
            ]);

            $this->creditService->addCredits(
                $payment->user,
                $payment->credits_purchased,
                "Credit purchase - {$payment->metadata['package_name']} Package",
                'Payment',
                $payment->id
            );
        }
    }

    /**
     * Handle failed payment webhook.
     */
    protected function handlePaymentFailed($paymentIntent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update(['status' => 'failed']);
        }
    }

    /**
     * Get available credit packages.
     */
    protected function getCreditPackages(): array
    {
        return [
            'basic' => [
                'name' => 'Basic',
                'price' => 10.00,
                'credits' => 10.00,
                'bonus' => '0%',
                'description' => 'Perfect for occasional parking',
            ],
            'standard' => [
                'name' => 'Standard',
                'price' => 25.00,
                'credits' => 27.00,
                'bonus' => '8%',
                'description' => 'Great value for regular users',
                'popular' => true,
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 50.00,
                'credits' => 55.00,
                'bonus' => '10%',
                'description' => 'Best for frequent parking',
            ],
            'ultimate' => [
                'name' => 'Ultimate',
                'price' => 100.00,
                'credits' => 115.00,
                'bonus' => '15%',
                'description' => 'Maximum savings for heavy users',
            ],
        ];
    }
}
