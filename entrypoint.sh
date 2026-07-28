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

# NUNCA sembrar en cada reinicio: eso borraba / pisaba datos reales (efecto “backup”).
SEED_LOCK_DIR="/app/storage/app/public"
mkdir -p "$SEED_LOCK_DIR" 2>/dev/null || SEED_LOCK_DIR="storage/app/public"

if [ "${RUN_FLUJO_SEED:-false}" = "true" ]; then
    if [ -f "$SEED_LOCK_DIR/.seed_flujo_completo.done" ]; then
        echo "⏭️ FlujoCompleto ya sembrado (lock). Datos preservados."
    else
        echo "🌱 Ejecutando FlujoCompletoTrazabilidadQrSeeder (una vez)..."
        if php artisan db:seed --class=FlujoCompletoTrazabilidadQrSeeder --force; then
            date -u +"%Y-%m-%dT%H:%M:%SZ" > "$SEED_LOCK_DIR/.seed_flujo_completo.done" 2>/dev/null || true
            echo "✔️ Seed flujo OK. Pon RUN_FLUJO_SEED=false en Railway."
        fi
    fi
elif [ "${RUN_SEED:-false}" = "true" ]; then
    if [ -f "$SEED_LOCK_DIR/.seed_base.done" ]; then
        echo "⏭️ Seed base ya ejecutado (lock). Datos preservados."
    else
        echo "🌱 Ejecutando Seeder completo (una vez)..."
        if php artisan db:seed --force; then
            date -u +"%Y-%m-%dT%H:%M:%SZ" > "$SEED_LOCK_DIR/.seed_base.done" 2>/dev/null || true
            echo "✔️ Seed base OK. Pon RUN_SEED=false en Railway."
        fi
    fi
else
    echo "⏭️ Seeders omitidos (RUN_SEED/RUN_FLUJO_SEED != true)"
fi

echo "🚀 Iniciando PHP-FPM..."
exec php-fpm
