@extends('layout')

@section('title', 'Support Ticket - Inbox')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-ticket-alt me-2"></i>{{ $ticket->ticket_number }}</h2>
        <div class="text-muted">{{ $ticket->subject }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
        @if(!$ticket->assigned_to_user_id || $ticket->assigned_to_user_id === auth()->id())
        <form action="{{ route('admin.support.tickets.assign', $ticket) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-user-check me-1"></i>Assign to me
            </button>
        </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted small">User</span><br>
                    <span class="fw-semibold">{{ $ticket->user?->name ?? 'Unknown' }}</span>
                    <div class="text-muted small">{{ $ticket->user?->email }}</div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small">Assigned To</span><br>
                    <span>{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</span>
                </div>

                <div class="mb-2">
                    <span class="text-muted small">Status</span><br>
                    <span class="badge bg-{{ in_array($ticket->status, ['open','in_progress']) ? 'warning' : ($ticket->status === 'resolved' ? 'success' : 'secondary') }}">
                        {{ str_replace('_',' ', ucfirst($ticket->status)) }}
                    </span>
                    @if(($ticket->priority ?? 'normal') === 'emergency')
                        <span class="badge bg-danger ms-1">Emergency</span>
                    @elseif(($ticket->priority ?? 'normal') === 'urgent')
                        <span class="badge bg-warning text-dark ms-1">Urgent</span>
                    @endif
                </div>

                <hr>

                <form action="{{ route('admin.support.tickets.status', $ticket) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small text-muted">Update Status</label>
                        <select class="form-select" name="status" required>
                            @foreach(['open','in_progress','resolved','closed'] as $opt)
                                <option value="{{ $opt }}" {{ $ticket->status === $opt ? 'selected' : '' }}>
                                    {{ str_replace('_',' ', ucfirst($opt)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fas fa-save me-1"></i>Save
                    </button>
                </form>

                @if($ticket->description)
                <hr>
                <div class="text-muted small mb-1">Description</div>
                <div class="small">{!! nl2br(e($ticket->description)) !!}</div>
                @endif

                <hr>
                <div class="text-muted small mb-1">Registered Vehicles (API)</div>
                <div id="supportVehicles" class="small text-muted">Loading vehicles...</div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fas fa-comments me-2"></i>Conversation</strong>
                <span class="text-muted small">{{ $ticket->messages->count() }} message(s)</span>
            </div>
            <div id="chatBox"
                 class="card-body bg-light"
                 style="height: 420px; overflow-y: auto;"
                 data-last-id="{{ $ticket->messages->last()?->id ?? 0 }}"
                 data-server-time="{{ now()->toDateTimeString() }}"
                 data-poll-url="{{ route('admin.support.tickets.messages.index', $ticket) }}">
                @forelse($ticket->messages as $message)
                    @php
                        $isMe = $message->sender_user_id === auth()->id();
                        $canDelete = !$message->trashed()
                            && $isMe
                            && $message->created_at
                            && $message->created_at->diffInSeconds(now()) <= 60;
                    @endphp
                    <div class="d-flex mb-2 {{ $isMe ? 'justify-content-end' : 'justify-content-start' }}" data-message-id="{{ $message->id }}">
                        <div class="p-2 rounded {{ $isMe ? 'bg-primary text-white' : 'bg-white border' }}" style="max-width: 80%;">
                            <div class="small fw-semibold mb-1 d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    {{ $message->sender?->name ?? 'Unknown' }}
                                    @if($message->is_internal)
                                        <span class="badge bg-dark ms-2">Internal</span>
                                    @endif
                                    <span class="{{ $isMe ? 'text-white-50' : 'text-muted' }} ms-2">
                                        {{ $message->created_at->format('d M Y, h:i A') }}
                                    </span>
                                    @if($isMe && !$message->trashed() && $message->read_at)
                                        <span class="ms-2 small text-white-50 js-read-indicator">Seen</span>
                                    @endif
                                </div>
                                @if($canDelete)
                                    <button type="button"
                                            class="btn btn-link btn-sm p-0 text-danger js-delete-message"
                                            data-delete-url="{{ route('admin.support.tickets.messages.delete', [$ticket, $message]) }}">
                                        Delete
                                    </button>
                                @endif
                            </div>
                            <div class="js-message-body">
                                @if($message->trashed())
                                    <div class="text-muted fst-italic small">Message deleted</div>
                                @else
                                    @if($message->message !== '')
                                        <div>{!! nl2br(e($message->message)) !!}</div>
                                    @endif
                                    @if($message->attachments->count() > 0)
                                        <div class="mt-2 d-flex flex-wrap gap-2">
                                            @foreach($message->attachments as $attachment)
                                                <a href="{{ route('admin.support.tickets.attachments.show', [$ticket, $attachment]) }}" target="_blank">
                                                    <img src="{{ route('admin.support.tickets.attachments.show', [$ticket, $attachment]) }}"
                                                         alt="{{ $attachment->original_name }}"
                                                         class="rounded border"
                                                         style="max-width: 140px; max-height: 140px;">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-comment-dots fa-3x mb-3"></i>
                        <div>No messages yet.</div>
                    </div>
                @endforelse
            </div>

            <div class="card-footer bg-white">
                @if($ticket->status === 'closed')
                    <div class="alert alert-secondary mb-0">
                        <i class="fas fa-lock me-1"></i>This ticket is closed.
                    </div>
                @else
                    <form id="chatForm" action="{{ route('admin.support.tickets.messages.store', $ticket) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2 d-flex align-items-center justify-content-between gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="internalNote" name="is_internal">
                                <label class="form-check-label" for="internalNote">Internal note (not visible to user)</label>
                            </div>
                            <div class="d-flex gap-2">
                                <select id="templateSelect" class="form-select form-select-sm" style="min-width: 220px;">
                                    <option value="">Canned reply...</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" data-content="{{ e($template->content) }}">
                                            {{ $template->category ? $template->category . ' - ' : '' }}{{ $template->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="insertTemplate">
                                    Insert
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <textarea name="message" class="form-control" rows="2" placeholder="Type your reply..."></textarea>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane me-1"></i>Send
                            </button>
                        </div>
                        <div class="mt-2">
                            <input type="file" name="attachment" class="form-control" accept="image/*">
                            <div class="form-text">Optional image (jpg/png/webp, max 5MB). Attachments are visible to users.</div>
                            <div id="attachmentPreview" class="mt-2 d-none">
                                <img src="" alt="Preview" class="rounded border" style="max-width: 160px; max-height: 160px;">
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                This will remove the message content and show “Message deleted” to both sides.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteMessage">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const box = document.getElementById('chatBox');
        const form = document.getElementById('chatForm');

        if (!box) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const pollUrl = box.dataset.pollUrl;
        let lastId = parseInt(box.dataset.lastId || '0', 10);
        let lastPollAt = box.dataset.serverTime || '';
        let pendingDeleteUrl = null;
        const ticketUserId = {{ $ticket->user_id }};

        const deleteModalEl = document.getElementById('deleteMessageModal');
        const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
        const confirmDeleteBtn = document.getElementById('confirmDeleteMessage');

        function isNearBottom() {
            return (box.scrollHeight - box.scrollTop - box.clientHeight) < 60;
        }

        function appendMessage(m) {
            const isMe = m.sender_user_id === {{ auth()->id() }};
            const isDeleted = !!m.is_deleted;

            const row = document.createElement('div');
            row.className = `d-flex mb-2 ${isMe ? 'justify-content-end' : 'justify-content-start'}`;
            row.dataset.messageId = String(m.id ?? '');

            const bubble = document.createElement('div');
            bubble.className = `p-2 rounded ${isMe ? 'bg-primary text-white' : 'bg-white border'}`;
            bubble.style.maxWidth = '80%';

            const header = document.createElement('div');
            header.className = 'small fw-semibold mb-1 d-flex justify-content-between align-items-center gap-2';

            const left = document.createElement('div');
            const name = document.createElement('span');
            name.textContent = m.sender_name ?? 'Unknown';
            left.appendChild(name);

            if (m.is_internal) {
                const internal = document.createElement('span');
                internal.className = 'badge bg-dark ms-2';
                internal.textContent = 'Internal';
                left.appendChild(internal);
            }

            const time = document.createElement('span');
            time.className = `${isMe ? 'text-white-50' : 'text-muted'} ms-2`;
            time.textContent = m.created_at_human ?? '';
            left.appendChild(time);

            header.appendChild(left);

            if (m.can_delete && m.delete_url && !isDeleted) {
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'btn btn-link btn-sm p-0 text-danger js-delete-message';
                delBtn.dataset.deleteUrl = m.delete_url;
                delBtn.textContent = 'Delete';
                header.appendChild(delBtn);
            }

            const bodyWrap = document.createElement('div');
            bodyWrap.className = 'js-message-body';
            if (isDeleted) {
                const deletedText = document.createElement('div');
                deletedText.className = 'text-muted fst-italic small';
                deletedText.textContent = 'Message deleted';
                bodyWrap.appendChild(deletedText);
            } else {
                const msgText = String(m.message ?? '');
                if (msgText !== '') {
                    const body = document.createElement('div');
                    msgText.split('\n').forEach((line, idx) => {
                        if (idx > 0) body.appendChild(document.createElement('br'));
                        body.appendChild(document.createTextNode(line));
                    });
                    bodyWrap.appendChild(body);
                }

                if (Array.isArray(m.attachments) && m.attachments.length > 0) {
                    const wrap = document.createElement('div');
                    wrap.className = 'mt-2 d-flex flex-wrap gap-2';
                    m.attachments.forEach(att => {
                        const link = document.createElement('a');
                        link.href = att.url;
                        link.target = '_blank';
                        const img = document.createElement('img');
                        img.src = att.url;
                        img.alt = att.name || 'attachment';
                        img.className = 'rounded border';
                        img.style.maxWidth = '140px';
                        img.style.maxHeight = '140px';
                        link.appendChild(img);
                        wrap.appendChild(link);
                    });
                    bodyWrap.appendChild(wrap);
                }
            }

            bubble.appendChild(header);
            bubble.appendChild(bodyWrap);
            row.appendChild(bubble);
            box.appendChild(row);

            if (isMe && m.read_at && !isDeleted) {
                const seen = document.createElement('span');
                seen.className = 'ms-2 small text-white-50 js-read-indicator';
                seen.textContent = 'Seen';
                left.appendChild(seen);
            }
        }

        function applyDeleted(id) {
            const row = box.querySelector(`[data-message-id="${id}"]`);
            if (!row) return;
            const body = row.querySelector('.js-message-body');
            if (body) {
                body.innerHTML = '<div class="text-muted fst-italic small">Message deleted</div>';
            }
            const delBtn = row.querySelector('.js-delete-message');
            if (delBtn) delBtn.remove();
            const seen = row.querySelector('.js-read-indicator');
            if (seen) seen.remove();
        }

        function applyRead(id) {
            const row = box.querySelector(`[data-message-id="${id}"]`);
            if (!row) return;
            const header = row.querySelector('.small.fw-semibold');
            if (!header) return;
            const existing = row.querySelector('.js-read-indicator');
            if (existing) return;
            const seen = document.createElement('span');
            seen.className = 'ms-2 small text-white-50 js-read-indicator';
            seen.textContent = 'Seen';
            header.querySelector('div')?.appendChild(seen);
        }

        async function poll() {
            if (!pollUrl) return;
            try {
                const url = `${pollUrl}?after_id=${lastId}` + (lastPollAt ? `&since=${encodeURIComponent(lastPollAt)}` : '');
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const messages = Array.isArray(data.messages) ? data.messages : [];
                const shouldScroll = isNearBottom();
                if (messages.length > 0) {
                    messages.forEach(appendMessage);
                    lastId = parseInt(String(data.last_id ?? lastId), 10);
                    box.dataset.lastId = String(lastId);
                }
                if (Array.isArray(data.deleted_ids)) {
                    data.deleted_ids.forEach(applyDeleted);
                }
                if (Array.isArray(data.read_ids)) {
                    data.read_ids.forEach(applyRead);
                }
                if (data.server_time) {
                    lastPollAt = data.server_time;
                }
                if (messages.length > 0 && shouldScroll) box.scrollTop = box.scrollHeight;
            } catch (e) {
                // ignore polling errors
            }
        }

        if (form && csrf) {
            const templateSelect = document.getElementById('templateSelect');
            const insertBtn = document.getElementById('insertTemplate');
            const preview = document.getElementById('attachmentPreview');
            const previewImg = preview ? preview.querySelector('img') : null;
            let fileInput = form.querySelector('input[type="file"][name="attachment"]');

            function bindFileInput() {
                if (!fileInput || !preview || !previewImg) return;
                fileInput.addEventListener('change', () => {
                    const file = fileInput.files && fileInput.files[0];
                    if (!file) {
                        preview.classList.add('d-none');
                        previewImg.src = '';
                        return;
                    }
                    const url = URL.createObjectURL(file);
                    previewImg.src = url;
                    preview.classList.remove('d-none');
                    previewImg.onload = () => URL.revokeObjectURL(url);
                });
            }

            function resetFileInput() {
                if (!fileInput) return;
                const clone = fileInput.cloneNode(true);
                fileInput.parentNode.replaceChild(clone, fileInput);
                fileInput = clone;
                if (preview && previewImg) {
                    preview.classList.add('d-none');
                    previewImg.src = '';
                }
                bindFileInput();
            }

            bindFileInput();

            if (insertBtn && templateSelect) {
                insertBtn.addEventListener('click', () => {
                    const option = templateSelect.selectedOptions[0];
                    const content = option ? option.getAttribute('data-content') : '';
                    if (!content) return;
                    const textarea = form.querySelector('textarea[name="message"]');
                    if (!textarea) return;
                    const prefix = textarea.value.trim() !== '' ? "\n" : "";
                    textarea.value = textarea.value + prefix + content;
                    textarea.focus();
                });
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const textarea = form.querySelector('textarea[name="message"]');
                const message = textarea?.value?.trim();
                const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                if (!message && !hasFile) return;

                const internal = form.querySelector('input[name="is_internal"]')?.checked ? 1 : 0;
                const formData = new FormData(form);
                formData.set('message', message);
                formData.set('is_internal', internal ? '1' : '0');

                try {
                    const shouldScroll = isNearBottom();
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: formData
                    });

                    if (!res.ok) return;
                    let data = null;
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = null;
                    }
                    if (data?.message) {
                        appendMessage(data.message);
                        lastId = Math.max(lastId, parseInt(String(data.message.id ?? lastId), 10));
                        box.dataset.lastId = String(lastId);
                    }
                    if (textarea) textarea.value = '';
                    const internalCheckbox = form.querySelector('input[name="is_internal"]');
                    if (internalCheckbox) internalCheckbox.checked = false;
                    resetFileInput();
                    if (shouldScroll) box.scrollTop = box.scrollHeight;
                } catch (err) {
                    // ignore
                }
            });
        }

        box.scrollTop = box.scrollHeight;
        poll();
        setInterval(() => {
            if (document.hidden) return;
            poll();
        }, 2000);

        box.addEventListener('click', async (e) => {
            const btn = e.target.closest('.js-delete-message');
            if (!btn) return;
            const deleteUrl = btn.dataset.deleteUrl;
            if (!deleteUrl || !csrf) return;
            pendingDeleteUrl = deleteUrl;
            if (deleteModal) {
                deleteModal.show();
            }
        });

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', async () => {
                const deleteUrl = pendingDeleteUrl;
                if (!deleteUrl || !csrf) return;
                try {
                    const res = await fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data?.deleted_id) {
                        applyDeleted(data.deleted_id);
                        if (data.server_time) {
                            lastPollAt = data.server_time;
                        }
                    }
                    if (deleteModal) {
                        deleteModal.hide();
                    }
                } catch (err) {
                    // ignore
                }
            });
        }

        function formatTimestamp(date) {
            const pad = (n) => String(n).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
        }

        function buildRequestParams() {
            const requestId = `req_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
            const timestamp = formatTimestamp(new Date());
            return new URLSearchParams({ requestId, timestamp });
        }

        async function loadVehicles() {
            const target = document.getElementById('supportVehicles');
            if (!target) return;

            try {
                const params = buildRequestParams();
                const res = await fetch(`/api/users/${ticketUserId}/vehicles?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) {
                    target.textContent = 'Unable to load vehicles.';
                    return;
                }
                const data = await res.json();
                if (data.status !== 'S') {
                    target.textContent = data.message || 'Unable to load vehicles.';
                    return;
                }
                const vehicles = Array.isArray(data.data) ? data.data : [];
                if (vehicles.length === 0) {
                    target.textContent = 'No active vehicles found.';
                    return;
                }
                const list = document.createElement('ul');
                list.className = 'list-unstyled mb-0';
                vehicles.forEach(v => {
                    const item = document.createElement('li');
                    const type = v.vehicle_type ? `(${v.vehicle_type})` : '';
                    const color = v.color ? ` - ${v.color}` : '';
                    item.textContent = `${v.plate_number} ${type} - ${v.brand ?? ''} ${v.model ?? ''}${color}`.replace(/\s+/g, ' ').trim();
                    list.appendChild(item);
                });
                target.innerHTML = '';
                target.appendChild(list);
            } catch (err) {
                target.textContent = 'Unable to load vehicles.';
            }
        }

        loadVehicles();
    })();
</script>
@endpush
