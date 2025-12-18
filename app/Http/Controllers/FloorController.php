<?php
  /**
  * Author: Adam Chin Wai Kin
  */
namespace App\Http\Controllers;
use App\Models\Zone;
use App\Models\ParkingLevel;
use Illuminate\Validation\Rule;


use Illuminate\Http\Request;

class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index($zoneId)
    {
        $zone = Zone::findOrFail($zoneId);
        $floors = ParkingLevel::with('parkingSlots')->where('zone_id', $zoneId)->get();

        $floors->each(function ($floor) {
            $floor->available_slots = $floor->parkingSlots->where('status', 'available')->count();
        });


        return view('floors.index', compact('zone', 'floors'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Zone $zone)
    {
        return view('floors.create', compact('zone'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $zoneId)
    {
        $zone = Zone::findOrFail($zoneId);

        $request->validate([
            'floor_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('parking_levels', 'level_name')->where(function ($query) use ($zoneId) {
                    return $query->where('zone_id', $zoneId);
                })
            ],
            'total_slots' => 'required|integer|min:1',
        ]);

        ParkingLevel::create([
            'zone_id' => $zone->id,
            'level_name' => $request->floor_name,
            'total_slots' => $request->total_slots,
            'available_slots' => $request->total_slots,
        ]);

        return redirect()->route('admin.zones.floors.index', $zone->id)
            ->with('success', 'Floor added successfully.');
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

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($zoneId, $floorId)
    {
        $floor = ParkingLevel::with('parkingSlots.bookings')->findOrFail($floorId);

        $blockedSlots = [];
        foreach ($floor->parkingSlots as $slot) {
            $activeBookings = $slot->bookings()
                ->whereIn('status', ['pending', 'confirmed', 'active'])
                ->where('booking_date', '>=', now()->toDateString())
                ->count();

            if ($activeBookings > 0) {
                $blockedSlots[] = $slot->slot_id;
            }
        }

        if (!empty($blockedSlots)) {
            return redirect()->route('admin.zones.floors.index', $zoneId)
                ->with('error', 'Cannot delete floor. Slots with active bookings: ' . implode(', ', $blockedSlots));
        }

        $floor->delete();

        return redirect()->route('admin.zones.floors.index', $zoneId)
            ->with('success', 'Floor deleted successfully.');
    }


}
