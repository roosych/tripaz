@extends('layouts.app')

@section('title', 'Edit Listing — TripAz')

@can('update', $listing)

@php
    $locale = app()->getLocale();
    $detail = $listing->detail ? $listing->detail()->first() : null;
    $type   = $listing->type->value;
@endphp

@section('content')
<div class="container-xl py-4">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('listings.show', $listing->slug) }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph ph-arrow-left"></i>
        </a>
        <div>
            <h1 class="h3 fw-bold mb-0"><i class="ph ph-pencil-simple me-2 text-warning"></i>Edit Listing</h1>
            <p class="text-muted mb-0 small">{{ $listing->translation($locale)?->title ?? $listing->slug }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <h6 class="fw-semibold"><i class="ph ph-warning-circle me-1"></i>Please fix the following errors:</h6>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('listings.update', $listing->slug) }}"
          enctype="multipart/form-data"
          id="listing-edit-form">
        @csrf
        @method('PUT')

        {{-- Type is fixed for edit (cannot change type) --}}
        <input type="hidden" name="type" value="{{ $type }}">

        <div class="row g-4">
            {{-- Main form column --}}
            <div class="col-lg-9">

                {{-- -------------------------------------------------------- --}}
                {{-- TABS --}}
                {{-- -------------------------------------------------------- --}}
                <ul class="nav nav-tabs nav-pills mb-0 border-bottom" id="formTabs">
                    <li class="nav-item"><button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button"><i class="ph ph-info me-1"></i>Basic</button></li>
                    <li class="nav-item"><button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-translations" type="button"><i class="ph ph-translate me-1"></i>Translations</button></li>
                    <li class="nav-item"><button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-location" type="button"><i class="ph ph-map-pin me-1"></i>Location</button></li>
                    <li class="nav-item"><button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-media" type="button"><i class="ph ph-images me-1"></i>Media</button></li>
                    <li class="nav-item"><button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-details" type="button"><i class="ph ph-list-checks me-1"></i>Details</button></li>
                    <li class="nav-item"><button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-cats" type="button"><i class="ph ph-tag me-1"></i>Categories</button></li>
                </ul>

                <div class="tab-content card border-0 shadow-sm rounded-bottom-3 rounded-top-0 form-tab-content">
                    <div class="card-body p-4">

                        {{-- ============================================== --}}
                        {{-- TAB 1: BASIC INFO --}}
                        {{-- ============================================== --}}
                        <div class="tab-pane fade show active" id="tab-basic">
                            <h5 class="fw-semibold mb-3">Listing Type</h5>

                            <div class="mb-4">
                                @php
                                    $typeLabels = ['hotel' => ['Hotel', 'ph-buildings', 'primary'], 'home' => ['Home', 'ph-house', 'info'], 'tour' => ['Tour', 'ph-map-trifold', 'success'], 'activity' => ['Activity', 'ph-lightning', 'warning'], 'guide' => ['Guide', 'ph-identification-card', 'secondary'], 'restaurant' => ['Restaurant', 'ph-fork-knife', 'danger']];
                                    [$tLabel, $tIcon, $tColor] = $typeLabels[$type] ?? [ucfirst($type), 'ph-grid-four', 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $tColor }} px-3 py-2 fs-6">
                                    <i class="ph {{ $tIcon }} me-2"></i>{{ $tLabel }}
                                </span>
                                <p class="text-muted small mt-2 mb-0">The listing type cannot be changed after creation.</p>
                            </div>

                            <hr>
                            <h5 class="fw-semibold mb-3">Contact Information</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-envelope"></i></span>
                                        <input type="email" name="contact_email"
                                               class="form-control @error('contact_email') is-invalid @enderror"
                                               value="{{ old('contact_email', $listing->contact_email) }}"
                                               placeholder="contact@example.com">
                                        @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                        <input type="tel" name="contact_phone"
                                               class="form-control @error('contact_phone') is-invalid @enderror"
                                               value="{{ old('contact_phone', $listing->contact_phone) }}"
                                               placeholder="+994 xx xxx xx xx">
                                        @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Website URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-globe"></i></span>
                                        <input type="url" name="website_url"
                                               class="form-control @error('website_url') is-invalid @enderror"
                                               value="{{ old('website_url', $listing->website_url) }}"
                                               placeholder="https://yoursite.com">
                                        @error('website_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ============================================== --}}
                        {{-- TAB 2: TRANSLATIONS --}}
                        {{-- ============================================== --}}
                        <div class="tab-pane fade" id="tab-translations">
                            <h5 class="fw-semibold mb-3">Multilingual Content</h5>

                            <ul class="nav nav-pills mb-3">
                                @foreach(['az' => 'Azerbaijani', 'ru' => 'Russian', 'en' => 'English'] as $loc => $locLabel)
                                    <li class="nav-item">
                                        <button class="nav-link {{ $loc === 'az' ? 'active' : '' }} fw-semibold"
                                                type="button" data-bs-toggle="pill"
                                                data-bs-target="#edit-locale-{{ $loc }}">
                                            {{ $locLabel }}
                                            @if($loc === 'az')<span class="badge bg-danger ms-1">Required</span>@endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content">
                                @foreach(['az' => ['Azerbaijani', true], 'ru' => ['Russian', false], 'en' => ['English', false]] as $loc => [$locLabel, $required])
                                    @php
                                        $trans = $listing->translations->firstWhere('locale', \App\Enums\Locale::from($loc));
                                    @endphp
                                    <div class="tab-pane fade {{ $loc === 'az' ? 'show active' : '' }}" id="edit-locale-{{ $loc }}">

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Title ({{ $locLabel }})
                                                @if($required)<span class="text-danger">*</span>@endif
                                            </label>
                                            <input type="text"
                                                   name="translations[{{ $loc }}][title]"
                                                   class="form-control @error("translations.{$loc}.title") is-invalid @enderror"
                                                   value="{{ old("translations.{$loc}.title", $trans?->title) }}"
                                                   placeholder="Listing title in {{ $locLabel }}"
                                                   {{ $required ? 'required' : '' }}>
                                            @error("translations.{$loc}.title")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Description ({{ $locLabel }})
                                                @if($required)<span class="text-danger">*</span>@endif
                                            </label>
                                            <textarea name="translations[{{ $loc }}][description]"
                                                      class="form-control @error("translations.{$loc}.description") is-invalid @enderror"
                                                      rows="6"
                                                      {{ $required ? 'required' : '' }}>{{ old("translations.{$loc}.description", $trans?->description) }}</textarea>
                                            @error("translations.{$loc}.description")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Address ({{ $locLabel }})</label>
                                            <input type="text"
                                                   name="translations[{{ $loc }}][address]"
                                                   class="form-control"
                                                   value="{{ old("translations.{$loc}.address", $trans?->address) }}">
                                        </div>

                                        <hr>
                                        <p class="text-muted small fw-semibold mb-2">SEO</p>

                                        <div class="mb-3">
                                            <label class="form-label">SEO Title</label>
                                            <input type="text"
                                                   name="translations[{{ $loc }}][seo_title]"
                                                   class="form-control"
                                                   value="{{ old("translations.{$loc}.seo_title", $trans?->seo_title) }}"
                                                   maxlength="255">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">SEO Description</label>
                                            <textarea name="translations[{{ $loc }}][seo_description]"
                                                      class="form-control"
                                                      rows="3"
                                                      maxlength="500">{{ old("translations.{$loc}.seo_description", $trans?->seo_description) }}</textarea>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ============================================== --}}
                        {{-- TAB 3: LOCATION --}}
                        {{-- ============================================== --}}
                        <div class="tab-pane fade" id="tab-location">
                            <h5 class="fw-semibold mb-3">Location</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Region / City</label>
                                    <select name="location[region_id]" class="form-select">
                                        <option value="">Select region...</option>
                                        @foreach($regions as $region)
                                            @if($region->children->isNotEmpty())
                                                <optgroup label="{{ $region->name('az') }}">
                                                    <option value="{{ $region->id }}" {{ (old('location.region_id', $listing->location?->region_id) == $region->id) ? 'selected' : '' }}>
                                                        {{ $region->name('az') }} (All)
                                                    </option>
                                                    @foreach($region->children as $child)
                                                        <option value="{{ $child->id }}" {{ (old('location.region_id', $listing->location?->region_id) == $child->id) ? 'selected' : '' }}>
                                                            &nbsp;&nbsp;{{ $child->name('az') }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                <option value="{{ $region->id }}" {{ (old('location.region_id', $listing->location?->region_id) == $region->id) ? 'selected' : '' }}>
                                                    {{ $region->name('az') }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Postal Code</label>
                                    <input type="text" name="location[postal_code]" class="form-control"
                                           value="{{ old('location.postal_code', $listing->location?->postal_code) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Latitude</label>
                                    <input type="number" step="any" name="location[latitude]"
                                           id="lat-input"
                                           class="form-control @error('location.latitude') is-invalid @enderror"
                                           value="{{ old('location.latitude', $listing->location?->latitude) }}">
                                    @error('location.latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Longitude</label>
                                    <input type="number" step="any" name="location[longitude]"
                                           id="lng-input"
                                           class="form-control @error('location.longitude') is-invalid @enderror"
                                           value="{{ old('location.longitude', $listing->location?->longitude) }}">
                                    @error('location.longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <p class="text-muted small mb-2">Click on the map to update the location.</p>
                                    <div id="location-picker-map" style="height: 350px; border-radius: 8px; border: 1px solid #dee2e6;"></div>
                                </div>
                            </div>
                        </div>

                        {{-- ============================================== --}}
                        {{-- TAB 4: MEDIA --}}
                        {{-- ============================================== --}}
                        <div class="tab-pane fade" id="tab-media">
                            <h5 class="fw-semibold mb-3">Media</h5>

                            {{-- Current featured image --}}
                            @if($listing->featured_image)
                                <div class="mb-3">
                                    <p class="fw-semibold mb-2">Current Featured Image</p>
                                    <img src="{{ asset('storage/' . $listing->featured_image) }}"
                                         alt="Featured" class="rounded-2 border"
                                         style="max-height: 180px;">
                                </div>
                            @endif

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Replace Featured Image</label>
                                <div class="upload-area" onclick="$('#featured-image-input').click()">
                                    <i class="ph ph-cloud-arrow-up display-4 text-muted"></i>
                                    <p class="mb-0 mt-2 text-muted small">Click to upload new featured image</p>
                                </div>
                                <input type="file" id="featured-image-input" name="featured_image"
                                       accept="image/*" class="d-none">
                                <div id="featured-preview" class="mt-2"></div>
                            </div>

                            {{-- Current gallery --}}
                            @if($listing->media->isNotEmpty())
                                <div class="mb-3">
                                    <p class="fw-semibold mb-2">Current Gallery</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($listing->media as $media)
                                            <div class="position-relative">
                                                <img src="{{ $media->url('thumb') ?: $media->url() }}"
                                                     alt="Media"
                                                     class="rounded-2 border"
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Add Gallery Images</label>
                                <div class="upload-area" id="gallery-upload-area" onclick="$('#gallery-images-input').click()">
                                    <i class="ph ph-images display-4 text-muted"></i>
                                    <p class="mb-0 mt-2 text-muted small">Click to add more images</p>
                                </div>
                                <input type="file" id="gallery-images-input" name="gallery[]"
                                       accept="image/*" multiple class="d-none">
                                <div id="gallery-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">YouTube Video URL</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ph ph-youtube-logo text-danger"></i></span>
                                    <input type="url" name="youtube_url" id="youtube-url-input"
                                           class="form-control"
                                           value="{{ old('youtube_url') }}"
                                           placeholder="https://www.youtube.com/watch?v=...">
                                </div>
                                <div id="youtube-preview" class="mt-2"></div>
                            </div>
                        </div>

                        {{-- ============================================== --}}
                        {{-- TAB 5: TYPE-SPECIFIC DETAILS --}}
                        {{-- ============================================== --}}
                        <div class="tab-pane fade" id="tab-details">
                            <h5 class="fw-semibold mb-3">Details — {{ ucfirst($type) }}</h5>

                            @if($type === 'hotel')
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Star Rating</label>
                                        <div class="d-flex gap-2">
                                            @for($s = 1; $s <= 5; $s++)
                                                <input type="radio" class="btn-check" name="detail[stars]"
                                                       id="edit-star-{{ $s }}" value="{{ $s }}"
                                                       {{ old('detail.stars', $detail?->stars) == $s ? 'checked' : '' }}>
                                                <label class="btn btn-outline-warning px-3" for="edit-star-{{ $s }}">
                                                    @for($i = 0; $i < $s; $i++)<i class="ph ph-star"></i>@endfor
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Total Rooms</label>
                                        <input type="number" name="detail[total_rooms]" class="form-control" min="1"
                                               value="{{ old('detail.total_rooms', $detail?->total_rooms) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Check-in</label>
                                        <input type="time" name="detail[check_in_time]" class="form-control"
                                               value="{{ old('detail.check_in_time', $detail?->check_in_time) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Check-out</label>
                                        <input type="time" name="detail[check_out_time]" class="form-control"
                                               value="{{ old('detail.check_out_time', $detail?->check_out_time) }}">
                                    </div>
                                </div>

                            @elseif($type === 'home')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Property Type</label>
                                        <select name="detail[property_type]" class="form-select">
                                            @foreach(['apartment' => 'Apartment', 'house' => 'House', 'villa' => 'Villa', 'studio' => 'Studio', 'cottage' => 'Cottage'] as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ old('detail.property_type', $detail?->property_type instanceof \App\Enums\PropertyType ? $detail->property_type->value : $detail?->property_type) === $val ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Bedrooms</label>
                                        <input type="number" name="detail[bedrooms]" class="form-control" min="1"
                                               value="{{ old('detail.bedrooms', $detail?->bedrooms) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Bathrooms</label>
                                        <input type="number" name="detail[bathrooms]" class="form-control" min="1"
                                               value="{{ old('detail.bathrooms', $detail?->bathrooms) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Max Guests</label>
                                        <input type="number" name="detail[max_guests]" class="form-control" min="1"
                                               value="{{ old('detail.max_guests', $detail?->max_guests) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Total Area (m&sup2;)</label>
                                        <input type="number" step="0.01" name="detail[total_area]" class="form-control" min="1"
                                               value="{{ old('detail.total_area', $detail?->total_area) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Floor</label>
                                        <input type="number" name="detail[floor]" class="form-control"
                                               value="{{ old('detail.floor', $detail?->floor) }}">
                                    </div>
                                </div>

                            @elseif($type === 'tour')
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Duration (hours)</label>
                                        <input type="number" step="0.5" name="detail[duration_hours]" class="form-control" min="0.5"
                                               value="{{ old('detail.duration_hours', $detail?->duration_hours) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Max Participants</label>
                                        <input type="number" name="detail[max_participants]" class="form-control" min="1"
                                               value="{{ old('detail.max_participants', $detail?->max_participants) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Meeting Point</label>
                                        <input type="text" name="detail[meeting_point]" class="form-control"
                                               value="{{ old('detail.meeting_point', $detail?->meeting_point) }}">
                                    </div>
                                </div>

                            @elseif($type === 'activity')
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Duration (minutes)</label>
                                        <input type="number" name="detail[duration_minutes]" class="form-control" min="15"
                                               value="{{ old('detail.duration_minutes', $detail?->duration_minutes) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Activity Type</label>
                                        <input type="text" name="detail[activity_type]" class="form-control"
                                               value="{{ old('detail.activity_type', $detail?->activity_type) }}">
                                    </div>
                                </div>

                            @elseif($type === 'guide')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Languages</label>
                                        <select name="detail[languages][]" class="form-select" multiple size="5">
                                            @php $currLangs = (array)($detail?->languages ?? []); @endphp
                                            @foreach(['azerbaijani' => 'Azerbaijani', 'russian' => 'Russian', 'english' => 'English', 'turkish' => 'Turkish', 'german' => 'German', 'french' => 'French', 'arabic' => 'Arabic', 'persian' => 'Persian', 'chinese' => 'Chinese', 'spanish' => 'Spanish'] as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ in_array($val, old('detail.languages', $currLangs)) ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Years of Experience</label>
                                        <input type="number" name="detail[experience_years]" class="form-control" min="0"
                                               value="{{ old('detail.experience_years', $detail?->experience_years) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Extended Bio</label>
                                        <textarea name="detail[bio_extra]" class="form-control" rows="5">{{ old('detail.bio_extra', $detail?->bio_extra) }}</textarea>
                                    </div>
                                </div>

                            @elseif($type === 'restaurant')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Price Range</label>
                                        <div class="btn-group w-100" role="group">
                                            @php $currPr = $detail?->price_range instanceof \App\Enums\PriceRange ? $detail->price_range->value : (string)$detail?->price_range; @endphp
                                            @foreach(['budget' => '₼', 'mid' => '₼₼', 'upscale' => '₼₼₼', 'fine_dining' => '₼₼₼₼'] as $val => $label)
                                                <input type="radio" class="btn-check" name="detail[price_range]"
                                                       id="edit-pr-{{ $val }}" value="{{ $val }}"
                                                       {{ old('detail.price_range', $currPr) === $val ? 'checked' : '' }}>
                                                <label class="btn btn-outline-success" for="edit-pr-{{ $val }}">{{ $label }}</label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex gap-4">
                                            @foreach(['has_outdoor' => ['ph-tree', 'Outdoor'], 'has_delivery' => ['ph-bicycle', 'Delivery'], 'has_takeaway' => ['ph-shopping-bag', 'Takeaway']] as $field => [$icon, $label])
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                           name="detail[{{ $field }}]" id="edit-{{ $field }}"
                                                           value="1" {{ old("detail.{$field}", $detail?->{$field}) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="edit-{{ $field }}">
                                                        <i class="ph {{ $icon }} me-1"></i>{{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Menu URL</label>
                                        <input type="url" name="detail[menu_url]" class="form-control"
                                               value="{{ old('detail.menu_url', $detail?->menu_url) }}">
                                    </div>
                                </div>
                            @endif

                        </div>

                        {{-- ============================================== --}}
                        {{-- TAB 6: CATEGORIES & AMENITIES --}}
                        {{-- ============================================== --}}
                        <div class="tab-pane fade" id="tab-cats">
                            <h5 class="fw-semibold mb-3">Categories</h5>
                            @php $selectedCats = $listing->categories->pluck('id')->toArray(); @endphp
                            <div class="row g-2 mb-4">
                                @foreach($categories as $cat)
                                    <div class="col-sm-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="categories[]" value="{{ $cat->id }}"
                                                   id="edit-cat-{{ $cat->id }}"
                                                   {{ in_array($cat->id, old('categories', $selectedCats)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="edit-cat-{{ $cat->id }}">
                                                @if($cat->icon)<i class="{{ $cat->icon }} me-1"></i>@endif
                                                {{ $cat->name('az') }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <h5 class="fw-semibold mb-3">Amenities</h5>
                            @php $selectedAmenities = $listing->amenities->pluck('id')->toArray(); @endphp
                            @foreach($amenities as $group => $groupAmenities)
                                @if($group)
                                    <h6 class="text-muted small fw-semibold text-uppercase mt-3 mb-2">{{ $group }}</h6>
                                @endif
                                <div class="row g-2">
                                    @foreach($groupAmenities as $amenity)
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="amenities[]" value="{{ $amenity->id }}"
                                                       id="edit-amenity-{{ $amenity->id }}"
                                                       {{ in_array($amenity->id, old('amenities', $selectedAmenities)) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="edit-amenity-{{ $amenity->id }}">
                                                    @if($amenity->icon)<i class="{{ $amenity->icon }} me-1"></i>@endif
                                                    {{ $amenity->name('az') }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                    </div>{{-- end card-body --}}
                </div>{{-- end tab-content --}}
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 1rem;">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold mb-1">Status</h6>
                        <p class="small text-muted mb-3">
                            Current:
                            <span class="badge bg-{{ $listing->status->value === 'published' ? 'success' : 'warning' }}">
                                {{ ucfirst($listing->status->value) }}
                            </span>
                        </p>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning py-2 fw-semibold">
                                <i class="ph ph-check-circle me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('listings.show', $listing->slug) }}"
                               class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    // Location picker map
    if ($('#location-picker-map').length) {
        var initLat = parseFloat($('#lat-input').val()) || 40.4093;
        var initLng = parseFloat($('#lng-input').val()) || 49.8671;
        var zoom    = ($('#lat-input').val()) ? 12 : 7;

        var pickerMap = L.map('location-picker-map').setView([initLat, initLng], zoom);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(pickerMap);

        var marker = null;
        if ($('#lat-input').val() && $('#lng-input').val()) {
            marker = L.marker([initLat, initLng]).addTo(pickerMap);
        }

        pickerMap.on('click', function (e) {
            var lat = e.latlng.lat.toFixed(7);
            var lng = e.latlng.lng.toFixed(7);
            $('#lat-input').val(lat);
            $('#lng-input').val(lng);
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(pickerMap);
            }
        });
    }

    // Featured image preview
    $('#featured-image-input').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#featured-preview').html('<img src="' + e.target.result + '" class="rounded-2 border mt-2" style="max-height:160px;">');
        };
        reader.readAsDataURL(file);
    });

    // Gallery preview
    $('#gallery-images-input').on('change', function () {
        $.each(this.files, function (i, file) {
            if (!file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#gallery-preview').append(
                    '<img src="' + e.target.result + '" class="rounded-2 border" style="width:80px;height:80px;object-fit:cover;">'
                );
            };
            reader.readAsDataURL(file);
        });
    });

    // YouTube embed preview
    $('#youtube-url-input').on('input', function () {
        var url = $(this).val().trim();
        var match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/);
        if (match) {
            $('#youtube-preview').html(
                '<div class="ratio ratio-16x9 mt-2 rounded-2 overflow-hidden" style="max-width: 400px;">' +
                '<iframe src="https://www.youtube.com/embed/' + match[1] + '" allowfullscreen></iframe>' +
                '</div>'
            );
        } else {
            $('#youtube-preview').html('');
        }
    });
});
</script>
@endsection

@else
    <div class="container py-5 text-center">
        <i class="ph ph-lock display-1 text-muted"></i>
        <h3 class="mt-3">Access Denied</h3>
        <p class="text-muted">You do not have permission to edit this listing.</p>
        <a href="{{ route('listings.index') }}" class="btn btn-primary">Browse Listings</a>
    </div>
@endcan
