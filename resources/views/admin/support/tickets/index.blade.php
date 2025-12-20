@extends('layout')

@section('title', 'Support Inbox - TARUMT Car Park')

@push('styles')
<style>
    .unread-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #dc3545;
        margin-left: 6px;
        vertical-align: middle;
        box-shadow: 0 0 0 1px #fff;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-inbox me-2"></i>Support Inbox</h2>
    <form class="d-flex flex-wrap gap-2" method="GET" action="{{ route('admin.support.tickets.index') }}">
        <select class="form-select me-2" name="priority">
            <option value="">All Priorities</option>
            @foreach(['emergency','urgent','normal'] as $opt)
                <option value="{{ $opt }}" {{ ($priority ?? '') === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
            @endforeach
        </select>
        <select class="form-select me-2" name="status">
            <option value="">All Statuses</option>
            @foreach(['open','in_progress','resolved','closed'] as $opt)
                <option value="{{ $opt }}" {{ $status === $opt ? 'selected' : '' }}>{{ str_replace('_',' ', ucfirst($opt)) }}</option>
            @endforeach
        </select>
        <select class="form-select me-2" name="sort">
            <option value="created_desc" {{ ($sort ?? '') === 'created_desc' || ($sort ?? '') === '' ? 'selected' : '' }}>Created (Newest)</option>
            <option value="created_asc" {{ ($sort ?? '') === 'created_asc' ? 'selected' : '' }}>Created (Oldest)</option>
        </select>
        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
        <a class="btn btn-outline-secondary ms-2" href="{{ route('admin.support.tickets.index') }}">
            <i class="fas fa-rotate-left me-1"></i>Reset
        </a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Ticket</th>
                <th>User</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Assigned</th>
                <th class="text-end">Updated</th>
            </tr>
            </thead>
            <tbody>
            @forelse($tickets as $ticket)
            <tr>
                <td class="fw-semibold">
                    <a href="{{ route('admin.support.tickets.show', $ticket) }}" class="text-decoration-none">
                        {{ $ticket->ticket_number }}
                        @if(($ticket->unread_messages_count ?? 0) > 0)
                            <span class="unread-dot" title="Unread message"></span>
                        @endif
                    </a>
                </td>
                <td>{{ $ticket->user?->name ?? 'Unknown' }}</td>
                <td>{{ $ticket->subject }}</td>
                <td>
                    <span class="badge bg-{{ in_array($ticket->status, ['open','in_progress']) ? 'warning' : ($ticket->status === 'resolved' ? 'success' : 'secondary') }}">
                        {{ str_replace('_',' ', ucfirst($ticket->status)) }}
                    </span>
                    @if(($ticket->priority ?? 'normal') === 'emergency')
                        <span class="badge bg-danger ms-1">Emergency</span>
                    @elseif(($ticket->priority ?? 'normal') === 'urgent')
                        <span class="badge bg-warning text-dark ms-1">Urgent</span>
                    @endif
                </td>
                <td>{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</td>
                <td class="text-end text-muted small">{{ $ticket->updated_at->format('d M Y, h:i A') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <div>No tickets found.</div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $tickets->links() }}
</div>
@endsection
