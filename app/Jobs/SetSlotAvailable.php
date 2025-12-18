<?php

namespace App\Jobs;

use App\Models\ParkingSlot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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

        if (!$slot) {
            Log::error("Slot ID {$this->slotId} not found when trying to set available");
            return;
        }

        $slot->refresh();

        if ($slot->status === 'maintenance') {
            $slot->status = 'available';
            $slot->save();
            Log::info("Slot {$slot->slot_id} status set to available by SetSlotAvailable job");
        } else {
            Log::info("Slot {$slot->slot_id} not in maintenance, skipping SetSlotAvailable job");
        }
    }
}

