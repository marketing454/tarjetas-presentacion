# COMPULAGO Team Presentation

Aplicacion Laravel para administrar tarjetas virtuales de empleados de COMPULAGO.

## Que hace

- Permite iniciar sesion en un panel administrativo.
- Administra sucursales con ciudad, direccion y telefono.
- Administra empleados con cargo, sucursal, foto y WhatsApp.
- Permite elegir el tipo de tarjeta por empleado: asesor normal, asesor de credito o asesor corporativo.
- Publica una tarjeta virtual por empleado en `/card/{slug}`.
- Genera vista previa SVG y descarga PNG del codigo QR de cada tarjeta.

## Stack

- PHP 8.4 en Docker, compatible con Composer `php:^8.3`.
- Laravel 13.
- MySQL 8.
- Nginx 1.25.
- phpMyAdmin 5.2.
- Bootstrap 5 y Font Awesome via CDN.
- `simplesoftwareio/simple-qrcode` para codigos QR.

## Estructura principal

- `app/Http/Controllers/Admin`: controladores del panel administrativo.
- `app/Http/Controllers/CardController.php`: tarjeta publica del empleado.
- `app/Models`: modelos `Branch` y `Employee`.
- `database/migrations`: tablas de sucursales y empleados.
- `database/seeders/AdminSeeder.php`: usuario administrador inicial.
- `resources/views/admin`: vistas del dashboard, sucursales y empleados.
- `resources/views/card/show.blade.php`: tarjeta publica.
- `routes/web.php`: rutas web, login, admin y tarjetas.

## Tipos de tarjeta

Cada empleado tiene un `card_type`:

- `normal`: asesores normales, paleta verde COMPULAGO.
- `credit`: asesores de credito, banner azul/dorado.
- `corporate`: asesores corporativos, banner sobrio azul oscuro.

El tipo se selecciona en el formulario de crear/editar empleado y cambia el banner, la superficie de la tarjeta, el aro de la foto y algunos acentos visuales.

## Imagenes recomendadas

- Foto de perfil: 600 x 600 px, rostro centrado y fondo limpio.
- Fondo personalizado de tarjeta: 900 x 420 px, JPG/PNG/WebP, dejando la zona central baja libre para que no compita con la foto superpuesta.

## Ejecucion con Docker

Desde la raiz del proyecto:

```bash
docker compose up -d --build
```

Servicios:

- App web: `http://localhost:8000`
- phpMyAdmin: `http://localhost:8080`
- MySQL host: `localhost:3307`

Credenciales iniciales:

- Email: `admin@compulago.com`
- Password: `Admin1234!`

## Comandos utiles

```bash
docker exec team_app php artisan migrate --force
docker exec team_app php artisan db:seed --force
docker exec team_app php artisan route:list --except-vendor
docker exec team_app php artisan test
```

## Variables importantes

La aplicacion usa MySQL dentro de Docker:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=team_cards
DB_USERNAME=laravel
```

`APP_URL` define la URL que se incrusta en los codigos QR. Para uso local suele ser:

```env
APP_URL=http://localhost:8000
```

Para compartir tarjetas dentro de la red local, puede apuntar a la IP del equipo, por ejemplo:

```env
APP_URL=http://192.168.2.84:8000
```
