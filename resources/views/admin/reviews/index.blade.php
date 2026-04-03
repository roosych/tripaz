@extends('layouts.admin')

@section('title', 'Модерация отзывов')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-star me-2" style="color:#dc3545"></i>Модерация отзывов
        </h1>
        <p class="text-muted mb-0 small mt-1">Одобряйте или отклоняйте отзывы гостей перед их публикацией</p>
    </div>
    @if(isset($reviews) && method_exists($reviews, 'total'))
        <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
            {{ $reviews->total() }} всего
        </span>
    @endif
</div>

{{-- ================================================================ --}}
{{-- FILTER TABS                                                       --}}
{{-- ================================================================ --}}
@php
    $currentFilter = request('filter', '');
@endphp

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $currentFilter === '' ? 'active fw-semibold' : '' }}"
           href="{{ route('admin.reviews.index') }}">
            Все
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $currentFilter === 'pending' ? 'active fw-semibold' : '' }}"
           href="{{ route('admin.reviews.index', ['filter' => 'pending']) }}">
            Ожидает
            @if(isset($pendingReviewsCount) && $pendingReviewsCount > 0)
                <span class="badge bg-warning text-dark ms-1 rounded-pill" style="font-size:0.65rem">
                    {{ $pendingReviewsCount }}
                </span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $currentFilter === 'approved' ? 'active fw-semibold' : '' }}"
           href="{{ route('admin.reviews.index', ['filter' => 'approved']) }}">
            Одобрено
        </a>
    </li>
</ul>

{{-- ================================================================ --}}
{{-- REVIEWS TABLE                                                     --}}
{{-- ================================================================ --}}
@if(isset($reviews) && $reviews->isNotEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="min-width:160px">Объявление</th>
                            <th class="py-3">Автор</th>
                            <th class="py-3" style="width:120px">Рейтинг</th>
                            <th class="py-3" style="min-width:200px">Комментарий</th>
                            <th class="py-3">Статус</th>
                            <th class="py-3">Дата</th>
                            <th class="pe-3 py-3 text-end" style="min-width:160px">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            @php
                                $isApproved = isset($review->is_approved) ? $review->is_approved : ($review->status === 'approved');
                                $listingTitle = $review->listing?->translations->firstWhere('locale', 'az')?->title
                                             ?? $review->listing?->translations->firstWhere('locale', 'en')?->title
                                             ?? ($review->listing?->slug ?? '—');
                            @endphp
                            <tr class="{{ !$isApproved ? 'table-warning' : '' }}">

                                {{-- Listing --}}
                                <td class="ps-3">
                                    @if($review->listing)
                                        <a href="{{ route('listings.show', $review->listing->slug) }}"
                                           target="_blank"
                                           class="text-decoration-none fw-semibold text-dark"
                                           style="font-size:0.875rem">
                                            {{ $listingTitle }}
                                            <i class="ph ph-link ms-1 text-muted" style="font-size:0.7rem"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>

                                {{-- Reviewer --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white flex-shrink-0"
                                             style="width:26px;height:26px;font-size:0.7rem;font-weight:700">
                                            {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
                                        </div>
                                        <span style="font-size:0.875rem">{{ $review->user?->name ?? 'Гость' }}</span>
                                    </div>
                                </td>

                                {{-- Rating --}}
                                <td>
                                    <div class="d-flex align-items-center gap-1" style="font-size:0.82rem">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="ph {{ $i <= ($review->rating ?? 0) ? 'ph-star text-warning' : 'ph-star text-muted' }}"></i>
                                        @endfor
                                        <span class="ms-1 text-muted">{{ $review->rating ?? '—' }}</span>
                                    </div>
                                </td>

                                {{-- Comment --}}
                                <td>
                                    <p class="mb-0 text-muted" style="font-size:0.82rem; max-width:240px">
                                        {{ Str::limit($review->comment ?? '', 90) }}
                                    </p>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if($isApproved)
                                        <span class="badge bg-success rounded-pill" style="font-size:0.68rem">Одобрено</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill" style="font-size:0.68rem">Ожидает</span>
                                    @endif
                                </td>

                                {{-- Date --}}
                                <td class="text-muted small">
                                    {{ $review->created_at->format('d M Y') }}
                                </td>

                                {{-- Actions --}}
                                <td class="pe-3">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap action-btn-group">

                                        {{-- Approve (show if not already approved) --}}
                                        @if(!$isApproved)
                                            <form method="POST"
                                                  action="{{ route('admin.reviews.approve', $review) }}"
                                                  style="display:inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="ph ph-check"></i> Одобрить
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Reject (always show) --}}
                                        <form method="POST"
                                              action="{{ route('admin.reviews.reject', $review) }}"
                                              style="display:inline"
                                              onsubmit="return confirm('Отклонить этот отзыв?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="ph ph-x"></i> Отклонить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($reviews->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-center py-3">
                {{ $reviews->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-star"
                title="Отзывы не найдены"
                message="Нет отзывов, соответствующих текущему фильтру." />
        </div>
    </div>

@endif

@endsection
