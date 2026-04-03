@extends('layouts.admin')

@section('title', 'Edit Listing')

@push('styles')
<style>
.ring-cover { outline: 2px solid #ffc107; outline-offset: 1px; }
#photo-drop-zone:hover, #photo-drop-zone.dragover { background: #f8f9fa; border-color: #6c757d !important; }
</style>
@endpush

@section('content')

@php
    $statusVal = $listing->status instanceof \App\Enums\ListingStatus
        ? $listing->status->value
        : (string)$listing->status;

    $typeVal = $listing->type instanceof \App\Enums\ListingType
        ? $listing->type->value
        : (string)$listing->type;

    $typeLabelMap = [
        'hotel'      => ['label' => 'Hotel',      'icon' => 'ph-buildings',    'color' => '#0d6efd'],
        'home'       => ['label' => 'Home',       'icon' => 'ph-house',        'color' => '#0dcaf0'],
        'tour'       => ['label' => 'Tour',       'icon' => 'ph-map-trifold',          'color' => '#198754'],
        'activity'   => ['label' => 'Activity',   'icon' => 'ph-lightning',   'color' => '#ffc107'],
        'guide'      => ['label' => 'Guide',      'icon' => 'ph-identification-card', 'color' => '#6c757d'],
        'restaurant' => ['label' => 'Restaurant', 'icon' => 'ph-fork-knife',   'color' => '#dc3545'],
    ];
    $tc = $typeLabelMap[$typeVal] ?? ['label' => ucfirst($typeVal), 'icon' => 'ph-tag', 'color' => '#6c757d'];
@endphp

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center gap-3 flex-wrap">
    <a href="{{ route('admin.listings.index') }}" class="btn btn-sm btn-outline-secondary" title="Back">
        <i class="ph ph-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="h4 fw-bold mb-0">
                <i class="ph ph-pencil-simple me-2 text-danger"></i>Edit Listing
            </h1>
            <x-status-badge :status="$statusVal" />
        </div>
        <p class="text-muted mb-0 small mt-1">{{ $listing->slug }}</p>
    </div>
</div>

{{-- ================================================================ --}}
{{-- VALIDATION ERRORS                                                 --}}
{{-- ================================================================ --}}
@if($errors->any())
    <div class="alert alert-danger d-flex gap-2 align-items-start mb-4">
        <i class="ph ph-warning-circle flex-shrink-0 mt-1"></i>
        <div>
            <div class="fw-semibold mb-1">Please fix the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ================================================================ --}}
{{-- EDIT FORM                                                         --}}
{{-- ================================================================ --}}
<form method="POST"
      action="{{ route('admin.listings.update', $listing) }}"
      id="admin-edit-form">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- -------------------------------------------------------- --}}
        {{-- LEFT: Main fields                                        --}}
        {{-- -------------------------------------------------------- --}}
        <div class="col-lg-8">

            {{-- Listing Type (read-only) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-tag me-2 text-primary"></i>Listing Type
                    </h6>
                </div>
                <div class="card-body">
                    <input type="hidden" name="type" value="{{ $typeVal }}">
                    <div class="d-inline-flex align-items-center gap-2 bg-light border rounded-3 px-3 py-2">
                        <i class="ph {{ $tc['icon'] }} fs-5" style="color:{{ $tc['color'] }}"></i>
                        <span class="fw-semibold">{{ $tc['label'] }}</span>
                        <span class="text-muted small">(cannot be changed after creation)</span>
                    </div>
                </div>
            </div>

            {{-- Titles --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-translate me-2 text-primary"></i>Titles &amp; Description
                    </h6>
                </div>
                <div class="card-body">

                    @foreach(['az' => 'Azerbaijani', 'ru' => 'Russian', 'en' => 'English'] as $locale => $localeName)
                        @php $trans = $listing->translations->firstWhere('locale', $locale); @endphp
                        <div class="mb-3">
                            <label for="title_{{ $locale }}" class="form-label fw-semibold">
                                Title ({{ $localeName }})
                                @if($locale === 'az') <span class="text-danger">*</span> @endif
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">
                                    {{ strtoupper($locale) }}
                                </span>
                                <input type="text"
                                       class="form-control @error("translations.{$locale}.title") is-invalid @enderror"
                                       id="title_{{ $locale }}"
                                       name="translations[{{ $locale }}][title]"
                                       value="{{ old("translations.{$locale}.title", $trans?->title) }}"
                                       {{ $locale === 'az' ? 'required' : '' }}>
                                @error("translations.{$locale}.title")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    @foreach(['az' => 'Azerbaijani', 'ru' => 'Russian', 'en' => 'English'] as $locale => $localeName)
                        @php $trans = $listing->translations->firstWhere('locale', $locale); @endphp
                        <div class="{{ $loop->last ? 'mb-0' : 'mb-3' }}">
                            <label for="description_{{ $locale }}" class="form-label fw-semibold">
                                Description ({{ $localeName }})
                                @if($locale === 'az') <span class="text-danger">*</span> @endif
                            </label>
                            <div class="input-group align-items-start">
                                <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">
                                    {{ strtoupper($locale) }}
                                </span>
                                <textarea class="form-control @error("translations.{$locale}.description") is-invalid @enderror"
                                          id="description_{{ $locale }}"
                                          name="translations[{{ $locale }}][description]"
                                          rows="5"
                                          {{ $locale === 'az' ? 'required' : '' }}>{{ old("translations.{$locale}.description", $trans?->description) }}</textarea>
                                @error("translations.{$locale}.description")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- DYNAMIC: Type-Specific Detail Fields                     --}}
            {{-- Rendered via JS on page load with existing values        --}}
            {{-- -------------------------------------------------------- --}}
            <div id="detail-fields-card" class="card border-0 shadow-sm mb-4" style="display:none;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0" id="detail-fields-heading">
                        <i class="ph ph-sliders-horizontal me-2 text-primary"></i><span id="detail-fields-title">Type Details</span>
                    </h6>
                </div>
                <div class="card-body" id="detail-fields-body">
                    {{-- Populated by JavaScript --}}
                </div>
            </div>

            @if($typeVal === 'tour')
            {{-- -------------------------------------------------------- --}}
            {{-- TOUR ONLY: Itinerary (hidden when no blocks saved yet)   --}}
            {{-- -------------------------------------------------------- --}}
            <div id="itinerary-card" class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-calendar me-2 text-success"></i>Itinerary
                        <span class="badge bg-success ms-2" style="font-size:0.65rem;vertical-align:middle">Tour only</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-success" id="add-day-btn">
                        <i class="ph ph-plus-circle me-1"></i>Добавить
                    </button>
                </div>
                <div class="card-body">
                    <div id="itinerary-days" class="d-flex flex-column gap-3">
                        {{-- Day rows injected by JS --}}
                    </div>
                    <div id="itinerary-empty" class="text-center text-muted py-3 small">
                        <i class="ph ph-calendar-x fs-4 d-block mb-1"></i>
                        Блоки маршрута не добавлены. Нажмите <strong>Добавить</strong>, чтобы начать составлять маршрут.
                    </div>
                </div>
            </div>
            @endif

            {{-- -------------------------------------------------------- --}}
            {{-- DYNAMIC: Amenities                                        --}}
            {{-- Rendered via JS on page load                             --}}
            {{-- -------------------------------------------------------- --}}
            <div id="amenities-card" class="card border-0 shadow-sm mb-4" style="display:none;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-check-square me-2 text-primary"></i>Amenities &amp; Features
                    </h6>
                </div>
                <div class="card-body" id="amenities-body">
                    {{-- Populated by JavaScript --}}
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-phone me-2 text-primary"></i>Contact Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contact_email" class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-envelope"></i></span>
                                <input type="email"
                                       class="form-control @error('contact_email') is-invalid @enderror"
                                       id="contact_email" name="contact_email"
                                       value="{{ old('contact_email', $listing->contact_email) }}">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label fw-semibold">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                <input type="text"
                                       class="form-control @error('contact_phone') is-invalid @enderror"
                                       id="contact_phone" name="contact_phone"
                                       value="{{ old('contact_phone', $listing->contact_phone) }}">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="website_url" class="form-label fw-semibold">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-globe"></i></span>
                                <input type="url"
                                       class="form-control @error('website_url') is-invalid @enderror"
                                       id="website_url" name="website_url"
                                       value="{{ old('website_url', $listing->website_url) }}">
                                @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Social Networks --}}
            @php $existingSocialLinks = old('social_links', $listing->social_links ?? []); @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-share-network me-2 text-primary"></i>Social Networks
                    </h6>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-social-btn">
                        <i class="ph ph-plus-circle me-1"></i>Add
                    </button>
                </div>
                <div class="card-body">
                    <div id="social-links-list" class="d-flex flex-column gap-2">
                        @foreach($existingSocialLinks as $i => $sl)
                            <div class="social-link-row d-flex gap-2 align-items-center">
                                <select class="form-select form-select-sm social-platform-select" name="social_links[{{ $i }}][platform]" style="max-width:160px">
                                    <option value="instagram" {{ ($sl['platform'] ?? '') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                                    <option value="facebook"  {{ ($sl['platform'] ?? '') === 'facebook'  ? 'selected' : '' }}>Facebook</option>
                                    <option value="tiktok"    {{ ($sl['platform'] ?? '') === 'tiktok'    ? 'selected' : '' }}>TikTok</option>
                                    <option value="whatsapp"  {{ ($sl['platform'] ?? '') === 'whatsapp'  ? 'selected' : '' }}>WhatsApp</option>
                                </select>
                                <input type="text"
                                       class="form-control form-control-sm social-value-input"
                                       name="social_links[{{ $i }}][value]"
                                       value="{{ $sl['value'] ?? '' }}"
                                       placeholder="{{ ($sl['platform'] ?? '') === 'whatsapp' ? '+994501234567' : 'https://...' }}">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-social-btn flex-shrink-0" title="Remove">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div id="social-links-empty" class="text-muted small {{ count($existingSocialLinks) > 0 ? 'd-none' : '' }}">
                        <i class="ph ph-info me-1"></i>No social links added. Click <strong>Add</strong> to add one.
                    </div>
                </div>
            </div>

        </div>

        {{-- -------------------------------------------------------- --}}
        {{-- RIGHT: Owner, Region, Categories, Info, Actions         --}}
        {{-- -------------------------------------------------------- --}}
        <div class="col-lg-4">

            {{-- Assign to Owner --}}
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-user-check me-2 text-danger"></i>Owner
                    </h6>
                </div>
                <div class="card-body">
                    <select class="form-select @error('user_id') is-invalid @enderror"
                            id="user_id"
                            name="user_id">
                        <option value="">— No owner (unassigned) —</option>
                        @foreach($hosts as $host)
                            <option value="{{ $host->id }}"
                                    {{ old('user_id', $listing->user_id) == $host->id ? 'selected' : '' }}>
                                {{ $host->name }} ({{ $host->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Region --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-map-pin me-2 text-primary"></i>Region
                    </h6>
                </div>
                <div class="card-body">
                    <select class="form-select @error('location.region_id') is-invalid @enderror"
                            name="location[region_id]"
                            id="region_id">
                        <option value="">— Choose a region —</option>
                        @foreach($regions ?? [] as $region)
                            <option value="{{ $region->id }}"
                                    {{ old('location.region_id', $listing->location?->region_id) == $region->id ? 'selected' : '' }}>
                                {{ $region->getTranslation('name', 'az') }}
                            </option>
                        @endforeach
                    </select>
                    @error('location.region_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- DYNAMIC: Categories                                       --}}
            {{-- Rendered via JS on page load                             --}}
            {{-- -------------------------------------------------------- --}}
            <div id="categories-card" class="card border-0 shadow-sm mb-4" style="display:none;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-tag me-2 text-primary"></i>Categories
                    </h6>
                </div>
                <div class="card-body">
                    <div id="categories-body" style="max-height:220px;overflow-y:auto;" class="d-flex flex-column gap-1">
                        {{-- Populated by JavaScript --}}
                    </div>
                    <div id="categories-empty" class="small text-muted d-none">
                        No categories available for this type.
                    </div>
                </div>
            </div>

            {{-- Loading indicator shown during AJAX call --}}
            <div id="type-loading" class="text-center py-3 d-none">
                <div class="spinner-border spinner-border-sm text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2 small text-muted">Loading fields...</span>
            </div>

            {{-- Listing Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-info me-2 text-primary"></i>Listing Info
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0" style="font-size:0.85rem">
                        <dt class="col-6 text-muted fw-normal">ID</dt>
                        <dd class="col-6">{{ $listing->id }}</dd>
                        <dt class="col-6 text-muted fw-normal">Created</dt>
                        <dd class="col-6">{{ $listing->created_at->format('d M Y') }}</dd>
                        <dt class="col-6 text-muted fw-normal">Updated</dt>
                        <dd class="col-6">{{ $listing->updated_at->format('d M Y') }}</dd>
                        <dt class="col-6 text-muted fw-normal">Status</dt>
                        <dd class="col-6"><x-status-badge :status="$statusVal" /></dd>
                        <dt class="col-6 text-muted fw-normal">Slug</dt>
                        <dd class="col-6 text-break">{{ $listing->slug }}</dd>
                    </dl>
                </div>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- Media                                                    --}}
            {{-- -------------------------------------------------------- --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-images me-2 text-primary"></i>Media
                    </h6>
                    <small class="text-muted">{{ $mediaItems->count() }} file(s)</small>
                </div>
                <div class="card-body p-2">

                    {{-- Existing media grid --}}
                    <div id="media-grid" class="row g-1 mb-2">
                        @forelse($mediaItems as $mediaItem)
                            @if($mediaItem->mime_type === 'video/youtube')
                                {{-- YouTube card --}}
                                <div class="col-4" id="media-item-{{ $mediaItem->id }}">
                                    <div class="position-relative rounded overflow-hidden bg-dark" style="aspect-ratio:1;cursor:default">
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="ph ph-youtube-logo text-danger" style="font-size:2rem"></i>
                                        </div>
                                        <button type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 px-1 media-delete-btn"
                                                style="font-size:0.65rem;line-height:1.4"
                                                data-media-id="{{ $mediaItem->id }}"
                                                data-delete-url="{{ route('admin.listings.media.destroy', [$listing, $mediaItem]) }}"
                                                title="Delete">
                                            <i class="ph ph-x"></i>
                                        </button>
                                    </div>
                                    <div class="text-truncate small text-muted mt-1 px-1" style="font-size:0.65rem">
                                        {{ Str::limit($mediaItem->custom_properties['youtube_url'] ?? '', 30) }}
                                    </div>
                                </div>
                            @else
                                {{-- Photo card --}}
                                @php
                                    $isCover = $listing->featured_image === $mediaItem->path;
                                @endphp
                                <div class="col-4" id="media-item-{{ $mediaItem->id }}">
                                    <div class="position-relative rounded overflow-hidden {{ $isCover ? 'ring-cover' : '' }}" style="aspect-ratio:1">
                                        <img src="{{ $mediaItem->url() }}"
                                             alt="photo"
                                             class="w-100 h-100"
                                             style="object-fit:cover">
                                        {{-- Cover badge --}}
                                        @if($isCover)
                                            <span class="position-absolute bottom-0 start-0 m-1 badge bg-warning text-dark" style="font-size:0.55rem">
                                                <i class="ph ph-star me-1"></i>Cover
                                            </span>
                                        @endif
                                        {{-- Set cover button --}}
                                        @if(!$isCover)
                                            <button type="button"
                                                    class="btn btn-sm btn-warning position-absolute bottom-0 start-0 m-1 p-0 px-1 media-cover-btn"
                                                    style="font-size:0.6rem;line-height:1.4"
                                                    data-media-id="{{ $mediaItem->id }}"
                                                    data-cover-url="{{ route('admin.listings.media.cover', [$listing, $mediaItem]) }}"
                                                    title="Set as cover">
                                                <i class="ph ph-star"></i>
                                            </button>
                                        @endif
                                        {{-- Delete button --}}
                                        <button type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 px-1 media-delete-btn"
                                                style="font-size:0.65rem;line-height:1.4"
                                                data-media-id="{{ $mediaItem->id }}"
                                                data-delete-url="{{ route('admin.listings.media.destroy', [$listing, $mediaItem]) }}"
                                                title="Delete">
                                            <i class="ph ph-x"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="col-12 text-center py-2 text-muted small" id="media-empty-msg">
                                <i class="ph ph-image me-1"></i>No media yet
                            </div>
                        @endforelse
                    </div>

                    <hr class="my-2">

                    {{-- Upload Photo --}}
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Upload Photo</label>
                        <div id="photo-drop-zone"
                             class="border border-dashed rounded p-2 text-center text-muted small"
                             style="cursor:pointer;border-style:dashed!important;min-height:60px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:4px">
                            <i class="ph ph-cloud-arrow-up fs-5"></i>
                            <span>Click or drag jpg/jpeg here</span>
                            <span style="font-size:0.7rem">Max {{ round(config('listing_media.max_kb') / 1024, 0) }} MB each</span>
                        </div>
                        <input type="file"
                               id="photo-file-input"
                               accept=".jpg,.jpeg,image/jpeg"
                               multiple
                               class="d-none">
                        <div id="photo-upload-progress" class="mt-1 d-none">
                            <div class="progress" style="height:4px">
                                <div class="progress-bar bg-primary" id="photo-progress-bar" style="width:0%"></div>
                            </div>
                            <small class="text-muted" id="photo-upload-status"></small>
                        </div>
                    </div>

                    <hr class="my-2">

                    {{-- YouTube --}}
                    @php
                        $existingYoutube = $mediaItems->firstWhere('mime_type', 'video/youtube');
                        $existingYoutubeUrl = $existingYoutube?->custom_properties['youtube_url'] ?? '';
                    @endphp
                    <div class="mb-1">
                        <label class="form-label small fw-semibold mb-1">YouTube Video</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="ph ph-youtube-logo text-danger"></i></span>
                            <input type="url"
                                   id="youtube-url-input"
                                   class="form-control form-control-sm"
                                   placeholder="https://www.youtube.com/watch?v=..."
                                   value="{{ $existingYoutubeUrl }}">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="youtube-add-btn">
                                {{ $existingYoutubeUrl ? 'Update' : 'Add' }}
                            </button>
                        </div>
                        <div id="youtube-feedback" class="small mt-1 d-none"></div>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="ph ph-floppy-disk me-2"></i>Save Changes
                    </button>
                    <a href="{{ route('listings.show', $listing->slug) }}"
                       target="_blank"
                       class="btn btn-outline-secondary w-100">
                        <i class="ph ph-eye me-2"></i>View on Site
                    </a>
                    <a href="{{ route('admin.listings.index') }}" class="btn btn-link text-muted w-100">
                        Back to Listings
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
$(function () {

    // ---------------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------------
    var apiUrl    = '{{ route('admin.api.listing-type-data') }}';
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // The type for this listing — fixed, cannot be changed.
    var currentType = '{{ $typeVal }}';

    // Map of type labels
    var typeLabels = {
        hotel:      'Hotel Details',
        home:       'Home / Apartment Details',
        tour:       'Tour Details',
        activity:   'Activity Details',
        guide:      'Guide Details',
        restaurant: 'Restaurant Details'
    };

    // Existing detail field values loaded from the DB (passed from PHP).
    // On a failed re-submission old() takes priority, so we merge them:
    // PHP detail values are the baseline; old() overrides them field-by-field.
    var dbDetailValues = @json($detailValues);
    var oldInput       = @json(old());

    // Build the effective detail values: old() wins if it has a 'detail' key,
    // otherwise fall back to the DB values.
    var detailValues = (oldInput && oldInput['detail'])
        ? oldInput['detail']
        : dbDetailValues;

    // Existing amenity and category IDs pre-selected for this listing.
    var selectedAmenityIds  = @json($listing->amenities->pluck('id'));
    var selectedCategoryIds = @json($listing->categories->pluck('id'));

    // If old() has amenities/categories (after a failed submit), prefer those.
    var amenityIds  = (oldInput && oldInput['amenities'])
        ? oldInput['amenities'].map(function (v) { return parseInt(v, 10); })
        : selectedAmenityIds.map(function (v) { return parseInt(v, 10); });

    var categoryIds = (oldInput && oldInput['categories'])
        ? oldInput['categories'].map(function (v) { return parseInt(v, 10); })
        : selectedCategoryIds.map(function (v) { return parseInt(v, 10); });

    // ---------------------------------------------------------------
    // Helpers — build individual form fields from schema descriptors
    // ---------------------------------------------------------------

    /**
     * Build a Bootstrap 5 form group for a single field descriptor.
     * Returns a jQuery element.
     */
    function buildField(field) {
        var $wrap = $('<div class="mb-3"></div>');

        if (field.type === 'checkbox') {
            var checkId   = 'field_' + field.name.replace(/[\[\]]+/g, '_');
            var isChecked = resolveDetailBool(field.name);
            $wrap.addClass('form-check').removeClass('mb-3').addClass('mb-2');
            $wrap.html(
                '<input class="form-check-input" type="checkbox"' +
                ' name="' + escHtml(field.name) + '"' +
                ' id="' + escHtml(checkId) + '"' +
                ' value="1"' +
                (isChecked ? ' checked' : '') +
                '>' +
                '<label class="form-check-label" for="' + escHtml(checkId) + '">' +
                escHtml(field.label) +
                '</label>'
            );
            return $wrap;
        }

        var fieldId  = 'field_' + field.name.replace(/[\[\]]+/g, '_');
        var required = field.required ? ' required' : '';
        var reqMark  = field.required ? ' <span class="text-danger">*</span>' : '';

        $wrap.append(
            '<label for="' + escHtml(fieldId) + '" class="form-label fw-semibold">' +
            escHtml(field.label) + reqMark +
            '</label>'
        );

        if (field.type === 'select') {
            var $sel  = $('<select class="form-select" id="' + escHtml(fieldId) + '" name="' + escHtml(field.name) + '"' + required + '></select>');
            var selVal = resolveDetailValue(field.name);
            $.each(field.options || [], function (i, opt) {
                var selected = (String(opt.value) === String(selVal)) ? ' selected' : '';
                $sel.append('<option value="' + escHtml(String(opt.value)) + '"' + selected + '>' + escHtml(opt.label) + '</option>');
            });
            $wrap.append($sel);

        } else if (field.type === 'pills') {
            // Generic pill checkboxes — options and name come from the API field descriptor
            var pillColor = field.color || 'secondary';
            var pillTagStr = resolveDetailTagValue(field.name);
            var selectedPills;
            if (pillTagStr) {
                selectedPills = pillTagStr.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
            } else {
                var pillMatch = field.name.match(/\[(\w+)\]\[\]$/);
                var pillKey = pillMatch ? pillMatch[1] : null;
                selectedPills = (pillKey && Array.isArray(detailValues[pillKey])) ? detailValues[pillKey] : [];
            }
            var $pillWrap = $('<div class="d-flex flex-wrap gap-1"></div>');
            $.each(field.options || [], function (i, opt) {
                var uid = 'pill_' + fieldId + '_' + i;
                $pillWrap.append(
                    '<input type="checkbox" class="btn-check" autocomplete="off"' +
                    ' id="' + escHtml(uid) + '"' +
                    ' name="' + escHtml(field.name) + '"' +
                    ' value="' + escHtml(String(opt.value)) + '"' +
                    (selectedPills.indexOf(String(opt.value)) !== -1 ? ' checked' : '') + '>' +
                    '<label class="btn btn-outline-' + escHtml(pillColor) + ' btn-sm rounded-pill" for="' + escHtml(uid) + '">' +
                    escHtml(opt.label) + '</label>'
                );
            });
            $wrap.append($pillWrap);

        } else if (field.type === 'tags') {
            var placeholder = field.placeholder || '';
            var tagsId      = fieldId + '_tags_input';
            var tagsVal     = resolveDetailTagValue(field.name);
            $wrap.append(
                '<input type="text"' +
                ' class="form-control"' +
                ' id="' + escHtml(tagsId) + '"' +
                ' placeholder="' + escHtml(placeholder) + '"' +
                ' data-tags-name="' + escHtml(field.name) + '"' +
                ' value="' + escHtml(tagsVal) + '"' +
                (field.required ? ' required' : '') +
                '>' +
                '<div class="form-text">Separate multiple values with commas.</div>' +
                '<div class="tags-hidden-container"></div>'
            );

        } else {
            // text, number, url, time
            var min    = (field.min  !== undefined) ? ' min="'  + field.min  + '"' : '';
            var step   = (field.step !== undefined) ? ' step="' + field.step + '"' : '';
            var txtVal = resolveDetailValue(field.name);
            $wrap.append(
                '<input type="' + escHtml(field.type) + '"' +
                ' class="form-control"' +
                ' id="' + escHtml(fieldId) + '"' +
                ' name="' + escHtml(field.name) + '"' +
                ' value="' + escHtml(String(txtVal !== null && txtVal !== undefined ? txtVal : '')) + '"' +
                min + step + required +
                '>'
            );
        }

        return $wrap;
    }

    /**
     * Render the detail fields card from a fields array returned by the API.
     */
    function renderDetailFields(fields, typeVal) {
        var $body = $('#detail-fields-body').empty();
        $('#detail-fields-title').text(typeLabels[typeVal] || 'Type Details');

        if (!fields || fields.length === 0) {
            $('#detail-fields-card').hide();
            return;
        }

        // Render fields in a 2-column grid where possible
        var $row = $('<div class="row g-3"></div>');
        $.each(fields, function (i, f) {
            if (f.type === 'checkbox') return; // facilities handled by amenity groups
            var colClass = (f.type === 'text' || f.type === 'url' || f.type === 'tags') ? 'col-12' : 'col-md-6';
            $row.append($('<div></div>').addClass(colClass).append(buildField(f)));
        });
        if ($row.children().length > 0) {
            $body.append($row);
        }

        $('#detail-fields-card').show();
        attachTagsListeners();
    }

    /**
     * Render the amenities card from the grouped amenities returned by the API.
     * Pre-checks checkboxes that match amenityIds.
     */
    function renderAmenities(amenityGroups) {
        var $body = $('#amenities-body').empty();

        if (!amenityGroups || amenityGroups.length === 0) {
            $('#amenities-card').hide();
            return;
        }

        $.each(amenityGroups, function (i, group) {
            if (!group.items || group.items.length === 0) { return; }

            var $groupWrap = $('<div class="mb-3"></div>');
            if (group.group && group.group !== 'null') {
                $groupWrap.append(
                    '<div class="text-muted fw-semibold small mb-2 text-uppercase" ' +
                    'style="font-size:0.7rem;letter-spacing:0.8px;border-bottom:1px solid #e9ecef;padding-bottom:4px">' +
                    escHtml(group.group) +
                    '</div>'
                );
            }

            var $checkList = $('<div class="row g-1"></div>');
            $.each(group.items, function (j, amenity) {
                var isChecked = (amenityIds.indexOf(amenity.id) !== -1) ? ' checked' : '';
                var checkId   = 'amenity_' + amenity.id;
                $checkList.append(
                    '<div class="col-md-6">' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="checkbox"' +
                    ' name="amenities[]"' +
                    ' value="' + amenity.id + '"' +
                    ' id="' + escHtml(checkId) + '"' +
                    isChecked +
                    '>' +
                    '<label class="form-check-label" for="' + escHtml(checkId) + '" style="font-size:0.88rem">' +
                    (amenity.icon ? '<i class="ph ' + escHtml(amenity.icon) + ' me-1 text-muted"></i>' : '') +
                    escHtml(amenity.name_az || amenity.name_en || '') +
                    '</label>' +
                    '</div>' +
                    '</div>'
                );
            });

            $groupWrap.append($checkList);
            $body.append($groupWrap);
        });

        $('#amenities-card').show();
    }

    /**
     * Render the categories checkboxes from the categories returned by the API.
     * Pre-checks checkboxes that match categoryIds.
     */
    function renderCategories(categories) {
        var $body  = $('#categories-body').empty();
        var $empty = $('#categories-empty');

        if (!categories || categories.length === 0) {
            $empty.removeClass('d-none');
            $('#categories-card').show();
            return;
        }

        $empty.addClass('d-none');

        $.each(categories, function (i, cat) {
            var isChecked = (categoryIds.indexOf(cat.id) !== -1) ? ' checked' : '';
            var checkId   = 'cat_' + cat.id;
            $body.append(
                '<div class="form-check">' +
                '<input class="form-check-input" type="checkbox"' +
                ' name="categories[]"' +
                ' value="' + cat.id + '"' +
                ' id="' + escHtml(checkId) + '"' +
                isChecked +
                '>' +
                '<label class="form-check-label" for="' + escHtml(checkId) + '" style="font-size:0.88rem">' +
                escHtml(cat.name_az || cat.name_en || '') +
                '</label>' +
                '</div>'
            );
        });

        $('#categories-card').show();
    }

    // ---------------------------------------------------------------
    // Tags input: convert comma-separated text to hidden inputs on submit
    // ---------------------------------------------------------------

    function attachTagsListeners() {
        $('#admin-edit-form').off('submit.tags').on('submit.tags', function () {
            $(this).find('[data-tags-name]').each(function () {
                var name = $(this).data('tags-name');
                var vals = $(this).val().split(',').map(function (v) { return v.trim(); }).filter(Boolean);
                var $container = $(this).next('.tags-hidden-container').empty();
                $(this).removeAttr('name');
                $.each(vals, function (i, v) {
                    $container.append('<input type="hidden" name="' + escHtml(name) + '" value="' + escHtml(v) + '">');
                });
            });
        });
    }

    // ---------------------------------------------------------------
    // Helpers — resolve values from the merged detail object
    // ---------------------------------------------------------------

    /**
     * Resolve the value for a field whose name looks like:
     *   detail[stars]  ->  detailValues['stars']
     *   detail[check_in_time]  ->  detailValues['check_in_time']
     */
    function resolveDetailValue(fieldName) {
        var match = fieldName.match(/^(\w+)\[(\w+)\]$/);
        if (match) {
            var key = match[2];
            if (detailValues[key] !== undefined && detailValues[key] !== null) {
                return detailValues[key];
            }
        }
        return '';
    }

    function resolveDetailBool(fieldName) {
        var val = resolveDetailValue(fieldName);
        return val === true || val === 1 || val === '1' || val === 'on';
    }

    /**
     * Resolve the value for an array-style tags field whose name looks like:
     *   detail[languages][]  ->  detailValues['languages'] (array -> join with ', ')
     */
    function resolveDetailTagValue(fieldName) {
        var match = fieldName.match(/^(\w+)\[(\w+)\]\[\]$/);
        if (match) {
            var key = match[2];
            if (detailValues[key] && Array.isArray(detailValues[key])) {
                return detailValues[key].join(', ');
            }
            // Could also be stored as a string (edge case after old() round-trip)
            if (typeof detailValues[key] === 'string') {
                return detailValues[key];
            }
        }
        return '';
    }

    // ---------------------------------------------------------------
    // HTML escaping
    // ---------------------------------------------------------------

    function escHtml(str) {
        return String(str)
            .replace(/&/g,  '&amp;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;')
            .replace(/"/g,  '&quot;')
            .replace(/'/g,  '&#039;');
    }

    // ---------------------------------------------------------------
    // Itinerary — day management (Tour only)
    // ---------------------------------------------------------------

    var dayCounter = 0;

    /**
     * Build and append a single itinerary block row to #itinerary-days.
     */
    function addDayRow(titleVal, descriptionVal) {
        dayCounter++;
        var idx = dayCounter;

        var $row = $(
            '<div class="itinerary-day border rounded-3 p-3 bg-light position-relative" data-day-index="' + idx + '">' +
            '<div class="d-flex align-items-center justify-content-end mb-2">' +
            '<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-day-btn" title="Удалить блок">' +
            '<i class="ph ph-trash"></i>' +
            '</button>' +
            '</div>' +
            '<div class="mb-2">' +
            '<label class="form-label fw-semibold small mb-1">Заголовок</label>' +
            '<input type="text"' +
            ' class="form-control form-control-sm itinerary-title"' +
            ' placeholder="Заголовок блока, напр: День 1, Маршрут А, Программа..."' +
            ' value="' + escHtml(titleVal || '') + '">' +
            '</div>' +
            '<div class="mb-0">' +
            '<label class="form-label fw-semibold small mb-1">Описание</label>' +
            '<textarea class="form-control form-control-sm itinerary-description"' +
            ' rows="3"' +
            ' placeholder="Опишите активности и программу этого блока...">' + escHtml(descriptionVal || '') + '</textarea>' +
            '</div>' +
            '</div>'
        );

        $row.find('.remove-day-btn').on('click', function () {
            $row.remove();
            syncItineraryEmpty();
        });

        $('#itinerary-days').append($row);
        syncItineraryEmpty();
    }

    function syncItineraryEmpty() {
        var count = $('#itinerary-days .itinerary-day').length;
        if (count === 0) {
            $('#itinerary-empty').show();
        } else {
            $('#itinerary-empty').hide();
        }
    }

    // "Add Day" button
    $('#add-day-btn').on('click', function () {
        addDayRow('', '');
    });

    // Before the form submits, convert the itinerary day rows into
    // properly named hidden inputs: detail[itinerary][0][title], etc.
    $('#admin-edit-form').on('submit.itinerary', function () {
        $(this).find('input[name^="detail[itinerary]"]').remove();

        var $form = $(this);
        var seq   = 0;
        $('#itinerary-days .itinerary-day').each(function () {
            var title = $(this).find('.itinerary-title').val().trim();
            var desc  = $(this).find('.itinerary-description').val().trim();
            if (title !== '' || desc !== '') {
                $form.append(
                    '<input type="hidden" name="detail[itinerary][' + seq + '][title]"' +
                    ' value="' + escHtml(title) + '">'
                );
                $form.append(
                    '<input type="hidden" name="detail[itinerary][' + seq + '][description]"' +
                    ' value="' + escHtml(desc) + '">'
                );
                seq++;
            }
        });
    });

    // Populate itinerary rows from saved data (or old() after failed submit).
    // Priority: old() > DB values.
    (function populateItinerary() {
        @if($typeVal === 'tour')
        var itineraryData;

        // Check if old() has itinerary data (after a failed validation round-trip)
        if (oldInput && oldInput['detail'] && oldInput['detail']['itinerary']) {
            itineraryData = oldInput['detail']['itinerary'];
        } else if (Array.isArray(dbDetailValues['itinerary'])) {
            itineraryData = dbDetailValues['itinerary'];
        } else {
            itineraryData = [];
        }

        if (Array.isArray(itineraryData) && itineraryData.length > 0) {
            $.each(itineraryData, function (i, day) {
                var t = (typeof day === 'object' && day !== null) ? (day['title'] || '') : '';
                var d = (typeof day === 'object' && day !== null) ? (day['description'] || '') : '';
                addDayRow(t, d);
            });
        } else {
            syncItineraryEmpty();
        }
        @endif
    }());

    // ---------------------------------------------------------------
    // AJAX — fetch data for the current listing type
    // ---------------------------------------------------------------

    var currentRequest = null;

    function loadTypeData(typeVal) {
        if (currentRequest) {
            currentRequest.abort();
        }

        $('#type-loading').removeClass('d-none');

        currentRequest = $.ajax({
            url:     apiUrl,
            method:  'GET',
            data:    { type: typeVal },
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .done(function (data) {
            renderDetailFields(data.fields || [], typeVal);
            renderAmenities(data.amenities || []);
            renderCategories(data.categories || []);
        })
        .fail(function (xhr) {
            if (xhr.statusText === 'abort') { return; }
            $('#detail-fields-card').hide();
            $('#amenities-card').hide();
            $('#categories-card').hide();
            var msg = (xhr.responseJSON && xhr.responseJSON.error)
                ? xhr.responseJSON.error
                : 'Failed to load type data. Please refresh and try again.';
            $('#type-loading').html(
                '<div class="alert alert-warning alert-sm small py-2 mt-2">' +
                '<i class="ph ph-warning me-1"></i>' + escHtml(msg) +
                '</div>'
            ).removeClass('d-none');
            return;
        })
        .always(function (data, textStatus) {
            if (textStatus !== 'abort') {
                $('#type-loading').addClass('d-none');
            }
        });
    }

    // ---------------------------------------------------------------
    // On page load: immediately load type data for the current type
    // and pre-fill all fields with existing listing values.
    // ---------------------------------------------------------------
    loadTypeData(currentType);

    // =================================================================
    // MEDIA MANAGEMENT
    // =================================================================
    var listingId       = {{ $listing->id }};
    var uploadUrl       = '{{ route('admin.listings.media.store', $listing) }}';
    var youtubeUrl      = '{{ route('admin.listings.media.youtube', $listing) }}';
    var existingYoutubeId = {{ $existingYoutube?->id ?? 'null' }};

    // ── Drag & Drop / Click to upload ──────────────────────────────
    var $dropZone  = $('#photo-drop-zone');
    var $fileInput = $('#photo-file-input');

    $dropZone.on('click', function () { $fileInput.trigger('click'); });

    $dropZone.on('dragover dragenter', function (e) {
        e.preventDefault();
        $dropZone.addClass('dragover');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        $dropZone.removeClass('dragover');
        if (e.type === 'drop') {
            uploadFiles(e.originalEvent.dataTransfer.files);
        }
    });

    $fileInput.on('change', function () {
        uploadFiles(this.files);
        this.value = '';
    });

    function uploadFiles(files) {
        var arr = Array.from(files);
        if (!arr.length) return;

        var $progress = $('#photo-upload-progress');
        var $bar      = $('#photo-progress-bar');
        var $status   = $('#photo-upload-status');

        $progress.removeClass('d-none');
        var done = 0;

        arr.forEach(function (file) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('_token', csrfToken);

            $.ajax({
                url: uploadUrl,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    appendPhotoThumb(res.id, res.url, res.path);
                    $('#media-empty-msg').remove();
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.errors
                        ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                        : 'Upload failed.';
                    $status.text(msg).addClass('text-danger').removeClass('text-muted');
                },
                complete: function () {
                    done++;
                    $bar.css('width', Math.round(done / arr.length * 100) + '%');
                    $status.text(done + '/' + arr.length + ' uploaded').removeClass('text-danger').addClass('text-muted');
                    if (done === arr.length) {
                        setTimeout(function () { $progress.addClass('d-none'); $bar.css('width', '0%'); }, 1500);
                    }
                }
            });
        });
    }

    function appendPhotoThumb(id, url, path) {
        var coverUrl  = '{{ url('admin/listings/' . $listing->id . '/media') }}/' + id + '/cover';
        var deleteUrl = '{{ url('admin/listings/' . $listing->id . '/media') }}/' + id;
        var html =
            '<div class="col-4" id="media-item-' + id + '">' +
                '<div class="position-relative rounded overflow-hidden" style="aspect-ratio:1">' +
                    '<img src="' + url + '" alt="photo" class="w-100 h-100" style="object-fit:cover">' +
                    '<button type="button" class="btn btn-sm btn-warning position-absolute bottom-0 start-0 m-1 p-0 px-1 media-cover-btn"' +
                        ' style="font-size:0.6rem;line-height:1.4"' +
                        ' data-media-id="' + id + '"' +
                        ' data-cover-url="' + coverUrl + '"' +
                        ' title="Set as cover"><i class="ph ph-star"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 px-1 media-delete-btn"' +
                        ' style="font-size:0.65rem;line-height:1.4"' +
                        ' data-media-id="' + id + '"' +
                        ' data-delete-url="' + deleteUrl + '"' +
                        ' title="Delete"><i class="ph ph-x"></i></button>' +
                '</div>' +
            '</div>';
        $('#media-grid').append(html);
    }

    // ── Delete ──────────────────────────────────────────────────────
    $(document).on('click', '.media-delete-btn', function () {
        var $btn      = $(this);
        var mediaId   = $btn.data('media-id');
        var deleteUrl = $btn.data('delete-url');

        if (!confirm('Delete this media?')) return;

        $.ajax({
            url: deleteUrl,
            method: 'POST',
            data: { _token: csrfToken, _method: 'DELETE' },
            success: function () {
                $('#media-item-' + mediaId).remove();
                if ($('#media-grid .col-4').length === 0) {
                    $('#media-grid').append('<div class="col-12 text-center py-2 text-muted small" id="media-empty-msg"><i class="ph ph-image me-1"></i>No media yet</div>');
                }
            },
            error: function () { alert('Could not delete. Try again.'); }
        });
    });

    // ── Set Cover ───────────────────────────────────────────────────
    $(document).on('click', '.media-cover-btn', function () {
        var $btn     = $(this);
        var mediaId  = $btn.data('media-id');
        var coverUrl = $btn.data('cover-url');

        $.ajax({
            url: coverUrl,
            method: 'POST',
            data: { _token: csrfToken, _method: 'PATCH' },
            success: function () {
                // Remove cover badges everywhere
                $('#media-grid .badge').remove();
                // Show all cover buttons and reset icon
                $('#media-grid .media-cover-btn').each(function () {
                    $(this).show();
                    $(this).find('i').css('color', '');
                });
                // Remove ring from all
                $('#media-grid .ring-cover').removeClass('ring-cover');

                // Mark new cover
                var $thumb = $('#media-item-' + mediaId + ' .position-relative');
                $thumb.addClass('ring-cover');
                $btn.hide();
                $thumb.append('<span class="position-absolute bottom-0 start-0 m-1 badge bg-warning text-dark" style="font-size:0.55rem"><i class="ph ph-star me-1"></i>Cover</span>');
            },
            error: function () { alert('Could not set cover. Try again.'); }
        });
    });

    // ── YouTube ─────────────────────────────────────────────────────
    $('#youtube-add-btn').on('click', function () {
        var url = $('#youtube-url-input').val().trim();
        var $fb = $('#youtube-feedback');

        if (!url) {
            $fb.text('Please enter a YouTube URL.').removeClass('text-success d-none').addClass('text-danger');
            return;
        }

        function doAdd() {
            $.ajax({
                url: youtubeUrl,
                method: 'POST',
                data: { _token: csrfToken, youtube_url: url },
                success: function (res) {
                    existingYoutubeId = res.id;
                    $fb.text('Video saved.').removeClass('text-danger d-none').addClass('text-success');
                    appendYoutubeThumb(res.id, url);
                    $('#media-empty-msg').remove();
                    $('#youtube-add-btn').text('Update');
                    setTimeout(function () { $fb.addClass('d-none'); }, 2000);
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error :
                              (xhr.responseJSON && xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : 'Error adding video.');
                    $fb.text(msg).removeClass('text-success d-none').addClass('text-danger');
                }
            });
        }

        if (existingYoutubeId) {
            // Delete old first, then add new
            var deleteUrl = '{{ url('admin/listings/' . $listing->id . '/media') }}/' + existingYoutubeId;
            $.ajax({
                url: deleteUrl,
                method: 'DELETE',
                data: { _token: csrfToken },
                success: function () {
                    $('#media-item-' + existingYoutubeId).remove();
                    existingYoutubeId = null;
                    doAdd();
                },
                error: function () { doAdd(); }
            });
        } else {
            doAdd();
        }
    });

    function appendYoutubeThumb(id, url) {
        var deleteUrl = '{{ url('admin/listings/' . $listing->id . '/media') }}/' + id;
        var short = url.length > 30 ? url.substring(0, 30) + '...' : url;
        var html =
            '<div class="col-4" id="media-item-' + id + '">' +
                '<div class="position-relative rounded overflow-hidden bg-dark" style="aspect-ratio:1">' +
                    '<div class="d-flex align-items-center justify-content-center h-100">' +
                        '<i class="ph ph-youtube-logo text-danger" style="font-size:2rem"></i>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 px-1 media-delete-btn"' +
                        ' style="font-size:0.65rem;line-height:1.4"' +
                        ' data-media-id="' + id + '"' +
                        ' data-delete-url="' + deleteUrl + '"' +
                        ' title="Delete"><i class="ph ph-x"></i></button>' +
                '</div>' +
                '<div class="text-truncate small text-muted mt-1 px-1" style="font-size:0.65rem">' + short + '</div>' +
            '</div>';
        $('#media-grid').append(html);
    }

    // ---------------------------------------------------------------
    // Social Networks
    // ---------------------------------------------------------------

    var socialPlatforms = {
        instagram: { label: 'Instagram', placeholder: 'https://instagram.com/...' },
        facebook:  { label: 'Facebook',  placeholder: 'https://facebook.com/...' },
        tiktok:    { label: 'TikTok',    placeholder: 'https://tiktok.com/...'   },
        whatsapp:  { label: 'WhatsApp',  placeholder: '+994501234567'            }
    };

    var socialCounter = $('#social-links-list .social-link-row').length;

    function buildSocialRow(idx, platform, value) {
        platform = platform || 'instagram';
        var ph = (platform === 'whatsapp') ? '+994501234567' : 'https://...';
        var optionsHtml = '';
        $.each(socialPlatforms, function(key, cfg) {
            optionsHtml += '<option value="' + escHtml(key) + '"' + (key === platform ? ' selected' : '') + '>' + escHtml(cfg.label) + '</option>';
        });
        return $(
            '<div class="social-link-row d-flex gap-2 align-items-center">' +
            '<select class="form-select form-select-sm social-platform-select" name="social_links[' + idx + '][platform]" style="max-width:160px">' +
            optionsHtml +
            '</select>' +
            '<input type="text" class="form-control form-control-sm social-value-input"' +
            ' name="social_links[' + idx + '][value]"' +
            ' value="' + escHtml(value || '') + '"' +
            ' placeholder="' + escHtml(ph) + '">' +
            '<button type="button" class="btn btn-outline-danger btn-sm remove-social-btn flex-shrink-0" title="Remove">' +
            '<i class="ph ph-trash"></i></button>' +
            '</div>'
        );
    }

    function syncSocialEmpty() {
        var count = $('#social-links-list .social-link-row').length;
        $('#social-links-empty')[count === 0 ? 'removeClass' : 'addClass']('d-none');
    }

    $('#add-social-btn').on('click', function() {
        var $row = buildSocialRow(socialCounter++, 'instagram', '');
        $('#social-links-list').append($row);
        syncSocialEmpty();
    });

    $(document).on('click', '.remove-social-btn', function() {
        $(this).closest('.social-link-row').remove();
        syncSocialEmpty();
    });

    $(document).on('change', '.social-platform-select', function() {
        var platform = $(this).val();
        var $input = $(this).closest('.social-link-row').find('.social-value-input');
        $input.attr('placeholder', platform === 'whatsapp' ? '+994501234567' : 'https://...');
    });

    syncSocialEmpty();

});
</script>
@endsection
