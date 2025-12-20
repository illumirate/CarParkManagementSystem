<?php

namespace App\Services\Support\Assignment;

use App\Models\SupportTicket;

interface SupportTicketAssignmentStrategy
{
    /**
     * Apply assignment rules to a support ticket.
     */
    public function assign(SupportTicket $ticket): void;
}

