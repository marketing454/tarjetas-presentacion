# Directorio público de sedes — Diseño

**Fecha:** 2026-06-23
**Proyecto:** Team-Presentation (card.compulago.com)
**Estado:** Aprobado para planificación

## Objetivo

Página pública de entrada en `/` que actúa como directorio: el visitante elige
una **ciudad** (icon-boxes), luego ve las **sedes** de esa ciudad (icon-boxes), y
al elegir una sede navega a `/sede/{slug}` (ruta ya existente).

## Decisiones tomadas

- **Ubicación:** `/` (raíz), reemplazando el redirect actual a login/dashboard.
- **Flujo:** single-page. Ciudades → sedes ocurre en cliente (JS show/hide), sin
  recargar ni AJAX. La selección de sede sí navega de verdad a `/sede/{slug}`.
- **`/` siempre público.** El admin entra al panel por `/login` o `/admin`.
- **Contenido de los boxes:** icono + nombre + conteo.
  - Ciudad: icono `fa-location-dot` + nombre + `"N sedes"`.
  - Sede: foto de la sede (`photo_url`) o `fa-building` de fallback + nombre.
- **Ciudad con una sola sede:** se muestra igual el paso intermedio de sedes
  (consistencia), no hay salto directo.
- **Sin tests** para esta feature.

## Arquitectura

### Ruta — `src/routes/web.php`

Reemplazar el bloque actual de `/`:

```php
Route::get('/', [DirectoryController::class, 'index'])->name('directory');
```

(Se elimina el redirect condicional a login/dashboard que existe hoy.)

### Controlador — `App\Http\Controllers\DirectoryController@index`

- Trae todas las sedes: `Branch::orderBy('city')->orderBy('name')->get()`.
- Las agrupa por `city` (`->groupBy('city')`).
- Construye una colección de ciudades con: nombre de ciudad, número de sedes, y
  la lista de sedes (`name`, `slug`, `photo_url`).
- Pasa el `theme` normal: `Employee::themeFor(Employee::CARD_TYPE_NORMAL)` para
  reutilizar los tokens de color del estilo de tarjetas existente.
- Sin lógica de presentación en el controlador.

### Vista — `resources/views/directory/index.blade.php`

- Reutiliza `@include('card._card-styles', ['theme' => $theme, 'backgroundUrl' => null])`
  y la estructura `.page-wrapper` / `.card` ya existente.
- Estilos propios nuevos: `.directory-grid`, `.directory-box`, `.directory-step`,
  basados en `--accent`, `--card-surface`, etc.
- **Toda** la data de sedes se renderiza en el HTML inicial, con atributos
  `data-city` para poder filtrar en cliente.

#### Estructura de la pantalla (dos pasos dentro del mismo `.card`)

1. **Paso ciudades** (visible al cargar):
   - Título: "Sedes COMPULAGO" / subtítulo "Elige tu ciudad".
   - Grid de icon-boxes de ciudad: `fa-location-dot` + nombre + `"N sedes"`.
   - Click en un box → oculta paso ciudades, muestra paso sedes filtrado por
     esa ciudad.

2. **Paso sedes** (oculto al cargar):
   - Botón "← Volver a ciudades".
   - Título con la ciudad activa.
   - Grid de icon-boxes de sede: cada box es `<a href="/sede/{slug}">` con la
     foto de la sede (`photo_url`) o `fa-building` de fallback + nombre.

### JavaScript (mínimo, inline)

- Delegación de click en los boxes de ciudad: setea la ciudad activa, muestra
  solo los boxes de sede con `data-city` correspondiente, cambia de paso.
- Botón "Volver": regresa al paso de ciudades.
- Sin peticiones de red.

## Casos borde

- **Sin sedes registradas:** mensaje "Aún no hay sedes registradas".
- **Ciudad con 1 sede:** muestra igual el paso de sedes (decisión tomada).
- **Sede sin foto:** fallback a icono `fa-building`.
- **SEO/meta:** `<title>Sedes COMPULAGO</title>` + meta description genérica
  (directorio de sedes y asesores COMPULAGO).

## Componentes (límites)

- `DirectoryController` — agrupa Branches por ciudad; sin lógica de vista.
- `directory/index.blade.php` — estructura + estilos del directorio; reutiliza
  `_card-styles`.
- Bloque `<script>` — solo show/hide entre pasos.

## Fuera de alcance (YAGNI)

- Modelo `City` (las ciudades siguen siendo el campo string `city` de Branch).
- Búsqueda/filtro por texto.
- Imágenes propias por ciudad.
- Tests automatizados.
