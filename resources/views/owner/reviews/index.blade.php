@extends('layouts.owner')

@section('title', 'Отзывы')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-chat-circle me-2 text-primary"></i>Отзывы на мои объявления
        </h1>
        <p class="text-muted mb-0 small mt-1">Посмотрите, что гости говорят о ваших объявлениях</p>
    </div>
</div>

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
                            <th class="py-3" style="width:110px">Рейтинг</th>
                            <th class="py-3" style="min-width:200px">Комментарий</th>
                            <th class="py-3">Статус</th>
                            <th class="pe-3 py-3">Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            @php
                                $isApproved = isset($review->is_approved) ? $review->is_approved : ($review->status === 'approved');
                                $reviewStatus = $isApproved ? 'approved' : 'pending';
                            @endphp
                            <tr>
                                {{-- Listing --}}
                                <td class="ps-3">
                                    @if($review->listing)
                                        <a href="{{ route('listings.show', $review->listing->slug) }}"
                                           target="_blank"
                                           class="text-decoration-none fw-semibold text-dark"
                                           style="font-size:0.88rem">
                                            {{ $review->listing->translations->firstWhere('locale','az')?->title
                                               ?? $review->listing->translations->firstWhere('locale','en')?->title
                                               ?? $review->listing->slug }}
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
                                             style="width:28px;height:28px;font-size:0.75rem;font-weight:700">
                                            {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
                                        </div>
                                        <span style="font-size:0.88rem">{{ $review->user?->name ?? 'Гость' }}</span>
                                    </div>
                                </td>

                                {{-- Rating --}}
                                <td>
                                    <div class="d-flex align-items-center gap-1" style="font-size:0.85rem">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="ph ph-star" @if($i <= ($review->rating ?? 0)) style="color:#ffc107" @else style="color:#dee2e6" @endif></i>
                                        @endfor
                                        <span class="ms-1 text-muted">{{ $review->rating ?? '—' }}</span>
                                    </div>
                                </td>

                                {{-- Comment --}}
                                <td>
                                    <p class="mb-0 text-muted" style="font-size:0.85rem; max-width:250px">
                                        {{ Str::limit($review->comment ?? '', 100) }}
                                    </p>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if($reviewStatus === 'approved')
                                        <span class="badge bg-success rounded-pill" style="font-size:0.7rem">Одобрено</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill" style="font-size:0.7rem">На проверке</span>
                                    @endif
                                </td>

                                {{-- Date --}}
                                <td class="pe-3 text-muted small">
                                    {{ $review->created_at->format('d M Y') }}
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
                {{ $reviews->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-chat-circle"
                title="Отзывов пока нет"
                message="Отзывы гостей на ваши объявления появятся здесь после их отправки и одобрения." />
        </div>
    </div>

@endif

@endsection
