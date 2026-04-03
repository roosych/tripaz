@extends('layouts.admin')

@section('title', 'Удобства')

@section('head')
<style>
    .drag-handle { cursor: grab; color: #adb5bd; }
    .drag-handle:active { cursor: grabbing; }
    .sortable-ghost { opacity: 0.4; background: #f8f9fa; }
    .sortable-chosen { background: #fff3cd; }
    #sort-status { transition: opacity 0.3s; }
</style>
@endsection

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-squares-four me-2" style="color:#dc3545"></i>Удобства
        </h1>
        <p class="text-muted mb-0 small mt-1">Управляйте опциями удобств, доступными для объявлений</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(isset($amenities) && method_exists($amenities, 'total'))
            <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
                {{ $amenities->total() }} всего
            </span>
        @endif
        <a href="{{ route('admin.amenity-groups.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph ph-stack me-1"></i>Группы
        </a>
        <span id="sort-status" class="text-muted small" style="opacity:0"></span>
        <a href="{{ route('admin.amenities.create') }}" class="btn btn-sm btn-danger">
            <i class="ph ph-plus-circle me-1"></i>Добавить удобство
        </a>
    </div>
</div>

{{-- ================================================================ --}}
{{-- FILTERS                                                           --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.amenities.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-sm-4 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="ph ph-magnifying-glass text-muted"></i></span>
                        <input type="text"
                               class="form-control"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Поиск по названию или группе..."
                               autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-3 col-md-2">
                    <select class="form-select" name="type">
                        <option value="">Все типы</option>
                        <option value="universal" {{ ($type ?? '') === 'universal' ? 'selected' : '' }}>Универсальный</option>
                        @foreach($types as $listingType)
                            <option value="{{ $listingType->value }}" {{ ($type ?? '') === $listingType->value ? 'selected' : '' }}>
                                {{ $listingType->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 col-md-2">
                    <select class="form-select" name="group">
                        <option value="">Все группы</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ ($groupId ?? '') == $g->id ? 'selected' : '' }}>
                                {{ $g->getTranslation('name', 'az') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-funnel me-1"></i>Фильтр
                    </button>
                    @if(($search ?? '') || ($type ?? '') || ($group ?? ''))
                        <a href="{{ route('admin.amenities.index') }}" class="btn btn-outline-secondary ms-1">
                            <i class="ph ph-x"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================ --}}
{{-- AMENITIES TABLE                                                   --}}
{{-- ================================================================ --}}
@if(isset($amenities) && $amenities->isNotEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width:36px"></th>
                            <th class="py-3" style="width:50px">#</th>
                            <th class="py-3">Иконка</th>
                            <th class="py-3" style="min-width:160px">Название (AZ)</th>
                            <th class="py-3">Название (EN)</th>
                            <th class="py-3">Группа</th>
                            <th class="py-3">Тип объявления</th>
                            <th class="pe-3 py-3 text-end" style="min-width:100px">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="amenities-sortable">
                        @foreach($amenities as $amenity)
                            @php
                                $typeVal = $amenity->listing_type instanceof \App\Enums\ListingType
                                    ? $amenity->listing_type->value
                                    : (string)$amenity->listing_type;

                                $typeBadgeMap = [
                                    'hotel'      => ['label' => 'Hotel',      'class' => 'bg-primary'],
                                    'home'       => ['label' => 'Home',        'class' => 'bg-info text-dark'],
                                    'tour'       => ['label' => 'Tour',        'class' => 'bg-success'],
                                    'activity'   => ['label' => 'Activity',   'class' => 'bg-warning text-dark'],
                                    'guide'      => ['label' => 'Guide',       'class' => 'bg-secondary'],
                                    'restaurant' => ['label' => 'Restaurant', 'class' => 'bg-danger'],
                                ];
                                $typeBadge = $typeBadgeMap[$typeVal] ?? null;
                            @endphp
                            <tr data-id="{{ $amenity->id }}">
                                <td class="ps-3">
                                    <i class="ph ph-dots-six-vertical drag-handle fs-5"></i>
                                </td>
                                <td class="text-muted small">{{ $amenity->id }}</td>
                                <td>
                                    @if($amenity->icon)
                                        <i class="ph {{ $amenity->icon }} fs-5 text-secondary"></i>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size:0.9rem">{{ $amenity->getTranslation('name', 'az') }}</div>
                                </td>
                                <td class="text-muted" style="font-size:0.875rem">{{ $amenity->getTranslation('name', 'en') }}</td>
                                <td>
                                    @if($amenity->amenityGroup)
                                        <a href="{{ route('admin.amenity-groups.edit', $amenity->amenityGroup) }}"
                                           class="badge bg-light text-dark border rounded-pill text-decoration-none"
                                           style="font-size:0.7rem">
                                            {{ $amenity->amenityGroup->getTranslation('name', 'az') }}
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($typeBadge)
                                        <span class="badge {{ $typeBadge['class'] }} rounded-pill" style="font-size:0.7rem">
                                            {{ $typeBadge['label'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border rounded-pill" style="font-size:0.7rem">Универсальный</span>
                                    @endif
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex justify-content-end align-items-center gap-1">
                                        <a href="{{ route('admin.amenities.edit', $amenity) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.amenities.destroy', $amenity) }}"
                                              style="display:inline"
                                              onsubmit="return confirm('Удалить удобство \'{{ addslashes($amenity->getTranslation('name', 'az')) }}\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                                <i class="ph ph-trash"></i>
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

        @if($amenities->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-center py-3">
                {{ $amenities->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-squares-four"
                title="Удобства не найдены"
                message="Ни одно удобство не соответствует текущему фильтру, или удобства ещё не созданы."
                actionLabel="Добавить первое удобство"
                actionUrl="{{ route('admin.amenities.create') }}" />
        </div>
    </div>

@endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var el = document.getElementById('amenities-sortable');
    if (!el) return;

    var statusEl = document.getElementById('sort-status');
    var saveTimer = null;

    Sortable.create(el, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function () {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveOrder, 400);
        }
    });

    function saveOrder() {
        var ids = Array.from(el.querySelectorAll('tr[data-id]'))
                       .map(function (tr) { return tr.dataset.id; });

        statusEl.textContent = 'Сохранение...';
        statusEl.style.opacity = '1';
        statusEl.style.color = '';

        $.ajax({
            url: '{{ route('admin.amenities.reorder') }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', ids: ids },
            success: function () {
                statusEl.textContent = 'Порядок сохранён ✓';
                setTimeout(function () { statusEl.style.opacity = '0'; }, 1800);
            },
            error: function () {
                statusEl.textContent = 'Ошибка сохранения';
                statusEl.style.color = '#dc3545';
            }
        });
    }
})();
</script>
@endsection
