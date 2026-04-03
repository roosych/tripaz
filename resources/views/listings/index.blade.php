@extends('layouts.app')

@php
    $pageTitleMap = [
        'hotel'      => __('common.types.hotels'),
        'home'       => __('common.types.homes'),
        'tour'       => __('common.types.tours'),
        'activity'   => __('common.types.activities'),
        'guide'      => __('common.types.guides'),
        'restaurant' => __('common.types.restaurants'),
    ];
@endphp
@section('title', ($type ? ($pageTitleMap[$type] ?? ucfirst($type)) : __('listings.all_listings')) . ' — TripAz Azerbaijan')

@section('content')
<div class="container-xl py-4">

    {{-- Page header --}}
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold" style="color: var(--tripaz-text-primary);">
                @if($type)
                    {{ $pageTitleMap[$type] ?? ucfirst($type) }}
                @else
                    {{ __('listings.all_listings') }}
                @endif
            </h1>
            <p class="mb-0" style="font-size:0.875rem; color: var(--tripaz-text-secondary);">
                {{ __('listings.subtitle.' . ($type ?? 'all')) }}
            </p>
        </div>

        {{-- Mobile filter toggle --}}
        <button class="btn btn-sm d-lg-none fw-semibold"
                style="border: 1px solid var(--tripaz-border); background: var(--tripaz-card-bg); color: var(--tripaz-text-primary); border-radius: var(--tripaz-radius-sm);"
                type="button"
                data-bs-toggle="collapse" data-bs-target="#mobile-filters">
            <i class="ph ph-funnel me-1" style="color:var(--tripaz-primary);"></i>{{ __('listings.filters') }}
        </button>
    </div>

    <div class="row g-4">

        {{-- ---------------------------------------------------------------- --}}
        {{-- LEFT: Filter Sidebar --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="col-lg-3">
            {{-- Mobile collapsible wrapper --}}
            <div class="collapse d-lg-block" id="mobile-filters">
                <div class="filter-sidebar">
                    <x-listing-filter
                        :regions="$regions"
                        :amenities="$amenities"
                        :categories="$categories"
                        :type="$type"
                        :locale="$locale"
                        :minBedrooms="$minBedrooms"
                        :maxBedrooms="$maxBedrooms"
                        :minGuests="$minGuests"
                        :maxGuests="$maxGuests" />
                </div>
            </div>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- RIGHT: Sort Toolbar + Listing Grid --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="col-lg-9">

            {{-- Sort toolbar --}}
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <span style="color:var(--tripaz-text-secondary); font-size:0.85rem;">
                    {{ $listings->total() }} {{ $listings->total() === 1 ? __('listings.results_one') : __('listings.results_other') }}
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:0.8rem; font-weight:600; color:var(--tripaz-text-secondary); text-transform:uppercase; letter-spacing:0.03em;">{{ __('listings.sort_by') }}</span>
                    <div class="d-flex gap-1" id="sort-group">
                        @php
                            $sortOptions = ['boost' => __('listings.sort.featured'), 'newest' => __('listings.sort.newest')];
                            $currentSort = request('sort', 'boost');
                        @endphp
                        @foreach($sortOptions as $sortVal => $sortLabel)
                            <input type="radio"
                                   class="btn-check sort-radio"
                                   name="sort_ui"
                                   id="sort-ui-{{ $sortVal }}"
                                   value="{{ $sortVal }}"
                                   {{ $currentSort === $sortVal ? 'checked' : '' }}
                                   autocomplete="off">
                            <label class="btn btn-sm rounded-pill px-3 fw-semibold"
                                   for="sort-ui-{{ $sortVal }}"
                                   style="font-size:0.8rem; border: 1px solid var(--tripaz-border); color: var(--tripaz-text-secondary); background: var(--tripaz-card-bg);">
                                {{ $sortLabel }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Listings container — replaced by AJAX --}}
            <div id="listings-container">
                @if($listings->isEmpty())
                    <div class="text-center py-5">
                        <i class="ph ph-magnifying-glass"
                           style="font-size:4rem; color:var(--tripaz-primary); opacity:0.7;"></i>
                        <h4 class="mt-3 fw-semibold" style="color: var(--tripaz-text-primary);">{{ __('listings.empty.title') }}</h4>
                        <p style="color: var(--tripaz-text-secondary);">{{ __('listings.empty.message') }}</p>
                        <a href="{{ route('listings.index', $type ? ['type' => $type] : []) }}"
                           class="btn btn-sm fw-semibold text-white mt-2"
                           style="background:var(--tripaz-primary); border:none; border-radius:8px; padding: 0.45rem 1.5rem;">
                            {{ __('listings.clear_filters') }}
                        </a>
                    </div>
                @else
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                        @foreach($listings as $listing)
                            <div class="col">
                                <x-listing-card :listing="$listing" :locale="$locale" />
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($listings->hasPages())
                        <div class="d-flex justify-content-center mt-5">
                            {{ $listings->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Sort pill active state — orange fill on checked */
#sort-group .btn-check:checked + .btn {
    background: var(--tripaz-primary) !important;
    border-color: var(--tripaz-primary) !important;
    color: #fff !important;
}
</style>
@endpush

@push('scripts')
<script>
$(function () {

    /**
     * Sort-by AJAX handler
     *
     * On change of any .sort-radio button:
     *  1. Build a new URL from current location params, overriding `sort`.
     *  2. Show spinner, disable the group.
     *  3. Fetch the page with the new sort param.
     *  4. Extract #listings-container from the response and replace it.
     *  5. Hide spinner, re-enable group. On error show an alert toast.
     */
    // Show spinner when filter form is submitted
    $(document).on('submit', '#filter-form', function () {
        $('#sort-spinner').removeClass('d-none');
    });

    // Show spinner when switching type category tabs
    $(document).on('click', '#type-tabs a', function () {
        $('#sort-spinner').removeClass('d-none');
    });

    $(document).on('change', '.sort-radio', function () {
        var sortVal    = $(this).val();
        var $container = $('#listings-container');
        var $spinner   = $('#sort-spinner');
        var $group     = $('#sort-group');

        // Build new URL: keep all existing query params, override sort, reset to page 1
        var params = new URLSearchParams(window.location.search);
        params.set('sort', sortVal);
        params.delete('page'); // reset pagination on sort change
        var newUrl = window.location.pathname + '?' + params.toString();

        // Update browser URL without full reload (cosmetic only, real data comes from AJAX)
        history.replaceState(null, '', newUrl);

        // Show loading state
        $spinner.removeClass('d-none');
        $group.css('pointer-events', 'none').css('opacity', '0.6');
        $container.css('opacity', '0.4');

        $.ajax({
            url: newUrl,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                // Parse the full HTML response and extract only #listings-container
                var $parsed = $($.parseHTML(html, document, true));
                var $newContainer = $parsed.find('#listings-container');

                if ($newContainer.length) {
                    $container.html($newContainer.html());
                    if (typeof window.reinitListingSwipers === 'function') {
                        window.reinitListingSwipers(document.getElementById('listings-container'));
                    }
                } else {
                    // Fallback: backend may not be ready yet — silently do nothing
                    console.warn('[Sort AJAX] #listings-container not found in response.');
                }
            },
            error: function (xhr) {
                // Backend not ready yet — log and show a dismissible alert
                console.warn('[Sort AJAX] Request failed (' + xhr.status + '). Backend may not be implemented yet.');

                var $alert = $(
                    '<div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">' +
                        '<i class="ph ph-warning me-2"></i>' +
                        'Sorting is not yet available. Please try again later.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>'
                );
                $container.before($alert);

                // Auto-dismiss after 4 s
                setTimeout(function () { $alert.alert('close'); }, 4000);
            },
            complete: function () {
                // Restore UI regardless of outcome
                $spinner.addClass('d-none');
                $group.css('pointer-events', '').css('opacity', '');
                $container.css('opacity', '');
            }
        });
    });

});
</script>
@endpush
