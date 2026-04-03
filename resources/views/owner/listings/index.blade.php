@extends('layouts.owner')

@section('title', 'Мои объявления')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-list-checks me-2 text-primary"></i>Мои объявления
        </h1>
        <p class="text-muted mb-0 small mt-1">Управляйте всеми вашими объявлениями и услугами</p>
    </div>
    <a href="{{ route('owner.listings.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="ph ph-plus-circle"></i>
        Добавить объявление
    </a>
</div>

{{-- ================================================================ --}}
{{-- FREE LIMIT USAGE BAR                                             --}}
{{-- ================================================================ --}}
@php
    $freePct = $freeLimit > 0 ? min(100, round(($publishedCount / $freeLimit) * 100)) : 100;
    $barClass = $freePct >= 100 ? 'bg-danger' : ($freePct >= 80 ? 'bg-warning' : 'bg-success');
@endphp
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <i class="ph ph-chart-bar text-primary"></i>
                <span class="fw-semibold small">Бесплатные объявления:</span>
                <span class="small text-muted">{{ $publishedCount }} / {{ $freeLimit }} использовано</span>
            </div>
            <div class="flex-grow-1" style="min-width:140px; max-width:260px">
                <div class="progress" style="height:8px; border-radius:4px">
                    <div class="progress-bar {{ $barClass }}"
                         role="progressbar"
                         style="width:{{ $freePct }}%"
                         aria-valuenow="{{ $publishedCount }}"
                         aria-valuemin="0"
                         aria-valuemax="{{ $freeLimit }}">
                    </div>
                </div>
            </div>
            @if($freePct >= 100)
                <span class="badge rounded-pill small" style="background-color:#fd7e14!important;color:#fff">
                    <i class="ph ph-warning-circle me-1"></i>Лимит достигнут — дополнительные объявления требуют оплаты
                </span>
            @endif
        </div>
    </div>
</div>

{{-- ================================================================ --}}
{{-- LISTINGS TABLE                                                    --}}
{{-- ================================================================ --}}
@if($listings->isEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-task-list-01"
                title="Объявлений пока нет"
                message="Создайте своё первое объявление, чтобы начать. Оно появится здесь после отправки."
                actionLabel="Добавить объявление"
                actionUrl="{{ route('owner.listings.create') }}" />
        </div>
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="min-width:220px">Заголовок</th>
                            <th class="py-3">Тип</th>
                            <th class="py-3">Статус</th>
                            <th class="py-3">Оплата</th>
                            <th class="py-3">Создано</th>
                            <th class="pe-3 py-3 text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listings as $listing)
                            @php
                                $typeBadgeMap = [
                                    'hotel'      => ['label' => 'Hotel',      'class' => 'bg-primary'],
                                    'home'       => ['label' => 'Home',        'class' => 'bg-info text-dark'],
                                    'tour'       => ['label' => 'Tour',        'class' => 'bg-success'],
                                    'activity'   => ['label' => 'Activity',   'class' => 'bg-warning text-dark'],
                                    'guide'      => ['label' => 'Guide',       'class' => 'bg-secondary'],
                                    'restaurant' => ['label' => 'Restaurant', 'class' => 'bg-danger'],
                                ];
                                $typeVal   = $listing->type instanceof \App\Enums\ListingType ? $listing->type->value : (string)$listing->type;
                                $typeBadge = $typeBadgeMap[$typeVal] ?? ['label' => ucfirst($typeVal), 'class' => 'bg-secondary'];
                                $statusVal = $listing->status instanceof \App\Enums\ListingStatus ? $listing->status->value : (string)$listing->status;
                                // Best-effort title: try az translation, fall through to any translation
                                $title = $listing->translations->firstWhere('locale', 'az')?->title
                                      ?? $listing->translations->firstWhere('locale', 'ru')?->title
                                      ?? $listing->translations->firstWhere('locale', 'en')?->title
                                      ?? '(Без названия)';
                            @endphp
                            <tr>
                                {{-- Title --}}
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark" style="font-size:0.9rem">
                                        {{ $title }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.78rem">
                                        {{ $listing->slug }}
                                    </div>
                                </td>

                                {{-- Type --}}
                                <td>
                                    <span class="badge {{ $typeBadge['class'] }} rounded-pill" style="font-size:0.72rem">
                                        {{ $typeBadge['label'] }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td>
                                    <x-status-badge :status="$statusVal" />
                                </td>

                                {{-- Payment --}}
                                <td>
                                    @if($listing->payment_required)
                                        @php
                                            $latestPmt    = $listing->payments->first();
                                            $pmtStatus    = $latestPmt
                                                ? ($latestPmt->status instanceof \App\Enums\ListingPaymentStatus
                                                    ? $latestPmt->status->value
                                                    : (string)$latestPmt->status)
                                                : null;
                                        @endphp
                                        @if(!$latestPmt || $pmtStatus === 'rejected')
                                            <div class="d-flex flex-column gap-1 align-items-start">
                                                <span class="badge rounded-pill"
                                                      style="background-color:#fd7e14!important;color:#fff;font-size:0.7rem">
                                                    Требуется оплата
                                                </span>
                                                <a href="{{ route('owner.listings.payment.show', $listing) }}"
                                                   class="btn btn-sm btn-outline-warning py-0 px-2"
                                                   style="font-size:0.75rem">
                                                    <i class="ph ph-credit-card me-1"></i>Оплатить
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary py-0 px-2"
                                                        style="font-size:0.75rem"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#withdrawModal"
                                                        data-listing-id="{{ $listing->id }}"
                                                        data-listing-title="{{ $title }}">
                                                    <i class="ph ph-arrow-clockwise me-1"></i>Отозвать
                                                </button>
                                            </div>
                                        @elseif($pmtStatus === 'pending')
                                            <span class="badge bg-warning text-dark rounded-pill" style="font-size:0.7rem">
                                                <i class="ph ph-hourglass me-1"></i>На рассмотрении
                                            </span>
                                        @elseif($pmtStatus === 'approved')
                                            <span class="badge bg-success rounded-pill" style="font-size:0.7rem">
                                                <i class="ph ph-check-circle me-1"></i>Активировано
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>

                                {{-- Created --}}
                                <td class="text-muted small">
                                    {{ $listing->created_at->format('d M Y') }}
                                </td>

                                {{-- Actions --}}
                                <td class="pe-3 text-end">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        {{-- View on public site --}}
                                        @if($statusVal === 'published')
                                            <a href="{{ route('listings.show', $listing->slug) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Просмотреть на сайте">
                                                <i class="ph ph-eye"></i>
                                            </a>
                                        @endif

                                        {{-- Pause (published → suspended) --}}
                                        @if($statusVal === 'published')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Приостановить объявление"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#toggleStatusModal"
                                                    data-slug="{{ $listing->slug }}"
                                                    data-title="{{ $title }}"
                                                    data-action="pause">
                                                <i class="ph ph-pause-circle"></i> Пауза
                                            </button>
                                        @endif

                                        {{-- Activate (suspended → published) --}}
                                        @if($statusVal === 'suspended')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Активировать объявление"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#toggleStatusModal"
                                                    data-slug="{{ $listing->slug }}"
                                                    data-title="{{ $title }}"
                                                    data-action="activate">
                                                <i class="ph ph-play-circle"></i> Активировать
                                            </button>
                                        @endif

                                        {{-- Edit --}}
                                        <a href="{{ route('owner.listings.edit', $listing->slug) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать объявление">
                                            <i class="ph ph-pencil-simple"></i> Редактировать
                                        </a>

                                        {{-- Delete trigger --}}
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Удалить объявление"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-slug="{{ $listing->slug }}"
                                                data-title="{{ $title }}">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </div>
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
                {{ $listings->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endif

{{-- ================================================================ --}}
{{-- TOGGLE STATUS MODAL                                               --}}
{{-- ================================================================ --}}
<div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="toggleStatusModalLabel">
                    <i id="toggleStatusIcon" class="me-2"></i><span id="toggleStatusTitle">Изменить статус</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1 text-muted" id="toggleStatusDesc"></p>
                <p class="fw-semibold" id="toggleStatusListingTitle"></p>
                <div class="alert d-flex gap-2 align-items-start py-2 mb-0" id="toggleStatusAlert">
                    <i class="ph ph-info flex-shrink-0 mt-1"></i>
                    <small id="toggleStatusInfo"></small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="toggleStatusForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn" id="toggleStatusBtn"></button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================ --}}
{{-- DELETE CONFIRMATION MODAL                                         --}}
{{-- ================================================================ --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="deleteModalLabel">
                    <i class="ph ph-warning text-danger me-2"></i>Удалить объявление
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1 text-muted">Вы собираетесь навсегда удалить:</p>
                <p class="fw-semibold" id="deleteListingTitle">это объявление</p>
                <div class="alert alert-warning d-flex gap-2 align-items-start py-2 mb-0">
                    <i class="ph ph-info flex-shrink-0 mt-1"></i>
                    <small>Это действие нельзя отменить. Все связанные медиафайлы и данные будут удалены.</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="ph ph-trash me-1"></i>Удалить навсегда
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================ --}}
{{-- WITHDRAW CONFIRMATION MODAL                                       --}}
{{-- ================================================================ --}}
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="withdrawModalLabel">
                    <i class="ph ph-arrow-clockwise text-warning me-2"></i>Вернуть в черновик
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1 text-muted">Вы собираетесь отозвать:</p>
                <p class="fw-semibold" id="withdrawListingTitle">это объявление</p>
                <div class="alert alert-warning d-flex gap-2 align-items-start py-2 mb-0">
                    <i class="ph ph-info flex-shrink-0 mt-1"></i>
                    <small>Объявление вернётся в статус <strong>Черновик</strong> и все ожидающие запросы на оплату будут отменены. Вы можете редактировать и повторно отправить его на проверку в любое время.</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="withdrawForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="ph ph-arrow-clockwise me-1"></i>Вернуть в черновик
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Populate toggle-status modal
    $('#toggleStatusModal').on('show.bs.modal', function (event) {
        var trigger = $(event.relatedTarget);
        var slug    = trigger.data('slug');
        var title   = trigger.data('title');
        var action  = trigger.data('action'); // 'pause' or 'activate'

        $('#toggleStatusListingTitle').text(title);
        $('#toggleStatusForm').attr('action', '/owner/listings/' + slug + '/toggle-status');

        if (action === 'pause') {
            $('#toggleStatusTitle').text('Приостановить объявление');
            $('#toggleStatusIcon').attr('class', 'ph ph-pause-circle-fill text-warning me-2');
            $('#toggleStatusDesc').text('Вы собираетесь приостановить:');
            $('#toggleStatusAlert').removeClass('alert-success').addClass('alert-warning');
            $('#toggleStatusInfo').text('Объявление будет скрыто с сайта и перестанет отображаться в поиске. Вы сможете активировать его в любой момент.');
            $('#toggleStatusBtn').text('Приостановить').removeClass('btn-success').addClass('btn-warning');
        } else {
            $('#toggleStatusTitle').text('Активировать объявление');
            $('#toggleStatusIcon').attr('class', 'ph ph-play-circle text-success me-2');
            $('#toggleStatusDesc').text('Вы собираетесь активировать:');
            $('#toggleStatusAlert').removeClass('alert-warning').addClass('alert-success');
            $('#toggleStatusInfo').text('Объявление снова появится на сайте и будет доступно в поиске.');
            $('#toggleStatusBtn').text('Активировать').removeClass('btn-warning').addClass('btn-success');
        }
    });

    // Populate delete modal with listing data
    $('#deleteModal').on('show.bs.modal', function (event) {
        var trigger = $(event.relatedTarget);
        var slug    = trigger.data('slug');
        var title   = trigger.data('title');

        $('#deleteListingTitle').text(title);
        $('#deleteForm').attr('action', '/owner/listings/' + slug);
    });

    // Populate withdraw modal
    $('#withdrawModal').on('show.bs.modal', function (event) {
        var trigger    = $(event.relatedTarget);
        var listingId  = trigger.data('listing-id');
        var title      = trigger.data('listing-title');

        $('#withdrawListingTitle').text(title);
        $('#withdrawForm').attr('action', '/owner/listings/' + listingId + '/withdraw');
    });
</script>
@endsection
