<?php

use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\SlotApiController;
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

// ==================== AUTHENTICATION MODULE APIs ====================
// Exposed: For other modules to get user's vehicles
Route::get('/users/{id}/vehicles', [AuthApiController::class, 'getVehicles']);

// ==================== SLOT MANAGEMENT MODULE APIs ====================
// Exposed: For booking module to consume slot data (simulating other team member's API)
Route::get('/slots', [SlotApiController::class, 'getSlots']);
