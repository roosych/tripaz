@extends('layouts.admin')

@section('title', 'Продвижение и буст')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="h4 fw-bold mb-0">
                <i class="ph ph-lightning me-2" style="color:#dc3545"></i>Продвижение и буст
            </h1>
            <p class="text-muted mb-0 small mt-1">
                Увеличьте вес продвижения объявления, чтобы оно отображалось выше в результатах поиска.
                Больший вес = более высокий приоритет в порядке отображения.
            </p>
        </div>
        @if(isset($listings) && method_exists($listings, 'total'))
            <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
                {{ $listings->total() }} опубликованных объявлений
            </span>
        @endif
    </div>
</div>

{{-- Info callout --}}
<div class="alert alert-info d-flex gap-2 align-items-start mb-4 py-2" style="font-size:0.875rem">
    <i class="ph ph-info flex-shrink-0 mt-1"></i>
    <div>
        <strong>Как работает продвижение:</strong> Стандартный вес продвижения равен <code>1</code>.
        Увеличение веса объявления (до <code>1000</code>) позволяет ему отображаться раньше в результатах поиска.
        Объявления сортируются по <em>boost_weight DESC</em>, затем по дате создания.
    </div>
</div>

{{-- ================================================================ --}}
{{-- PROMOTIONS TABLE                                                  --}}
{{-- ================================================================ --}}
@if(isset($listings) && $listings->isNotEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="min-width:220px">Заголовок</th>
                            <th class="py-3">Тип</th>
                            <th class="py-3">Статус</th>
                            <th class="py-3 text-center" style="width:130px">Вес продвижения</th>
                            <th class="pe-3 py-3" style="min-width:200px">Обновить вес</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listings as $listing)
                            @php
                                $typeVal = $listing->type instanceof \App\Enums\ListingType
                                    ? $listing->type->value
                                    : (string)$listing->type;

                                $statusVal = $listing->status instanceof \App\Enums\ListingStatus
                                    ? $listing->status->value
                                    : (string)$listing->status;

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

                                $boostWeight = $listing->boost_weight ?? 1;
                                // Highlight rows with non-default boost
                                $isBoosted = $boostWeight > 1;
                            @endphp
                            <tr class="{{ $isBoosted ? 'table-warning' : '' }}">

                                {{-- Title --}}
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark" style="font-size:0.9rem">
                                        {{ $title }}
                                        @if($isBoosted)
                                            <i class="ph ph-star text-warning ms-1" style="font-size:0.75rem" title="Продвигаемое объявление"></i>
                                        @endif
                                    </div>
                                    <div class="text-muted" style="font-size:0.75rem">
                                        <a href="{{ route('listings.show', $listing->slug) }}"
                                           target="_blank"
                                           class="text-muted text-decoration-none">
                                            {{ $listing->slug }}
                                            <i class="ph ph-link ms-1" style="font-size:0.65rem"></i>
                                        </a>
                                    </div>
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

                                {{-- Current Boost Weight --}}
                                <td class="text-center">
                                    @if($isBoosted)
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1" style="font-size:0.8rem; font-weight:700">
                                            {{ $boostWeight }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1" style="font-size:0.8rem">
                                            {{ $boostWeight }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Update Boost form --}}
                                <td class="pe-3">
                                    <form method="POST"
                                          action="{{ route('admin.promotions.update', $listing) }}"
                                          class="d-flex align-items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number"
                                               class="form-control form-control-sm"
                                               name="boost_weight"
                                               value="{{ $boostWeight }}"
                                               min="1"
                                               max="1000"
                                               step="1"
                                               style="width:80px"
                                               aria-label="Boost weight for {{ $title }}">
                                        <button type="submit" class="btn btn-sm btn-primary flex-shrink-0">
                                            <i class="ph ph-lightning me-1"></i>Обновить
                                        </button>
                                        @if($isBoosted)
                                            {{-- Reset to default button --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary flex-shrink-0 reset-boost-btn"
                                                    data-form="{{ 'boost-form-' . $listing->id }}"
                                                    title="Сбросить до значения по умолчанию (1)">
                                                <i class="ph ph-arrow-clockwise"></i>
                                            </button>
                                        @endif
                                    </form>
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
                icon="ph-energy"
                title="Нет опубликованных объявлений"
                message="Опубликованные объявления появятся здесь для управления продвижением." />
        </div>
    </div>

@endif

@endsection

@section('scripts')
<script>
    // Reset boost weight button: set the number input back to 1 and submit
    $(document).on('click', '.reset-boost-btn', function () {
        var $form = $(this).closest('form');
        $form.find('input[name="boost_weight"]').val(1);
        $form.submit();
    });
</script>
@endsection
