@echo off
cd /d "%~dp0"

echo ========================================
echo   AgroFusion Mobile - Expo
echo ========================================
echo.

REM Check if node_modules exists
if not exist "node_modules" (
    echo [1/2] Instalando dependencias...
    call npm install
    if %errorlevel% neq 0 (
        echo ERROR: Fallo al instalar dependencias
        pause
        exit /b 1
    )
) else (
    echo [1/2] Dependencias ya instaladas.
)

echo [2/2] Iniciando Expo...
echo.
echo Abre la app con:
echo   - Escanea el QR con Expo Go (Android/iOS)
echo   - Presiona 'w' para abrir en navegador
echo.

set REACT_NATIVE_PACKAGER_HOSTNAME=192.168.137.1
npx expo start
pause
