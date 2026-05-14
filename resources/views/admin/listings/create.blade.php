@extends('layouts.admin')

@section('title', 'Create Listing')

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#listing-map { height: 200px; border-radius: 8px; z-index: 0; }
#location_latitude::placeholder, #location_longitude::placeholder { color: #bcc0c4; }
</style>
@endsection

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center gap-3">
    <a href="{{ route('admin.listings.index') }}" class="btn btn-sm btn-outline-secondary" title="Back to listings">
        <i class="ph ph-arrow-left"></i>
    </a>
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-plus-circle me-2 text-danger"></i>Create Listing
        </h1>
        <p class="text-muted mb-0 small mt-1">Create a listing and assign it to a host user</p>
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
{{-- FORM                                                              --}}
{{-- ================================================================ --}}
<form method="POST" action="{{ route('admin.listings.store') }}" id="admin-create-form">
    @csrf

    <div class="row g-4">

        {{-- -------------------------------------------------------- --}}
        {{-- LEFT: Main form fields                                   --}}
        {{-- -------------------------------------------------------- --}}
        <div class="col-lg-8">

            {{-- Listing Type --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-tag me-2 text-primary"></i>Listing Type
                        <span class="text-danger ms-1">*</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2" id="type-selector">
                        @foreach([
                            'hotel'      => ['label' => 'Hotel',      'icon' => 'ph-buildings',    'color' => '#0d6efd'],
                            'home'       => ['label' => 'Home',       'icon' => 'ph-house',        'color' => '#0dcaf0'],
                            'tour'       => ['label' => 'Tour',       'icon' => 'ph-map-trifold',          'color' => '#198754'],
                            'activity'   => ['label' => 'Activity',   'icon' => 'ph-lightning',   'color' => '#ffc107'],
                            'guide'      => ['label' => 'Guide',      'icon' => 'ph-identification-card', 'color' => '#6c757d'],
                            'restaurant' => ['label' => 'Restaurant', 'icon' => 'ph-fork-knife',   'color' => '#dc3545'],
                        ] as $typeVal => $typeConfig)
                            <div class="col-6 col-sm-4 col-md-2">
                                <input type="radio"
                                       class="btn-check"
                                       name="type"
                                       id="type_{{ $typeVal }}"
                                       value="{{ $typeVal }}"
                                       {{ old('type') === $typeVal ? 'checked' : '' }}
                                       required>
                                <label class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-3 gap-1"
                                       for="type_{{ $typeVal }}"
                                       style="font-size:0.8rem">
                                    <i class="ph {{ $typeConfig['icon'] }} fs-4" style="color:{{ $typeConfig['color'] }}"></i>
                                    {{ $typeConfig['label'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    {{-- Prompt shown when no type is selected yet --}}
                    <div id="type-select-hint" class="mt-3 small text-muted {{ old('type') ? 'd-none' : '' }}">
                        <i class="ph ph-arrow-up me-1"></i>Select a listing type to load relevant fields, categories, and amenities.
                    </div>
                </div>
            </div>

            {{-- Titles (Translations) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-translate me-2 text-primary"></i>Titles &amp; Description
                    </h6>
                </div>
                <div class="card-body">

                    @foreach(['az' => 'Azerbaijani', 'ru' => 'Russian', 'en' => 'English'] as $locale => $localeName)
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
                                       value="{{ old("translations.{$locale}.title") }}"
                                       {{ $locale === 'az' ? 'required' : '' }}>
                                @error("translations.{$locale}.title")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    @foreach(['az' => 'Azerbaijani', 'ru' => 'Russian', 'en' => 'English'] as $locale => $localeName)
                        <div class="mb-3">
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
                                          rows="4"
                                          {{ $locale === 'az' ? 'required' : '' }}>{{ old("translations.{$locale}.description") }}</textarea>
                                @error("translations.{$locale}.description")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="{{ $loop->last ? 'mb-0' : 'mb-3' }}">
                            <label for="address_{{ $locale }}" class="form-label fw-semibold">
                                Address ({{ $localeName }})
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">
                                    {{ strtoupper($locale) }}
                                </span>
                                <input type="text"
                                       class="form-control @error("translations.{$locale}.address") is-invalid @enderror"
                                       id="address_{{ $locale }}"
                                       name="translations[{{ $locale }}][address]"
                                       value="{{ old("translations.{$locale}.address") }}"
                                       placeholder="Физический адрес объекта">
                                @error("translations.{$locale}.address")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- DYNAMIC: Type-Specific Detail Fields                     --}}
            {{-- Rendered via JS after type selection                     --}}
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

            {{-- -------------------------------------------------------- --}}
            {{-- TOUR ONLY: Itinerary                                     --}}
            {{-- Shown/hidden by JS based on the selected listing type    --}}
            {{-- -------------------------------------------------------- --}}
            <div id="itinerary-card" class="card border-0 shadow-sm mb-4" style="display:none;">
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

            {{-- -------------------------------------------------------- --}}
            {{-- DYNAMIC: Amenities                                        --}}
            {{-- Rendered via JS after type selection                     --}}
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
                                       value="{{ old('contact_email') }}"
                                       placeholder="contact@example.com">
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
                                       value="{{ old('contact_phone') }}"
                                       placeholder="+994 50 000 00 00">
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
                                       value="{{ old('website_url') }}"
                                       placeholder="https://www.example.com">
                                @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Social Networks --}}
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
                        @foreach(old('social_links', []) as $i => $sl)
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
                    <div id="social-links-empty" class="text-muted small {{ count(old('social_links', [])) > 0 ? 'd-none' : '' }}">
                        <i class="ph ph-info me-1"></i>No social links added. Click <strong>Add</strong> to add one.
                    </div>
                </div>
            </div>

        </div>

        {{-- -------------------------------------------------------- --}}
        {{-- RIGHT: Owner, Region, Categories, Actions sidebar        --}}
        {{-- -------------------------------------------------------- --}}
        <div class="col-lg-4">

            {{-- Assign to Owner --}}
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-user-check me-2 text-danger"></i>Assign to Owner
                    </h6>
                </div>
                <div class="card-body">
                    <select class="form-select @error('user_id') is-invalid @enderror"
                            id="user_id"
                            name="user_id">
                        <option value="">— No owner (unassigned) —</option>
                        @foreach($hosts as $host)
                            <option value="{{ $host->id }}"
                                    {{ old('user_id') == $host->id ? 'selected' : '' }}>
                                {{ $host->name }}
                                <span class="text-muted">({{ $host->email }})</span>
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-muted mt-1">
                        <i class="ph ph-info me-1"></i>
                        Optional — can be assigned later when an owner claims this listing.
                    </div>
                </div>
            </div>

            {{-- Price --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-currency-circle-dollar me-2 text-success"></i>Цена
                    </h6>
                </div>
                <div class="card-body">
                    <label for="price_from" class="form-label fw-semibold">Цена от (₼)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ph ph-currency-dollar-simple"></i></span>
                        <input type="number"
                               class="form-control @error('price_from') is-invalid @enderror"
                               id="price_from"
                               name="price_from"
                               value="{{ old('price_from') }}"
                               min="0"
                               step="0.01"
                               placeholder="0.00">
                        @error('price_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text text-muted">Минимальная цена за ночь / услугу. Оставьте пустым если цена не применима.</div>
                </div>
            </div>

            {{-- Location --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-map-pin me-2 text-primary"></i>Локация
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="region_id" class="form-label fw-semibold">Регион</label>
                        <select class="form-select @error('location.region_id') is-invalid @enderror"
                                id="region_id"
                                name="location[region_id]">
                            <option value="">— Выберите регион —</option>
                            @foreach($regions ?? [] as $region)
                                <option value="{{ $region->id }}"
                                        {{ old('location.region_id') == $region->id ? 'selected' : '' }}>
                                    {{ $region->getTranslation('name', 'az') }}
                                </option>
                            @endforeach
                        </select>
                        @error('location.region_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label for="location_latitude" class="form-label fw-semibold">Широта</label>
                            <input type="number"
                                   class="form-control @error('location.latitude') is-invalid @enderror"
                                   id="location_latitude"
                                   name="location[latitude]"
                                   value="{{ old('location.latitude') }}"
                                   step="0.00000001"
                                   placeholder="40.4093">
                            @error('location.latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label for="location_longitude" class="form-label fw-semibold">Долгота</label>
                            <input type="number"
                                   class="form-control @error('location.longitude') is-invalid @enderror"
                                   id="location_longitude"
                                   name="location[longitude]"
                                   value="{{ old('location.longitude') }}"
                                   step="0.00000001"
                                   placeholder="49.8671">
                            @error('location.longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div id="listing-map"></div>
                    <div class="form-text text-muted mt-1">
                        <i class="ph ph-cursor-click me-1"></i>Кликните по карте чтобы задать координаты
                    </div>
                </div>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- DYNAMIC: Categories                                       --}}
            {{-- The entire card is replaced/shown via JS                 --}}
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

            {{-- Media --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-images me-2 text-primary"></i>Media
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 px-3 mb-0 small">
                        <i class="ph ph-info me-1"></i>
                        Photos and videos can be added after the listing is created.
                    </div>
                </div>
            </div>

            {{-- Submit actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="ph ph-plus-circle me-2"></i>Create Listing
                    </button>
                    <a href="{{ route('admin.listings.index') }}" class="btn btn-link text-muted w-100">
                        Cancel
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
    var apiUrl   = '{{ route('admin.api.listing-type-data') }}';
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Map of type labels (mirrors Blade array above)
    var typeLabels = {
        hotel:      'Hotel Details',
        home:       'Home / Apartment Details',
        tour:       'Tour Details',
        activity:   'Activity Details',
        guide:      'Guide Details',
        restaurant: 'Restaurant Details'
    };

    // Old values passed from server after a failed form submission,
    // so we can re-populate fields that JS renders.
    var oldValues = @json(old());

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
            // Render as a form-check (boolean toggle)
            var checkId = 'field_' + field.name.replace(/[\[\]]+/g, '_');
            var isChecked = resolveOldBool(field.name);
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

        // All other fields get a standard label + input/select
        var fieldId  = 'field_' + field.name.replace(/[\[\]]+/g, '_');
        var required = field.required ? ' required' : '';
        var reqMark  = field.required ? ' <span class="text-danger">*</span>' : '';

        $wrap.append(
            '<label for="' + escHtml(fieldId) + '" class="form-label fw-semibold">' +
            escHtml(field.label) + reqMark +
            '</label>'
        );

        if (field.type === 'select') {
            var $sel = $('<select class="form-select" id="' + escHtml(fieldId) + '" name="' + escHtml(field.name) + '"' + required + '></select>');
            var oldVal = resolveOldValue(field.name);
            $.each(field.options || [], function (i, opt) {
                var selected = (String(opt.value) === String(oldVal)) ? ' selected' : '';
                $sel.append('<option value="' + escHtml(String(opt.value)) + '"' + selected + '>' + escHtml(opt.label) + '</option>');
            });
            $wrap.append($sel);

        } else if (field.type === 'pills') {
            // Generic pill checkboxes — options and input name come from the field descriptor
            var pillColor = field.color || 'secondary';
            var oldPillStr = resolveOldTagValue(field.name);
            var selectedPills = oldPillStr
                ? oldPillStr.split(',').map(function (s) { return s.trim(); }).filter(Boolean)
                : [];
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
            // Tags: a plain text input whose value is comma-separated.
            // On form submit we split and post as array via a hidden field builder.
            var placeholder = field.placeholder || '';
            var tagsId = fieldId + '_tags_input';
            var oldVal = resolveOldTagValue(field.name);
            $wrap.append(
                '<input type="text"' +
                ' class="form-control"' +
                ' id="' + escHtml(tagsId) + '"' +
                ' placeholder="' + escHtml(placeholder) + '"' +
                ' data-tags-name="' + escHtml(field.name) + '"' +
                ' value="' + escHtml(oldVal) + '"' +
                (field.required ? ' required' : '') +
                '>' +
                '<div class="form-text">Separate multiple values with commas.</div>' +
                '<div class="tags-hidden-container"></div>'
            );

        } else {
            // text, number, url, time
            var min    = (field.min  !== undefined) ? ' min="'  + field.min  + '"' : '';
            var step   = (field.step !== undefined) ? ' step="' + field.step + '"' : '';
            var oldVal = resolveOldValue(field.name);
            $wrap.append(
                '<input type="' + escHtml(field.type) + '"' +
                ' class="form-control"' +
                ' id="' + escHtml(fieldId) + '"' +
                ' name="' + escHtml(field.name) + '"' +
                ' value="' + escHtml(String(oldVal || '')) + '"' +
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
     */
    function renderAmenities(amenityGroups) {
        var $body = $('#amenities-body').empty();

        if (!amenityGroups || amenityGroups.length === 0) {
            $('#amenities-card').hide();
            return;
        }

        var oldAmenities = oldValues['amenities'] ? oldValues['amenities'] : [];

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
                var isChecked = (oldAmenities.indexOf(String(amenity.id)) !== -1 ||
                                 oldAmenities.indexOf(amenity.id) !== -1) ? ' checked' : '';
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
        var oldCats = oldValues['categories'] ? oldValues['categories'] : [];

        $.each(categories, function (i, cat) {
            var isChecked = (oldCats.indexOf(String(cat.id)) !== -1 ||
                             oldCats.indexOf(cat.id) !== -1) ? ' checked' : '';
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
        // Remove any previously attached submit handler to avoid double-binding
        $('#admin-create-form').off('submit.tags').on('submit.tags', function () {
            $(this).find('[data-tags-name]').each(function () {
                var name = $(this).data('tags-name');
                var vals = $(this).val().split(',').map(function (v) { return v.trim(); }).filter(Boolean);
                var $container = $(this).next('.tags-hidden-container').empty();
                // Replace the text input's name so it is not submitted as plain text
                $(this).removeAttr('name');
                $.each(vals, function (i, v) {
                    $container.append('<input type="hidden" name="' + escHtml(name) + '" value="' + escHtml(v) + '">');
                });
            });
        });
    }

    // ---------------------------------------------------------------
    // Helpers — old value resolution
    // ---------------------------------------------------------------

    function resolveOldValue(fieldName) {
        // fieldName looks like: detail[stars] or detail[property_type]
        var match = fieldName.match(/^(\w+)\[(\w+)\]$/);
        if (match) {
            var group = match[1]; // 'detail'
            var key   = match[2];
            if (oldValues[group] && oldValues[group][key] !== undefined) {
                return oldValues[group][key];
            }
        }
        return '';
    }

    function resolveOldBool(fieldName) {
        var val = resolveOldValue(fieldName);
        return val === true || val === 1 || val === '1' || val === 'on';
    }

    function resolveOldTagValue(fieldName) {
        // fieldName looks like: detail[languages][] or detail[cuisine_types][]
        var match = fieldName.match(/^(\w+)\[(\w+)\]\[\]$/);
        if (match) {
            var group = match[1];
            var key   = match[2];
            if (oldValues[group] && Array.isArray(oldValues[group][key])) {
                return oldValues[group][key].join(', ');
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

    var dayCounter = 0; // monotonically increasing index for unique IDs

    /**
     * Build and append a single itinerary block row to #itinerary-days.
     * @param {string} titleVal       Pre-fill value for the title input.
     * @param {string} descriptionVal Pre-fill value for the description textarea.
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

    /** Show/hide the empty state hint. */
    function syncItineraryEmpty() {
        var count = $('#itinerary-days .itinerary-day').length;
        if (count === 0) {
            $('#itinerary-empty').show();
        } else {
            $('#itinerary-empty').hide();
        }
    }

    /** Show or hide the itinerary card depending on whether the type is tour. */
    function syncItineraryCard(typeVal) {
        if (typeVal === 'tour') {
            $('#itinerary-card').show();
        } else {
            $('#itinerary-card').hide();
        }
    }

    // "Add Day" button
    $('#add-day-btn').on('click', function () {
        addDayRow('', '');
    });

    // Before the form submits, convert the itinerary day rows into
    // properly named hidden inputs: detail[itinerary][0][title], etc.
    // We remove any pre-existing hidden inputs from a previous submit attempt first.
    $('#admin-create-form').on('submit.itinerary', function () {
        // Remove stale hidden inputs from any previous submission attempt
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

    // Restore itinerary rows from old() after a failed validation round-trip.
    // oldValues['detail']['itinerary'] will be an array of {title, description} objects.
    (function restoreItineraryFromOld() {
        var itinerary = oldValues['detail'] && oldValues['detail']['itinerary']
            ? oldValues['detail']['itinerary']
            : [];
        if (Array.isArray(itinerary) && itinerary.length > 0) {
            $.each(itinerary, function (i, day) {
                var t = (typeof day === 'object' && day !== null) ? (day['title'] || '') : '';
                var d = (typeof day === 'object' && day !== null) ? (day['description'] || '') : '';
                addDayRow(t, d);
            });
        } else {
            syncItineraryEmpty();
        }
    }());

    // ---------------------------------------------------------------
    // AJAX — fetch data for the selected listing type
    // ---------------------------------------------------------------

    var currentRequest = null;

    function loadTypeData(typeVal) {
        // Abort any in-flight request
        if (currentRequest) {
            currentRequest.abort();
        }

        syncItineraryCard(typeVal);

        $('#type-select-hint').addClass('d-none');
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
            // Show a small inline error alert
            var msg = (xhr.responseJSON && xhr.responseJSON.error)
                ? xhr.responseJSON.error
                : 'Failed to load type data. Please refresh and try again.';
            $('#type-loading').html(
                '<div class="alert alert-warning alert-sm small py-2 mt-2">' +
                '<i class="ph ph-warning me-1"></i>' + escHtml(msg) +
                '</div>'
            ).removeClass('d-none');
            return; // skip the finally hide below
        })
        .always(function (data, textStatus) {
            if (textStatus !== 'abort') {
                $('#type-loading').addClass('d-none');
            }
        });
    }

    // ---------------------------------------------------------------
    // Event: type radio button changed
    // ---------------------------------------------------------------

    $('input[name="type"]').on('change', function () {
        var typeVal = $(this).val();
        if (typeVal) {
            loadTypeData(typeVal);
        }
    });

    // ---------------------------------------------------------------
    // On page load: if a type was already selected (old() after
    // validation failure), trigger the load immediately.
    // ---------------------------------------------------------------
    var preselectedType = $('input[name="type"]:checked').val();
    if (preselectedType) {
        loadTypeData(preselectedType);
    }

    // ---------------------------------------------------------------
    // Social Networks
    // ---------------------------------------------------------------

    var socialPlatforms = {
        instagram: { label: 'Instagram', icon: 'ph-instagram-logo', color: '#E1306C', placeholder: 'https://instagram.com/...' },
        facebook:  { label: 'Facebook',  icon: 'ph-facebook-logo',  color: '#1877F2', placeholder: 'https://facebook.com/...' },
        tiktok:    { label: 'TikTok',    icon: 'ph-tiktok-logo',    color: '#000',    placeholder: 'https://tiktok.com/...'    },
        whatsapp:  { label: 'WhatsApp',  icon: 'ph-whatsapp-logo',  color: '#25D366', placeholder: '+994501234567'             }
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var BAKU = [40.4093, 49.8671];
    var DEFAULT_ZOOM = 10;
    var PIN_ZOOM = 14;

    var $lat = $('#location_latitude');
    var $lng = $('#location_longitude');

    var initLat = parseFloat($lat.val());
    var initLng = parseFloat($lng.val());
    var hasCoords = !isNaN(initLat) && !isNaN(initLng);

    var map = L.map('listing-map').setView(
        hasCoords ? [initLat, initLng] : BAKU,
        hasCoords ? PIN_ZOOM : DEFAULT_ZOOM
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    var marker = hasCoords
        ? L.marker([initLat, initLng]).addTo(map)
        : null;

    function setMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
        map.setView([lat, lng], Math.max(map.getZoom(), PIN_ZOOM));
    }

    map.on('click', function (e) {
        var wrapped = e.latlng.wrap();
        var lat = parseFloat(wrapped.lat.toFixed(6));
        var lng = parseFloat(wrapped.lng.toFixed(6));
        $lat.val(lat);
        $lng.val(lng);
        setMarker(lat, lng);
    });

    function onInputChange() {
        var lat = parseFloat($lat.val());
        var lng = parseFloat($lng.val());
        if (!isNaN(lat) && !isNaN(lng)) {
            setMarker(lat, lng);
        }
    }

    $lat.on('change', onInputChange);
    $lng.on('change', onInputChange);
}());
</script>
@endsection
