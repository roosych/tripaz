<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Панель администратора') — TripAz</title>

    {{-- Bootstrap 5.3.3 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Bootstrap Icons 1.11.3 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Phosphor Icons 2.1.1 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css">

    <style>
        :root {
            --tripaz-primary: #0d6efd;
            --tripaz-dark: #1a1a2e;
            --admin-accent: #dc3545;
            --sidebar-width: 250px;
            --navbar-height: 56px;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f0f2f5;
        }

        /* ── Sidebar ── */
        #admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: #12121f; /* slightly darker than tripaz-dark */
            color: #fff;
            overflow-y: auto;
            z-index: 1040;
            display: flex;
            flex-direction: column;
        }

        #admin-sidebar::-webkit-scrollbar { width: 4px; }
        #admin-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }

        .sidebar-brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .sidebar-brand .brand-texts {
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand .brand-name {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            line-height: 1;
        }

        .sidebar-brand .brand-name span {
            color: var(--tripaz-primary);
        }

        .sidebar-brand .brand-badge {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            background-color: var(--admin-accent);
            color: #fff;
            padding: 0.1rem 0.4rem;
            border-radius: 3px;
            margin-top: 3px;
            align-self: flex-start;
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
            color: rgba(255,255,255,0.3);
            padding: 0.75rem 1.25rem 0.25rem;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.6rem 1.25rem;
            color: rgba(255,255,255,0.68);
            border-radius: 0;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.15s, color 0.15s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            background-color: rgba(255,255,255,0.05);
            color: #fff;
        }

        /* Admin uses red accent for active state */
        .sidebar-nav .nav-link.active {
            background-color: rgba(220,53,69,0.18);
            color: #fff;
            border-left-color: var(--admin-accent);
        }

        .sidebar-nav .nav-link i {
            font-size: 1rem;
            width: 1.25rem;
            text-align: center;
        }

        .sidebar-nav .nav-link .badge {
            margin-left: auto;
            font-size: 0.65rem;
        }

        .sidebar-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.07);
            font-size: 0.78rem;
            color: rgba(255,255,255,0.25);
        }

        /* ── Top Navbar ── */
        #admin-topnav {
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

        .admin-topnav-badge {
            background-color: var(--admin-accent);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.15rem 0.45rem;
            border-radius: 3px;
        }

        /* ── Main Content ── */
        #admin-main {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 1.75rem;
            min-height: calc(100vh - var(--navbar-height));
        }

        /* ── Mobile ── */
        @media (max-width: 991.98px) {
            #admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }
            #admin-sidebar.show {
                transform: translateX(0);
            }
            #admin-topnav {
                left: 0;
            }
            #admin-main {
                margin-left: 0;
            }
            .sidebar-overlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.45);
                z-index: 1035;
            }
        }

        .sidebar-overlay { display: none; }

        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .table > :not(caption) > * > * {
            vertical-align: middle;
        }

        /* ── Admin action buttons consistent sizing ── */
        .action-btn-group .btn {
            white-space: nowrap;
        }
    </style>

    @yield('head')
</head>
<body>

{{-- ================================================================ --}}
{{-- SIDEBAR                                                          --}}
{{-- ================================================================ --}}
<aside id="admin-sidebar">

    {{-- Brand --}}
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon">
            <i class="ph ph-shield-check fs-4" style="color: var(--admin-accent)"></i>
        </div>
        <div class="brand-texts">
            <span class="brand-name">Trip<span>Az</span></span>
            <span class="brand-badge">Админ</span>
        </div>
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <span class="sidebar-section-label">Обзор</span>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="ph ph-grid-four"></i> Обзор
        </a>

        <span class="sidebar-section-label">Модерация</span>

        <a href="{{ route('admin.listings.index') }}"
           class="nav-link {{ request()->routeIs('admin.listings.*') ? 'active' : '' }}">
            <i class="ph ph-buildings"></i>
            Объявления
            {{-- Pending count badge — pass $pendingListingsCount from controller or use 0 as placeholder --}}
            @if(isset($pendingListingsCount) && $pendingListingsCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $pendingListingsCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.reviews.index') }}"
           class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
            <i class="ph ph-star"></i>
            Отзывы
            {{-- Pending count badge --}}
            @if(isset($pendingReviewsCount) && $pendingReviewsCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $pendingReviewsCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.listing-payments.index') }}"
           class="nav-link {{ request()->routeIs('admin.listing-payments.*') ? 'active' : '' }}">
            <i class="ph ph-credit-card"></i>
            Платежи
            @if(isset($pendingPaymentsCount) && $pendingPaymentsCount > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $pendingPaymentsCount }}</span>
            @endif
        </a>

        <span class="sidebar-section-label">Пользователи и маркетинг</span>

        <a href="{{ route('admin.users.index') }}"
           class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="ph ph-users-three"></i> Пользователи
        </a>

        <a href="{{ route('admin.promotions.index') }}"
           class="nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
            <i class="ph ph-lightning"></i> Продвижение
        </a>

        <span class="sidebar-section-label">Контент</span>

        <a href="{{ route('admin.categories.index') }}"
           class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="ph ph-tag"></i> Категории
        </a>

        <a href="{{ route('admin.amenities.index') }}"
           class="nav-link {{ request()->routeIs('admin.amenities.*') && !request()->routeIs('admin.amenity-groups.*') ? 'active' : '' }}">
            <i class="ph ph-squares-four"></i> Удобства
        </a>

        <a href="{{ route('admin.amenity-groups.index') }}"
           class="nav-link {{ request()->routeIs('admin.amenity-groups.*') ? 'active' : '' }}">
            <i class="ph ph-stack"></i> Группы удобств
        </a>

        <a href="{{ route('admin.lookup-options.index') }}"
           class="nav-link {{ request()->routeIs('admin.lookup-options.*') ? 'active' : '' }}">
            <i class="ph ph-check-square"></i> Опции полей
        </a>

        <a href="{{ route('admin.regions.index') }}"
           class="nav-link {{ request()->routeIs('admin.regions.*') ? 'active' : '' }}">
            <i class="ph ph-map-pin"></i> Регионы
        </a>

        <span class="sidebar-section-label">Настройки</span>

        <a href="{{ route('admin.booking-config.index') }}"
           class="nav-link {{ request()->routeIs('admin.booking-config.*') ? 'active' : '' }}">
            <i class="ph ph-calendar-check"></i> Конф. бронирования
        </a>

        <a href="{{ route('admin.system-settings.index') }}"
           class="nav-link {{ request()->routeIs('admin.system-settings.*') ? 'active' : '' }}">
            <i class="ph ph-sliders-horizontal"></i> Системные настройки
        </a>
    </nav>

    {{-- Sidebar footer --}}
    <div class="sidebar-footer">
        &copy; {{ date('Y') }} TripAz Admin
    </div>
</aside>

{{-- Sidebar overlay (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- ================================================================ --}}
{{-- TOP NAVBAR                                                        --}}
{{-- ================================================================ --}}
<header id="admin-topnav">

    {{-- Mobile toggle --}}
    <button class="btn btn-sm btn-outline-secondary d-lg-none me-1"
            id="sidebarToggle" type="button" aria-label="Toggle sidebar">
        <i class="ph ph-list fs-5"></i>
    </button>

    {{-- Label --}}
    <div class="me-auto d-flex align-items-center gap-2">
        <span class="fw-semibold text-secondary small">
            <i class="ph ph-shield-check me-1"></i>Панель администратора
        </span>
        <span class="admin-topnav-badge">Админ</span>
    </div>

    {{-- View public site --}}
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
<main id="admin-main">

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
        $('#admin-sidebar').toggleClass('show');
        $('#sidebarOverlay').toggle();
    });
    $('#sidebarOverlay').on('click', function () {
        $('#admin-sidebar').removeClass('show');
        $(this).hide();
    });
</script>

@yield('scripts')

</body>
</html>
