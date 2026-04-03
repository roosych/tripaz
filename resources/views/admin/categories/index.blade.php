@extends('layouts.admin')

@section('title', 'Категории')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-tag me-2" style="color:#dc3545"></i>Категории
        </h1>
        <p class="text-muted mb-0 small mt-1">Управление категориями объявлений и их иерархией</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(isset($categories) && method_exists($categories, 'total'))
            <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
                {{ $categories->total() }} всего
            </span>
        @endif
        <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-danger">
            <i class="ph ph-plus-circle me-1"></i>Добавить категорию
        </a>
    </div>
</div>

{{-- ================================================================ --}}
{{-- FILTERS                                                           --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.categories.index') }}">
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
                <div class="col-sm-4 col-md-3">
                    <select class="form-select" name="type">
                        <option value="">Все типы</option>
                        <option value="universal" {{ ($type ?? '') === 'universal' ? 'selected' : '' }}>Универсальный (все типы)</option>
                        @foreach($types as $listingType)
                            <option value="{{ $listingType->value }}" {{ ($type ?? '') === $listingType->value ? 'selected' : '' }}>
                                {{ $listingType->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-funnel me-1"></i>Фильтр
                    </button>
                    @if(($search ?? '') || ($type ?? ''))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary ms-1">
                            <i class="ph ph-x"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================ --}}
{{-- CATEGORIES TABLE                                                  --}}
{{-- ================================================================ --}}
@if(isset($categories) && $categories->isNotEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width:50px">#</th>
                            <th class="py-3">Иконка</th>
                            <th class="py-3" style="min-width:180px">Название (AZ)</th>
                            <th class="py-3">Название (EN)</th>
                            <th class="py-3">Родитель</th>
                            <th class="py-3">Тип объявления</th>
                            <th class="py-3">Порядок</th>
                            <th class="py-3">Слаг</th>
                            <th class="pe-3 py-3 text-end" style="min-width:120px">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            @php
                                $typeVal = $category->listing_type instanceof \App\Enums\ListingType
                                    ? $category->listing_type->value
                                    : (string)$category->listing_type;

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
                            <tr>
                                <td class="ps-3 text-muted small">{{ $category->id }}</td>
                                <td>
                                    @if($category->icon)
                                        <i class="ph {{ $category->icon }} fs-5 text-secondary"></i>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size:0.9rem">{{ $category->getTranslation('name', 'az') }}</div>
                                </td>
                                <td class="text-muted" style="font-size:0.875rem">{{ $category->getTranslation('name', 'en') }}</td>
                                <td class="text-muted" style="font-size:0.875rem">
                                    {{ $category->parent?->getTranslation('name', 'az') ?? '—' }}
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
                                <td class="text-muted small">{{ $category->sort_order }}</td>
                                <td class="text-muted" style="font-size:0.78rem">
                                    <code>{{ $category->slug }}</code>
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex justify-content-end align-items-center gap-1">
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.categories.destroy', $category) }}"
                                              style="display:inline"
                                              onsubmit="return confirm('Удалить категорию \'{{ addslashes($category->getTranslation('name', 'az')) }}\'? Дочерние категории будут перемещены к родительской.')">
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

        @if($categories->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-center py-3">
                {{ $categories->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-tag"
                title="Категорий не найдено"
                message="Нет категорий, соответствующих текущему фильтру, или ни одна ещё не создана."
                actionLabel="Добавить первую категорию"
                actionUrl="{{ route('admin.categories.create') }}" />
        </div>
    </div>

@endif

@endsection
