<?php

namespace Database\Factories\Slot;

use App\Models\ParkingSlot;

class VipSlotFactory implements SlotFactoryInterface
{
    public function create(array $data): ParkingSlot
    {
        return ParkingSlot::create([
            'slot_id'   => $data['slot_id'],
            'zone_id'   => $data['zone_id'],
            'level_id'  => $data['level_id'],
            'status'    => 'reserved',
            'type'      => 'vip',
        ]);
    }
}
