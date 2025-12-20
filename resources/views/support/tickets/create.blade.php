@extends('layout')

@section('title', 'Create Support Ticket - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Create Support Ticket</h2>
    <a href="{{ route('support.tickets.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('support.tickets.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
                <div class="form-text">Example: Booking issue, Payment issue, Account problem</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <textarea name="description" rows="5" class="form-control" placeholder="Describe your issue...">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane me-1"></i>Submit Ticket
            </button>
        </form>
    </div>
</div>
@endsection

