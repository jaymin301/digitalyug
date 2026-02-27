<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Digital Yug Panel') – Digital Yug</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>

{{-- Mobile Overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- ═══ SIDEBAR ═════════════════════════════════════════════ --}}
<nav class="sidebar" id="mainSidebar">
    {{-- Logo --}}
    <a href="{{ route('dashboard') }}" class="sidebar-logo">
        <div class="logo-icon"><i class="fa-solid fa-bolt"></i></div>
        <div class="logo-text">
            <div class="logo-name">Digital Yug</div>
            <div class="logo-tagline">Marketing Agency Panel</div>
        </div>
    </a>

    {{-- Nav --}}
    <div class="sidebar-nav">

        {{-- MAIN --}}
        <div class="sidebar-section-title">Main</div>
        <ul class="list-unstyled mb-0">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>

            @role('Admin')
            <li class="nav-item">
                <a href="{{ route('admin.employees.index') }}" class="nav-link {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span class="nav-link-text">Employees</span>
                </a>
            </li>
            @endrole
        </ul>

        {{-- WORKFLOW --}}
        <div class="sidebar-section-title">Workflow</div>
        <ul class="list-unstyled mb-0">

            @role('Admin|Manager|Sales Executive')
            <li class="nav-item">
                <a href="{{ route('leads.index') }}" class="nav-link {{ request()->routeIs('leads*') ? 'active' : '' }}">
                    <i class="fa-solid fa-handshake"></i>
                    <span class="nav-link-text">Leads</span>
                </a>
            </li>
            @endrole

            @role('Admin|Manager')
            <li class="nav-item">
                <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects*') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-open"></i>
                    <span class="nav-link-text">Projects</span>
                </a>
            </li>
            @endrole

            @role('Admin|Manager|Concept Writer')
            <li class="nav-item">
                <a href="{{ route('concepts.index') }}" class="nav-link {{ request()->routeIs('concepts*') ? 'active' : '' }}">
                    <i class="fa-solid fa-lightbulb"></i>
                    <span class="nav-link-text">Concepts</span>
                </a>
            </li>
            @endrole

            @role('Admin|Manager|Shooting Person')
            <li class="nav-item">
                <a href="{{ route('shoots.index') }}" class="nav-link {{ request()->routeIs('shoots*') ? 'active' : '' }}">
                    <i class="fa-solid fa-camera"></i>
                    <span class="nav-link-text">Shoot Schedules</span>
                </a>
            </li>
            @endrole

            @role('Admin|Manager|Video Editor')
            <li class="nav-item">
                <a href="{{ route('editing.index') }}" class="nav-link {{ request()->routeIs('editing*') ? 'active' : '' }}">
                    <i class="fa-solid fa-film"></i>
                    <span class="nav-link-text">Editing</span>
                </a>
            </li>
            @endrole
        </ul>

        {{-- ANALYTICS --}}
        @role('Admin|Manager')
        <div class="sidebar-section-title">Analytics</div>
        <ul class="list-unstyled mb-0">
            <li class="nav-item">
                <a href="{{ route('reports.monthly') }}" class="nav-link {{ request()->routeIs('reports*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span class="nav-link-text">Monthly Reports</span>
                </a>
            </li>
        </ul>
        @endrole

        {{-- NOTIFICATIONS --}}
        <div class="sidebar-section-title">Account</div>
        <ul class="list-unstyled mb-0">
            <li class="nav-item">
                <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bell"></i>
                    <span class="nav-link-text">Notifications</span>
                    @php $unreadCount = auth()->user()->unreadNotificationsCount(); @endphp
                    @if($unreadCount > 0)
                        <span class="nav-badge">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    {{-- Sidebar Footer: Quick User --}}
    <div class="sidebar-footer">
        <a href="{{ route('profile.edit') }}" class="user-quick">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->role_name }}</div>
            </div>
        </a>
    </div>
</nav>

{{-- ═══ TOPBAR ════════════════════════════════════════════ --}}
<header class="topbar" id="mainTopbar">
    <div class="topbar-left">
        {{-- Desktop toggle --}}
        <button class="sidebar-toggle-btn d-none d-lg-flex" id="sidebarToggle" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        {{-- Mobile toggle --}}
        <button class="sidebar-toggle-btn d-flex d-lg-none" id="mobileSidebarToggle" title="Open Menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav aria-label="breadcrumb" class="topbar-breadcrumb d-none d-sm-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Home</a></li>
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>

    <div class="topbar-right">
        {{-- Notification Bell --}}
        <div class="dropdown">
            <button class="topbar-action" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-count" id="notifBadge" style="display:none">0</span>
            </button>
            <div class="dropdown-menu notif-dropdown dropdown-menu-end" aria-labelledby="notifDropdown">
                <div class="notif-header">
                    <strong>Notifications</strong>
                    <button class="btn btn-sm btn-link text-muted p-0" id="markAllRead">Mark all read</button>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="text-center text-muted py-4" id="notifEmpty">
                        <i class="fa-solid fa-bell-slash fa-2x mb-2 d-block opacity-30"></i>
                        No new notifications
                    </div>
                </div>
                <div class="notif-footer">
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-link text-decoration-none">View all notifications</a>
                </div>
            </div>
        </div>

        {{-- User Menu --}}
        <div class="dropdown">
            <button class="user-dropdown-btn" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <span class="user-name">{{ auth()->user()->name }}</span>
                <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#718096;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;border-radius:12px;border-color:#e2e8f0;box-shadow:0 10px 30px rgba(0,0,0,0.12);">
                <li>
                    <div class="px-4 py-3 border-bottom">
                        <div class="fw-bold" style="font-size:14px;">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:12px;">{{ auth()->user()->email }}</div>
                        <div class="mt-1">
                            <span class="badge" style="background:rgba(108,63,197,0.1);color:#6c3fc5;font-size:11px;">{{ auth()->user()->role_name }}</span>
                        </div>
                    </div>
                </li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-user me-2 text-muted"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('notifications.index') }}"><i class="fa-solid fa-bell me-2 text-muted"></i>Notifications</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- ═══ CONTENT ════════════════════════════════════════════ --}}
<div class="content-area" id="contentArea">
    <div class="page-content">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible alert-auto-dismiss fade show" style="border-radius:10px;border:none;background:rgba(38,222,129,0.12);color:#1a8754;" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible alert-auto-dismiss fade show" style="border-radius:10px;" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

{{-- Global Loader --}}
<div id="global-loader">
    <div class="loader-inner">
        <div class="spinner"></div>
        <p>Please wait...</p>
    </div>
</div>

{{-- Scroll-to-top --}}
<div class="scroll-top" id="scrollTop" title="Scroll to top">
    <i class="fa-solid fa-chevron-up"></i>
</div>

{{-- Per-page scripts --}}
@stack('scripts')

<script>
// Notification polling
function loadNotifications() {
    $.getJSON('{{ route('notifications.recent') }}', function(res) {
        const badge = $('#notifBadge');
        const list = $('#notifList');
        const empty = $('#notifEmpty');

        if (res.unread > 0) {
            badge.text(res.unread > 99 ? '99+' : res.unread).show();
        } else {
            badge.hide();
        }

        if (res.notifications.length === 0) {
            empty.show();
            return;
        }
        empty.hide();

        const iconMap = {
            'concept_assigned': 'fa-lightbulb', 'concept_approved': 'fa-check-circle',
            'shoot_scheduled': 'fa-camera', 'edit_assigned': 'fa-film',
            'edit_approved': 'fa-star', 'lead_updated': 'fa-handshake',
            'project_created': 'fa-folder-plus', 'concepts_submitted': 'fa-paper-plane',
        };

        let html = '';
        res.notifications.forEach(n => {
            const icon = iconMap[n.type] || 'fa-bell';
            html += `
                <div class="notif-item${n.is_read ? '' : ' unread'}" onclick="markRead(${n.id}, '${n.link}')">
                    <div class="notif-avatar"><i class="fa-solid ${icon}"></i></div>
                    <div class="notif-body">
                        <div class="notif-title">${n.title}</div>
                        <div class="notif-text">${n.message}</div>
                        <div class="notif-time">${n.time} · by ${n.triggered_by}</div>
                    </div>
                    ${!n.is_read ? '<div class="notif-dot"></div>' : ''}
                </div>`;
        });
        list.html(html);
    });
}

function markRead(id, link) {
    $.post('{{ url('/notifications') }}/' + id + '/read'}}', function() {
        loadNotifications();
        if (link) window.location.href = link;
    });
};

$('#markAllRead').on('click', function() {
    $.post('{{ route('notifications.read-all') }}', function() {
        loadNotifications();
        showSuccess('All notifications marked as read.');
    });
});

$(document).ready(function() {
    loadNotifications();
    setInterval(loadNotifications, 30000);

    // Mobile sidebar
    $('#mobileSidebarToggle').on('click', function() {
        $('#mainSidebar').toggleClass('mobile-open');
        $('#sidebarOverlay').toggleClass('active');
    });

    $('#sidebarOverlay').on('click', function() {
        $('#mainSidebar').removeClass('mobile-open');
        $('#sidebarOverlay').removeClass('active');
    });

    // Sidebar submenu
    $('.nav-item.has-submenu > .nav-link').on('click', function(e) {
        e.preventDefault();
        const $parent = $(this).closest('.nav-item');
        $parent.toggleClass('open');
        $parent.find('.submenu').toggleClass('show');
    });

    // Scroll-to-top
    $(window).on('scroll', function() {
        $('#scrollTop').toggleClass('visible', $(this).scrollTop() > 300);
    });
    $('#scrollTop').on('click', function() {
        $('html,body').animate({ scrollTop: 0 }, 300);
    });
});
</script>
</body>
</html>
