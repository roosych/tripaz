@extends('layouts.admin')

@section('title', 'Системные настройки')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-sliders-horizontal me-2" style="color:var(--admin-accent)"></i>Системные настройки
        </h1>
        <p class="text-muted mb-0 small mt-1">
            Управляйте глобальной конфигурацией платформы — изменения вступают в силу сразу после сохранения.
        </p>
    </div>
</div>

{{-- ================================================================ --}}
{{-- SETTINGS GROUPS                                                   --}}
{{-- ================================================================ --}}
@forelse($settings as $group => $groupSettings)

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
            <h6 class="fw-semibold text-uppercase text-muted mb-0" style="font-size:0.75rem; letter-spacing:1px">
                <i class="ph ph-folder me-2"></i>{{ ucfirst(str_replace('_', ' ', $group)) }}
            </h6>
        </div>
        <div class="card-body pt-3">
            @foreach($groupSettings as $setting)
                <div class="row g-0 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">

                    {{-- Setting info --}}
                    <div class="col-lg-5 mb-2 mb-lg-0">
                        <div class="fw-semibold small text-dark" style="font-size:0.875rem">
                            {{ $setting->key }}
                        </div>
                        @if($setting->description)
                            <div class="text-muted mt-1" style="font-size:0.8rem">
                                {{ $setting->description }}
                            </div>
                        @endif
                        <div class="mt-1 d-flex gap-2 align-items-center">
                            <span class="badge bg-light text-muted border rounded-pill px-2"
                                  style="font-size:0.68rem; font-weight:600">
                                {{ strtoupper($setting->cast) }}
                            </span>
                            @if($setting->is_public)
                                <span class="badge bg-success rounded-pill px-2"
                                      style="font-size:0.68rem; font-weight:600">
                                    Публичный
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-2"
                                      style="font-size:0.68rem; font-weight:600">
                                    Внутренний
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Current value display --}}
                    <div class="col-lg-2 mb-2 mb-lg-0 d-flex align-items-center">
                        <div>
                            <div class="text-muted small" style="font-size:0.75rem">Текущее значение</div>
                            <div class="fw-semibold" style="font-size:0.875rem">
                                {{ $setting->value }}
                            </div>
                        </div>
                    </div>

                    {{-- Edit form --}}
                    <div class="col-lg-5 d-flex align-items-center">
                        <form method="POST"
                              action="{{ route('admin.system-settings.update', $setting) }}"
                              class="d-flex gap-2 w-100">
                            @csrf
                            @method('PATCH')

                            @if($setting->cast === 'boolean')
                                <select name="value"
                                        class="form-select form-select-sm flex-grow-1">
                                    <option value="1" {{ $setting->value == '1' || strtolower($setting->value) === 'true' ? 'selected' : '' }}>
                                        Включено (true)
                                    </option>
                                    <option value="0" {{ $setting->value == '0' || strtolower($setting->value) === 'false' ? 'selected' : '' }}>
                                        Отключено (false)
                                    </option>
                                </select>
                            @else
                                <input type="{{ in_array($setting->cast, ['integer', 'float']) ? 'number' : 'text' }}"
                                       name="value"
                                       class="form-control form-control-sm flex-grow-1"
                                       value="{{ $setting->value }}"
                                       @if($setting->cast === 'float') step="0.01" @endif
                                       required>
                            @endif

                            <button type="submit" class="btn btn-sm btn-primary flex-shrink-0">
                                <i class="ph ph-check"></i>
                                <span class="d-none d-xl-inline ms-1">Сохранить</span>
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    </div>

@empty
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-sliders-horizontal"
                title="Настройки не найдены"
                message="Системные настройки ещё не настроены." />
        </div>
    </div>
@endforelse

{{-- ================================================================ --}}
{{-- INFO CARD                                                         --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm border-start border-primary border-3">
    <div class="card-body py-2 px-3 d-flex gap-2 align-items-start">
        <i class="ph ph-info text-primary flex-shrink-0 mt-1"></i>
        <div style="font-size:0.8rem" class="text-muted">
            Настройки кэшируются на 1 час. Изменения немедленно отражаются в кэше.
            Параметр <strong>free_published_listings_limit</strong> определяет, сколько объявлений каждый владелец может опубликовать бесплатно.
            Объявления сверх этого лимита требуют оплаты в размере <strong>price_per_extra_listing</strong> в <strong>listing_payment_currency</strong>.
        </div>
    </div>
</div>

@endsection
