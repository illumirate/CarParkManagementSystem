{{-- Author: Ng Ian Kai --}}

@extends('layout')

@section('title', 'My Bookings - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check me-2"></i>Upcoming Bookings</h2>
    <div>
        <a href="{{ route('bookings.history') }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-history me-1"></i>History
        </a>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Booking
        </a>
    </div>
</div>

@if($bookings->count() > 0)
<div class="row">
    @foreach($bookings as $booking)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 border-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
            <div class="card-header bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }} text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-ticket-alt me-1"></i>{{ $booking->booking_number }}</span>
                <span class="badge bg-light text-dark">{{ ucfirst($booking->status) }}</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-calendar fa-fw text-muted me-2"></i>
                        <strong>{{ $booking->booking_date->format('l, d M Y') }}</strong>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-clock fa-fw text-muted me-2"></i>
                        <span>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</span>
                    </div>
                </div>

                <hr>

                <div class="row small">
                    <div class="col-6">
                        <span class="text-muted">Zone</span><br>
                        <strong>{{ $booking->parkingSlot->zone->zone_name }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Slot</span><br>
                        <strong>{{ $booking->parkingSlot->slot_id }}</strong>
                    </div>
                </div>

                <div class="row small mt-2">
                    <div class="col-6">
                        <span class="text-muted">Vehicle</span><br>
                        <strong>{{ $booking->vehicle->plate_number }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Fee</span><br>
                        <strong class="text-success">RM {{ number_format($booking->total_fee, 2) }}</strong>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Details
                    </a>
                    @if($booking->canBeCancelled())
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#cancelModal{{ $booking->id }}">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Modal --}}
    <div class="modal fade" id="cancelModal{{ $booking->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Booking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel booking <strong>{{ $booking->booking_number }}</strong>?</p>

                        @php $refund = $booking->getRefundAmount(); @endphp
                        <div class="alert alert-{{ $refund > 0 ? 'info' : 'warning' }}">
                            @if($refund == $booking->total_fee)
                                <i class="fas fa-info-circle me-1"></i>
                                Full refund of <strong>RM {{ number_format($refund, 2) }}</strong> will be credited to your account.
                            @elseif($refund > 0)
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Partial refund of <strong>RM {{ number_format($refund, 2) }}</strong> (50%) will be credited.
                            @else
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                No refund applicable as the booking has already started or passed.
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">Reason (optional)</label>
                            <textarea class="form-control" name="cancellation_reason" rows="2"
                                      placeholder="Why are you cancelling?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Booking</button>
                        <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
        <h5>No Upcoming Bookings</h5>
        <p class="text-muted">You don't have any upcoming parking reservations.</p>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Book a Parking Slot
        </a>
    </div>
</div>
@endif
@endsection
