@extends('layouts.app')

@php
    $translation = $listing->translation($locale);
    $detail      = $listing->detail ? $listing->detail()->first() : null;
    $type        = $listing->type->value;
    $location    = $listing->location;

    $typeBadgeClass = match($type) {
        'hotel'      => 'badge-hotel',
        'home'       => 'badge-home',
        'tour'       => 'badge-tour',
        'activity'   => 'badge-activity',
        'guide'      => 'badge-guide',
        'restaurant' => 'badge-restaurant',
        default      => 'bg-secondary',
    };

    $typeLabel = match($type) {
        'hotel'      => __('common.types.hotel'),
        'home'       => __('common.types.home'),
        'tour'       => __('common.types.tour'),
        'activity'   => __('common.types.activity'),
        'guide'      => __('common.types.guide'),
        'restaurant' => __('common.types.restaurant'),
        default      => ucfirst($type),
    };

    $regionName = $location?->region?->name($locale) ?? null;

    // Rating distribution: [5 => count, 4 => count, ...]
    $ratingDist = $listing->reviews->countBy('rating')->toArray();

    // Cover image — exclude YouTube, prefer cover collection, fallback to first image media
    $coverMedia = $listing->media->where('mime_type', '!=', 'video/youtube')->firstWhere('collection', 'cover')
        ?? $listing->media->where('mime_type', '!=', 'video/youtube')->sortBy('sort_order')->first();

    if ($listing->featured_image) {
        // Look up the media record so we can use custom_properties['url'] for seeded/external images
        $featuredMedia = $listing->media->firstWhere('path', $listing->featured_image);
        if ($featuredMedia && !empty($featuredMedia->custom_properties['url'])) {
            $heroUrl = $featuredMedia->custom_properties['url'];
        } elseif (str_starts_with($listing->featured_image, 'http')) {
            $heroUrl = $listing->featured_image;
        } else {
            $heroUrl = asset('storage/' . $listing->featured_image);
        }
    } elseif ($coverMedia) {
        $heroUrl = $coverMedia->custom_properties['url'] ?? $coverMedia->url() ?: 'https://placehold.co/1200x500';
    } else {
        $heroUrl = 'https://placehold.co/1200x500';
    }

    $isFavorited = auth()->check()
        ? $listing->favorites()->where('user_id', auth()->id())->exists()
        : false;
@endphp

@section('title', ($translation?->seo_title ?: $translation?->title) . ' — TripAz')
@section('meta_description', $translation?->seo_description ?: Str::limit($translation?->description ?? '', 160))

@section('content')

    @php
        $galleryImages  = $imageMedia->values();
        $totalImages    = $galleryImages->count();
        $avgRating      = (float)($listing->avg_rating ?? 0);
        $reviewCount    = (int)($listing->review_count ?? 0);
        $addressParts   = array_filter([$translation?->address, $regionName]);
        $fullAddress    = implode(', ', $addressParts);
        $hasCoords      = !empty($location?->latitude) && !empty($location?->longitude);

        // YouTube ID
        $ytId = null;
        if ($youtubeUrl) {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $youtubeUrl, $ytMatch);
            $ytId = $ytMatch[1] ?? null;
        }

        // Total slides in modal: video (if any) + images
        $totalSlides = ($ytId ? 1 : 0) + $totalImages;
        $smallCount  = min($totalSlides - 1, 4);  // small cells in right 2×2 grid
        $moreCount   = max(0, $totalSlides - 5);  // hidden beyond 5 shown
        $imgStart    = $ytId ? 0 : 1;             // image collection offset for small cells
    @endphp

    {{-- ---------------------------------------------------------------- --}}
    {{-- 1. PAGE HEADER — breadcrumb-style banner --}}
    {{-- ---------------------------------------------------------------- --}}
    <div class="listing-page-header">
        <div class="container-xl">
            <div class="listing-page-header-inner">

                {{-- Left: badge + title + location --}}
                <div class="listing-page-header-body">

                    {{-- Type badge --}}
                    <span class="badge {{ $typeBadgeClass }} mb-2">{{ $typeLabel }}</span>

                    {{-- Star rating --}}
                    @if($avgRating > 0)
                        <div class="listing-page-stars mb-2">
                            @for($s = 1; $s <= 5; $s++)
                                @if($s <= floor($avgRating))
                                    <i class="ph ph-star"></i>
                                @elseif($s - $avgRating < 1 && $s - $avgRating > 0)
                                    <i class="ph ph-star-half"></i>
                                @else
                                    <i class="ph ph-star"></i>
                                @endif
                            @endfor
                            <span class="listing-page-stars-value">{{ number_format($avgRating, 1) }}</span>
                            @if($reviewCount > 0)
                                <span class="listing-page-stars-count">({{ $reviewCount }} reviews)</span>
                            @endif
                        </div>
                    @endif

                    {{-- Title --}}
                    <h1 class="listing-page-title">{{ $translation?->title ?? '—' }}</h1>

                    {{-- Address --}}
                    @if($fullAddress)
                        <p class="listing-page-location">
                            <i class="ph ph-map-pin"></i>
                            {{ $fullAddress }}
                            @if($hasCoords)
                                <a href="#" class="listing-page-map-link"
                                   data-bs-toggle="modal" data-bs-target="#mapModal">{{ __('listings.view_map') }}</a>
                            @endif
                        </p>
                    @endif

                </div>

                {{-- Right: Share + Save buttons --}}
                @php
                    $shareTitle = addslashes($translation?->title ?? '');
                    $shareText  = ($translation?->title ?? '') . ($typeLabel ? ' · ' . $typeLabel : '') . ($fullAddress ? ' · ' . $fullAddress : '');
                    $shareTextEncoded = urlencode($shareText);
                    $shareHeroEncoded = urlencode($heroUrl);
                @endphp
                <div class="listing-page-header-actions d-flex gap-2">
                    {{-- Share dropdown --}}
                    <div class="dropdown" id="share-dropdown">
                        <button class="btn listing-page-fav-btn dropdown-toggle-none"
                                type="button"
                                id="shareBtn"
                                title="{{ __('listings.share') }}"
                                data-share-title="{{ $shareTitle }}"
                                data-share-text="{{ $shareText }}"
                                data-share-hero="{{ $heroUrl }}">
                            <i class="ph ph-share-network"></i>
                            <span>{{ __('listings.share') }}</span>
                        </button>
                        <ul class="dropdown-menu share-dropdown-menu shadow-sm" id="shareMenu"
                            aria-labelledby="shareBtn">
                            <li>
                                <a class="dropdown-item share-item" href="#"
                                   data-network="telegram"
                                   data-url="https://t.me/share/url?url=PAGE_URL&text={{ $shareTextEncoded }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="me-2" style="flex-shrink:0">
                                        <circle cx="12" cy="12" r="12" fill="#2AABEE"/>
                                        <path d="M5.5 11.5l10-4-3.5 10-2-3-4.5-3z" fill="white" stroke="white" stroke-width="0.5" stroke-linejoin="round"/>
                                        <path d="M10 14.5l.7-2.3 2.8 2.3" fill="white"/>
                                    </svg>
                                    Telegram
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item share-item" href="#"
                                   data-network="whatsapp"
                                   data-url="https://wa.me/?text={{ $shareTextEncoded }}%20PAGE_URL">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="me-2" style="flex-shrink:0">
                                        <circle cx="12" cy="12" r="12" fill="#25D366"/>
                                        <path d="M16.8 14.6c-.3-.1-1.6-.8-1.8-.9-.3-.1-.5-.1-.7.1-.2.3-.7.9-.8 1-.2.2-.3.2-.6.1-.3-.2-1.2-.4-2.3-1.4-.9-.8-1.4-1.7-1.6-2-.2-.3 0-.4.1-.6l.4-.5c.1-.2.2-.3.2-.5 0-.2-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4H8c-.2 0-.5.1-.8.4C7 8.6 6.3 9.3 6.3 10.8s1 3 1.2 3.2c.2.2 2 3 4.8 4.2 2.8 1.2 2.8.8 3.3.7.5-.1 1.6-.6 1.8-1.3.2-.6.2-1.2.1-1.3-.1-.1-.3-.2-.5-.3z" fill="white"/>
                                    </svg>
                                    WhatsApp
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item share-item" href="#"
                                   data-network="vk"
                                   data-url="https://vk.com/share.php?url=PAGE_URL&title={{ $shareTextEncoded }}&image={{ $shareHeroEncoded }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="me-2" style="flex-shrink:0">
                                        <circle cx="12" cy="12" r="12" fill="#0077FF"/>
                                        <path d="M12.9 15.5h1c.3 0 .4-.2.4-.4 0 0-.1-.9.4-1 .5-.2 1.1.9 1.7 1.2.5.3.9.2.9.2l1.8-.1s.9 0 .5-.8c0-.1-.3-.6-1.4-1.6-1.1-1-1-1 .4-2.6.9-1.1 1.2-1.7 1.1-2-.1-.3-.7-.2-.7-.2H17c-.2 0-.3.1-.4.2-.1.1-.5 1-.9 1.6-.5.7-1.2 1.6-1.5 1.5-.3-.1-.3-.7-.3-1.1V9.8c0-.3-.1-.5-.5-.5h-2.8s-.3 0-.4.2c-.1.2.1.3.2.4.2.1.4.4.4.8l.1 2.1c0 .3-.1.4-.3.4-.5 0-1.1-.9-1.9-2.2-.4-.7-.6-1-.9-1.5-.1-.2-.3-.3-.6-.3H5.9c-.3 0-.4.1-.4.3 0 .2.1.4.1.4s1.3 3 2.8 4.6c1.3 1.4 2.8 1.7 4.1 1.7z" fill="white"/>
                                    </svg>
                                    ВКонтакте
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item share-item" href="#" data-network="copy">
                                    <i class="ph ph-link me-2" style="font-size:1.1rem; color: var(--tripaz-text-secondary);"></i>
                                    Скопировать ссылку
                                </a>
                            </li>
                        </ul>
                    </div>
                    <button class="btn listing-page-fav-btn{{ $isFavorited ? ' active' : '' }}"
                            data-listing-id="{{ $listing->id }}"
                            title="{{ $isFavorited ? __('listings.remove_favorite') : __('listings.add_favorite') }}">
                        <i class="ph {{ $isFavorited ? 'ph-heart-fill' : 'ph-heart' }}"></i>
                        <span>{{ $isFavorited ? __('common.saved') : __('common.save') }}</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- 2. MEDIA THUMBNAIL STRIP --}}
    {{-- ---------------------------------------------------------------- --}}
    @if($totalSlides > 0)
    {{-- ---------------------------------------------------------------- --}}
    {{-- GALLERY MODAL — fullscreen carousel --}}
    {{-- ---------------------------------------------------------------- --}}
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-label="Photo gallery" aria-modal="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-black">
                <div class="modal-header border-0 py-2">
                    <span class="text-white-50 small" id="galleryCounter">1 / {{ $totalSlides }}</span>
                    <button type="button" class="btn-close btn-close-white ms-auto"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex align-items-center justify-content-center p-0">
                    <div id="galleryCarousel" class="carousel slide w-100" data-bs-ride="false">
                        <div class="carousel-inner">

                            @php $slideNo = 0; @endphp

                            {{-- YouTube video slide (first) --}}
                            @if($ytId)
                                <div class="carousel-item {{ $slideNo === 0 ? 'active' : '' }}">
                                    <div class="d-flex align-items-center justify-content-center"
                                         style="min-height: calc(100vh - 80px);">
                                        <div class="ratio ratio-16x9 w-100" style="max-width: 960px;">
                                            <iframe class="gallery-yt-iframe"
                                                    data-src="https://www.youtube.com/embed/{{ $ytId }}?rel=0&enablejsapi=1"
                                                    src=""
                                                    title="YouTube video"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen></iframe>
                                        </div>
                                    </div>
                                </div>
                                @php $slideNo++; @endphp
                            @endif

                            {{-- Image slides --}}
                            @foreach($galleryImages as $i => $media)
                                <div class="carousel-item {{ $slideNo === 0 ? 'active' : '' }}">
                                    <img src="{{ $media->custom_properties['url'] ?? $media->url() }}"
                                         alt="{{ $media->custom_properties['alt'] ?? ($translation?->title ?? 'Photo ' . ($i + 1)) }}"
                                         class="gallery-modal-img"
                                         loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                         onerror="this.onerror=null;this.src='https://placehold.co/1200x800?text=No+Photo'">
                                </div>
                                @php $slideNo++; @endphp
                            @endforeach

                        </div>
                        @if($totalSlides > 1)
                            <button class="carousel-control-prev" type="button"
                                    data-bs-target="#galleryCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button"
                                    data-bs-target="#galleryCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

<div class="container-xl py-4">

    <div class="row g-4">

        {{-- ------------------------------------------------------------ --}}
        {{-- MAIN COLUMN --}}
        {{-- ------------------------------------------------------------ --}}
        <div class="col-lg-8">

            {{-- -------------------------------------------------------- --}}
            {{-- MEDIA PHOTO GRID --}}
            {{-- -------------------------------------------------------- --}}
            @if($totalSlides > 0)
            <div class="listing-photo-grid mb-4">

                {{-- Slot 0: large photo / video (left, full height) --}}
                <div class="listing-photo-grid__main"
                     data-gallery-open="0"
                     role="button" tabindex="0">
                    @if($ytId)
                        <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg"
                             alt="Watch Video" loading="eager">
                        <div class="photo-grid-play">
                            <i class="ph ph-play-circle"></i>
                        </div>
                    @elseif($galleryImages->isNotEmpty())
                        <img src="{{ $galleryImages[0]->custom_properties['url'] ?? $galleryImages[0]->url() }}"
                             alt="{{ $galleryImages[0]->custom_properties['alt'] ?? ($translation?->title ?? 'Photo') }}"
                             loading="eager"
                             onerror="this.onerror=null;this.src='https://placehold.co/800x400?text=No+Photo'">
                    @endif
                </div>

                {{-- Slots 1–4: small photos (right, 2×2 grid) --}}
                @if($smallCount > 0)
                <div class="listing-photo-grid__thumbs">
                    @for($t = 1; $t <= $smallCount; $t++)
                        @php
                            $imgIndex = $imgStart + ($t - 1);
                            $isLast   = ($t === $smallCount) && ($moreCount > 0);
                        @endphp
                        <div class="listing-photo-grid__thumb{{ $isLast ? ' listing-photo-grid__thumb--more' : '' }}"
                             data-gallery-open="{{ $t }}"
                             role="button" tabindex="0">
                            @if($galleryImages->has($imgIndex))
                                <img src="{{ $galleryImages[$imgIndex]->custom_properties['url'] ?? $galleryImages[$imgIndex]->url() }}"
                                     alt="{{ $galleryImages[$imgIndex]->custom_properties['alt'] ?? ('Photo ' . ($imgIndex + 1)) }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='https://placehold.co/400x200?text=Photo'">
                            @else
                                <img src="https://placehold.co/400x200?text=%20" alt="">
                            @endif
                            @if($isLast)
                                <div class="photo-grid-more">
                                    <span>+{{ $moreCount }}</span>
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
                @endif

            </div>
            @endif

            {{-- -------------------------------------------------------- --}}
            {{-- 3. YOUTUBE VIDEO --}}
            {{-- -------------------------------------------------------- --}}
            {{-- Video block removed: YouTube video is shown together with photos in the gallery --}}

            {{-- -------------------------------------------------------- --}}
            {{-- 4. DESCRIPTION --}}
            {{-- -------------------------------------------------------- --}}
            @if($translation?->description)
                <div class="card border-0 mb-4"
                     style="border-radius: var(--tripaz-radius-card); box-shadow: var(--tripaz-shadow);">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold mb-3" style="color: var(--tripaz-text-primary);">
                            {{ __('listings.about') }}
                        </h6>
                        <div class="lh-lg text-secondary" style="white-space: pre-line;">{{ $translation->description }}</div>
                    </div>
                </div>
            @endif

            {{-- -------------------------------------------------------- --}}
            {{-- 5. TYPE-SPECIFIC DETAILS PANEL --}}
            {{-- -------------------------------------------------------- --}}
            @if($detail)
                <div class="card border-0 mb-4"
                     style="border-radius: var(--tripaz-radius-card); box-shadow: var(--tripaz-shadow);">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold mb-3" style="color: var(--tripaz-text-primary);">
                            {{ __('listings.details') }}
                        </h6>

                        @if($type === 'hotel')
                            <div class="row g-3">
                                @if($detail->check_in_time)
                                    <div class="col-sm-6">
                                        <p class="text-muted small fw-semibold mb-1">{{ __('listings.check_in_time') }}</p>
                                        <p class="mb-0 fw-semibold"><i class="ph ph-clock me-2 text-success"></i>{{ $detail->check_in_time }}</p>
                                    </div>
                                @endif
                                @if($detail->check_out_time)
                                    <div class="col-sm-6">
                                        <p class="text-muted small fw-semibold mb-1">{{ __('listings.check_out_time') }}</p>
                                        <p class="mb-0 fw-semibold"><i class="ph ph-clock me-2 text-warning"></i>{{ $detail->check_out_time }}</p>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($detail->policies))
                                <hr>
                                <h6 class="fw-semibold mb-2">{{ __('listings.policies') }}</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach((array)$detail->policies as $policy => $value)
                                        <li class="list-group-item px-0 py-1 border-0">
                                            <i class="ph ph-check-circle text-success me-2"></i>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $policy)) }}:</strong> {{ $value }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif


                        @elseif($type === 'home')
                            <div class="row g-3">
                                @if($detail->property_type)
                                    <div class="col-sm-6">
                                        <p class="text-muted small fw-semibold mb-1">{{ __('listings.property_type') }}</p>
                                        <p class="mb-0 fw-semibold">
                                            <i class="ph ph-house me-2 text-info"></i>
                                            {{ ucfirst($detail->property_type instanceof \App\Enums\PropertyType ? $detail->property_type->value : $detail->property_type) }}
                                        </p>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <p class="text-muted small fw-semibold mb-1">{{ $detail->bedrooms !== 1 ? __('common.beds') : __('common.bed') }}</p>
                                    <p class="mb-0 fw-semibold"><i class="ph ph-door me-2 text-info"></i>{{ $detail->bedrooms }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-muted small fw-semibold mb-1">Bathrooms</p>
                                    <p class="mb-0 fw-semibold"><i class="ph ph-drop me-2 text-info"></i>{{ $detail->bathrooms }} bathroom{{ $detail->bathrooms !== 1 ? 's' : '' }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-muted small fw-semibold mb-1">{{ __('listings.booking.guests') }}</p>
                                    <p class="mb-0 fw-semibold"><i class="ph ph-users me-2 text-info"></i>{{ $detail->max_guests }} {{ __('common.guests') }}</p>
                                </div>
                                @if($detail->total_area)
                                    <div class="col-sm-6">
                                        <p class="text-muted small fw-semibold mb-1">Total Area</p>
                                        <p class="mb-0 fw-semibold"><i class="ph ph-arrows-out me-2 text-info"></i>{{ $detail->total_area }} {{ __('common.sqm') }}</p>
                                    </div>
                                @endif
                                @if($detail->floor !== null)
                                    <div class="col-sm-6">
                                        <p class="text-muted small fw-semibold mb-1">Floor</p>
                                        <p class="mb-0 fw-semibold"><i class="ph ph-stack me-2 text-info"></i>Floor {{ $detail->floor }}</p>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($detail->house_rules))
                                <hr>
                                <h6 class="fw-semibold mb-2">{{ __('listings.house_rules') }}</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach((array)$detail->house_rules as $ruleKey => $ruleValue)
                                        <li class="list-group-item px-0 py-1 border-0">
                                            <i class="ph ph-check text-success me-2"></i>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $ruleKey)) }}:</strong>
                                            {{ is_bool($ruleValue) ? ($ruleValue ? __('common.yes') : __('common.no')) : $ruleValue }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                        @elseif($type === 'tour')
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <p class="text-muted small fw-semibold mb-1">{{ __('listings.duration') }}</p>
                                    <p class="mb-0 fw-semibold"><i class="ph ph-clock me-2 text-success"></i>{{ $detail->duration_hours }} hours</p>
                                </div>
                                @if($detail->max_participants)
                                    <div class="col-sm-6">
                                        <p class="text-muted small fw-semibold mb-1">{{ __('listings.max_participants') }}</p>
                                        <p class="mb-0 fw-semibold"><i class="ph ph-users-three me-2 text-success"></i>{{ $detail->max_participants }} people</p>
                                    </div>
                                @endif
                                @if($detail->meeting_point)
                                    <div class="col-12">
                                        <p class="text-muted small fw-semibold mb-1">{{ __('listings.meeting_point') }}</p>
                                        <p class="mb-0"><i class="ph ph-map-pin me-2 text-success"></i>{{ $detail->meeting_point }}</p>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($detail->includes) || !empty($detail->excludes))
                                <hr>
                                <div class="row g-3">
                                    @if(!empty($detail->includes))
                                        <div class="col-sm-6">
                                            <h6 class="fw-semibold text-success mb-2"><i class="ph ph-check-circle me-1"></i>{{ __('listings.includes') }}</h6>
                                            <ul class="list-unstyled mb-0">
                                                @foreach((array)$detail->includes as $item)
                                                    <li class="mb-1 small"><i class="ph ph-check text-success me-1"></i>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if(!empty($detail->excludes))
                                        <div class="col-sm-6">
                                            <h6 class="fw-semibold text-danger mb-2"><i class="ph ph-x-circle me-1"></i>{{ __('listings.excludes') }}</h6>
                                            <ul class="list-unstyled mb-0">
                                                @foreach((array)$detail->excludes as $item)
                                                    <li class="mb-1 small"><i class="ph ph-x text-danger me-1"></i>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($detail->itinerary))
                                <hr>
                                <h6 class="fw-semibold mb-3">Itinerary</h6>
                                <div class="accordion" id="itineraryAccordion">
                                    @foreach((array)$detail->itinerary as $dayIdx => $day)
                                        @php
                                            $dayNum     = $dayIdx + 1;
                                            $dayTitle   = is_array($day) ? ($day['title'] ?? "Day {$dayNum}") : "Day {$dayNum}";
                                            $dayContent = is_array($day) ? ($day['description'] ?? '') : $day;
                                        @endphp
                                        <div class="accordion-item border-0 mb-2">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button {{ $dayIdx > 0 ? 'collapsed' : '' }} bg-light rounded-2 shadow-none"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#day-{{ $dayIdx }}">
                                                    <span class="badge bg-success me-2">Day {{ $dayNum }}</span>
                                                    {{ $dayTitle }}
                                                </button>
                                            </h2>
                                            <div id="day-{{ $dayIdx }}"
                                                 class="accordion-collapse collapse {{ $dayIdx === 0 ? 'show' : '' }}"
                                                 data-bs-parent="#itineraryAccordion">
                                                <div class="accordion-body pt-2">{{ $dayContent }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        @elseif($type === 'activity')
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <p class="text-muted small fw-semibold mb-1">{{ __('listings.duration') }}</p>
                                    <p class="mb-0 fw-semibold"><i class="ph ph-clock me-2 text-warning"></i>{{ $detail->duration_minutes }} minutes</p>
                                </div>
                                @if($detail->max_participants)
                                    <div class="col-sm-6">
                                        <p class="text-muted small fw-semibold mb-1">{{ __('listings.max_participants') }}</p>
                                        <p class="mb-0 fw-semibold"><i class="ph ph-users-three me-2 text-warning"></i>{{ $detail->max_participants }}</p>
                                    </div>
                                @endif
                                @if($listing->categories->isNotEmpty())
                                    <div class="col-12">
                                        <p class="text-muted small fw-semibold mb-2">Categories</p>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($listing->categories as $cat)
                                                <span class="badge bg-warning text-dark">{{ $cat->name($locale) }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @elseif($type === 'guide')
                            <div class="row g-3">
                                @if($detail->languages)
                                    @php
                                        $langNames = [
                                            'en' => 'English', 'ru' => 'Russian', 'az' => 'Azerbaijani',
                                            'de' => 'German',  'fr' => 'French',  'es' => 'Spanish',
                                            'ar' => 'Arabic',  'zh' => 'Chinese', 'tr' => 'Turkish',
                                            'it' => 'Italian', 'ja' => 'Japanese','ko' => 'Korean',
                                            'pt' => 'Portuguese', 'hi' => 'Hindi',
                                        ];
                                    @endphp
                                    <div class="col-12">
                                        <p class="text-muted small fw-semibold mb-2">{{ __('listings.languages_spoken') }}</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach((array)$detail->languages as $lang)
                                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                    <i class="ph ph-translate me-1"></i>{{ $langNames[$lang] ?? ucfirst($lang) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if($detail->experience_years)
                                    <div class="col-sm-6">
                                        <p class="text-muted small fw-semibold mb-1">{{ __('common.yrs_exp') }}</p>
                                        <p class="mb-0 fw-semibold"><i class="ph ph-medal me-2 text-secondary"></i>{{ $detail->experience_years }} years</p>
                                    </div>
                                @endif
                                @if(!empty($detail->certifications))
                                    <div class="col-12">
                                        <p class="text-muted small fw-semibold mb-2">{{ __('listings.certifications') }}</p>
                                        <ul class="list-unstyled mb-0">
                                            @foreach((array)$detail->certifications as $cert)
                                                <li class="mb-1 small"><i class="ph ph-seal-check text-success me-2"></i>{{ $cert }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if($detail->bio_extra)
                                    <div class="col-12">
                                        <hr>
                                        <p class="text-muted small fw-semibold mb-1">{{ __('listings.extended_bio') }}</p>
                                        <p class="text-secondary lh-lg" style="white-space: pre-line;">{{ $detail->bio_extra }}</p>
                                    </div>
                                @endif
                            </div>

                        @elseif($type === 'restaurant')
                            @php
                                $priceRangeIcons = ['budget' => '₼', 'mid' => '₼₼', 'upscale' => '₼₼₼', 'fine_dining' => '₼₼₼₼'];
                                $prVal = $detail->price_range instanceof \App\Enums\PriceRange ? $detail->price_range->value : (string)$detail->price_range;
                            @endphp
                            <div class="row g-3">
                                @if(!empty($detail->cuisine_types))
                                    <div class="col-12">
                                        <p class="text-muted small fw-semibold mb-2">{{ __('listings.cuisine_types') }}</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach((array)$detail->cuisine_types as $cuisine)
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-2 px-3">
                                                    <i class="ph ph-fork-knife me-1"></i>{{ $cuisine }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <p class="text-muted small fw-semibold mb-1">{{ __('listings.price_range') }}</p>
                                    <p class="mb-0 fw-bold text-success fs-5">{{ $priceRangeIcons[$prVal] ?? '$' }}
                                        <small class="text-muted fs-6 fw-normal ms-1">{{ ucwords(str_replace('_', ' ', $prVal)) }}</small>
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                @if($detail->has_outdoor)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-2 px-3">
                                        <i class="ph ph-tree me-1"></i>{{ __('listings.outdoor_seating') }}
                                    </span>
                                @endif
                                @if($detail->has_delivery)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 py-2 px-3">
                                        <i class="ph ph-bicycle me-1"></i>Delivery
                                    </span>
                                @endif
                                @if($detail->has_takeaway)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 py-2 px-3">
                                        <i class="ph ph-shopping-bag me-1"></i>Takeaway
                                    </span>
                                @endif
                            </div>

                            {{-- Opening Hours — временно скрыто --}}

                            @if($detail->menu_url)
                                <div class="mt-3">
                                    <a href="{{ $detail->menu_url }}" target="_blank" rel="noopener"
                                       class="btn btn-outline-danger">
                                        <i class="ph ph-file-text me-2"></i>{{ __('listings.view_menu') }}
                                    </a>
                                </div>
                            @endif
                        @endif

                    </div>
                </div>
            @endif

            {{-- -------------------------------------------------------- --}}
            {{-- 6. AMENITIES --}}
            {{-- -------------------------------------------------------- --}}
            @if($amenitiesByGroup->isNotEmpty())
                <div class="card border-0 mb-4"
                     style="border-radius: var(--tripaz-radius-card); box-shadow: var(--tripaz-shadow);">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold mb-3" style="color: var(--tripaz-text-primary);">
                            {{ __('listings.amenities') }}
                        </h6>
                        <div class="row g-4">
                        @foreach($amenitiesByGroup as $group => $groupAmenities)
                            <div class="col-12 col-md-4">
                                @if($group)
                                    <h6 class="text-muted fw-semibold mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        {{ $group }}
                                    </h6>
                                @endif
                                <div class="d-flex flex-column gap-1">
                                    @foreach($groupAmenities as $amenity)
                                        <div class="amenity-item ps-0">
                                            @if($amenity->icon)
                                                <i class="{{ $amenity->icon }}" style="color:var(--tripaz-primary);"></i>
                                            @else
                                                <i class="ph ph-check" style="color:var(--tripaz-primary);"></i>
                                            @endif
                                            <span>{{ $amenity->name($locale) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            @endif


            {{--
            --------------------------------------------------------
            9. REVIEWS SECTION (commented out — not yet implemented)
            --------------------------------------------------------
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3">Reviews</h6>

                    Rating overview bar
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="text-center">
                            <div class="display-4 fw-bold text-primary">{{ number_format($listing->avg_rating ?? 0, 1) }}</div>
                            <x-star-rating :rating="$listing->avg_rating ?? 0" />
                            <small class="text-muted">{{ $listing->review_count ?? 0 }} reviews</small>
                        </div>
                        <div class="flex-fill">
                            @foreach([5, 4, 3, 2, 1] as $star)
                                @php $pct = $listing->review_count > 0 ? round(($ratingDist[$star] ?? 0) / $listing->review_count * 100) : 0; @endphp
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="small text-muted" style="min-width:14px">{{ $star }}</span>
                                    <i class="ph ph-star text-warning" style="font-size:0.75rem"></i>
                                    <div class="progress flex-fill" style="height:6px;">
                                        <div class="progress-bar bg-warning" style="width: {{ $listing->review_count > 0 ? $pct : 0 }}%"></div>
                                    </div>
                                    <span class="small text-muted" style="min-width:30px">{{ $listing->review_count > 0 ? $pct . '%' : '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(($listing->review_count ?? 0) === 0)
                        <div class="text-center py-3 text-muted">
                            <i class="ph ph-chat-circle display-4 opacity-25"></i>
                            <p class="mt-2 mb-0">No reviews yet. Be the first to share your experience!</p>
                            @auth
                                <button class="btn btn-outline-primary btn-sm mt-3">Write a Review</button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm mt-3">Login to Review</a>
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
            --}}

            {{-- -------------------------------------------------------- --}}
            {{-- 9. SIMILAR LISTINGS IN THIS REGION --}}
            {{-- -------------------------------------------------------- --}}
            @if($similarListings->isNotEmpty())
            <div class="card border-0 mb-4"
                 style="border-radius: var(--tripaz-radius-card); box-shadow: var(--tripaz-shadow);">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3" style="color: var(--tripaz-text-primary);">
                        Bu regionda digər təkliflər
                    </h6>

                    <div class="row row-cols-2 row-cols-md-4 g-3">
                        @foreach($similarListings as $similarListing)
                            @php
                                $st = $similarListing->translation($locale);

                                $typeBadgeMap = [
                                    'hotel'      => ['label' => __('common.types.hotel'),      'class' => 'badge-hotel'],
                                    'home'       => ['label' => __('common.types.home_short'), 'class' => 'badge-home'],
                                    'tour'       => ['label' => __('common.types.tour'),       'class' => 'badge-tour'],
                                    'activity'   => ['label' => __('common.types.activity'),   'class' => 'badge-activity'],
                                    'guide'      => ['label' => __('common.types.guide'),      'class' => 'badge-guide'],
                                    'restaurant' => ['label' => __('common.types.restaurant'), 'class' => 'badge-restaurant'],
                                ];
                                $slType      = $similarListing->type->value;
                                $slTypeBadge = $typeBadgeMap[$slType] ?? ['label' => ucfirst($slType), 'class' => 'bg-secondary'];

                                $slCover = $similarListing->media->where('mime_type', '!=', 'video/youtube')->firstWhere('collection', 'cover')
                                    ?? $similarListing->media->where('mime_type', '!=', 'video/youtube')->sortBy('sort_order')->first();

                                if ($similarListing->featured_image) {
                                    $slCoverUrl = str_starts_with($similarListing->featured_image, 'http')
                                        ? $similarListing->featured_image
                                        : asset('storage/' . $similarListing->featured_image);
                                } elseif ($slCover) {
                                    $slCoverUrl = $slCover->url('thumb') ?: ($slCover->custom_properties['url'] ?? $slCover->url());
                                } else {
                                    $slCoverUrl = 'https://placehold.co/600x400';
                                }
                            @endphp
                            <div class="col">
                                <div class="card h-100 border-0 overflow-hidden"
                                     style="border-radius: var(--tripaz-radius-card); box-shadow: var(--tripaz-shadow);">
                                    <div class="position-relative">
                                        <a href="{{ route('listings.show', $similarListing->slug) }}" class="d-block">
                                            <img src="{{ $slCoverUrl }}"
                                                 alt="{{ $st?->title ?? 'Listing' }}"
                                                 class="card-img-top"
                                                 style="height: 130px; object-fit: cover;"
                                                 loading="lazy"
                                                 onerror="this.onerror=null;this.src='https://placehold.co/600x400'">
                                        </a>
                                        <span class="badge {{ $slTypeBadge['class'] }} position-absolute top-0 start-0 m-2 rounded-pill">
                                            {{ $slTypeBadge['label'] }}
                                        </span>
                                        @if($similarListing->boost_weight > 1)
                                            <span class="badge bg-warning text-dark position-absolute bottom-0 start-0 m-2 rounded-pill">
                                                <i class="ph ph-star me-1"></i>{{ __('common.featured') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="card-body p-2">
                                        <a href="{{ route('listings.show', $similarListing->slug) }}"
                                           class="text-dark text-decoration-none fw-semibold"
                                           style="font-size: 0.8rem; line-height: 1.3;">
                                            {{ $st?->title ?? '—' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('listings.index', ['region' => $listing->location->region->slug ?? $listing->location->region_id]) }}"
                           class="btn btn-sm fw-semibold"
                           style="border: 1.5px solid var(--tripaz-primary); color: var(--tripaz-primary); border-radius: 8px; padding: 0.4rem 1.4rem; background: transparent;"
                           target="_blank">
                            Hamısına bax
                        </a>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- ------------------------------------------------------------ --}}
        {{-- SIDEBAR: Contact + Booking Widget --}}
        {{-- ------------------------------------------------------------ --}}
        <div class="col-lg-4">
            <div class="booking-widget">

                {{-- Contact card --}}
                <div class="card border-0 mb-3"
                     style="border-radius: var(--tripaz-radius-card); box-shadow: var(--tripaz-shadow);">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold mb-3" style="color: var(--tripaz-text-primary);">{{ __('footer.section.contact') }}</h6>
                        @if($listing->contact_phone)
                            @php
                                $rawPhone    = preg_replace('/\D/', '', $listing->contact_phone);
                                $telHref     = '+' . $rawPhone;
                                // Format: +994XX XXX XXXX
                                $displayPhone = $listing->contact_phone;
                                if (strlen($rawPhone) === 12 && str_starts_with($rawPhone, '994')) {
                                    $displayPhone = '+994' . substr($rawPhone, 3, 2) . ' ' . substr($rawPhone, 5, 3) . ' ' . substr($rawPhone, 8, 4);
                                }
                            @endphp
                            <a href="tel:{{ $telHref }}"
                               class="d-flex align-items-center gap-2 text-dark text-decoration-none mb-2">
                                <i class="ph ph-phone" style="color:var(--tripaz-primary);"></i>
                                <span>{{ $displayPhone }}</span>
                            </a>
                        @endif
                        @if($listing->contact_email)
                            <a href="mailto:{{ $listing->contact_email }}"
                               class="d-flex align-items-center gap-2 text-dark text-decoration-none mb-2">
                                <i class="ph ph-envelope" style="color:var(--tripaz-primary);"></i>
                                <span>{{ $listing->contact_email }}</span>
                            </a>
                        @endif
                        @php
                            $waFromSocials = null;
                            if ($listing->social_links) {
                                foreach ($listing->social_links as $sl) {
                                    if (($sl['platform'] ?? '') === 'whatsapp' && !empty($sl['value'])) {
                                        $waFromSocials = 'https://wa.me/' . preg_replace('/\D/', '', $sl['value']);
                                        break;
                                    }
                                }
                            }
                        @endphp
                        @if($listing->social_links)
                            @php $socials = collect($listing->social_links); @endphp
                            @if($socials->isNotEmpty())
                                @php
                                    $socialConfig = [
                                        'instagram' => ['icon' => 'bi-instagram', 'color' => '#E1306C'],
                                        'facebook'  => ['icon' => 'bi-facebook',  'color' => '#1877F2'],
                                        'tiktok'    => ['icon' => 'bi-tiktok',    'color' => '#000'],
                                        'whatsapp'  => ['icon' => 'bi-whatsapp',  'color' => '#25D366'],
                                    ];
                                @endphp
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    @foreach($socials as $link)
                                        @php
                                            $platform = $link['platform'] ?? '';
                                            $cfg = $socialConfig[$platform] ?? null;
                                        @endphp
                                        @if($cfg && !empty($link['value']) && !($platform === 'whatsapp' && $waFromSocials))
                                            @php
                                                $href = $platform === 'whatsapp'
                                                    ? 'https://wa.me/' . preg_replace('/\D/', '', $link['value'])
                                                    : $link['value'];
                                            @endphp
                                            <a href="{{ $href }}" target="_blank" rel="noopener"
                                               title="{{ ucfirst($platform) }}"
                                               style="color: {{ $cfg['color'] }}; font-size: 1.4rem; line-height: 1;">
                                                <i class="ph {{ $cfg['icon'] }}"></i>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        @endif
                        @if($listing->website_url)
                            <a href="{{ $listing->website_url }}" target="_blank" rel="noopener"
                               class="d-flex align-items-center gap-2 text-dark text-decoration-none mb-2">
                                <i class="ph ph-globe" style="color:var(--tripaz-primary);"></i>
                                <span>{{ parse_url($listing->website_url, PHP_URL_HOST) ?? $listing->website_url }}</span>
                            </a>
                        @endif
                        @if(!$listing->contact_phone && !$listing->contact_email && !$listing->website_url)
                            <p class="text-muted small mb-0">{{ __('listings.contact_unavailable') }}</p>
                        @endif

                        {{-- Action buttons --}}
                        @if($listing->contact_phone || $waFromSocials)
                            <div class="d-grid gap-2 mt-3">
                                @if($listing->contact_phone)
                                    <a href="tel:{{ $listing->contact_phone }}"
                                       class="btn btn-primary"
                                       style="background:var(--tripaz-primary); border-color:var(--tripaz-primary);">
                                        <i class="ph ph-phone me-2"></i>{{ __('listings.call') }}
                                    </a>
                                @endif
                                @if($waFromSocials)
                                    <a href="{{ $waFromSocials }}"
                                       target="_blank" rel="noopener"
                                       class="btn"
                                       style="background:#25D366; border-color:#25D366; color:#fff;">
                                        <i class="ph ph-whatsapp-logo me-2"></i>WhatsApp
                                    </a>
                                @endif
                            </div>
                        @endif
                        {{-- Map — hidden for guides --}}
                        @if($type !== 'guide')
                            @if($location?->latitude && $location?->longitude)
                                <hr class="my-3">
                                <h6 class="fw-semibold mb-3" style="color: var(--tripaz-text-primary);">{{ __('listings.view_map') }}</h6>
                                <div id="listing-map" class="rounded-2 overflow-hidden"></div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm flex-fill"
                                            data-bs-toggle="modal" data-bs-target="#mapModal">
                                        <i class="ph ph-map-trifold me-1"></i>{{ __('listings.view_map') }}
                                    </button>
                                    <a href="https://waze.com/ul?ll={{ $location->latitude }},{{ $location->longitude }}&navigate=yes"
                                       target="_blank" rel="noopener"
                                       class="btn btn-outline-secondary btn-sm flex-fill">
                                        <i class="ph ph-navigation-arrow me-1"></i>Waze
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Booking widget (hotels & homes only) --}}
                @if($bookingEnabled && in_array($type, ['hotel', 'home']))
                    <div class="card border-0 mb-3"
                         style="border-radius: var(--tripaz-radius-card); box-shadow: var(--tripaz-shadow);"
                         id="booking">
                        <div class="card-header text-white py-3"
                             style="background: var(--tripaz-primary); border-radius: var(--tripaz-radius-card) var(--tripaz-radius-card) 0 0;">
                            <h6 class="mb-0 fw-semibold">{{ __('listings.book_cta.' . $type) }}</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('listings.show', $listing->slug) }}" method="GET" id="booking-form">
                                <input type="hidden" name="action" value="book">

                                <div class="mb-3">
                                    <label for="check-in" class="form-label small fw-semibold">{{ __('listings.booking.check_in') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-calendar"></i></span>
                                        <input type="text" id="check-in" name="checkin"
                                               class="form-control" placeholder="Select date"
                                               readonly value="{{ request('checkin') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="check-out" class="form-label small fw-semibold">{{ __('listings.booking.check_out') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-calendar"></i></span>
                                        <input type="text" id="check-out" name="checkout"
                                               class="form-control" placeholder="Select date"
                                               readonly value="{{ request('checkout') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="guest-count" class="form-label small fw-semibold">{{ __('listings.booking.guests') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-users"></i></span>
                                        <input type="number" id="guest-count" name="guests"
                                               class="form-control" min="1"
                                               max="{{ $detail?->max_guests ?? ($type === 'home' ? 10 : 4) }}"
                                               value="{{ request('guests', 1) }}">
                                        <span class="input-group-text">{{ __('common.guests') }}</span>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary py-2 fw-semibold">
                                        <i class="ph ph-magnifying-glass me-2"></i>{{ __('listings.booking.check_availability') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

{{-- Owner actions --}}
                @can('update', $listing)
                    <div class="d-grid gap-2">
                        <a href="{{ route('owner.listings.edit', $listing->slug) }}"
                           class="btn btn-warning">
                            <i class="ph ph-pencil-simple me-2"></i>{{ __('listings.edit') }}
                        </a>
                    </div>
                @endcan

            </div>
        </div>
    </div>
</div>

{{-- Map Modal --}}
@if($location?->latitude && $location?->longitude)
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-semibold" id="mapModalLabel">
                    {{ $translation?->title ?? __('listings.view_map') }}
                </h5>
                <div class="d-flex align-items-center gap-2 ms-auto me-3">
                    <a href="https://waze.com/ul?ll={{ $location->latitude }},{{ $location->longitude }}&navigate=yes"
                       target="_blank" rel="noopener"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="ph ph-navigation-arrow me-1"></i>Waze
                    </a>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $location->latitude }},{{ $location->longitude }}"
                       target="_blank" rel="noopener"
                       class="btn btn-sm btn-outline-danger">
                        <i class="ph ph-map-trifold me-1"></i>Google Maps
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="listing-map-modal" style="height: 520px;"></div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
@if($location?->latitude && $location?->longitude)
<script>
(function () {
    var lat = {{ (float) $location->latitude }};
    var lng = {{ (float) $location->longitude }};
    var title = @json($translation?->title ?? 'Location');

    var iconHtml = '<div style="background:#0d6efd;color:#fff;padding:6px 10px;border-radius:20px;font-size:13px;font-weight:600;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,.3)">' +
                   '<i class="ph ph-map-pin me-1"></i>' + title + '</div>';

    // Small sidebar map
    var map = L.map('listing-map').setView([lat, lng], 14);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);
    L.marker([lat, lng], { icon: L.divIcon({ className: '', html: iconHtml, iconAnchor: [0, 0] }) })
        .addTo(map)
        .bindPopup('<strong>' + title + '</strong>')
        .openPopup();

    // Modal map — init once on first open
    var modalMap = null;
    document.getElementById('mapModal').addEventListener('shown.bs.modal', function () {
        if (modalMap) { modalMap.invalidateSize(); return; }
        modalMap = L.map('listing-map-modal').setView([lat, lng], 15);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(modalMap);
        L.marker([lat, lng], { icon: L.divIcon({ className: '', html: iconHtml, iconAnchor: [0, 0] }) })
            .addTo(modalMap)
            .bindPopup('<strong>' + title + '</strong>')
            .openPopup();
    });
})();
</script>
@endif

<script>
$(function () {
    // Booking datepickers
    var today = new Date();

    $('#check-in').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: today,
        onSelect: function (dateStr) {
            var parts = dateStr.split('-');
            var nextDay = new Date(parts[0], parts[1] - 1, parseInt(parts[2]) + 1);
            $('#check-out').datepicker('option', 'minDate', nextDay);
        }
    });

    $('#check-out').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: new Date(today.getTime() + 86400000),
    });

    // Thumbnail strip — gallery modal
    var $galleryModal    = $('#galleryModal');
    var $galleryCarousel = $('#galleryCarousel');
    var totalSlides      = {{ $totalSlides }};

    function loadYtIfNeeded() {
        var $iframe = $galleryCarousel.find('.gallery-yt-iframe');
        if ($iframe.length && !$iframe.attr('src')) {
            $iframe.attr('src', $iframe.data('src'));
        }
    }

    function updateGalleryCounter(idx) {
        $('#galleryCounter').text((idx + 1) + ' / ' + totalSlides);
    }

    function openGallery(slideIndex) {
        if (!$galleryModal.length) return;
        var bsCarousel = bootstrap.Carousel.getOrCreateInstance($galleryCarousel[0], { interval: false });
        bsCarousel.to(slideIndex);
        updateGalleryCounter(slideIndex);
        bootstrap.Modal.getOrCreateInstance($galleryModal[0]).show();
    }

    // Thumbnail click (including +N overlay)
    $(document).on('click keydown', '[data-gallery-open]', function (e) {
        if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
        openGallery(parseInt($(this).data('gallery-open')) || 0);
    });

    // Update counter on slide; load YouTube when video slide becomes active
    $galleryCarousel.on('slid.bs.carousel', function (e) {
        updateGalleryCounter(e.to);
        if ($galleryCarousel.find('.carousel-item.active .gallery-yt-iframe').length) {
            loadYtIfNeeded();
        }
    });

    // Load YouTube when modal opens on video slide
    $galleryModal.on('shown.bs.modal', function () {
        if ($galleryCarousel.find('.carousel-item.active .gallery-yt-iframe').length) {
            loadYtIfNeeded();
        }
    });

    // Pause YouTube when modal closes
    $galleryModal.on('hide.bs.modal', function () {
        $galleryCarousel.find('.gallery-yt-iframe').each(function () {
            this.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
        });
    });

    // ----------------------------------------------------------------
    // Share button — toggle dropdown or native Web Share API on mobile
    // ----------------------------------------------------------------
    var $shareBtn  = $('#shareBtn');
    var $shareMenu = $('#shareMenu');

    $shareBtn.on('click', function (e) {
        e.stopPropagation();

        // Mobile: use native share if available
        if (navigator.share) {
            navigator.share({
                title: $shareBtn.data('share-title'),
                text:  $shareBtn.data('share-text'),
                url:   window.location.href,
            }).catch(function () {});
            return;
        }

        // Desktop: toggle dropdown
        $shareMenu.toggleClass('show');
    });

    // Close dropdown on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#share-dropdown').length) {
            $shareMenu.removeClass('show');
        }
    });

    // Handle share network clicks
    $(document).on('click', '.share-item', function (e) {
        e.preventDefault();
        var network = $(this).data('network');

        if (network === 'copy') {
            navigator.clipboard.writeText(window.location.href).then(function () {
                var $item = $('.share-item[data-network="copy"]');
                var orig = $item.html();
                $item.html('<i class="ph ph-check me-2" style="font-size:1.1rem;color:var(--tripaz-primary);"></i>Скопировано!');
                setTimeout(function () { $item.html(orig); }, 2000);
            });
        } else {
            var url = $(this).data('url').replace('PAGE_URL', encodeURIComponent(window.location.href));
            window.open(url, '_blank', 'noopener,width=640,height=480');
        }

        $shareMenu.removeClass('show');
    });
});
</script>

@endsection

@push('styles')
<style>
/* Share dropdown */
#share-dropdown { position: relative; }

.share-dropdown-menu {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    z-index: 1050;
    min-width: 200px;
    background: var(--tripaz-card-bg);
    border: 1px solid var(--tripaz-border);
    border-radius: var(--tripaz-radius-sm);
    padding: 6px 0;
    display: none;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
}
.share-dropdown-menu.show { display: block; }

.share-dropdown-menu .dropdown-item {
    display: flex;
    align-items: center;
    padding: 9px 16px;
    font-size: 0.875rem;
    color: var(--tripaz-text-primary);
    white-space: nowrap;
    transition: background 0.15s;
}
.share-dropdown-menu .dropdown-item:hover {
    background: var(--tripaz-hover-bg, rgba(141,187,63,.08));
    color: var(--tripaz-primary);
}
.share-dropdown-menu .dropdown-divider {
    border-color: var(--tripaz-border);
}
</style>
@endpush
