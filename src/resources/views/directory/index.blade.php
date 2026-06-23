<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <title>Sedes COMPULAGO</title>
    <meta name="description" content="Directorio de sedes y asesores COMPULAGO. Encuentra tu ciudad y sede.">
    <meta property="og:title" content="Sedes COMPULAGO">
    <meta property="og:description" content="Encuentra tu ciudad y sede COMPULAGO">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @include('card._card-styles', ['theme' => $theme, 'backgroundUrl' => null])
    <style>
        .dir-title { font-size: 1.4rem; font-weight: 800; color: #0f172a; text-align: center; margin-bottom: .25rem; }
        .dir-subtitle { font-size: .85rem; color: #64748b; text-align: center; margin-bottom: 1.5rem; }
        .dir-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
        .dir-box {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: .5rem; padding: 1.1rem .75rem; border-radius: 16px; background: #f8fafc;
            border: 1px solid #eef2f7; text-decoration: none; color: inherit; cursor: pointer;
            transition: all .2s ease; text-align: center; min-height: 110px;
        }
        .dir-box:hover { background: #f1f5f9; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.08); }
        .dir-box-icon {
            width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
            background: color-mix(in srgb, var(--accent) 16%, #ffffff); color: var(--accent-dark); overflow: hidden;
        }
        .dir-box-icon img { width: 100%; height: 100%; object-fit: cover; }
        .dir-box-name { font-weight: 700; font-size: .85rem; color: #0f172a; line-height: 1.2; }
        .dir-box-meta { font-size: .72rem; color: #64748b; }
        .dir-back {
            display: inline-flex; align-items: center; gap: .4rem; background: none; border: none;
            color: var(--accent-dark); font-weight: 700; font-size: .82rem; cursor: pointer;
            padding: 0; margin-bottom: 1rem;
        }
        .dir-empty { text-align: center; padding: 2rem 1rem; color: #94a3b8; font-size: .85rem; }
        .dir-step[hidden] { display: none; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="card">
        <div class="card-body" style="padding: 2rem 1.75rem;">

            <div class="card-brand-logo" style="padding-top: 0; padding-bottom: 1.25rem;">
                <img src="{{ asset('Logo-Compulago.png') }}" alt="COMPULAGO">
            </div>

            {{-- Paso 1: ciudades --}}
            <div class="dir-step" id="step-cities">
                <h1 class="dir-title">Nuestras Sedes</h1>
                <p class="dir-subtitle">Elige tu ciudad</p>

                @if($cities->isEmpty())
                    <div class="dir-empty">Aún no hay sedes registradas.</div>
                @else
                    <div class="dir-grid">
                        @foreach($cities as $city => $branches)
                            <div class="dir-box" role="button" tabindex="0" data-city-trigger="{{ $city }}">
                                <span class="dir-box-icon"><i class="fas fa-location-dot"></i></span>
                                <span class="dir-box-name">{{ $city }}</span>
                                <span class="dir-box-meta">{{ $branches->count() }} {{ $branches->count() === 1 ? 'sede' : 'sedes' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Paso 2: sedes --}}
            <div class="dir-step" id="step-branches" hidden>
                <button type="button" class="dir-back" id="dir-back">
                    <i class="fas fa-arrow-left"></i> Volver a ciudades
                </button>
                <h1 class="dir-title" id="dir-city-name"></h1>
                <p class="dir-subtitle">Elige una sede</p>

                <div class="dir-grid">
                    @foreach($cities as $city => $branches)
                        @foreach($branches as $branch)
                            <a href="{{ route('branch.show', $branch->slug) }}"
                               class="dir-box" data-city="{{ $city }}" hidden>
                                <span class="dir-box-icon">
                                    @if($branch->photo_url)
                                        <img src="{{ $branch->photo_url }}" alt="{{ $branch->name }}"
                                             style="object-position: {{ $branch->photo_position_x }}% {{ $branch->photo_position_y }}%;">
                                    @else
                                        <i class="fas fa-building"></i>
                                    @endif
                                </span>
                                <span class="dir-box-name">{{ $branch->name }}</span>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>

        </div>

        <div class="card-footer">
            <strong>COMPULAGO</strong> · Directorio de Sedes
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const stepCities   = document.getElementById('step-cities');
    const stepBranches = document.getElementById('step-branches');
    const cityNameEl   = document.getElementById('dir-city-name');
    const branchBoxes  = document.querySelectorAll('#step-branches .dir-box');

    function openCity(city) {
        cityNameEl.textContent = city;
        branchBoxes.forEach(b => { b.hidden = (b.dataset.city !== city); });
        stepCities.hidden = true;
        stepBranches.hidden = false;
        window.scrollTo({ top: 0 });
    }

    function backToCities() {
        stepBranches.hidden = true;
        stepCities.hidden = false;
    }

    document.querySelectorAll('[data-city-trigger]').forEach(el => {
        el.addEventListener('click', () => openCity(el.dataset.cityTrigger));
        el.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openCity(el.dataset.cityTrigger); }
        });
    });

    document.getElementById('dir-back')?.addEventListener('click', backToCities);
});
</script>
</body>
</html>
