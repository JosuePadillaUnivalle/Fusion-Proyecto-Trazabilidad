@echo off
cd /d "%~dp0"

echo ========================================
echo   AgroFusion Mobile - Expo TUNNEL
echo ========================================
echo.
echo Usa este modo si el QR en LAN da "Something went wrong".
echo Requiere internet. Puede tardar un poco mas en cargar.
echo.

if not exist "node_modules" (
    call npm install
)

npx expo start --tunnel --clear

pause
