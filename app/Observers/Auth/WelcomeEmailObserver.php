<?php

namespace App\Observers\Auth;

use App\Contracts\ObserverInterface;
use App\Contracts\SubjectInterface;
use Illuminate\Support\Facades\Log;

/**
 * OBSERVER PATTERN - Concrete Observer 1
 * Handles user.registered event by sending email verification notification.
 */
class WelcomeEmailObserver implements ObserverInterface
{
    public function update(SubjectInterface $subject): void
    {
        $state = $subject->getState();

        if ($state['event'] !== 'user.registered') {
            return;
        }

        $user = $state['user'];

        // Send the actual email verification notification
        $user->sendEmailVerificationNotification();

        Log::info("[OBSERVER] WelcomeEmailObserver: Verification email sent to {$user->email}");
    }
}
