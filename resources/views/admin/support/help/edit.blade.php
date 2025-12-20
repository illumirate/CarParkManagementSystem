@extends('layout')

@section('title', 'Edit Help Article - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-pen me-2"></i>Edit Help Article</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.support.help.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('support.help') }}#{{ $article->slug }}" class="btn btn-outline-primary">
            <i class="fas fa-eye me-1"></i>View on Help Page
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.support.help.update', $article) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.support.help.partials.form', ['article' => $article])
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-save me-1"></i>Save Changes
            </button>
        </form>
    </div>
</div>
@endsection

