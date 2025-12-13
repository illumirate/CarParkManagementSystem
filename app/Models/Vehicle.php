<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'plate_number',
        'vehicle_type',
        'brand',
        'model',
        'color',
        'is_primary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // ==================== HELPER METHODS ====================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPrimary(): bool
    {
        return $this->is_primary === true;
    }

    /**
     * Get formatted plate number (uppercase)
     */
    public function getFormattedPlateNumberAttribute(): string
    {
        return strtoupper($this->plate_number);
    }
}
