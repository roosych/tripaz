@extends('layouts.app')

@section('title', 'Add New Listing — TripAz')

@can('create', App\Models\Listing::class)

@section('content')
<div class="container-xl py-4">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('listings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph ph-arrow-left"></i>
        </a>
        <div>
            <h1 class="h3 fw-bold mb-0"><i class="ph ph-plus-circle me-2 text-primary"></i>Add New Listing</h1>
            <p class="text-muted mb-0 small">Fill out all sections to publish your listing</p>
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

    <form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data" id="listing-form">
        @csrf

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

                            <div class="row g-3 mb-4" id="type-selector">
                                @foreach([
                                    'hotel'      => ['Hotel',      'ph-buildings',     'primary'],
                                    'home'       => ['Home',        'ph-house',        'info'],
                                    'tour'       => ['Tour',        'ph-map-trifold',          'success'],
                                    'activity'   => ['Activity',   'ph-lightning',    'warning'],
                                    'guide'      => ['Guide',       'ph-identification-card', 'secondary'],
                                    'restaurant' => ['Restaurant', 'ph-fork-knife',   'danger'],
                                ] as $typeVal => [$typeLabel, $typeIcon, $typeColor])
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <input type="radio" class="btn-check" name="type" id="type-{{ $typeVal }}"
                                               value="{{ $typeVal }}"
                                               {{ old('type') === $typeVal ? 'checked' : '' }}>
                                        <label class="btn btn-outline-{{ $typeColor }} w-100 py-3 d-flex flex-column align-items-center gap-1 type-btn"
                                               for="type-{{ $typeVal }}">
                                            <i class="ph {{ $typeIcon }} fs-3"></i>
                                            <span class="fw-semibold small">{{ $typeLabel }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <hr>
                            <h5 class="fw-semibold mb-3">Contact Information</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-envelope"></i></span>
                                        <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror"
                                               value="{{ old('contact_email') }}" placeholder="contact@example.com">
                                        @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                        <input type="tel" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                                               value="{{ old('contact_phone') }}" placeholder="+994 xx xxx xx xx">
                                        @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Website URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ph ph-globe"></i></span>
                                        <input type="url" name="website_url" class="form-control @error('website_url') is-invalid @enderror"
                                               value="{{ old('website_url') }}" placeholder="https://yoursite.com">
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

                            <ul class="nav nav-pills mb-3" id="localeTabs">
                                @foreach(['az' => 'Azerbaijani', 'ru' => 'Russian', 'en' => 'English'] as $loc => $locLabel)
                                    <li class="nav-item">
                                        <button class="nav-link {{ $loc === 'az' ? 'active' : '' }} fw-semibold"
                                                type="button"
                                                data-bs-toggle="pill"
                                                data-bs-target="#locale-{{ $loc }}">
                                            {{ $locLabel }}
                                            @if($loc === 'az')<span class="badge bg-danger ms-1">Required</span>@endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content">
                                @foreach(['az' => ['Azerbaijani', true], 'ru' => ['Russian', false], 'en' => ['English', false]] as $loc => [$locLabel, $required])
                                    <div class="tab-pane fade {{ $loc === 'az' ? 'show active' : '' }}" id="locale-{{ $loc }}">

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Title ({{ $locLabel }})
                                                @if($required)<span class="text-danger">*</span>@endif
                                            </label>
                                            <input type="text"
                                                   name="translations[{{ $loc }}][title]"
                                                   class="form-control @error("translations.{$loc}.title") is-invalid @enderror"
                                                   value="{{ old("translations.{$loc}.title") }}"
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
                                                      placeholder="Detailed description in {{ $locLabel }}"
                                                      {{ $required ? 'required' : '' }}>{{ old("translations.{$loc}.description") }}</textarea>
                                            @error("translations.{$loc}.description")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Address ({{ $locLabel }})</label>
                                            <input type="text"
                                                   name="translations[{{ $loc }}][address]"
                                                   class="form-control"
                                                   value="{{ old("translations.{$loc}.address") }}"
                                                   placeholder="Full address in {{ $locLabel }}">
                                        </div>

                                        <hr>
                                        <p class="text-muted small fw-semibold mb-2">SEO (optional)</p>

                                        <div class="mb-3">
                                            <label class="form-label">SEO Title ({{ $locLabel }})</label>
                                            <input type="text"
                                                   name="translations[{{ $loc }}][seo_title]"
                                                   class="form-control"
                                                   value="{{ old("translations.{$loc}.seo_title") }}"
                                                   placeholder="SEO page title (max 60 chars)"
                                                   maxlength="255">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">SEO Description ({{ $locLabel }})</label>
                                            <textarea name="translations[{{ $loc }}][seo_description]"
                                                      class="form-control"
                                                      rows="3"
                                                      placeholder="SEO meta description (max 160 chars)"
                                                      maxlength="500">{{ old("translations.{$loc}.seo_description") }}</textarea>
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
                                                    <option value="{{ $region->id }}" {{ old('location.region_id') == $region->id ? 'selected' : '' }}>
                                                        {{ $region->name('az') }} (All)
                                                    </option>
                                                    @foreach($region->children as $child)
                                                        <option value="{{ $child->id }}" {{ old('location.region_id') == $child->id ? 'selected' : '' }}>
                                                            &nbsp;&nbsp;{{ $child->name('az') }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                <option value="{{ $region->id }}" {{ old('location.region_id') == $region->id ? 'selected' : '' }}>
                                                    {{ $region->name('az') }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Postal Code</label>
                                    <input type="text" name="location[postal_code]" class="form-control"
                                           value="{{ old('location.postal_code') }}" placeholder="e.g. AZ1000">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Latitude</label>
                                    <input type="number" step="any" name="location[latitude]"
                                           id="lat-input"
                                           class="form-control @error('location.latitude') is-invalid @enderror"
                                           value="{{ old('location.latitude') }}"
                                           placeholder="e.g. 40.4093">
                                    @error('location.latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Longitude</label>
                                    <input type="number" step="any" name="location[longitude]"
                                           id="lng-input"
                                           class="form-control @error('location.longitude') is-invalid @enderror"
                                           value="{{ old('location.longitude') }}"
                                           placeholder="e.g. 49.8671">
                                    @error('location.longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <p class="text-muted small mb-2">
                                        <i class="ph ph-info me-1"></i>
                                        Click on the map to set the exact location, or enter coordinates manually above.
                                    </p>
                                    <div id="location-picker-map" style="height: 350px; border-radius: 8px; border: 1px solid #dee2e6;"></div>
                                </div>
                            </div>
                        </div>

                        {{-- ============================================== --}}
                        {{-- TAB 4: MEDIA --}}
                        {{-- ============================================== --}}
                        <div class="tab-pane fade" id="tab-media">
                            <h5 class="fw-semibold mb-3">Media</h5>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Featured Image</label>
                                <div class="upload-area" id="featured-upload-area" onclick="$('#featured-image-input').click()">
                                    <i class="ph ph-cloud-arrow-up display-4 text-muted"></i>
                                    <p class="mb-1 mt-2 fw-semibold">Click to upload featured image</p>
                                    <p class="text-muted small mb-0">JPG, PNG, WebP — max 5MB</p>
                                </div>
                                <input type="file" id="featured-image-input" name="featured_image"
                                       accept="image/*" class="d-none">
                                <div id="featured-preview" class="mt-2"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Gallery Images</label>
                                <div class="upload-area" id="gallery-upload-area" onclick="$('#gallery-images-input').click()">
                                    <i class="ph ph-images display-4 text-muted"></i>
                                    <p class="mb-1 mt-2 fw-semibold">Click to upload gallery images</p>
                                    <p class="text-muted small mb-0">Multiple files allowed — drag &amp; drop supported</p>
                                </div>
                                <input type="file" id="gallery-images-input" name="gallery[]"
                                       accept="image/*" multiple class="d-none">
                                <div id="gallery-preview" class="d-flex flex-wrap gap-2 mt-2" id="sortable-gallery"></div>
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
                            <h5 class="fw-semibold mb-3">Type-Specific Details</h5>
                            <p class="text-muted small mb-4">Select a listing type in the Basic tab first to see relevant fields.</p>

                            {{-- HOTEL details --}}
                            <div class="detail-section" id="detail-hotel" style="display:none">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Star Rating</label>
                                        <div class="d-flex gap-2" id="star-rating-picker">
                                            @for($s = 1; $s <= 5; $s++)
                                                <input type="radio" class="btn-check" name="detail[stars]"
                                                       id="star-{{ $s }}" value="{{ $s }}"
                                                       {{ old('detail.stars') == $s ? 'checked' : '' }}>
                                                <label class="btn btn-outline-warning px-3" for="star-{{ $s }}">
                                                    @for($i = 0; $i < $s; $i++)<i class="ph ph-star"></i>@endfor
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Total Rooms</label>
                                        <input type="number" name="detail[total_rooms]" class="form-control" min="1"
                                               value="{{ old('detail.total_rooms') }}" placeholder="e.g. 50">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Check-in Time</label>
                                        <input type="time" name="detail[check_in_time]" class="form-control"
                                               value="{{ old('detail.check_in_time', '14:00') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Check-out Time</label>
                                        <input type="time" name="detail[check_out_time]" class="form-control"
                                               value="{{ old('detail.check_out_time', '12:00') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- HOME details --}}
                            <div class="detail-section" id="detail-home" style="display:none">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Property Type <span class="text-danger">*</span></label>
                                        <select name="detail[property_type]" class="form-select" required>
                                            @foreach(['apartment' => 'Apartment', 'house' => 'House', 'villa' => 'Villa', 'studio' => 'Studio', 'cottage' => 'Cottage'] as $val => $label)
                                                <option value="{{ $val }}" {{ old('detail.property_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Bedrooms <span class="text-danger">*</span></label>
                                        <input type="number" name="detail[bedrooms]" class="form-control" min="1" value="{{ old('detail.bedrooms', 1) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Bathrooms <span class="text-danger">*</span></label>
                                        <input type="number" name="detail[bathrooms]" class="form-control" min="1" value="{{ old('detail.bathrooms', 1) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Max Guests <span class="text-danger">*</span></label>
                                        <input type="number" name="detail[max_guests]" class="form-control" min="1" value="{{ old('detail.max_guests', 2) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Total Area (m&sup2;)</label>
                                        <input type="number" step="0.01" name="detail[total_area]" class="form-control" min="1"
                                               value="{{ old('detail.total_area') }}" placeholder="e.g. 75">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Floor</label>
                                        <input type="number" name="detail[floor]" class="form-control"
                                               value="{{ old('detail.floor') }}" placeholder="e.g. 3">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">House Rules</label>
                                        <x-tag-input name="detail[house_rules][]" placeholder="Add rule (press Enter)" />
                                    </div>
                                </div>
                            </div>

                            {{-- TOUR details --}}
                            <div class="detail-section" id="detail-tour" style="display:none">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Duration (hours) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.5" name="detail[duration_hours]" class="form-control" min="0.5"
                                               value="{{ old('detail.duration_hours') }}" placeholder="e.g. 4" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Max Participants</label>
                                        <input type="number" name="detail[max_participants]" class="form-control" min="1"
                                               value="{{ old('detail.max_participants') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Meeting Point</label>
                                        <input type="text" name="detail[meeting_point]" class="form-control"
                                               value="{{ old('detail.meeting_point') }}" placeholder="e.g. Fountain Square">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Includes</label>
                                        <x-tag-input name="detail[includes][]" placeholder="Add item (press Enter)" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Excludes</label>
                                        <x-tag-input name="detail[excludes][]" placeholder="Add item (press Enter)" />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Itinerary</label>
                                        <div id="itinerary-builder"></div>
                                        <button type="button" class="btn btn-outline-success btn-sm mt-2" id="add-day-btn">
                                            <i class="ph ph-plus-circle me-1"></i>Add Day
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- ACTIVITY details --}}
                            <div class="detail-section" id="detail-activity" style="display:none">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Duration (minutes) <span class="text-danger">*</span></label>
                                        <input type="number" name="detail[duration_minutes]" class="form-control" min="15" step="5"
                                               value="{{ old('detail.duration_minutes') }}" placeholder="e.g. 120" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Activity Type</label>
                                        <input type="text" name="detail[activity_type]" class="form-control"
                                               value="{{ old('detail.activity_type') }}" placeholder="e.g. Hiking, Rafting">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Max Participants</label>
                                        <input type="number" name="detail[max_participants]" class="form-control" min="1"
                                               value="{{ old('detail.max_participants') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- GUIDE details --}}
                            <div class="detail-section" id="detail-guide" style="display:none">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Languages <span class="text-danger">*</span></label>
                                        <select name="detail[languages][]" class="form-select" multiple size="5" required>
                                            @foreach(['azerbaijani' => 'Azerbaijani', 'russian' => 'Russian', 'english' => 'English', 'turkish' => 'Turkish', 'german' => 'German', 'french' => 'French', 'arabic' => 'Arabic', 'persian' => 'Persian', 'chinese' => 'Chinese', 'spanish' => 'Spanish'] as $val => $label)
                                                <option value="{{ $val }}" {{ in_array($val, (array) old('detail.languages', [])) ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Years of Experience</label>
                                        <input type="number" name="detail[experience_years]" class="form-control" min="0"
                                               value="{{ old('detail.experience_years') }}" placeholder="e.g. 5">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Certifications</label>
                                        <x-tag-input name="detail[certifications][]" placeholder="Add certification (press Enter)" />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Extended Bio</label>
                                        <textarea name="detail[bio_extra]" class="form-control" rows="5"
                                                  placeholder="Additional bio information...">{{ old('detail.bio_extra') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- RESTAURANT details --}}
                            <div class="detail-section" id="detail-restaurant" style="display:none">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Cuisine Types <span class="text-danger">*</span></label>
                                        <x-tag-input name="detail[cuisine_types][]" placeholder="Add cuisine type (press Enter)" required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Price Range <span class="text-danger">*</span></label>
                                        <div class="btn-group w-100" role="group">
                                            @foreach(['budget' => '₼', 'mid' => '₼₼', 'upscale' => '₼₼₼', 'fine_dining' => '₼₼₼₼'] as $val => $label)
                                                <input type="radio" class="btn-check" name="detail[price_range]"
                                                       id="rest-pr-{{ $val }}" value="{{ $val }}"
                                                       {{ old('detail.price_range', 'mid') === $val ? 'checked' : '' }}>
                                                <label class="btn btn-outline-success" for="rest-pr-{{ $val }}">{{ $label }}</label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Seating Capacity</label>
                                        <input type="number" name="detail[seating_capacity]" class="form-control" min="1"
                                               value="{{ old('detail.seating_capacity') }}" placeholder="e.g. 80">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Options</label>
                                        <div class="d-flex gap-4">
                                            @foreach(['has_outdoor' => ['ph-tree', 'Outdoor Seating'], 'has_delivery' => ['ph-bicycle', 'Delivery'], 'has_takeaway' => ['ph-shopping-bag', 'Takeaway']] as $field => [$icon, $label])
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                           name="detail[{{ $field }}]" id="{{ $field }}-form"
                                                           value="1" {{ old("detail.{$field}") ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $field }}-form">
                                                        <i class="ph {{ $icon }} me-1"></i>{{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Opening hours --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Opening Hours</label>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Day</th>
                                                        <th>Closed</th>
                                                        <th>Open</th>
                                                        <th>Close</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                        <tr>
                                                            <td class="align-middle">{{ ucfirst($day) }}</td>
                                                            <td class="align-middle">
                                                                <input type="checkbox" class="form-check-input"
                                                                       name="detail[opening_hours][{{ $day }}][closed]"
                                                                       value="1"
                                                                       {{ old("detail.opening_hours.{$day}.closed") ? 'checked' : '' }}>
                                                            </td>
                                                            <td>
                                                                <input type="time" class="form-control form-control-sm"
                                                                       name="detail[opening_hours][{{ $day }}][open]"
                                                                       value="{{ old("detail.opening_hours.{$day}.open", '09:00') }}">
                                                            </td>
                                                            <td>
                                                                <input type="time" class="form-control form-control-sm"
                                                                       name="detail[opening_hours][{{ $day }}][close]"
                                                                       value="{{ old("detail.opening_hours.{$day}.close", '22:00') }}">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Menu URL</label>
                                        <input type="url" name="detail[menu_url]" class="form-control"
                                               value="{{ old('detail.menu_url') }}" placeholder="https://...">
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- ============================================== --}}
                        {{-- TAB 6: CATEGORIES & AMENITIES --}}
                        {{-- ============================================== --}}
                        <div class="tab-pane fade" id="tab-cats">
                            <h5 class="fw-semibold mb-3">Categories</h5>

                            @if($categories->isEmpty())
                                <p class="text-muted small">No categories available.</p>
                            @else
                                <div class="row g-2 mb-4">
                                    @foreach($categories as $cat)
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="categories[]" value="{{ $cat->id }}"
                                                       id="cat-{{ $cat->id }}"
                                                       {{ in_array($cat->id, (array) old('categories', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="cat-{{ $cat->id }}">
                                                    @if($cat->icon)<i class="{{ $cat->icon }} me-1"></i>@endif
                                                    {{ $cat->name('az') }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <h5 class="fw-semibold mb-3">Amenities</h5>
                            @if($amenities->isEmpty())
                                <p class="text-muted small">No amenities available.</p>
                            @else
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
                                                           id="form-amenity-{{ $amenity->id }}"
                                                           {{ in_array($amenity->id, (array) old('amenities', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="form-amenity-{{ $amenity->id }}">
                                                        @if($amenity->icon)<i class="{{ $amenity->icon }} me-1"></i>@endif
                                                        {{ $amenity->name('az') }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endif
                        </div>

                    </div>{{-- end card-body --}}
                </div>{{-- end tab-content --}}
            </div>

            {{-- Sidebar: submit --}}
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 1rem;">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold mb-3">Publish Listing</h6>
                        <p class="small text-muted mb-3">Your listing will be reviewed before going live.</p>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-semibold">
                                <i class="ph ph-paper-plane-tilt me-2"></i>Submit for Review
                            </button>
                            <a href="{{ route('listings.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>

                        <hr>
                        <div id="form-progress-indicator">
                            <p class="small fw-semibold mb-2">Progress</p>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex justify-content-between small">
                                    <span>Type selected</span>
                                    <span id="prog-type" class="text-muted">—</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span>Title (AZ)</span>
                                    <span id="prog-title" class="text-muted">—</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span>Region</span>
                                    <span id="prog-region" class="text-muted">—</span>
                                </div>
                            </div>
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

    // --------------------------------------------------------
    // Type selector — show/hide detail sections
    // --------------------------------------------------------
    $('input[name="type"]').on('change', function () {
        var type = $(this).val();
        $('.detail-section').hide();
        if (type) {
            $('#detail-' + type).show();
        }
        updateProgress();
    });

    // Restore state on load
    var savedType = $('input[name="type"]:checked').val();
    if (savedType) {
        $('.detail-section').hide();
        $('#detail-' + savedType).show();
    }

    // --------------------------------------------------------
    // Progress indicators
    // --------------------------------------------------------
    function updateProgress() {
        var type = $('input[name="type"]:checked').val();
        $('#prog-type').html(type ? '<span class="text-success"><i class="ph ph-check"></i></span>' : '<span class="text-muted">—</span>');

        var title = $('input[name="translations[az][title]"]').val();
        $('#prog-title').html(title ? '<span class="text-success"><i class="ph ph-check"></i></span>' : '<span class="text-muted">—</span>');

        var region = $('select[name="location[region_id]"]').val();
        $('#prog-region').html(region ? '<span class="text-success"><i class="ph ph-check"></i></span>' : '<span class="text-muted">—</span>');
    }

    $('input[name="translations[az][title]"]').on('input', updateProgress);
    $('select[name="location[region_id]"]').on('change', updateProgress);

    // --------------------------------------------------------
    // Location picker map
    // --------------------------------------------------------
    if ($('#location-picker-map').length) {
        var pickerMap = L.map('location-picker-map').setView([40.4093, 49.8671], 7);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(pickerMap);

        var marker = null;

        // If coords already set (old input), place marker
        var initLat = parseFloat($('#lat-input').val());
        var initLng = parseFloat($('#lng-input').val());
        if (!isNaN(initLat) && !isNaN(initLng)) {
            marker = L.marker([initLat, initLng]).addTo(pickerMap);
            pickerMap.setView([initLat, initLng], 12);
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

        // Sync map if inputs change manually
        $('#lat-input, #lng-input').on('change', function () {
            var lat = parseFloat($('#lat-input').val());
            var lng = parseFloat($('#lng-input').val());
            if (!isNaN(lat) && !isNaN(lng)) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(pickerMap);
                }
                pickerMap.setView([lat, lng], 12);
            }
        });
    }

    // --------------------------------------------------------
    // Featured image preview
    // --------------------------------------------------------
    $('#featured-image-input').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#featured-preview').html(
                '<div class="position-relative d-inline-block">' +
                '<img src="' + e.target.result + '" class="rounded-2 border" style="max-height:160px; max-width:100%;">' +
                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 px-1" id="remove-featured">' +
                '<i class="ph ph-x"></i></button></div>'
            );
        };
        reader.readAsDataURL(file);
    });

    $(document).on('click', '#remove-featured', function () {
        $('#featured-image-input').val('');
        $('#featured-preview').html('');
    });

    // --------------------------------------------------------
    // Gallery drag-and-drop
    // --------------------------------------------------------
    var uploadArea = document.getElementById('gallery-upload-area');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            $(uploadArea).addClass('drag-over');
        });
        uploadArea.addEventListener('dragleave', function () {
            $(uploadArea).removeClass('drag-over');
        });
        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            $(uploadArea).removeClass('drag-over');
            handleGalleryFiles(e.dataTransfer.files);
        });
    }

    $('#gallery-images-input').on('change', function () {
        handleGalleryFiles(this.files);
    });

    function handleGalleryFiles(files) {
        $.each(files, function (i, file) {
            if (!file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                var thumb = $('<div class="position-relative" style="width:80px;height:80px;">' +
                    '<img src="' + e.target.result + '" class="rounded-2 border w-100 h-100" style="object-fit:cover;">' +
                    '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 px-1 remove-gallery-thumb" style="font-size:0.7rem">' +
                    '<i class="ph ph-x"></i></button>' +
                    '</div>');
                $('#gallery-preview').append(thumb);
            };
            reader.readAsDataURL(file);
        });
    }

    $(document).on('click', '.remove-gallery-thumb', function () {
        $(this).closest('[style]').remove();
    });

    // Make gallery sortable
    if ($('#gallery-preview').length) {
        $('#gallery-preview').sortable({
            placeholder: 'ui-state-highlight',
            forcePlaceholderSize: true,
        });
    }

    // --------------------------------------------------------
    // YouTube embed preview
    // --------------------------------------------------------
    $('#youtube-url-input').on('input', function () {
        var url = $(this).val().trim();
        var match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/);
        if (match) {
            var ytId = match[1];
            $('#youtube-preview').html(
                '<div class="ratio ratio-16x9 mt-2 rounded-2 overflow-hidden" style="max-width: 400px;">' +
                '<iframe src="https://www.youtube.com/embed/' + ytId + '" allowfullscreen></iframe>' +
                '</div>'
            );
        } else {
            $('#youtube-preview').html('');
        }
    });

    // --------------------------------------------------------
    // Itinerary day builder
    // --------------------------------------------------------
    var dayCount = 0;

    $('#add-day-btn').on('click', function () {
        dayCount++;
        var dayHtml = '<div class="itinerary-day" data-day="' + dayCount + '">' +
            '<div class="d-flex align-items-center gap-2 mb-2">' +
            '<span class="badge bg-success">Day ' + dayCount + '</span>' +
            '<input type="text" name="detail[itinerary][' + (dayCount-1) + '][title]" class="form-control form-control-sm" placeholder="Day title">' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-day"><i class="ph ph-trash"></i></button>' +
            '</div>' +
            '<textarea name="detail[itinerary][' + (dayCount-1) + '][description]" class="form-control form-control-sm" rows="3" placeholder="Day description..."></textarea>' +
            '</div>';
        $('#itinerary-builder').append(dayHtml);
    });

    $(document).on('click', '.remove-day', function () {
        $(this).closest('.itinerary-day').remove();
        // Re-number days
        dayCount = 0;
        $('.itinerary-day').each(function () {
            dayCount++;
            $(this).find('.badge').text('Day ' + dayCount);
            $(this).find('input').attr('name', 'detail[itinerary][' + (dayCount-1) + '][title]');
            $(this).find('textarea').attr('name', 'detail[itinerary][' + (dayCount-1) + '][description]');
        });
    });

    // Initial progress update
    updateProgress();
});
</script>
@endsection

@else
    <div class="container py-5 text-center">
        <i class="ph ph-lock display-1 text-muted"></i>
        <h3 class="mt-3">Access Denied</h3>
        <p class="text-muted">You do not have permission to create listings.</p>
        <a href="{{ route('listings.index') }}" class="btn btn-primary">Browse Listings</a>
    </div>
@endcan
