<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\ParkingLevel;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zones = Zone::all();
        return view('zones.index', compact('zones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('zones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'zone_code' => 'required|string|max:50|unique:zones,zone_code',
            'zone_name' => 'required|string|max:255',
            'type' => 'required|in:single,multi',
        ]);

        if ($request->type === 'single') {
            $request->validate([
                'total_slots' => 'required|integer|min:1'
            ]);

            $zone = Zone::create([
                'zone_code' => $request->zone_code,
                'zone_name' => $request->zone_name,
                'type' => 'single',
                'total_slots' => $request->total_slots,
                'available_slots' => $request->total_slots,
            ]);

            return redirect()->route('zones.index')->with('success', 'Zone Added Sucessfully.');
        }

        $request->validate([
            'floors' => 'required|array|min:1',
            'floors.*.name' => 'required|string',
            'floors.*.slots' => 'required|integer|min:1',
        ]);

        $total = collect($request->floors)->sum('slots');

        $zone = Zone::create([
            'zone_code' => $request->zone_code,
            'zone_name' => $request->zone_name,
            'type' => 'multi',
            'total_slots' => $total,
            'available_slots' => $total,
        ]);

        foreach ($request->floors as $floor) {
            ParkingLevel::create([
                'zone_id' => $zone->id,
                'level_name' => $floor['name'],
                'total_slots' => $floor['slots'],
                'available_slots' => $floor['slots'],
            ]);
        }

        return redirect()->route('zones.index')->with('success', 'Zone Added Sucessfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
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
    public function update(Request $request, $id)
    {
        $zone = Zone::findOrFail($id);

        $rules = [
            'zone_code' => 'required|string|max:50|unique:zones,zone_code,' . $id,
            'zone_name' => 'required|string|max:255',
            'type' => 'required|in:single,multi',
        ];

        if ($request->type === 'single') {
            $rules['total_slots'] = 'required|integer|min:1';
        } else {
            $rules['floors'] = 'required|array';
        }

        $request->validate($rules);

        if ($request->type === 'single') {
            $zone->update([
                'zone_code' => $request->zone_code,
                'zone_name' => $request->zone_name,
                'type' => 'single',
                'total_slots' => $request->total_slots,
                'available_slots' => $request->total_slots,
            ]);

            ParkingLevel::where('zone_id', $zone->id)->delete();
        }

        if ($request->type === 'multi') {
            $zone->update([
                'zone_code' => $request->zone_code,
                'zone_name' => $request->zone_name,
                'type' => 'multi',
                'total_slots' => 0,
                'available_slots' => 0,
            ]);

            if (!empty($request->floors)) {
                foreach ($request->floors as $key => $floor) {
                    if ($key !== 'new') {
                        $existing = ParkingLevel::find($floor['id'] ?? null);
                        if ($existing) {
                            $existing->update([
                                'level_name' => $floor['name'],
                                'total_slots' => $floor['slots'],
                                'available_slots' => $floor['slots'],
                            ]);
                        }
                    }
                }
            }

            if (isset($request->floors['new']['name'])) {
                $names = $request->floors['new']['name'];
                $slots = $request->floors['new']['slots'];

                foreach ($names as $index => $name) {
                    ParkingLevel::create([
                        'zone_id' => $zone->id,
                        'level_name' => $name,
                        'total_slots' => $slots[$index],
                        'available_slots' => $slots[$index],
                    ]);
                }
            }

            $sum = ParkingLevel::where('zone_id', $zone->id)->sum('total_slots');
            $zone->update(['total_slots' => $sum, 'available_slots' => $sum]);
        }

        return redirect()->route('zones.index')->with('success', 'Zone updated.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Zone::findOrFail($id)->delete();
        return redirect()->route('zones.index')->with('success', 'Zone successfully deleted.');
    }
}
