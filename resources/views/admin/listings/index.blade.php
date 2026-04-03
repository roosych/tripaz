@extends('layouts.admin')

@section('title', 'Модерация объявлений')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-buildings me-2" style="color:#dc3545"></i>Объявления
        </h1>
        <p class="text-muted mb-0 small mt-1">Проверяйте, одобряйте, отклоняйте и управляйте всеми объявлениями</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(isset($listings) && method_exists($listings, 'total'))
            <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
                {{ $listings->total() }} всего
            </span>
        @endif
        <a href="{{ route('admin.listings.create') }}" class="btn btn-sm btn-danger">
            <i class="ph ph-plus-circle me-1"></i>Добавить объявление
        </a>
    </div>
</div>

{{-- ================================================================ --}}
{{-- SEARCH / FILTER BAR                                               --}}
{{-- ================================================================ --}}
@php
    $hasFilters = ($q ?? '') !== '' || ($typeFilter ?? '') !== '' || ($categoryId ?? '') !== '';
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.listings.index') }}">
            {{-- Preserve the active status tab --}}
            @if(($currentStatus ?? '') !== '')
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif

            <div class="row g-2 align-items-center">

                {{-- Search (title / slug / ID) --}}
                <div class="col-sm-5 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="ph ph-magnifying-glass text-muted"></i></span>
                        <input type="text"
                               class="form-control"
                               name="q"
                               value="{{ $q ?? '' }}"
                               placeholder="Поиск по заголовку, слагу или ID…"
                               autocomplete="off">
                    </div>
                </div>

                {{-- Type --}}
                <div class="col-sm-4 col-md-2">
                    <select class="form-select" id="filter-type" name="type">
                        <option value="">Все типы</option>
                        @foreach($listingTypes as $lt)
                            <option value="{{ $lt->value }}" {{ ($typeFilter ?? '') === $lt->value ? 'selected' : '' }}>
                                {{ $lt->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Category --}}
                <div class="col-sm-4 col-md-3">
                    <select class="form-select" id="filter-category" name="category_id">
                        <option value="">Все категории</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)($categoryId ?? '') === (string)$cat->id ? 'selected' : '' }}>
                                {{ $cat->getTranslation('name', 'az') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-funnel me-1"></i>Фильтр
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('admin.listings.index', ($currentStatus ?? '') !== '' ? ['status' => $currentStatus] : []) }}"
                           class="btn btn-outline-secondary ms-1"
                           title="Clear filters">
                            <i class="ph ph-x"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ================================================================ --}}
{{-- STATUS FILTER TABS                                                --}}
{{-- ================================================================ --}}
@php
    $filterTabs = [
        ''               => 'Все',
        'pending_review' => 'Ожидает',
        'published'      => 'Опубликовано',
        'rejected'       => 'Отклонено',
        'suspended'      => 'Приостановлено',
        'draft'          => 'Черновик',
    ];

    // Build the extra params (q, type, category_id) to preserve when switching tabs
    $tabExtraParams = array_filter([
        'q'           => $q ?? '',
        'type'        => $typeFilter ?? '',
        'category_id' => $categoryId ?? '',
    ], fn ($v) => $v !== '');
@endphp

<ul class="nav nav-tabs mb-3">
    @foreach($filterTabs as $statusKey => $statusLabel)
        @php
            $tabParams = $statusKey !== ''
                ? array_merge(['status' => $statusKey], $tabExtraParams)
                : $tabExtraParams;
        @endphp
        <li class="nav-item">
            <a class="nav-link {{ ($currentStatus ?? '') === $statusKey ? 'active fw-semibold' : '' }}"
               href="{{ route('admin.listings.index', $tabParams ?: []) }}">
                {{ $statusLabel }}
                @if($statusKey === 'pending_review' && isset($pendingListingsCount) && $pendingListingsCount > 0)
                    <span class="badge bg-warning text-dark ms-1 rounded-pill" style="font-size:0.65rem">
                        {{ $pendingListingsCount }}
                    </span>
                @endif
            </a>
        </li>
    @endforeach
</ul>

{{-- Result count when filters are active --}}
@if($hasFilters)
    <p class="text-muted small mb-2">
        Найдено <strong>{{ $listings->total() }}</strong> объявлени{{ $listings->total() === 1 ? 'е' : 'й' }},
        соответствующих текущим фильтрам.
    </p>
@endif

{{-- ================================================================ --}}
{{-- LISTINGS TABLE                                                    --}}
{{-- ================================================================ --}}
@if(isset($listings) && $listings->isNotEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width:50px">#</th>
                            <th class="py-3" style="min-width:200px">Заголовок</th>
                            <th class="py-3">Владелец</th>
                            <th class="py-3">Тип</th>
                            <th class="py-3">Статус</th>
                            <th class="py-3">Создано</th>
                            <th class="pe-3 py-3 text-end" style="min-width:200px">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listings as $listing)
                            @php
                                $statusVal = $listing->status instanceof \App\Enums\ListingStatus
                                    ? $listing->status->value
                                    : (string)$listing->status;

                                $typeVal = $listing->type instanceof \App\Enums\ListingType
                                    ? $listing->type->value
                                    : (string)$listing->type;

                                $typeBadgeMap = [
                                    'hotel'      => ['label' => 'Hotel',      'class' => 'bg-primary'],
                                    'home'       => ['label' => 'Home',        'class' => 'bg-info text-dark'],
                                    'tour'       => ['label' => 'Tour',        'class' => 'bg-success'],
                                    'activity'   => ['label' => 'Activity',   'class' => 'bg-warning text-dark'],
                                    'guide'      => ['label' => 'Guide',       'class' => 'bg-secondary'],
                                    'restaurant' => ['label' => 'Restaurant', 'class' => 'bg-danger'],
                                ];
                                $typeBadge = $typeBadgeMap[$typeVal] ?? ['label' => ucfirst($typeVal), 'class' => 'bg-secondary'];

                                $title = $listing->translations->firstWhere('locale', 'az')?->title
                                      ?? $listing->translations->firstWhere('locale', 'en')?->title
                                      ?? '(Untitled)';
                            @endphp
                            <tr class="{{ $statusVal === 'pending_review' ? 'table-warning' : '' }}">

                                {{-- ID --}}
                                <td class="ps-3 text-muted small">{{ $listing->id }}</td>

                                {{-- Title --}}
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size:0.9rem">
                                        {{ $title }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.75rem">
                                        {{ $listing->slug }}
                                    </div>
                                </td>

                                {{-- Owner --}}
                                <td>
                                    <div style="font-size:0.875rem">
                                        {{ $listing->user?->name ?? '—' }}
                                    </div>
                                    @if($listing->user?->email)
                                        <div class="text-muted" style="font-size:0.75rem">
                                            {{ $listing->user->email }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Type --}}
                                <td>
                                    <span class="badge {{ $typeBadge['class'] }} rounded-pill" style="font-size:0.7rem">
                                        {{ $typeBadge['label'] }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td>
                                    <x-status-badge :status="$statusVal" />
                                </td>

                                {{-- Created --}}
                                <td class="text-muted small">
                                    {{ $listing->created_at->format('d M Y') }}
                                </td>

                                {{-- Actions --}}
                                <td class="pe-3">
                                    <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap action-btn-group">

                                        {{-- View on public site --}}
                                        <a href="{{ route('listings.show', $listing->slug) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="View listing">
                                            <i class="ph ph-eye"></i>
                                        </a>

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.listings.edit', $listing) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit listing">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>

                                        {{-- Pending: Approve + Reject --}}
                                        @if($statusVal === 'pending_review')
                                            <form method="POST"
                                                  action="{{ route('admin.listings.approve', $listing) }}"
                                                  style="display:inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" title="Одобрить">
                                                    <i class="ph ph-check"></i> Одобрить
                                                </button>
                                            </form>

                                            <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal"
                                                    data-listing-id="{{ $listing->id }}"
                                                    data-listing-title="{{ $title }}"
                                                    title="Отклонить">
                                                <i class="ph ph-x"></i> Отклонить
                                            </button>
                                        @endif

                                        {{-- Published: Suspend --}}
                                        @if($statusVal === 'published')
                                            <form method="POST"
                                                  action="{{ route('admin.listings.suspend', $listing) }}"
                                                  style="display:inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-sm btn-warning"
                                                        title="Приостановить объявление"
                                                        onclick="return confirm('Приостановить это объявление?')">
                                                    <i class="ph ph-pause-circle"></i> Приостановить
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Suspended: Reinstate --}}
                                        @if($statusVal === 'suspended')
                                            <form method="POST"
                                                  action="{{ route('admin.listings.reinstate', $listing) }}"
                                                  style="display:inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-primary" title="Восстановить">
                                                    <i class="ph ph-arrow-clockwise"></i> Восстановить
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($listings->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-center py-3">
                {{ $listings->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-buildings"
                title="Объявлений не найдено"
                message="Нет объявлений, соответствующих выбранному фильтру." />
        </div>
    </div>

@endif

{{-- ================================================================ --}}
{{-- REJECT MODAL                                                      --}}
{{-- ================================================================ --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="rejectModalLabel">
                    <i class="ph ph-x-circle text-danger me-2"></i>Отклонить объявление
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body pt-2">
                    <p class="text-muted mb-2">
                        Отклонение: <strong id="rejectListingTitle">это объявление</strong>
                    </p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-semibold">
                            Причина отклонения <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control"
                                  id="rejection_reason"
                                  name="rejection_reason"
                                  rows="3"
                                  placeholder="Объясните, почему это объявление отклоняется..."
                                  required></textarea>
                        <div class="form-text">Эта причина будет отправлена владельцу объявления.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ph ph-x-circle me-1"></i>Подтвердить отклонение
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(function () {
    var apiUrl       = '{{ route('admin.api.listing-type-data') }}';
    var selectedCatId = '{{ $categoryId ?? '' }}';

    // All categories from server (shown when no type filter is active)
    @php
        $allCatsJs = $categories->map(function ($c) {
            return ['id' => $c->id, 'name_az' => $c->getTranslation('name', 'az')];
        });
    @endphp
    var allCategories = @json($allCatsJs);

    function populateCategorySelect(cats, selectedId) {
        var $sel = $('#filter-category').empty();
        $sel.append('<option value="">Все категории</option>');
        $.each(cats, function (i, cat) {
            var selected = String(cat.id) === String(selectedId) ? ' selected' : '';
            $sel.append(
                '<option value="' + cat.id + '"' + selected + '>' +
                $('<span>').text(cat.name_az).html() +
                '</option>'
            );
        });
    }

    var currentRequest = null;

    function loadCategoriesForType(typeVal) {
        if (currentRequest) { currentRequest.abort(); }

        if (!typeVal) {
            populateCategorySelect(allCategories, selectedCatId);
            return;
        }

        currentRequest = $.ajax({
            url:  apiUrl,
            method: 'GET',
            data: { type: typeVal },
        }).done(function (data) {
            populateCategorySelect(data.categories || [], selectedCatId);
        }).fail(function (xhr) {
            if (xhr.statusText !== 'abort') {
                populateCategorySelect(allCategories, selectedCatId);
            }
        });
    }

    $('#filter-type').on('change', function () {
        selectedCatId = ''; // reset selected category when type changes
        loadCategoriesForType($(this).val());
    });

    // On page load: if a type is already selected, filter categories immediately
    var initialType = $('#filter-type').val();
    if (initialType) {
        loadCategoriesForType(initialType);
    }
});
</script>
<script>
    // Populate reject modal with listing data
    $('#rejectModal').on('show.bs.modal', function (event) {
        var trigger = $(event.relatedTarget);
        var listingId = trigger.data('listing-id');
        var listingTitle = trigger.data('listing-title');

        $('#rejectListingTitle').text(listingTitle);
        // Build the reject route action using the listing ID
        $('#rejectForm').attr('action', '/admin/listings/' + listingId + '/reject');
    });
</script>
@endsection
