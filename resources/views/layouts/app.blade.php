<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TripAz — Azerbaijan Travel Marketplace')</title>
    <meta name="description" content="@yield('meta_description', 'Discover hotels, homes, tours, activities, guides and restaurants across Azerbaijan.')">

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Phosphor Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css">

    {{-- Theme --}}
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    {{-- jQuery UI CSS --}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.min.css">

    {{-- jQuery 3.7.1 + jQuery UI — loaded in <head> so inline scripts in content work --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    @yield('head')
    @stack('styles')
</head>
<body>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-2">
    <div class="container-xl">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="/images/logo.png" alt="TripAz" height="38" style="display:block;">
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto gap-1">
                @foreach(['hotel' => 'hotels', 'home' => 'homes', 'tour' => 'tours', 'activity' => 'activities', 'guide' => 'guides', 'restaurant' => 'restaurants'] as $typeVal => $typeKey)
                    <li class="nav-item">
                        <a class="nav-link {{ request('type') === $typeVal ? 'active' : '' }}"
                           href="{{ route('listings.index', ['type' => $typeVal]) }}">
                            {{ __('common.types.' . $typeKey) }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="d-flex align-items-center gap-2">
                {{-- Locale switcher --}}
                <div class="locale-switcher btn-group" role="group">
                    @foreach(['az' => 'AZ', 'ru' => 'RU', 'en' => 'EN'] as $loc => $label)
                        <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['lang' => $loc])) }}"
                           class="btn btn-sm btn-outline-secondary {{ app()->getLocale() === $loc ? 'active' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                {{-- Auth buttons --}}
                @auth
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ph ph-user-circle me-1"></i>{{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @role('host')
                            <li><a class="dropdown-item" href="{{ route('owner.listings.create') }}">
                                <i class="ph ph-plus-circle me-2"></i>{{ __('nav.add_listing') }}
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            @endrole

                            {{-- Role-based dashboard links --}}
                            @role('admin')
                            <li><a class="dropdown-item" href="{{ route('admin.listings.index') }}">
                                <i class="ph ph-shield-check me-2"></i>{{ __('nav.admin_panel') }}
                            </a></li>
                            @endrole

                            @role('host')
                            <li><a class="dropdown-item" href="{{ route('owner.listings.index') }}">
                                <i class="ph ph-house me-2"></i>{{ __('nav.owner_cabinet') }}
                            </a></li>
                            @endrole

                            @role('user')
                            <li><a class="dropdown-item" href="{{ route('dashboard.profile.show') }}">
                                <i class="ph ph-user me-2"></i>{{ __('dashboard.my_cabinet') }}
                            </a></li>
                            @endrole

                            <li><a class="dropdown-item" href="{{ route('dashboard.favorites.index') }}">
                                <i class="ph ph-heart me-2 text-danger"></i>{{ __('dashboard.my_favorites') }}
                            </a></li>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="ph ph-sign-out me-2"></i>{{ __('auth.logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">{{ __('auth.login.label') }}</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- Flash messages --}}
@if(session('success'))
    <div class="container-xl mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ph ph-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="container-xl mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ph ph-warning-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

{{-- Main content --}}
<main>
    @yield('content')
</main>

{{-- Footer --}}
<footer class="mt-5 py-5">
    <div class="container-xl">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="{{ url('/') }}" class="d-inline-block mb-3">
                    <img src="/images/logo_white.png" alt="TripAz" height="36" style="display:block; filter: brightness(0) invert(1);">
                </a>
                <p class="mb-3">{{ __('footer.tagline') }}</p>
                <div class="d-flex gap-3">
                    <a href="#"><i class="ph ph-facebook-logo fs-5"></i></a>
                    <a href="#"><i class="ph ph-instagram-logo fs-5"></i></a>
                    <a href="#"><i class="ph ph-telegram-logo fs-5"></i></a>
                    <a href="#"><i class="ph ph-youtube-logo fs-5"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-sm-4">
                <h6 class="text-white mb-3">{{ __('footer.section.explore') }}</h6>
                <ul class="list-unstyled">
                    @foreach(['hotel' => 'hotels', 'home' => 'homes', 'tour' => 'tours', 'activity' => 'activities', 'guide' => 'guides', 'restaurant' => 'restaurants'] as $t => $tk)
                        <li class="mb-1"><a href="{{ route('listings.index', ['type' => $t]) }}">{{ __('common.types.' . $tk) }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="col-lg-2 col-sm-4">
                <h6 class="text-white mb-3">{{ __('footer.section.company') }}</h6>
                <ul class="list-unstyled">
                    <li class="mb-1"><a href="#">{{ __('footer.about') }}</a></li>
                    <li class="mb-1"><a href="#">{{ __('footer.blog') }}</a></li>
                    <li class="mb-1"><a href="#">{{ __('footer.careers') }}</a></li>
                    <li class="mb-1"><a href="#">{{ __('footer.press') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-sm-4">
                <h6 class="text-white mb-3">{{ __('footer.section.contact') }}</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="ph ph-map-pin me-2"></i>{{ __('footer.contact.address') }}</li>
                    <li class="mb-2"><i class="ph ph-envelope me-2"></i>info@tripaz.az</li>
                    <li class="mb-2"><i class="ph ph-phone me-2"></i>+994 12 000 00 00</li>
                </ul>
            </div>
        </div>
        <hr class="mt-4 mb-3" style="border-color: rgba(255,255,255,0.1)">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small>&copy; {{ date('Y') }} TripAz. All rights reserved.</small>
            <small>
                <a href="#" class="me-3">{{ __('footer.privacy') }}</a>
                <a href="#">{{ __('footer.terms') }}</a>
            </small>
        </div>
    </div>
</footer>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Leaflet.js --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Locale switching via query param + session
    $(function () {
        // Preserve current page params when switching locale
        $('.locale-switcher a').on('click', function (e) {
            // Links already have ?lang= param — just follow them
        });
    });
</script>

{{-- Favorites AJAX handler --}}
<script src="{{ asset('js/favorites.js') }}"></script>

@stack('scripts')

@yield('scripts')

{{-- Global page-load spinner --}}
<div id="sort-spinner" class="d-none" aria-hidden="true">
    <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;">
        <span class="visually-hidden">{{ __('common.loading') }}</span>
    </div>
</div>
<script>
    // Show spinner on any internal link click
    $(document).on('click', 'a', function () {
        var href = $(this).attr('href');
        if (!href || href === '#' || href.startsWith('#') || href.startsWith('http') || href.startsWith('mailto:') || href.startsWith('tel:') || $(this).attr('target') === '_blank' || $(this).data('bs-toggle')) return;
        $('#sort-spinner').removeClass('d-none');
    });

    // Hide spinner once page is fully loaded
    $(window).on('load', function () {
        $('#sort-spinner').addClass('d-none');
    });

    // Also hide on back/forward navigation
    $(window).on('pageshow', function () {
        $('#sort-spinner').addClass('d-none');
    });
</script>

{{-- Login modal — shown to guests who click the favorite button --}}
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="loginModalLabel">
                    <i class="ph ph-heart text-danger me-2"></i>{{ __('listings.modal_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2 px-4 pb-4">
                <p class="text-muted small mb-4">{{ __('listings.modal_desc') }}</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="modal-email" class="form-label fw-semibold small">{{ __('form.email') }}</label>
                        <input type="email"
                               class="form-control"
                               id="modal-email"
                               name="email"
                               placeholder="you@example.com"
                               required
                               autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label for="modal-password" class="form-label fw-semibold small">{{ __('form.password') }}</label>
                        <input type="password"
                               class="form-control"
                               id="modal-password"
                               name="password"
                               placeholder="••••••••"
                               required
                               autocomplete="current-password">
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="remember" id="modal-remember">
                            <label class="form-check-label small" for="modal-remember">{{ __('form.remember_me') }}</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="ph ph-sign-in me-1"></i>{{ __('auth.sign_in') }}
                    </button>
                </form>

                {{-- Registration temporarily disabled --}}
            </div>
        </div>
    </div>
</div>

</body>
</html>
