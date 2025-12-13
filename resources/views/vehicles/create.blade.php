@extends('layout')

@section('title', 'Add Vehicle - TARUMT Car Park')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-plus me-2"></i>Add New Vehicle
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('vehicles.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="plate_number" class="form-label">Plate Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control @error('plate_number') is-invalid @enderror"
                                   id="plate_number" name="plate_number" value="{{ old('plate_number') }}"
                                   placeholder="e.g., ABC 1234" required style="text-transform: uppercase;">
                        </div>
                        <div class="form-text">Enter Malaysian vehicle plate number</div>
                        @error('plate_number')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="vehicle_type" id="car"
                                       value="car" {{ old('vehicle_type', 'car') == 'car' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="car">
                                    <i class="fas fa-car me-1"></i>Car
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="vehicle_type" id="motorcycle"
                                       value="motorcycle" {{ old('vehicle_type') == 'motorcycle' ? 'checked' : '' }}>
                                <label class="form-check-label" for="motorcycle">
                                    <i class="fas fa-motorcycle me-1"></i>Motorcycle
                                </label>
                            </div>
                        </div>
                        @error('vehicle_type')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" class="form-control @error('brand') is-invalid @enderror"
                                   id="brand" name="brand" value="{{ old('brand') }}"
                                   placeholder="e.g., Toyota, Honda">
                            @error('brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control @error('model') is-invalid @enderror"
                                   id="model" name="model" value="{{ old('model') }}"
                                   placeholder="e.g., Vios, City">
                            @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control @error('color') is-invalid @enderror"
                               id="color" name="color" value="{{ old('color') }}"
                               placeholder="e.g., White, Black, Silver">
                        @error('color')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Add Vehicle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
