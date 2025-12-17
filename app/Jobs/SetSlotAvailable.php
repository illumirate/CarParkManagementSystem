<?php

namespace App\Jobs;

use App\Models\ParkingSlot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetSlotAvailable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $slotId;

    public function __construct($slotId)
    {
        $this->slotId = $slotId;
    }

    public function handle()
    {
        $slot = ParkingSlot::find($this->slotId);
        if ($slot && $slot->status === 'maintenance') {
            $slot->status = 'available';
            $slot->save();
        }
    }
}
