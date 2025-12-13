@extends('layout')

@section('title', 'Payment Successful - TARUMT Car Park')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow text-center">
            <div class="card-body py-5">
                <div class="mb-4">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-check fa-3x"></i>
                    </div>
                </div>

                <h3 class="text-success mb-3">Payment Successful!</h3>

                @if($payment)
                <p class="text-muted mb-4">
                    Your payment of <strong>RM {{ number_format($payment->amount, 2) }}</strong> has been processed successfully.
                </p>

                <div class="alert alert-success">
                    <i class="fas fa-coins me-2"></i>
                    <strong>{{ number_format($payment->credits_purchased, 2) }} credits</strong> have been added to your account.
                </div>

                <div class="bg-light rounded p-3 mb-4">
                    <small class="text-muted">Transaction ID</small><br>
                    <code>{{ $payment->stripe_payment_intent_id }}</code>
                </div>
                @endif

                <p class="mb-4">
                    Your new balance: <strong class="text-success fs-4">RM {{ number_format($currentBalance, 2) }}</strong>
                </p>

                <div class="d-grid gap-2">
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                        <i class="fas fa-parking me-2"></i>Book a Parking Slot
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
