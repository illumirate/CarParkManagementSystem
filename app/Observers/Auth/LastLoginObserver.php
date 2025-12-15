<?php

namespace App\Observers\Auth;

use App\Contracts\ObserverInterface;
use App\Contracts\SubjectInterface;
use Illuminate\Support\Facades\Log;

/**
 * OBSERVER PATTERN - Concrete Observer 2
 * Handles user.logged_in and user.logged_out events.
 */
class LastLoginObserver implements ObserverInterface
{
    public function update(SubjectInterface $subject): void
    {
        $state = $subject->getState();
        $user = $state['user'];
        $event = $state['event'];

        if ($event === 'user.logged_in') {
            // Update last login timestamp
            $user->update(['last_login_at' => now()]);
            Log::info("[OBSERVER] LastLoginObserver: User {$user->email} logged in at " . now());
        }

        if ($event === 'user.logged_out') {
            // Log the logout event for audit trail
            Log::info("[OBSERVER] LastLoginObserver: User {$user->email} logged out at " . now());
        }
    }
}
