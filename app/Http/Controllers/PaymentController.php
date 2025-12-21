<?php
//  Author: Ng Ian Kai

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * =============================================================================
 * ADAPTER PATTERN - Client Class
 * =============================================================================
 *
 * This controller is the CLIENT in the Adapter Pattern.
 * It works with the PaymentGatewayInterface (Target) without knowing about
 * the concrete payment gateway implementation (Stripe).
 *
 * Pattern Roles:
 * - CLIENT: This class (PaymentController)
 * - TARGET: PaymentGatewayInterface (the interface this client uses)
 * - ADAPTER: StripePaymentAdapter (injected via the interface)
 * - ADAPTEE: Stripe SDK (hidden behind the adapter)
 *
 * Benefits demonstrated:
 * - Controller doesn't import or reference Stripe directly
 * - Payment gateway can be swapped by changing the binding in AppServiceProvider
 * - Easy to test with mock implementations
 *
 * @see \App\Contracts\PaymentGatewayInterface - The Target interface
 * @see \App\Adapters\StripePaymentAdapter - The Adapter implementation
 */
class PaymentController extends Controller
{
    /**
     * The payment gateway interface (Target in Adapter Pattern).
     *
     * The concrete implementation (StripePaymentAdapter) is injected
     * via Laravel's service container based on the binding in AppServiceProvider.
     */
    protected PaymentGatewayInterface $paymentGateway;
    protected CreditService $creditService;

    /**
     * Constructor with dependency injection.
     *
     * ADAPTER PATTERN: We inject the TARGET interface, not the concrete adapter.
     * Laravel resolves this to StripePaymentAdapter based on our binding.
     */
    public function __construct(PaymentGatewayInterface $paymentGateway, CreditService $creditService)
    {
        $this->paymentGateway = $paymentGateway;
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
            // ADAPTER PATTERN: Call the Target interface method
            // The adapter translates this to the Stripe SDK call
            $paymentResult = $this->paymentGateway->createPayment(
                (int)($package['price'] * 100), // Convert to cents
                'myr',
                [
                    'user_id' => Auth::id(),
                    'package' => $request->package,
                    'credits' => $package['credits'],
                ]
            );

            // Check for errors using our standardized result
            if ($paymentResult->errorMessage) {
                Log::error('Payment creation failed: ' . $paymentResult->errorMessage);
                return response()->json(['error' => 'Failed to create payment. Please try again.'], 500);
            }

            // Return client secret - payment record will be created only after successful payment
            return response()->json([
                'clientSecret' => $paymentResult->clientSecret,
                'amount' => $package['price'],
                'credits' => $package['credits'],
            ]);

        } catch (\Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage());
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
            // ADAPTER PATTERN: Retrieve payment using the Target interface
            $paymentResult = $this->paymentGateway->getPayment($request->payment_intent_id);

            if ($paymentResult->isSuccessful()) {
                // Get package info from metadata
                $packages = $this->getCreditPackages();
                $packageKey = $paymentResult->metadata['package'] ?? null;
                $package = $packages[$packageKey] ?? null;

                if (!$package) {
                    return response()->json(['error' => 'Invalid package information.'], 400);
                }

                // Create payment record only after successful payment
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'stripe_payment_intent_id' => $paymentResult->id,
                    'stripe_payment_method_id' => null, // Will be updated via webhook if needed
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
