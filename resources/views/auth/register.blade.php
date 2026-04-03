@extends('layouts.app')

@section('title', 'Register — TripAz')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="h4 fw-bold mb-1 text-center">Create an account</h2>
                    <p class="text-muted text-center mb-4 small">Join TripAz to start exploring Azerbaijan</p>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <i class="ph ph-warning me-1"></i>
                            Please fix the errors below before continuing.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" novalidate>
                        @csrf

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                autocomplete="name"
                                autofocus
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                required
                            >
                            <div class="form-text">Minimum 8 characters.</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm password</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                autocomplete="new-password"
                                required
                            >
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph ph-user-add-01 me-1"></i>Create Account
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <p class="text-center text-muted mt-3 small">
                Already have an account?
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Login</a>
            </p>

        </div>
    </div>
</div>
@endsection
