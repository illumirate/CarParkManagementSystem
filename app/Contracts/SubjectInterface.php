<?php

namespace App\Contracts;

/**
 * OBSERVER PATTERN - Subject Interface
 * Defines register(), unregister(), notifyAll(), and getState() methods.
 */
interface SubjectInterface
{
    public function register(ObserverInterface $observer): void;
    public function unregister(ObserverInterface $observer): void;
    public function notifyAll(): void;
    public function getState(): mixed;
}
