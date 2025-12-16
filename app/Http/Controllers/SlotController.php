<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use App\Models\SlotMaintenance;
use Illuminate\Http\Request;
use App\Models\Zone;
use App\Models\ParkingLevel;
use Database\Factories\Slot\SlotFactory;

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

    public function generate($zoneId, $floorId, $slotType = 'regular')
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
            'type' => 'required|in:Regular,Staff,VIP',
        ]);

        $slot = ParkingSlot::findOrFail($slotId);

        $slot->type = $request->type;

        if (in_array($request->type, ['VIP'])) {
            $slot->status = 'reserved';
        } else {
            $slot->status = 'available';
        }

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
        $request->validate([
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $slot = ParkingSlot::findOrFail($slotId);

        SlotMaintenance::create([
            'slot_id' => $slot->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        if (now()->between($request->start_time, $request->end_time)) {
            $slot->update(['status' => 'unavailable']);
        }

        return redirect()->back()->with('success', 'Slot scheduled for maintenance.');
    }

    public function showMaintenanceForm($zoneId, $floorId, $slotId)
    {

        $slot = ParkingSlot::findOrFail($slotId);
        $floor = $slot->parkingLevel;
        $zone = $slot->zone;
        $slots = ParkingSlot::where('level_id', $floor->id)->get();


        return view('parkingslots.maintenance', compact('zone', 'floor', 'slot', 'slots'));
    }
}
