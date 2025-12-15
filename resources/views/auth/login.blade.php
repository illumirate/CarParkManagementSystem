@extends('layout')

@section('title', 'Login - TARUMT Car Park')

@push('styles')
{{-- Google reCAPTCHA Script --}}
@if(config('services.recaptcha.site_key'))
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   placeholder="your.email@tarc.edu.my" required autofocus>
                        </div>
                        @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    {{-- Google reCAPTCHA v2 --}}
                    @if(config('services.recaptcha.site_key'))
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                        @error('g-recaptcha-response')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                {{-- Google OAuth Login --}}
                @if(config('services.google.client_id'))
                <div class="d-grid mb-3">
                    <a href="{{ route('auth.google') }}" class="btn btn-outline-danger">
                        <i class="fab fa-google me-2"></i>Continue with Google
                    </a>
                </div>
                <hr class="my-4">
                @endif

                <div class="text-center">
                    <a href="{{ route('password.request') }}" class="text-decoration-none">
                        <i class="fas fa-key me-1"></i>Forgot your password?
                    </a>
                </div>

                <div class="text-center mt-3">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-decoration-none fw-bold">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
