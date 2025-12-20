@extends('layout')

@section('title', 'Help & Parking Guidelines - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-book me-2"></i>Help & Parking Guidelines</h2>
        <div class="text-muted">Quick guides for booking, payments, and parking rules.</div>
    </div>
    @auth
    <div class="d-flex gap-2">
        <a href="{{ route('support.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create Support Ticket
        </a>
        <a href="{{ route('support.tickets.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-ticket-alt me-1"></i>My Tickets
        </a>
    </div>
    @endauth
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-list-check me-2"></i>Contents</h5>
                @if(isset($articles) && $articles->count())
                    <ul class="list-unstyled mb-0">
                        @foreach($articles->groupBy(fn($a) => $a->category ?: 'General') as $cat => $group)
                            <li class="mt-3 text-muted small fw-semibold">{{ $cat }}</li>
                            @foreach($group as $article)
                                <li class="mb-2">
                                    <a class="text-decoration-none" href="#{{ $article->slug }}">{{ $article->title }}</a>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted small">
                        No help articles yet. Please contact admin to add content.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @if(isset($articles) && $articles->count())
            @foreach($articles->groupBy(fn($a) => $a->category ?: 'General') as $cat => $group)
                <div class="mb-3">
                    <h4 class="text-muted">{{ $cat }}</h4>
                </div>
                @foreach($group as $article)
                    <div class="card mb-3" id="{{ $article->slug }}">
                        <div class="card-body">
                            <h4 class="mb-3"><i class="fas fa-file-lines me-2"></i>{{ $article->title }}</h4>
                            <div class="small">{!! nl2br(e($article->content)) !!}</div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                    <h5>No Help Content Yet</h5>
                    <p class="text-muted">Admin can add help articles from the Help Docs page.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
