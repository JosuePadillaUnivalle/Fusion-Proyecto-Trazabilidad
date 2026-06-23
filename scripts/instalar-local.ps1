# AgroFusion — instalación local en Windows
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

Write-Host "==> AgroFusion: instalación local" -ForegroundColor Cyan

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Error "Composer no está instalado. Descárgalo en https://getcomposer.org/"
}

Write-Host "==> composer install"
composer install --no-interaction --prefer-dist

if (-not (Test-Path ".env")) {
    Write-Host "==> Creando .env desde .env.example"
    Copy-Item ".env.example" ".env"
}

# Asegurar SQLite (misma config que el equipo de desarrollo)
$envContent = Get-Content ".env" -Raw
$envContent = $envContent -replace '(?m)^DB_CONNECTION=.*$', 'DB_CONNECTION=sqlite'
if ($envContent -notmatch '(?m)^DB_DATABASE=') {
    $envContent += "`nDB_DATABASE=database/database.sqlite`n"
} else {
    $envContent = $envContent -replace '(?m)^DB_DATABASE=.*$', 'DB_DATABASE=database/database.sqlite'
}
if ($envContent -notmatch '(?m)^SESSION_DRIVER=') {
    $envContent += "`nSESSION_DRIVER=file`n"
} else {
    $envContent = $envContent -replace '(?m)^SESSION_DRIVER=.*$', 'SESSION_DRIVER=file'
}
if ($envContent -notmatch '(?m)^CACHE_STORE=') {
    $envContent += "`nCACHE_STORE=file`n"
} else {
    $envContent = $envContent -replace '(?m)^CACHE_STORE=.*$', 'CACHE_STORE=file'
}
if ($envContent -notmatch '(?m)^QUEUE_CONNECTION=') {
    $envContent += "`nQUEUE_CONNECTION=sync`n"
} else {
    $envContent = $envContent -replace '(?m)^QUEUE_CONNECTION=.*$', 'QUEUE_CONNECTION=sync'
}
Set-Content ".env" $envContent -NoNewline

if ((Get-Content ".env" -Raw) -notmatch 'APP_KEY=base64:') {
    Write-Host "==> php artisan key:generate"
    php artisan key:generate --force
}

if (-not (Test-Path "database/database.sqlite")) {
    Write-Host "==> Sin SQLite en el repo: migrando y sembrando..."
    New-Item -ItemType File -Path "database/database.sqlite" -Force | Out-Null
    php artisan migrate --force
    php artisan db:seed --force
} else {
    Write-Host "==> Usando database/database.sqlite del repositorio"
    php artisan migrate --force
}

Write-Host "==> Reparando roles y permisos"
php artisan agrofusion:reparar-permisos

Write-Host "==> Asegurando stock demo (planta + mayorista)"
php artisan agrofusion:asegurar-datos-demo

if (-not (Test-Path "public/storage")) {
    Write-Host "==> php artisan storage:link"
    php artisan storage:link
}

Write-Host ""
Write-Host "Listo. Ejecuta:" -ForegroundColor Green
Write-Host "  php artisan serve --port=8001"
Write-Host ""
Write-Host "Login admin: admin@agrofusion.com / 12345" -ForegroundColor Yellow
