@extends('layouts.dashboard')

@section('title', __('dashboard.my_bookings'))

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header">
    <h1 class="h4 fw-bold mb-0">
        <i class="ph ph-calendar-check me-2 text-primary"></i>{{ __('dashboard.my_bookings') }}
    </h1>
    <p class="text-muted mb-0 small mt-1">{{ __('dashboard.bookings.subtitle') }}</p>
</div>

{{-- ================================================================ --}}
{{-- EMPTY STATE                                                       --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <x-empty-state
            icon="ph-calendar-check-in-01"
            :title="__('dashboard.bookings.empty.title')"
            :message="__('dashboard.bookings.empty.message')"
            :actionLabel="__('dashboard.browse_listings')"
            actionUrl="{{ route('listings.index') }}" />

        <div class="text-center pb-4">
            <p class="text-muted small mb-0">
                <i class="ph ph-clock me-1"></i>
                {{ __('dashboard.bookings.dev_notice') }}
            </p>
        </div>
    </div>
</div>

@endsection
