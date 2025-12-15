<?php

namespace App\Contracts;

/**
 * OBSERVER PATTERN - Observer Interface
 * Defines the update() method that all concrete observers must implement.
 */
interface ObserverInterface
{
    public function update(SubjectInterface $subject): void;
}
