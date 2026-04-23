<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &mdash; HRM System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── Base ── */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #212529;
        }

        /* ── Sidebar ── */
        #sidebar {
            width: 260px;
            min-height: 100vh;
            background: #1e2a3b;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            transition: transform 0.25s ease;
        }

        #sidebar .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 22px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-decoration: none;
        }

        #sidebar .sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
        }

        #sidebar .brand-text h1 {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            margin: 0;
            line-height: 1.2;
        }

        #sidebar .brand-text small {
            font-size: 11px;
            color: rgba(255,255,255,.45);
        }

        /* Nav section label */
        #sidebar .nav-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
            padding: 18px 20px 6px;
        }

        /* Nav links */
        #sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255,255,255,.65);
            transition: background .2s, color .2s;
            text-decoration: none;
            text-transform: uppercase;
        }

        #sidebar .nav-link i {
            font-size: 17px;
            width: 22px;
            text-align: center;
        }

        #sidebar .nav-link:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }

        #sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(59,130,246,.25), rgba(99,102,241,.25));
            color: #60a5fa;
            border-left: 3px solid #3b82f6;
            margin-left: 10px;
            padding-left: 17px;
        }

        /* Sidebar footer */
        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        #sidebar .sidebar-footer .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #sidebar .sidebar-footer .avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        #sidebar .sidebar-footer .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #sidebar .sidebar-footer .user-role {
            font-size: 11px;
            color: rgba(255,255,255,.45);
            margin: 0;
            text-transform: capitalize;
        }

        /* ── Main wrapper ── */
        #main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.25s ease;
        }

        /* ── Topbar ── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }

        .topbar .page-title {
            font-size: 17px;
            font-weight: 700;
            margin: 0;
            color: #111827;
            text-transform: uppercase;
        }

        h1, h2, h3, h4, h5, h6, .modal-title {
            text-transform: uppercase;
        }

        .topbar .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Toggle button (mobile) */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: #374151;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background .2s;
        }

        .sidebar-toggle:hover {
            background: #f3f4f6;
        }

        /* Logout button */
        .btn-logout {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 7px 14px;
            cursor: pointer;
            transition: background .2s, color .2s, border-color .2s;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }

        /* ── Page content ── */
        .page-content {
            padding: 28px;
            flex: 1;
        }

        /* ── Card overrides ── */
        .card {
            border: none;
            box-shadow: 0 1px 4px rgba(0,0,0,.07);
            border-radius: 12px;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }

        /* ── Stat card (dashboard) ── */
        .stat-card {
            border-radius: 12px !important;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,.1) !important;
        }

        /* ── Overlay (mobile) ── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1039;
        }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.open {
                transform: translateX(0);
            }

            #sidebar-overlay.open {
                display: block;
            }

            #main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: flex;
                align-items: center;
            }
        }

        /* ── Alerts ── */
        .alert {
            border-radius: 10px;
        }

        /* ── Tables ── */
        .table > :not(caption) > * > * {
            padding: .75rem 1rem;
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Sidebar ── --}}
<aside id="sidebar">

    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-grid-1x2-fill"></i>
        </div>
        <div class="brand-text">
            <h1>HRM System</h1>
            <small>Enterprise Edition</small>
        </div>
    </a>

    {{-- Navigation --}}
    <div class="mt-2">
        <div class="nav-label">Main Menu</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

        <a href="{{ route('pegawai.index') }}"
           class="nav-link {{ request()->routeIs('pegawai.*') && !request()->routeIs('pegawai.arsip') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            PEGAWAI NON ASN
        </a>

        @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('pegawai.arsip') }}"
               class="nav-link {{ request()->routeIs('pegawai.arsip') ? 'active' : '' }}">
                <i class="bi bi-archive-fill"></i>
                ARSIP PEGAWAI
            </a>

            <a href="{{ route('satker.index') }}"
               class="nav-link {{ request()->routeIs('satker.*') ? 'active' : '' }}">
                <i class="bi bi-building-fill"></i>
                SATKER/SATWIL
            </a>

            <a href="{{ route('users.index') }}"
               class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                OPERATOR
            </a>

            <a href="{{ route('approval.index') }}"
               class="nav-link {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                <i class="bi bi-check2-circle"></i>
                <span class="flex-grow-1">Approval</span>
                @php
                    $pendingCount = \App\Models\PegawaiRequest::where('status','pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="badge bg-warning text-dark" style="font-size:10px;font-weight:600;padding:3px 7px;border-radius:20px;">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>


            <a href="{{ route('prodi.index') }}"
               class="nav-link {{ request()->routeIs('prodi.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark-fill"></i>
                Program Studi
            </a>
        @endif
    </div>

    {{-- Footer / User info --}}
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div style="min-width:0;">
                <p class="user-name">{{ auth()->user()->name }}</p>
                <p class="user-role">{{ auth()->user()->role_display }}</p>
            </div>
        </div>
    </div>

</aside>

{{-- Sidebar overlay (mobile) --}}
<div id="sidebar-overlay"></div>

{{-- ── Main Content ── --}}
<div id="main-content">

    {{-- Topbar --}}
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                <i class="bi bi-list"></i>
            </button>
            <h2 class="page-title">@yield('title', 'Dashboard')</h2>
        </div>

        <div class="topbar-right">
            {{-- Breadcrumb slot --}}
            @hasSection('breadcrumb')
                <nav aria-label="breadcrumb" class="d-none d-md-block me-3">
                    @yield('breadcrumb')
                </nav>
            @endif

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Page Content --}}
    <div class="page-content">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

</div>

{{-- ── Bootstrap 5 JS ── --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Sidebar toggle (mobile)
    const toggle   = document.getElementById('sidebarToggle');
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);
</script>

@stack('scripts')

</body>
</html>
