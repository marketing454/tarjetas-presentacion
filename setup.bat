@echo off
chcp 65001 >nul
cls
echo.
echo  ╔══════════════════════════════════════════════════╗
echo  ║     COMPULAGO - Team Presentation               ║
echo  ║     Instalacion inicial del proyecto            ║
echo  ╚══════════════════════════════════════════════════╝
echo.

REM Verificar Docker
docker --version >nul 2>&1
if errorlevel 1 (
    echo  [ERROR] Docker no esta instalado o no esta corriendo.
    echo  Instala Docker Desktop desde: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)
echo  [OK] Docker detectado.

REM Paso 1: Crear proyecto Laravel en src/
echo.
echo  [1/7] Instalando Laravel 11 en src/ ...
if not exist "src\artisan" (
    docker run --rm -v "%CD%/src:/app" -w /app composer:2 ^
        create-project laravel/laravel . --no-interaction --prefer-dist
    if errorlevel 1 (
        echo  [ERROR] Fallo la instalacion de Laravel.
        pause
        exit /b 1
    )
    echo  [OK] Laravel instalado.
) else (
    echo  [OK] Laravel ya instalado, continuando...
)

REM Paso 2: Instalar paquete QR Code
echo.
echo  [2/7] Instalando paquete QR Code...
docker run --rm -v "%CD%/src:/app" -w /app composer:2 ^
    require simplesoftwareio/simple-qrcode --no-interaction --quiet
echo  [OK] Paquete QR instalado.

REM Paso 3: Copiar archivos del proyecto
echo.
echo  [3/7] Aplicando archivos del proyecto...
xcopy /E /Y /I /Q "custom\*" "src\"
echo  [OK] Archivos copiados.

REM Paso 4: Iniciar servicios
echo.
echo  [4/7] Construyendo e iniciando servicios Docker...
docker-compose up -d --build
if errorlevel 1 (
    echo  [ERROR] Fallo al iniciar servicios.
    pause
    exit /b 1
)
echo  [OK] Servicios iniciados.

REM Paso 5: Esperar base de datos
echo.
echo  [5/7] Esperando que la base de datos este lista (25 segundos)...
timeout /t 25 /nobreak >nul
echo  [OK] Listo.

REM Paso 6: Configurar aplicacion
echo.
echo  [6/7] Configurando aplicacion Laravel...
docker exec team_app php artisan key:generate --force
docker exec team_app php artisan storage:link
docker exec team_app php artisan migrate --force
docker exec team_app php artisan db:seed --force
docker exec team_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec team_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
echo  [OK] Configuracion completa.

echo.
echo  ╔══════════════════════════════════════════════════╗
echo  ║              ¡INSTALACION COMPLETA!             ║
echo  ╠══════════════════════════════════════════════════╣
echo  ║                                                  ║
echo  ║   Dashboard Admin:  http://localhost:8000        ║
echo  ║   phpMyAdmin:       http://localhost:8080        ║
echo  ║                                                  ║
echo  ╠══════════════════════════════════════════════════╣
echo  ║   CREDENCIALES ADMIN:                            ║
echo  ║   Email:     admin@compulago.com                 ║
echo  ║   Password:  Admin1234!                          ║
echo  ╠══════════════════════════════════════════════════╣
echo  ║                                                  ║
echo  ║   Detener:  docker-compose down                  ║
echo  ║   Iniciar:  docker-compose up -d                 ║
echo  ║                                                  ║
echo  ╚══════════════════════════════════════════════════╝
echo.
pause
