@echo off
cd /d "%~dp0"

echo ========================================
echo   AgroFusion - Generar APK (instalar 1 vez)
echo ========================================
echo.
echo Esto crea un .apk que instalas en el celular.
echo Despues NO necesitas:
echo   - Expo Go
echo   - Escanear QR
echo   - Conectar el celular a la PC
echo.
echo Solo necesitas internet (WiFi o datos) para Railway.
echo Mismas credenciales que la web en produccion.
echo.
echo Requisitos:
echo   - Cuenta gratis en https://expo.dev
echo   - npm install -g eas-cli
echo   - eas login
echo.
echo El build se hace en la nube de Expo (10-20 min).
echo Al terminar te da un enlace para descargar el APK.
echo.
pause

call npx eas-cli build --platform android --profile preview

echo.
echo Instala el APK en el Android y abre "AgroFusion".
pause
