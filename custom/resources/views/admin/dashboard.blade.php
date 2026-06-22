@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm px-3">
        <i class="fas fa-plus me-1"></i> Nuevo empleado
    </a>
@endsection

@push('styles')
<style>
/* ─── KPI Cards ─────────────────────────────────────────────────── */
.kpi-card {
    border: none;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.04);
    overflow: hidden;
    transition: box-shadow .2s ease, transform .2s ease;
}
.kpi-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,.1), 0 0 0 1px rgba(0,0,0,.06);
    transform: translateY(-2px);
}
.kpi-accent {
    height: 4px;
}
.kpi-icon {
    width: 50px; height: 50px;
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.kpi-value {
    font-size: 2.25rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
    letter-spacing: -0.5px;
    font-variant-numeric: tabular-nums;
}
.kpi-label {
    font-size: .8rem;
    color: #64748b;
    font-weight: 500;
    letter-spacing: .2px;
    margin-top: 4px;
}
.kpi-footer {
    font-size: .72rem;
    color: #94a3b8;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 5px;
}
.kpi-badge {
    font-size: .68rem;
    padding: 3px 9px;
    border-radius: 20px;
    font-weight: 600;
}

/* ─── Dashboard Content Cards ────────────────────────────────────── */
.dash-card {
    border: none;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.04);
}
.dash-card .card-header {
    background: none;
    border-bottom: 1px solid #f1f5f9;
    padding: 1rem 1.5rem;
    font-weight: 600;
    font-size: .875rem;
    color: #0f172a;
}
.dash-card .header-icon {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .8rem;
    margin-right: .5rem;
    flex-shrink: 0;
}

/* ─── Employee Table ──────────────────────────────────────────────── */
.emp-table th {
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #94a3b8;
    border-top: none;
    background: #f8fafc;
    padding: .6rem 1rem;
}
.emp-table th:first-child { padding-left: 1.5rem; }
.emp-table th:last-child  { padding-right: 1.5rem; }
.emp-table td {
    vertical-align: middle;
    font-size: .875rem;
    padding: .7rem 1rem;
    border-bottom: 1px solid #f8fafc;
    border-top: none;
}
.emp-table td:first-child { padding-left: 1.5rem; }
.emp-table td:last-child  { padding-right: 1.5rem; }
.emp-table tbody tr:hover { background: #fafcff; }
.emp-table tbody tr:last-child td { border-bottom: none; }

/* ─── Branch List ─────────────────────────────────────────────────── */
.branch-item {
    padding: .875rem 1.5rem;
    border-bottom: 1px solid #f8fafc;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background .15s;
}
.branch-item:last-child { border-bottom: none; }
.branch-item:hover { background: #fafcff; }
.branch-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #8dc63f;
    flex-shrink: 0;
    margin-right: .7rem;
}
.branch-count {
    min-width: 30px; height: 26px;
    padding: 0 8px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    font-size: .75rem;
    font-weight: 600;
    color: #475569;
    display: flex; align-items: center; justify-content: center;
    gap: 4px;
}
</style>
@endpush

@section('content')

{{-- ─── KPI Row ─────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4 align-items-start">

    {{-- Empleados --}}
    <div class="col-sm-4">
        <div class="kpi-card">
            <div class="kpi-accent" style="background:linear-gradient(90deg,#2563eb,#60a5fa);"></div>
            <div class="p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                        <i class="fas fa-users" style="color:#2563eb;"></i>
                    </div>
                    <span class="kpi-badge" style="background:#eff6ff;color:#2563eb;">Totales</span>
                </div>
                <div class="kpi-value">{{ $stats['employees'] }}</div>
                <div class="kpi-label">Empleados registrados</div>
                <div class="kpi-footer">
                    <i class="fas fa-building" style="font-size:.6rem;"></i>
                    En {{ $stats['branches'] }} sucursal{{ $stats['branches'] != 1 ? 'es' : '' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Sucursales --}}
    <div class="col-sm-4">
        <div class="kpi-card">
            <div class="kpi-accent" style="background:linear-gradient(90deg,#8dc63f,#a3e635);"></div>
            <div class="p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                        <i class="fas fa-building" style="color:#16a34a;"></i>
                    </div>
                    <span class="kpi-badge" style="background:#f0fdf4;color:#16a34a;">Activas</span>
                </div>
                <div class="kpi-value">{{ $stats['branches'] }}</div>
                <div class="kpi-label">Sucursales activas</div>
                <div class="kpi-footer">
                    <i class="fas fa-location-dot" style="font-size:.6rem;"></i>
                    En {{ $stats['cities'] }} ciudad{{ $stats['cities'] != 1 ? 'es' : '' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Ciudades --}}
    <div class="col-sm-4">
        <div class="kpi-card">
            <div class="kpi-accent" style="background:linear-gradient(90deg,#f59e0b,#fbbf24);"></div>
            <div class="p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);">
                        <i class="fas fa-location-dot" style="color:#d97706;"></i>
                    </div>
                    <span class="kpi-badge" style="background:#fffbeb;color:#d97706;">Cobertura</span>
                </div>
                <div class="kpi-value">{{ $stats['cities'] }}</div>
                <div class="kpi-label">Ciudades con presencia</div>
                <div class="kpi-footer">
                    <i class="fas fa-map" style="font-size:.6rem;"></i>
                    Alcance geográfico
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ─── Content Row ──────────────────────────────────────────────── --}}
<div class="row g-4 align-items-start">

    {{-- Empleados recientes --}}
    <div class="col-lg-8">
        <div class="dash-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="header-icon" style="background:#eff6ff;">
                        <i class="fas fa-clock" style="color:#2563eb;"></i>
                    </span>
                    Empleados recientes
                </div>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;">
                    Ver todos <i class="fas fa-arrow-right fa-xs ms-1"></i>
                </a>
            </div>

            @if($recentEmployees->isEmpty())
                <div class="text-center py-5">
                    <div class="mx-auto mb-3" style="width:52px;height:52px;border-radius:14px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-users" style="color:#cbd5e1;font-size:1.25rem;"></i>
                    </div>
                    <p class="fw-semibold mb-1" style="color:#475569;font-size:.875rem;">Sin empleados aún</p>
                    <p class="text-muted small mb-3">Comienza agregando el primer empleado</p>
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-plus me-1"></i> Crear empleado
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0 emp-table">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Cargo</th>
                                <th>Sucursal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($recentEmployees as $emp)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($emp->photo_url)
                                            <img src="{{ $emp->photo_url }}" class="avatar" alt="{{ $emp->name }}">
                                        @else
                                            <div class="avatar d-flex" style="background:#e0e7ff;color:#4f46e5;">
                                                {{ $emp->initials }}
                                            </div>
                                        @endif
                                        <span class="fw-semibold text-dark" style="font-size:.875rem;">{{ $emp->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size:.82rem;color:#64748b;">{{ $emp->position }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-branch rounded-pill">
                                        <i class="fas fa-location-dot me-1"></i>{{ $emp->branch->city ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('card.show', $emp->slug) }}" target="_blank"
                                           class="btn btn-sm btn-outline-secondary" title="Ver tarjeta"
                                           style="font-size:.7rem;padding:.25rem .55rem;">
                                            <i class="fas fa-arrow-up-right-from-square"></i>
                                        </a>
                                        <button onclick="showQr({{ $emp->id }}, '{{ $emp->name }}', '{{ route('admin.employees.qr', $emp) }}', '{{ route('card.show', $emp->slug) }}')"
                                                class="btn btn-sm btn-outline-primary" title="Ver QR"
                                                style="font-size:.7rem;padding:.25rem .55rem;">
                                            <i class="fas fa-qrcode"></i>
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

    {{-- Sucursales --}}
    <div class="col-lg-4">
        <div class="dash-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="header-icon" style="background:#f0fdf4;">
                        <i class="fas fa-building" style="color:#16a34a;"></i>
                    </span>
                    Sucursales
                </div>
                <a href="{{ route('admin.branches.create') }}"
                   class="btn btn-sm btn-outline-success"
                   title="Nueva sucursal"
                   style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;">
                    <i class="fas fa-plus fa-xs"></i>
                </a>
            </div>

            @if($branches->isEmpty())
                <div class="text-center py-5">
                    <div class="mx-auto mb-3" style="width:52px;height:52px;border-radius:14px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-building" style="color:#cbd5e1;font-size:1.25rem;"></i>
                    </div>
                    <p class="fw-semibold mb-1" style="color:#475569;font-size:.875rem;">Sin sucursales</p>
                    <a href="{{ route('admin.branches.create') }}" class="btn btn-success btn-sm px-3 mt-1">
                        Crear sucursal
                    </a>
                </div>
            @else
                <div>
                @foreach($branches as $branch)
                    <div class="branch-item">
                        <div class="d-flex align-items-center">
                            <div class="branch-dot"></div>
                            <div>
                                <div class="fw-semibold" style="font-size:.875rem;color:#0f172a;">{{ $branch->name }}</div>
                                <div style="font-size:.72rem;color:#94a3b8;margin-top:1px;">
                                    <i class="fas fa-location-dot me-1" style="font-size:.6rem;"></i>{{ $branch->city }}
                                </div>
                            </div>
                        </div>
                        <div class="branch-count" title="{{ $branch->employees_count }} empleados">
                            {{ $branch->employees_count }}
                            <i class="fas fa-user" style="font-size:.55rem;color:#94a3b8;"></i>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
