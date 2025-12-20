<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportInboxController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $sort = $request->string('sort')->toString();

        $query = SupportTicket::query()
            ->with(['user', 'assignedTo'])
            ->withCount(['messages'])
            ->withCount(['messages as unread_messages_count' => function ($query) {
                $query->whereNull('read_at')
                    ->where('is_internal', false)
                    ->whereColumn('support_ticket_messages.sender_user_id', 'support_tickets.user_id');
            }])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($priority !== '', fn ($q) => $q->where('priority', $priority));

        switch ($sort) {
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Staff members see unassigned + tickets assigned to them.
        // Admins can see all tickets.
        $user = Auth::user();
        if ($user && !$user->isAdmin()) {
            $query->where(function ($q) {
                $q->whereNull('assigned_to_user_id')
                    ->orWhere('assigned_to_user_id', Auth::id());
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        return view('admin.support.tickets.index', [
            'tickets' => $tickets,
            'status' => $status,
            'priority' => $priority,
            'sort' => $sort,
        ]);
    }

    public function show(SupportTicket $ticket): View|RedirectResponse
    {
        if (!$this->canAccessTicket($ticket)) {
            return redirect()->route('admin.support.tickets.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        $this->markUserMessagesRead($ticket);

        $ticket->load([
            'user',
            'assignedTo',
            'messages' => function ($query) {
                $query->withTrashed()->with(['sender', 'attachments'])->oldest();
            },
        ]);

        $templates = \App\Models\SupportReplyTemplate::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('title')
            ->get();

        return view('admin.support.tickets.show', [
            'ticket' => $ticket,
            'templates' => $templates,
        ]);
    }

    public function messages(Request $request, SupportTicket $ticket): JsonResponse
    {
        if (!$this->canAccessTicket($ticket)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $afterId = (int) $request->query('after_id', 0);

        $messages = $ticket->messages()
            ->withTrashed()
            ->when($afterId > 0, fn($q) => $q->where('id', '>', $afterId))
            ->with(['sender', 'attachments'])
            ->oldest()
            ->limit(50)
            ->get();

        $this->markUserMessagesRead($ticket);

        $since = $request->query('since');
        $sinceTime = null;
        if ($since) {
            try {
                $sinceTime = \Carbon\Carbon::parse($since);
            } catch (\Exception $e) {
                $sinceTime = null;
            }
        }
        $deletedIds = [];
        if ($sinceTime) {
            $deletedIds = $ticket->messages()
                ->withTrashed()
                ->whereNotNull('deleted_at')
                ->where('deleted_at', '>', $sinceTime)
                ->pluck('id')
                ->all();
        }

        $payload = $messages->map(function ($m) use ($ticket) {
            $isDeleted = $m->trashed();
            $canDelete = $this->canDeleteMessage($m);
            return [
                'id' => $m->id,
                'sender_user_id' => $m->sender_user_id,
                'sender_name' => $m->sender?->name ?? 'Unknown',
                'is_internal' => (bool) $m->is_internal,
                'is_deleted' => $isDeleted,
                'created_at' => $m->created_at?->toIso8601String(),
                'created_at_human' => $m->created_at?->format('d M Y, h:i A'),
                'message' => $isDeleted ? '' : $m->message,
                'read_at' => $m->read_at?->toDateTimeString(),
                'attachments' => $isDeleted ? [] : $m->attachments->map(function ($a) use ($ticket) {
                    return [
                        'id' => $a->id,
                        'name' => $a->original_name,
                        'mime' => $a->mime_type,
                        'size' => $a->size,
                        'url' => route('admin.support.tickets.attachments.show', [$ticket, $a]),
                    ];
                })->values(),
                'can_delete' => $canDelete,
                'delete_url' => $canDelete ? route('admin.support.tickets.messages.delete', [$ticket, $m]) : null,
            ];
        });

        $readIds = [];
        if ($sinceTime) {
            $readIds = $ticket->messages()
                ->whereNull('deleted_at')
                ->whereNotNull('read_at')
                ->where('sender_user_id', Auth::id())
                ->where('read_at', '>', $sinceTime)
                ->pluck('id')
                ->all();
        }

        return response()->json([
            'messages' => $payload,
            'last_id' => $messages->last()?->id ?? $afterId,
            'deleted_ids' => $deletedIds,
            'read_ids' => $readIds,
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    public function assignToMe(SupportTicket $ticket): RedirectResponse
    {
        if (!$this->canAccessTicket($ticket)) {
            return redirect()->route('admin.support.tickets.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        if ($ticket->assigned_to_user_id && $ticket->assigned_to_user_id !== Auth::id()) {
            return back()->withErrors(['error' => 'This ticket is already assigned to someone else.']);
        }

        $ticket->update([
            'assigned_to_user_id' => Auth::id(),
            'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
        ]);

        return redirect()->route('admin.support.tickets.show', $ticket)
            ->with('success', 'Ticket assigned to you.');
    }

    public function storeMessage(Request $request, SupportTicket $ticket): JsonResponse|RedirectResponse
    {
        if (!$this->canAccessTicket($ticket)) {
            return redirect()->route('admin.support.tickets.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        if ($ticket->status === 'closed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => ['message' => ['This ticket is closed and cannot receive new messages.']],
                ], 422);
            }

            return back()->withErrors(['message' => 'This ticket is closed and cannot receive new messages.']);
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
            'is_internal' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $messageText = trim((string) ($validated['message'] ?? ''));
        if ($messageText === '' && !$request->hasFile('attachment')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => ['message' => ['Please type a message or attach an image.']],
                ], 422);
            }

            return back()->withErrors(['message' => 'Please type a message or attach an image.']);
        }

        $isInternal = (bool) ($validated['is_internal'] ?? false);
        if ($request->hasFile('attachment')) {
            $isInternal = false;
        }

        $message = $ticket->messages()->create([
            'sender_user_id' => Auth::id(),
            'message' => $messageText,
            'is_internal' => $isInternal,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('support_attachments');

            SupportTicketAttachment::create([
                'support_ticket_message_id' => $message->id,
                'uploader_user_id' => Auth::id(),
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $message->load('sender');
        $message->load('attachments');

        if (in_array($ticket->status, ['open'])) {
            $ticket->update(['status' => 'in_progress']);
        }

        if ($request->expectsJson()) {
            $canDelete = $this->canDeleteMessage($message);
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'sender_user_id' => $message->sender_user_id,
                    'sender_name' => $message->sender?->name ?? 'Unknown',
                    'is_internal' => (bool) $message->is_internal,
                    'is_deleted' => false,
                    'created_at' => $message->created_at?->toIso8601String(),
                    'created_at_human' => $message->created_at?->format('d M Y, h:i A'),
                    'message' => $message->message,
                    'read_at' => $message->read_at?->toDateTimeString(),
                    'attachments' => $message->attachments->map(function ($a) use ($ticket) {
                        return [
                            'id' => $a->id,
                            'name' => $a->original_name,
                            'mime' => $a->mime_type,
                            'size' => $a->size,
                            'url' => route('admin.support.tickets.attachments.show', [$ticket, $a]),
                        ];
                    })->values(),
                    'can_delete' => $canDelete,
                    'delete_url' => $canDelete ? route('admin.support.tickets.messages.delete', [$ticket, $message]) : null,
                ],
            ]);
        }

        return redirect()->route('admin.support.tickets.show', $ticket);
    }

    public function deleteMessage(SupportTicket $ticket, SupportTicketMessage $message): JsonResponse|RedirectResponse
    {
        if (!$this->canAccessTicket($ticket)) {
            abort(403);
        }

        if ($ticket->id !== $message->support_ticket_id) {
            abort(404);
        }

        if ($message->sender_user_id !== Auth::id()) {
            abort(403);
        }

        if (!$this->withinDeleteWindow($message)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'errors' => ['message' => ['Deletion window has expired.']],
                ], 422);
            }

            return back()->withErrors(['message' => 'Deletion window has expired.']);
        }

        $message->deleted_by_user_id = Auth::id();
        $message->save();
        $message->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'deleted_id' => $message->id,
                'server_time' => now()->toDateTimeString(),
            ]);
        }

        return redirect()->route('admin.support.tickets.show', $ticket);
    }

    private function withinDeleteWindow(SupportTicketMessage $message): bool
    {
        if (!$message->created_at) {
            return false;
        }

        return $message->created_at->diffInSeconds(now()) <= 60;
    }

    private function canDeleteMessage(SupportTicketMessage $message): bool
    {
        return $message->sender_user_id === Auth::id()
            && !$message->trashed()
            && $this->withinDeleteWindow($message);
    }

    private function markUserMessagesRead(SupportTicket $ticket): void
    {
        $ticket->messages()
            ->whereNull('deleted_at')
            ->whereNull('read_at')
            ->where('is_internal', false)
            ->where('sender_user_id', '!=', Auth::id())
            ->update(['read_at' => now()]);
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        if (!$this->canAccessTicket($ticket)) {
            return redirect()->route('admin.support.tickets.index')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $status = $validated['status'];

        $updates = [
            'status' => $status,
        ];

        if ($status === 'resolved') {
            $updates['resolved_at'] = now();
            $updates['closed_at'] = null;
        } elseif ($status === 'closed') {
            $updates['closed_at'] = now();
        } elseif (in_array($status, ['open', 'in_progress', 'escalated'])) {
            $updates['resolved_at'] = null;
            $updates['closed_at'] = null;
        }

        $ticket->update($updates);

        return redirect()->route('admin.support.tickets.show', $ticket)
            ->with('success', 'Ticket status updated.');
    }

    private function canAccessTicket(SupportTicket $ticket): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Staff can access unassigned tickets or tickets assigned to them.
        return $user->isTarumtStaff()
            && ($ticket->assigned_to_user_id === null || $ticket->assigned_to_user_id === Auth::id());
    }
}
