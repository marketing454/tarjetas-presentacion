# Despliegue en cPanel — COMPULAGO Tarjetas

Guía paso a paso. App Laravel que se sirve desde `public/`. Los datos reales
(11 sedes, 30 empleados) ya están en `deploy/team_cards.sql`.

---

## Requisitos en el hosting
- PHP **8.2 o superior** (cPanel → "Select PHP Version").
- Extensiones PHP: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`.
- MySQL 5.7+ / MariaDB 10.3+.
- Acceso SSH o **Terminal** de cPanel (para composer/artisan). Composer disponible.

---

## Fase 1 — Base de datos (cPanel)
1. cPanel → **MySQL® Databases** → crear base (ej. `usuario_team_cards`).
2. Crear usuario MySQL y contraseña fuerte.
3. Añadir el usuario a la base con **ALL PRIVILEGES**.
4. Anotar nombre, usuario y contraseña → van al `.env`.

## Fase 2 — Importar los datos
1. cPanel → **phpMyAdmin** → seleccionar la base creada.
2. Pestaña **Importar** → subir `deploy/team_cards.sql` → Continuar.
3. Verificar que aparezcan las tablas (`branches`, `employees`, `card_scans`, etc.).

## Fase 3 — Subir el código
Comprimir la carpeta `src/` SIN: `vendor/`, `node_modules/`, `.env`, `storage/logs/*`.

**Estructura recomendada (subdominio):**
- Subir y descomprimir el proyecto en `~/laravel-tarjetas` (fuera de `public_html`).
- Crear subdominio (ej. `tarjetas.compulago.com`) con **Document Root** =
  `~/laravel-tarjetas/public`.

**Alternativa (hosting básico sin cambiar document root):**
- Contenido de `public/` → `public_html/`.
- Resto del proyecto → `~/laravel-tarjetas/`.
- Editar `public_html/index.php` y corregir las dos rutas:
  ```php
  require __DIR__.'/../laravel-tarjetas/vendor/autoload.php';
  $app = require_once __DIR__.'/../laravel-tarjetas/bootstrap/app.php';
  ```

## Fase 4 — Configurar el .env
1. Copiar `deploy/.env.production.example` como `.env` en la raíz del proyecto.
2. Rellenar `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_URL`.
3. Generar la clave:  `php artisan key:generate`  (pega el resultado en `APP_KEY`).

## Fase 5 — Instalar dependencias y enlazar storage
Desde Terminal/SSH, en la raíz del proyecto:
```bash
composer install --no-dev --optimize-autoloader
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> Si `storage:link` falla en cPanel, crear el enlace manual:
> `ln -s ../laravel-tarjetas/storage/app/public public_html/storage`

Permisos de escritura:
```bash
chmod -R 775 storage bootstrap/cache
```
> **NO** ejecutar `migrate` ni `db:seed`: los datos ya se importaron en la Fase 2.

## Fase 6 — Dominio, HTTPS y prueba
1. cPanel → **SSL/TLS Status** → activar Let's Encrypt → forzar HTTPS.
2. Probar:
   - Login admin: `https://tu-dominio/login`
   - Tarjeta empleado: `/card/{slug}`
   - Tarjeta sede: `/sede/{slug}`
   - Generar/descargar QR y subir una foto de empleado.

## Fase 7 — Post-deploy (seguridad)
- [ ] Cambiar la contraseña del admin `admin@compulago.com`.
- [ ] Confirmar `APP_DEBUG=false`.
- [ ] Backup inicial de la BD desde phpMyAdmin.
- [ ] Si subes fotos nuevas y no aparecen, revisar el symlink de `storage`.

---

## Re-despliegue (cuando haya cambios de código)
```bash
git pull   # o subir archivos cambiados
composer install --no-dev --optimize-autoloader
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
