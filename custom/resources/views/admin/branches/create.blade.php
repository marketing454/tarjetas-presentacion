@extends('layouts.admin')

@section('title', 'Nueva Sucursal')
@section('page-title', 'Nueva Sucursal')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left fa-sm"></i>
            </a>
            <h5 class="mb-0 fw-bold">Nueva sucursal</h5>
        </div>

        <div class="card content-card">
            <div class="card-body p-4">
                <form action="{{ route('admin.branches.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre de la sucursal <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Ej: Sucursal Centro" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ciudad <span class="text-danger">*</span></label>
                        <select name="city" class="form-select @error('city') is-invalid @enderror" required>
                            <option value="">Seleccionar ciudad...</option>
                            @foreach(['CARTAGENA','BARRANQUILLA','SANTA MARTA','RIOHACHA','VALLEDUPAR','PEREIRA','MONTERIA'] as $city)
                                <option value="{{ $city }}" {{ old('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Dirección exacta</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                               value="{{ old('address') }}" placeholder="Ej: Centro Comercial Los Ejecutivos, Local 41">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Link exacto de Google Maps</label>
                        <input type="url" name="maps_url" class="form-control @error('maps_url') is-invalid @enderror"
                               value="{{ old('maps_url') }}" placeholder="https://maps.app.goo.gl/...">
                        <div class="form-text">
                            Opcional. Si lo llenas, el botón “Ir a Sede” abrirá este enlace exacto.
                        </div>
                        @error('maps_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" placeholder="Ej: +57 601 234 5678">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Guardar sucursal
                        </button>
                        <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
