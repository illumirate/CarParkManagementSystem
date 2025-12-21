<?php
//  Author: Ng Ian Kai

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingSlot;
use App\Models\Zone;
use App\Notifications\BookingConfirmation;
use App\Services\BookingService;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected CreditService $creditService;

    public function __construct(BookingService $bookingService, CreditService $creditService)
    {
        $this->bookingService = $bookingService;
        $this->creditService = $creditService;
    }

    /**
     * Display list of upcoming bookings.
     */
    public function index(): View
    {
        $bookings = Auth::user()->bookings()
            ->with(['parkingSlot.zone', 'vehicle'])
            ->upcoming()
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return view('bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Display booking history.
     */
    public function history(): View
    {
        $bookings = Auth::user()->bookings()
            ->with(['parkingSlot.zone', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('bookings.history', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Display booking creation form.
     */
    public function create(): View
    {
        $zones = Zone::all();
        $vehicles = Auth::user()->vehicles()->active()->get();

        return view('bookings.create', [
            'zones' => $zones,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Search for available slots (AJAX).
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'level_id' => 'nullable|exists:parking_levels,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_type' => 'nullable|in:car,motorcycle',
        ]);

        // Validate end time is before 10 PM
        if ($request->end_time > '22:00') {
            return response()->json([
                'success' => false,
                'message' => 'Booking must end by 10:00 PM.',
            ], 422);
        }

        // Validate start time is not in the past for today's bookings
        if ($request->date === now()->toDateString()) {
            $currentTime = now()->format('H:i');
            if ($request->start_time < $currentTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Start time cannot be in the past.',
                ], 422);
            }
        }

        // Validate minimum 1 hour duration
        $start = \Carbon\Carbon::parse($request->start_time);
        $end = \Carbon\Carbon::parse($request->end_time);
        if ($start->diffInMinutes($end) < 60) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum booking duration is 1 hour.',
            ], 422);
        }

        $availableSlots = $this->bookingService->searchAvailableSlots(
            $request->zone_id,
            $request->level_id,
            $request->date,
            $request->start_time,
            $request->end_time,
            $request->slot_type
        );

        $fee = Booking::calculateFee($request->start_time, $request->end_time);

        return response()->json([
            'success' => true,
            'slots' => $availableSlots,
            'fee' => $fee,
            'fee_formatted' => 'RM ' . number_format($fee, 2),
        ]);
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'parking_slot_id' => 'required|exists:parking_slots,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $user = Auth::user();

        // Verify vehicle belongs to user
        $vehicle = $user->vehicles()->find($request->vehicle_id);
        if (!$vehicle) {
            return back()->withErrors(['vehicle_id' => 'Invalid vehicle selected.']);
        }

        // Verify end time is before 10 PM
        if ($request->end_time > '22:00') {
            return back()->withErrors(['end_time' => 'Booking must end by 10:00 PM.']);
        }

        // Verify start time is not in the past for today's bookings
        if ($request->booking_date === now()->toDateString()) {
            $currentTime = now()->format('H:i');
            if ($request->start_time < $currentTime) {
                return back()->withErrors(['start_time' => 'Start time cannot be in the past.']);
            }
        }

        // Verify minimum 1 hour
        $start = \Carbon\Carbon::parse($request->start_time);
        $end = \Carbon\Carbon::parse($request->end_time);
        if ($start->diffInMinutes($end) < 60) {
            return back()->withErrors(['end_time' => 'Minimum booking duration is 1 hour.']);
        }

        // Check slot availability
        $slot = ParkingSlot::findOrFail($request->parking_slot_id);
        if (!$this->bookingService->isSlotAvailableFor($slot, $request->booking_date, $request->start_time, $request->end_time)) {
            return back()->withErrors(['parking_slot_id' => 'This slot is no longer available for the selected time.']);
        }

        // Calculate fee
        $fee = Booking::calculateFee($request->start_time, $request->end_time);

        // Check if user has enough credits
        if (!$user->hasCredits($fee)) {
            return redirect()->route('credits.index')
                ->withErrors(['credits' => 'Insufficient credits. Please top up your balance.']);
        }

        // Create booking
        try {
            $booking = $this->bookingService->createBooking(
                $user,
                $slot,
                $vehicle,
                $request->booking_date,
                $request->start_time,
                $request->end_time,
                $fee
            );

            // Deduct credits
            $this->creditService->deductCredits(
                $user,
                $fee,
                "Parking booking #{$booking->booking_number}",
                'Booking',
                $booking->id
            );

            // Update booking status to confirmed
            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // Note: Slot status remains 'available' - availability is determined by booking conflicts
            // The hasBookingConflict() method checks for date/time
            // Send booking confirmation email
            $user->notify(new BookingConfirmation($booking));

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking confirmed! Your parking slot has been reserved.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create booking. Please try again.']);
        }
    }

    /**
     * Display booking details.
     */
    public function show(Booking $booking): View|RedirectResponse
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return redirect()->route('bookings.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        $booking->load(['parkingSlot.zone', 'parkingSlot.parkingLevel', 'vehicle']);

        $vehicles = Auth::user()->vehicles()->active()->get();

        return view('bookings.show', [
            'booking' => $booking,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Update the vehicle for a booking.
     */
    public function updateVehicle(Request $request, Booking $booking): RedirectResponse
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return redirect()->route('bookings.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        // Ensure booking can be modified
        if (!$booking->canBeModified()) {
            return back()->withErrors(['error' => 'This booking can no longer be modified.']);
        }

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        // Verify vehicle belongs to user
        $vehicle = Auth::user()->vehicles()->find($request->vehicle_id);
        if (!$vehicle) {
            return back()->withErrors(['vehicle_id' => 'Invalid vehicle selected.']);
        }

        $booking->update([
            'vehicle_id' => $vehicle->id,
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Vehicle updated successfully.');
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return redirect()->route('bookings.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['error' => 'This booking cannot be cancelled.']);
        }

        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Calculate refund
        $refundAmount = $booking->getRefundAmount();

        // Process cancellation
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason ?? 'Cancelled by user',
            'cancelled_at' => now(),
        ]);

        // Process refund if applicable
        if ($refundAmount > 0) {
            $this->creditService->addCredits(
                Auth::user(),
                $refundAmount,
                "Refund for cancelled booking #{$booking->booking_number}",
                'Booking',
                $booking->id
            );
        }

        $message = $refundAmount > 0
            ? "Booking cancelled. RM " . number_format($refundAmount, 2) . " has been refunded to your account."
            : "Booking cancelled. No refund applicable as per cancellation policy.";

        return redirect()->route('bookings.index')->with('success', $message);
    }

    /**
     * Get levels for a zone (AJAX).
     */
    public function getLevels(Zone $zone): JsonResponse
    {
        $levels = $zone->parkingLevels()->get()->map(function ($level) {
            return [
                'id' => $level->id,
                'level_name' => $level->level_name,
            ];
        });

        return response()->json([
            'success' => true,
            'levels' => $levels,
        ]);
    }

    /**
     * Get slot availability for visualization (AJAX).
     */
    public function getSlotAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'level_id' => 'nullable|exists:parking_levels,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);

        $slots = $this->bookingService->getSlotAvailabilityMap(
            $request->zone_id,
            $request->level_id,
            $request->date,
            $request->start_time,
            $request->end_time
        );

        return response()->json([
            'success' => true,
            'slots' => $slots,
        ]);
    }
}
