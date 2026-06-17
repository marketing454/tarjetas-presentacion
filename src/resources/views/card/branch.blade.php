<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <title>{{ $branch->name }} — COMPULAGO</title>

    <meta name="description" content="Directorio de asesores de {{ $branch->name }}, {{ $branch->city }}">
    <meta property="og:title" content="{{ $branch->name }}">
    <meta property="og:description" content="Sede COMPULAGO · {{ $branch->city }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @include('card._card-styles', ['theme' => $theme, 'backgroundUrl' => null])
    <style>
        .advisor-list { display: flex; flex-direction: column; gap: .65rem; margin-bottom: 1.5rem; }
        .advisor-row { display: flex; align-items: center; gap: .65rem; padding: .65rem .85rem; border-radius: 14px; background: #f8fafc; transition: all .2s ease; }
        .advisor-row:hover { background: #f1f5f9; }
        .advisor-link { display: flex; align-items: center; gap: .85rem; flex: 1; min-width: 0; text-decoration: none; color: inherit; }
        .advisor-avatar { width: 46px; height: 46px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: var(--avatar-bg); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .95rem; color: var(--avatar-text); }
        .advisor-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .advisor-info { flex: 1; min-width: 0; }
        .advisor-name { font-weight: 700; font-size: .88rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .advisor-position { font-size: .74rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .advisor-whatsapp { width: 38px; height: 38px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.05rem; text-decoration: none; }
        .advisor-whatsapp:hover { background: #bbf7d0; }
        .advisor-empty { text-align: center; padding: 2rem 1rem; color: #94a3b8; font-size: .85rem; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="card">

        {{-- Header --}}
        <div class="card-header">
            <div class="avatar-wrap">
                <div class="avatar-ring">
                    <div class="avatar-inner">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">
            <h1 class="emp-name">{{ $branch->name }}</h1>
            <span class="emp-branch">
                <i class="fas fa-location-dot"></i>
                {{ $branch->city }}
            </span>

            <div class="divider"></div>

            <div class="advisor-list">
                @forelse($branch->employees as $employee)
                    <div class="advisor-row">
                        <a href="{{ route('card.show', $employee->slug) }}" class="advisor-link">
                            <div class="advisor-avatar">
                                @if($employee->photo_url)
                                    <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}">
                                @else
                                    {{ $employee->initials }}
                                @endif
                            </div>
                            <div class="advisor-info">
                                <div class="advisor-name">{{ $employee->name }}</div>
                                <div class="advisor-position">{{ $employee->position }}</div>
                            </div>
                        </a>
                        @if($employee->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $employee->whatsapp) }}?text=Hola%20{{ urlencode($employee->name) }},%20te%20contacto%20desde%20la%20tarjeta%20de%20tu%20sede."
                               class="advisor-whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="advisor-empty">Aún no hay asesores asignados a esta sede.</div>
                @endforelse
            </div>

            <div class="divider"></div>

            <div class="contact-grid">
                <a href="{{ $branch->maps_link }}" class="contact-btn btn-maps" target="_blank">
                    <span class="btn-icon"><i class="fas fa-location-dot"></i></span>
                    <span class="btn-label">Ir a Sede</span>
                    <span class="btn-arrow"><i class="fas fa-chevron-right"></i></span>
                </a>

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
                <img src="{{ asset('Logo-Compulago.png') }}" alt="COMPULAGO">
            </div>
        </div>

        {{-- Footer --}}
        <div class="card-footer">
            <strong>COMPULAGO</strong> · Tarjeta de Sede · {{ $branch->city }}
        </div>

    </div>
</div>
</body>
</html>
