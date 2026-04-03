@extends('layouts.app')

@section('title', 'Verify Email — TripAz')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-5 text-center">

                    <i class="ph ph-envelope-simple-open display-3 text-primary mb-3"></i>

                    <h2 class="h4 fw-bold mb-2">Verify Your Email Address</h2>
                    <p class="text-muted mb-4">
                        Please check your inbox and click the verification link we sent you.
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success py-2 small text-start">
                            <i class="ph ph-check-circle me-1"></i>{{ session('success') }}
                        </div>
                    @endif

                    <button type="button" class="btn btn-outline-primary w-100 mb-3" disabled>
                        <i class="ph ph-paper-plane-tilt me-1"></i>Resend Verification Email
                    </button>

                    <a href="{{ url('/') }}" class="btn btn-link text-muted text-decoration-none small">
                        <i class="ph ph-arrow-left me-1"></i>Back to Home
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
