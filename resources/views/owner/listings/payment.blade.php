@extends('layouts.owner')

@section('title', 'Активировать объявление')

@section('content')

@php
    $title = $listing->translations->firstWhere('locale', 'az')?->title
           ?? $listing->translations->firstWhere('locale', 'ru')?->title
           ?? $listing->translations->firstWhere('locale', 'en')?->title
           ?? '(Без названия)';

    $pmtStatus = $latestPayment
        ? ($latestPayment->status instanceof \App\Enums\ListingPaymentStatus
            ? $latestPayment->status->value
            : (string)$latestPayment->status)
        : null;
@endphp

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-credit-card me-2 text-primary"></i>Активировать объявление
        </h1>
        <p class="text-muted mb-0 small mt-1">{{ $title }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('owner.listings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph ph-arrow-left me-1"></i>Назад к объявлениям
        </a>
        @if($pmtStatus !== 'pending')
            <button type="button"
                    class="btn btn-sm btn-outline-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#withdrawModal">
                <i class="ph ph-arrow-clockwise me-1"></i>Вернуть в черновик
            </button>
        @endif
    </div>
</div>

{{-- ================================================================ --}}
{{-- INFO ALERT                                                        --}}
{{-- ================================================================ --}}
<div class="alert alert-info d-flex gap-2 align-items-start mb-4">
    <i class="ph ph-info flex-shrink-0 mt-1"></i>
    <div>
        <strong>Требуется оплата</strong><br>
        Это объявление требует активации, потому что ваш лимит бесплатных объявлений исчерпан.
        Пожалуйста, завершите оплату ниже, чтобы опубликовать объявление.
    </div>
</div>

<div class="row g-4">

    {{-- ============================================================ --}}
    {{-- LEFT COLUMN: Price + Bank Instructions                        --}}
    {{-- ============================================================ --}}
    <div class="col-lg-5">

        {{-- Price Card --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center py-4">
                <div class="mb-2">
                    <i class="ph ph-shield display-4 text-success"></i>
                </div>
                <div class="display-6 fw-bold text-dark mb-1">
                    {{ number_format($price, 2) }} {{ $currency }}
                </div>
                <p class="text-muted small mb-0">Оплата необходима для публикации этого объявления</p>
            </div>
        </div>

        {{-- Payment Instructions Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0 pt-3 px-3">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-bank me-2 text-primary"></i>Реквизиты для банковского перевода
                </h6>
                <p class="text-muted small mb-0 mt-1">Отправьте платёж на счёт ниже</p>
            </div>
            <div class="card-body pt-3">
                <dl class="row mb-0" style="font-size:0.875rem">
                    <dt class="col-sm-5 text-muted fw-normal">Банк</dt>
                    <dd class="col-sm-7 fw-semibold mb-2">Kapital Bank</dd>

                    <dt class="col-sm-5 text-muted fw-normal">IBAN</dt>
                    <dd class="col-sm-7 fw-semibold mb-2" style="word-break:break-all">
                        AZ12 KPBR 1234 5678 9012 3456
                    </dd>

                    <dt class="col-sm-5 text-muted fw-normal">Сумма</dt>
                    <dd class="col-sm-7 fw-semibold mb-2">
                        {{ number_format($price, 2) }} {{ $currency }}
                    </dd>

                    <dt class="col-sm-5 text-muted fw-normal">Ссылка</dt>
                    <dd class="col-sm-7 mb-0">
                        <code class="bg-light px-2 py-1 rounded" style="font-size:0.8rem">
                            Listing #{{ $listing->ulid }}
                        </code>
                        <div class="text-muted mt-1" style="font-size:0.75rem">
                            Укажите этот код при переводе
                        </div>
                    </dd>
                </dl>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- RIGHT COLUMN: Status / Upload Form                            --}}
    {{-- ============================================================ --}}
    <div class="col-lg-7">

        @if($pmtStatus === 'cancelled')

            {{-- Payment was cancelled by host --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ph ph-x-circle display-3 text-secondary"></i>
                    </div>
                    <h5 class="fw-semibold mb-2 text-muted">Запрос на оплату отменён</h5>
                    <p class="text-muted mb-4">
                        Вы ранее отозвали это объявление. Вы можете отправить новое подтверждение оплаты или снова отозвать объявление.
                    </p>
                    <a href="{{ route('owner.listings.payment.show', $listing) }}" class="btn btn-outline-primary">
                        <i class="ph ph-arrow-clockwise me-1"></i>Отправить новый платёж
                    </a>
                </div>
            </div>

        @elseif($pmtStatus === 'pending')

            {{-- Payment is under review --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ph ph-hourglass display-3 text-warning"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Подтверждение оплаты на проверке</h5>
                    <p class="text-muted mb-0">
                        Ваше подтверждение оплаты отправлено и в настоящее время проверяется.
                        Мы уведомим вас, когда оно будет одобрено.
                    </p>
                    <div class="mt-4 pt-2 border-top">
                        <div class="row text-start g-2" style="font-size:0.875rem">
                            <div class="col-sm-4 text-muted">Отправлено</div>
                            <div class="col-sm-8 fw-semibold">
                                {{ $latestPayment->created_at->format('d M Y, H:i') }}
                            </div>
                            <div class="col-sm-4 text-muted">Метод</div>
                            <div class="col-sm-8 fw-semibold">{{ $latestPayment->payment_method ?? '—' }}</div>
                            @if($latestPayment->notes)
                                <div class="col-sm-4 text-muted">Ваши заметки</div>
                                <div class="col-sm-8">{{ $latestPayment->notes }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @else

            {{-- Show rejection alert if previous submission was rejected --}}
            @if($pmtStatus === 'rejected')
                <div class="alert alert-danger d-flex gap-2 align-items-start mb-3">
                    <i class="ph ph-x-circle flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>Предыдущий платёж был отклонён.</strong>
                        @if($latestPayment->admin_notes)
                            <div class="mt-1">Причина: {{ $latestPayment->admin_notes }}</div>
                        @endif
                        <div class="mt-1 small text-muted">Пожалуйста, ознакомьтесь с причиной и повторно отправьте подтверждение оплаты.</div>
                    </div>
                </div>
            @endif

            {{-- Upload Form --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0 pt-3 px-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="ph ph-upload me-2 text-primary"></i>Отправить подтверждение оплаты
                    </h6>
                    <p class="text-muted small mb-0 mt-1">
                        После банковского перевода загрузите квитанцию ниже
                    </p>
                </div>
                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger py-2 mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li class="small">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('owner.listings.payment.store', $listing) }}"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- Payment Method --}}
                        <div class="mb-3">
                            <label for="payment_method" class="form-label fw-semibold small">
                                Метод оплаты <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('payment_method') is-invalid @enderror"
                                    id="payment_method"
                                    name="payment_method"
                                    required>
                                <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>
                                    Выберите метод оплаты
                                </option>
                                @foreach(['bank_transfer' => 'Банковский перевод', 'credit_card' => 'Кредитная карта', 'cash' => 'Наличные', 'other' => 'Другое'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('payment_method') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Proof File --}}
                        <div class="mb-3">
                            <label for="proof_file" class="form-label fw-semibold small">
                                Подтверждение оплаты <span class="text-danger">*</span>
                            </label>
                            <input type="file"
                                   class="form-control @error('proof_file') is-invalid @enderror"
                                   id="proof_file"
                                   name="proof_file"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   required>
                            <div class="form-text small text-muted">
                                Загрузите квитанцию об оплате или скриншот (JPG, PNG или PDF, максимум 5 МБ)
                            </div>
                            @error('proof_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold small">
                                Дополнительные заметки <span class="text-muted fw-normal">(необязательно)</span>
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="3"
                                      maxlength="1000"
                                      placeholder="Любая дополнительная информация для администратора, проверяющего ваш платёж…">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph ph-paper-plane-tilt me-2"></i>Отправить подтверждение оплаты
                            </button>
                            <a href="{{ route('owner.listings.index') }}" class="btn btn-outline-secondary">
                                Отмена
                            </a>
                            <span class="text-muted ms-auto" style="font-size:0.75rem">
                                <i class="ph ph-shield me-1"></i>Максимум 5 загрузок в минуту
                            </span>
                        </div>

                    </form>
                </div>
            </div>

        @endif

    </div>
</div>

{{-- ================================================================ --}}
{{-- WITHDRAW MODAL                                                    --}}
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
                <p class="text-muted mb-2">
                    Отзыв <strong>{{ $title }}</strong> вернёт его в статус <strong>Черновик</strong>
                    и отменит все ожидающие запросы на оплату.
                </p>
                <div class="alert alert-warning d-flex gap-2 align-items-start py-2 mb-0">
                    <i class="ph ph-info flex-shrink-0 mt-1"></i>
                    <small>Вы можете редактировать и повторно отправить объявление на проверку в любое время.</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form method="POST" action="{{ route('owner.listings.payment.withdraw', $listing) }}">
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
