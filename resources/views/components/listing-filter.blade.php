{{--
    Listing Filter Sidebar Component
    Props:
      $regions    — Collection of Region (with children)
      $amenities  — Collection grouped by group (only when type is selected)
      $categories — Collection of Category
      $type       — string|null current selected type
      $locale     — string
--}}
@props([
    'regions'     => collect(),
    'amenities'   => collect(),
    'categories'  => collect(),
    'type'        => null,
    'locale'      => 'az',
    'minBedrooms' => 1,
    'maxBedrooms' => 10,
    'minGuests'   => 1,
    'maxGuests'   => 20,
])

<div class="card border-0 rounded-3" style="box-shadow: var(--tripaz-shadow);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="ph ph-funnel me-2"></i>{{ __('listings.filters') }}</h6>
    </div>
    <div class="card-body p-3">
        <form method="GET" action="{{ route('listings.index') }}" id="filter-form">

            {{-- -------------------------------------------------------- --}}
            {{-- Type selector (pill tabs) --}}
            {{-- -------------------------------------------------------- --}}
            <div class="mb-4">
                <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.category') }}</label>
                <div class="d-flex flex-wrap gap-1" id="type-tabs">
                    <a href="{{ route('listings.index') }}"
                       class="btn btn-sm {{ !$type ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">
                        {{ __('listings.filter.all') }}
                    </a>
                    @foreach(['hotel' => ['hotels', 'ph-buildings', 'btn-primary'], 'home' => ['homes', 'ph-house', 'btn-info'], 'tour' => ['tours', 'ph-map-trifold', 'btn-success'], 'activity' => ['activities', 'ph-lightning', 'btn-warning'], 'guide' => ['guides', 'ph-identification-card', 'btn-secondary'], 'restaurant' => ['restaurants', 'ph-fork-knife', 'btn-danger']] as $t => [$typeKey, $icon, $btnClass])
                        <a href="{{ route('listings.index', array_merge(request()->except('type', 'page'), ['type' => $t])) }}"
                           class="btn btn-sm {{ $type === $t ? $btnClass : 'btn-outline-secondary' }} rounded-pill">
                            <i class="ph {{ $icon }} me-1"></i>{{ __('common.types.' . $typeKey) }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Hidden type input for form submission --}}
            @if($type)
                <input type="hidden" name="type" value="{{ $type }}">
            @endif

            {{-- -------------------------------------------------------- --}}
            {{-- Search / Autocomplete --}}
            {{-- -------------------------------------------------------- --}}
            <div class="mb-3">
                <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.search') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="ph ph-magnifying-glass"></i></span>
                    <input type="text"
                           id="listing-search"
                           name="search"
                           class="form-control"
                           placeholder="{{ __('listings.filter.search_placeholder') }}"
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- Region / City --}}
            {{-- -------------------------------------------------------- --}}
            <div class="mb-3">
                <label for="region-select" class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.region') }}</label>
                <select name="region" id="region-select" class="form-select form-select-sm">
                    <option value="">{{ __('listings.filter.all_destinations') }}</option>
                    @foreach($regions as $region)
                        @foreach($region->children as $child)
                            <option value="{{ $child->id }}" {{ request('region') == $child->id ? 'selected' : '' }}>
                                {{ $child->name($locale) }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- Type-specific filter panels --}}
            {{-- -------------------------------------------------------- --}}

            {{-- HOTEL filters --}}
            <div class="type-filters" id="filters-hotel" style="{{ $type === 'hotel' ? '' : 'display:none' }}">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.min_stars') }}</label>
                    <div class="d-flex flex-column gap-1">
                        @for($s = 5; $s >= 1; $s--)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stars"
                                       id="stars-{{ $s }}" value="{{ $s }}"
                                       {{ request('stars') == $s ? 'checked' : '' }}>
                                <label class="form-check-label" for="stars-{{ $s }}">
                                    @for($i = 0; $i < $s; $i++)<i class="ph ph-star text-warning"></i>@endfor
                                    {{ $s }}&nbsp;{{ $s > 1 ? __('listings.filter.stars_up') : __('listings.filter.star') }}
                                </label>
                            </div>
                        @endfor
                    </div>
                </div>

                @if($amenities->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.amenities') }}</label>
                        @foreach($amenities as $group => $groupAmenities)
                            @php
                                $groupArr = $groupAmenities->values();
                                $total    = $groupArr->count();
                                $visible  = 4;
                                $hidden   = max(0, $total - $visible);
                                $groupSlug = 'hotel-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($group ?: 'ungrouped'));
                            @endphp
                            @if($group)
                                <p class="small fw-semibold text-secondary mb-1 mt-2">{{ ucfirst($group) }}</p>
                            @endif
                            @foreach($groupArr->take($visible) as $amenity)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="amenities[]" value="{{ $amenity->id }}"
                                           id="amenity-{{ $amenity->id }}"
                                           {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                        @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                        {{ $amenity->name($locale) }}
                                    </label>
                                </div>
                            @endforeach
                            @if($hidden > 0)
                                <div class="amenity-overflow d-none" id="overflow-{{ $groupSlug }}">
                                    @foreach($groupArr->skip($visible) as $amenity)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="amenities[]" value="{{ $amenity->id }}"
                                                   id="amenity-{{ $amenity->id }}"
                                                   {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                                @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                                {{ $amenity->name($locale) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button"
                                        class="btn btn-link btn-sm p-0 mt-1 text-decoration-none amenity-toggle"
                                        data-target="overflow-{{ $groupSlug }}"
                                        data-more="{{ $hidden }}">
                                    <i class="ph ph-plus-circle me-1"></i>+{{ $hidden }} more
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- HOME filters --}}
            <div class="type-filters" id="filters-home" style="{{ $type === 'home' ? '' : 'display:none' }}">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.property_type') }}</label>
                    <select name="property_type" class="form-select form-select-sm">
                        <option value="">Any type</option>
                        @foreach(['apartment' => 'Apartment', 'house' => 'House', 'villa' => 'Villa', 'studio' => 'Studio', 'cottage' => 'Cottage'] as $val => $label)
                            <option value="{{ $val }}" {{ request('property_type') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="bedrooms-input" class="form-label small text-muted fw-semibold text-uppercase">
                        Min Bedrooms: <span id="bedrooms-val">{{ request('bedrooms', $minBedrooms) }}</span>
                    </label>
                    <input type="range" class="form-range" id="bedrooms-input" name="bedrooms"
                           min="{{ $minBedrooms }}" max="{{ $maxBedrooms }}" step="1"
                           value="{{ max($minBedrooms, min(request('bedrooms', $minBedrooms), $maxBedrooms)) }}"
                           oninput="document.getElementById('bedrooms-val').textContent = this.value">
                    <div class="d-flex justify-content-between small text-muted"><span>{{ $minBedrooms }}</span><span>{{ $maxBedrooms }}</span></div>
                </div>

                <div class="mb-3">
                    <label for="guests-input" class="form-label small text-muted fw-semibold text-uppercase">
                        Min Guests: <span id="guests-val">{{ request('guests', $minGuests) }}</span>
                    </label>
                    <input type="range" class="form-range" id="guests-input" name="guests"
                           min="{{ $minGuests }}" max="{{ $maxGuests }}" step="1"
                           value="{{ max($minGuests, min(request('guests', $minGuests), $maxGuests)) }}"
                           oninput="document.getElementById('guests-val').textContent = this.value">
                    <div class="d-flex justify-content-between small text-muted"><span>{{ $minGuests }}</span><span>{{ $maxGuests }}</span></div>
                </div>

                @if($amenities->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.amenities') }}</label>
                        @foreach($amenities as $group => $groupAmenities)
                            @php
                                $groupArr  = $groupAmenities->values();
                                $total     = $groupArr->count();
                                $visible   = 4;
                                $hidden    = max(0, $total - $visible);
                                $groupSlug = 'home-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($group ?: 'ungrouped'));
                            @endphp
                            @if($group)
                                <p class="small fw-semibold text-secondary mb-1 mt-2">{{ ucfirst($group) }}</p>
                            @endif
                            @foreach($groupArr->take($visible) as $amenity)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="amenities[]" value="{{ $amenity->id }}"
                                           id="amenity-{{ $amenity->id }}"
                                           {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                        @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                        {{ $amenity->name($locale) }}
                                    </label>
                                </div>
                            @endforeach
                            @if($hidden > 0)
                                <div class="amenity-overflow d-none" id="overflow-{{ $groupSlug }}">
                                    @foreach($groupArr->skip($visible) as $amenity)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="amenities[]" value="{{ $amenity->id }}"
                                                   id="amenity-{{ $amenity->id }}"
                                                   {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                                @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                                {{ $amenity->name($locale) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button"
                                        class="btn btn-link btn-sm p-0 mt-1 text-decoration-none amenity-toggle"
                                        data-target="overflow-{{ $groupSlug }}"
                                        data-more="{{ $hidden }}">
                                    <i class="ph ph-plus-circle me-1"></i>+{{ $hidden }} more
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- TOUR filters --}}
            <div class="type-filters" id="filters-tour" style="{{ $type === 'tour' ? '' : 'display:none' }}">
                @php
                    $tourCategories = $categories->filter(fn($c) => !$c->listing_type || $c->listing_type->value === 'tour');
                @endphp
                @if($tourCategories->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('Categories') }}</label>
                        <div class="d-flex flex-column gap-1">
                            @foreach($tourCategories as $cat)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="categories[]" value="{{ $cat->id }}"
                                           id="tour-cat-{{ $cat->id }}"
                                           {{ in_array($cat->id, (array) request('categories', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="tour-cat-{{ $cat->id }}">
                                        @if($cat->icon)<i class="{{ $cat->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                        {{ $cat->name($locale) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($amenities->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.amenities') }}</label>
                        @foreach($amenities as $group => $groupAmenities)
                            @php
                                $groupArr  = $groupAmenities->values();
                                $total     = $groupArr->count();
                                $visible   = 4;
                                $hidden    = max(0, $total - $visible);
                                $groupSlug = 'tour-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($group ?: 'ungrouped'));
                            @endphp
                            @if($group)
                                <p class="small fw-semibold text-secondary mb-1 mt-2">{{ ucfirst($group) }}</p>
                            @endif
                            @foreach($groupArr->take($visible) as $amenity)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="amenities[]" value="{{ $amenity->id }}"
                                           id="amenity-{{ $amenity->id }}"
                                           {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                        @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                        {{ $amenity->name($locale) }}
                                    </label>
                                </div>
                            @endforeach
                            @if($hidden > 0)
                                <div class="amenity-overflow d-none" id="overflow-{{ $groupSlug }}">
                                    @foreach($groupArr->skip($visible) as $amenity)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="amenities[]" value="{{ $amenity->id }}"
                                                   id="amenity-{{ $amenity->id }}"
                                                   {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                                @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                                {{ $amenity->name($locale) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button"
                                        class="btn btn-link btn-sm p-0 mt-1 text-decoration-none amenity-toggle"
                                        data-target="overflow-{{ $groupSlug }}"
                                        data-more="{{ $hidden }}">
                                    <i class="ph ph-plus-circle me-1"></i>+{{ $hidden }} more
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ACTIVITY filters --}}
            <div class="type-filters" id="filters-activity" style="{{ $type === 'activity' ? '' : 'display:none' }}">
                @if($amenities->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.amenities') }}</label>
                        @foreach($amenities as $group => $groupAmenities)
                            @php
                                $groupArr  = $groupAmenities->values();
                                $total     = $groupArr->count();
                                $visible   = 4;
                                $hidden    = max(0, $total - $visible);
                                $groupSlug = 'activity-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($group ?: 'ungrouped'));
                            @endphp
                            @if($group)
                                <p class="small fw-semibold text-secondary mb-1 mt-2">{{ ucfirst($group) }}</p>
                            @endif
                            @foreach($groupArr->take($visible) as $amenity)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="amenities[]" value="{{ $amenity->id }}"
                                           id="amenity-{{ $amenity->id }}"
                                           {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                        @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                        {{ $amenity->name($locale) }}
                                    </label>
                                </div>
                            @endforeach
                            @if($hidden > 0)
                                <div class="amenity-overflow d-none" id="overflow-{{ $groupSlug }}">
                                    @foreach($groupArr->skip($visible) as $amenity)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="amenities[]" value="{{ $amenity->id }}"
                                                   id="amenity-{{ $amenity->id }}"
                                                   {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                                @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                                {{ $amenity->name($locale) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button"
                                        class="btn btn-link btn-sm p-0 mt-1 text-decoration-none amenity-toggle"
                                        data-target="overflow-{{ $groupSlug }}"
                                        data-more="{{ $hidden }}">
                                    <i class="ph ph-plus-circle me-1"></i>+{{ $hidden }} more
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- RESTAURANT filters --}}
            <div class="type-filters" id="filters-restaurant" style="{{ $type === 'restaurant' ? '' : 'display:none' }}">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.price_range') }}</label>
                    <div class="btn-group w-100" role="group">
                        @foreach(['budget' => '₼', 'mid' => '₼₼', 'upscale' => '₼₼₼', 'fine_dining' => '₼₼₼₼'] as $val => $label)
                            <input type="radio" class="btn-check" name="price_range"
                                   id="price-{{ $val }}" value="{{ $val }}"
                                   {{ request('price_range') === $val ? 'checked' : '' }}>
                            <label class="btn btn-outline-success btn-sm" for="price-{{ $val }}">{{ $label }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted fw-semibold text-uppercase">Cuisine</label>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach([
                            'azerbaijani' => 'Azerbaijani',
                            'turkish'     => 'Turkish',
                            'russian'     => 'Russian',
                            'italian'     => 'Italian',
                            'asian'       => 'Asian',
                            'european'    => 'European',
                            'chinese'     => 'Chinese',
                            'japanese'    => 'Japanese',
                            'indian'      => 'Indian',
                            'fast_food'   => 'Fast Food',
                        ] as $val => $label)
                            <input type="checkbox" class="btn-check" autocomplete="off"
                                   name="cuisine[]" id="cuisine-{{ $val }}" value="{{ $val }}"
                                   {{ in_array($val, (array) request('cuisine', [])) ? 'checked' : '' }}>
                            <label class="btn btn-outline-danger btn-sm rounded-pill" for="cuisine-{{ $val }}">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($amenities->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.amenities') }}</label>
                        @foreach($amenities as $group => $groupAmenities)
                            @php
                                $groupArr  = $groupAmenities->values();
                                $total     = $groupArr->count();
                                $visible   = 4;
                                $hidden    = max(0, $total - $visible);
                                $groupSlug = 'restaurant-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($group ?: 'ungrouped'));
                            @endphp
                            @if($group)
                                <p class="small fw-semibold text-secondary mb-1 mt-2">{{ ucfirst($group) }}</p>
                            @endif
                            @foreach($groupArr->take($visible) as $amenity)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="amenities[]" value="{{ $amenity->id }}"
                                           id="amenity-{{ $amenity->id }}"
                                           {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                        @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                        {{ $amenity->name($locale) }}
                                    </label>
                                </div>
                            @endforeach
                            @if($hidden > 0)
                                <div class="amenity-overflow d-none" id="overflow-{{ $groupSlug }}">
                                    @foreach($groupArr->skip($visible) as $amenity)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="amenities[]" value="{{ $amenity->id }}"
                                                   id="amenity-{{ $amenity->id }}"
                                                   {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                                @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                                {{ $amenity->name($locale) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button"
                                        class="btn btn-link btn-sm p-0 mt-1 text-decoration-none amenity-toggle"
                                        data-target="overflow-{{ $groupSlug }}"
                                        data-more="{{ $hidden }}">
                                    <i class="ph ph-plus-circle me-1"></i>+{{ $hidden }} more
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- GUIDE filters --}}
            <div class="type-filters" id="filters-guide" style="{{ $type === 'guide' ? '' : 'display:none' }}">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-semibold text-uppercase">Languages</label>
                    <select name="languages[]" class="form-select form-select-sm" multiple>
                        @foreach(['azerbaijani' => 'Azerbaijani', 'russian' => 'Russian', 'english' => 'English', 'turkish' => 'Turkish', 'german' => 'German', 'french' => 'French', 'arabic' => 'Arabic', 'chinese' => 'Chinese'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ in_array($val, (array) request('languages', [])) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($amenities->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold text-uppercase">{{ __('listings.filter.amenities') }}</label>
                        @foreach($amenities as $group => $groupAmenities)
                            @php
                                $groupArr  = $groupAmenities->values();
                                $total     = $groupArr->count();
                                $visible   = 4;
                                $hidden    = max(0, $total - $visible);
                                $groupSlug = 'guide-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($group ?: 'ungrouped'));
                            @endphp
                            @if($group)
                                <p class="small fw-semibold text-secondary mb-1 mt-2">{{ ucfirst($group) }}</p>
                            @endif
                            @foreach($groupArr->take($visible) as $amenity)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="amenities[]" value="{{ $amenity->id }}"
                                           id="amenity-{{ $amenity->id }}"
                                           {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                        @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                        {{ $amenity->name($locale) }}
                                    </label>
                                </div>
                            @endforeach
                            @if($hidden > 0)
                                <div class="amenity-overflow d-none" id="overflow-{{ $groupSlug }}">
                                    @foreach($groupArr->skip($visible) as $amenity)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="amenities[]" value="{{ $amenity->id }}"
                                                   id="amenity-{{ $amenity->id }}"
                                                   {{ in_array($amenity->id, (array) request('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="amenity-{{ $amenity->id }}">
                                                @if($amenity->icon)<i class="{{ $amenity->icon }} me-1 text-muted" style="font-size:0.75rem"></i>@endif
                                                {{ $amenity->name($locale) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button"
                                        class="btn btn-link btn-sm p-0 mt-1 text-decoration-none amenity-toggle"
                                        data-target="overflow-{{ $groupSlug }}"
                                        data-more="{{ $hidden }}">
                                    <i class="ph ph-plus-circle me-1"></i>+{{ $hidden }} more
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- Submit / Clear --}}
            {{-- -------------------------------------------------------- --}}
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="ph ph-funnel me-1"></i>{{ __('listings.filter.apply') }}
                </button>
                <a href="{{ route('listings.index', $type ? ['type' => $type] : []) }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="ph ph-x"></i> {{ __('listings.filter.reset') }}
                </a>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    /* Prevent select2 from overflowing the sticky sidebar */
    #filter-form .select2-container { width: 100% !important; }
    /* Shrink the select2 field to match Bootstrap sm size */
    #filter-form .select2-container--bootstrap-5 .select2-selection {
        min-height: 31px;
        font-size: .875rem;
        padding: .25rem .5rem;
    }
    #filter-form .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-top: 0;
        padding-bottom: 0;
    }
    #filter-form .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 31px;
    }
    /* Remove focus borders/outlines on all filter inputs */
    #filter-form .form-control:focus,
    #filter-form .form-select:focus,
    #filter-form .form-check-input:focus,
    #filter-form .form-range:focus {
        box-shadow: none;
        border-color: #dee2e6;
        outline: none;
    }
    #filter-form .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    #filter-form .select2-container--bootstrap-5.select2-container--open .select2-selection,
    #filter-form .select2-container--bootstrap-5.select2-container--open .select2-selection--single,
    #filter-form .select2-container--bootstrap-5.select2-container--open .select2-selection--multiple {
        box-shadow: none !important;
        border-color: #dee2e6 !important;
        outline: none;
    }
    /* Apply Filters button — no focus/active ring */
    #filter-form button[type="submit"]:focus,
    #filter-form button[type="submit"]:active,
    #filter-form button[type="submit"]:focus-visible {
        box-shadow: none !important;
        outline: none;
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

{{-- Filter JS (sliders, autocomplete) --}}
<script>
$(function () {

    // --------------------------------------------------------
    // Select2 for all <select> elements inside the filter form
    // --------------------------------------------------------
    $('#filter-form select').each(function () {
        var $sel   = $(this);
        var isMulti = $sel.prop('multiple');
        $sel.select2({
            theme:       'bootstrap-5',
            width:       '100%',
            allowClear:  true,
            placeholder: isMulti ? 'Select languages...' : $sel.find('option[value=""]').text() || 'Select...',
            dropdownParent: $sel.closest('.card'),
        });
    });


    // Bedrooms and Guests use native <input type="range"> — no init needed.

    // --------------------------------------------------------
    // Search autocomplete
    // --------------------------------------------------------
    if ($('#listing-search').length) {
        $('#listing-search').autocomplete({
            minLength: 2,
            delay: 300,
            source: function (request, response) {
                $.getJSON('/api/listings/search', { q: request.term }, function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.title,
                            value: item.title,
                            slug:  item.slug,
                        };
                    }));
                }).fail(function () {
                    response([]);
                });
            },
            select: function (e, ui) {
                window.location.href = '/listings/' + ui.item.slug;
                return false;
            }
        });
    }

    // --------------------------------------------------------
    // Amenity group expand / collapse
    // --------------------------------------------------------
    $(document).on('click', '.amenity-toggle', function () {
        var targetId  = $(this).data('target');
        var moreCount = $(this).data('more');
        var $overflow = $('#' + targetId);
        if ($overflow.hasClass('d-none')) {
            $overflow.removeClass('d-none');
            $(this).html('<i class="ph ph-minus-circle me-1"></i>Show less');
        } else {
            $overflow.addClass('d-none');
            $(this).html('<i class="ph ph-plus-circle me-1"></i>+' + moreCount + ' more');
        }
    });

    // Auto-expand groups that have checked hidden amenities (e.g. from a prior filter request)
    $('.amenity-overflow').each(function () {
        if ($(this).find('input[type=checkbox]:checked').length > 0) {
            $(this).removeClass('d-none');
            var $btn = $('[data-target="' + $(this).attr('id') + '"]');
            $btn.html('<i class="ph ph-minus-circle me-1"></i>Show less');
        }
    });
});
</script>
