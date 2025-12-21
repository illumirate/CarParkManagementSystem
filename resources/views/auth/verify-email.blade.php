{{-- Author: Leo Chia Chuen --}}

@extends('layout')

@section('title', 'Verify Email - TARUMT Car Park')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark text-center py-3">
                <h4 class="mb-0"><i class="fas fa-envelope-open me-2"></i>Verify Your Email</h4>
            </div>
            <div class="card-body p-4 text-center">
                <div class="mb-4">
                    <i class="fas fa-envelope fa-4x text-warning"></i>
                </div>

                <h5>Almost there!</h5>
                <p class="text-muted">
                    We've sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
                    Please check your inbox and click the link to verify your email address.
                </p>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    If you don't see the email, check your spam folder.
                </div>

                <form method="POST" action="{{ route('verification.resend') }}" class="mt-4">
                    @csrf
                    <p class="text-muted mb-2">Didn't receive the email?</p>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-redo me-2"></i>Resend Verification Email
                    </button>
                </form>

                <hr class="my-4">

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
