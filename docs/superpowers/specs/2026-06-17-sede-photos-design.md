# Foto principal y carrusel de fotos de sede

**Fecha:** 2026-06-17
**Estado:** Aprobado por el usuario, pendiente de plan de implementacion

## Contexto

La tarjeta publica de sede (`/sede/{slug}`, agregada en la feature anterior) muestra hoy un icono generico de edificio en el header. El usuario quiere reemplazarlo por una foto real de la sede, con control manual de encuadre (arrastrar para reposicionar, igual que una foto de portada de Facebook), y agregar debajo del directorio de asesores un carrusel de fotos adicionales de la sede. Esas fotos del carrusel no se suben al proyecto: viven en un servicio externo y solo se guarda el link.

## Alcance

Incluye:
- Foto principal de sede subida como archivo, con posicion de encuadre ajustable por arrastre.
- Mostrar esa foto en el header de `/sede/{slug}` en vez del icono de edificio (con icono como respaldo si no hay foto).
- Lista de URLs de fotos externas por sede, administrable desde el admin (agregar/quitar, el orden de la lista define el orden del carrusel).
- Carrusel simple (sin libreria externa) en la tarjeta publica de sede, mostrando esas fotos.

No incluye (fuera de alcance):
- Subir o alojar las fotos del carrusel dentro del proyecto (siempre son URLs externas).
- Reordenar el carrusel arrastrando filas en el admin (el orden es simplemente el orden de la lista al guardar).
- Aplicar foto principal o carrusel a empleados (es exclusivo de `Branch`).

## 1. Foto principal de sede

### Datos

Nuevas columnas en `branches`:
- `photo` (string, nullable) — ruta del archivo en el disco `public`, mismo patron que `Employee::photo`.
- `photo_position_x` (unsigned tinyint, default 50) — porcentaje horizontal de encuadre (0-100).
- `photo_position_y` (unsigned tinyint, default 50) — porcentaje vertical de encuadre (0-100).

`Branch` gana un accessor `getPhotoUrlAttribute()` idéntico en forma al de `Employee`: devuelve la URL publica si el archivo existe en el disco `public`, o cadena vacia si no.

### Admin (crear/editar sede)

En los formularios `admin/branches/create.blade.php` y `edit.blade.php` se agrega:
- Input de archivo para la foto (`photo`, `image|mimes:jpeg,png,jpg,webp|max:5120`, igual que la validacion de foto de empleado).
- Un marco circular de previsualizacion (mismo tamano/estilo que el `avatar-ring` de la tarjeta publica) que muestra la imagen recien seleccionada (via `URL.createObjectURL` en el `change` del input) o la foto ya guardada al editar.
- Arrastre con el mouse/touch sobre esa previsualizacion: al soltar, JS calcula el desplazamiento como porcentaje del tamano de la imagen y lo escribe en dos inputs ocultos `photo_position_x` / `photo_position_y` (default 50/50 si nunca se arrastra).
- Boton "Quitar foto" (checkbox `remove_photo`, igual patron que empleados) cuando ya existe una foto guardada.

`BranchController@store` y `@update` agregan `photo`, `photo_position_x`, `photo_position_y` a la validacion y al guardado, reusando el mismo patron de `Storage::disk('public')->store('branches', 'public')` y borrado de foto anterior que ya usa `EmployeeController`.

### Tarjeta publica

En `card/branch.blade.php`, el `avatar-inner` que hoy siempre muestra `<i class="fas fa-building">` pasa a:
- Si `$branch->photo_url` existe: `<img src="{{ $branch->photo_url }}" style="object-position: {{ $branch->photo_position_x }}% {{ $branch->photo_position_y }}%;">` (con `object-fit: cover` ya heredado de la regla existente `.avatar-inner img`).
- Si no: el icono de edificio actual, sin cambios.

## 2. Carrusel de fotos externas

### Datos

Nueva tabla `branch_photos`:
- `id`
- `branch_id` (FK a `branches`, `cascadeOnDelete`)
- `url` (string, 1000)
- `position` (unsigned integer) — define el orden dentro del carrusel
- `timestamps`

`Branch` gana la relacion `photos()` (`hasMany(BranchPhoto::class)`, `orderBy('position')`). Nuevo modelo `BranchPhoto` (fillable: `branch_id`, `url`, `position`).

### Admin (crear/editar sede)

En los mismos formularios, una seccion "Fotos de la sede (URLs externas)":
- Lista de inputs de texto, uno por foto existente (al editar) mas uno vacio al final.
- Boton "+ Agregar foto" (JS, sin recargar pagina) agrega otro input de texto al final de la lista.
- Boton "×" junto a cada input lo quita de la lista (JS, sin recargar pagina).
- Los inputs se nombran como array (`photos[]`) para llegar al controlador como lista ordenada.

`BranchController@store`/`@update` validan `photos.*` como `nullable|url|max:1000`, descartan las filas vacias, y reemplazan todas las `branch_photos` de esa sede: borran las existentes (en `update`) y crean una fila nueva por cada URL no vacia, en el orden recibido (`position` = indice en la lista filtrada).

### Tarjeta publica

En `card/branch.blade.php`, si `$branch->photos` no esta vacio, se agrega una nueva seccion entre el directorio de asesores y el `contact-grid`:
- Un contenedor con una imagen visible a la vez (`object-fit: cover`, mismo radio de borde que el resto de la tarjeta).
- Flechas "‹"/"›" para retroceder/avanzar (JS vanilla: cambia una clase/indice, oculta la imagen actual y muestra la siguiente).
- Puntos indicadores debajo, uno por foto, resaltando el activo.
- Si `$branch->photos` esta vacio, la seccion completa no se renderiza (sin espacio vacio ni controles deshabilitados).

## Resumen de archivos a tocar

- Migraciones nuevas: `add_photo_fields_to_branches_table`, `create_branch_photos_table`.
- `app/Models/Branch.php`: `photo_url` accessor, fillable, relacion `photos()`.
- `app/Models/BranchPhoto.php` (nuevo).
- `app/Http/Controllers/Admin/BranchController.php`: manejo de foto + posicion + sincronizacion de `branch_photos` en `store`/`update`.
- `resources/views/admin/branches/create.blade.php` y `edit.blade.php`: campo de foto + arrastre, lista de URLs.
- `resources/views/card/branch.blade.php`: foto principal con posicion, seccion de carrusel.

## Testing

- Test de feature: crear sede con foto y posicion guarda los tres campos correctamente.
- Test de feature: quitar foto (`remove_photo`) borra el archivo y deja `photo` en null.
- Test de feature: guardar una lista de URLs crea las filas `branch_photos` en el orden enviado.
- Test de feature: guardar una lista de URLs mas corta que la anterior elimina las filas sobrantes (reemplazo completo).
- Test de feature: `GET /sede/{slug}` muestra la foto principal con el `object-position` correcto cuando existe, e icono de edificio cuando no.
- Test de feature: `GET /sede/{slug}` no renderiza la seccion de carrusel cuando la sede no tiene fotos.
