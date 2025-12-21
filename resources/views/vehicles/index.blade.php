{{-- Author: Leo Chia Chuen --}}

@extends('layout')

@section('title', 'My Vehicles - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-car me-2"></i>My Vehicles</h2>
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add Vehicle
    </a>
</div>

@if($vehicles->count() > 0)
<div class="row">
    @foreach($vehicles as $vehicle)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 {{ $vehicle->is_primary ? 'border-primary' : '' }}">
            @if($vehicle->is_primary)
            <div class="card-header bg-primary text-white py-2">
                <i class="fas fa-star me-1"></i>Primary Vehicle
            </div>
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="card-title mb-1">{{ $vehicle->plate_number }}</h5>
                        <span class="badge bg-{{ $vehicle->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($vehicle->status) }}
                        </span>
                    </div>
                    <i class="fas fa-{{ $vehicle->vehicle_type === 'car' ? 'car' : 'motorcycle' }} fa-2x text-muted"></i>
                </div>

                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <small class="text-muted">Type:</small><br>
                        {{ ucfirst($vehicle->vehicle_type) }}
                    </li>
                    @if($vehicle->brand || $vehicle->model)
                    <li class="mb-2">
                        <small class="text-muted">Brand/Model:</small><br>
                        {{ $vehicle->brand ?? '' }} {{ $vehicle->model ?? '' }}
                    </li>
                    @endif
                    @if($vehicle->color)
                    <li class="mb-2">
                        <small class="text-muted">Color:</small><br>
                        {{ $vehicle->color }}
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if(!$vehicle->is_primary)
                        <form action="{{ route('vehicles.primary', $vehicle) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Set as Primary">
                                <i class="fas fa-star"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to remove this vehicle?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-car fa-4x text-muted mb-3"></i>
        <h5>No Vehicles Registered</h5>
        <p class="text-muted">Add your vehicle to start booking parking slots.</p>
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Your First Vehicle
        </a>
    </div>
</div>
@endif
@endsection
