<?php
//  Author: Leo Chia Chuen

namespace App\Services;

use App\Models\User;
use App\Observers\Auth\LastLoginObserver;
use App\Observers\Auth\UserEventSubject;
use App\Observers\Auth\WelcomeEmailObserver;

/**
 * OBSERVER PATTERN - Service Manager
 *
 * Manages Observer Pattern for user authentication events.
 * Creates Subject, registers Observers, and triggers notifications.
 */
class UserEventService
{
    private UserEventSubject $subject;
    private array $observers = [];

    public function __construct()
    {
        // Create Subject
        $this->subject = new UserEventSubject();

        // Create and register Concrete Observers
        $this->observers = [
            'welcome_email' => new WelcomeEmailObserver(),
            'last_login' => new LastLoginObserver(),
        ];

        foreach ($this->observers as $observer) {
            $this->subject->register($observer);
        }
    }

    public function userRegistered(User $user): void
    {
        $this->subject->setState('user.registered', $user);
    }

    public function userLoggedIn(User $user): void
    {
        $this->subject->setState('user.logged_in', $user);
    }

    public function userLoggedOut(User $user): void
    {
        $this->subject->setState('user.logged_out', $user);
    }
}
