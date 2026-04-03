@extends('layouts.admin')

@section('title', 'Конфигурация бронирования')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-bold">Конфигурация бронирования</h4>
        <p class="text-muted mb-0 small">
            Включение или отключение функции бронирования для каждого типа объявлений глобально.
            Отдельные объявления могут по-прежнему переопределять эту настройку.
        </p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold text-secondary">
            <i class="ph ph-calendar-check me-2"></i>Настройки бронирования по типу объявления
        </h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Тип объявления</th>
                    <th class="text-center">Глобальный статус</th>
                    <th class="text-end pe-4">Переключить</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($configs as $config)
                    <tr>
                        {{-- Type name --}}
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $icon = match($config->type->value) {
                                        'hotel'      => 'ph-buildings',
                                        'home'       => 'ph-house',
                                        'tour'       => 'ph-map-trifold',
                                        'activity'   => 'ph-lightning',
                                        'guide'      => 'ph-identification-card',
                                        'restaurant' => 'ph-coffee',
                                        default      => 'ph-tag',
                                    };
                                @endphp
                                <i class="ph {{ $icon }} text-secondary fs-5"></i>
                                <span class="fw-semibold">{{ $config->type->label() }}</span>
                            </div>
                        </td>

                        {{-- Status badge --}}
                        <td class="text-center">
                            @if ($config->booking_enabled)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                    <i class="ph ph-check-circle me-1"></i>Включено
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill">
                                    <i class="ph ph-x-circle me-1"></i>Отключено
                                </span>
                            @endif
                        </td>

                        {{-- Toggle form --}}
                        <td class="text-end pe-4">
                            <form method="POST"
                                  action="{{ route('admin.booking-config.update', $config->type->value) }}"
                                  class="d-inline-flex align-items-center gap-3">
                                @csrf
                                @method('PATCH')

                                {{-- Hidden fallback so unchecked checkbox sends 0 --}}
                                <input type="hidden" name="booking_enabled" value="0">

                                <div class="form-check form-switch mb-0">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="toggle_{{ $config->type->value }}"
                                        name="booking_enabled"
                                        value="1"
                                        {{ $config->booking_enabled ? 'checked' : '' }}
                                        onchange="this.form.submit()"
                                        style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                    >
                                    <label class="form-check-label visually-hidden"
                                           for="toggle_{{ $config->type->value }}">
                                        Переключить бронирование для {{ $config->type->label() }}
                                    </label>
                                </div>

                                <noscript>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Сохранить</button>
                                </noscript>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white border-top py-3">
        <p class="text-muted small mb-0">
            <i class="ph ph-info me-1"></i>
            Изменения вступают в силу немедленно. Переключите строку, чтобы включить или отключить бронирование для этого типа объявлений глобально.
        </p>
    </div>
</div>
@endsection
