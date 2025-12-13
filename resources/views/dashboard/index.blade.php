@extends('layout')

@section('title', 'Dashboard - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    <span class="text-muted">Welcome back, {{ $user->name }}!</span>
</div>

{{-- Stats Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
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
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Bookings</h6>
                        <h3 class="mb-0">{{ $stats['total_bookings'] }}</h3>
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
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Completed</h6>
                        <h3 class="mb-0">{{ $stats['completed_bookings'] }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <span class="small">Successful parkings</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
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
