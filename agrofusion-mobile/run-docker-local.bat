@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0.."

echo ========================================
echo   AgroFusion - Docker local (web + API)
echo ========================================
echo.
echo Solo arranca estos 3 contenedores existentes:
echo   agronexus-db, agronexus-laravel, agronexus
echo No toca tus otros proyectos Docker.
echo.

docker ps -a --filter "name=agronexus" --format "  {{.Names}}  {{.Status}}" 2>nul
echo.

echo [1/3] Iniciando contenedores...
docker start agronexus-db agronexus-laravel agronexus 2>nul
if errorlevel 1 (
    echo.
    echo No se encontraron contenedores. Creando stack AgroFusion...
    docker compose up -d --no-build 2>nul
    if errorlevel 1 (
        echo ERROR: Ejecuta desde la carpeta del proyecto:
        echo   docker compose up -d
        pause
        exit /b 1
    )
)

echo [2/3] Esperando Laravel...
timeout /t 6 /nobreak >nul

echo [3/3] Migraciones API movil (Sanctum)...
docker exec agronexus-laravel php artisan migrate --force 2>nul

echo.
echo IPs de tu PC ^(actualiza LOCAL_HOST en agrofusion-mobile/src/config/api.js^):
ipconfig | findstr /i "192.168.1"
echo.
echo   Web local:  http://localhost:8080
echo   API movil:  http://TU_IP_WIFI:8080/api
echo   Prueba PC:  http://127.0.0.1:8080/api/test-api
echo.
echo Deja Docker corriendo. En otra terminal: agrofusion-mobile\run-mobile.bat
echo.
pause
