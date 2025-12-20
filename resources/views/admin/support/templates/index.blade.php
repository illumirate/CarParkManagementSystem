@extends('layout')

@section('title', 'Canned Replies - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-comment-dots me-2"></i>Canned Replies</h2>
        <div class="text-muted">Reusable reply templates for support staff/admin.</div>
    </div>
    <a href="{{ route('admin.support.templates.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>New Template
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($templates as $template)
                <tr>
                    <td class="fw-semibold">{{ $template->title }}</td>
                    <td>{{ $template->category ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.support.templates.edit', $template) }}">
                                <i class="fas fa-pen me-1"></i>Edit
                            </a>
                            <form action="{{ route('admin.support.templates.toggle', $template) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-{{ $template->is_active ? 'warning' : 'success' }}" type="submit">
                                    <i class="fas fa-bolt me-1"></i>{{ $template->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.support.templates.destroy', $template) }}" method="POST"
                                  onsubmit="return confirm('Delete this template?');">
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
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="fas fa-comment-slash fa-3x mb-3"></i>
                        <div>No templates yet.</div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $templates->links() }}
</div>
@endsection

