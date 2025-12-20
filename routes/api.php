<?php

use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\SlotApiController;
use App\Http\Controllers\Api\SupportApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Web Services for module-to-module communication.
| All routes are prefixed with /api
|
*/

// ==================== BOOKING MODULE APIs ====================
// Exposed: For other modules to consume booking data
Route::get('/bookings', [BookingApiController::class, 'getBookings']);
Route::get('/bookings/stats', [BookingApiController::class, 'getBookingStats']);
Route::get('/slots/{slotId}/active-bookings', action: [BookingApiController::class, 'getActiveBookingsForSlot']);

// ==================== LIVE SUPPORT MODULE APIs ====================
// Exposed: For other modules to create support tickets from booking issues
Route::post('/support/tickets/booking-issue', [SupportApiController::class, 'createBookingTicket']);
Route::get('/support/tickets/booking-report', [SupportApiController::class, 'getBookingReport']);

// ==================== AUTHENTICATION MODULE APIs ====================
// Exposed: For other modules to get user's vehicles
Route::get('/users/{id}/vehicles', [AuthApiController::class, 'getVehicles']);

// ==================== SLOT MANAGEMENT MODULE APIs ====================
// Exposed: For booking module to consume slot data (simulating other team member's API)
Route::get('/slots', [SlotApiController::class, 'getSlots']);
Route::get('/zones', [SlotApiController::class, 'getZones']);
