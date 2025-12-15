<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use Illuminate\Http\Request;
use App\Models\Zone;
use App\Models\ParkingLevel;

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
    public function edit(ParkingSlot $parkingSlot)
    {
        //
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
    public function destroy(ParkingSlot $parkingSlot)
    {
        //
    }

    public function generate($zoneId, $floorId)
    {
        $zone = Zone::findOrFail($zoneId);
        $floor = ParkingLevel::findOrFail($floorId);

        // Delete existing slots if you want to regenerate from scratch (optional)
        // ParkingSlot::where('zone_id', $zone->id)->where('level_id', $floor->id)->delete();

        $totalSlots = $floor->total_slots;

        for ($i = 1; $i <= $totalSlots; $i++) {
            ParkingSlot::create([
                'slot_id' => $zone->zone_code . '-' . $floor->level_name . '-' . $i,
                'zone_id' => $zone->id,
                'level_id' => $floor->id,
                'status' => 'available',
            ]);
        }


        return redirect()->route('admin..floors.slots.index', [$zone->id, $floor->id])
            ->with('success', 'Parking slots generated successfully.');
    }


}
