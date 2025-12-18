<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * WEB SERVICES - Booking Module API Controller
 *
 * Exposes booking data for other modules to consume.
 * Consumes slot data from Slot Management Module.
 */
class BookingApiController extends Controller
{
    /**
     * EXPOSED API: Get booking records
     *
     * Endpoint: GET /api/bookings
     * Target Modules: Admin/Analytics Module, Payment Module
     */
    public function getBookings(Request $request): JsonResponse
    {
        // Validate mandatory IFA fields
        $validator = Validator::make($request->all(), [
            'requestId' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'F',
                'message' => 'Missing mandatory fields: requestId and timestamp are required',
                'errors' => $validator->errors(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 400);
        }

        try {
            $query = Booking::with(['user:id,name,email', 'parkingSlot:id,slot_number', 'vehicle:id,plate_number']);

            // Optional filters
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $bookings = $query->orderBy('created_at', 'desc')->get();

            // Transform data for API response
            $data = $bookings->map(function ($booking) {
                return [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'user_name' => $booking->user->name ?? null,
                    'slot_id' => $booking->parking_slot_id,
                    'slot_number' => $booking->parkingSlot->slot_number ?? null,
                    'vehicle_plate' => $booking->vehicle->plate_number ?? null,
                    'booking_date' => $booking->booking_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'status' => $booking->status,
                    'total_fee' => $booking->total_fee,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'Bookings retrieved successfully',
                'requestId' => $request->requestId,
                'data' => $data,
                'count' => $data->count(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => 'Error retrieving bookings: ' . $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 500);
        }
    }

    /**
     * EXPOSED API: Get booking statistics for a user
     *
     * Endpoint: GET /api/bookings/stats
     * Target Modules: Authentication Module (User Profile)
     */
    public function getBookingStats(Request $request): JsonResponse
    {
        // Validate mandatory IFA fields
        $validator = Validator::make($request->all(), [
            'requestId' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'F',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 400);
        }

        try {
            $userId = $request->user_id;

            $stats = [
                'total_bookings' => Booking::where('user_id', $userId)->count(),
                'confirmed_bookings' => Booking::where('user_id', $userId)->where('status', 'confirmed')->count(),
                'completed_bookings' => Booking::where('user_id', $userId)->where('status', 'completed')->count(),
                'cancelled_bookings' => Booking::where('user_id', $userId)->where('status', 'cancelled')->count(),
                'total_spent' => Booking::where('user_id', $userId)
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->sum('total_fee'),
            ];

            return response()->json([
                'status' => 'S',
                'message' => 'Booking statistics retrieved successfully',
                'requestId' => $request->requestId,
                'data' => $stats,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => 'Error retrieving statistics: ' . $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 500);
        }
    }

    /**
     * EXPOSED API: Get active/future bookings for a slot
     * Endpoint: GET /api/bookings/slot/{slotId}/active
     * Target Modules: Slot Management
     */
    public function getActiveBookingsForSlot(Request $request, $slotId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'requestId' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'F',
                'message' => 'Missing mandatory fields: requestId and timestamp',
                'errors' => $validator->errors(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 400);
        }

        $today = now()->toDateString();
        $bookings = Booking::with(['user:id,name,email', 'vehicle:id,plate_number'])
            ->where('parking_slot_id', $slotId)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where('booking_date', '>=', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $data = $bookings->map(fn($b) => [
            'booking_id' => $b->id,
            'user_id' => $b->user_id,
            'user_name' => $b->user->name ?? null,
            'vehicle_plate' => $b->vehicle->plate_number ?? null,
            'start_time' => $b->start_time,
            'end_time' => $b->end_time,
            'status' => $b->status,
        ]);

        return response()->json([
            'status' => 'S',
            'message' => 'Active bookings retrieved successfully',
            'requestId' => $request->requestId,
            'data' => $data,
            'count' => $data->count(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
