@extends('layout')

@section('title', 'Top Up Credits - TARUMT Car Park')

@section('content')
<div class="text-center mb-4">
    <h2><i class="fas fa-wallet me-2"></i>Top Up Credits</h2>
    <p class="text-muted">Current Balance: <strong class="text-success fs-4">RM {{ number_format($currentBalance, 2) }}</strong></p>
</div>

<div class="row justify-content-center mb-4">
    @foreach($packages as $key => $package)
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card h-100 package-card {{ isset($package['popular']) ? 'border-primary' : '' }}" data-package="{{ $key }}">
            @if(isset($package['popular']))
            <div class="card-header bg-primary text-white text-center py-1">
                <small><i class="fas fa-star me-1"></i>Most Popular</small>
            </div>
            @endif
            <div class="card-body text-center">
                <h5 class="card-title">{{ $package['name'] }}</h5>
                <div class="my-3">
                    <span class="fs-2 fw-bold text-primary">RM {{ number_format($package['price'], 0) }}</span>
                </div>
                <p class="mb-2">
                    <i class="fas fa-coins text-warning me-1"></i>
                    <strong>{{ number_format($package['credits'], 0) }} Credits</strong>
                </p>
                @if($package['bonus'] !== '0%')
                <span class="badge bg-success">+{{ $package['bonus'] }} Bonus</span>
                @endif
                <p class="text-muted small mt-2">{{ $package['description'] }}</p>
            </div>
            <div class="card-footer bg-transparent">
                <button type="button" class="btn btn-{{ isset($package['popular']) ? 'primary' : 'outline-primary' }} w-100 select-package"
                        data-package="{{ $key }}">
                    Select
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Payment Form (hidden until package selected) --}}
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow" id="paymentCard" style="display: none;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Details</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Package: <strong id="selectedPackageName">-</strong></span>
                        <span>Amount: <strong id="selectedAmount">RM 0.00</strong></span>
                    </div>
                </div>

                <form id="payment-form">
                    <div class="mb-3">
                        <label for="card-element" class="form-label">Card Details</label>
                        <div id="card-element" class="form-control py-3">
                            <!-- Stripe Card Element will be inserted here -->
                        </div>
                        <div id="card-errors" class="text-danger small mt-1" role="alert"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg" id="submit-button">
                            <span id="button-text">
                                <i class="fas fa-lock me-2"></i>Pay Now
                            </span>
                            <span id="spinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>Secured by Stripe
                    </small>
                </div>
            </div>
        </div>

        {{-- Stripe not configured message --}}
        @if(!$stripeKey)
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Payment system is not configured. Please contact administrator.
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($stripeKey)
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('{{ $stripeKey }}');
    const elements = stripe.elements();

    const style = {
        base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            '::placeholder': { color: '#aab7c4' }
        },
        invalid: {
            color: '#dc3545',
            iconColor: '#dc3545'
        }
    };

    const cardElement = elements.create('card', { style: style });
    cardElement.mount('#card-element');

    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        displayError.textContent = event.error ? event.error.message : '';
    });

    let selectedPackage = null;
    let clientSecret = null;

    // Package selection
    document.querySelectorAll('.select-package').forEach(button => {
        button.addEventListener('click', function() {
            selectedPackage = this.dataset.package;

            // Update UI
            document.querySelectorAll('.package-card').forEach(card => {
                card.classList.remove('border-success', 'bg-light');
            });
            this.closest('.package-card').classList.add('border-success', 'bg-light');

            // Create payment intent
            fetch('{{ route("credits.intent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ package: selectedPackage })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                clientSecret = data.clientSecret;
                document.getElementById('selectedPackageName').textContent = selectedPackage.charAt(0).toUpperCase() + selectedPackage.slice(1);
                document.getElementById('selectedAmount').textContent = 'RM ' + data.amount.toFixed(2);
                document.getElementById('paymentCard').style.display = 'block';
                document.getElementById('paymentCard').scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                alert('Failed to initialize payment. Please try again.');
            });
        });
    });

    // Form submission
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        if (!clientSecret) {
            alert('Please select a package first.');
            return;
        }

        setLoading(true);

        const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement
            }
        });

        if (error) {
            document.getElementById('card-errors').textContent = error.message;
            setLoading(false);
        } else if (paymentIntent.status === 'succeeded') {
            // Process the payment on our server
            fetch('{{ route("credits.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ payment_intent_id: paymentIntent.id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.error || 'Payment processing failed.');
                    setLoading(false);
                }
            })
            .catch(error => {
                alert('An error occurred. Please check your payment history.');
                setLoading(false);
            });
        }
    });

    function setLoading(isLoading) {
        document.getElementById('submit-button').disabled = isLoading;
        document.getElementById('spinner').style.display = isLoading ? 'inline-block' : 'none';
        document.getElementById('button-text').textContent = isLoading ? 'Processing...' : 'Pay Now';
    }
});
</script>
@endif
@endpush
