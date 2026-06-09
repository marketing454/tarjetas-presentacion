@extends('layouts.admin')

@section('title', 'Banners')
@section('page-title', 'Banners predeterminados')

@section('topbar-actions')
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm px-3">
        <i class="fas fa-plus me-1"></i> Nuevo empleado
    </a>
@endsection

@push('styles')
<style>
    .banner-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }
    .banner-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }
    .banner-preview {
        min-height: 150px;
        background:
            linear-gradient(135deg, rgba(15, 23, 42, .32), rgba(15, 23, 42, .08)),
            linear-gradient(135deg, #14532d, #16a34a);
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, .88);
        cursor: pointer;
    }
    .banner-card[data-card-type="credit"] .banner-preview {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, .32), rgba(15, 23, 42, .08)),
            linear-gradient(135deg, #075985, #f59e0b);
    }
    .banner-card[data-card-type="corporate"] .banner-preview {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, .32), rgba(15, 23, 42, .08)),
            linear-gradient(135deg, #0f172a, #334155);
    }
    .banner-preview.has-image {
        min-height: 170px;
    }
    .banner-meta {
        padding: 1rem;
    }
    .banner-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        flex-shrink: 0;
    }
    @media (max-width: 992px) {
        .banner-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-1">Banners por tipo de asesor</h5>
        <p class="text-muted small mb-0">
            Estos banners se aplican automáticamente a las tarjetas que no tengan un fondo personalizado.
        </p>
    </div>
</div>

<form action="{{ route('admin.card-banners.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="banner-grid">
        @foreach(\App\Models\Employee::cardTypes() as $value => $type)
            @php
                $banner = $banners->get($value);
                $previewUrl = $banner?->url;
                $inputId = 'banner_' . $value;
            @endphp

            <div class="banner-card" data-card-type="{{ $value }}">
                <div id="preview_{{ $value }}"
                     class="banner-preview {{ $previewUrl ? 'has-image' : '' }}"
                     style="{{ $previewUrl ? "background-image: linear-gradient(rgba(15,23,42,.18), rgba(15,23,42,.18)), url('{$previewUrl}')" : '' }}"
                     onclick="document.getElementById('{{ $inputId }}').click()">
                    @if(!$previewUrl)
                        <div class="text-center">
                            <i class="fas fa-image d-block mb-2 fs-4"></i>
                            <span class="small fw-semibold">Subir banner</span>
                        </div>
                    @endif
                </div>

                <div class="banner-meta">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="banner-icon"><i class="fas {{ $type['icon'] }}"></i></span>
                        <div>
                            <div class="fw-bold text-dark">{{ $type['label'] }}</div>
                            <div class="text-muted" style="font-size:.76rem;">{{ $type['description'] }}</div>
                        </div>
                    </div>

                    <input type="file" id="{{ $inputId }}" name="banners[{{ $value }}]"
                           accept="image/*" class="d-none" onchange="previewBanner(this, '{{ $value }}')">

                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <a href="#" onclick="document.getElementById('{{ $inputId }}').click();return false;" style="font-size:.84rem;">
                            {{ $previewUrl ? 'Cambiar banner' : 'Subir banner' }}
                        </a>
                        @if($previewUrl)
                            <label class="text-danger d-flex align-items-center gap-1 mb-0" style="font-size:.78rem;">
                                <input type="checkbox" name="remove[{{ $value }}]" value="1">
                                Quitar
                            </label>
                        @endif
                    </div>

                    <div class="text-muted mt-2" style="font-size:.72rem;">
                        Recomendado: 900 x 420 px, JPG/PNG/WebP.
                    </div>
                    @error("banners.$value")<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save me-1"></i> Guardar banners
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4">
            Cancelar
        </a>
    </div>
</form>

@push('scripts')
<script>
function previewBanner(input, cardType) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById(`preview_${cardType}`);
        preview.innerHTML = '';
        preview.classList.add('has-image');
        preview.style.backgroundImage = `linear-gradient(rgba(15,23,42,.18), rgba(15,23,42,.18)), url('${e.target.result}')`;
        preview.style.backgroundSize = 'cover';
        preview.style.backgroundPosition = 'center';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endpush

@endsection
