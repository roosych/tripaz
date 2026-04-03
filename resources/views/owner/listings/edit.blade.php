@extends('layouts.owner')

@section('title', 'Редактировать объявление')

@push('styles')
<style>
.ring-cover { outline: 2px solid #ffc107; outline-offset: 1px; }
#photo-drop-zone:hover, #photo-drop-zone.dragover { background: #f8f9fa; border-color: #6c757d !important; }
</style>
@endpush

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center gap-3 flex-wrap">
    <a href="{{ route('owner.listings.show', $listing->slug) }}" class="btn btn-sm btn-outline-secondary" title="Назад">
        <i class="ph ph-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="h4 fw-bold mb-0">
                <i class="ph ph-pencil-simple me-2 text-primary"></i>Редактировать объявление
            </h1>
            @php
                $statusVal = $listing->status instanceof \App\Enums\ListingStatus
                    ? $listing->status->value
                    : (string)$listing->status;
            @endphp
            <x-status-badge :status="$statusVal" />
        </div>
        <p class="text-muted mb-0 small mt-1">
            {{ $listing->slug }}
        </p>
    </div>

    {{-- Submit for Review (stub action) --}}
    @if($statusVal === 'draft' || $statusVal === 'rejected')
        <form method="POST" action="{{ route('owner.listings.submit', $listing->slug) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                <i class="ph ph-paper-plane-tilt"></i>
                Отправить на проверку
            </button>
        </form>
    @endif
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
{{-- EDIT FORM                                                         --}}
{{-- ================================================================ --}}
<form method="POST"
      action="{{ route('owner.listings.update', $listing->slug) }}"
      id="owner-edit-form">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- -------------------------------------------------------- --}}
        {{-- LEFT: Main fields                                        --}}
        {{-- -------------------------------------------------------- --}}
        <div class="col-lg-8">

            {{-- Listing Type (read-only on edit — type changes are not allowed) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-tag me-2 text-primary"></i>Тип объявления
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $typeVal = $listing->type instanceof \App\Enums\ListingType
                            ? $listing->type->value
                            : (string)$listing->type;

                        $typeLabelMap = [
                            'hotel'      => ['label' => 'Hotel',      'icon' => 'ph-buildings',     'color' => '#0d6efd'],
                            'home'       => ['label' => 'Home',        'icon' => 'ph-house',         'color' => '#0dcaf0'],
                            'tour'       => ['label' => 'Tour',        'icon' => 'ph-map-trifold',           'color' => '#198754'],
                            'activity'   => ['label' => 'Activity',   'icon' => 'ph-lightning',     'color' => '#ffc107'],
                            'guide'      => ['label' => 'Guide',       'icon' => 'ph-identification-card',  'color' => '#6c757d'],
                            'restaurant' => ['label' => 'Restaurant', 'icon' => 'ph-fork-knife',     'color' => '#dc3545'],
                        ];
                        $tc = $typeLabelMap[$typeVal] ?? ['label' => ucfirst($typeVal), 'icon' => 'ph-tag', 'color' => '#6c757d'];
                    @endphp
                    <input type="hidden" name="type" value="{{ $typeVal }}">
                    <div class="d-inline-flex align-items-center gap-2 bg-light border rounded-3 px-3 py-2">
                        <i class="ph {{ $tc['icon'] }} fs-5" style="color:{{ $tc['color'] }}"></i>
                        <span class="fw-semibold">{{ $tc['label'] }}</span>
                        <span class="text-muted small">(нельзя изменить после создания)</span>
                    </div>
                </div>
            </div>

            {{-- Titles --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-translate me-2 text-primary"></i>Заголовки &amp; описание
                    </h6>
                </div>
                <div class="card-body">

                    @foreach(['az' => 'Azerbaijani', 'ru' => 'Russian', 'en' => 'English'] as $locale => $localeName)
                        @php
                            $trans = $listing->translations->firstWhere('locale', $locale);
                        @endphp
                        <div class="mb-3">
                            <label for="title_{{ $locale }}" class="form-label fw-semibold">
                                Заголовок ({{ $localeName }})
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

                    {{-- Descriptions (all locales) --}}
                    @foreach(['az' => 'Азербайджанский', 'ru' => 'Русский', 'en' => 'Английский'] as $locale => $localeName)
                        @php $trans = $listing->translations->firstWhere('locale', $locale); @endphp
                        <div class="{{ $loop->last ? 'mb-0' : 'mb-3' }}">
                            <label for="description_{{ $locale }}" class="form-label fw-semibold">
                                Описание ({{ $localeName }})
                            </label>
                            <div class="input-group align-items-start">
                                <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">{{ strtoupper($locale) }}</span>
                                <textarea class="form-control @error("translations.{$locale}.description") is-invalid @enderror"
                                          id="description_{{ $locale }}"
                                          name="translations[{{ $locale }}][description]"
                                          rows="5">{{ old("translations.{$locale}.description", $trans?->description) }}</textarea>
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
            <div id="itinerary-card" class="card border-0 shadow-sm mb-4" style="{{ $typeVal === 'tour' ? '' : 'display:none;' }}">
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
                                       value="{{ old('contact_email', $listing->contact_email) }}">
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
                                       value="{{ old('contact_phone', $listing->contact_phone) }}">
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
                                       value="{{ old('website', $listing->website) }}">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Социальные сети --}}
            @php $existingSocialLinks = old('social_links', $listing->social_links ?? []); @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-share-network-08 me-2 text-primary"></i>Социальные сети
                    </h6>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-social-btn">
                        <i class="ph ph-plus-circle me-1"></i>Добавить
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
                                <button type="button" class="btn btn-outline-danger btn-sm remove-social-btn flex-shrink-0" title="Удалить">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div id="social-links-empty" class="text-muted small {{ count($existingSocialLinks) > 0 ? 'd-none' : '' }}">
                        <i class="ph ph-info me-1"></i>Нет ссылок. Нажмите <strong>Добавить</strong>, чтобы добавить.
                    </div>
                </div>
            </div>

        </div>

        {{-- -------------------------------------------------------- --}}
        {{-- RIGHT: Region, Categories, Actions                       --}}
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
                    <select class="form-select @error('region_id') is-invalid @enderror"
                            name="region_id"
                            id="region_id">
                        <option value="">— Выберите регион —</option>
                        @foreach($regions ?? [] as $region)
                            <option value="{{ $region->id }}"
                                    {{ old('region_id', $listing->location?->region_id) == $region->id ? 'selected' : '' }}>
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

            {{-- Listing info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-info me-2 text-primary"></i>Информация об объявлении
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0" style="font-size:0.85rem">
                        <dt class="col-6 text-muted fw-normal">Создано</dt>
                        <dd class="col-6">{{ $listing->created_at->format('d M Y') }}</dd>
                        <dt class="col-6 text-muted fw-normal">Обновлено</dt>
                        <dd class="col-6">{{ $listing->updated_at->format('d M Y') }}</dd>
                        <dt class="col-6 text-muted fw-normal">Статус</dt>
                        <dd class="col-6"><x-status-badge :status="$statusVal" /></dd>
                        <dt class="col-6 text-muted fw-normal">Slug</dt>
                        <dd class="col-6 text-break">{{ $listing->slug }}</dd>
                    </dl>
                </div>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- Media                                                    --}}
            {{-- -------------------------------------------------------- --}}
            @php $existingYoutube = $mediaItems->firstWhere('mime_type', 'video/youtube'); @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-images me-2 text-primary"></i>Медиафайлы
                    </h6>
                    <small class="text-muted">{{ $mediaItems->count() }} файл(ов)</small>
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
                                                data-delete-url="{{ route('owner.listings.media.destroy', [$listing->slug, $mediaItem->id]) }}"
                                                title="Удалить">
                                            <i class="ph ph-x"></i>
                                        </button>
                                    </div>
                                    <div class="text-truncate small text-muted mt-1 px-1" style="font-size:0.65rem">
                                        {{ Str::limit($mediaItem->custom_properties['youtube_url'] ?? '', 30) }}
                                    </div>
                                </div>
                            @else
                                {{-- Photo card --}}
                                @php $isCover = $listing->featured_image === $mediaItem->path; @endphp
                                <div class="col-4" id="media-item-{{ $mediaItem->id }}">
                                    <div class="position-relative rounded overflow-hidden {{ $isCover ? 'ring-cover' : '' }}" style="aspect-ratio:1">
                                        <img src="{{ $mediaItem->url() }}"
                                             alt="фото"
                                             class="w-100 h-100"
                                             style="object-fit:cover">
                                        @if($isCover)
                                            <span class="position-absolute bottom-0 start-0 m-1 badge bg-warning text-dark" style="font-size:0.55rem">
                                                <i class="ph ph-star me-1"></i>Обложка
                                            </span>
                                        @endif
                                        @if(!$isCover)
                                            <button type="button"
                                                    class="btn btn-sm btn-warning position-absolute bottom-0 start-0 m-1 p-0 px-1 media-cover-btn"
                                                    style="font-size:0.6rem;line-height:1.4"
                                                    data-media-id="{{ $mediaItem->id }}"
                                                    data-cover-url="{{ route('owner.listings.media.cover', [$listing->slug, $mediaItem->id]) }}"
                                                    title="Сделать обложкой">
                                                <i class="ph ph-star"></i>
                                            </button>
                                        @endif
                                        <button type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 px-1 media-delete-btn"
                                                style="font-size:0.65rem;line-height:1.4"
                                                data-media-id="{{ $mediaItem->id }}"
                                                data-delete-url="{{ route('owner.listings.media.destroy', [$listing->slug, $mediaItem->id]) }}"
                                                title="Удалить">
                                            <i class="ph ph-x"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="col-12 text-center py-2 text-muted small" id="media-empty-msg">
                                <i class="ph ph-image me-1"></i>Нет медиафайлов
                            </div>
                        @endforelse
                    </div>

                    <hr class="my-2">

                    {{-- Upload Photo --}}
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Загрузить фото</label>
                        <div id="photo-drop-zone"
                             class="border border-dashed rounded p-2 text-center text-muted small"
                             style="cursor:pointer;border-style:dashed!important;min-height:60px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:4px">
                            <i class="ph ph-cloud-arrow-up fs-5"></i>
                            <span>Нажмите или перетащите jpg/jpeg сюда</span>
                            <span style="font-size:0.7rem">Макс. {{ round(config('listing_media.max_kb', 5120) / 1024, 0) }} МБ каждый</span>
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
                    @php $existingYoutubeUrl = $existingYoutube?->custom_properties['youtube_url'] ?? ''; @endphp
                    <div class="mb-1">
                        <label class="form-label small fw-semibold mb-1">YouTube видео</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="ph ph-youtube-logo text-danger"></i></span>
                            <input type="url"
                                   id="youtube-url-input"
                                   class="form-control form-control-sm"
                                   placeholder="https://www.youtube.com/watch?v=..."
                                   value="{{ $existingYoutubeUrl }}">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="youtube-add-btn">
                                {{ $existingYoutubeUrl ? 'Обновить' : 'Добавить' }}
                            </button>
                        </div>
                        <div id="youtube-feedback" class="small mt-1 d-none"></div>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ph ph-floppy-disk me-2"></i>Сохранить изменения
                    </button>

                    @if($statusVal === 'draft' || $statusVal === 'rejected')
                        <div class="position-relative">
                            <div class="border-top my-1"></div>
                        </div>
                        <form method="POST" action="{{ route('owner.listings.submit', $listing->slug) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ph ph-paper-plane-tilt me-2"></i>Отправить на проверку
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('owner.listings.index') }}" class="btn btn-link text-muted w-100">
                        Назад к объявлениям
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
    var currentType = '{{ $typeVal }}';

    var typeLabels = {
        hotel:      'Детали отеля',
        home:       'Детали жилья / апартаментов',
        tour:       'Детали тура',
        activity:   'Детали активности',
        guide:      'Детали гида',
        restaurant: 'Детали ресторана'
    };

    var dbDetailValues = @json($detailValues);
    var oldInput       = @json(old());

    var detailValues = (oldInput && oldInput['detail']) ? oldInput['detail'] : dbDetailValues;

    var selectedAmenityIds  = @json($listing->amenities->pluck('id'));
    var selectedCategoryIds = @json($listing->categories->pluck('id'));

    var amenityIds  = (oldInput && oldInput['amenities'])
        ? oldInput['amenities'].map(function(v) { return parseInt(v, 10); })
        : selectedAmenityIds.map(function(v) { return parseInt(v, 10); });

    var categoryIds = (oldInput && oldInput['categories'])
        ? oldInput['categories'].map(function(v) { return parseInt(v, 10); })
        : selectedCategoryIds.map(function(v) { return parseInt(v, 10); });

    // ---- Helpers ----

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function resolveDetailValue(fieldName) {
        var match = fieldName.match(/^(\w+)\[(\w+)\]$/);
        if (match) {
            var key = match[2];
            if (detailValues[key] !== undefined && detailValues[key] !== null) return detailValues[key];
        }
        return '';
    }

    function resolveDetailBool(fieldName) {
        var val = resolveDetailValue(fieldName);
        return val === true || val === 1 || val === '1' || val === 'on';
    }

    function resolveDetailTagValue(fieldName) {
        var match = fieldName.match(/^(\w+)\[(\w+)\]\[\]$/);
        if (match) {
            var key = match[2];
            if (detailValues[key] && Array.isArray(detailValues[key])) return detailValues[key].join(', ');
            if (typeof detailValues[key] === 'string') return detailValues[key];
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
                (resolveDetailBool(field.name) ? ' checked' : '') + '>' +
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
            var selVal = resolveDetailValue(field.name);
            $.each(field.options || [], function(i, opt) {
                $sel.append('<option value="' + escHtml(String(opt.value)) + '"' + (String(opt.value) === String(selVal) ? ' selected' : '') + '>' + escHtml(opt.label) + '</option>');
            });
            $wrap.append($sel);

        } else if (field.type === 'pills') {
            var pillColor = field.color || 'secondary';
            var pillTagStr = resolveDetailTagValue(field.name);
            var selectedPills;
            if (pillTagStr) {
                selectedPills = pillTagStr.split(',').map(function(s){return s.trim();}).filter(Boolean);
            } else {
                var pillMatch = field.name.match(/\[(\w+)\]\[\]$/);
                var pillKey = pillMatch ? pillMatch[1] : null;
                selectedPills = (pillKey && Array.isArray(detailValues[pillKey])) ? detailValues[pillKey] : [];
            }
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
                ' data-tags-name="' + escHtml(field.name) + '" value="' + escHtml(resolveDetailTagValue(field.name)) + '"' + required + '>' +
                '<div class="form-text">Разделяйте значения запятыми.</div><div class="tags-hidden-container"></div>'
            );

        } else {
            var min  = field.min  !== undefined ? ' min="'  + field.min  + '"' : '';
            var step = field.step !== undefined ? ' step="' + field.step + '"' : '';
            var txtVal = resolveDetailValue(field.name);
            $wrap.append(
                '<input type="' + escHtml(field.type) + '" class="form-control" id="' + escHtml(fieldId) + '" name="' + escHtml(field.name) + '"' +
                ' value="' + escHtml(String(txtVal !== null && txtVal !== undefined ? txtVal : '')) + '"' + min + step + required + '>'
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

        $.each(amenityGroups, function(i, group) {
            if (!group.items || group.items.length === 0) return;
            var $groupWrap = $('<div class="mb-3"></div>');
            if (group.group && group.group !== 'null') {
                $groupWrap.append('<div class="text-muted fw-semibold small mb-2 text-uppercase" style="font-size:0.7rem;letter-spacing:0.8px;border-bottom:1px solid #e9ecef;padding-bottom:4px">' + escHtml(group.group) + '</div>');
            }
            var $checkList = $('<div class="row g-1"></div>');
            $.each(group.items, function(j, amenity) {
                var isChecked = (amenityIds.indexOf(amenity.id) !== -1) ? ' checked' : '';
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
        $.each(categories, function(i, cat) {
            var isChecked = (categoryIds.indexOf(cat.id) !== -1) ? ' checked' : '';
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
        $('#owner-edit-form').off('submit.tags').on('submit.tags', function() {
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

    $('#add-day-btn').on('click', function() { addDayRow('', ''); });

    $('#owner-edit-form').on('submit.itinerary', function() {
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

    (function populateItinerary() {
        @if($typeVal === 'tour')
        var itineraryData;
        if (oldInput && oldInput['detail'] && oldInput['detail']['itinerary']) {
            itineraryData = oldInput['detail']['itinerary'];
        } else if (Array.isArray(dbDetailValues['itinerary'])) {
            itineraryData = dbDetailValues['itinerary'];
        } else {
            itineraryData = [];
        }
        if (Array.isArray(itineraryData) && itineraryData.length > 0) {
            $.each(itineraryData, function(i, day) {
                addDayRow(typeof day === 'object' ? (day['title'] || '') : '', typeof day === 'object' ? (day['description'] || '') : '');
            });
        } else {
            syncItineraryEmpty();
        }
        @else
        syncItineraryEmpty();
        @endif
    }());

    // ---- AJAX ----

    var currentRequest = null;

    function loadTypeData(typeVal) {
        if (currentRequest) currentRequest.abort();
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

    // Load type data on page load (type is fixed in edit mode)
    loadTypeData(currentType);

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

    // =================================================================
    // MEDIA MANAGEMENT
    // =================================================================
    var uploadUrl        = '{{ route('owner.listings.media.store', $listing->slug) }}';
    var youtubeUrl       = '{{ route('owner.listings.media.youtube', $listing->slug) }}';
    var baseMediaUrl     = '{{ url('owner/listings/' . $listing->slug . '/media') }}';
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
                    appendPhotoThumb(res.id, res.url);
                    $('#media-empty-msg').remove();
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.errors
                        ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                        : 'Ошибка загрузки.';
                    $status.text(msg).addClass('text-danger').removeClass('text-muted');
                },
                complete: function () {
                    done++;
                    $bar.css('width', Math.round(done / arr.length * 100) + '%');
                    $status.text(done + '/' + arr.length + ' загружено').removeClass('text-danger').addClass('text-muted');
                    if (done === arr.length) {
                        setTimeout(function () { $progress.addClass('d-none'); $bar.css('width', '0%'); }, 1500);
                    }
                }
            });
        });
    }

    function appendPhotoThumb(id, url) {
        var coverUrl  = baseMediaUrl + '/' + id + '/cover';
        var deleteUrl = baseMediaUrl + '/' + id;
        var html =
            '<div class="col-4" id="media-item-' + id + '">' +
                '<div class="position-relative rounded overflow-hidden" style="aspect-ratio:1">' +
                    '<img src="' + url + '" alt="фото" class="w-100 h-100" style="object-fit:cover">' +
                    '<button type="button" class="btn btn-sm btn-warning position-absolute bottom-0 start-0 m-1 p-0 px-1 media-cover-btn"' +
                        ' style="font-size:0.6rem;line-height:1.4"' +
                        ' data-media-id="' + id + '"' +
                        ' data-cover-url="' + coverUrl + '"' +
                        ' title="Сделать обложкой"><i class="ph ph-star"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 px-1 media-delete-btn"' +
                        ' style="font-size:0.65rem;line-height:1.4"' +
                        ' data-media-id="' + id + '"' +
                        ' data-delete-url="' + deleteUrl + '"' +
                        ' title="Удалить"><i class="ph ph-x"></i></button>' +
                '</div>' +
            '</div>';
        $('#media-grid').append(html);
    }

    // ── Delete ──────────────────────────────────────────────────────
    $(document).on('click', '.media-delete-btn', function () {
        var $btn      = $(this);
        var mediaId   = $btn.data('media-id');
        var deleteUrl = $btn.data('delete-url');

        if (!confirm('Удалить этот медиафайл?')) return;

        $.ajax({
            url: deleteUrl,
            method: 'POST',
            data: { _token: csrfToken, _method: 'DELETE' },
            success: function () {
                $('#media-item-' + mediaId).remove();
                if ($('#media-grid .col-4').length === 0) {
                    $('#media-grid').append('<div class="col-12 text-center py-2 text-muted small" id="media-empty-msg"><i class="ph ph-image me-1"></i>Нет медиафайлов</div>');
                }
            },
            error: function () { alert('Не удалось удалить. Попробуйте снова.'); }
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
                $('#media-grid .badge').remove();
                $('#media-grid .media-cover-btn').each(function () {
                    $(this).show();
                    $(this).find('i').css('color', '');
                });
                $('#media-grid .ring-cover').removeClass('ring-cover');

                var $thumb = $('#media-item-' + mediaId + ' .position-relative');
                $thumb.addClass('ring-cover');
                $btn.hide();
                $thumb.append('<span class="position-absolute bottom-0 start-0 m-1 badge bg-warning text-dark" style="font-size:0.55rem"><i class="ph ph-star me-1"></i>Обложка</span>');
            },
            error: function () { alert('Не удалось установить обложку. Попробуйте снова.'); }
        });
    });

    // ── YouTube ─────────────────────────────────────────────────────
    $('#youtube-add-btn').on('click', function () {
        var url = $('#youtube-url-input').val().trim();
        var $fb = $('#youtube-feedback');

        if (!url) {
            $fb.text('Введите ссылку на YouTube.').removeClass('text-success d-none').addClass('text-danger');
            return;
        }

        function doAdd() {
            $.ajax({
                url: youtubeUrl,
                method: 'POST',
                data: { _token: csrfToken, youtube_url: url },
                success: function (res) {
                    existingYoutubeId = res.id;
                    $fb.text('Видео сохранено.').removeClass('text-danger d-none').addClass('text-success');
                    appendYoutubeThumb(res.id, url);
                    $('#media-empty-msg').remove();
                    $('#youtube-add-btn').text('Обновить');
                    setTimeout(function () { $fb.addClass('d-none'); }, 2000);
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error :
                              (xhr.responseJSON && xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : 'Ошибка добавления видео.');
                    $fb.text(msg).removeClass('text-success d-none').addClass('text-danger');
                }
            });
        }

        if (existingYoutubeId) {
            var deleteUrl = baseMediaUrl + '/' + existingYoutubeId;
            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: { _token: csrfToken, _method: 'DELETE' },
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
        var deleteUrl = baseMediaUrl + '/' + id;
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
                        ' title="Удалить"><i class="ph ph-x"></i></button>' +
                '</div>' +
                '<div class="text-truncate small text-muted mt-1 px-1" style="font-size:0.65rem">' + short + '</div>' +
            '</div>';
        $('#media-grid').append(html);
    }

});
</script>
@endsection
