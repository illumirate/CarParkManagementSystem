<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ParkingSlot;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class BookingService
{
    /**
     * Search for available parking slots.
     */
    public function searchAvailableSlots(
        int $zoneId,
        ?int $levelId,
        string $date,
        string $startTime,
        string $endTime
    ): Collection {
        $query = ParkingSlot::query()
            ->with(['zone', 'parkingLevel'])
            ->where('zone_id', $zoneId)
            ->where('status', 'available');

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        $slots = $query->get();

        // Filter out slots that have conflicting bookings
        return $slots->filter(function ($slot) use ($date, $startTime, $endTime) {
            return $slot->isAvailableFor($date, $startTime, $endTime);
        })->values();
    }

    /**
     * Create a new booking.
     */
    public function createBooking(
        User $user,
        ParkingSlot $slot,
        Vehicle $vehicle,
        string $date,
        string $startTime,
        string $endTime,
        float $fee
    ): Booking {
        return Booking::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'parking_slot_id' => $slot->id,
            'booking_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_fee' => $fee,
            'status' => 'pending',
        ]);
    }

    /**
     * Get slot availability map for visualization.
     */
    public function getSlotAvailabilityMap(
        int $zoneId,
        ?int $levelId,
        string $date,
        string $startTime,
        string $endTime
    ): Collection {
        $query = ParkingSlot::query()
            ->with(['zone', 'parkingLevel'])
            ->where('zone_id', $zoneId);

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        $slots = $query->get();

        return $slots->map(function ($slot) use ($date, $startTime, $endTime) {
            $isAvailable = $slot->status === 'available'
                && $slot->isAvailableFor($date, $startTime, $endTime);

            return [
                'id' => $slot->id,
                'slot_number' => $slot->slot_number,
                'status' => $slot->status,
                'is_available' => $isAvailable,
                'level_name' => $slot->parkingLevel?->level_name,
            ];
        });
    }

    /**
     * Expire overdue bookings.
     */
    public function expireOverdueBookings(): int
    {
        $count = Booking::where('status', 'confirmed')
            ->where('booking_date', '<', now()->toDateString())
            ->orWhere(function ($query) {
                $query->where('booking_date', now()->toDateString())
                    ->where('end_time', '<', now()->format('H:i'));
            })
            ->update(['status' => 'expired']);

        return $count;
    }

    /**
     * Get active booking for a user.
     */
    public function getActiveBooking(User $user): ?Booking
    {
        return $user->bookings()
            ->where('status', 'active')
            ->first();
    }

    /**
     * Check in to a booking.
     */
    public function checkIn(Booking $booking): bool
    {
        if ($booking->status !== 'confirmed') {
            return false;
        }

        $booking->update([
            'status' => 'active',
            'checked_in_at' => now(),
        ]);

        return true;
    }

    /**
     * Check out from a booking.
     */
    public function checkOut(Booking $booking): bool
    {
        if ($booking->status !== 'active') {
            return false;
        }

        $booking->update([
            'status' => 'completed',
            'checked_out_at' => now(),
        ]);

        return true;
    }
}
