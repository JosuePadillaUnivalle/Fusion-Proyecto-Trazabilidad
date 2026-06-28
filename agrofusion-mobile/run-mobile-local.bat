@echo off
cd /d "%~dp0"

echo ========================================
echo   AgroFusion Mobile - Docker local
echo ========================================
echo.
echo 1) En otra terminal: run-docker-local.bat
echo 2) PC y telefono en la MISMA WiFi
echo 3) La app intenta Railway y luego Docker local solo
echo.
pause

call run-mobile.bat
