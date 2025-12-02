<?php

namespace App\Http\Controllers;

use App\Models\Zone;
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
            'total_slots' => 'required|integer|min:1',
        ]);

        Zone::create([
            'zone_code' => $request->zone_code,
            'zone_name' => $request->zone_name,
            'total_slots' => $request->total_slots,
            'available_slots' => $request->total_slots, 
        ]);

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
        return view('zones.edit', compact('zone', 'id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'zone_code' => 'required|string|max:50|unique:zones,zone_code,' . $id,
            'zone_name' => 'required|string|max:255',
            'total_slots' => 'required|integer|min:1',
        ]);

        $zone = Zone::findOrFail($id);
        $zone->update($request->only(['zone_code','zone_name','total_slots']));
        return redirect()->route('zones.index')->with('success','Zone successfully editted.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Zone::findOrFail( $id )->delete();
        return redirect()->route('zones.index')->with('success','Zone successfully deleted.');
    }
}
