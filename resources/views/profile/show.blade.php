{{-- Author: Leo Chia Chuen --}}

@extends('layout')

@section('title', 'My Profile - TARUMT Car Park')

@section('content')
<div class="row">
    <div class="col-md-4">
        {{-- Profile Card --}}
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                <span class="badge bg-{{ $user->isStudent() ? 'info' : 'secondary' }}">
                    {{ ucfirst($user->user_type ?? 'User') }}
                </span>
                @if($user->isAdmin())
                <span class="badge bg-danger">Admin</span>
                @endif
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-wallet me-2 text-success"></i>Credit Balance</span>
                    <strong class="text-success">RM {{ number_format($creditBalance, 2) }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-car me-2 text-primary"></i>Vehicles</span>
                    <strong>{{ $vehicles->count() }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-check-circle me-2 text-info"></i>Status</span>
                    <span class="badge bg-{{ $user->isActive() ? 'success' : 'danger' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </li>
            </ul>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </a>
                    <a href="{{ route('profile.password') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-key me-1"></i>Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Profile Details --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <i class="fas fa-user me-2"></i>Profile Information
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Full Name</div>
                    <div class="col-md-8">{{ $user->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Email Address</div>
                    <div class="col-md-8">
                        {{ $user->email }}
                        @if($user->hasVerifiedEmail())
                        <span class="badge bg-success ms-2"><i class="fas fa-check me-1"></i>Verified</span>
                        @else
                        <span class="badge bg-warning ms-2"><i class="fas fa-exclamation me-1"></i>Not Verified</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Phone Number</div>
                    <div class="col-md-8">{{ $user->phone ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">{{ $user->isStudent() ? 'Student ID' : 'Staff ID' }}</div>
                    <div class="col-md-8">{{ $user->student_id ?? $user->staff_id ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Faculty</div>
                    <div class="col-md-8">{{ $user->faculty ?? '-' }}</div>
                </div>
                @if($user->isStudent())
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Course</div>
                    <div class="col-md-8">{{ $user->course ?? '-' }}</div>
                </div>
                @else
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Department</div>
                    <div class="col-md-8">{{ $user->department ?? '-' }}</div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-4 text-muted">Member Since</div>
                    <div class="col-md-8">{{ $user->created_at->format('d M Y') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-4 text-muted">Last Login</div>
                    <div class="col-md-8">{{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : 'Never' }}</div>
                </div>
            </div>
        </div>

        {{-- Registered Vehicles --}}
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-car me-2"></i>Registered Vehicles</span>
                <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Add Vehicle
                </a>
            </div>
            <div class="card-body p-0">
                @if($vehicles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plate Number</th>
                                <th>Type</th>
                                <th>Brand/Model</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $vehicle)
                            <tr>
                                <td>
                                    <strong>{{ $vehicle->plate_number }}</strong>
                                    @if($vehicle->is_primary)
                                    <span class="badge bg-primary ms-1">Primary</span>
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-{{ $vehicle->vehicle_type === 'car' ? 'car' : 'motorcycle' }} me-1"></i>
                                    {{ ucfirst($vehicle->vehicle_type) }}
                                </td>
                                <td>{{ $vehicle->brand ?? '-' }} {{ $vehicle->model ?? '' }}</td>
                                <td>
                                    <span class="badge bg-{{ $vehicle->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($vehicle->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-car fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No vehicles registered</p>
                    <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Your First Vehicle
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
