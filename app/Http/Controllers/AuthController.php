<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserEventService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

/**
 * OBSERVER PATTERN - Client that triggers events via UserEventService.
 */
class AuthController extends Controller
{
    protected UserEventService $userEventService;

    public function __construct(UserEventService $userEventService)
    {
        $this->userEventService = $userEventService;
    }
    // ==================== LOGIN ====================

    /**
     * Display the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Verify reCAPTCHA if configured
        if (config('services.recaptcha.secret_key')) {
            $recaptchaResponse = $request->input('g-recaptcha-response');

            if (!$recaptchaResponse) {
                return back()->withErrors([
                    'g-recaptcha-response' => 'Please complete the reCAPTCHA verification.',
                ])->onlyInput('email');
            }

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);

            if (!$response->json('success')) {
                return back()->withErrors([
                    'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
                ])->onlyInput('email');
            }
        }

        // First check if user exists and email is verified
        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->hasVerifiedEmail()) {
            return back()->withErrors([
                'email' => 'Please verify your email address before logging in. Check your inbox for the verification link.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // OBSERVER PATTERN: Notify observers of login event
            $this->userEventService->userLoggedIn(Auth::user());

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();

        // OBSERVER PATTERN: Notify observers of logout event
        if ($user) {
            $this->userEventService->userLoggedOut($user);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // ==================== REGISTRATION ====================

    /**
     * Display the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@(student\.tarc\.edu\.my|tarc\.edu\.my)$/i',
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'in:student,staff'],
            'student_id' => ['required_if:user_type,student', 'nullable', 'string', 'max:20'],
            'staff_id' => ['required_if:user_type,staff', 'nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'regex:/^(\+?6?01)[0-46-9]-*[0-9]{7,8}$/'],
        ], [
            'email.regex' => 'Please use your TARUMT email address (@student.tarc.edu.my or @tarc.edu.my).',
            'phone.regex' => 'Please enter a valid Malaysian phone number.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'student_id' => $request->user_type === 'student' ? $request->student_id : null,
            'staff_id' => $request->user_type === 'staff' ? $request->staff_id : null,
            'phone' => $request->phone,
            'role' => 'user',
            'status' => 'active',
            'credit_balance' => 0.00,
        ]);

        // OBSERVER PATTERN: Notify observers of registration event
        // This triggers WelcomeEmailObserver which sends the verification email
        $this->userEventService->userRegistered($user);

        // Don't auto-login - require email verification first
        return redirect()->route('login')
            ->with('success', 'Registration successful! Please check your email to verify your account before logging in.');
    }

    // ==================== PASSWORD RESET ====================

    /**
     * Display forgot password form.
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Display reset password form.
     */
    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    // ==================== EMAIL VERIFICATION ====================

    /**
     * Display email verification notice.
     */
    public function showVerificationNotice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard'))
            : view('auth.verify-email');
    }

    /**
     * Handle email verification.
     */
    public function verifyEmail(Request $request, $id, $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => 'Invalid verification link.']);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard') . '?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route('dashboard') . '?verified=1')
            ->with('success', 'Email verified successfully!');
    }

    /**
     * Resend verification email.
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent!');
    }

    // ==================== GOOGLE OAUTH ====================

    /**
     * Redirect to Google for authentication.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists with this email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Existing user - link Google ID if not already linked
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                // Create new user from Google data
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(24)), // Random password for OAuth users
                    'email_verified_at' => now(), // Google emails are pre-verified
                    'user_type' => 'public', // Default user type
                ]);

                // OBSERVER PATTERN: Notify observers of registration event
                $this->userEventService->userRegistered($user);
            }

            // Log in the user
            Auth::login($user, true);

            // OBSERVER PATTERN: Notify observers of login event
            $this->userEventService->userLoggedIn($user);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Logged in with Google successfully!');

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Google login failed. Please try again.');
        }
    }
}
