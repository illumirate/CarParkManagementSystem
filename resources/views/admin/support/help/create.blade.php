@extends('layout')

@section('title', 'Create Help Article - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Create Help Article</h2>
    <a href="{{ route('admin.support.help.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.support.help.store') }}" method="POST">
            @csrf
            @include('admin.support.help.partials.form', ['article' => null])
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-save me-1"></i>Create
            </button>
        </form>
    </div>
</div>
@endsection

