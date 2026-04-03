@extends('layouts.dashboard')

@section('title', __('dashboard.my_reviews'))

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-chat-centered-text me-2 text-primary"></i>{{ __('dashboard.my_reviews') }}
        </h1>
        <p class="text-muted mb-0 small mt-1">
            {{ __('dashboard.reviews.subtitle') }}
        </p>
    </div>
    @if(isset($reviews) && $reviews->isNotEmpty())
        <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
            {{ $reviews->total() }} {{ $reviews->total() === 1 ? __('common.reviews_one') : __('common.reviews_other') }}
        </span>
    @endif
</div>

{{-- ================================================================ --}}
{{-- REVIEWS GRID                                                      --}}
{{-- ================================================================ --}}
@if(isset($reviews) && $reviews->isNotEmpty())

    <div class="row g-3">
        @foreach($reviews as $review)
            @php
                $isApproved = isset($review->is_approved) ? $review->is_approved : ($review->status === 'approved');
                $listingTitle = $review->listing?->translations->firstWhere('locale', 'az')?->title
                             ?? $review->listing?->translations->firstWhere('locale', 'en')?->title
                             ?? ($review->listing?->slug ?? 'Listing');
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">

                    {{-- Card header: listing title --}}
                    <div class="card-header bg-transparent border-bottom py-2 px-3">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="flex-grow-1" style="min-width:0">
                                @if($review->listing)
                                    <a href="{{ route('listings.show', $review->listing->slug) }}"
                                       class="text-decoration-none fw-semibold text-dark d-block text-truncate"
                                       style="font-size:0.9rem">
                                        {{ $listingTitle }}
                                    </a>
                                @else
                                    <span class="fw-semibold text-muted" style="font-size:0.9rem">
                                        {{ $listingTitle }}
                                    </span>
                                @endif
                            </div>
                            {{-- Status badge --}}
                            @if($isApproved)
                                <span class="badge bg-success rounded-pill flex-shrink-0" style="font-size:0.65rem">{{ __('dashboard.reviews.approved') }}</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill flex-shrink-0" style="font-size:0.65rem">{{ __('dashboard.reviews.pending') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Card body: stars + comment --}}
                    <div class="card-body px-3 py-2">
                        {{-- Star display --}}
                        <div class="d-flex align-items-center gap-1 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="ph ph-star"
                                   style="font-size:0.9rem; {{ $i <= ($review->rating ?? 0) ? 'color:#ffc107' : 'color:#dee2e6' }}"></i>
                            @endfor
                            <span class="ms-1 text-muted small">{{ $review->rating ?? '—' }}/5</span>
                        </div>

                        {{-- Comment --}}
                        <p class="text-muted mb-0" style="font-size:0.85rem; line-height:1.5">
                            @if($review->comment)
                                &ldquo;{{ Str::limit($review->comment, 150) }}&rdquo;
                            @else
                                <em>{{ __('dashboard.reviews.no_text') }}</em>
                            @endif
                        </p>
                    </div>

                    {{-- Card footer: date --}}
                    <div class="card-footer bg-transparent border-top py-2 px-3">
                        <small class="text-muted">
                            <i class="ph ph-calendar me-1"></i>
                            {{ $review->created_at->format('d M Y') }}
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($reviews->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $reviews->links('pagination::bootstrap-5') }}
        </div>
    @endif

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-chat-circle"
                :title="__('dashboard.reviews.empty.title')"
                :message="__('dashboard.reviews.empty.message')"
                :actionLabel="__('dashboard.browse_listings')"
                actionUrl="{{ route('listings.index') }}" />
        </div>
    </div>

@endif

@endsection
