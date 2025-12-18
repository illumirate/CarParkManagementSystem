<?php
  /**
  * Author: Adam Chin Wai Kin
  */
namespace Database\Factories\Zone;

use App\Models\Zone;
use App\Models\ParkingLevel;
use Illuminate\Http\Request;

class SingleLevelZoneFactory implements ZoneFactoryInterface
{
    public function create(Request $request): Zone
    {
        $zone = Zone::create([
            'zone_code' => $request->zone_code,
            'zone_name' => $request->zone_name,
            'type' => 'single',
            'total_slots' => $request->total_slots,
            'available_slots' => $request->total_slots,
        ]);

        ParkingLevel::create([
            'zone_id' => $zone->id,
            'level_name' => 'Ground',
            'total_slots' => $request->total_slots,
            'available_slots' => $request->total_slots,
        ]);

        return $zone;
    }
}
