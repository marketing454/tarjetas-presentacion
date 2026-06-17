# Tarjeta publica de sede (sucursal escaneable)

**Fecha:** 2026-06-17
**Estado:** Aprobado por el usuario, pendiente de plan de implementacion

## Contexto

Cada empleado ya tiene una tarjeta publica escaneable en `/card/{slug}` con QR descargable desde el admin. El usuario pidio que las sucursales tambien tengan su propia URL y QR: al escanear el QR de una sede se debe abrir una pagina con el mismo estilo visual que la tarjeta de un asesor, pero mostrando el directorio de todos los asesores de esa sede.

## Alcance

Incluye:
- URL publica y QR por sucursal.
- Pagina publica de sede con directorio de asesores (foto, nombre, cargo, boton de WhatsApp), enlazando a la tarjeta individual de cada asesor.
- Boton de QR en el admin de sucursales, igual al de empleados.
- Los escaneos de sede se registran en las mismas metricas (`CardScan`) que los escaneos de empleados.

No incluye (fuera de alcance):
- Edicion del orden o curaduria manual del listado de asesores por sede (se ordena por nombre).
- Cambios al flujo de creacion/edicion de empleados.

## 1. Modelo de datos y rutas

### Branch: slug

- Nueva columna `slug` (string, unico, nullable a nivel de columna) en `branches`.
- `Branch::generateSlug(string $name): string` (misma logica que `Employee::generateSlug()`): slugifica el nombre y agrega sufijo numerico si ya existe.
- Migracion de backfill: para las sucursales existentes sin slug, genera el slug a partir de `name` en la propia migracion.
- `BranchController@store` genera el slug una sola vez al crear la sucursal (mismo patron que `EmployeeController@store`). `@update` nunca regenera el slug aunque el nombre cambie, para no invalidar QRs ya impresos.

### CardScan: generalizar a empleado o sede

- Nueva columna `branch_id` (FK nullable a `branches`, `cascadeOnDelete`).
- La columna `employee_id` pasa de NOT NULL a nullable. Como el proyecto no tiene `doctrine/dbal` instalado, el cambio se hace con SQL crudo dentro de la migracion (`DB::statement('ALTER TABLE card_scans MODIFY employee_id BIGINT UNSIGNED NULL')`), no con `->change()`.
- Invariante de aplicacion (no constraint de BD): cada fila tiene exactamente uno de `employee_id` / `branch_id` lleno. Lo garantizan los dos metodos publicos de escritura.
- `CardScan::record()` se reemplaza por dos metodos estaticos que comparten la logica interna de deteccion de bots y geolocalizacion:
  - `CardScan::recordForEmployee(int $employeeId, string $ip, ?string $ua, ?string $referrer): void`
  - `CardScan::recordForBranch(int $branchId, string $ip, ?string $ua, ?string $referrer): void`
- Nueva relacion `CardScan::branch(): BelongsTo`.

### Rutas publicas

```php
Route::get('/card/{slug}', [CardController::class, 'show'])->name('card.show');
Route::get('/sede/{slug}', [BranchCardController::class, 'show'])->name('branch.show');
```

`BranchCardController@show`:
- Busca la sucursal por slug (`firstOrFail`), carga `employees` ordenados por `name`.
- Registra el escaneo de forma diferida (`defer`, igual que `CardController@show`) via `CardScan::recordForBranch()`.
- Renderiza `resources/views/card/branch.blade.php`.

### Rutas admin nuevas

```php
Route::get('branches/{branch}/qr', [BranchController::class, 'downloadQr'])->name('branches.qr');
Route::get('branches/{branch}/qr-preview', [BranchController::class, 'qrPreview'])->name('branches.qr-preview');
```

## 2. Pagina publica de sede

Vista nueva `resources/views/card/branch.blade.php`, reutilizando el mismo "shell" visual de `card/show.blade.php` (el `.page-wrapper` / `.card` con gradiente, sombra y animacion `fadeUp`), con el tema de color `CARD_TYPE_NORMAL` (verde COMPULAGO) fijo, ya que una sede no tiene `card_type`.

**Header:** icono de sede (`fa-building` o similar) en vez de foto de empleado, dentro del mismo `avatar-ring`. Debajo: nombre de la sede (`emp-name` equivalente) y badge de ciudad (`emp-branch` equivalente).

**Cuerpo — directorio de asesores:** lista de filas, una por empleado de la sede (ordenados por nombre):
- Avatar pequeno (foto o iniciales, reutilizando `Employee::photo_url` / `initials`).
- Nombre + cargo (`position`).
- Boton de WhatsApp inline (mismo estilo `.btn-whatsapp` que la tarjeta individual), con `stopPropagation` para no disparar la navegacion de la fila.
- El resto de la fila es un link a `route('card.show', $employee->slug)`.
- Si la sede no tiene empleados: estado vacio ("Aun no hay asesores asignados a esta sede.").

**Pie de la tarjeta:** boton "Ir a Sede" (`branch.maps_link`), botones de Instagram/Facebook/Tienda Online (mismas URLs fijas que ya usa `card/show.blade.php`), logo COMPULAGO y footer "COMPULAGO · Tarjeta Virtual · {ciudad}", igual que en la tarjeta de empleado.

## 3. Admin: QR de sede + metricas

### Admin de sucursales

- `resources/views/admin/branches/index.blade.php`: se agrega una celda de acciones con:
  - Boton "Ver tarjeta publica" → `route('branch.show', $branch->slug)`, `target="_blank"`.
  - Boton "Ver / Descargar QR" → abre el modal `#qrModal` ya existente en `layouts/admin.blade.php`.
- `BranchController@downloadQr` / `@qrPreview`: mismo patron que `EmployeeController@downloadQr` / `@qrPreview`, generando el QR sobre `route('branch.show', $branch->slug)`.
- `layouts/admin.blade.php`: se agrega `showBranchQr(branchId, branchName, downloadUrl, cardUrl)`, funcion paralela a `showQr()`, apuntando a `/admin/branches/{id}/qr-preview`. Reutiliza el mismo modal `#qrModal`.

### Dashboard de metricas (`MetricsController` + `admin/metrics/index.blade.php`)

- Los KPIs `totalScans`, `scansToday`, `scansWeek`, `scansMonth` no cambian de codigo: al contar todas las filas de `card_scans`, ya incluyen automaticamente los escaneos de sede.
- Los desgloses `byDevice`, `byOs`, `byBrowser`, `byCity`, `byCountry` y el `timeline` no cambian: no dependen de si el escaneo es de empleado o de sede.
- Nuevo panel `topBranches`: igual a `topEmployees` pero agrupado por `branch_id`, con `->whereNotNull('branch_id')` y `with('branch:id,name,city,slug')`.
- `topEmployees` agrega `->whereNotNull('employee_id')` (antes no hacia falta porque la columna era NOT NULL).
- `recentScans`: cada fila se muestra como el empleado (si `employee_id` esta lleno) o como "Sede: {nombre}" enlazando a `route('branch.show', ...)` (si `branch_id` esta lleno). Se carga con `with(['employee:id,name,position,slug', 'branch:id,name,slug'])`.

## Resumen de archivos a tocar

- Migraciones nuevas: `add_slug_to_branches_table`, `add_branch_id_and_nullable_employee_id_to_card_scans_table`.
- `app/Models/Branch.php`: `generateSlug()`, fillable incluye `slug`.
- `app/Models/CardScan.php`: `recordForEmployee()`, `recordForBranch()`, relacion `branch()`.
- `app/Http/Controllers/CardController.php`: usa `recordForEmployee()`.
- `app/Http/Controllers/BranchCardController.php` (nuevo).
- `app/Http/Controllers/Admin/BranchController.php`: asigna slug en store/update, agrega `downloadQr()` / `qrPreview()`.
- `app/Http/Controllers/Admin/MetricsController.php`: `topBranches`, ajustes de `topEmployees` y `recentScans`.
- `resources/views/card/branch.blade.php` (nuevo).
- `resources/views/admin/branches/index.blade.php`: botones de QR / ver tarjeta.
- `resources/views/admin/metrics/index.blade.php`: panel "Top sedes", ajuste de escaneos recientes.
- `resources/views/layouts/admin.blade.php`: `showBranchQr()`.
- `routes/web.php`: rutas publicas y de admin nuevas.

## Testing

- Test de feature: `GET /sede/{slug}` devuelve 200 y contiene los nombres de los empleados de esa sede.
- Test de feature: `GET /sede/{slug-invalido}` devuelve 404.
- Test unitario: `Branch::generateSlug()` evita colisiones (mismo patron que el test existente de `Employee::generateSlug()`, si existe).
- Test unitario/feature: `CardScan::recordForBranch()` crea una fila con `branch_id` lleno y `employee_id` nulo, y no falla la migracion de `employee_id` a nullable.
- Test de feature: el panel de metricas no rompe cuando hay escaneos mezclados de empleado y de sede (KPIs y `topBranches` cuentan correctamente).
