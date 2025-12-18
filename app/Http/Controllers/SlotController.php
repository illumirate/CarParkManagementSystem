<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use App\Models\SlotMaintenance;
use Illuminate\Http\Request;
use App\Models\Zone;
use App\Models\ParkingLevel;
use Database\Factories\Slot\SlotFactory;
use App\Jobs\SetSlotAvailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Services\BookingService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($zoneId, $floorId)
    {
        $zone = Zone::findOrFail($zoneId);
        $floor = ParkingLevel::findOrFail($floorId);

        $slots = ParkingSlot::where('zone_id', $zone->id)
            ->where('level_id', $floor->id)
            ->get();

        return view('parkingslots.index', compact('zone', 'floor', 'slots'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ParkingSlot $parkingSlot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $zone = Zone::find($id);
        $parkingLevels = ParkingLevel::where('zone_id', $zone->id)->get();
        return view('zones.edit', compact('zone', 'id', 'parkingLevels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParkingSlot $parkingSlot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (is_array($id)) {
            ParkingSlot::destroy($id);
        } else {
            ParkingSlot::findOrFail($id)->delete();
        }
    }

    public function generate($zoneId, $floorId, $slotType = 'Car')
    {
        $zone = Zone::findOrFail($zoneId);
        $floor = ParkingLevel::findOrFail($floorId);

        ParkingSlot::where('zone_id', $zone->id)->where('level_id', $floor->id)->delete();

        $totalSlots = $floor->total_slots;

        for ($i = 1; $i <= $totalSlots; $i++) {
            $slotId = $zone->zone_code . '-' . $floor->level_name . '-' . $i;

            SlotFactory::make($slotType)->create([
                'slot_id' => $slotId,
                'zone_id' => $zone->id,
                'level_id' => $floor->id,
            ]);
        }

        return redirect()->route('admin.zones.floors.slots.index', [$zone->id, $floor->id])
            ->with('success', 'Parking slots generated successfully.');
    }

    public function bulkMarkUnavailable(Request $request, $zoneId, $floorId)
    {
        $slotIds = $request->input('slot_ids', []);

        if (empty($slotIds)) {
            return redirect()->back()->with('error', 'No slots selected.');
        }

        $blockedSlots = [];
        $updatedCount = 0;

        foreach ($slotIds as $slotId) {
            $slot = ParkingSlot::find($slotId);
            if (!$slot)
                continue;

            $activeBookings = BookingService::getActiveBookingsForSlot($slotId);

            if (!empty($activeBookings['data'])) {
                $blockedSlots[] = $slot->slot_id;
                continue;
            }

            $slot->update(['status' => 'unavailable']);
            $updatedCount++;
        }

        $message = "{$updatedCount} slots marked as unavailable.";
        if (!empty($blockedSlots)) {
            $message .= " Cannot mark the following slots due to active bookings: " . implode(', ', $blockedSlots);
        }

        return redirect()->route('admin.zones.floors.slots.index', [$zoneId, $floorId])
            ->with('success', $message);
    }


    public function bulkMarkAvailable(Request $request, $zoneId, $floorId)
    {
        $slotIds = $request->input('slot_ids', []);

        if (empty($slotIds)) {
            return redirect()->back()->with('error', 'No slots selected.');
        }

        $blockedSlots = [];
        $updatedCount = 0;

        foreach ($slotIds as $slotId) {
            $slot = ParkingSlot::find($slotId);
            if (!$slot)
                continue;

            $activeBookings = BookingService::getActiveBookingsForSlot($slotId);

            if (!empty($activeBookings['data']) || $slot->status === 'maintenance') {
                $blockedSlots[] = $slot->slot_id;
                continue;
            }

            $slot->update(['status' => 'available']);
            $updatedCount++;
        }

        $message = "{$updatedCount} slots marked as available.";
        if (!empty($blockedSlots)) {
            $message .= " Cannot mark the following slots due to active bookings or maintenance: " . implode(', ', $blockedSlots);
        }

        return redirect()->route('admin.zones.floors.slots.index', [$zoneId, $floorId])
            ->with('success', $message);
    }


    public function updateType(Request $request, $zoneId, $floorId, $slotId)
    {
        $request->validate([
            'type' => 'required|in:Car,Motorcycle',
        ]);

        $slot = ParkingSlot::findOrFail($slotId);

        $slot->type = $request->type;


        $slot->save();

        return response()->json([
            'success' => true,
            'slot_id' => $slot->id,
            'new_type' => $slot->type,
            'new_status' => $slot->status,
        ]);
    }

    public function scheduleMaintenance(Request $request, $zoneId, $floorId, $slotId)
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            Log::warning("Unauthorized schedule attempt for slot {$slotId} by user ID: " . ($user?->id ?? 'guest'));
            abort(403, 'Unauthorized. Admin access required.');
        }

        try {
            $slot = ParkingSlot::findOrFail($slotId);

            $request->validate([
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
            ]);

            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);

            $activeBookings = BookingService::getActiveBookingsForSlot($slotId);

            Log::info("User {$user->name} ({$user->id}) attempting to schedule maintenance for slot {$slotId} from {$startTime} to {$endTime}");

            if ($activeBookings === null) {
                Log::error("Booking service failure for slot {$slotId} by user {$user->id}");
                return redirect()->back()->with('error', 'Failed to check active bookings via Booking service.');
            }

            foreach ($activeBookings['data'] ?? [] as $booking) {
                $bookingStart = Carbon::parse($booking['start_time']);
                $bookingEnd = Carbon::parse($booking['end_time']);

                if ($startTime->lt($bookingEnd) && $endTime->gt($bookingStart)) {
                    Log::warning("Overlap detected: slot {$slotId}, booking {$booking['booking_id']}, user {$user->id}");
                    return redirect()->back()->with('error', 'Maintenance time overlaps with an existing booking.');
                }
            }

            $slot->update(['status' => 'maintenance']);

            SlotMaintenance::create([
                'slot_id' => $slot->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

            Log::info("Maintenance scheduled successfully for slot {$slotId} by user {$user->id}");

            SetSlotAvailable::dispatch($slot->id)->delay($endTime);

            return redirect()->back()->with('success', 'Slot scheduled for maintenance successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to schedule maintenance for slot {$slotId} by user {$user->id}: {$e->getMessage()}");
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function updateMaintenance(Request $request, $zoneId, $floorId, $slotId)
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            Log::warning("Unauthorized maintenance update attempt for slot {$slotId} by user ID: " . ($user?->id ?? 'guest'));
            abort(403, 'Unauthorized. Admin access required.');
        }

        $request->validate([
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $slot = ParkingSlot::findOrFail($slotId);
            $maintenance = $slot->maintenance;

            if (!$maintenance) {
                Log::warning("No maintenance record found for slot {$slotId} by user {$user->id}");
                return redirect()->back()->with('error', 'No maintenance record found for this slot.');
            }

            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);

            $activeBookings = BookingService::getActiveBookingsForSlot($slotId);

            foreach ($activeBookings['data'] ?? [] as $booking) {
                $bookingStart = Carbon::parse($booking['start_time']);
                $bookingEnd = Carbon::parse($booking['end_time']);

                if ($startTime->lt($bookingEnd) && $endTime->gt($bookingStart)) {
                    Log::warning("Overlap detected during maintenance update: slot {$slotId}, booking {$booking['booking_id']}, user {$user->id}");
                    return redirect()->back()->with('error', 'Maintenance time overlaps with an existing booking.');
                }
            }

            $maintenance->update(['start_time' => $startTime, 'end_time' => $endTime]);

            $slot->update(['status' => now()->between($startTime, $endTime) ? 'maintenance' : 'available']);

            Log::info("Maintenance updated successfully for slot {$slotId} by user {$user->id}");

            return redirect()->back()->with('success', 'Maintenance updated successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to update maintenance for slot {$slotId} by user {$user->id}: {$e->getMessage()}");
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function completeMaintenance($zoneId, $floorId, $slotId)
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            Log::warning("Unauthorized complete maintenance attempt for slot {$slotId} by user ID: " . ($user?->id ?? 'guest'));
            abort(403, 'Unauthorized. Admin access required.');
        }

        try {
            $slot = ParkingSlot::findOrFail($slotId);
            $maintenance = $slot->maintenance;

            if ($maintenance) {
                $maintenance->end_time = now();
                $maintenance->save();
                Log::info("Maintenance completed for slot {$slot->slot_id} by user {$user->id}");
            }

            $slot->update(['status' => 'available']);

            return redirect()->back()->with('success', 'Maintenance marked as complete.');
        } catch (\Exception $e) {
            Log::error("Failed to complete maintenance for slot {$slotId} by user {$user->id}: {$e->getMessage()}");
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function showMaintenanceForm($zoneId, $floorId, $slotId)
    {
        $slot = ParkingSlot::with('maintenance')->findOrFail($slotId);
        $floor = $slot->parkingLevel;
        $zone = $slot->zone;

        return view('parkingslots.maintenance', compact('slot', 'floor', 'zone'));
    }
}
