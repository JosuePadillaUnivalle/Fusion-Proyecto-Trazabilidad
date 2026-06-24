@echo off
cd /d "%~dp0"
echo ========================================
echo   Capturas automaticas - Guion AgroFusion
echo ========================================
echo.
echo Requisito: Expo corriendo (npm start) y abrir web con "w"
echo o que http://localhost:8081 responda.
echo.
if not exist "node_modules\playwright" (
  echo Instalando Playwright...
  call npm install --no-save playwright
  call npx playwright install chromium
)
node scripts/capture-guion-screenshots.mjs
echo.
echo Capturas en: capturas-guion\
pause
