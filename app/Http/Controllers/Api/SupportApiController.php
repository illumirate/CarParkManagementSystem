<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SupportTicket;
use App\Services\Support\Assignment\SupportTicketAssignmentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportApiController extends Controller
{
    /**
     * Get booking report info (API).
     */
    public function getBookingReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
        ]);

        $ticket = SupportTicket::query()
            ->where('booking_id', $validated['booking_id'])
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => true,
                'exists' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => true,
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'issue_type' => $ticket->issue_type ?? str_replace('Booking Issue: ', '', $ticket->subject),
        ]);
    }

    /**
     * Create a support ticket for a booking-related issue (API).
     */
    public function createBookingTicket(Request $request, SupportTicketAssignmentManager $assignmentManager): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'issue_type' => ['required', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking = Booking::with(['user', 'parkingSlot.zone', 'vehicle'])->findOrFail($validated['booking_id']);

        $existingTicket = SupportTicket::query()
            ->where('booking_id', $booking->id)
            ->first();

        if ($existingTicket) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'ticket_id' => $existingTicket->id,
                'ticket_number' => $existingTicket->ticket_number,
                'issue_type' => $existingTicket->issue_type ?? str_replace('Booking Issue: ', '', $existingTicket->subject),
            ]);
        }

        $subject = 'Booking Issue: ' . $validated['issue_type'];

        $lines = [
            "Booking Number: {$booking->booking_number}",
            "User: {$booking->user?->name} ({$booking->user?->email})",
            "Zone: {$booking->parkingSlot?->zone?->zone_name}",
            "Slot: {$booking->parkingSlot?->slot_id}",
            "Vehicle: {$booking->vehicle?->plate_number}",
            "Date: {$booking->booking_date->format('Y-m-d')}",
            "Time: {$booking->start_time->format('H:i')} - {$booking->end_time->format('H:i')}",
        ];

        if (!empty($validated['message'])) {
            $lines[] = '';
            $lines[] = 'Reported Issue:';
            $lines[] = $validated['message'];
        }

        $ticket = SupportTicket::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'subject' => $subject,
            'issue_type' => $validated['issue_type'],
            'description' => implode("\n", array_filter($lines)),
            'status' => 'open',
            'priority' => 'normal',
        ]);

        $assignmentManager->assign($ticket);

        return response()->json([
            'success' => true,
            'exists' => false,
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'issue_type' => $ticket->issue_type,
        ], 201);
    }
}
