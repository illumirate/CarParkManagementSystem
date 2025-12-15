<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * WEB SERVICES - Slot Management Module API Controller
 *
 * This simulates the API exposed by another team member's Slot Management Module.
 * The Booking Module consumes this API to get available parking slots.
 */
class SlotApiController extends Controller
{
    /**
     * EXPOSED API: Get parking slots data
     *
     * Endpoint: GET /api/slots
     * Source Module: Slot Management Module (simulated)
     * Target Module: Slot Booking Module
     */
    public function getSlots(Request $request): JsonResponse
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
            $query = ParkingSlot::with(['zone:id,name', 'level:id,level_name']);

            // Optional filters
            if ($request->has('zone_id')) {
                $query->where('zone_id', $request->zone_id);
            }

            if ($request->has('level_id')) {
                $query->where('level_id', $request->level_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $slots = $query->get();

            // Transform data for API response
            $data = $slots->map(function ($slot) {
                return [
                    'slot_id' => $slot->id,
                    'slot_number' => $slot->slot_number,
                    'zone_id' => $slot->zone_id,
                    'zone_name' => $slot->zone->name ?? null,
                    'level_id' => $slot->level_id,
                    'level_name' => $slot->level->level_name ?? null,
                    'slot_type' => $slot->slot_type ?? 'regular',
                    'status' => $slot->status,
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'Parking slots retrieved successfully',
                'requestId' => $request->requestId,
                'data' => $data,
                'count' => $data->count(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => 'Error retrieving slots: ' . $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 500);
        }
    }

    /**
     * Get zones with slot counts
     *
     * Endpoint: GET /api/zones
     */
    public function getZones(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'requestId' => 'required|string',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'F',
                'message' => 'Missing mandatory fields',
                'errors' => $validator->errors(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 400);
        }

        try {
            $zones = Zone::withCount('parkingSlots')->get();

            $data = $zones->map(function ($zone) {
                return [
                    'zone_id' => $zone->id,
                    'zone_name' => $zone->name,
                    'total_slots' => $zone->parking_slots_count,
                    'hourly_rate' => $zone->hourly_rate,
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'Zones retrieved successfully',
                'requestId' => $request->requestId,
                'data' => $data,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => 'Error retrieving zones: ' . $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 500);
        }
    }
}
