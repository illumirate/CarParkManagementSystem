<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Add credits to user account.
     */
    public function addCredits(
        User $user,
        float $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): CreditTransaction {
        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId) {
            $balanceBefore = $user->credit_balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update user balance
            $user->update(['credit_balance' => $balanceAfter]);

            // Create transaction record
            return CreditTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    /**
     * Deduct credits from user account.
     */
    public function deductCredits(
        User $user,
        float $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): CreditTransaction {
        if (!$this->hasEnoughCredits($user, $amount)) {
            throw new \Exception('Insufficient credits.');
        }

        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId) {
            // Refresh user to get latest balance
            $user->refresh();

            $balanceBefore = $user->credit_balance;
            $balanceAfter = $balanceBefore - $amount;

            // Update user balance
            $user->update(['credit_balance' => $balanceAfter]);

            // Create transaction record
            return CreditTransaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    /**
     * Check if user has enough credits.
     */
    public function hasEnoughCredits(User $user, float $amount): bool
    {
        return $user->credit_balance >= $amount;
    }

    /**
     * Get user's transaction history.
     */
    public function getTransactionHistory(User $user, int $limit = 20)
    {
        return $user->creditTransactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's current balance.
     */
    public function getBalance(User $user): float
    {
        return $user->credit_balance;
    }
}
