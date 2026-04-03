@extends('layouts.admin')

@section('title', 'Группы удобств')

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
            <i class="ph ph-stack me-2" style="color:#dc3545"></i>Группы удобств
        </h1>
        <p class="text-muted mb-0 small mt-1">Управляйте группами для организации удобств объявлений</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span id="sort-status" class="text-muted small" style="opacity:0"></span>
        <a href="{{ route('admin.amenities.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph ph-squares-four me-1"></i>Удобства
        </a>
        <a href="{{ route('admin.amenity-groups.create') }}" class="btn btn-sm btn-danger">
            <i class="ph ph-plus-circle me-1"></i>Добавить группу
        </a>
    </div>
</div>

{{-- ================================================================ --}}
{{-- GROUPS TABLE                                                      --}}
{{-- ================================================================ --}}
@if($groups->isNotEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width:36px"></th>
                            <th class="py-3" style="width:50px">#</th>
                            <th class="py-3" style="min-width:160px">Название (AZ)</th>
                            <th class="py-3">Название (RU)</th>
                            <th class="py-3">Название (EN)</th>
                            <th class="py-3">Типы объявлений</th>
                            <th class="py-3 text-center" style="width:90px">Удобств</th>
                            <th class="pe-3 py-3 text-end" style="min-width:100px">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="groups-sortable">
                        @foreach($groups as $group)
                            <tr data-id="{{ $group->id }}">
                                <td class="ps-3">
                                    <i class="ph ph-dots-six-vertical drag-handle fs-5"></i>
                                </td>
                                <td class="text-muted small">{{ $group->id }}</td>
                                <td>
                                    <div class="fw-semibold" style="font-size:0.9rem">
                                        {{ $group->getTranslation('name', 'az') }}
                                    </div>
                                </td>
                                <td class="text-muted" style="font-size:0.875rem">
                                    {{ $group->getTranslation('name', 'ru') ?: '—' }}
                                </td>
                                <td class="text-muted" style="font-size:0.875rem">
                                    {{ $group->getTranslation('name', 'en') ?: '—' }}
                                </td>
                                <td>
                                    @php
                                        $typeBadgeMap = [
                                            'hotel'      => ['label' => 'Hotel',      'class' => 'bg-primary'],
                                            'home'       => ['label' => 'Home',        'class' => 'bg-info text-dark'],
                                            'tour'       => ['label' => 'Tour',        'class' => 'bg-success'],
                                            'activity'   => ['label' => 'Activity',   'class' => 'bg-warning text-dark'],
                                            'guide'      => ['label' => 'Guide',       'class' => 'bg-secondary'],
                                            'restaurant' => ['label' => 'Restaurant', 'class' => 'bg-danger'],
                                        ];
                                    @endphp
                                    @if(!empty($group->listing_types))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($group->listing_types as $lt)
                                                @if(isset($typeBadgeMap[$lt]))
                                                    <span class="badge {{ $typeBadgeMap[$lt]['class'] }} rounded-pill" style="font-size:0.65rem">
                                                        {{ $typeBadgeMap[$lt]['label'] }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge bg-light text-dark border rounded-pill" style="font-size:0.65rem">Все типы</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border" style="font-size:0.75rem">
                                        {{ $group->sort_order }}
                                    </span>
                                </td>
                                <td class="text-center" style="width:90px">
                                    <a href="{{ route('admin.amenities.index', ['group' => $group->id]) }}"
                                       class="badge bg-primary rounded-pill text-decoration-none"
                                       style="font-size:0.75rem">
                                        {{ $group->amenities_count }}
                                    </a>
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex justify-content-end align-items-center gap-1">
                                        <a href="{{ route('admin.amenity-groups.edit', $group) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.amenity-groups.destroy', $group) }}"
                                              style="display:inline"
                                              onsubmit="return confirm('Удалить группу \'{{ addslashes($group->getTranslation('name', 'az')) }}\'?\nУдобства, входящие в эту группу, станут без группы.')">
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
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-stack"
                title="Группы не найдены"
                message="Создайте первую группу, чтобы организовать удобства по категориям."
                actionLabel="Добавить группу"
                actionUrl="{{ route('admin.amenity-groups.create') }}" />
        </div>
    </div>

@endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var el = document.getElementById('groups-sortable');
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
            url: '{{ route('admin.amenity-groups.reorder') }}',
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
