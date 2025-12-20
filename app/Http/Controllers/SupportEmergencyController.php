<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\EmergencyReported;
use App\Services\Support\Assignment\SupportTicketAssignmentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportEmergencyController extends Controller
{
    public function create(): View
    {
        return view('support.emergency.create');
    }

    public function store(Request $request, SupportTicketAssignmentManager $assignmentManager): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:accident,medical,breakdown,security,fire,other'],
            'zone' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $typeLabel = match ($validated['type']) {
            'accident' => 'Accident',
            'medical' => 'Medical',
            'breakdown' => 'Vehicle Breakdown',
            'security' => 'Security Issue',
            'fire' => 'Fire/Smoke',
            default => 'Other',
        };

        $lines = [
            "Emergency Type: {$typeLabel}",
        ];

        if (!empty($validated['zone'])) {
            $lines[] = "Zone: {$validated['zone']}";
        }
        if (!empty($validated['location'])) {
            $lines[] = "Location Details: {$validated['location']}";
        }

        $lines[] = '';
        $lines[] = $validated['description'];

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => "Emergency: {$typeLabel}",
            'description' => implode("\n", $lines),
            'status' => 'open',
            'priority' => 'emergency',
        ]);

        $assignmentManager->assign($ticket);

        $recipients = User::query()
            ->where(function ($q) {
                $q->where('role', 'admin')
                    ->orWhere('user_type', 'staff');
            })
            ->where('status', 'active')
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new EmergencyReported($ticket));
        }

        return redirect()->route('support.tickets.show', $ticket)
            ->with('success', 'Emergency report submitted. A support staff/admin will respond ASAP.');
    }
}
