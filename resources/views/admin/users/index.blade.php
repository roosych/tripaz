@extends('layouts.admin')

@section('title', 'Управление пользователями')

@section('content')

{{-- ================================================================ --}}
{{-- PAGE HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="ph ph-users-three me-2" style="color:#dc3545"></i>Управление пользователями
        </h1>
        <p class="text-muted mb-0 small mt-1">Просмотр и управление учётными записями и ролями пользователей</p>
    </div>
    @if(isset($users) && method_exists($users, 'total'))
        <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size:0.8rem">
            {{ $users->total() }} пользователей
        </span>
    @endif
</div>

{{-- ================================================================ --}}
{{-- SEARCH BOX                                                        --}}
{{-- ================================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.users.index') }}" id="user-search-form">
            <div class="row g-2 align-items-center">
                <div class="col-sm-8 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="ph ph-magnifying-glass text-muted"></i></span>
                        <input type="text"
                               class="form-control"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Поиск по имени или email..."
                               autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('admin.users.index') }}"
                               class="btn btn-outline-secondary"
                               title="Clear search">
                                <i class="ph ph-x"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-magnifying-glass me-1"></i>Поиск
                    </button>
                </div>
                @if(request('search'))
                    <div class="col-auto">
                        <span class="text-muted small">
                            Результаты для: <strong>&ldquo;{{ request('search') }}&rdquo;</strong>
                        </span>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ================================================================ --}}
{{-- USERS TABLE                                                       --}}
{{-- ================================================================ --}}
@if(isset($users) && $users->isNotEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width:50px">#</th>
                            <th class="py-3" style="min-width:180px">Имя</th>
                            <th class="py-3">Email</th>
                            <th class="py-3" style="min-width:140px">Роли</th>
                            <th class="py-3">Дата регистрации</th>
                            <th class="pe-3 py-3" style="min-width:200px">Изменить роль</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                {{-- ID --}}
                                <td class="ps-3 text-muted small">{{ $user->id }}</td>

                                {{-- Name --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white flex-shrink-0"
                                             style="width:30px;height:30px;font-size:0.75rem;font-weight:700">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <span class="fw-semibold" style="font-size:0.9rem">{{ $user->name }}</span>
                                    </div>
                                </td>

                                {{-- Email --}}
                                <td class="text-muted" style="font-size:0.875rem">
                                    {{ $user->email }}
                                </td>

                                {{-- Current Roles --}}
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($user->getRoleNames() as $role)
                                            @php
                                                $roleClass = match($role) {
                                                    'admin' => 'bg-danger',
                                                    'owner' => 'bg-primary',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $roleClass }} rounded-pill" style="font-size:0.65rem">
                                                {{ ucfirst($role) }}
                                            </span>
                                        @empty
                                            <span class="text-muted small">Нет ролей</span>
                                        @endforelse
                                    </div>
                                </td>

                                {{-- Joined --}}
                                <td class="text-muted small">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                {{-- Edit Role inline form --}}
                                <td class="pe-3">
                                    <form method="POST"
                                          action="{{ route('admin.users.role', $user) }}"
                                          class="d-flex align-items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select class="form-select form-select-sm" name="role" style="min-width:100px">
                                            @foreach($roles ?? ['user', 'owner', 'admin'] as $role)
                                                <option value="{{ $role }}"
                                                        {{ $user->hasRole($role) ? 'selected' : '' }}>
                                                    {{ ucfirst($role) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                            Обновить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-center py-3">
                {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(request('search'))
                <x-empty-state
                    icon="ph-magnifying-glass"
                    title="Пользователи не найдены"
                    message="Ни один пользователь не соответствует вашему запросу. Попробуйте другое имя или email."
                    actionLabel="Сбросить поиск"
                    actionUrl="{{ route('admin.users.index') }}" />
            @else
                <x-empty-state
                    icon="ph-users-three"
                    title="Пользователей пока нет"
                    message="Зарегистрированные пользователи появятся здесь." />
            @endif
        </div>
    </div>

@endif

@endsection

@section('scripts')
<script>
    // Auto-submit search on clear (when input is emptied and user presses Enter)
    $('#search').on('keydown', function (e) {
        if (e.key === 'Enter' && $(this).val() === '') {
            e.preventDefault();
            window.location.href = '{{ route('admin.users.index') }}';
        }
    });
</script>
@endsection
