@extends('layouts.admin')

@section('title', 'Детали платежа')

@section('content')

@php
    $pmtStatus = $payment->status instanceof \App\Enums\ListingPaymentStatus
        ? $payment->status->value
        : (string)$payment->status;

    $listingTitle = $payment->listing?->translations?->firstWhere('locale', 'az')?->title
                 ?? $payment->listing?->translations?->firstWhere('locale', 'ru')?->title
                 ?? $payment->listing?->translations?->firstWhere('locale', 'en')?->title
                 ?? '(Без названия)';

    $listingType = $payment->listing?->type instanceof \App\Enums\ListingType
        ? $payment->listing->type->value
        : (string)($payment->listing?->type ?? '');

    $listingStatus = $payment->listing?->status instanceof \App\Enums\ListingStatus
        ? $payment->listing->status->value
        : (string)($payment->listing?->status ?? '');

    $isImage = $payment->proof_file_path
        && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $payment->proof_file_path);
    $isPdf   = $payment->proof_file_path
        && preg_match('/\.pdf$/i', $payment->proof_file_path);
@endphp

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-credit-card me-2" style="color:#dc3545"></i>Детали платежа
        </h1>
        <p class="text-muted mb-0 small mt-1">
            Отправлено {{ $payment->created_at->format('d M Y, H:i') }}
        </p>
    </div>
    <a href="{{ route('admin.listing-payments.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ph ph-arrow-left me-1"></i>Назад к платежам
    </a>
</div>

{{-- ================================================================ --}}
{{-- STATUS BANNER                                                     --}}
{{-- ================================================================ --}}
@if($pmtStatus === 'pending')
    <div class="alert alert-warning d-flex gap-2 align-items-center mb-4">
        <i class="ph ph-hourglass flex-shrink-0"></i>
        <span><strong>Ожидает проверки</strong> — Этот платёж ожидает вашего решения.</span>
    </div>
@elseif($pmtStatus === 'cancelled')
    <div class="alert alert-secondary d-flex gap-2 align-items-center mb-4">
        <i class="ph ph-x-circle flex-shrink-0"></i>
        <span>
            <strong>Отменено</strong> — Владелец вернул это объявление в черновик
            @if($payment->cancelled_at)
                {{ $payment->cancelled_at->format('d M Y, H:i') }}
            @endif
            . Никаких действий не требуется.
        </span>
    </div>
@elseif($pmtStatus === 'approved')
    <div class="alert alert-success d-flex gap-2 align-items-center mb-4">
        <i class="ph ph-check-circle flex-shrink-0"></i>
        <span>
            <strong>Одобрено</strong> — Одобрено
            <strong>{{ $payment->reviewer?->name ?? '—' }}</strong>
            {{ $payment->reviewed_at?->format('d M Y, H:i') ?? '—' }}.
        </span>
    </div>
@elseif($pmtStatus === 'rejected')
    <div class="alert alert-danger d-flex gap-2 align-items-start mb-4">
        <i class="ph ph-x-circle flex-shrink-0 mt-1"></i>
        <div>
            <strong>Отклонено</strong> — Отклонено
            <strong>{{ $payment->reviewer?->name ?? '—' }}</strong>
            {{ $payment->reviewed_at?->format('d M Y, H:i') ?? '—' }}.
            @if($payment->admin_notes)
                <div class="mt-1">Причина: {{ $payment->admin_notes }}</div>
            @endif
        </div>
    </div>
@endif

{{-- ================================================================ --}}
{{-- TWO-COLUMN LAYOUT                                                 --}}
{{-- ================================================================ --}}
<div class="row g-4">

    {{-- ============================================================ --}}
    {{-- LEFT: Listing Details                                         --}}
    {{-- ============================================================ --}}
    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0 pt-3 px-3">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-buildings me-2 text-primary"></i>Детали объявления
                </h6>
            </div>
            <div class="card-body pt-3">
                <dl class="row mb-0" style="font-size:0.875rem">
                    <dt class="col-sm-4 text-muted fw-normal">Название</dt>
                    <dd class="col-sm-8 fw-semibold mb-2">{{ $listingTitle }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Slug</dt>
                    <dd class="col-sm-8 mb-2">
                        <code class="text-muted" style="font-size:0.8rem">
                            {{ $payment->listing?->slug ?? '—' }}
                        </code>
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Тип</dt>
                    <dd class="col-sm-8 mb-2">
                        @php
                            $typeBadgeMap = [
                                'hotel'      => ['label' => 'Hotel',      'class' => 'bg-primary'],
                                'home'       => ['label' => 'Home',       'class' => 'bg-info text-dark'],
                                'tour'       => ['label' => 'Tour',       'class' => 'bg-success'],
                                'activity'   => ['label' => 'Activity',  'class' => 'bg-warning text-dark'],
                                'guide'      => ['label' => 'Guide',      'class' => 'bg-secondary'],
                                'restaurant' => ['label' => 'Restaurant','class' => 'bg-danger'],
                            ];
                            $tb = $typeBadgeMap[$listingType] ?? ['label' => ucfirst($listingType), 'class' => 'bg-secondary'];
                        @endphp
                        <span class="badge {{ $tb['class'] }} rounded-pill" style="font-size:0.72rem">
                            {{ $tb['label'] }}
                        </span>
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Статус</dt>
                    <dd class="col-sm-8 mb-2">
                        <x-status-badge :status="$listingStatus" />
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Владелец</dt>
                    <dd class="col-sm-8 mb-2">
                        <div class="fw-semibold">{{ $payment->listing?->user?->name ?? '—' }}</div>
                        <div class="text-muted" style="font-size:0.8rem">
                            {{ $payment->listing?->user?->email ?? '' }}
                        </div>
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Создано</dt>
                    <dd class="col-sm-8 mb-0">
                        {{ $payment->listing?->created_at?->format('d M Y') ?? '—' }}
                    </dd>
                </dl>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- RIGHT: Payment Details                                        --}}
    {{-- ============================================================ --}}
    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pb-0 pt-3 px-3">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-receipt me-2 text-primary"></i>Детали платежа
                </h6>
            </div>
            <div class="card-body pt-3">
                <dl class="row mb-0" style="font-size:0.875rem">
                    <dt class="col-sm-4 text-muted fw-normal">Сумма</dt>
                    <dd class="col-sm-8 fw-bold mb-2" style="font-size:1rem">
                        {{ number_format($payment->amount, 2) }}
                        <span class="text-muted fw-normal" style="font-size:0.875rem">{{ $payment->currency }}</span>
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Метод</dt>
                    <dd class="col-sm-8 mb-2">
                        {{ $payment->payment_method
                            ? ucwords(str_replace('_', ' ', $payment->payment_method))
                            : '—' }}
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Ссылка</dt>
                    <dd class="col-sm-8 mb-2">
                        <code class="bg-light px-2 py-1 rounded" style="font-size:0.78rem">
                            {{ $payment->ulid ?? $payment->id }}
                        </code>
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Отправлено</dt>
                    <dd class="col-sm-8 mb-2">
                        {{ $payment->created_at->format('d M Y, H:i') }}
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Статус</dt>
                    <dd class="col-sm-8 mb-0">
                        @if($pmtStatus === 'pending')
                            <span class="badge bg-warning text-dark rounded-pill" style="font-size:0.72rem">Ожидает</span>
                        @elseif($pmtStatus === 'approved')
                            <span class="badge bg-success rounded-pill" style="font-size:0.72rem">Одобрено</span>
                        @elseif($pmtStatus === 'rejected')
                            <span class="badge bg-danger rounded-pill" style="font-size:0.72rem">Отклонено</span>
                        @elseif($pmtStatus === 'cancelled')
                            <span class="badge bg-secondary rounded-pill" style="font-size:0.72rem">Отменено</span>
                        @else
                            <span class="badge bg-secondary rounded-pill" style="font-size:0.72rem">{{ ucfirst($pmtStatus) }}</span>
                        @endif
                    </dd>

                    @if($payment->cancelled_at)
                        <dt class="col-sm-4 text-muted fw-normal mt-2">Отменено</dt>
                        <dd class="col-sm-8 mt-2">{{ $payment->cancelled_at->format('d M Y, H:i') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

    </div>

</div>

{{-- ================================================================ --}}
{{-- PAYMENT PROOF                                                     --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent border-0 pb-0 pt-3 px-3">
        <h6 class="fw-semibold mb-0">
            <i class="ph ph-file-text me-2 text-primary"></i>Файл подтверждения
        </h6>
    </div>
    <div class="card-body">
        @if($payment->proof_file_path)
            @if($isImage)
                <div class="text-center">
                    <img src="{{ asset('storage/' . $payment->proof_file_path) }}"
                         alt="Подтверждение платежа"
                         class="img-fluid rounded border"
                         style="max-height:500px; max-width:100%; object-fit:contain">
                </div>
                <div class="text-center mt-2">
                    <a href="{{ asset('storage/' . $payment->proof_file_path) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="ph ph-link me-1"></i>Открыть в полном размере
                    </a>
                </div>
            @elseif($isPdf)
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                    <i class="ph ph-file-pdf text-danger fs-2"></i>
                    <div>
                        <div class="fw-semibold">PDF-документ</div>
                        <div class="text-muted small">Подтверждение платежа в формате PDF</div>
                    </div>
                    <a href="{{ asset('storage/' . $payment->proof_file_path) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-primary ms-auto">
                        <i class="ph ph-download me-1"></i>Скачать PDF
                    </a>
                </div>
            @else
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                    <i class="ph ph-file-text fs-2 text-muted"></i>
                    <div>
                        <div class="fw-semibold">Загруженный файл</div>
                        <div class="text-muted small">{{ $payment->proof_file_path }}</div>
                    </div>
                    <a href="{{ asset('storage/' . $payment->proof_file_path) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-secondary ms-auto">
                        <i class="ph ph-download me-1"></i>Скачать
                    </a>
                </div>
            @endif
        @else
            <p class="text-muted mb-0"><em>Файл подтверждения не загружен.</em></p>
        @endif
    </div>
</div>

{{-- ================================================================ --}}
{{-- HOST NOTES                                                        --}}
{{-- ================================================================ --}}
@if($payment->notes)
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent border-0 pb-0 pt-3 px-3">
            <h6 class="fw-semibold mb-0">
                <i class="ph ph-chat-circle me-2 text-primary"></i>Заметки владельца
            </h6>
        </div>
        <div class="card-body">
            <p class="mb-0" style="white-space:pre-wrap">{{ $payment->notes }}</p>
        </div>
    </div>
@endif

{{-- ================================================================ --}}
{{-- ADMIN NOTES (shown if rejected)                                   --}}
{{-- ================================================================ --}}
@if($pmtStatus === 'rejected' && $payment->admin_notes)
    <div class="card border-0 shadow-sm mt-4 border-start border-danger border-3">
        <div class="card-header bg-transparent border-0 pb-0 pt-3 px-3">
            <h6 class="fw-semibold mb-0 text-danger">
                <i class="ph ph-shield-warning me-2"></i>Заметки администратора (Причина отклонения)
            </h6>
        </div>
        <div class="card-body">
            <p class="mb-0" style="white-space:pre-wrap">{{ $payment->admin_notes }}</p>
        </div>
    </div>
@endif

{{-- ================================================================ --}}
{{-- ACTION BUTTONS (pending only)                                     --}}
{{-- ================================================================ --}}
@if($pmtStatus === 'pending')
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body d-flex gap-3 flex-wrap align-items-center">
            <span class="text-muted small me-auto">
                <i class="ph ph-warning-circle me-1"></i>Проверьте файл подтверждения платежа выше перед принятием решения.
            </span>

            {{-- Approve --}}
            <form method="POST"
                  action="{{ route('admin.listing-payments.approve', $payment) }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="btn btn-success"
                        onclick="return confirm('Одобрить этот платёж и опубликовать объявление?')">
                    <i class="ph ph-check-circle me-2"></i>Одобрить платёж
                </button>
            </form>

            {{-- Reject --}}
            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#rejectModal"
                    data-payment-id="{{ $payment->id }}"
                    data-listing-title="{{ $listingTitle }}">
                <i class="ph ph-x-circle me-2"></i>Отклонить платёж
            </button>
        </div>
    </div>
@endif

{{-- ================================================================ --}}
{{-- REJECT MODAL                                                      --}}
{{-- ================================================================ --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="rejectModalLabel">
                    <i class="ph ph-x-circle text-danger me-2"></i>Отклонить платёж
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body pt-2">
                    <p class="text-muted mb-3">
                        Отклонение платежа для:
                        <span class="fw-semibold text-dark" id="rejectListingTitle"></span>
                    </p>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label fw-semibold small">
                            Причина отклонения <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control"
                                  id="rejectReason"
                                  name="reason"
                                  rows="4"
                                  maxlength="500"
                                  placeholder="Объясните, почему платёж отклоняется…"
                                  required></textarea>
                        <div class="form-text small text-muted">
                            Это сообщение будет показано владельцу.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ph ph-x-circle me-1"></i>Отклонить платёж
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $('#rejectModal').on('show.bs.modal', function (event) {
        var trigger       = $(event.relatedTarget);
        var paymentId     = trigger.data('payment-id');
        var listingTitle  = trigger.data('listing-title');

        $('#rejectListingTitle').text(listingTitle);
        $('#rejectForm').attr(
            'action',
            '/admin/listing-payments/' + paymentId + '/reject'
        );
        $('#rejectReason').val('');
    });
</script>
@endsection
