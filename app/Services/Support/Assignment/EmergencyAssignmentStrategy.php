<?php

namespace App\Services\Support\Assignment;

use App\Models\SupportTicket;
use App\Models\User;

class EmergencyAssignmentStrategy implements SupportTicketAssignmentStrategy
{
    /**
     * Assign emergency tickets to an available admin first, otherwise staff.
     */
    public function assign(SupportTicket $ticket): void
    {
        if ($ticket->assigned_to_user_id) {
            return;
        }

        $assignee = User::query()
            ->where('status', 'active')
            ->where('role', 'admin')
            ->orderBy('id')
            ->first();

        if (!$assignee) {
            $assignee = User::query()
                ->where('status', 'active')
                ->where('user_type', 'staff')
                ->orderBy('id')
                ->first();
        }

        if (!$assignee) {
            return;
        }

        $ticket->update([
            'assigned_to_user_id' => $assignee->id,
            'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
        ]);
    }
}

