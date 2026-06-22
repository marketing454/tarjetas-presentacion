@extends('layouts.admin')

@section('title', 'Empleados')
@section('page-title', 'Empleados')

@section('topbar-actions')
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm px-3">
        <i class="fas fa-plus me-1"></i> Nuevo empleado
    </a>
@endsection

@section('content')

@if($noBranchCount > 0)
    <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
        <div>
            <i class="fas fa-triangle-exclamation me-2"></i>
            Hay <strong>{{ $noBranchCount }}</strong>
            {{ $noBranchCount === 1 ? 'empleado sin sucursal asignada' : 'empleados sin sucursal asignada' }}.
        </div>
        <a href="{{ route('admin.employees.index', ['branch' => 'none']) }}" class="btn btn-sm btn-warning">
            Ver empleados sin sucursal
        </a>
    </div>
@endif

{{-- Filtros --}}
<div class="card content-card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Buscar por nombre o cargo..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-sm-4">
                <select name="branch" class="form-select form-select-sm">
                    <option value="">Todas las sucursales</option>
                    <option value="none" {{ request('branch') === 'none' ? 'selected' : '' }}>⚠ Sin sucursal</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->city }} — {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-3">Filtrar</button>
                @if(request()->hasAny(['search', 'branch']))
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card content-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-users me-2 text-primary"></i>Empleados</span>
        <span class="badge bg-light text-dark">{{ $employees->count() }} resultados</span>
    </div>
    <div class="card-body p-0">
        @if($employees->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">No se encontraron empleados.</p>
                @if(!request()->hasAny(['search', 'branch']))
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm mt-3">
                        <i class="fas fa-plus me-1"></i> Crear primer empleado
                    </a>
                @endif
            </div>
        @else
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Empleado</th>
                        <th>Cargo</th>
                        <th>Sucursal</th>
                        <th>Contacto</th>
                        <th>Tarjeta</th>
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($employees as $emp)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($emp->photo_url)
                                    <img src="{{ $emp->photo_url }}" class="avatar" alt="{{ $emp->name }}">
                                @else
                                    <div class="avatar d-flex" style="background:#e0e7ff;color:#4f46e5;">
                                        {{ $emp->initials }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size:.875rem;">{{ $emp->name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">/card/{{ $emp->slug }}</div>
                                    <span class="badge rounded-pill mt-1"
                                          style="background:{{ $emp->card_theme['branch_bg'] }};color:{{ $emp->card_theme['branch_text'] }};font-size:.66rem;">
                                        {{ $emp->card_type_label }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.85rem;">{{ $emp->position }}</td>
                        <td>
                            @if($emp->branch)
                                <div style="font-size:.82rem;">{{ $emp->branch->name }}</div>
                                <span class="badge badge-branch rounded-pill mt-1">
                                    <i class="fas fa-location-dot me-1"></i>{{ $emp->branch->city }}
                                </span>
                            @else
                                <span class="badge bg-danger rounded-pill">
                                    <i class="fas fa-triangle-exclamation me-1"></i>Sin sucursal
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2 align-items-center">
                                @if($emp->whatsapp)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $emp->whatsapp) }}" target="_blank"
                                       class="text-success" title="WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
                                @if($emp->instagram)
                                    <a href="https://instagram.com/{{ ltrim($emp->instagram, '@') }}" target="_blank"
                                       class="text-danger" title="Instagram">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                @endif
                                @if($emp->facebook)
                                    <a href="{{ str_starts_with($emp->facebook, 'http') ? $emp->facebook : 'https://facebook.com/' . $emp->facebook }}"
                                       target="_blank" class="text-primary" title="Facebook">
                                        <i class="fab fa-facebook"></i>
                                    </a>
                                @endif
                                @if(!$emp->whatsapp && !$emp->instagram && !$emp->facebook)
                                    <span class="text-muted small">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('card.show', $emp->slug) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary" title="Ver tarjeta pública">
                                    <i class="fas fa-arrow-up-right-from-square fa-sm"></i>
                                </a>
                                <button onclick="showQr({{ $emp->id }}, '{{ addslashes($emp->name) }}', '{{ route('admin.employees.qr', $emp) }}', '{{ route('card.show', $emp->slug) }}')"
                                        class="btn btn-sm btn-outline-primary" title="Ver / Descargar QR">
                                    <i class="fas fa-qrcode fa-sm"></i>
                                </button>
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.employees.edit', $emp) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="fas fa-pen fa-sm"></i>
                                </a>
                                <form action="{{ route('admin.employees.destroy', $emp) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar a {{ addslashes($emp->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fas fa-trash fa-sm"></i>
                                    </button>
                                </form>
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

@endsection
