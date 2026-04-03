@extends('layouts.admin')

@section('title', 'Дашборд')

@section('head')
<style>
    .stat-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.15s, box-shadow 0.15s;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.10) !important;
    }
    .stat-card .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -1px;
    }
    .stat-card .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1.1rem 0.75rem;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.15s;
        border: 1.5px solid transparent;
        text-decoration: none;
        cursor: pointer;
    }
    .quick-action-btn i {
        font-size: 1.4rem;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.10);
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
    }
    .progress-thin {
        height: 6px;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-grid-four me-2" style="color:var(--admin-accent)"></i>Дашборд
        </h1>
        <p class="text-muted mb-0 small mt-1">
            С возвращением, <strong>{{ Auth::user()->name }}</strong> — {{ now()->format('l, d F Y') }}
        </p>
    </div>
    <a href="{{ route('admin.listings.create') }}" class="btn btn-danger d-flex align-items-center gap-2">
        <i class="ph ph-plus-circle"></i>
        <span>Добавить объявление</span>
    </a>
</div>

{{-- ================================================================ --}}
{{-- STAT CARDS                                                        --}}
{{-- ================================================================ --}}
<div class="row g-3 mb-4">

    {{-- Total Listings --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="stat-icon" style="background:rgba(13,110,253,0.12)">
                    <i class="ph ph-buildings" style="color:#0d6efd"></i>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($stats['listings_total']) }}</div>
                    <div class="stat-label text-muted">Всего объявлений</div>
                    @if($stats['listings_pending'] > 0)
                        <span class="status-pill mt-1 d-inline-flex" style="background:#fff3cd;color:#856404">
                            <i class="ph ph-clock" style="font-size:0.6rem"></i>
                            {{ $stats['listings_pending'] }} ожидает
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Published --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="stat-icon" style="background:rgba(25,135,84,0.12)">
                    <i class="ph ph-check-circle" style="color:#198754"></i>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($stats['listings_published']) }}</div>
                    <div class="stat-label text-muted">Опубликовано</div>
                    @if($stats['listings_total'] > 0)
                        @php $pct = round($stats['listings_published'] / $stats['listings_total'] * 100); @endphp
                        <span class="status-pill mt-1" style="background:rgba(25,135,84,0.10);color:#198754">
                            {{ $pct }}% от всего
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Users --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="stat-icon" style="background:rgba(108,117,125,0.12)">
                    <i class="ph ph-users-three" style="color:#6c757d"></i>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($stats['users_total']) }}</div>
                    <div class="stat-label text-muted">Пользователи</div>
                    <span class="status-pill mt-1" style="background:rgba(13,202,240,0.12);color:#087990">
                        <i class="ph ph-house" style="font-size:0.6rem"></i>
                        {{ $stats['hosts_total'] }} хостов
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Reviews --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="stat-icon" style="background:rgba(255,193,7,0.15)">
                    <i class="ph ph-star" style="color:#ffc107"></i>
                </div>
                <div>
                    <div class="stat-value text-dark">{{ number_format($stats['reviews_total']) }}</div>
                    <div class="stat-label text-muted">Отзывы</div>
                    @if($stats['reviews_pending'] > 0)
                        <span class="status-pill mt-1 d-inline-flex" style="background:#fff3cd;color:#856404">
                            <i class="ph ph-clock" style="font-size:0.6rem"></i>
                            {{ $stats['reviews_pending'] }} ожидает
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ================================================================ --}}
{{-- LISTINGS STATUS BREAKDOWN + QUICK ACTIONS                        --}}
{{-- ================================================================ --}}
<div class="row g-3 mb-4">

    {{-- Status Breakdown --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-chart-bar me-2 text-primary"></i>Разбивка по статусам объявлений
                </h6>
            </div>
            <div class="card-body pt-3">
                @php
                    $total = max($stats['listings_total'], 1);
                    $breakdown = [
                        'published'      => ['label' => 'Опубликовано',    'color' => '#198754', 'bg' => 'rgba(25,135,84,0.10)',  'val' => $stats['listings_published']],
                        'pending_review' => ['label' => 'Ожидает проверки','color' => '#ffc107', 'bg' => 'rgba(255,193,7,0.15)',  'val' => $stats['listings_pending']],
                        'draft'          => ['label' => 'Черновик',         'color' => '#6c757d', 'bg' => 'rgba(108,117,125,0.10)','val' => $stats['listings_draft']],
                        'rejected'       => ['label' => 'Отклонено',        'color' => '#dc3545', 'bg' => 'rgba(220,53,69,0.10)',  'val' => $stats['listings_rejected']],
                        'suspended'      => ['label' => 'Приостановлено',   'color' => '#fd7e14', 'bg' => 'rgba(253,126,20,0.10)', 'val' => $stats['listings_suspended']],
                    ];
                @endphp

                @foreach($breakdown as $status => $info)
                    @php $pct = round($info['val'] / $total * 100); @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="status-pill" style="background:{{ $info['bg'] }};color:{{ $info['color'] }}">
                                {{ $info['label'] }}
                            </span>
                            <span class="fw-semibold small">
                                {{ $info['val'] }}
                                <span class="text-muted fw-normal">({{ $pct }}%)</span>
                            </span>
                        </div>
                        <div class="progress progress-thin bg-light">
                            <div class="progress-bar progress-thin"
                                 style="width:{{ $pct }}%;background:{{ $info['color'] }};border-radius:4px">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-lightning me-2 text-warning"></i>Быстрые действия
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">

                    <div class="col-4 col-sm-4">
                        <a href="{{ route('admin.listings.create') }}"
                           class="quick-action-btn w-100 text-danger"
                           style="background:rgba(220,53,69,0.07);border-color:rgba(220,53,69,0.15)">
                            <i class="ph ph-plus-circle"></i>
                            Добавить объявление
                        </a>
                    </div>

                    <div class="col-4 col-sm-4">
                        <a href="{{ route('admin.listings.index', ['status' => 'pending_review']) }}"
                           class="quick-action-btn w-100 text-warning"
                           style="background:rgba(255,193,7,0.08);border-color:rgba(255,193,7,0.2)">
                            <i class="ph ph-clock"></i>
                            Ожидает
                            @if($stats['listings_pending'] > 0)
                                <span class="badge rounded-pill bg-warning text-dark" style="font-size:0.65rem">
                                    {{ $stats['listings_pending'] }}
                                </span>
                            @endif
                        </a>
                    </div>

                    <div class="col-4 col-sm-4">
                        <a href="{{ route('admin.listings.index') }}"
                           class="quick-action-btn w-100 text-primary"
                           style="background:rgba(13,110,253,0.07);border-color:rgba(13,110,253,0.15)">
                            <i class="ph ph-buildings"></i>
                            Все объявления
                        </a>
                    </div>

                    <div class="col-4 col-sm-4">
                        <a href="{{ route('admin.users.index') }}"
                           class="quick-action-btn w-100 text-secondary"
                           style="background:rgba(108,117,125,0.07);border-color:rgba(108,117,125,0.15)">
                            <i class="ph ph-users"></i>
                            Управление пользователями
                        </a>
                    </div>

                    <div class="col-4 col-sm-4">
                        <a href="{{ route('admin.reviews.index') }}"
                           class="quick-action-btn w-100"
                           style="color:#ffc107;background:rgba(255,193,7,0.07);border-color:rgba(255,193,7,0.2)">
                            <i class="ph ph-star"></i>
                            Отзывы
                            @if($stats['reviews_pending'] > 0)
                                <span class="badge rounded-pill bg-warning text-dark" style="font-size:0.65rem">
                                    {{ $stats['reviews_pending'] }}
                                </span>
                            @endif
                        </a>
                    </div>

                    <div class="col-4 col-sm-4">
                        <a href="{{ route('admin.promotions.index') }}"
                           class="quick-action-btn w-100"
                           style="color:#6f42c1;background:rgba(111,66,193,0.07);border-color:rgba(111,66,193,0.15)">
                            <i class="ph ph-rocket"></i>
                            Продвижение
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

{{-- ================================================================ --}}
{{-- RECENT LISTINGS                                                   --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-semibold mb-0">
            <i class="ph ph-clock me-2 text-secondary"></i>Недавние объявления
        </h6>
        <a href="{{ route('admin.listings.index') }}" class="btn btn-sm btn-outline-secondary">
            Смотреть все <i class="ph ph-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        @if($recentListings->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:0.875rem">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-2">Заголовок</th>
                        <th class="py-2">Владелец</th>
                        <th class="py-2">Тип</th>
                        <th class="py-2">Статус</th>
                        <th class="py-2">Создано</th>
                        <th class="pe-3 py-2 text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentListings as $listing)
                        @php
                            $statusVal = $listing->status instanceof \App\Enums\ListingStatus
                                ? $listing->status->value
                                : (string)$listing->status;
                            $typeVal = $listing->type instanceof \App\Enums\ListingType
                                ? $listing->type->value
                                : (string)$listing->type;
                            $typeBadgeMap = [
                                'hotel'      => ['label' => 'Hotel',      'class' => 'bg-primary'],
                                'home'       => ['label' => 'Home',       'class' => 'bg-info text-dark'],
                                'tour'       => ['label' => 'Tour',       'class' => 'bg-success'],
                                'activity'   => ['label' => 'Activity',   'class' => 'bg-warning text-dark'],
                                'guide'      => ['label' => 'Guide',      'class' => 'bg-secondary'],
                                'restaurant' => ['label' => 'Restaurant', 'class' => 'bg-danger'],
                            ];
                            $typeBadge = $typeBadgeMap[$typeVal] ?? ['label' => ucfirst($typeVal), 'class' => 'bg-secondary'];
                            $title = $listing->translations->firstWhere('locale', 'az')?->title
                                  ?? $listing->translations->firstWhere('locale', 'en')?->title
                                  ?? '(Untitled)';
                        @endphp
                        <tr class="{{ $statusVal === 'pending_review' ? 'table-warning' : '' }}">
                            <td class="ps-3">
                                <div class="fw-semibold text-truncate" style="max-width:200px">{{ $title }}</div>
                                <div class="text-muted" style="font-size:0.72rem">{{ $listing->slug }}</div>
                            </td>
                            <td class="text-muted">{{ $listing->user?->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $typeBadge['class'] }} rounded-pill" style="font-size:0.7rem">
                                    {{ $typeBadge['label'] }}
                                </span>
                            </td>
                            <td><x-status-badge :status="$statusVal" /></td>
                            <td class="text-muted">{{ $listing->created_at->format('d M Y') }}</td>
                            <td class="pe-3 text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($statusVal === 'pending_review')
                                        <form method="POST"
                                              action="{{ route('admin.listings.approve', $listing) }}"
                                              style="display:inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-xs btn-success" style="font-size:0.72rem;padding:0.2rem 0.55rem" title="Одобрить">
                                                <i class="ph ph-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.listings.edit', $listing) }}"
                                       class="btn btn-outline-primary"
                                       style="font-size:0.72rem;padding:0.2rem 0.55rem"
                                       title="Редактировать">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="p-4 text-center text-muted">
                <i class="ph ph-tray fs-2 d-block mb-2 opacity-25"></i>
                Объявлений пока нет.
            </div>
        @endif
    </div>
</div>

@endsection
