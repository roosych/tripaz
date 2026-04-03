<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Кабинет владельца') — TripAz</title>

    {{-- Bootstrap 5.3.3 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Bootstrap Icons 1.11.3 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --tripaz-primary: #0d6efd;
            --tripaz-dark: #1a1a2e;
            --sidebar-width: 250px;
            --navbar-height: 56px;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f0f2f5;
        }

        /* ── Sidebar ── */
        #owner-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--tripaz-dark);
            color: #fff;
            overflow-y: auto;
            z-index: 1040;
            display: flex;
            flex-direction: column;
        }

        #owner-sidebar::-webkit-scrollbar { width: 4px; }
        #owner-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        .sidebar-brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-decoration: none;
        }

        .sidebar-brand .brand-name {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .sidebar-brand .brand-name span {
            color: var(--tripaz-primary);
        }

        .sidebar-brand .brand-sub {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.45);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1px;
        }

        .sidebar-nav {
            padding: 0.75rem 0;
            flex: 1;
        }

        .sidebar-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.35);
            padding: 0.75rem 1.25rem 0.25rem;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.6rem 1.25rem;
            color: rgba(255,255,255,0.72);
            border-radius: 0;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.15s, color 0.15s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            background-color: rgba(255,255,255,0.06);
            color: #fff;
        }

        .sidebar-nav .nav-link.active {
            background-color: rgba(13,110,253,0.18);
            color: #fff;
            border-left-color: var(--tripaz-primary);
        }

        .sidebar-nav .nav-link i {
            font-size: 1rem;
            width: 1.25rem;
            text-align: center;
        }

        .sidebar-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 0.78rem;
            color: rgba(255,255,255,0.3);
        }

        /* ── Top Navbar ── */
        #owner-topnav {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            z-index: 1030;
            gap: 1rem;
        }

        /* ── Main Content ── */
        #owner-main {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 1.75rem;
            min-height: calc(100vh - var(--navbar-height));
        }

        /* ── Mobile Sidebar Toggle ── */
        @media (max-width: 991.98px) {
            #owner-sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }
            #owner-sidebar.show {
                transform: translateX(0);
            }
            #owner-topnav {
                left: 0;
            }
            #owner-main {
                margin-left: 0;
            }
            .sidebar-overlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.4);
                z-index: 1035;
            }
        }

        .sidebar-overlay { display: none; }

        /* ── Page header style ── */
        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        /* ── Table tweaks ── */
        .table > :not(caption) > * > * {
            vertical-align: middle;
        }
    </style>

    @yield('head')
</head>
<body>

{{-- ================================================================ --}}
{{-- SIDEBAR                                                          --}}
{{-- ================================================================ --}}
<aside id="owner-sidebar">

    {{-- Brand --}}
    <a class="sidebar-brand d-flex flex-column" href="{{ route('owner.dashboard') }}">
        <span class="brand-name">Trip<span>Az</span></span>
        <span class="brand-sub">Кабинет владельца</span>
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <span class="sidebar-section-label">Обзор</span>

        <a href="{{ route('owner.dashboard') }}"
           class="nav-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
            <i class="ph ph-gauge"></i> Дашборд
        </a>

        <span class="sidebar-section-label">Управление</span>

        <a href="{{ route('owner.listings.index') }}"
           class="nav-link {{ request()->routeIs('owner.listings.index') || request()->routeIs('owner.listings.show') || request()->routeIs('owner.listings.edit') ? 'active' : '' }}">
            <i class="ph ph-list-checks"></i> Мои объявления
        </a>

        <a href="{{ route('owner.listings.create') }}"
           class="nav-link {{ request()->routeIs('owner.listings.create') ? 'active' : '' }}">
            <i class="ph ph-plus-circle"></i> Добавить объявление
        </a>

        <span class="sidebar-section-label">Активность</span>

        <a href="{{ route('owner.reviews.index') }}"
           class="nav-link {{ request()->routeIs('owner.reviews.*') ? 'active' : '' }}">
            <i class="ph ph-chat-circle"></i> Отзывы
        </a>

        <a href="{{ route('owner.bookings.index') }}"
           class="nav-link {{ request()->routeIs('owner.bookings.*') ? 'active' : '' }}">
            <i class="ph ph-calendar-check"></i> Бронирования
        </a>
    </nav>

    {{-- Sidebar footer --}}
    <div class="sidebar-footer">
        &copy; {{ date('Y') }} TripAz
    </div>
</aside>

{{-- Sidebar overlay (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- ================================================================ --}}
{{-- TOP NAVBAR                                                        --}}
{{-- ================================================================ --}}
<header id="owner-topnav">

    {{-- Mobile toggle --}}
    <button class="btn btn-sm btn-outline-secondary d-lg-none me-1"
            id="sidebarToggle" type="button" aria-label="Toggle sidebar">
        <i class="ph ph-list fs-5"></i>
    </button>

    {{-- Page title breadcrumb placeholder --}}
    <div class="me-auto">
        <span class="fw-semibold text-secondary small">
            <i class="ph ph-buildings me-1"></i>Кабинет владельца
        </span>
    </div>

    {{-- Quick link to public site --}}
    <a href="{{ route('listings.index') }}" target="_blank"
       class="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex align-items-center gap-1">
        <i class="ph ph-link"></i>
        <span>Перейти на сайт</span>
    </a>

    {{-- User dropdown --}}
    @auth
    <div class="dropdown">
        <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ph ph-user-circle text-secondary"></i>
            <span class="d-none d-sm-inline text-truncate" style="max-width:120px">{{ Auth::user()->name }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <span class="dropdown-item-text small text-muted">{{ Auth::user()->email }}</span>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
                <a class="dropdown-item" href="{{ route('dashboard.profile.show') }}">
                    <i class="ph ph-user me-2"></i>Мой профиль
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('listings.index') }}">
                    <i class="ph ph-globe me-2"></i>Публичный сайт
                </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item text-danger" type="submit">
                        <i class="ph ph-sign-out me-2"></i>Выйти
                    </button>
                </form>
            </li>
        </ul>
    </div>
    @endauth
</header>

{{-- ================================================================ --}}
{{-- MAIN CONTENT                                                      --}}
{{-- ================================================================ --}}
<main id="owner-main">

    {{-- Flash messages --}}
    <x-flash-message />

    @yield('content')
</main>

{{-- Bootstrap 5 JS Bundle --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- jQuery 3.7.1 --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    // Mobile sidebar toggle
    $('#sidebarToggle').on('click', function () {
        $('#owner-sidebar').toggleClass('show');
        $('#sidebarOverlay').toggle();
    });
    $('#sidebarOverlay').on('click', function () {
        $('#owner-sidebar').removeClass('show');
        $(this).hide();
    });
</script>

@yield('scripts')

</body>
</html>
