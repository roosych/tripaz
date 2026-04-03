@extends('layouts.admin')

@section('title', 'Регионы')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-map-pin me-2" style="color:#dc3545"></i>Регионы
        </h1>
        <p class="text-muted mb-0 small mt-1">Управление географическими регионами и местоположениями</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(isset($regions) && method_exists($regions, 'total'))
            <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
                {{ $regions->total() }} всего
            </span>
        @endif
        <a href="{{ route('admin.regions.create') }}" class="btn btn-sm btn-danger">
            <i class="ph ph-plus-circle me-1"></i>Добавить регион
        </a>
    </div>
</div>

{{-- ================================================================ --}}
{{-- FILTERS                                                           --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.regions.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-sm-5 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="ph ph-magnifying-glass text-muted"></i></span>
                        <input type="text"
                               class="form-control"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Поиск по названию или слагу..."
                               autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-3 col-md-2">
                    <select class="form-select" name="type">
                        <option value="">Все типы</option>
                        @foreach($types as $regionType)
                            <option value="{{ $regionType->value }}" {{ ($type ?? '') === $regionType->value ? 'selected' : '' }}>
                                {{ ucfirst($regionType->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-funnel me-1"></i>Фильтр
                    </button>
                    @if(($search ?? '') || ($type ?? ''))
                        <a href="{{ route('admin.regions.index') }}" class="btn btn-outline-secondary ms-1">
                            <i class="ph ph-x"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================ --}}
{{-- REGIONS TABLE                                                     --}}
{{-- ================================================================ --}}
@if(isset($regions) && $regions->isNotEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width:50px">#</th>
                            <th class="py-3" style="min-width:180px">Название (AZ)</th>
                            <th class="py-3">Название (EN)</th>
                            <th class="py-3">Тип</th>
                            <th class="py-3">Родитель</th>
                            <th class="py-3">Слаг</th>
                            <th class="pe-3 py-3 text-end" style="min-width:100px">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($regions as $region)
                            @php
                                $typeVal = $region->type instanceof \App\Enums\RegionType
                                    ? $region->type->value
                                    : (string)$region->type;

                                $typeBadgeMap = [
                                    'country'  => 'bg-danger',
                                    'region'   => 'bg-primary',
                                    'district' => 'bg-info text-dark',
                                    'city'     => 'bg-success',
                                ];
                                $typeBadgeClass = $typeBadgeMap[$typeVal] ?? 'bg-secondary';
                            @endphp
                            <tr>
                                <td class="ps-3 text-muted small">{{ $region->id }}</td>
                                <td>
                                    <div class="fw-semibold" style="font-size:0.9rem">{{ $region->getTranslation('name', 'az') }}</div>
                                </td>
                                <td class="text-muted" style="font-size:0.875rem">{{ $region->getTranslation('name', 'en') }}</td>
                                <td>
                                    <span class="badge {{ $typeBadgeClass }} rounded-pill" style="font-size:0.7rem">
                                        {{ ucfirst($typeVal) }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:0.875rem">
                                    {{ $region->parent?->getTranslation('name', 'az') ?? '—' }}
                                </td>
                                <td class="text-muted" style="font-size:0.78rem">
                                    <code>{{ $region->slug }}</code>
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex justify-content-end align-items-center gap-1">
                                        <a href="{{ route('admin.regions.edit', $region) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.regions.destroy', $region) }}"
                                              style="display:inline"
                                              onsubmit="return confirm('Удалить регион \'{{ addslashes($region->getTranslation('name', 'az')) }}\'? Дочерние регионы будут перемещены к родительскому.')">
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

        @if($regions->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-center py-3">
                {{ $regions->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-map-pin"
                title="Регионов не найдено"
                message="Нет регионов, соответствующих текущему фильтру, или ни одного ещё не создано."
                actionLabel="Добавить первый регион"
                actionUrl="{{ route('admin.regions.create') }}" />
        </div>
    </div>

@endif

@endsection
