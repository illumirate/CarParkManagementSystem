@extends('layout')

@section('title', 'Create Canned Reply - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Create Canned Reply</h2>
    <a href="{{ route('admin.support.templates.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.support.templates.store') }}" method="POST">
            @csrf
            @include('admin.support.templates.partials.form', ['template' => null])
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-save me-1"></i>Create
            </button>
        </form>
    </div>
</div>
@endsection

