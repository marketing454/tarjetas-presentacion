@extends('layouts.admin')

@section('title', 'Editar Empleado')
@section('page-title', 'Editar Empleado')

@section('content')

<div class="row g-4">
    <div class="col-lg-7">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left fa-sm"></i>
            </a>
            <h5 class="mb-0 fw-bold">Editar: {{ $employee->name }}</h5>
        </div>

        <div class="card content-card">
            <div class="card-body p-4">
                <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    {{-- Preview foto --}}
                    <div class="text-center mb-4">
                        <div id="photoPreview" class="avatar avatar-lg mx-auto mb-2"
                             style="width:80px;height:80px;font-size:1.5rem;cursor:pointer;
                                    {{ $employee->photo_url ? "background-image:url('{$employee->photo_url}');background-size:cover;background-position:center;" : 'background:#e0e7ff;color:#4f46e5;' }}"
                             onclick="document.getElementById('photo').click()">
                            @if(!$employee->photo_url)
                                {{ $employee->initials }}
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.8rem;">
                            <a href="#" onclick="document.getElementById('photo').click();return false;">
                                Cambiar foto
                            </a>
                            @if($employee->photo)
                                · <a href="#" onclick="removePhoto();return false;" class="text-danger">Quitar foto</a>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            Recomendado: 600 x 600 px, rostro centrado y fondo limpio.
                        </div>
                        <input type="file" id="photo" name="photo" accept="image/*" class="d-none"
                               onchange="previewPhoto(this)">
                        <input type="hidden" name="remove_photo" id="remove_photo" value="0">
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $employee->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Cargo <span class="text-danger">*</span></label>
                            <input type="text" name="position" class="form-control @error('position') is-invalid @enderror"
                                   value="{{ old('position', $employee->position) }}" required>
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Sucursal <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->city }} — {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @include('admin.employees._card_type_field', [
                        'selectedType' => old('card_type', $employee->card_type ?? \App\Models\Employee::CARD_TYPE_NORMAL)
                    ])

                    <div class="mt-4">
                        <label class="form-label fw-semibold">Fondo personalizado de tarjeta</label>
                        <div id="backgroundPreview" class="rounded-4 border d-flex align-items-center justify-content-center text-muted mb-2"
                             style="height:140px;background:{{ $employee->custom_card_background_url ? "linear-gradient(rgba(15,23,42,.18), rgba(15,23,42,.18)), url('{$employee->custom_card_background_url}')" : 'linear-gradient(135deg,#0f172a,#2563eb)' }};background-size:cover;background-position:center;cursor:pointer;"
                             onclick="document.getElementById('card_background').click()">
                            @if(!$employee->custom_card_background_url)
                                <div class="text-center small text-white">
                                    <i class="fas fa-image d-block mb-1"></i>
                                    Cargar imagen de fondo
                                </div>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div>
                                <a href="#" onclick="document.getElementById('card_background').click();return false;" style="font-size:.82rem;">
                                    Cambiar fondo
                                </a>
                                @if($employee->card_background)
                                    · <a href="#" onclick="removeBackground();return false;" class="text-danger" style="font-size:.82rem;">Quitar fondo</a>
                                @endif
                            </div>
                            <span class="text-muted" style="font-size:.72rem;">Recomendado: 900 x 420 px, JPG/PNG/WebP.</span>
                        </div>
                        <div class="form-text">
                            Opcional. Si no hay fondo personalizado, la tarjeta usará el banner predeterminado del tipo de asesor.
                        </div>
                        <input type="file" id="card_background" name="card_background" accept="image/*" class="d-none"
                               onchange="previewBackground(this)">
                        <input type="hidden" name="remove_card_background" id="remove_card_background" value="0">
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
                                   value="{{ old('whatsapp', $employee->whatsapp) }}" placeholder="+57 300 000 0000">
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
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Actualizar
                        </button>
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Panel lateral: QR + acceso tarjeta --}}
    <div class="col-lg-5">
        <h5 class="fw-bold mb-4">Tarjeta & QR</h5>
        <div class="card content-card">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <img src="{{ route('admin.employees.qr-preview', $employee) }}"
                         alt="QR Code" style="width:180px;height:180px;" class="rounded">
                </div>
                <p class="text-muted small mb-1">URL de la tarjeta:</p>
                <code class="d-block bg-light rounded px-3 py-2 mb-3" style="font-size:.78rem;word-break:break-all;">
                    {{ route('card.show', $employee->slug) }}
                </code>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('admin.employees.qr', $employee) }}"
                       class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-download me-1"></i> Descargar QR
                    </a>
                    <a href="{{ route('card.show', $employee->slug) }}" target="_blank"
                       class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fas fa-arrow-up-right-from-square me-1"></i> Ver tarjeta
                    </a>
                </div>
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
        document.getElementById('remove_photo').value = '0';
    };
    reader.readAsDataURL(input.files[0]);
}

function removePhoto() {
    const preview = document.getElementById('photoPreview');
    preview.style.backgroundImage = '';
    preview.style.background = '#e0e7ff';
    preview.style.color = '#4f46e5';
    preview.innerHTML = '{{ $employee->initials }}';
    document.getElementById('photo').value = '';
    document.getElementById('remove_photo').value = '1';
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
        document.getElementById('remove_card_background').value = '0';
    };
    reader.readAsDataURL(input.files[0]);
}

function removeBackground() {
    const preview = document.getElementById('backgroundPreview');
    preview.style.backgroundImage = '';
    preview.style.background = 'linear-gradient(135deg,#0f172a,#2563eb)';
    preview.innerHTML = '<div class="text-center small text-white"><i class="fas fa-image d-block mb-1"></i>Cargar imagen de fondo</div>';
    document.getElementById('card_background').value = '';
    document.getElementById('remove_card_background').value = '1';
}
</script>
@endpush
@endsection
