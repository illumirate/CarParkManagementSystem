<?php

namespace App\Services\Support\Assignment;

use App\Models\SupportTicket;

class SupportTicketAssignmentManager
{
    public function __construct(
        private EmergencyAssignmentStrategy $emergencyStrategy,
        private DefaultAssignmentStrategy $defaultStrategy,
    ) {
    }

    public function assign(SupportTicket $ticket): void
    {
        if ($ticket->priority === 'emergency') {
            $this->emergencyStrategy->assign($ticket);
            return;
        }

        $this->defaultStrategy->assign($ticket);
    }
}

