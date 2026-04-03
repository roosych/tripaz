{{--
    Listing Card Component
    Props:
      $listing — App\Models\Listing (with translations, location.region, media, amenities, detail loaded)
      $locale  — string (current locale)
--}}
@props([
    'listing',
    'locale'      => 'az',
    'isFavorited' => null,
])

@php
    $translation = $listing->translation($locale);
    $detail      = $listing->detail ? $listing->detail()->first() : null;
    $type        = $listing->type->value;

    $typeBadgeMap = [
        'hotel'      => ['label' => __('common.types.hotel'),      'class' => 'badge-hotel'],
        'home'       => ['label' => __('common.types.home_short'), 'class' => 'badge-home'],
        'tour'       => ['label' => __('common.types.tour'),       'class' => 'badge-tour'],
        'activity'   => ['label' => __('common.types.activity'),   'class' => 'badge-activity'],
        'guide'      => ['label' => __('common.types.guide'),      'class' => 'badge-guide'],
        'restaurant' => ['label' => __('common.types.restaurant'), 'class' => 'badge-restaurant'],
    ];

    $typeBadge = $typeBadgeMap[$type] ?? ['label' => ucfirst($type), 'class' => 'bg-secondary'];

    // All images for slider (exclude YouTube, max 5)
    $slideImages = $listing->media
        ->where('mime_type', '!=', 'video/youtube')
        ->sortBy('sort_order')
        ->take(5)
        ->values();

    // Build URLs array
    $slideUrls = $slideImages->map(function ($m) {
        return $m->custom_properties['url'] ?? $m->url();
    })->filter()->values()->all();

    // Prepend featured_image if set
    if ($listing->featured_image) {
        $featuredUrl = str_starts_with($listing->featured_image, 'http')
            ? $listing->featured_image
            : asset('storage/' . $listing->featured_image);
        array_unshift($slideUrls, $featuredUrl);
        $slideUrls = array_unique($slideUrls);
    }

    if (empty($slideUrls)) {
        $slideUrls = ['https://placehold.co/600x400'];
    }

    $sliderId = 'swiper-' . $listing->id;

    // Region name
    $regionName = $listing->location?->region?->name($locale) ?? null;

    // Address
    $address = $translation?->address ?? '';

    // Booking
    $bookingEnabled = $listing->isBookingEnabled();

    // is_favorited may come from withExists (public index) or passed explicitly via prop
    $favorited = $isFavorited !== null
        ? (bool) $isFavorited
        : (bool) ($listing->is_favorited ?? false);
@endphp

<div class="card listing-card h-100 border-0 overflow-hidden"
     style="border-radius: var(--tripaz-radius-card); box-shadow: var(--tripaz-shadow);">

    {{-- Cover slider --}}
    <div class="position-relative" style="overflow:hidden;">

        <div class="swiper listing-card-swiper" id="{{ $sliderId }}" style="height:210px;">
            <div class="swiper-wrapper">
                @foreach($slideUrls as $i => $url)
                    <div class="swiper-slide">
                        <a href="{{ route('listings.show', $listing->slug) }}" class="d-block" style="height:210px;">
                            <img src="{{ $url }}"
                                 alt="{{ $translation?->title ?? 'Listing' }}"
                                 style="height:210px; object-fit:cover; width:100%; display:block;"
                                 loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                 onerror="this.onerror=null;this.src='https://placehold.co/600x400'">
                        </a>
                    </div>
                @endforeach
            </div>

            @if(count($slideUrls) > 1)
                <div class="swiper-button-prev listing-swiper-btn"></div>
                <div class="swiper-button-next listing-swiper-btn"></div>
                <div class="swiper-pagination listing-swiper-pagination"></div>
            @endif
        </div>

        {{-- Favorite button top-right --}}
        <div class="position-absolute top-0 end-0 m-2" style="z-index:10">
            <x-favorite-button
                :listingId="$listing->id"
                :isFavorited="$favorited" />
        </div>

        {{-- Type badge top-left --}}
        <span class="badge rounded-pill position-absolute top-0 start-0 m-2 {{ $typeBadge['class'] }}"
              style="font-size:0.7rem; padding: 4px 10px; z-index:10;">
            {{ $typeBadge['label'] }}
        </span>

        {{-- Featured badge bottom-left --}}
        @if($listing->boost_weight > 1)
            <span class="badge rounded-pill position-absolute bottom-0 start-0 m-2 bg-warning text-dark"
                  style="font-size:0.7rem; z-index:10;">
                <i class="ph ph-star me-1"></i>{{ __('common.featured') }}
            </span>
        @endif
    </div>

    {{-- Card body --}}
    <div class="card-body d-flex flex-column p-3 gap-1">

        {{-- Title --}}
        <h6 class="fw-semibold mb-0 lh-sm" style="font-size:0.92rem;">
            <a href="{{ route('listings.show', $listing->slug) }}"
               class="text-decoration-none"
               style="color: var(--tripaz-text-primary);">
                {{ $translation?->title ?? '—' }}
            </a>
        </h6>

        {{-- Location --}}
        @if($regionName || $address)
            <p class="mb-0 d-flex align-items-center gap-1"
               style="font-size:0.78rem; color: var(--tripaz-text-secondary);">
                <i class="ph ph-map-pin" style="font-size:0.8rem; color:var(--tripaz-primary);"></i>
                {{ $address ?: $regionName }}
                @if($address && $regionName)
                    &middot; {{ $regionName }}
                @endif
            </p>
        @endif

        {{-- Star rating --}}
        {{-- <x-star-rating
            :rating="$listing->avg_rating ?? 0"
            :reviewCount="$listing->review_count ?? 0"
            size="sm" /> --}}

        {{-- Type-specific snippet --}}
        <div style="font-size:0.78rem; color:var(--tripaz-text-secondary);">
            @if($type === 'hotel' && $detail)
                @if($detail->stars)
                    <span class="me-2">
                        @for($i = 0; $i < $detail->stars; $i++)<i class="ph ph-star text-warning"></i>@endfor
                        {{ $detail->stars }}-star
                    </span>
                @endif
                {{-- @if($detail->check_in_time)
                    <span class="me-2"><i class="ph ph-clock me-1"></i>In: {{ $detail->check_in_time }}</span>
                @endif
                @if($detail->check_out_time)
                    <span><i class="ph ph-clock me-1"></i>Out: {{ $detail->check_out_time }}</span>
                @endif --}}

            @elseif($type === 'home' && $detail)
                <span class="me-2"><i class="ph ph-door me-1"></i>{{ $detail->bedrooms }} {{ $detail->bedrooms !== 1 ? __('common.beds') : __('common.bed') }}</span>
                <span class="me-2"><i class="ph ph-users-three me-1"></i>{{ $detail->max_guests }} {{ __('common.guests') }}</span>
                @if($detail->total_area)
                    <span><i class="ph ph-arrows-out me-1"></i>{{ $detail->total_area }} {{ __('common.sqm') }}</span>
                @endif

            @elseif($type === 'tour' && $detail)
                <span class="me-2"><i class="ph ph-clock me-1"></i>{{ $detail->duration_hours }}h</span>

            @elseif($type === 'activity' && $detail)
                <span class="me-2"><i class="ph ph-clock me-1"></i>{{ $detail->duration_minutes }} min</span>

            @elseif($type === 'guide' && $detail)
                @if($detail->languages)
                    <span class="me-2">
                        <i class="ph ph-translate me-1"></i>
                        {{ implode(', ', array_slice((array)$detail->languages, 0, 3)) }}
                    </span>
                @endif
                @if($detail->experience_years)
                    <span><i class="ph ph-medal me-1"></i>{{ $detail->experience_years }} {{ __('common.yrs_exp') }}</span>
                @endif

            @elseif($type === 'restaurant' && $detail)
                @if($detail->price_range)
                    @php
                        $priceIcons = ['budget' => '₼', 'mid' => '₼₼', 'upscale' => '₼₼₼', 'fine_dining' => '₼₼₼₼'];
                        $priceVal   = $detail->price_range instanceof \App\Enums\PriceRange
                            ? $detail->price_range->value
                            : (string) $detail->price_range;
                    @endphp
                    <span class="fw-semibold text-success">{{ $priceIcons[$priceVal] ?? '₼' }}</span>
                @endif
            @endif
        </div>

        {{-- Amenities --}}
        @if($listing->amenities->isNotEmpty())
            <x-amenity-list
                :amenities="$listing->amenities"
                :max="4"
                :locale="$locale" />
        @endif

        {{-- Book Now button --}}
        @if($bookingEnabled && in_array($type, ['hotel', 'home']))
            <div class="mt-auto pt-2">
                <a href="{{ route('listings.show', $listing->slug) }}#booking"
                   class="btn btn-sm w-100 fw-semibold text-white"
                   style="background:var(--tripaz-primary); border:none; border-radius:8px;">
                    {{ __('listings.book_now') }}
                </a>
            </div>
        @endif

    </div>
</div>

@once
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
    .listing-card-swiper { width: 100%; }
    .listing-swiper-btn {
        --swiper-navigation-size: 16px;
        width: 28px; height: 28px;
        background: rgba(255,255,255,0.85);
        border-radius: 50%;
        color: #333;
        top: 50%; transform: translateY(-50%);
        opacity: 0;
        transition: opacity 0.2s;
    }
    .listing-card:hover .listing-swiper-btn { opacity: 1; }
    .listing-swiper-btn::after { font-size: 10px; font-weight: 700; }
    .listing-swiper-btn.swiper-button-prev { left: 8px; }
    .listing-swiper-btn.swiper-button-next { right: 8px; }
    .listing-swiper-btn.swiper-button-disabled {
        pointer-events: auto !important;
        opacity: 0;
    }
    .listing-card:hover .listing-swiper-btn.swiper-button-disabled { opacity: 0.35; }
    .listing-swiper-pagination {
        bottom: 6px !important;
    }
    .listing-swiper-pagination .swiper-pagination-bullet {
        width: 5px; height: 5px;
        background: #fff;
        opacity: 0.7;
    }
    .listing-swiper-pagination .swiper-pagination-bullet-active {
        opacity: 1;
        background: #fff;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
function initListingSwipers(root) {
    root = root || document;
    root.querySelectorAll('.listing-card-swiper').forEach(function (el) {
        if (el._swiperInitialized) return;
        el._swiperInitialized = true;

        new Swiper(el, {
            loop: false,
            pagination: {
                el: el.querySelector('.listing-swiper-pagination'),
                clickable: true,
            },
            navigation: {
                prevEl: el.querySelector('.listing-swiper-btn.swiper-button-prev'),
                nextEl: el.querySelector('.listing-swiper-btn.swiper-button-next'),
            },
        });
    });

    // Prevent arrow/pagination clicks from bubbling to the <a> slide link
    root.querySelectorAll('.listing-swiper-btn, .listing-swiper-pagination').forEach(function (btn) {
        if (btn._stopPropSet) return;
        btn._stopPropSet = true;
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initListingSwipers();
});

// Re-init after AJAX container replacement (sort/filter)
window.reinitListingSwipers = initListingSwipers;
</script>
@endpush
@endonce
