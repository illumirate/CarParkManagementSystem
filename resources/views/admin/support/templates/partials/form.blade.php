<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control"
               value="{{ old('title', $template?->title) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Category (optional)</label>
        <input type="text" name="category" class="form-control"
               value="{{ old('category', $template?->category) }}"
               placeholder="e.g. Booking, Payment, Policy">
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="isActive" name="is_active"
                   {{ old('is_active', $template?->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="isActive">Active</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Content</label>
        <textarea name="content" rows="6" class="form-control" required
                  placeholder="Type the reply content...">{{ old('content', $template?->content) }}</textarea>
    </div>
</div>
