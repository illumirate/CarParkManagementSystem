<?php
//  Author: Leo Chia Chuen

namespace App\Observers\Auth;

use App\Contracts\ObserverInterface;
use App\Contracts\SubjectInterface;
use App\Models\User;

/**
 * OBSERVER PATTERN - Concrete Subject (SubjectImpl)
 * Manages list of observers and notifies them when state changes.
 */
class UserEventSubject implements SubjectInterface
{
    private array $observers = [];
    private array $state = [];

    public function register(ObserverInterface $observer): void
    {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    public function unregister(ObserverInterface $observer): void
    {
        unset($this->observers[spl_object_hash($observer)]);
    }

    public function notifyAll(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function getState(): mixed
    {
        return $this->state;
    }

    public function setState(string $event, User $user): void
    {
        $this->state = ['event' => $event, 'user' => $user];
        $this->notifyAll();
    }
}
