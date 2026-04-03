@extends('layouts.owner')

@section('title', 'Бронирования')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header">
    <h1 class="h4 fw-bold mb-0">
        <i class="ph ph-calendar-check me-2 text-primary"></i>Бронирования
    </h1>
    <p class="text-muted mb-0 small mt-1">Управляйте бронированиями для ваших объявлений</p>
</div>

{{-- ================================================================ --}}
{{-- COMING SOON / EMPTY STATE                                         --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <x-empty-state
            icon="ph-calendar-remove-01"
            title="Бронирований пока нет"
            message="Бронирования появятся здесь, когда пользователи забронируют ваши объявления." />

        <div class="text-center pb-4">
            <p class="text-muted small mb-0">
                <i class="ph ph-clock me-1"></i>
                Модуль бронирования в настоящее время находится в разработке. Следите за обновлениями.
            </p>
        </div>
    </div>
</div>

@endsection
