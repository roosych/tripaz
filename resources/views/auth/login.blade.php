@extends('layouts.app')

@section('title', 'Login — TripAz')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="h4 fw-bold mb-1 text-center">{{ __('auth.login.welcome') }}</h2>
                    <p class="text-muted text-center mb-4 small">{{ __('auth.login.subtitle') }}</p>

                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">{{ __('form.email_address') }}</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                autofocus
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">{{ __('form.password') }}</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="current-password"
                                required
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="mb-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="remember"
                                    name="remember"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <label class="form-check-label text-muted" for="remember">
                                    {{ __('form.remember_me') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph ph-sign-in me-1"></i>{{ __('auth.login.label') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            {{-- Registration temporarily disabled --}}

        </div>
    </div>
</div>
@endsection
