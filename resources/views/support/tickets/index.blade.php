@extends('layout')

@section('title', 'Support Tickets - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-headset me-2"></i>Support Tickets</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('support.help') }}" class="btn btn-outline-secondary">
            <i class="fas fa-book me-1"></i>Help
        </a>
        <a href="{{ route('support.emergency.create') }}" class="btn btn-outline-danger">
            <i class="fas fa-triangle-exclamation me-1"></i>Emergency
        </a>
        <a href="{{ route('support.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Ticket
        </a>
    </div>
</div>

@if($tickets->count() > 0)
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Ticket</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th class="text-end">Updated</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td class="fw-semibold">
                    <a href="{{ route('support.tickets.show', $ticket) }}" class="text-decoration-none">
                        {{ $ticket->ticket_number }}
                    </a>
                </td>
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
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $tickets->links() }}
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-life-ring fa-4x text-muted mb-3"></i>
        <h5>No Support Tickets</h5>
        <p class="text-muted">Need help? Create a ticket and our team will respond.</p>
        <a href="{{ route('support.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create Ticket
        </a>
    </div>
</div>
@endif
@endsection
