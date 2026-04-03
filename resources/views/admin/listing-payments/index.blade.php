@extends('layouts.admin')

@section('title', 'Оплаты объявлений')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-credit-card me-2" style="color:#dc3545"></i>Оплаты объявлений
            @if($pendingCount > 0)
                <span class="badge bg-warning text-dark rounded-pill ms-2" style="font-size:0.75rem">
                    {{ $pendingCount }} ожидает
                </span>
            @endif
        </h1>
        <p class="text-muted mb-0 small mt-1">Просматривайте и управляйте заявками на оплату активации объявлений</p>
    </div>
    @if(method_exists($payments, 'total'))
        <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
            {{ $payments->total() }} всего
        </span>
    @endif
</div>

{{-- ================================================================ --}}
{{-- FILTERS                                                          --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <form method="GET" action="{{ route('admin.listing-payments.index') }}"
              class="d-flex flex-wrap gap-2 align-items-end">

            {{-- Status filter --}}
            <div style="min-width:130px">
                <label class="form-label small fw-semibold mb-1">Статус</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Все статусы</option>
                    @foreach(['pending' => 'Ожидает', 'approved' => 'Одобрено', 'rejected' => 'Отклонено', 'cancelled' => 'Отменено'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Listing type filter --}}
            <div style="min-width:130px">
                <label class="form-label small fw-semibold mb-1">Тип объявления</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Все типы</option>
                    @foreach(['hotel' => 'Hotel', 'home' => 'Home', 'tour' => 'Tour', 'activity' => 'Activity', 'guide' => 'Guide', 'restaurant' => 'Restaurant'] as $val => $label)
                        <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Host search --}}
            <div style="min-width:180px">
                <label class="form-label small fw-semibold mb-1">Владелец</label>
                <input type="text"
                       name="host"
                       class="form-control form-control-sm"
                       placeholder="Имя или email…"
                       value="{{ request('host') }}">
            </div>

            <button type="submit" class="btn btn-sm btn-primary">
                <i class="ph ph-funnel me-1"></i>Фильтр
            </button>

            @if(request()->hasAny(['status', 'type', 'host']))
                <a href="{{ route('admin.listing-payments.index') }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="ph ph-x-circle me-1"></i>Сбросить
                </a>
            @endif

        </form>
    </div>
</div>

{{-- ================================================================ --}}
{{-- PAYMENTS TABLE                                                    --}}
{{-- ================================================================ --}}
@if($payments->isEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <x-empty-state
                icon="ph-credit-card"
                title="Платежей пока нет"
                message="Заявки на оплату объявлений ещё не поступали." />
        </div>
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="min-width:200px">Объявление</th>
                            <th class="py-3">Ссылка</th>
                            <th class="py-3">Владелец</th>
                            <th class="py-3">Сумма</th>
                            <th class="py-3">Метод</th>
                            <th class="py-3">Статус</th>
                            <th class="py-3">Отправлено</th>
                            <th class="pe-3 py-3 text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $pmtStatus = $payment->status instanceof \App\Enums\ListingPaymentStatus
                                    ? $payment->status->value
                                    : (string)$payment->status;

                                $listingTitle = $payment->listing?->translations?->firstWhere('locale', 'az')?->title
                                             ?? $payment->listing?->translations?->firstWhere('locale', 'ru')?->title
                                             ?? $payment->listing?->translations?->firstWhere('locale', 'en')?->title
                                             ?? '(Без названия)';
                            @endphp
                            <tr>
                                {{-- Listing --}}
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark" style="font-size:0.9rem">
                                        {{ $listingTitle }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.78rem">
                                        {{ $payment->listing?->slug ?? '—' }}
                                    </div>
                                </td>

                                {{-- Reference (ULID) --}}
                                <td>
                                    <code class="text-muted bg-light px-1 rounded"
                                          style="font-size:0.72rem; word-break:break-all; max-width:100px; display:inline-block">
                                        {{ $payment->ulid ?? $payment->id }}
                                    </code>
                                </td>

                                {{-- Host --}}
                                <td>
                                    <div class="fw-semibold" style="font-size:0.875rem">
                                        {{ $payment->user?->name ?? '—' }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.78rem">
                                        {{ $payment->user?->email ?? '' }}
                                    </div>
                                </td>

                                {{-- Amount --}}
                                <td>
                                    <span class="fw-semibold text-dark" style="font-size:0.875rem">
                                        {{ number_format($payment->amount, 2) }}
                                    </span>
                                    <span class="text-muted small">{{ $payment->currency }}</span>
                                </td>

                                {{-- Method --}}
                                <td class="small text-muted">
                                    {{ $payment->payment_method
                                        ? ucwords(str_replace('_', ' ', $payment->payment_method))
                                        : '—' }}
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if($pmtStatus === 'pending')
                                        <span class="badge bg-warning text-dark rounded-pill" style="font-size:0.72rem">
                                            Ожидает
                                        </span>
                                    @elseif($pmtStatus === 'approved')
                                        <span class="badge bg-success rounded-pill" style="font-size:0.72rem">
                                            Одобрено
                                        </span>
                                    @elseif($pmtStatus === 'rejected')
                                        <span class="badge bg-danger rounded-pill" style="font-size:0.72rem">
                                            Отклонено
                                        </span>
                                    @elseif($pmtStatus === 'cancelled')
                                        <span class="badge bg-secondary rounded-pill" style="font-size:0.72rem">
                                            Отменено
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill" style="font-size:0.72rem">
                                            {{ ucfirst($pmtStatus) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Submitted --}}
                                <td class="text-muted small">
                                    {{ $payment->created_at->format('d M Y') }}
                                    <div style="font-size:0.72rem">{{ $payment->created_at->format('H:i') }}</div>
                                </td>

                                {{-- Actions --}}
                                <td class="pe-3 text-end">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap action-btn-group">

                                        {{-- Review --}}
                                        <a href="{{ route('admin.listing-payments.show', $payment) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Просмотр деталей">
                                            <i class="ph ph-eye me-1"></i>Просмотр
                                        </a>

                                        @if($pmtStatus === 'pending')
                                            {{-- Quick Approve --}}
                                            <form method="POST"
                                                  action="{{ route('admin.listing-payments.approve', $payment) }}"
                                                  class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-sm btn-success"
                                                        title="Одобрить платёж"
                                                        onclick="return confirm('Одобрить этот платёж и опубликовать объявление?')">
                                                    <i class="ph ph-check me-1"></i>Одобрить
                                                </button>
                                            </form>

                                            {{-- Reject (modal) --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Отклонить платёж"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal"
                                                    data-payment-id="{{ $payment->id }}"
                                                    data-listing-title="{{ $listingTitle }}">
                                                <i class="ph ph-x me-1"></i>Отклонить
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-center py-3">
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>
        @endif
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
                        Отклонение платежа для: <span class="fw-semibold text-dark" id="rejectListingTitle"></span>
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
        var trigger = $(event.relatedTarget);
        var paymentId    = trigger.data('payment-id');
        var listingTitle = trigger.data('listing-title');

        $('#rejectListingTitle').text(listingTitle);
        $('#rejectForm').attr(
            'action',
            '/admin/listing-payments/' + paymentId + '/reject'
        );
        $('#rejectReason').val('');
    });
</script>
@endsection
