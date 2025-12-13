@extends('layout')

@section('title', 'Booking History - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-history me-2"></i>Booking History</h2>
    <a href="{{ route('bookings.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-calendar me-1"></i>Upcoming
    </a>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        @if($bookings->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking #</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Zone / Slot</th>
                        <th>Vehicle</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr>
                        <td><code>{{ $booking->booking_number }}</code></td>
                        <td>{{ $booking->booking_date->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}</td>
                        <td>
                            {{ $booking->parkingSlot->zone->zone_name }}<br>
                            <small class="text-muted">{{ $booking->parkingSlot->slot_number }}</small>
                        </td>
                        <td>{{ $booking->vehicle->plate_number }}</td>
                        <td>RM {{ number_format($booking->total_fee, 2) }}</td>
                        <td>
                            @switch($booking->status)
                                @case('completed')
                                    <span class="badge bg-success">Completed</span>
                                    @break
                                @case('confirmed')
                                    <span class="badge bg-primary">Confirmed</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                    @break
                                @case('expired')
                                    <span class="badge bg-secondary">Expired</span>
                                    @break
                                @default
                                    <span class="badge bg-warning">{{ ucfirst($booking->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $bookings->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-history fa-4x text-muted mb-3"></i>
            <h5>No Booking History</h5>
            <p class="text-muted">You haven't made any bookings yet.</p>
            <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Book Your First Slot
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
