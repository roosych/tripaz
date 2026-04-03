@extends('layouts.dashboard')

@section('title', __('dashboard.profile.title'))

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header">
    <h1 class="h4 fw-bold mb-0">
        <i class="ph ph-user-circle me-2 text-primary"></i>{{ __('dashboard.profile.title') }}
    </h1>
    <p class="text-muted mb-0 small mt-1">{{ __('dashboard.profile.subtitle') }}</p>
</div>

<div class="row g-4">

    {{-- ================================================================ --}}
    {{-- LEFT: Personal Information form                                   --}}
    {{-- ================================================================ --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-user me-2 text-primary"></i>{{ __('dashboard.profile.personal_info') }}
                </h6>
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger d-flex gap-2 align-items-start mb-3">
                        <i class="ph ph-warning-circle flex-shrink-0 mt-1"></i>
                        <div>
                            <div class="fw-semibold mb-1">{{ __('dashboard.profile.title') }}:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li class="small">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard.profile.update') }}" id="profile-form">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            {{ __('form.full_name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', Auth::user()->name) }}"
                               required
                               autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            {{ __('form.email') }} <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email', Auth::user()->email) }}"
                               required
                               autocomplete="email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password section --}}
                    <div class="border rounded-3 p-3 bg-light mb-4">
                        <p class="fw-semibold mb-2 small">
                            <i class="ph ph-lock me-2"></i>{{ __('dashboard.profile.change_password') }}
                            <span class="text-muted fw-normal">({{ __('form.password_current_help') }})</span>
                        </p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-semibold">{{ __('form.password_current') }}</label>
                            <input type="password"
                                   class="form-control form-control-sm @error('current_password') is-invalid @enderror"
                                   id="current_password"
                                   name="current_password"
                                   autocomplete="current-password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-semibold">{{ __('form.password_new') }}</label>
                            <input type="password"
                                   class="form-control form-control-sm @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="password_confirmation" class="form-label small fw-semibold">{{ __('form.password_confirm') }}</label>
                            <input type="password"
                                   class="form-control form-control-sm"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-floppy-disk me-2"></i>{{ __('form.save_changes') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- RIGHT: Account Info                                               --}}
    {{-- ================================================================ --}}
    <div class="col-lg-5">

        {{-- Account summary card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-info me-2 text-primary"></i>{{ __('dashboard.profile.account_info') }}
                </h6>
            </div>
            <div class="card-body">

                {{-- Avatar placeholder --}}
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:56px;height:56px;font-size:1.4rem;flex-shrink:0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ Auth::user()->name }}</div>
                        <div class="text-muted small">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <dl class="row mb-0" style="font-size:0.875rem">
                    <dt class="col-5 text-muted fw-normal">{{ __('dashboard.profile.member_since') }}</dt>
                    <dd class="col-7">{{ Auth::user()->created_at->format('M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">{{ __('form.email') }}</dt>
                    <dd class="col-7 text-break">{{ Auth::user()->email }}</dd>

                    <dt class="col-5 text-muted fw-normal">{{ __('dashboard.profile.total_reviews') }}</dt>
                    <dd class="col-7">{{ $reviewCount ?? 0 }}</dd>

                    <dt class="col-5 text-muted fw-normal pt-1">{{ __('form.roles') }}</dt>
                    <dd class="col-7 pt-1">
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(Auth::user()->getRoleNames() as $role)
                                <span class="badge bg-secondary rounded-pill" style="font-size:0.7rem">
                                    {{ ucfirst($role) }}
                                </span>
                            @endforeach
                        </div>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-lightning me-2 text-primary"></i>{{ __('dashboard.profile.quick_links') }}
                </h6>
            </div>
            <div class="card-body d-flex flex-column gap-2 pt-2">
                <a href="{{ route('dashboard.reviews.index') }}" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center gap-2">
                    <i class="ph ph-chat-centered-text text-primary"></i> {{ __('dashboard.my_reviews') }}
                </a>
                <a href="{{ route('dashboard.favorites.index') }}" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center gap-2">
                    <i class="ph ph-heart text-danger"></i> {{ __('dashboard.my_favorites') }}
                </a>
                <a href="{{ route('dashboard.bookings.index') }}" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center gap-2">
                    <i class="ph ph-calendar-check text-success"></i> {{ __('dashboard.my_bookings') }}
                </a>
                <a href="{{ route('listings.index') }}" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center gap-2">
                    <i class="ph ph-compass text-warning"></i> {{ __('dashboard.browse_listings') }}
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
