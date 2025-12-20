<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TARUMT Car Park Management System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        html, body { height: 100%; }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1 0 auto; }
        footer { flex-shrink: 0; }
        .navbar-brand { font-weight: 600; }
        .credit-badge { font-size: 0.85rem; }
        .sidebar { min-height: calc(100vh - 56px); }
        .card { border-radius: 0.5rem; }
        .btn { border-radius: 0.375rem; }
    </style>
    @stack('styles')
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fas fa-parking me-2"></i>TARUMT Car Park
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            @auth
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                        <i class="fas fa-calendar-check me-1"></i>My Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bookings.create') ? 'active' : '' }}" href="{{ route('bookings.create') }}">
                        <i class="fas fa-plus-circle me-1"></i>Book Slot
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                        <i class="fas fa-car me-1"></i>My Vehicles
                    </a>
                </li>
                @if(!auth()->user()->isAdmin() && !auth()->user()->isTarumtStaff())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('support.*') ? 'active' : '' }}" href="{{ route('support.tickets.index') }}">
                        <i class="fas fa-headset me-1"></i>Support
                    </a>
                </li>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->isTarumtStaff())
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('support.*') ? 'active' : '' }}"
                               href="{{ route('support.tickets.index') }}">
                                <i class="fas fa-user me-2"></i>User Support
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('admin.support.tickets.*') ? 'active' : '' }}"
                               href="{{ route('admin.support.tickets.index') }}">
                                <i class="fas fa-inbox me-2"></i>Support Inbox
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('admin.support.help.*') ? 'active' : '' }}"
                               href="{{ route('admin.support.help.index') }}">
                                <i class="fas fa-book-open me-2"></i>Help Docs
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('admin.support.templates.*') ? 'active' : '' }}"
                               href="{{ route('admin.support.templates.index') }}">
                                <i class="fas fa-comment-dots me-2"></i>Canned Replies
                            </a>
                        </li>
                        @if(auth()->user()->isAdmin())
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.zones.index') }}"><i class="fas fa-map-marker-alt me-2"></i>Zones</a></li>
                        @endif
                    </ul>
                </li>
                @endif
            </ul>
            <ul class="navbar-nav">
                @if(auth()->user()->isAdmin() || auth()->user()->isTarumtStaff())
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       id="supportNotifBell"
                       data-notif-url="{{ route('admin.support.notifications.index') }}"
                       data-notif-read-url-template="{{ route('admin.support.notifications.read', ['id' => '___ID___']) }}"
                       data-notif-delete-url-template="{{ route('admin.support.notifications.delete', ['id' => '___ID___']) }}">
                        <i class="fas fa-bell"></i>
                        <span id="supportNotifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                            0
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px;">
                        <li class="dropdown-header">Notifications</li>
                        <li><hr class="dropdown-divider"></li>
                        <div id="supportNotifList" style="max-height: 360px; overflow-y: auto;"></div>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3 pb-2 text-muted small">
                            Emergency reports will appear here.
                        </li>
                    </ul>
                </li>
                @endif
                <li class="nav-item me-3">
                    <a class="nav-link" href="{{ route('credits.index') }}">
                        <span class="badge bg-success credit-badge">
                            <i class="fas fa-wallet me-1"></i>RM {{ number_format(auth()->user()->credit_balance, 2) }}
                        </span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('payments.history') }}"><i class="fas fa-history me-2"></i>Payment History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
            @else
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i>Register</a>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>
</main>

<footer class="bg-dark text-light py-3 mt-auto">
    <div class="container text-center">
        <small>&copy; {{ date('Y') }} TARUMT Car Park Management System. All rights reserved.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@auth
@if(auth()->user()->isAdmin() || auth()->user()->isTarumtStaff())
<script>
    (function () {
        const bell = document.getElementById('supportNotifBell');
        const badge = document.getElementById('supportNotifBadge');
        const list = document.getElementById('supportNotifList');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!bell || !badge || !list) return;

        const notifUrl = bell.dataset.notifUrl;
        const readTemplate = bell.dataset.notifReadUrlTemplate;
        const deleteTemplate = bell.dataset.notifDeleteUrlTemplate;
        let lastUnread = null;

        function renderEmpty() {
            list.innerHTML = `
                <li class="px-3 py-2 text-muted small">No notifications.</li>
            `;
        }

        function esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }

        function render(notifications) {
            if (!Array.isArray(notifications) || notifications.length === 0) {
                renderEmpty();
                return;
            }

            list.innerHTML = notifications.map(n => {
                const data = n.data || {};
                const isUnread = !n.read_at;
                const isEmergency = (data.type === 'emergency_reported') || (data.priority === 'emergency');

                let title = 'Notification';
                let href = '#';
                let meta = n.created_at_human || '';

                if (data.type === 'emergency_reported') {
                    title = `Emergency: ${data.ticket_number || ''}`;
                    href = `/admin/support/tickets/${data.ticket_id}`;
                }

                return `
                    <div class="dropdown-item d-flex align-items-start gap-2 ${isUnread ? 'fw-semibold' : ''}">
                        <div class="pt-1">
                            <i class="fas ${isEmergency ? 'fa-triangle-exclamation text-danger' : 'fa-info-circle text-muted'}"></i>
                        </div>
                        <a href="${href}"
                           class="text-decoration-none text-reset flex-grow-1"
                           data-notif-id="${esc(n.id)}">
                            <div>${esc(title)}</div>
                            ${data.subject ? `<div class="text-muted small">${esc(data.subject)}</div>` : ''}
                            <div class="text-muted small">${esc(meta)}</div>
                        </a>
                        ${isUnread ? '<span class="badge bg-danger align-self-center">New</span>' : ''}
                        <button type="button"
                                class="btn btn-link btn-sm text-danger p-0 ms-2 js-delete-notif"
                                data-notif-id="${esc(n.id)}"
                                title="Delete notification">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }).join('');

            list.querySelectorAll('a[data-notif-id]').forEach(a => {
                a.addEventListener('click', async () => {
                    const id = a.getAttribute('data-notif-id');
                    if (!id || !csrf || !readTemplate) return;
                    try {
                        await fetch(readTemplate.replace('___ID___', id), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            }
                        });
                    } catch (e) {
                        // ignore
                    }
                });
            });

            list.querySelectorAll('.js-delete-notif').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = btn.getAttribute('data-notif-id');
                    if (!id || !csrf || !deleteTemplate) return;
                    try {
                        const res = await fetch(deleteTemplate.replace('___ID___', id), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            }
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        const unread = parseInt(String(data.unread_count ?? 0), 10);
                        if (unread > 0) {
                            badge.classList.remove('d-none');
                            badge.textContent = unread > 99 ? '99+' : String(unread);
                        } else {
                            badge.classList.add('d-none');
                        }
                        refresh();
                    } catch (err) {
                        // ignore
                    }
                });
            });
        }

        async function refresh() {
            if (!notifUrl) return;
            try {
                const res = await fetch(notifUrl + '?limit=5', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                const unread = parseInt(String(data.unread_count ?? 0), 10);

                if (unread > 0) {
                    badge.classList.remove('d-none');
                    badge.textContent = unread > 99 ? '99+' : String(unread);
                } else {
                    badge.classList.add('d-none');
                }

                render(data.notifications);

                // lightweight "new notification" hint by briefly animating the bell
                if (lastUnread !== null && unread > lastUnread) {
                    bell.classList.add('text-warning');
                    setTimeout(() => bell.classList.remove('text-warning'), 800);
                }
                lastUnread = unread;
            } catch (e) {
                // ignore
            }
        }

        renderEmpty();
        refresh();
        setInterval(() => {
            if (document.hidden) return;
            refresh();
        }, 3000);
    })();
</script>
@endif
@endauth
@stack('scripts')
</body>
</html>
