@extends('layouts.admin')

@section('title', 'Редактировать группу — ' . $amenityGroup->getTranslation('name', 'az'))

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-stack me-2" style="color:#dc3545"></i>Редактировать группу
        </h1>
        <p class="text-muted mb-0 small mt-1">
            Редактирование: <strong>{{ $amenityGroup->getTranslation('name', 'az') }}</strong>
            &nbsp;·&nbsp;
            <a href="{{ route('admin.amenities.index', ['group' => $amenityGroup->id]) }}" class="text-muted small">
                {{ $amenityGroup->amenities_count }} удобств(а)
            </a>
        </p>
    </div>
    <a href="{{ route('admin.amenity-groups.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ph ph-arrow-left me-1"></i>Назад к группам
    </a>
</div>

{{-- ================================================================ --}}
{{-- FORM                                                              --}}
{{-- ================================================================ --}}
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('admin.amenity-groups.update', $amenityGroup) }}">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-semibold text-secondary mb-3 text-uppercase" style="font-size:0.7rem;letter-spacing:1px">
                        Название группы
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="name_az">
                                Азербайджанский <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">AZ</span>
                                <input type="text"
                                       id="name_az"
                                       name="name[az]"
                                       class="form-control @error('name.az') is-invalid @enderror"
                                       value="{{ old('name.az', $amenityGroup->getTranslation('name', 'az')) }}"
                                       required>
                                @error('name.az')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="name_ru">
                                Русский <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">RU</span>
                                <input type="text"
                                       id="name_ru"
                                       name="name[ru]"
                                       class="form-control @error('name.ru') is-invalid @enderror"
                                       value="{{ old('name.ru', $amenityGroup->getTranslation('name', 'ru')) }}"
                                       required>
                                @error('name.ru')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="name_en">
                                Английский <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" style="font-size:0.75rem;font-weight:700">EN</span>
                                <input type="text"
                                       id="name_en"
                                       name="name[en]"
                                       class="form-control @error('name.en') is-invalid @enderror"
                                       value="{{ old('name.en', $amenityGroup->getTranslation('name', 'en')) }}"
                                       required>
                                @error('name.en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-semibold text-secondary mb-3 text-uppercase" style="font-size:0.7rem;letter-spacing:1px">
                        Настройки
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="sort_order">Порядок сортировки</label>
                            <input type="number"
                                   id="sort_order"
                                   name="sort_order"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $amenityGroup->sort_order) }}"
                                   min="0"
                                   max="9999">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Группы с меньшим числом отображаются первыми.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Типы объявлений</label>
                            @php $savedTypes = old('listing_types', $amenityGroup->listing_types ?? []); @endphp
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($types as $listingType)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="listing_types[]"
                                               value="{{ $listingType->value }}"
                                               id="lt_{{ $listingType->value }}"
                                               {{ in_array($listingType->value, $savedTypes) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lt_{{ $listingType->value }}">
                                            {{ $listingType->label() }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Оставьте пустым — группа будет универсальной (для всех типов).</div>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <form method="POST"
                              action="{{ route('admin.amenity-groups.destroy', $amenityGroup) }}"
                              onsubmit="return confirm('Удалить группу \'{{ addslashes($amenityGroup->getTranslation('name', 'az')) }}\'?\nУдобства этой группы останутся без группы.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    {{ $amenityGroup->amenities_count > 0 ? 'disabled title=\'Сначала удалите или переназначьте удобства группы\'' : '' }}>
                                <i class="ph ph-trash me-1"></i>Удалить группу
                            </button>
                        </form>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.amenity-groups.index') }}" class="btn btn-outline-secondary">Отмена</a>
                            <button type="submit" class="btn btn-danger">
                                <i class="ph ph-check me-1"></i>Сохранить изменения
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        {{-- Metadata --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body py-3">
                <div class="row g-2 text-muted" style="font-size:0.8rem">
                    <div class="col-sm-4">
                        <span class="fw-semibold text-dark">ID:</span> {{ $amenityGroup->id }}
                    </div>
                    <div class="col-sm-4">
                        <span class="fw-semibold text-dark">Создано:</span> {{ $amenityGroup->created_at->format('d M Y, H:i') }}
                    </div>
                    <div class="col-sm-4">
                        <span class="fw-semibold text-dark">Удобств:</span>
                        <a href="{{ route('admin.amenities.index', ['group' => $amenityGroup->id]) }}">
                            {{ $amenityGroup->amenities_count }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
