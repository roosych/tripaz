@extends('layouts.dashboard')

@section('title', __('dashboard.my_favorites'))

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-heart me-2 text-danger"></i>{{ __('dashboard.my_favorites') }}
        </h1>
        <p class="text-muted mb-0 small mt-1">{{ __('dashboard.favorites.subtitle') }}</p>
    </div>
    @if($favorites->isNotEmpty())
        <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
            {{ $favorites->total() }} {{ __('dashboard.favorites.saved') }}
        </span>
    @endif
</div>

{{-- ================================================================ --}}
{{-- EMPTY STATE                                                       --}}
{{-- ================================================================ --}}
@if($favorites->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-heart"
                :title="__('dashboard.favorites.empty.title')"
                :message="__('dashboard.favorites.empty.message')"
                :actionLabel="__('dashboard.browse_listings')"
                actionUrl="{{ route('listings.index') }}" />
        </div>
    </div>

{{-- ================================================================ --}}
{{-- FAVORITES GRID                                                    --}}
{{-- ================================================================ --}}
@else
    @php $locale = app()->getLocale(); @endphp

    <div class="row g-3">
        @foreach($favorites as $listing)
            <div class="col-md-6 col-lg-4">
                <x-listing-card
                    :listing="$listing"
                    :locale="$locale"
                    :isFavorited="true" />
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($favorites->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $favorites->links() }}
        </div>
    @endif
@endif

@endsection
