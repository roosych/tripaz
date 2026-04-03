@extends('layouts.admin')

@section('title', 'Опции полей')

@section('content')

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-check-square me-2 text-primary"></i>Опции полей
        </h1>
        <p class="text-muted mb-0 small mt-1">Управление значениями выпадающих списков и групп кнопок</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex gap-2 align-items-center mt-3">
        <i class="ph ph-check-circle flex-shrink-0"></i>{{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger d-flex gap-2 align-items-start mt-3">
        <i class="ph ph-warning-circle flex-shrink-0 mt-1"></i>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li class="small">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4 mt-1">

    {{-- -------------------------------------------------------- --}}
    {{-- LEFT: Type selector                                      --}}
    {{-- -------------------------------------------------------- --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0 small text-uppercase text-muted" style="letter-spacing:0.6px">Тип опций</h6>
            </div>
            <div class="list-group list-group-flush rounded-bottom">
                @foreach($types as $typeKey => $typeLabel)
                    <a href="{{ route('admin.lookup-options.index', ['type' => $typeKey]) }}"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 px-3 {{ $currentType === $typeKey ? 'active' : '' }}"
                       style="font-size:0.88rem">
                        @php
                            $icon = match($typeKey) {
                                'language'      => 'ph-translate',
                                'cuisine_type'  => 'ph-fork-knife',
                                'property_type' => 'ph-house',
                                'difficulty'    => 'ph-chart-bar',
                                default         => 'ph-list-checks',
                            };
                        @endphp
                        <i class="ph {{ $icon }}"></i>
                        {{ $typeLabel }}
                        <span class="badge bg-secondary rounded-pill ms-auto" style="font-size:0.65rem">
                            {{ \App\Models\LookupOption::where('type', $typeKey)->count() }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- -------------------------------------------------------- --}}
    {{-- RIGHT: Options list + add form                          --}}
    {{-- -------------------------------------------------------- --}}
    <div class="col-lg-9">

        {{-- Options table --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex align-items-center justify-content-between">
                <h6 class="fw-semibold mb-0">
                    {{ $types[$currentType] }}
                    <span class="text-muted fw-normal ms-1">({{ $options->count() }})</span>
                </h6>
            </div>

            @if($options->isEmpty())
                <div class="card-body text-center text-muted py-4 small">
                    <i class="ph ph-tray fs-4 d-block mb-2"></i>
                    Нет опций. Добавьте первую ниже.
                </div>
            @else
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-2" style="width:36px"></th>
                                <th class="py-2" style="width:130px">Значение</th>
                                <th class="py-2">Метка</th>
                                <th class="pe-3 py-2 text-end" style="width:80px">Действия</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-options">
                            @foreach($options as $option)
                                <tr data-id="{{ $option->id }}">
                                    <td class="ps-3 align-middle text-muted" style="cursor:grab">
                                        <i class="ph ph-dots-six-vertical"></i>
                                    </td>
                                    <td class="align-middle">
                                        <code class="small">{{ $option->value }}</code>
                                    </td>
                                    <td class="align-middle">
                                        <form method="POST"
                                              action="{{ route('admin.lookup-options.update', $option) }}"
                                              class="d-flex gap-2 align-items-center">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="sort_order" value="{{ $option->sort_order }}">
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   name="label"
                                                   value="{{ $option->label }}"
                                                   style="max-width:240px"
                                                   required>
                                            <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2" title="Сохранить">
                                                <i class="ph ph-floppy-disk"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="pe-3 text-end align-middle">
                                        <form method="POST"
                                              action="{{ route('admin.lookup-options.destroy', $option) }}"
                                              onsubmit="return confirm('Удалить «{{ $option->label }}»?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Удалить">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Save feedback toast --}}
                    <div id="sort-toast" class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;display:none">
                        <div class="toast align-items-center text-bg-success border-0 show" role="alert">
                            <div class="d-flex">
                                <div class="toast-body py-2 small">
                                    <i class="ph ph-check me-1"></i> Порядок сохранён
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Add new option --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-semibold mb-0">
                    <i class="ph ph-plus-circle me-2 text-success"></i>Добавить опцию
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.lookup-options.store') }}">
                    @csrf
                    <input type="hidden" name="type" value="{{ $currentType }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold small">Значение <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('value') is-invalid @enderror"
                                   name="value"
                                   value="{{ old('value') }}"
                                   placeholder="напр: georgian"
                                   pattern="[a-z0-9_]+"
                                   title="Только строчные буквы, цифры и _"
                                   required>
                            <div class="form-text">Только a–z, 0–9 и _</div>
                            @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label fw-semibold small">Метка (название) <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('label') is-invalid @enderror"
                                   name="label"
                                   value="{{ old('label') }}"
                                   placeholder="напр: Georgian"
                                   required>
                            @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label fw-semibold small">Порядок</label>
                            <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}">
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var tbody = document.getElementById('sortable-options');
    if (!tbody) return;

    var toastEl = document.getElementById('sort-toast');
    var toastTimer;

    function showToast() {
        toastEl.style.display = 'block';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.style.display = 'none'; }, 2000);
    }

    Sortable.create(tbody, {
        animation: 150,
        handle: 'td:first-child',
        ghostClass: 'table-active',

        onEnd: function () {
            var ids = Array.from(tbody.querySelectorAll('tr[data-id]'))
                .map(function (tr) { return tr.dataset.id; });

            fetch('{{ route('admin.lookup-options.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: ids }),
            })
            .then(function (r) { if (r.ok) showToast(); });
        },
    });
})();
</script>
@endsection
