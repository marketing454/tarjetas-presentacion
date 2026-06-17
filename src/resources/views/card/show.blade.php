@php
    $theme = $employee->card_theme;
    $backgroundUrl = $employee->card_background_url;
    $logoFile = $employee->card_type === \App\Models\Employee::CARD_TYPE_CORPORATE
        ? 'Logo-compulago-corporativo.png'
        : 'Logo-Compulago.png';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <title>{{ $employee->name }} — {{ $employee->position }}</title>

    {{-- SEO / Open Graph --}}
    <meta name="description" content="{{ $employee->name }}, {{ $employee->position }} en {{ $employee->branch->name ?? 'COMPULAGO' }}">
    <meta property="og:title" content="{{ $employee->name }}">
    <meta property="og:description" content="{{ $employee->position }} · {{ $employee->branch->city ?? '' }}">
    @if($employee->photo_url)
    <meta property="og:image" content="{{ $employee->photo_url }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('card._card-styles', ['theme' => $theme, 'backgroundUrl' => $backgroundUrl])
</head>
<body>
<div class="page-wrapper">
    <div class="card">

        {{-- Header --}}
        <div class="card-header">
            <div class="avatar-wrap">
                <div class="avatar-ring">
                    <div class="avatar-inner">
                        @if($employee->photo_url)
                            <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}">
                        @else
                            {{ $employee->initials }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">
            <h1 class="emp-name">{{ $employee->name }}</h1>
            <span class="emp-card-type">
                <i class="fas fa-id-card"></i>
                {{ $employee->card_type_label }}
            </span>

            @if($employee->branch)
                <span class="emp-branch">
                    <i class="fas fa-location-dot"></i>
                    {{ $employee->branch->city }}
                    @if($employee->branch->name !== $employee->branch->city)
                        · {{ $employee->branch->name }}
                    @endif
                </span>
            @endif

            <div class="divider"></div>

            <div class="contact-grid">
                @if($employee->branch)
                    <a href="{{ $employee->branch->maps_link }}"
                       class="contact-btn btn-maps" target="_blank">
                        <span class="btn-icon"><i class="fas fa-location-dot"></i></span>
                        <span class="btn-label">Ir a Sede</span>
                        <span class="btn-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                @endif

                @if($employee->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $employee->whatsapp) }}?text=Hola%20{{ urlencode($employee->name) }},%20te%20contacto%20desde%20tu%20tarjeta%20virtual."
                       class="contact-btn btn-whatsapp" target="_blank">
                        <span class="btn-icon"><i class="fab fa-whatsapp"></i></span>
                        <span class="btn-label">Contactar por WhatsApp</span>
                        <span class="btn-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                @endif

                <a href="https://www.instagram.com/compulagoweb/" class="contact-btn btn-instagram" target="_blank">
                    <span class="btn-icon"><i class="fab fa-instagram"></i></span>
                    <span class="btn-label">@compulagoweb</span>
                    <span class="btn-arrow"><i class="fas fa-chevron-right"></i></span>
                </a>

                <a href="https://www.facebook.com/compulagoweb" class="contact-btn btn-facebook" target="_blank">
                    <span class="btn-icon"><i class="fab fa-facebook-f"></i></span>
                    <span class="btn-label">@compulagoweb</span>
                    <span class="btn-arrow"><i class="fas fa-chevron-right"></i></span>
                </a>

                <a href="https://compulago.com" class="contact-btn btn-website" target="_blank">
                    <span class="btn-icon"><i class="fas fa-store"></i></span>
                    <span class="btn-label">Ver Tienda Online</span>
                    <span class="btn-arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
            </div>

            <div class="card-brand-logo">
                <img src="{{ asset($logoFile) }}" alt="COMPULAGO">
            </div>
        </div>

        {{-- Footer --}}
        <div class="card-footer">
            <strong>COMPULAGO</strong> · Tarjeta Virtual
            @if($employee->branch)
                · {{ $employee->branch->city }}
            @endif
        </div>

    </div>
</div>
</body>
</html>
