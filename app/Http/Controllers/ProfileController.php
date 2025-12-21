<?php
//  Author: Leo Chia Chuen

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display user profile.
     */
    public function show(): View
    {
        $user = Auth::user();

        return view('profile.show', [
            'user' => $user,
            'vehicles' => $user->vehicles()->active()->get(),
            'recentBookings' => $user->bookings()->latest()->take(5)->get(),
            'creditBalance' => $user->credit_balance,
        ]);
    }

    /**
     * Display edit profile form.
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update user profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^(\+?6?01)[0-46-9]-*[0-9]{7,8}$/'],
            'faculty' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'course' => ['nullable', 'string', 'max:100'],
        ], [
            'phone.regex' => 'Please enter a valid Malaysian phone number.',
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'faculty' => $request->faculty,
            'department' => $request->department,
            'course' => $request->course,
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Display change password form.
     */
    public function showChangePasswordForm(): View
    {
        return view('profile.change-password');
    }

    /**
     * Update user password.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Password changed successfully!');
    }
}
