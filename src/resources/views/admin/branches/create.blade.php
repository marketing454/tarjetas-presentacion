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
                <form action="{{ route('admin.branches.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="text-center mb-4">
                        <div id="sedePhotoPreview" class="avatar avatar-lg mx-auto mb-2"
                             style="width:120px;height:120px;font-size:1.5rem;background:#dcfce7;color:#16a34a;cursor:grab;"
                             onclick="document.getElementById('sedePhoto').click()">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="text-muted" style="font-size:.8rem;">
                            <a href="#" onclick="document.getElementById('sedePhoto').click();return false;">
                                Subir foto de la sede
                            </a>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            Despues de subirla, arrastra la foto dentro del circulo para elegir el encuadre.
                        </div>
                        <input type="file" id="sedePhoto" name="photo" accept="image/*" class="d-none"
                               onchange="previewSedePhoto(this)">
                        <input type="hidden" name="photo_position_x" id="photo_position_x" value="50">
                        <input type="hidden" name="photo_position_y" id="photo_position_y" value="50">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Fotos de la sede (URLs externas)</label>
                        <div id="photoUrlRows">
                            <div class="d-flex gap-2 mb-2">
                                <input type="url" name="photos[]" class="form-control" placeholder="https://...">
                                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPhotoUrlRow()">
                            <i class="fas fa-plus me-1"></i> Agregar foto
                        </button>
                        <div class="form-text">
                            Pega el link directo de cada foto (alojada en otro servicio). El orden de la lista es el orden del carrusel.
                        </div>
                    </div>

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

@push('scripts')
<script>
function previewSedePhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('sedePhotoPreview');
        preview.innerHTML = '';
        preview.style.backgroundImage = `url('${e.target.result}')`;
        preview.style.backgroundSize = 'cover';
        preview.style.backgroundPosition = '50% 50%';
        document.getElementById('photo_position_x').value = '50';
        document.getElementById('photo_position_y').value = '50';
    };
    reader.readAsDataURL(input.files[0]);
}

function setupSedePhotoDrag() {
    const container = document.getElementById('sedePhotoPreview');
    const xInput = document.getElementById('photo_position_x');
    const yInput = document.getElementById('photo_position_y');
    let dragging = false;
    let startX = 0, startY = 0;
    let posX = parseInt(xInput.value, 10);
    let posY = parseInt(yInput.value, 10);

    function clamp(v) { return Math.max(0, Math.min(100, v)); }

    function start(e) {
        dragging = true;
        const point = e.touches ? e.touches[0] : e;
        startX = point.clientX;
        startY = point.clientY;
    }

    function move(e) {
        if (!dragging) return;
        const point = e.touches ? e.touches[0] : e;
        const dx = point.clientX - startX;
        const dy = point.clientY - startY;
        const rect = container.getBoundingClientRect();
        posX = clamp(posX - (dx / rect.width) * 100);
        posY = clamp(posY - (dy / rect.height) * 100);
        container.style.backgroundPosition = `${posX}% ${posY}%`;
        startX = point.clientX;
        startY = point.clientY;
        e.preventDefault();
    }

    function end() {
        if (!dragging) return;
        dragging = false;
        xInput.value = Math.round(posX);
        yInput.value = Math.round(posY);
    }

    container.addEventListener('mousedown', start);
    container.addEventListener('touchstart', start, { passive: true });
    window.addEventListener('mousemove', move);
    window.addEventListener('touchmove', move, { passive: false });
    window.addEventListener('mouseup', end);
    window.addEventListener('touchend', end);
}
document.addEventListener('DOMContentLoaded', setupSedePhotoDrag);

function addPhotoUrlRow() {
    const container = document.getElementById('photoUrlRows');
    const row = document.createElement('div');
    row.className = 'd-flex gap-2 mb-2';
    row.innerHTML = '<input type="url" name="photos[]" class="form-control" placeholder="https://..."><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>';
    container.appendChild(row);
}
</script>
@endpush
@endsection
