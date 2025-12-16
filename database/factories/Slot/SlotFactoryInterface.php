<?php

namespace Database\Factories\Slot;

use App\Models\ParkingSlot;

interface SlotFactoryInterface
{
    public function create(array $data): ParkingSlot;
}
