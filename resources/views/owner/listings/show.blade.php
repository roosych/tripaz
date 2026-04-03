@extends('layouts.owner')

@section('title', 'Объявление')

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
        'home'       => ['label' => 'Home',        'icon' => 'ph-house',       'color' => '#0dcaf0'],
        'tour'       => ['label' => 'Tour',        'icon' => 'ph-map-trifold',         'color' => '#198754'],
        'activity'   => ['label' => 'Activity',    'icon' => 'ph-lightning',   'color' => '#ffc107'],
        'guide'      => ['label' => 'Guide',       'icon' => 'ph-identification-card','color' => '#6c757d'],
        'restaurant' => ['label' => 'Restaurant',  'icon' => 'ph-fork-knife',   'color' => '#dc3545'],
    ];
    $tc = $typeLabelMap[$typeVal] ?? ['label' => ucfirst($typeVal), 'icon' => 'ph-tag', 'color' => '#6c757d'];

    $azTrans = $listing->translations->firstWhere('locale', 'az');
    $ruTrans = $listing->translations->firstWhere('locale', 'ru');
    $enTrans = $listing->translations->firstWhere('locale', 'en');

    $title = $azTrans?->title ?? $ruTrans?->title ?? $enTrans?->title ?? '(Без названия)';

    $canSubmit   = in_array($statusVal, ['draft', 'rejected']);
    $canEdit     = in_array($statusVal, ['draft', 'rejected', 'pending_review']);
    $canPause    = $statusVal === 'published';
    $canActivate = $statusVal === 'suspended';
@endphp

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center gap-3 flex-wrap">
    <a href="{{ route('owner.listings.index') }}" class="btn btn-sm btn-outline-secondary" title="Назад">
        <i class="ph ph-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="h4 fw-bold mb-0">{{ $title }}</h1>
            <x-status-badge :status="$statusVal" />
        </div>
        <p class="text-muted mb-0 small mt-1">{{ $listing->slug }}</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        @if($statusVal === 'published')
            <a href="{{ route('listings.show', $listing->slug) }}"
               target="_blank"
               class="btn btn-outline-secondary btn-sm">
                <i class="ph ph-eye me-1"></i>На сайте
            </a>
        @endif
        @if($canEdit)
            <a href="{{ route('owner.listings.edit', $listing->slug) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="ph ph-pencil-simple me-1"></i>Редактировать
            </a>
        @endif
        @if($canSubmit)
            <form method="POST" action="{{ route('owner.listings.submit', $listing->slug) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="ph ph-paper-plane-tilt me-1"></i>Отправить на проверку
                </button>
            </form>
        @endif
    </div>
</div>

{{-- ================================================================ --}}
{{-- FLASH MESSAGES                                                    --}}
{{-- ================================================================ --}}
@if(session('success'))
    <div class="alert alert-success d-flex gap-2 align-items-center mt-3">
        <i class="ph ph-check-circle flex-shrink-0"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger d-flex gap-2 align-items-center mt-3">
        <i class="ph ph-warning-circle flex-shrink-0"></i>
        {{ session('error') }}
    </div>
@endif

<div class="row g-4 mt-1">

    {{-- -------------------------------------------------------- --}}
    {{-- LEFT: Main details                                       --}}
    {{-- -------------------------------------------------------- --}}
    <div class="col-lg-8">

        {{-- Status notice --}}
        @if($statusVal === 'pending_review')
            <div class="alert alert-info d-flex gap-2 align-items-start mb-4">
                <i class="ph ph-hourglass flex-shrink-0 mt-1"></i>
                <div>
                    <div class="fw-semibold">На проверке</div>
                    <div class="small">Ваше объявление находится на рассмотрении. Мы уведомим вас о результате.</div>
                </div>
            </div>
        @elseif($statusVal === 'rejected')
            <div class="alert alert-danger d-flex gap-2 align-items-start mb-4">
                <i class="ph ph-x-circle flex-shrink-0 mt-1"></i>
                <div>
                    <div class="fw-semibold">Отклонено</div>
                    <div class="small">Объявление было отклонено. Отредактируйте его и повторно отправьте на проверку.</div>
                </div>
            </div>
        @elseif($statusVal === 'awaiting_payment')
            <div class="alert alert-warning d-flex gap-2 align-items-start mb-4">
                <i class="ph ph-credit-card flex-shrink-0 mt-1"></i>
                <div>
                    <div class="fw-semibold">Требуется оплата</div>
                    <div class="small">Для публикации этого объявления необходима оплата.
                        <a href="{{ route('owner.listings.payment.show', $listing) }}" class="fw-semibold">Перейти к оплате →</a>
                    </div>
                </div>
            </div>
        @elseif($statusVal === 'draft')
            <div class="alert alert-secondary d-flex gap-2 align-items-start mb-4">
                <i class="ph ph-pencil-simple flex-shrink-0 mt-1"></i>
                <div>
                    <div class="fw-semibold">Черновик</div>
                    <div class="small">Объявление сохранено как черновик. Отредактируйте и отправьте на проверку, когда будете готовы.</div>
                </div>
            </div>
        @elseif($statusVal === 'suspended')
            <div class="alert alert-warning d-flex gap-2 align-items-start mb-4">
                <i class="ph ph-pause-circle flex-shrink-0 mt-1"></i>
                <div>
                    <div class="fw-semibold">Приостановлено</div>
                    <div class="small">Объявление временно скрыто с сайта. Нажмите «Активировать», чтобы снова опубликовать его.</div>
                </div>
            </div>
        @endif

        {{-- Translations --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-translate me-2 text-primary"></i>Заголовки &amp; описание
                </h6>
            </div>
            <div class="card-body">
                @foreach([
                    ['trans' => $azTrans, 'lang' => 'AZ', 'name' => 'Азербайджанский'],
                    ['trans' => $ruTrans, 'lang' => 'RU', 'name' => 'Русский'],
                    ['trans' => $enTrans, 'lang' => 'EN', 'name' => 'Английский'],
                ] as $row)
                    @if($row['trans'])
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-light text-dark border" style="font-size:0.7rem;font-weight:700">{{ $row['lang'] }}</span>
                                <span class="text-muted small">{{ $row['name'] }}</span>
                            </div>
                            <div class="fw-semibold">{{ $row['trans']->title }}</div>
                            @if($row['trans']->description)
                                <div class="text-muted small mt-1" style="white-space:pre-line">{{ $row['trans']->description }}</div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Contact info --}}
        @if($listing->contact_email || $listing->contact_phone || $listing->website_url)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-phone me-2 text-primary"></i>Контактная информация
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0" style="font-size:0.9rem">
                        @if($listing->contact_email)
                            <dt class="col-sm-4 text-muted fw-normal">Email</dt>
                            <dd class="col-sm-8">
                                <a href="mailto:{{ $listing->contact_email }}">{{ $listing->contact_email }}</a>
                            </dd>
                        @endif
                        @if($listing->contact_phone)
                            <dt class="col-sm-4 text-muted fw-normal">Телефон</dt>
                            <dd class="col-sm-8">{{ $listing->contact_phone }}</dd>
                        @endif
                        @if($listing->website_url)
                            <dt class="col-sm-4 text-muted fw-normal">Веб-сайт</dt>
                            <dd class="col-sm-8">
                                <a href="{{ $listing->website_url }}" target="_blank" rel="noopener">{{ $listing->website_url }}</a>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        {{-- Amenities --}}
        @if($listing->amenities->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-check-square me-2 text-primary"></i>Удобства
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($listing->amenities->groupBy('group') as $group => $items)
                        @if($group)
                            <div class="text-muted fw-semibold small text-uppercase mb-2"
                                 style="font-size:0.7rem;letter-spacing:0.8px;border-bottom:1px solid #e9ecef;padding-bottom:4px">
                                {{ $group }}
                            </div>
                        @endif
                        <div class="row g-1 mb-3">
                            @foreach($items as $amenity)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 small text-muted">
                                        <i class="ph {{ $amenity->icon ?? 'ph-check' }} text-success"></i>
                                        {{ $amenity->getTranslation('name', 'az') ?: $amenity->getTranslation('name', 'en') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- -------------------------------------------------------- --}}
    {{-- RIGHT: Sidebar                                           --}}
    {{-- -------------------------------------------------------- --}}
    <div class="col-lg-4">

        {{-- Info card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-info me-2 text-primary"></i>Информация
                </h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0" style="font-size:0.85rem">
                    <dt class="col-6 text-muted fw-normal">Тип</dt>
                    <dd class="col-6">
                        <i class="ph {{ $tc['icon'] }} me-1" style="color:{{ $tc['color'] }}"></i>
                        {{ $tc['label'] }}
                    </dd>

                    <dt class="col-6 text-muted fw-normal">Статус</dt>
                    <dd class="col-6"><x-status-badge :status="$statusVal" /></dd>

                    @if($listing->location?->region)
                        <dt class="col-6 text-muted fw-normal">Регион</dt>
                        <dd class="col-6">{{ $listing->location->region->getTranslation('name', 'az') }}</dd>
                    @endif

                    <dt class="col-6 text-muted fw-normal">Создано</dt>
                    <dd class="col-6">{{ $listing->created_at->format('d M Y') }}</dd>

                    <dt class="col-6 text-muted fw-normal">Обновлено</dt>
                    <dd class="col-6">{{ $listing->updated_at->format('d M Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Categories --}}
        @if($listing->categories->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-tag me-2 text-primary"></i>Категории
                    </h6>
                </div>
                <div class="card-body d-flex flex-wrap gap-1">
                    @foreach($listing->categories as $category)
                        <span class="badge bg-light text-dark border" style="font-size:0.78rem">
                            {{ $category->getTranslation('name', 'az') ?: $category->getTranslation('name', 'en') }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Actions card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column gap-2">
                @if($canSubmit)
                    <form method="POST" action="{{ route('owner.listings.submit', $listing->slug) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success w-100">
                            <i class="ph ph-paper-plane-tilt me-2"></i>Отправить на проверку
                        </button>
                    </form>
                @endif

                @if($canEdit)
                    <a href="{{ route('owner.listings.edit', $listing->slug) }}" class="btn btn-primary w-100">
                        <i class="ph ph-pencil-simple me-2"></i>Редактировать
                    </a>
                @endif

                @if($statusVal === 'awaiting_payment')
                    <a href="{{ route('owner.listings.payment.show', $listing) }}" class="btn btn-warning w-100">
                        <i class="ph ph-credit-card me-2"></i>Оплатить
                    </a>
                @endif

                @if($canPause)
                    <form method="POST" action="{{ route('owner.listings.toggle-status', $listing->slug) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-warning w-100"
                                onclick="return confirm('Приостановить объявление? Оно будет скрыто с сайта.')">
                            <i class="ph ph-pause-circle me-2"></i>Приостановить
                        </button>
                    </form>
                @endif

                @if($canActivate)
                    <form method="POST" action="{{ route('owner.listings.toggle-status', $listing->slug) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Активировать объявление? Оно снова появится на сайте.')">
                            <i class="ph ph-play-circle me-2"></i>Активировать
                        </button>
                    </form>
                @endif

                @if($statusVal === 'published')
                    <a href="{{ route('listings.show', $listing->slug) }}" target="_blank" class="btn btn-outline-secondary w-100">
                        <i class="ph ph-eye me-2"></i>Просмотр на сайте
                    </a>
                @endif

                <a href="{{ route('owner.listings.index') }}" class="btn btn-link text-muted w-100">
                    К списку объявлений
                </a>
            </div>
        </div>

    </div>
</div>

@endsection
