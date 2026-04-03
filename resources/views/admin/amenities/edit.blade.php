@extends('layouts.admin')

@section('title', 'Редактировать удобство — ' . $amenity->getTranslation('name', 'az'))

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-squares-four me-2" style="color:#dc3545"></i>Редактировать удобство
        </h1>
        <p class="text-muted mb-0 small mt-1">Редактирование: <strong>{{ $amenity->getTranslation('name', 'az') }}</strong></p>
    </div>
    <a href="{{ route('admin.amenities.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ph ph-arrow-left me-1"></i>Назад к удобствам
    </a>
</div>

{{-- ================================================================ --}}
{{-- FORM                                                              --}}
{{-- ================================================================ --}}
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('admin.amenities.update', $amenity) }}">
                    @csrf
                    @method('PUT')

                    {{-- Names --}}
                    <h6 class="fw-semibold text-secondary mb-3 text-uppercase" style="font-size:0.7rem;letter-spacing:1px">
                        Переводы
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="name_az">
                                Название (Азербайджанский) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="name_az"
                                   name="name[az]"
                                   class="form-control @error('name.az') is-invalid @enderror"
                                   value="{{ old('name.az', $amenity->getTranslation('name', 'az')) }}"
                                   required>
                            @error('name.az')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="name_ru">
                                Название (Русский) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="name_ru"
                                   name="name[ru]"
                                   class="form-control @error('name.ru') is-invalid @enderror"
                                   value="{{ old('name.ru', $amenity->getTranslation('name', 'ru')) }}"
                                   required>
                            @error('name.ru')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="name_en">
                                Название (Английский) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="name_en"
                                   name="name[en]"
                                   class="form-control @error('name.en') is-invalid @enderror"
                                   value="{{ old('name.en', $amenity->getTranslation('name', 'en')) }}"
                                   required>
                            @error('name.en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Settings --}}
                    <h6 class="fw-semibold text-secondary mb-3 text-uppercase" style="font-size:0.7rem;letter-spacing:1px">
                        Настройки
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="listing_type">Тип объявления</label>
                            @php
                                $currentType = $amenity->listing_type instanceof \App\Enums\ListingType
                                    ? $amenity->listing_type->value
                                    : (string)$amenity->listing_type;
                            @endphp
                            <select id="listing_type"
                                    name="listing_type"
                                    class="form-select @error('listing_type') is-invalid @enderror">
                                <option value="">Универсальный (применяется ко всем типам)</option>
                                @foreach($types as $listingType)
                                    <option value="{{ $listingType->value }}"
                                            {{ old('listing_type', $currentType) === $listingType->value ? 'selected' : '' }}>
                                        {{ $listingType->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('listing_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="amenity_group_id">Группа</label>
                            <div class="input-group">
                                <select id="amenity_group_id"
                                        name="amenity_group_id"
                                        class="form-select @error('amenity_group_id') is-invalid @enderror">
                                    <option value="">— Без группы —</option>
                                    @foreach($groups as $g)
                                        <option value="{{ $g->id }}"
                                            {{ old('amenity_group_id', $amenity->amenity_group_id) == $g->id ? 'selected' : '' }}>
                                            {{ $g->getTranslation('name', 'az') }}
                                            @if($g->getTranslation('name', 'ru') !== $g->getTranslation('name', 'az'))
                                                / {{ $g->getTranslation('name', 'ru') }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('admin.amenity-groups.create') }}"
                                   class="btn btn-outline-secondary"
                                   title="Создать новую группу"
                                   target="_blank">
                                    <i class="ph ph-plus"></i>
                                </a>
                            </div>
                            @error('amenity_group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text"><a href="{{ route('admin.amenity-groups.index') }}" target="_blank">Управление группами →</a></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="icon">Иконка</label>
                            <input type="text"
                                   id="icon"
                                   name="icon"
                                   class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', $amenity->icon) }}"
                                   placeholder="e.g. ph ph-house">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Полный класс <a href="https://phosphoricons.com/" target="_blank" rel="noopener">Phosphor Icons</a>, например: <code>ph ph-wifi-high</code>
                            </div>
                        </div>
                    </div>

                    {{-- Icon preview --}}
                    <div id="icon-preview" class="mb-4" style="{{ old('icon', $amenity->icon) ? '' : 'display:none' }}">
                        <label class="form-label fw-semibold">Предпросмотр иконки</label>
                        <div class="d-flex align-items-center gap-2 p-3 bg-light rounded border">
                            <i id="preview-icon" class="{{ old('icon', $amenity->icon) }} fs-3 text-primary"></i>
                            <span id="preview-icon-name" class="text-muted small">{{ old('icon', $amenity->icon) }}</span>
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
                              action="{{ route('admin.amenities.destroy', $amenity) }}"
                              onsubmit="return confirm('Удалить это удобство? Оно будет удалено из всех объявлений.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="ph ph-trash me-1"></i>Удалить удобство
                            </button>
                        </form>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.amenities.index') }}" class="btn btn-outline-secondary">Отмена</a>
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
                        <span class="fw-semibold text-dark">ID:</span> {{ $amenity->id }}
                    </div>
                    <div class="col-sm-4">
                        <span class="fw-semibold text-dark">Создано:</span> {{ $amenity->created_at->format('d M Y, H:i') }}
                    </div>
                    <div class="col-sm-4">
                        <span class="fw-semibold text-dark">Обновлено:</span> {{ $amenity->updated_at->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $('#icon').on('input', function () {
        var iconClass = $(this).val().trim();
        if (iconClass) {
            $('#preview-icon').attr('class', iconClass + ' fs-3 text-primary');
            $('#preview-icon-name').text(iconClass);
            $('#icon-preview').show();
        } else {
            $('#icon-preview').hide();
        }
    });
</script>
@endsection
