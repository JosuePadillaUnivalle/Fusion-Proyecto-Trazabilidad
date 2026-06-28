@echo off

setlocal enabledelayedexpansion

cd /d "%~dp0"



echo ========================================

echo   AgroFusion Mobile - Expo

echo ========================================

echo.



if not exist "node_modules" (

    echo [1/4] Instalando dependencias...

    call npm install

    if !errorlevel! neq 0 (

        echo ERROR: Fallo al instalar dependencias

        pause

        exit /b 1

    )

) else (

    echo [1/4] Dependencias OK.

)



echo [2/4] IPs de esta PC:

ipconfig | findstr /i "IPv4"

echo.



REM Usa la IP WiFi (192.168.1.x). Cambia aqui si tu red es otra.

set "WIFI_IP="

for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"192.168.1."') do (

    set "WIFI_IP=%%a"

    set "WIFI_IP=!WIFI_IP: =!"

    goto :ip_found

)

:ip_found

if not defined WIFI_IP (

    echo AVISO: No se detecto IP 192.168.1.x

    echo Si el QR falla, usa run-mobile-tunnel.bat

    echo.

) else (

    set "REACT_NATIVE_PACKAGER_HOSTNAME=!WIFI_IP!"

    echo [3/4] IP para el QR ^(WiFi^): !WIFI_IP!

    echo   PC y telefono en la MISMA WiFi.

    echo.

)



echo [4/4] Iniciando Expo ^(LAN^)...

echo   - Actualiza Expo Go en el telefono ^(Play Store / App Store^)

echo   - Si sale "Something went wrong", cierra Expo Go y prueba run-mobile-tunnel.bat

echo   - Docker local: run-docker-local.bat + misma WiFi (puerto 8080)
echo   - API auto: Railway primero, luego Docker local

echo.



npx expo start --lan --clear



pause

