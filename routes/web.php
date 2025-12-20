<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportHelpController;
use App\Http\Controllers\SupportHelpAdminController;
use App\Http\Controllers\SupportInboxController;
use App\Http\Controllers\SupportEmergencyController;
use App\Http\Controllers\SupportNotificationController;
use App\Http\Controllers\SupportReplyTemplateController;
use App\Http\Controllers\SupportTicketAttachmentController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\SlotController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ==================== PUBLIC ROUTES ====================
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// ==================== GUEST ROUTES ====================
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    // Registration
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    // Password Reset
    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Google OAuth
    Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// ==================== EMAIL VERIFICATION (signed URL, no login required) ====================
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

// ==================== AUTHENTICATED ROUTES ====================
Route::middleware(['auth', 'active'])->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification (for logged-in users)
    Route::get('email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::post('email/resend', [AuthController::class, 'resendVerification'])->name('verification.resend');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.password');
    Route::put('profile/password', [ProfileController::class, 'changePassword'])->name('profile.password.update');

    // Vehicles
    Route::resource('vehicles', VehicleController::class);
    Route::post('vehicles/{vehicle}/primary', [VehicleController::class, 'setPrimary'])->name('vehicles.primary');

    // Bookings
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/history', [BookingController::class, 'history'])->name('bookings.history');
    Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('bookings/search', [BookingController::class, 'search'])->name('bookings.search');
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('bookings/{booking}/vehicle', [BookingController::class, 'updateVehicle'])->name('bookings.updateVehicle');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Credits & Payments
    Route::get('credits', [PaymentController::class, 'index'])->name('credits.index');
    Route::post('credits/payment-intent', [PaymentController::class, 'createPaymentIntent'])->name('credits.intent');
    Route::post('credits/process', [PaymentController::class, 'processPayment'])->name('credits.process');
    Route::get('credits/success', [PaymentController::class, 'success'])->name('credits.success');
    Route::get('payments', [PaymentController::class, 'history'])->name('payments.history');

    // Live Support (User)
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('help', [SupportHelpController::class, 'index'])->name('help');
        Route::get('emergency', [SupportEmergencyController::class, 'create'])->name('emergency.create');
        Route::post('emergency', [SupportEmergencyController::class, 'store'])->name('emergency.store');
        Route::get('tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
        Route::post('tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
        Route::get('tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
        Route::get('tickets/{ticket}/messages', [SupportTicketController::class, 'messages'])->name('tickets.messages.index');
        Route::post('tickets/{ticket}/messages', [SupportTicketController::class, 'storeMessage'])->name('tickets.messages.store');
        Route::post('tickets/{ticket}/messages/{message}/delete', [SupportTicketController::class, 'deleteMessage'])
            ->name('tickets.messages.delete');
        Route::post('tickets/{ticket}/close', [SupportTicketController::class, 'close'])->name('tickets.close');
        Route::get('tickets/{ticket}/attachments/{attachment}', [SupportTicketAttachmentController::class, 'show'])
            ->name('tickets.attachments.show');
    });
});

// ==================== ADMIN ROUTES ====================
Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Zone Management
    Route::resource('zones', ZoneController::class);

    // Floor Management
    Route::resource('zones.floors', App\Http\Controllers\FloorController::class);

    // Slot Management
    Route::resource('zones.floors.slots', App\Http\Controllers\SlotController::class);
    Route::post(
        'zones/{zone}/floors/{floor}/slots/generate',
        [App\Http\Controllers\SlotController::class, 'generate']
    )
        ->name('zones.floors.slots.generate');

    Route::post(
        'zones/{zone}/floors/{floor}/slots/bulk-mark-unavail',
        [SlotController::class, 'bulkMarkUnavailable']
    )->name('zones.floors.slots.bulkMarkUnavailable');

    Route::post(
        'zones/{zone}/floors/{floor}/slots/bulk-mark-avail',
        [SlotController::class, 'bulkMarkAvailable']
    )->name('zones.floors.slots.bulkMarkAvailable');

    Route::post('zones/{zone}/floors/{floor}/slots/{slot}/update-type', [SlotController::class, 'updateType'])
        ->name('zones.floors.slots.updateType');

    Route::post(
        'zones/{zone}/floors/{floor}/slots/{slot}/maintenance',
        [SlotController::class, 'scheduleMaintenance']
    )->name('zones.floors.slots.scheduleMaintenance');

    Route::put('zones/{zone}/floors/{floor}/slots/{slot}/update-maintenance', [SlotController::class, 'updateMaintenance'])
        ->name('zones.floors.slots.updateMaintenance');

    Route::put('zones/{zone}/floors/{floor}/slots/{slot}/complete-maintenance', [SlotController::class, 'completeMaintenance'])
        ->name('zones.floors.slots.completeMaintenance');


    Route::get('zones/{zone}/floors/{floor}/slots/{slot}/maintenance', [SlotController::class, 'showMaintenanceForm'])
        ->name('zones.floors.slots.scheduleMaintenanceForm');


});

// ==================== SUPPORT AGENT ROUTES (Admin/Staff) ====================
Route::middleware(['auth', 'active', 'support_agent'])->prefix('admin/support')->name('admin.support.')->group(function () {
    Route::get('tickets', [SupportInboxController::class, 'index'])->name('tickets.index');
    Route::get('tickets/{ticket}', [SupportInboxController::class, 'show'])->name('tickets.show');
    Route::post('tickets/{ticket}/assign', [SupportInboxController::class, 'assignToMe'])->name('tickets.assign');
    Route::get('tickets/{ticket}/messages', [SupportInboxController::class, 'messages'])->name('tickets.messages.index');
    Route::post('tickets/{ticket}/messages', [SupportInboxController::class, 'storeMessage'])->name('tickets.messages.store');
    Route::post('tickets/{ticket}/messages/{message}/delete', [SupportInboxController::class, 'deleteMessage'])
        ->name('tickets.messages.delete');
    Route::post('tickets/{ticket}/status', [SupportInboxController::class, 'updateStatus'])->name('tickets.status');
    Route::get('tickets/{ticket}/attachments/{attachment}', [SupportTicketAttachmentController::class, 'show'])
        ->name('tickets.attachments.show');

    // Notifications (Admin/Staff)
    Route::get('notifications', [SupportNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [SupportNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/{id}/delete', [SupportNotificationController::class, 'destroy'])->name('notifications.delete');

    // Canned replies (Admin/Staff)
    Route::get('templates', [SupportReplyTemplateController::class, 'index'])->name('templates.index');
    Route::get('templates/create', [SupportReplyTemplateController::class, 'create'])->name('templates.create');
    Route::post('templates', [SupportReplyTemplateController::class, 'store'])->name('templates.store');
    Route::get('templates/{template}/edit', [SupportReplyTemplateController::class, 'edit'])->name('templates.edit');
    Route::put('templates/{template}', [SupportReplyTemplateController::class, 'update'])->name('templates.update');
    Route::delete('templates/{template}', [SupportReplyTemplateController::class, 'destroy'])->name('templates.destroy');
    Route::post('templates/{template}/toggle', [SupportReplyTemplateController::class, 'toggle'])->name('templates.toggle');

    // Help documentation management (Admin/Staff)
    Route::get('help', [SupportHelpAdminController::class, 'index'])->name('help.index');
    Route::get('help/create', [SupportHelpAdminController::class, 'create'])->name('help.create');
    Route::post('help', [SupportHelpAdminController::class, 'store'])->name('help.store');
    Route::get('help/{article}/edit', [SupportHelpAdminController::class, 'edit'])->name('help.edit');
    Route::put('help/{article}', [SupportHelpAdminController::class, 'update'])->name('help.update');
    Route::delete('help/{article}', [SupportHelpAdminController::class, 'destroy'])->name('help.destroy');
    Route::post('help/{article}/toggle', [SupportHelpAdminController::class, 'togglePublish'])->name('help.toggle');
});

// ==================== API ROUTES (for AJAX) ====================
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('zones/{zone}/levels', [BookingController::class, 'getLevels'])->name('api.zones.levels');
    Route::get('slots/availability', [BookingController::class, 'getSlotAvailability'])->name('api.slots.availability');
});
