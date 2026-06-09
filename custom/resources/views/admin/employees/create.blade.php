@extends('layouts.admin')

@section('title', 'Nuevo Empleado')
@section('page-title', 'Nuevo Empleado')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left fa-sm"></i>
            </a>
            <h5 class="mb-0 fw-bold">Nuevo empleado</h5>
        </div>

        <div class="card content-card">
            <div class="card-body p-4">
                <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Preview foto --}}
                    <div class="text-center mb-4">
                        <div id="photoPreview" class="avatar avatar-lg mx-auto mb-2"
                             style="width:80px;height:80px;font-size:1.5rem;background:#e0e7ff;color:#4f46e5;cursor:pointer;"
                             onclick="document.getElementById('photo').click()">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="text-muted" style="font-size:.8rem;">
                            <a href="#" onclick="document.getElementById('photo').click();return false;">
                                Subir foto de perfil
                            </a>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            Recomendado: 600 x 600 px, rostro centrado y fondo limpio.
                        </div>
                        <input type="file" id="photo" name="photo" accept="image/*" class="d-none"
                               onchange="previewPhoto(this)">
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="Ej: Juan García">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Cargo <span class="text-danger">*</span></label>
                            <input type="text" name="position" class="form-control @error('position') is-invalid @enderror"
                                   value="{{ old('position') }}" required placeholder="Ej: Asesor Comercial">
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Sucursal <span class="text-danger">*</span></label>
                        @if($branches->isEmpty())
                            <div class="alert alert-warning py-2 small">
                                No hay sucursales. <a href="{{ route('admin.branches.create') }}">Crea una primero.</a>
                            </div>
                        @else
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                <option value="">— Seleccionar sucursal —</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->city }} — {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @endif
                    </div>

                    @include('admin.employees._card_type_field', [
                        'selectedType' => old('card_type', \App\Models\Employee::CARD_TYPE_NORMAL)
                    ])

                    <div class="mt-4">
                        <label class="form-label fw-semibold">Fondo personalizado de tarjeta</label>
                        <div id="backgroundPreview" class="rounded-4 border d-flex align-items-center justify-content-center text-muted mb-2"
                             style="height:140px;background:linear-gradient(135deg,#0f172a,#2563eb);background-size:cover;background-position:center;cursor:pointer;"
                             onclick="document.getElementById('card_background').click()">
                            <div class="text-center small text-white">
                                <i class="fas fa-image d-block mb-1"></i>
                                Cargar imagen de fondo
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <a href="#" onclick="document.getElementById('card_background').click();return false;" style="font-size:.82rem;">
                                Subir fondo personalizado
                            </a>
                            <span class="text-muted" style="font-size:.72rem;">Recomendado: 900 x 420 px, JPG/PNG/WebP.</span>
                        </div>
                        <div class="form-text">
                            Opcional. Si lo dejas vacío, la tarjeta usará el banner predeterminado del tipo de asesor.
                        </div>
                        <input type="file" id="card_background" name="card_background" accept="image/*" class="d-none"
                               onchange="previewBackground(this)">
                        @error('card_background')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-4">
                    <p class="fw-semibold mb-3 text-muted small text-uppercase">Redes y contacto</p>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">
                                <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                            </label>
                            <input type="text" name="whatsapp" class="form-control"
                                   value="{{ old('whatsapp') }}" placeholder="+57 300 000 0000">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold text-muted">
                                <i class="fab fa-instagram me-1"></i> Instagram &amp;
                                <i class="fab fa-facebook ms-1 me-1"></i> Facebook
                            </label>
                            <input type="text" class="form-control bg-light" disabled
                                   value="Fijos de COMPULAGO (no editables)">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4" {{ $branches->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-save me-1"></i> Guardar empleado
                        </button>
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('photoPreview');
        preview.innerHTML = '';
        preview.style.backgroundImage = `url('${e.target.result}')`;
        preview.style.backgroundSize = 'cover';
        preview.style.backgroundPosition = 'center';
    };
    reader.readAsDataURL(input.files[0]);
}

function previewBackground(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('backgroundPreview');
        preview.innerHTML = '';
        preview.style.backgroundImage = `linear-gradient(rgba(15,23,42,.18), rgba(15,23,42,.18)), url('${e.target.result}')`;
        preview.style.backgroundSize = 'cover';
        preview.style.backgroundPosition = 'center';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endpush
@endsection
