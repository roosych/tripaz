@extends('layouts.owner')

@section('title', 'Дашборд')

@section('head')
<style>
    /* ── Stat cards ── */
    .stat-card {
        border: 0;
        border-radius: .75rem;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        transition: box-shadow .15s;
    }
    .stat-card:hover {
        box-shadow: 0 4px 14px rgba(0,0,0,.11);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: .625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .stat-label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #6c757d;
    }
    .stat-value {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: #1a1a2e;
    }
    .stat-sub {
        font-size: .78rem;
        color: #6c757d;
        margin-top: .15rem;
    }

    /* ── Quick-action buttons ── */
    .quick-action-btn {
        border-radius: .625rem;
        padding: .65rem 1rem;
        font-size: .875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: .55rem;
        text-decoration: none;
        transition: background-color .15s, transform .1s;
    }
    .quick-action-btn:hover {
        transform: translateY(-1px);
    }

    /* ── Recent tables ── */
    .section-card {
        border: 0;
        border-radius: .75rem;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
    }
    .section-card .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f0f0;
        padding: 1rem 1.25rem .75rem;
        font-weight: 700;
        font-size: .9rem;
        color: #1a1a2e;
    }

    /* ── Star rating mini ── */
    .stars-mini {
        color: #ffc107;
        font-size: .8rem;
        letter-spacing: -.5px;
    }
    .stars-mini.text-muted {
        color: #dee2e6 !important;
    }

    /* ── Status pills ── */
    .pill-draft           { background: #e9ecef; color: #495057; }
    .pill-pending_review  { background: #fff3cd; color: #856404; }
    .pill-published       { background: #d1e7dd; color: #0a3622; }
    .pill-rejected        { background: #f8d7da; color: #58151c; }
    .pill-suspended       { background: #343a40; color: #f8f9fa; }
</style>
@endsection

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-gauge me-2 text-primary"></i>Дашборд
        </h1>
        <p class="text-muted mb-0 small mt-1">
            С возвращением, <strong>{{ Auth::user()->name }}</strong>.
            Здесь представлен обзор ваших объявлений и активности.
        </p>
    </div>
    <a href="{{ route('owner.listings.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="ph ph-plus-circle"></i>
        Добавить объявление
    </a>
</div>

{{-- ================================================================ --}}
{{-- STAT CARDS                                                        --}}
{{-- ================================================================ --}}
<div class="row g-3 mb-4">

    {{-- Total Listings --}}
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon" style="background:rgba(13,110,253,.1); color:#0d6efd">
                    <i class="ph ph-list-checks"></i>
                </div>
                <div>
                    <div class="stat-label">Всего</div>
                    <div class="stat-value">{{ $totalListings }}</div>
                    <div class="stat-sub">Все объявления</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Published --}}
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon" style="background:rgba(25,135,84,.1); color:#198754">
                    <i class="ph ph-check-circle"></i>
                </div>
                <div>
                    <div class="stat-label">Опубликовано</div>
                    <div class="stat-value">{{ $publishedCount }}</div>
                    <div class="stat-sub">Активны на сайте</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Review --}}
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon" style="background:rgba(255,193,7,.12); color:#856404">
                    <i class="ph ph-hourglass"></i>
                </div>
                <div>
                    <div class="stat-label">На проверке</div>
                    <div class="stat-value">{{ $pendingCount }}</div>
                    <div class="stat-sub">Ожидают рассмотрения</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Drafts --}}
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon" style="background:rgba(108,117,125,.1); color:#6c757d">
                    <i class="ph ph-file-text"></i>
                </div>
                <div>
                    <div class="stat-label">Черновики</div>
                    <div class="stat-value">{{ $draftCount }}</div>
                    <div class="stat-sub">Ещё не отправлены</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Second row: Reviews + Average Rating --}}
<div class="row g-3 mb-4">

    {{-- Reviews --}}
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon" style="background:rgba(13,202,240,.1); color:#0dcaf0">
                    <i class="ph ph-chat-circle"></i>
                </div>
                <div>
                    <div class="stat-label">Отзывы</div>
                    <div class="stat-value">{{ $totalReviews }}</div>
                    <div class="stat-sub">Одобренные отзывы</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Average Rating --}}
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon" style="background:rgba(255,193,7,.12); color:#ffc107">
                    <i class="ph ph-star-half"></i>
                </div>
                <div>
                    <div class="stat-label">Ср. рейтинг</div>
                    <div class="stat-value">{{ $averageRating ?? '—' }}</div>
                    <div class="stat-sub">Из 5</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rejected (shown only if any) --}}
    @if($rejectedCount > 0)
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100 border border-danger border-opacity-25">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon" style="background:rgba(220,53,69,.1); color:#dc3545">
                    <i class="ph ph-x-circle"></i>
                </div>
                <div>
                    <div class="stat-label">Отклонено</div>
                    <div class="stat-value text-danger">{{ $rejectedCount }}</div>
                    <div class="stat-sub">Требует внимания</div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ================================================================ --}}
{{-- QUICK ACTIONS                                                     --}}
{{-- ================================================================ --}}
<div class="card section-card mb-4">
    <div class="card-header">
        <i class="ph ph-lightning me-1 text-warning"></i> Быстрые действия
    </div>
    <div class="card-body d-flex flex-wrap gap-2 py-3">
        <a href="{{ route('owner.listings.create') }}"
           class="quick-action-btn btn btn-primary">
            <i class="ph ph-plus-circle"></i> Новое объявление
        </a>
        <a href="{{ route('owner.listings.index') }}"
           class="quick-action-btn btn btn-outline-primary">
            <i class="ph ph-list-checks"></i> Все объявления
        </a>
        <a href="{{ route('owner.reviews.index') }}"
           class="quick-action-btn btn btn-outline-secondary">
            <i class="ph ph-chat-circle"></i> Отзывы
        </a>
        <a href="{{ route('owner.bookings.index') }}"
           class="quick-action-btn btn btn-outline-secondary">
            <i class="ph ph-calendar-check"></i> Бронирования
        </a>
        <a href="{{ route('listings.index') }}" target="_blank"
           class="quick-action-btn btn btn-outline-dark">
            <i class="ph ph-link"></i> Открыть сайт
        </a>
    </div>
</div>

{{-- ================================================================ --}}
{{-- RECENT LISTINGS + RECENT REVIEWS                                 --}}
{{-- ================================================================ --}}
<div class="row g-3">

    {{-- Recent Listings --}}
    <div class="col-lg-7">
        <div class="card section-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="ph ph-list-checks me-1"></i> Недавние объявления</span>
                <a href="{{ route('owner.listings.index') }}" class="btn btn-sm btn-outline-primary">
                    Смотреть все
                </a>
            </div>

            @if($recentListings->isEmpty())
                <div class="card-body">
                    <p class="text-muted mb-0 text-center small py-3">
                        Объявлений пока нет.
                        <a href="{{ route('owner.listings.create') }}">Создайте первое объявление.</a>
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-2 small fw-semibold" style="min-width:160px">Заголовок</th>
                                <th class="py-2 small fw-semibold">Тип</th>
                                <th class="py-2 small fw-semibold">Статус</th>
                                <th class="pe-3 py-2 small fw-semibold text-end">Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentListings as $listing)
                                @php
                                    $typeMap = [
                                        'hotel'      => ['label' => 'Hotel',      'class' => 'bg-primary'],
                                        'home'       => ['label' => 'Home',        'class' => 'bg-info text-dark'],
                                        'tour'       => ['label' => 'Tour',        'class' => 'bg-success'],
                                        'activity'   => ['label' => 'Activity',   'class' => 'bg-warning text-dark'],
                                        'guide'      => ['label' => 'Guide',       'class' => 'bg-secondary'],
                                        'restaurant' => ['label' => 'Restaurant', 'class' => 'bg-danger'],
                                    ];
                                    $typeVal   = $listing->type instanceof \App\Enums\ListingType ? $listing->type->value : (string)$listing->type;
                                    $typeBadge = $typeMap[$typeVal] ?? ['label' => ucfirst($typeVal), 'class' => 'bg-secondary'];
                                    $statusVal = $listing->status instanceof \App\Enums\ListingStatus ? $listing->status->value : (string)$listing->status;
                                    $title = $listing->translations->firstWhere('locale', 'az')?->title
                                          ?? $listing->translations->firstWhere('locale', 'ru')?->title
                                          ?? $listing->translations->firstWhere('locale', 'en')?->title
                                          ?? '(Untitled)';
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-dark text-truncate" style="max-width:200px; font-size:.875rem">
                                            {{ $title }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $typeBadge['class'] }} rounded-pill" style="font-size:.7rem">
                                            {{ $typeBadge['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $pillClass = match($statusVal) {
                                                'published'      => 'pill-published',
                                                'pending_review' => 'pill-pending_review',
                                                'draft'          => 'pill-draft',
                                                'rejected'       => 'pill-rejected',
                                                'suspended'      => 'pill-suspended',
                                                default          => 'pill-draft',
                                            };
                                            $pillLabel = match($statusVal) {
                                                'pending_review' => 'На проверке',
                                                default          => ucfirst(str_replace('_', ' ', $statusVal)),
                                            };
                                        @endphp
                                        <span class="badge rounded-pill {{ $pillClass }}" style="font-size:.7rem">
                                            {{ $pillLabel }}
                                        </span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('owner.listings.edit', $listing->slug) }}"
                                           class="btn btn-sm btn-outline-primary py-0 px-2"
                                           style="font-size:.78rem">
                                            <i class="ph ph-pencil-simple"></i> Редактировать
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Reviews --}}
    <div class="col-lg-5">
        <div class="card section-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="ph ph-chat-circle me-1"></i> Недавние отзывы</span>
                <a href="{{ route('owner.reviews.index') }}" class="btn btn-sm btn-outline-secondary">
                    Смотреть все
                </a>
            </div>

            @if($recentReviews->isEmpty())
                <div class="card-body">
                    <p class="text-muted mb-0 text-center small py-3">Отзывов пока нет.</p>
                </div>
            @else
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($recentReviews as $review)
                            @php
                                $reviewTitle = $review->listing?->translations->firstWhere('locale', 'az')?->title
                                           ?? $review->listing?->translations->firstWhere('locale', 'ru')?->title
                                           ?? $review->listing?->translations->firstWhere('locale', 'en')?->title
                                           ?? '(Untitled)';
                                $rating = (int) $review->rating;
                            @endphp
                            <li class="list-group-item px-3 py-2 border-0 border-bottom">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div style="min-width:0">
                                        <div class="fw-semibold text-dark text-truncate" style="font-size:.83rem; max-width:200px">
                                            {{ $reviewTitle }}
                                        </div>
                                        <div class="text-muted" style="font-size:.75rem">
                                            by {{ $review->user?->name ?? 'Гость' }}
                                            &middot; {{ $review->created_at->diffForHumans() }}
                                        </div>
                                        @if($review->body)
                                            <div class="text-muted mt-1" style="font-size:.78rem; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical">
                                                {{ $review->body }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <div class="stars-mini">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="ph ph-star"
                                                   @if($i <= $rating) style="color:#ffc107" @else style="color:#dee2e6" @endif></i>
                                            @endfor
                                        </div>
                                        <div class="small text-muted">{{ $rating }}/5</div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
