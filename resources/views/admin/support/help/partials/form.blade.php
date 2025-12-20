<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control"
               value="{{ old('title', $article?->title) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug (optional)</label>
        <input type="text" name="slug" class="form-control"
               value="{{ old('slug', $article?->slug) }}"
               placeholder="auto-generated from title">
        <div class="form-text">Used as section id on the help page.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Category (optional)</label>
        <input type="text" name="category" class="form-control"
               value="{{ old('category', $article?->category) }}"
               placeholder="e.g. Booking, Payments, Parking Rules">
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="isPublished" name="is_published"
                   {{ old('is_published', $article?->is_published) ? 'checked' : '' }}>
            <label class="form-check-label" for="isPublished">Published</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Content</label>
        <textarea name="content" rows="10" class="form-control" required
                  placeholder="Write your help content here...">{{ old('content', $article?->content) }}</textarea>
        <div class="form-text">Plain text (line breaks preserved). Avoid pasting unsafe HTML.</div>
    </div>
</div>
