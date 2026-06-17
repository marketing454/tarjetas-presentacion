@extends('layouts.admin')

@section('title', 'Sucursales')
@section('page-title', 'Sucursales')

@section('topbar-actions')
    <a href="{{ route('admin.branches.create') }}" class="btn btn-primary btn-sm px-3">
        <i class="fas fa-plus me-1"></i> Nueva sucursal
    </a>
@endsection

@section('content')

<div class="card content-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-building me-2 text-primary"></i>Todas las sucursales</span>
        <span class="badge bg-light text-dark">{{ $branches->count() }} registros</span>
    </div>
    <div class="card-body p-0">
        @if($branches->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-building fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">No hay sucursales registradas.</p>
                <a href="{{ route('admin.branches.create') }}" class="btn btn-primary btn-sm mt-3">
                    <i class="fas fa-plus me-1"></i> Crear primera sucursal
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Nombre</th>
                        <th>Ciudad</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Empleados</th>
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($branches as $branch)
                    <tr>
                        <td class="ps-4 text-muted">{{ $branch->id }}</td>
                        <td class="fw-semibold">{{ $branch->name }}</td>
                        <td>
                            <span class="badge badge-branch rounded-pill">
                                <i class="fas fa-location-dot me-1"></i>{{ $branch->city }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $branch->address ?? '—' }}</td>
                        <td class="text-muted small">{{ $branch->phone ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.employees.index', ['branch' => $branch->id]) }}"
                               class="badge bg-light text-dark text-decoration-none">
                                {{ $branch->employees_count }} empleados
                            </a>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('branch.show', $branch->slug) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary" title="Ver tarjeta pública">
                                    <i class="fas fa-arrow-up-right-from-square fa-sm"></i>
                                </a>
                                <button onclick="showBranchQr({{ $branch->id }}, '{{ addslashes($branch->name) }}', '{{ route('admin.branches.qr', $branch) }}', '{{ route('branch.show', $branch->slug) }}')"
                                        class="btn btn-sm btn-outline-primary" title="Ver / Descargar QR">
                                    <i class="fas fa-qrcode fa-sm"></i>
                                </button>
                                <a href="{{ route('admin.branches.edit', $branch) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="fas fa-pen fa-sm"></i>
                                </a>
                                <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar la sucursal {{ addslashes($branch->name) }} y todos sus empleados?')">
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
