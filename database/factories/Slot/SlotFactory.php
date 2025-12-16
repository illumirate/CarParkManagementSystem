<?php

namespace Database\Factories\Slot;

class SlotFactory
{
    public static function make(string $type): SlotFactoryInterface
    {
        return match($type) {
            'staff'   => new StaffSlotFactory(),
            'vip'     => new VipSlotFactory(),
            default   => new RegularSlotFactory(),
        };
    }
}
