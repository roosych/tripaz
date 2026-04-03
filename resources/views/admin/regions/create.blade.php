@extends('layouts.admin')

@section('title', 'Добавить регион')

@section('content')

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-map-pin me-2" style="color:#dc3545"></i>Добавить регион
        </h1>
        <p class="text-muted mb-0 small mt-1">Создание нового географического региона</p>
    </div>
    <a href="{{ route('admin.regions.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ph ph-arrow-left me-1"></i>Назад к регионам
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('admin.regions.store') }}">
                    @csrf

                    <h6 class="fw-semibold text-secondary mb-3 text-uppercase" style="font-size:0.7rem;letter-spacing:1px">
                        Названия
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
                                   value="{{ old('name.az') }}"
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
                                   value="{{ old('name.ru') }}"
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
                                   value="{{ old('name.en') }}"
                                   required>
                            @error('name.en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-semibold text-secondary mb-3 text-uppercase" style="font-size:0.7rem;letter-spacing:1px">
                        Настройки
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="type">
                                Тип региона <span class="text-danger">*</span>
                            </label>
                            <select id="type" name="type"
                                    class="form-select @error('type') is-invalid @enderror"
                                    required>
                                <option value="">— Выберите тип —</option>
                                @foreach($types as $regionType)
                                    <option value="{{ $regionType->value }}"
                                            {{ old('type') === $regionType->value ? 'selected' : '' }}>
                                        {{ ucfirst($regionType->value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="parent_id">Родительский регион</label>
                            <select id="parent_id" name="parent_id"
                                    class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">Нет (верхний уровень)</option>
                                @foreach($parentOptions as $parent)
                                    @php
                                        $parentTypeVal = $parent->type instanceof \App\Enums\RegionType
                                            ? $parent->type->value
                                            : (string)$parent->type;
                                    @endphp
                                    <option value="{{ $parent->id }}"
                                            {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->getTranslation('name', 'az') }} ({{ ucfirst($parentTypeVal) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="slug">Slug</label>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}"
                                   placeholder="автоматически из английского названия">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Оставьте пустым для автоматической генерации из английского названия.</div>
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

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('admin.regions.index') }}" class="btn btn-outline-secondary">Отмена</a>
                        <button type="submit" class="btn btn-danger">
                            <i class="ph ph-plus-circle me-1"></i>Создать регион
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection
