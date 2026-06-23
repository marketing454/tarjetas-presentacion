# Directorio público de sedes — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Página pública en `/` que lista ciudades como icon-boxes; al elegir una ciudad se muestran las sedes de esa ciudad (icon-boxes); al elegir una sede navega a `/sede/{slug}`.

**Architecture:** Un `DirectoryController@index` agrupa las `Branch` por `city` y pasa la data a una vista Blade. La navegación ciudad→sedes es client-side (show/hide con JS, sin AJAX). Toda la data de sedes va en el HTML inicial con `data-city`. Se reutilizan los estilos de `card/_card-styles.blade.php`.

**Tech Stack:** Laravel 11, Blade, Font Awesome 6, JS vanilla. Sin tests (acordado).

## Global Constraints

- Ubicación: ruta `/` (raíz), reemplaza el redirect actual a login/dashboard.
- `/` queda **siempre público**; el admin entra al panel por `/login` o `/admin`.
- Sin AJAX: toda la data de sedes se renderiza en el HTML inicial.
- Ciudad con 1 sede: igual muestra el paso intermedio de sedes.
- Fallback de foto de sede: icono `fa-building`.
- Sin tests automatizados; verificación manual en navegador.
- Archivos de la app viven bajo `src/` (la copia `custom/` no se toca).

---

### Task 1: DirectoryController y ruta `/`

**Files:**
- Create: `src/app/Http/Controllers/DirectoryController.php`
- Modify: `src/routes/web.php` (bloque de la ruta `/`, líneas ~25-28)

**Interfaces:**
- Consumes: `App\Models\Branch` (campos `name`, `city`, `slug`, accessor `photo_url`), `App\Models\Employee::themeFor()` y `Employee::CARD_TYPE_NORMAL`.
- Produces: vista `directory.index` con variables `$cities` (Collection agrupada) y `$theme` (array). `$cities` es el resultado de `groupBy('city')` sobre una Collection de `Branch`, es decir `Collection<string, Collection<Branch>>`.

- [ ] **Step 1: Crear el controlador**

Crear `src/app/Http/Controllers/DirectoryController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;

class DirectoryController extends Controller
{
    public function index()
    {
        $cities = Branch::orderBy('city')
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'slug', 'photo', 'photo_position_x', 'photo_position_y'])
            ->groupBy('city');

        $theme = Employee::themeFor(Employee::CARD_TYPE_NORMAL);

        return view('directory.index', compact('cities', 'theme'));
    }
}
```

- [ ] **Step 2: Reemplazar la ruta `/` en `src/routes/web.php`**

Reemplazar este bloque actual:

```php
// Raíz → redirige
Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'admin.dashboard' : 'login');
});
```

por:

```php
// Raíz → directorio público de sedes
Route::get('/', [DirectoryController::class, 'index'])->name('directory');
```

Y añadir el import al inicio del archivo, junto a los otros `use` de controladores:

```php
use App\Http\Controllers\DirectoryController;
```

> Nota: `use Illuminate\Support\Facades\Auth;` puede quedar; sigue usándose en la ruta `/login`.

- [ ] **Step 3: Verificar que la ruta resuelve**

Run (Docker o entorno local del proyecto):
```bash
php artisan route:list --path=/ 2>/dev/null | grep -i directory
```
Expected: aparece la ruta `GET /` → `DirectoryController@index` con name `directory`.

Si aún no existe la vista, cargar `/` dará error de vista — se resuelve en Task 2. No commitear todavía.

---

### Task 2: Vista del directorio (ciudades + sedes + JS)

**Files:**
- Create: `src/resources/views/directory/index.blade.php`

**Interfaces:**
- Consumes: `$cities` (Collection agrupada por ciudad) y `$theme` (array) desde Task 1; partial `card._card-styles` (espera `$theme` y `$backgroundUrl`); accessor `$branch->photo_url`; ruta `branch.show` (`/sede/{slug}`).
- Produces: HTML final navegable. No expone interfaces a otras tareas.

- [ ] **Step 1: Crear la vista**

Crear `src/resources/views/directory/index.blade.php`:

```blade
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
```

- [ ] **Step 2: Verificación manual — paso ciudades**

Cargar `/` en el navegador (el subdominio o el entorno local del proyecto).
Expected:
- Se ve el logo, "Nuestras Sedes", "Elige tu ciudad".
- Aparece un box por cada ciudad distinta con su conteo de sedes ("N sedes").
- Si no hay sedes en la BD: mensaje "Aún no hay sedes registradas."

- [ ] **Step 3: Verificación manual — paso sedes y navegación**

Hacer click en una ciudad.
Expected:
- Se oculta el grid de ciudades y aparece "Volver a ciudades" + el nombre de la ciudad + las sedes de **esa** ciudad únicamente.
- Cada sede muestra su foto o el icono `fa-building` si no tiene foto.
- Click en una sede → navega a `/sede/{slug}` (la tarjeta de sede existente).
- "Volver a ciudades" regresa al grid de ciudades.

- [ ] **Step 4: Commit**

```bash
git add src/app/Http/Controllers/DirectoryController.php src/routes/web.php src/resources/views/directory/index.blade.php
git commit -m "Add public branch directory at / (cities -> branches -> sede)"
```

---

## Self-Review

- **Spec coverage:**
  - Ruta `/` pública reemplazando redirect → Task 1. ✔
  - Agrupar Branch por ciudad + theme normal → Task 1. ✔
  - Vista single-page, data en HTML, sin AJAX → Task 2. ✔
  - Boxes ciudad (icono + nombre + conteo) → Task 2 paso 1. ✔
  - Boxes sede (foto/`photo_url` o `fa-building`) → Task 2 paso 1. ✔
  - Navegación a `/sede/{slug}` vía `route('branch.show')` → Task 2. ✔
  - Ciudad con 1 sede igual muestra paso intermedio (singular "sede") → Task 2. ✔
  - Sin sedes → mensaje vacío → Task 2. ✔
  - Reutiliza `_card-styles` → Task 2. ✔
  - SEO/meta → Task 2 `<head>`. ✔
  - Sin tests → plan usa verificación manual. ✔
- **Placeholders:** ninguno; todo el código está completo.
- **Type consistency:** `$cities` (groupBy), `$theme` (array), `route('branch.show', $branch->slug)`, `photo_url`, `data-city` ↔ `dataset.city`, `data-city-trigger` ↔ `dataset.cityTrigger` — consistentes entre tareas.
