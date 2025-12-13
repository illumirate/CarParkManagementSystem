<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related reference model (Payment, Booking, etc.)
     */
    public function reference()
    {
        if ($this->reference_type && $this->reference_id) {
            $modelClass = 'App\\Models\\' . $this->reference_type;
            if (class_exists($modelClass)) {
                return $modelClass::find($this->reference_id);
            }
        }
        return null;
    }

    // ==================== SCOPES ====================

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==================== HELPER METHODS ====================

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    /**
     * Get formatted amount with +/- sign
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->isCredit() ? '+' : '-';
        return $sign . ' RM ' . number_format($this->amount, 2);
    }
}
