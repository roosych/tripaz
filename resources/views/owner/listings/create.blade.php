@extends('layouts.owner')

@section('title', 'Добавить объявление')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center gap-3">
    <a href="{{ route('owner.listings.index') }}" class="btn btn-sm btn-outline-secondary" title="Назад к объявлениям">
        <i class="ph ph-arrow-left"></i>
    </a>
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-plus-circle me-2 text-primary"></i>Добавить объявление
        </h1>
        <p class="text-muted mb-0 small mt-1">Заполните данные ниже и отправьте на проверку</p>
    </div>
</div>

{{-- ================================================================ --}}
{{-- VALIDATION ERRORS                                                 --}}
{{-- ================================================================ --}}
@if($errors->any())
    <div class="alert alert-danger d-flex gap-2 align-items-start mb-4">
        <i class="ph ph-warning-circle flex-shrink-0 mt-1"></i>
        <div>
            <div class="fw-semibold mb-1">Пожалуйста, исправьте следующие ошибки:</div>
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
<form method="POST" action="{{ route('owner.listings.store') }}" id="owner-create-form">
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
                        <i class="ph ph-tag me-2 text-primary"></i>Тип объявления
                        <span class="text-danger ms-1">*</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2" id="type-selector">
                        @foreach([
                            'hotel'      => ['label' => 'Hotel',      'icon' => 'ph-buildings',     'color' => '#0d6efd'],
                            'home'       => ['label' => 'Home',        'icon' => 'ph-house',         'color' => '#0dcaf0'],
                            'tour'       => ['label' => 'Tour',        'icon' => 'ph-map-trifold',           'color' => '#198754'],
                            'activity'   => ['label' => 'Activity',   'icon' => 'ph-lightning',     'color' => '#ffc107'],
                            'guide'      => ['label' => 'Guide',       'icon' => 'ph-identification-card',  'color' => '#6c757d'],
                            'restaurant' => ['label' => 'Restaurant', 'icon' => 'ph-fork-knife',     'color' => '#dc3545'],
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
                    <div id="type-select-hint" class="mt-3 small text-muted {{ old('type') ? 'd-none' : '' }}">
                        <i class="ph ph-arrow-up me-1"></i>Выберите тип объявления для загрузки полей, категорий и удобств.
                    </div>
                </div>
            </div>

            {{-- Titles (Translations) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-translate me-2 text-primary"></i>Заголовки &amp; описание
                    </h6>
                </div>
                <div class="card-body">

                    {{-- AZ Title (required) --}}
                    <div class="mb-3">
                        <label for="title_az" class="form-label fw-semibold">
                            Заголовок (Азербайджанский) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">AZ</span>
                            <input type="text"
                                   class="form-control @error('translations.az.title') is-invalid @enderror"
                                   id="title_az"
                                   name="translations[az][title]"
                                   value="{{ old('translations.az.title') }}"
                                   placeholder="e.g. Baku Palace Hotel"
                                   required>
                            @error('translations.az.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- RU Title --}}
                    <div class="mb-3">
                        <label for="title_ru" class="form-label fw-semibold">
                            Заголовок (Русский)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">RU</span>
                            <input type="text"
                                   class="form-control @error('translations.ru.title') is-invalid @enderror"
                                   id="title_ru"
                                   name="translations[ru][title]"
                                   value="{{ old('translations.ru.title') }}"
                                   placeholder="e.g. Бакинский Дворец Отель">
                            @error('translations.ru.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- EN Title --}}
                    <div class="mb-3">
                        <label for="title_en" class="form-label fw-semibold">
                            Заголовок (Английский)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">EN</span>
                            <input type="text"
                                   class="form-control @error('translations.en.title') is-invalid @enderror"
                                   id="title_en"
                                   name="translations[en][title]"
                                   value="{{ old('translations.en.title') }}"
                                   placeholder="e.g. Baku Palace Hotel">
                            @error('translations.en.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Descriptions (all locales) --}}
                    @foreach(['az' => 'Азербайджанский', 'ru' => 'Русский', 'en' => 'Английский'] as $locale => $localeName)
                        <div class="{{ $loop->last ? 'mb-0' : 'mb-3' }}">
                            <label for="description_{{ $locale }}" class="form-label fw-semibold">
                                Описание ({{ $localeName }})
                            </label>
                            <div class="input-group align-items-start">
                                <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">{{ strtoupper($locale) }}</span>
                                <textarea class="form-control @error("translations.{$locale}.description") is-invalid @enderror"
                                          id="description_{{ $locale }}"
                                          name="translations[{{ $locale }}][description]"
                                          rows="4"
                                          placeholder="Опишите объявление на {{ strtolower($localeName) }}...">{{ old("translations.{$locale}.description") }}</textarea>
                                @error("translations.{$locale}.description")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- DYNAMIC: Type-Specific Detail Fields --}}
            <div id="detail-fields-card" class="card border-0 shadow-sm mb-4" style="display:none;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0" id="detail-fields-heading">
                        <i class="ph ph-sliders-horizontal me-2 text-primary"></i><span id="detail-fields-title">Детали типа</span>
                    </h6>
                </div>
                <div class="card-body" id="detail-fields-body">
                    {{-- Заполняется JavaScript-ом --}}
                </div>
            </div>

            {{-- TOUR ONLY: Itinerary --}}
            <div id="itinerary-card" class="card border-0 shadow-sm mb-4" style="display:none;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-calendar me-2 text-success"></i>Маршрут
                        <span class="badge bg-success ms-2" style="font-size:0.65rem;vertical-align:middle">Только для тура</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-success" id="add-day-btn">
                        <i class="ph ph-plus-circle me-1"></i>Добавить
                    </button>
                </div>
                <div class="card-body">
                    <div id="itinerary-days" class="d-flex flex-column gap-3"></div>
                    <div id="itinerary-empty" class="text-center text-muted py-3 small">
                        <i class="ph ph-calendar-x fs-4 d-block mb-1"></i>
                        Блоки маршрута не добавлены. Нажмите <strong>Добавить</strong>, чтобы начать составлять маршрут.
                    </div>
                </div>
            </div>

            {{-- DYNAMIC: Amenities --}}
            <div id="amenities-card" class="card border-0 shadow-sm mb-4" style="display:none;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-check-square me-2 text-primary"></i>Удобства &amp; особенности
                    </h6>
                </div>
                <div class="card-body" id="amenities-body">
                    {{-- Заполняется JavaScript-ом --}}
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-phone me-2 text-primary"></i>Контактная информация
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
                                       id="contact_email"
                                       name="contact_email"
                                       value="{{ old('contact_email') }}"
                                       placeholder="contact@example.com">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label fw-semibold">Телефон</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                <input type="text"
                                       class="form-control @error('contact_phone') is-invalid @enderror"
                                       id="contact_phone"
                                       name="contact_phone"
                                       value="{{ old('contact_phone') }}"
                                       placeholder="+994 50 000 00 00">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="website" class="form-label fw-semibold">Веб-сайт</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-globe"></i></span>
                                <input type="url"
                                       class="form-control @error('website') is-invalid @enderror"
                                       id="website"
                                       name="website"
                                       value="{{ old('website') }}"
                                       placeholder="https://www.example.com">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Социальные сети --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-share-network me-2 text-primary"></i>Социальные сети
                    </h6>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-social-btn">
                        <i class="ph ph-plus-circle me-1"></i>Добавить
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
                                <button type="button" class="btn btn-outline-danger btn-sm remove-social-btn flex-shrink-0" title="Удалить">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div id="social-links-empty" class="text-muted small {{ count(old('social_links', [])) > 0 ? 'd-none' : '' }}">
                        <i class="ph ph-info me-1"></i>Нет ссылок. Нажмите <strong>Добавить</strong>, чтобы добавить.
                    </div>
                </div>
            </div>

        </div>

        {{-- -------------------------------------------------------- --}}
        {{-- RIGHT: Region & Categories sidebar                       --}}
        {{-- -------------------------------------------------------- --}}
        <div class="col-lg-4">

            {{-- Region --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-map-pin me-2 text-primary"></i>Регион
                    </h6>
                </div>
                <div class="card-body">
                    <label for="region_id" class="form-label fw-semibold">Выберите регион</label>
                    <select class="form-select @error('region_id') is-invalid @enderror"
                            id="region_id"
                            name="region_id">
                        <option value="">— Выберите регион —</option>
                        @foreach($regions ?? [] as $region)
                            <option value="{{ $region->id }}"
                                    {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                {{ $region->getTranslation('name', 'az') }}
                            </option>
                        @endforeach
                    </select>
                    @error('region_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- DYNAMIC: Categories --}}
            <div id="categories-card" class="card border-0 shadow-sm mb-4" style="display:none;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-tag me-2 text-primary"></i>Категории
                    </h6>
                </div>
                <div class="card-body">
                    <div id="categories-body" style="max-height:220px;overflow-y:auto;" class="d-flex flex-column gap-1">
                        {{-- Заполняется JavaScript-ом --}}
                    </div>
                    <div id="categories-empty" class="small text-muted d-none">
                        Нет категорий для данного типа.
                    </div>
                </div>
            </div>

            {{-- Loading indicator --}}
            <div id="type-loading" class="text-center py-3 d-none">
                <div class="spinner-border spinner-border-sm text-secondary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <span class="ms-2 small text-muted">Загрузка полей...</span>
            </div>

            {{-- Media --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-images me-2 text-primary"></i>Медиафайлы
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 px-3 mb-0 small">
                        <i class="ph ph-info me-1"></i>
                        Фото и видео можно добавить после создания объявления.
                    </div>
                </div>
            </div>

            {{-- Submit actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ph ph-paper-plane-tilt me-2"></i>Отправить на проверку
                    </button>
                    <button type="submit" name="save_draft" value="1" class="btn btn-outline-secondary w-100">
                        <i class="ph ph-floppy-disk me-2"></i>Сохранить как черновик
                    </button>
                    <a href="{{ route('owner.listings.index') }}" class="btn btn-link text-muted w-100">
                        Отмена
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

    var apiUrl    = '{{ route('owner.api.listing-type-data') }}';
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var typeLabels = {
        hotel:      'Детали отеля',
        home:       'Детали жилья / апартаментов',
        tour:       'Детали тура',
        activity:   'Детали активности',
        guide:      'Детали гида',
        restaurant: 'Детали ресторана'
    };

    var oldValues = @json(old());

    // ---- Helpers ----

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function resolveOldValue(fieldName) {
        var match = fieldName.match(/^(\w+)\[(\w+)\]$/);
        if (match && oldValues[match[1]] && oldValues[match[1]][match[2]] !== undefined) {
            return oldValues[match[1]][match[2]];
        }
        return '';
    }

    function resolveOldBool(fieldName) {
        var val = resolveOldValue(fieldName);
        return val === true || val === 1 || val === '1' || val === 'on';
    }

    function resolveOldTagValue(fieldName) {
        var match = fieldName.match(/^(\w+)\[(\w+)\]\[\]$/);
        if (match && oldValues[match[1]] && Array.isArray(oldValues[match[1]][match[2]])) {
            return oldValues[match[1]][match[2]].join(', ');
        }
        return '';
    }

    // ---- Field builder ----

    function buildField(field) {
        var $wrap = $('<div class="mb-3"></div>');

        if (field.type === 'checkbox') {
            var checkId = 'field_' + field.name.replace(/[\[\]]+/g, '_');
            $wrap.addClass('form-check').removeClass('mb-3').addClass('mb-2');
            $wrap.html(
                '<input class="form-check-input" type="checkbox" name="' + escHtml(field.name) + '" id="' + escHtml(checkId) + '" value="1"' +
                (resolveOldBool(field.name) ? ' checked' : '') + '>' +
                '<label class="form-check-label" for="' + escHtml(checkId) + '">' + escHtml(field.label) + '</label>'
            );
            return $wrap;
        }

        var fieldId  = 'field_' + field.name.replace(/[\[\]]+/g, '_');
        var required = field.required ? ' required' : '';
        var reqMark  = field.required ? ' <span class="text-danger">*</span>' : '';

        $wrap.append('<label for="' + escHtml(fieldId) + '" class="form-label fw-semibold">' + escHtml(field.label) + reqMark + '</label>');

        if (field.type === 'select') {
            var $sel = $('<select class="form-select" id="' + escHtml(fieldId) + '" name="' + escHtml(field.name) + '"' + required + '></select>');
            var oldVal = resolveOldValue(field.name);
            $.each(field.options || [], function (i, opt) {
                $sel.append('<option value="' + escHtml(String(opt.value)) + '"' + (String(opt.value) === String(oldVal) ? ' selected' : '') + '>' + escHtml(opt.label) + '</option>');
            });
            $wrap.append($sel);

        } else if (field.type === 'pills') {
            var pillColor = field.color || 'secondary';
            var oldPillStr = resolveOldTagValue(field.name);
            var selectedPills = oldPillStr ? oldPillStr.split(',').map(function(s){return s.trim();}).filter(Boolean) : [];
            var $pillWrap = $('<div class="d-flex flex-wrap gap-1"></div>');
            $.each(field.options || [], function(i, opt) {
                var uid = 'pill_' + fieldId + '_' + i;
                $pillWrap.append(
                    '<input type="checkbox" class="btn-check" autocomplete="off" id="' + escHtml(uid) + '" name="' + escHtml(field.name) + '" value="' + escHtml(String(opt.value)) + '"' +
                    (selectedPills.indexOf(String(opt.value)) !== -1 ? ' checked' : '') + '>' +
                    '<label class="btn btn-outline-' + escHtml(pillColor) + ' btn-sm rounded-pill" for="' + escHtml(uid) + '">' + escHtml(opt.label) + '</label>'
                );
            });
            $wrap.append($pillWrap);

        } else if (field.type === 'tags') {
            var tagsId = fieldId + '_tags_input';
            $wrap.append(
                '<input type="text" class="form-control" id="' + escHtml(tagsId) + '" placeholder="' + escHtml(field.placeholder || '') + '"' +
                ' data-tags-name="' + escHtml(field.name) + '" value="' + escHtml(resolveOldTagValue(field.name)) + '"' + required + '>' +
                '<div class="form-text">Разделяйте значения запятыми.</div><div class="tags-hidden-container"></div>'
            );

        } else {
            var min  = field.min  !== undefined ? ' min="'  + field.min  + '"' : '';
            var step = field.step !== undefined ? ' step="' + field.step + '"' : '';
            $wrap.append(
                '<input type="' + escHtml(field.type) + '" class="form-control" id="' + escHtml(fieldId) + '" name="' + escHtml(field.name) + '"' +
                ' value="' + escHtml(String(resolveOldValue(field.name) || '')) + '"' + min + step + required + '>'
            );
        }
        return $wrap;
    }

    // ---- Renderers ----

    function renderDetailFields(fields, typeVal) {
        var $body = $('#detail-fields-body').empty();
        $('#detail-fields-title').text(typeLabels[typeVal] || 'Детали типа');
        if (!fields || fields.length === 0) { $('#detail-fields-card').hide(); return; }

        var checkboxFields = [], otherFields = [];
        $.each(fields, function(i, f) { (f.type === 'checkbox' ? checkboxFields : otherFields).push(f); });

        if (otherFields.length > 0) {
            var $row = $('<div class="row g-3"></div>');
            $.each(otherFields, function(i, f) {
                var colClass = (f.type === 'text' || f.type === 'url' || f.type === 'tags') ? 'col-12' : 'col-md-6';
                $row.append($('<div></div>').addClass(colClass).append(buildField(f)));
            });
            $body.append($row);
        }
        if (checkboxFields.length > 0) {
            $body.append('<p class="fw-semibold small text-muted text-uppercase mt-2 mb-1">Удобства</p>');
            var $checkRow = $('<div class="row g-2"></div>');
            $.each(checkboxFields, function(i, f) { $checkRow.append($('<div class="col-md-6"></div>').append(buildField(f))); });
            $body.append($checkRow);
        }
        $('#detail-fields-card').show();
        attachTagsListeners();
    }

    function renderAmenities(amenityGroups) {
        var $body = $('#amenities-body').empty();
        if (!amenityGroups || amenityGroups.length === 0) { $('#amenities-card').hide(); return; }

        var oldAmenities = oldValues['amenities'] ? oldValues['amenities'] : [];
        $.each(amenityGroups, function(i, group) {
            if (!group.items || group.items.length === 0) { return; }
            var $groupWrap = $('<div class="mb-3"></div>');
            if (group.group && group.group !== 'null') {
                $groupWrap.append('<div class="text-muted fw-semibold small mb-2 text-uppercase" style="font-size:0.7rem;letter-spacing:0.8px;border-bottom:1px solid #e9ecef;padding-bottom:4px">' + escHtml(group.group) + '</div>');
            }
            var $checkList = $('<div class="row g-1"></div>');
            $.each(group.items, function(j, amenity) {
                var isChecked = (oldAmenities.indexOf(String(amenity.id)) !== -1 || oldAmenities.indexOf(amenity.id) !== -1) ? ' checked' : '';
                var checkId = 'amenity_' + amenity.id;
                $checkList.append(
                    '<div class="col-md-6"><div class="form-check">' +
                    '<input class="form-check-input" type="checkbox" name="amenities[]" value="' + amenity.id + '" id="' + escHtml(checkId) + '"' + isChecked + '>' +
                    '<label class="form-check-label" for="' + escHtml(checkId) + '" style="font-size:0.88rem">' +
                    (amenity.icon ? '<i class="ph ' + escHtml(amenity.icon) + ' me-1 text-muted"></i>' : '') +
                    escHtml(amenity.name_az || amenity.name_en || '') + '</label></div></div>'
                );
            });
            $groupWrap.append($checkList);
            $body.append($groupWrap);
        });
        $('#amenities-card').show();
    }

    function renderCategories(categories) {
        var $body = $('#categories-body').empty();
        var $empty = $('#categories-empty');
        if (!categories || categories.length === 0) { $empty.removeClass('d-none'); $('#categories-card').show(); return; }
        $empty.addClass('d-none');
        var oldCats = oldValues['categories'] ? oldValues['categories'] : [];
        $.each(categories, function(i, cat) {
            var isChecked = (oldCats.indexOf(String(cat.id)) !== -1 || oldCats.indexOf(cat.id) !== -1) ? ' checked' : '';
            var checkId = 'cat_' + cat.id;
            $body.append(
                '<div class="form-check"><input class="form-check-input" type="checkbox" name="categories[]" value="' + cat.id + '" id="' + escHtml(checkId) + '"' + isChecked + '>' +
                '<label class="form-check-label" for="' + escHtml(checkId) + '" style="font-size:0.88rem">' + escHtml(cat.name_az || cat.name_en || '') + '</label></div>'
            );
        });
        $('#categories-card').show();
    }

    // ---- Tags submit handler ----

    function attachTagsListeners() {
        $('#owner-create-form').off('submit.tags').on('submit.tags', function() {
            $(this).find('[data-tags-name]').each(function() {
                var name = $(this).data('tags-name');
                var vals = $(this).val().split(',').map(function(v){return v.trim();}).filter(Boolean);
                var $container = $(this).next('.tags-hidden-container').empty();
                $(this).removeAttr('name');
                $.each(vals, function(i, v) { $container.append('<input type="hidden" name="' + escHtml(name) + '" value="' + escHtml(v) + '">'); });
            });
        });
    }

    // ---- Itinerary ----

    var dayCounter = 0;

    function addDayRow(titleVal, descriptionVal) {
        dayCounter++;
        var idx = dayCounter;
        var $row = $(
            '<div class="itinerary-day border rounded-3 p-3 bg-light position-relative" data-day-index="' + idx + '">' +
            '<div class="d-flex align-items-center justify-content-end mb-2">' +
            '<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-day-btn" title="Удалить"><i class="ph ph-trash"></i></button></div>' +
            '<div class="mb-2"><label class="form-label fw-semibold small mb-1">Заголовок</label>' +
            '<input type="text" class="form-control form-control-sm itinerary-title" placeholder="День 1, Маршрут А..." value="' + escHtml(titleVal || '') + '"></div>' +
            '<div class="mb-0"><label class="form-label fw-semibold small mb-1">Описание</label>' +
            '<textarea class="form-control form-control-sm itinerary-description" rows="3" placeholder="Опишите программу...">' + escHtml(descriptionVal || '') + '</textarea></div></div>'
        );
        $row.find('.remove-day-btn').on('click', function() { $row.remove(); syncItineraryEmpty(); });
        $('#itinerary-days').append($row);
        syncItineraryEmpty();
    }

    function syncItineraryEmpty() {
        var count = $('#itinerary-days .itinerary-day').length;
        $('#itinerary-empty')[count === 0 ? 'show' : 'hide']();
    }

    function syncItineraryCard(typeVal) {
        $('#itinerary-card')[typeVal === 'tour' ? 'show' : 'hide']();
    }

    $('#add-day-btn').on('click', function() { addDayRow('', ''); });

    $('#owner-create-form').on('submit.itinerary', function() {
        $(this).find('input[name^="detail[itinerary]"]').remove();
        var $form = $(this), seq = 0;
        $('#itinerary-days .itinerary-day').each(function() {
            var title = $(this).find('.itinerary-title').val().trim();
            var desc  = $(this).find('.itinerary-description').val().trim();
            if (title !== '' || desc !== '') {
                $form.append('<input type="hidden" name="detail[itinerary][' + seq + '][title]" value="' + escHtml(title) + '">');
                $form.append('<input type="hidden" name="detail[itinerary][' + seq + '][description]" value="' + escHtml(desc) + '">');
                seq++;
            }
        });
    });

    (function restoreItinerary() {
        var itinerary = oldValues['detail'] && oldValues['detail']['itinerary'] ? oldValues['detail']['itinerary'] : [];
        if (Array.isArray(itinerary) && itinerary.length > 0) {
            $.each(itinerary, function(i, day) {
                addDayRow(typeof day === 'object' ? (day['title'] || '') : '', typeof day === 'object' ? (day['description'] || '') : '');
            });
        } else {
            syncItineraryEmpty();
        }
    }());

    // ---- AJAX ----

    var currentRequest = null;

    function loadTypeData(typeVal) {
        if (currentRequest) currentRequest.abort();
        syncItineraryCard(typeVal);
        $('#type-select-hint').addClass('d-none');
        $('#type-loading').removeClass('d-none');

        currentRequest = $.ajax({ url: apiUrl, method: 'GET', data: { type: typeVal }, headers: { 'X-CSRF-TOKEN': csrfToken } })
        .done(function(data) {
            renderDetailFields(data.fields || [], typeVal);
            renderAmenities(data.amenities || []);
            renderCategories(data.categories || []);
        })
        .fail(function(xhr) {
            if (xhr.statusText === 'abort') return;
            $('#detail-fields-card, #amenities-card, #categories-card').hide();
            var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Ошибка загрузки. Обновите страницу.';
            $('#type-loading').html('<div class="alert alert-warning small py-2 mt-2"><i class="ph ph-warning me-1"></i>' + escHtml(msg) + '</div>').removeClass('d-none');
            return;
        })
        .always(function(data, textStatus) { if (textStatus !== 'abort') $('#type-loading').addClass('d-none'); });
    }

    $('input[name="type"]').on('change', function() { if ($(this).val()) loadTypeData($(this).val()); });

    var preselectedType = $('input[name="type"]:checked').val();
    if (preselectedType) loadTypeData(preselectedType);

    // ---------------------------------------------------------------
    // Социальные сети
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
            '<button type="button" class="btn btn-outline-danger btn-sm remove-social-btn flex-shrink-0" title="Удалить">' +
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
