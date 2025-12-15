<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * WEB SERVICES - Authentication Module API Controller
 *
 * Exposes vehicle data for other modules to consume.
 * Consumes booking statistics from Booking Module.
 */
class AuthApiController extends Controller
{
    /**
     * EXPOSED API: Get user's vehicles
     *
     * Endpoint: GET /api/users/{id}/vehicles
     * Target Modules: Booking Module
     */
    public function getVehicles(Request $request, int $id): JsonResponse
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
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => 'F',
                    'message' => 'User not found',
                    'requestId' => $request->requestId,
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ], 404);
            }

            // Get user's active vehicles
            $vehicles = $user->vehicles()->where('status', 'active')->get();

            $data = $vehicles->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'plate_number' => $vehicle->plate_number,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'color' => $vehicle->color,
                    'type' => $vehicle->type,
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'Vehicles retrieved successfully',
                'requestId' => $request->requestId,
                'data' => $data,
                'count' => $data->count(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => 'Error retrieving vehicles: ' . $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 500);
        }
    }
}
