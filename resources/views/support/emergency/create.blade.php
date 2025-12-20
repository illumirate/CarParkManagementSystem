@extends('layout')

@section('title', 'Emergency Report - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1 text-danger"><i class="fas fa-triangle-exclamation me-2"></i>Report Emergency</h2>
        <div class="text-muted">Use this for urgent parking-related emergencies that need immediate attention.</div>
    </div>
    <a href="{{ route('support.tickets.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="alert alert-danger">
    <div class="fw-semibold mb-1"><i class="fas fa-phone me-1"></i>If this is life-threatening:</div>
    <div>Please call emergency services immediately. This form notifies campus support staff/admin quickly.</div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('support.emergency.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Emergency Type</label>
                    <select name="type" class="form-select" required>
                        <option value="" disabled {{ old('type') ? '' : 'selected' }}>Select one...</option>
                        <option value="accident" {{ old('type') === 'accident' ? 'selected' : '' }}>Accident</option>
                        <option value="medical" {{ old('type') === 'medical' ? 'selected' : '' }}>Medical</option>
                        <option value="breakdown" {{ old('type') === 'breakdown' ? 'selected' : '' }}>Vehicle Breakdown</option>
                        <option value="security" {{ old('type') === 'security' ? 'selected' : '' }}>Security Issue</option>
                        <option value="fire" {{ old('type') === 'fire' ? 'selected' : '' }}>Fire/Smoke</option>
                        <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Zone (optional)</label>
                    <input type="text" name="zone" class="form-control" value="{{ old('zone') }}"
                           placeholder="e.g. Arena 4th Floor, Block K, Sports Complex">
                </div>

                <div class="col-12">
                    <label class="form-label">Location Details (optional)</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}"
                           placeholder="e.g. Slot A-15, near lift, entrance gate, level 5">
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="6" class="form-control" required
                              placeholder="Describe what happened and what help you need...">{{ old('description') }}</textarea>
                    <div class="form-text">Include booking number/vehicle plate number if relevant.</div>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Submitted as an <span class="badge bg-danger">Emergency</span> ticket to staff/admin inbox.
                </div>
                <button class="btn btn-danger" type="submit">
                    <i class="fas fa-paper-plane me-1"></i>Submit Emergency Report
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

