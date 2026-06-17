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
        @if($branch->photos->isNotEmpty())
        .sede-carousel { position: relative; margin-bottom: 1.5rem; border-radius: 14px; overflow: hidden; }
        .sede-carousel-track { position: relative; height: 200px; background: #f1f5f9; }
        .sede-carousel-slide { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .sede-carousel-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 32px; height: 32px; border-radius: 50%; background: rgba(15,23,42,.55); color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .sede-carousel-arrow.prev { left: .5rem; }
        .sede-carousel-arrow.next { right: .5rem; }
        .sede-carousel-dots { display: flex; justify-content: center; gap: .4rem; padding: .6rem 0; background: #f8fafc; }
        .sede-carousel-dot { width: 7px; height: 7px; border-radius: 50%; background: #cbd5e1; border: none; cursor: pointer; padding: 0; }
        .sede-carousel-dot.active { background: var(--accent); }
        @endif
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
                        @if($branch->photo_url)
                            <img src="{{ $branch->photo_url }}" alt="{{ $branch->name }}"
                                 style="object-position: {{ $branch->photo_position_x }}% {{ $branch->photo_position_y }}%;">
                        @else
                            <i class="fas fa-building"></i>
                        @endif
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

            @if($branch->photos->isNotEmpty())
                <div class="divider"></div>

                <div class="sede-carousel">
                    <div class="sede-carousel-track">
                        @foreach($branch->photos as $i => $photo)
                            <img src="{{ $photo->url }}" class="sede-carousel-slide" style="display: {{ $i === 0 ? 'block' : 'none' }};" alt="{{ $branch->name }}">
                        @endforeach
                    </div>
                    @if($branch->photos->count() > 1)
                        <button type="button" class="sede-carousel-arrow prev">&lsaquo;</button>
                        <button type="button" class="sede-carousel-arrow next">&rsaquo;</button>
                        <div class="sede-carousel-dots">
                            @foreach($branch->photos as $i => $photo)
                                <button type="button" class="sede-carousel-dot {{ $i === 0 ? 'active' : '' }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

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
@if($branch->photos->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const track = document.querySelector('.sede-carousel-track');
    if (!track) return;
    const slides = track.querySelectorAll('.sede-carousel-slide');
    const dots = document.querySelectorAll('.sede-carousel-dot');
    let current = 0;

    function show(index) {
        slides.forEach((s, i) => s.style.display = i === index ? 'block' : 'none');
        dots.forEach((d, i) => d.classList.toggle('active', i === index));
        current = index;
    }

    document.querySelector('.sede-carousel-arrow.prev')?.addEventListener('click', () => {
        show((current - 1 + slides.length) % slides.length);
    });
    document.querySelector('.sede-carousel-arrow.next')?.addEventListener('click', () => {
        show((current + 1) % slides.length);
    });
    dots.forEach((d, i) => d.addEventListener('click', () => show(i)));
});
</script>
@endif
</body>
</html>
