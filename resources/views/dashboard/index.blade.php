@extends('layout')

@section('title', 'Dashboard - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    <span class="text-muted">Welcome back, {{ $user->name }}!</span>
</div>

{{-- Stats Cards (via Web Service API) --}}
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Credit Balance</h6>
                        <h3 class="mb-0">RM {{ number_format($stats['credit_balance'], 2) }}</h3>
                    </div>
                    <i class="fas fa-wallet fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('credits.index') }}" class="text-white text-decoration-none small">
                    <i class="fas fa-plus-circle me-1"></i>Top Up Credits
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Bookings</h6>
                        <h3 class="mb-0" id="stats-total-bookings">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </h3>
                    </div>
                    <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('bookings.history') }}" class="text-white text-decoration-none small">
                    <i class="fas fa-history me-1"></i>View History
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Completed</h6>
                        <h3 class="mb-0" id="stats-completed-bookings">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <span class="small">Successful parkings</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Vehicles</h6>
                        <h3 class="mb-0">{{ $stats['vehicles'] }}</h3>
                    </div>
                    <i class="fas fa-car fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('vehicles.index') }}" class="text-white text-decoration-none small">
                    <i class="fas fa-cog me-1"></i>Manage Vehicles
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Detailed Booking Stats (from API via AJAX) --}}
<div class="card mb-4" id="booking-stats-card">
    <div class="card-header bg-white">
        <i class="fas fa-chart-bar me-2"></i>Booking Statistics
        <small class="text-muted">(via Web Service API)</small>
    </div>
    <div class="card-body">
        <div class="row text-center" id="booking-stats-content">
            <div class="col-12 text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading booking statistics...</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Active Booking --}}
    <div class="col-md-8">
        @if($activeBooking)
        <div class="card border-success mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-parking me-2"></i>Active Parking Session
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Booking #:</strong> {{ $activeBooking->booking_number }}</p>
                        <p><strong>Zone:</strong> {{ $activeBooking->parkingSlot->zone->zone_name }}</p>
                        <p><strong>Slot:</strong> {{ $activeBooking->parkingSlot->slot_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Vehicle:</strong> {{ $activeBooking->vehicle->plate_number }}</p>
                        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($activeBooking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($activeBooking->end_time)->format('h:i A') }}</p>
                        <p><strong>Fee:</strong> RM {{ number_format($activeBooking->total_fee, 2) }}</p>
                    </div>
                </div>
                <a href="{{ route('bookings.show', $activeBooking) }}" class="btn btn-success">
                    <i class="fas fa-eye me-1"></i>View Details
                </a>
            </div>
        </div>
        @endif

        {{-- Upcoming Bookings --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar me-2"></i>Upcoming Bookings</span>
                <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>New Booking
                </a>
            </div>
            <div class="card-body p-0">
                @if($upcomingBookings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking #</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Zone</th>
                                <th>Slot</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingBookings as $booking)
                            <tr>
                                <td><code>{{ $booking->booking_number }}</code></td>
                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}</td>
                                <td>{{ $booking->parkingSlot->zone->zone_name }}</td>
                                <td>{{ $booking->parkingSlot->slot_number }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
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
                @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No upcoming bookings</p>
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Book a Parking Slot
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-exchange-alt me-2"></i>Recent Transactions</span>
                <a href="{{ route('payments.history') }}" class="btn btn-link btn-sm p-0">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentTransactions->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($recentTransactions as $transaction)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">{{ $transaction->created_at->format('d M Y, h:i A') }}</small>
                            <span>{{ $transaction->description }}</span>
                        </div>
                        <span class="badge bg-{{ $transaction->type === 'credit' ? 'success' : 'danger' }}">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}RM {{ number_format($transaction->amount, 2) }}
                        </span>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0 small">No transactions yet</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card mt-4">
            <div class="card-header bg-white">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                        <i class="fas fa-parking me-2"></i>Book Parking Slot
                    </a>
                    <a href="{{ route('credits.index') }}" class="btn btn-success">
                        <i class="fas fa-plus-circle me-2"></i>Top Up Credits
                    </a>
                    <a href="{{ route('vehicles.create') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-car me-2"></i>Add Vehicle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// WEB SERVICES - Consume Booking Stats API via Frontend AJAX
document.addEventListener('DOMContentLoaded', function() {
    const statsContainer = document.getElementById('booking-stats-content');
    const userId = {{ Auth::id() }};
    const requestId = 'REQ_' + Date.now();
    const timestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');

    fetch(`/api/bookings/stats?user_id=${userId}&requestId=${requestId}&timestamp=${encodeURIComponent(timestamp)}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'S') {
                const stats = data.data;

                // Update stats cards
                document.getElementById('stats-total-bookings').textContent = stats.total_bookings;
                document.getElementById('stats-completed-bookings').textContent = stats.completed_bookings;

                // Update detailed stats card
                statsContainer.innerHTML = `
                    <div class="col-md-3 col-6 mb-3">
                        <div class="h4 mb-0 text-primary">${stats.total_bookings}</div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="h4 mb-0 text-success">${stats.confirmed_bookings}</div>
                        <small class="text-muted">Confirmed</small>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="h4 mb-0 text-info">${stats.completed_bookings}</div>
                        <small class="text-muted">Completed</small>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="h4 mb-0 text-danger">${stats.cancelled_bookings}</div>
                        <small class="text-muted">Cancelled</small>
                    </div>
                    <div class="col-12 mt-2">
                        <hr class="my-2">
                        <span class="text-muted">Total Spent:</span>
                        <strong class="text-success ms-2">RM ${parseFloat(stats.total_spent).toFixed(2)}</strong>
                    </div>
                `;
                console.log('[API CONSUMED] Booking Stats API called successfully via frontend', { requestId });
            } else {
                document.getElementById('stats-total-bookings').textContent = '0';
                document.getElementById('stats-completed-bookings').textContent = '0';
                statsContainer.innerHTML = '<div class="col-12 text-danger">Failed to load statistics</div>';
            }
        })
        .catch(error => {
            console.error('[API CONSUMED] Failed to fetch booking stats:', error);
            document.getElementById('stats-total-bookings').textContent = '-';
            document.getElementById('stats-completed-bookings').textContent = '-';
            statsContainer.innerHTML = '<div class="col-12 text-danger">Error loading statistics</div>';
        });
});
</script>
@endpush
