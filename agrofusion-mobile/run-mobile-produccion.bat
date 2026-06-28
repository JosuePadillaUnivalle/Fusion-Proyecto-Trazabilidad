@echo off
cd /d "%~dp0"

echo ========================================
echo   AgroFusion Mobile - PRODUCCION
echo ========================================
echo.
echo En src/config/api.js cambia a:
echo   export const API_MODE = 'production';
echo.
echo Luego recarga la app en Expo Go.
echo Requiere internet en el telefono.
echo Mismas credenciales que la web en Railway.
echo.
pause

call run-mobile.bat
