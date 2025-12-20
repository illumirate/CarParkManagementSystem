<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupportTicketAttachmentController extends Controller
{
    public function show(SupportTicket $ticket, SupportTicketAttachment $attachment)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if (!$user->isAdmin() && !$user->isTarumtStaff() && $ticket->user_id !== $user->id) {
            abort(403);
        }

        $message = $attachment->message;
        if (!$message || $message->support_ticket_id !== $ticket->id || $message->trashed()) {
            abort(404);
        }

        $disk = Storage::disk('local');
        if (!$disk->exists($attachment->stored_path)) {
            abort(404);
        }

        return $disk->response($attachment->stored_path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"',
        ]);
    }
}
