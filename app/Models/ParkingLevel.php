<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingLevel extends Model
{
    protected $fillable = [
        'zone_id',
        'level_name',
        'total_slots',
        'available_slots',
    ];

    // ==================== RELATIONSHIPS ====================

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function parkingSlots(): HasMany
    {
        return $this->hasMany(ParkingSlot::class, 'level_id');
    }
}
