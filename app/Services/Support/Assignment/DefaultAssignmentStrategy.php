<?php

namespace App\Services\Support\Assignment;

use App\Models\SupportTicket;

class DefaultAssignmentStrategy implements SupportTicketAssignmentStrategy
{
    /**
     * Default behavior: keep ticket unassigned (manual assignment).
     */
    public function assign(SupportTicket $ticket): void
    {
        // No auto-assignment for normal tickets.
    }
}

