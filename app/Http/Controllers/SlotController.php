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

        ParkingSlot::whereIn('id', $slotIds)->update([
            'status' => 'unavailable'
        ]);

        return redirect()->route('admin.zones.floors.slots.index', [$zoneId, $floorId])
            ->with('success', 'Selected slots marked as unavailable.');
    }

    public function bulkMarkAvailable(Request $request, $zoneId, $floorId)
    {
        $slotIds = $request->input('slot_ids', []);

        if (empty($slotIds)) {
            return redirect()->back()->with('error', 'No slots selected.');
        }

        ParkingSlot::whereIn('id', $slotIds)->update([
            'status' => 'available'
        ]);

        return redirect()->route('admin.zones.floors.slots.index', [$zoneId, $floorId])
            ->with('success', 'Selected slots marked as available.');
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
        try {
            $slot = ParkingSlot::findOrFail($slotId);

            $request->validate([
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
            ]);

            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);

            $activeBookings = BookingService::getActiveBookingsForSlot($slotId);

            if ($activeBookings === null) {
                return redirect()->back()->with('error', 'Failed to check active bookings via Booking service.');
            }

            if (!empty($activeBookings['data'])) {
                foreach ($activeBookings['data'] as $booking) {
                    $bookingStart = Carbon::parse($booking['start_time']);
                    $bookingEnd = Carbon::parse($booking['end_time']);

                    Log::info("Checking overlap for slot {$slotId}");
                    Log::info("Maintenance Start: {$startTime} | End: {$endTime}");
                    Log::info("Booking Start: {$bookingStart} | End: {$bookingEnd}");

                    if ($startTime->lt($bookingEnd) && $endTime->gt($bookingStart)) {
                        Log::warning("Overlap detected with booking {$booking['booking_id']}");
                        return redirect()->back()->with('error', 'Maintenance time overlaps with an existing booking. Cannot schedule maintenance.');
                    } else {
                        Log::info("No overlap with booking {$booking['booking_id']}");
                    }
                }
            }

            $slot->update(['status' => 'maintenance']);

            SlotMaintenance::create([
                'slot_id' => $slot->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

            return redirect()->back()->with('success', 'Slot scheduled for maintenance successfully.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Slot not found.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());

        } catch (\Exception $e) {
            \Log::error("Error scheduling maintenance for slot {$slotId}: {$e->getMessage()}");
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }




    public function updateMaintenance(Request $request, $zoneId, $floorId, $slotId)
    {
        $request->validate([
            'end_time' => 'required|date|after:start_time',
        ]);

        $slot = ParkingSlot::findOrFail($slotId);
        $maintenance = $slot->maintenance;

        if (!$maintenance) {
            return redirect()->back()->with('error', 'No maintenance record found for this slot.');
        }

        $maintenance->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        if (now()->between($request->start_time, $request->end_time)) {
            $slot->update(['status' => 'maintenance']);
        }

        return redirect()->back()->with('success', 'Maintenance updated successfully.');
    }

    public function completeMaintenance($zoneId, $floorId, $slotId)
    {
        $slot = ParkingSlot::findOrFail($slotId);
        $maintenance = $slot->maintenance;

        if ($maintenance) {
            $maintenance->end_time = now();
            $maintenance->save();
        }
        $slot->update(['status' => 'available']);

        return redirect()->back()->with('success', 'Maintenance marked as complete.');
    }

    public function showMaintenanceForm($zoneId, $floorId, $slotId)
    {
        $slot = ParkingSlot::with('maintenance')->findOrFail($slotId);
        $floor = $slot->parkingLevel;
        $zone = $slot->zone;

        return view('parkingslots.maintenance', compact('slot', 'floor', 'zone'));
    }
}
