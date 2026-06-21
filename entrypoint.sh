#!/bin/bash

# Crear .env si no existe
if [ ! -f .env ]; then
    echo "📄 No existe .env — creando desde .env.example"
    cp .env.example .env
else
    echo "✔️ Archivo .env ya existe — no se copia"
fi

# Solo generar APP_KEY si está vacía o no existe (evita invalidar sesiones en reinicios)
if grep -qE "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "✔️ APP_KEY ya configurada — no se regenera"
else
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate --force || true
fi

echo "⚙️ Aplicando permisos..."
chmod -R 777 storage bootstrap/cache || true

echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force || true

echo "🌱 Ejecutando Seeder..."
php artisan db:seed --force || true

echo "🚀 Iniciando PHP-FPM..."
exec php-fpm
