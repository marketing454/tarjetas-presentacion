@extends('layouts.admin')

@section('title', 'Editar Sucursal')
@section('page-title', 'Editar Sucursal')

@section('content')

<div class="row g-4">
    <div class="col-lg-6">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left fa-sm"></i>
            </a>
            <h5 class="mb-0 fw-bold">Editar: {{ $branch->name }}</h5>
        </div>

        <div class="card content-card">
            <div class="card-body p-4">
                <form action="{{ route('admin.branches.update', $branch) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $branch->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ciudad <span class="text-danger">*</span></label>
                        <select name="city" class="form-select @error('city') is-invalid @enderror" required>
                            <option value="">Seleccionar ciudad...</option>
                            @foreach(['CARTAGENA','BARRANQUILLA','SANTA MARTA','RIOHACHA','VALLEDUPAR','PEREIRA','MONTERIA'] as $city)
                                <option value="{{ $city }}" {{ old('city', $branch->city) === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Dirección exacta</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                               value="{{ old('address', $branch->address) }}">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Link exacto de Google Maps</label>
                        <input type="url" name="maps_url" class="form-control @error('maps_url') is-invalid @enderror"
                               value="{{ old('maps_url', $branch->maps_url) }}"
                               placeholder="https://maps.app.goo.gl/...">
                        <div class="form-text">
                            Opcional. Si lo llenas, el botón “Ir a Sede” abrirá este enlace exacto.
                        </div>
                        @error('maps_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $branch->phone) }}">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Actualizar
                        </button>
                        <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Empleados de esta sucursal --}}
    <div class="col-lg-6">
        <h5 class="fw-bold mb-4">
            Empleados en esta sucursal
            <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;">
                {{ $branch->employees->count() }}
            </span>
        </h5>
        <div class="card content-card">
            <div class="card-body p-0">
                @if($branch->employees->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <p class="small mb-0">Sin empleados en esta sucursal.</p>
                        <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-plus me-1"></i> Agregar empleado
                        </a>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                    @foreach($branch->employees as $emp)
                        <li class="list-group-item border-0 px-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($emp->photo_url)
                                    <img src="{{ $emp->photo_url }}" class="avatar" alt="">
                                @else
                                    <div class="avatar d-flex" style="background:#e0e7ff;color:#4f46e5;">
                                        {{ $emp->initials }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size:.875rem;">{{ $emp->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">{{ $emp->position }}</div>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.employees.edit', $emp) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-pen fa-xs"></i>
                                    </a>
                                    <a href="{{ route('card.show', $emp->slug) }}" target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-arrow-up-right-from-square fa-xs"></i>
                                    </a>
                                </div>
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
