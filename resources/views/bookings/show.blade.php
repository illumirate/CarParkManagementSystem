@extends('layout')

@section('title', 'Booking Details - TARUMT Car Park')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'secondary') }} text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>Booking {{ $booking->booking_number }}
                    </h5>
                    <span class="badge bg-light text-dark">{{ ucfirst($booking->status) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Parking Details</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">Zone</td>
                                <td><strong>{{ $booking->parkingSlot->zone->zone_name }}</strong></td>
                            </tr>
                            @if($booking->parkingSlot->parkingLevel)
                            <tr>
                                <td class="text-muted">Level</td>
                                <td>{{ $booking->parkingSlot->parkingLevel->level_name }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Slot ID</td>
                                <td><strong class="fs-5">{{ $booking->parkingSlot->slot_id }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Schedule</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">Date</td>
                                <td><strong>{{ $booking->booking_date->format('l, d M Y') }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Time</td>
                                <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Duration</td>
                                <td>{{ $booking->getFormattedDuration() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Vehicle</h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-{{ $booking->vehicle->vehicle_type === 'car' ? 'car' : 'motorcycle' }} fa-2x text-muted me-3"></i>
                                <div>
                                    <strong>{{ $booking->vehicle->plate_number }}</strong><br>
                                    <small class="text-muted">{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}</small>
                                </div>
                            </div>
                            @if($booking->canBeModified())
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changeVehicleModal">
                                <i class="fas fa-edit me-1"></i>Change
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Payment</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Total Fee</td>
                                <td><strong class="text-success fs-5">RM {{ number_format($booking->total_fee, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>
                                    @if($booking->status === 'confirmed' || $booking->status === 'completed')
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Paid</span>
                                    @elseif($booking->status === 'cancelled')
                                    <span class="badge bg-secondary">Refunded</span>
                                    @else
                                    <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($booking->status === 'cancelled')
                <hr>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-ban me-1"></i>Booking Cancelled</h6>
                    <p class="mb-1"><small class="text-muted">Cancelled at: {{ $booking->cancelled_at->format('d M Y, h:i A') }}</small></p>
                    @if($booking->cancellation_reason)
                    <p class="mb-0">Reason: {{ $booking->cancellation_reason }}</p>
                    @endif
                </div>
                @endif

                <hr>

                <div class="row text-muted small">
                    <div class="col-md-6">
                        <i class="fas fa-clock me-1"></i>Booked on: {{ $booking->created_at->format('d M Y, h:i A') }}
                    </div>
                    @if($booking->confirmed_at)
                    <div class="col-md-6">
                        <i class="fas fa-check-circle me-1"></i>Confirmed: {{ $booking->confirmed_at->format('d M Y, h:i A') }}
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Bookings
                    </a>
                    @if($booking->canBeCancelled())
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="fas fa-times me-1"></i>Cancel Booking
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
@if($booking->canBeCancelled())
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking?</p>

                    @php $refund = $booking->getRefundAmount(); @endphp
                    <div class="alert alert-{{ $refund > 0 ? 'info' : 'warning' }}">
                        @if($refund == $booking->total_fee)
                            <i class="fas fa-info-circle me-1"></i>
                            Full refund of <strong>RM {{ number_format($refund, 2) }}</strong> will be credited.
                        @elseif($refund > 0)
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Partial refund of <strong>RM {{ number_format($refund, 2) }}</strong> (50%) will be credited.
                        @else
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            No refund applicable.
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason (optional)</label>
                        <textarea class="form-control" name="cancellation_reason" rows="2"></textarea>
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
@endif

{{-- Change Vehicle Modal --}}
@if($booking->canBeModified())
<div class="modal fade" id="changeVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bookings.updateVehicle', $booking) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Change Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Select a different vehicle for this booking:</p>

                    <div class="mb-3">
                        <label for="vehicle_id" class="form-label">Vehicle <span class="text-danger">*</span></label>
                        <select class="form-select" name="vehicle_id" id="vehicle_id" required>
                            @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ $booking->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->plate_number }} ({{ ucfirst($vehicle->vehicle_type) }}) - {{ $vehicle->brand }} {{ $vehicle->model }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
