@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm px-3">
        <i class="fas fa-plus me-1"></i> Nuevo empleado
    </a>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#eff6ff;">
                    <i class="fas fa-users" style="color:#2563eb;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark lh-1">{{ $stats['employees'] }}</div>
                    <div class="text-muted small">Empleados</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#f0fdf4;">
                    <i class="fas fa-building" style="color:#16a34a;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark lh-1">{{ $stats['branches'] }}</div>
                    <div class="text-muted small">Sucursales</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#fef9c3;">
                    <i class="fas fa-location-dot" style="color:#ca8a04;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-dark lh-1">{{ $stats['cities'] }}</div>
                    <div class="text-muted small">Ciudades</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Empleados recientes --}}
    <div class="col-lg-8">
        <div class="card content-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="fas fa-clock me-2 text-primary"></i>Empleados recientes</span>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-primary">
                    Ver todos
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentEmployees->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-2x mb-2 opacity-25"></i>
                        <p class="mb-0 small">No hay empleados aún.</p>
                        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm mt-3">
                            Crear primer empleado
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr>
                                <th class="ps-4">Empleado</th>
                                <th>Cargo</th>
                                <th>Sucursal</th>
                                <th class="pe-4"></th>
                            </tr></thead>
                            <tbody>
                            @foreach($recentEmployees as $emp)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($emp->photo_url)
                                                <img src="{{ $emp->photo_url }}" class="avatar" alt="">
                                            @else
                                                <div class="avatar d-flex" style="background:#e0e7ff;color:#4f46e5;">
                                                    {{ $emp->initials }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size:.875rem;">{{ $emp->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted" style="font-size:.85rem;">{{ $emp->position }}</td>
                                    <td>
                                        <span class="badge badge-branch rounded-pill">
                                            <i class="fas fa-location-dot me-1"></i>{{ $emp->branch->city ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('card.show', $emp->slug) }}" target="_blank"
                                               class="btn btn-sm btn-outline-secondary" title="Ver tarjeta">
                                                <i class="fas fa-arrow-up-right-from-square fa-sm"></i>
                                            </a>
                                            <button onclick="showQr({{ $emp->id }}, '{{ $emp->name }}', '{{ route('admin.employees.qr', $emp) }}', '{{ route('card.show', $emp->slug) }}')"
                                                    class="btn btn-sm btn-outline-primary" title="Ver QR">
                                                <i class="fas fa-qrcode fa-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sucursales --}}
    <div class="col-lg-4">
        <div class="card content-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="fas fa-building me-2 text-success"></i>Sucursales</span>
                <a href="{{ route('admin.branches.create') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-plus fa-sm"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($branches->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-building fa-2x mb-2 opacity-25"></i>
                        <p class="mb-0 small">No hay sucursales.</p>
                        <a href="{{ route('admin.branches.create') }}" class="btn btn-success btn-sm mt-3">
                            Crear sucursal
                        </a>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                    @foreach($branches as $branch)
                        <li class="list-group-item border-0 px-4 py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold" style="font-size:.875rem;">{{ $branch->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        <i class="fas fa-location-dot me-1"></i>{{ $branch->city }}
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark fw-semibold">
                                    {{ $branch->employees_count }} <i class="fas fa-user fa-xs ms-1"></i>
                                </span>
                            </div>
                        </li>
                    @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
