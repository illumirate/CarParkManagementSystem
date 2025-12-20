@extends('layout')

@section('title', 'Manage Help Articles - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-book me-2"></i>Manage Help Articles</h2>
        <div class="text-muted">Create and update help documentation shown to users.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('support.help') }}" class="btn btn-outline-secondary">
            <i class="fas fa-eye me-1"></i>View User Help Page
        </a>
        <a href="{{ route('admin.support.help.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Article
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Slug</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($articles as $article)
                <tr>
                    <td class="fw-semibold">{{ $article->title }}</td>
                    <td>{{ $article->category ?? '-' }}</td>
                    <td class="text-muted small">{{ $article->slug }}</td>
                    <td>
                        <span class="badge bg-{{ $article->is_published ? 'success' : 'secondary' }}">
                            {{ $article->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.support.help.edit', $article) }}">
                                <i class="fas fa-pen me-1"></i>Edit
                            </a>
                            <form action="{{ route('admin.support.help.toggle', $article) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-{{ $article->is_published ? 'warning' : 'success' }}" type="submit">
                                    <i class="fas fa-bullhorn me-1"></i>{{ $article->is_published ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.support.help.destroy', $article) }}" method="POST"
                                  onsubmit="return confirm('Delete this help article?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-book-open fa-3x mb-3"></i>
                        <div>No help articles yet.</div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $articles->links() }}
</div>
@endsection

