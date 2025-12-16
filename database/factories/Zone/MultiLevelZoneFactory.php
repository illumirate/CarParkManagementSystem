<?php

namespace Database\Factories\Zone;

use App\Models\Zone;
use App\Models\ParkingLevel;
use Illuminate\Http\Request;

class MultiLevelZoneFactory implements ZoneFactoryInterface
{
    public function create(Request $request): Zone
    {
        $totalSlots = collect($request->floors)->sum('slots');

        $zone = Zone::create([
            'zone_code' => $request->zone_code,
            'zone_name' => $request->zone_name,
            'type' => 'multi',
            'total_slots' => $totalSlots,
            'available_slots' => $totalSlots,
        ]);

        foreach ($request->floors as $floor) {
            ParkingLevel::create([
                'zone_id' => $zone->id,
                'level_name' => $floor['name'],
                'total_slots' => $floor['slots'],
                'available_slots' => $floor['slots'],
            ]);
        }

        return $zone;
    }
}
