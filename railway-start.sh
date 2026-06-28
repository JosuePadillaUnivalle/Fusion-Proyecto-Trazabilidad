#!/usr/bin/env bash
set -euo pipefail

echo "==> AgroFusion — arranque en Railway"

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
  echo "==> Generando APP_KEY..."
  php artisan key:generate --force --no-interaction
fi

vincular_storage_volumen() {
  # Railway: volumen persistente en /app/storage/app/public; app en /var/www (Nixpacks).
  if [ -d /app/storage/app/public ] && [ -f /var/www/artisan ]; then
    echo "==> Enlazando storage al volumen persistente de Railway"
    mkdir -p /var/www/storage/app /var/www/public
    rm -rf /var/www/storage/app/public
    ln -sfn /app/storage/app/public /var/www/storage/app/public
    rm -f /var/www/public/storage
    ln -sfn /var/www/storage/app/public /var/www/public/storage

    if [ -d /var/www/public/storage/insumos ]; then
      n=$(ls -1 /var/www/public/storage/insumos 2>/dev/null | wc -l | tr -d ' ')
      echo "==> Storage OK: ${n} archivo(s) en insumos/"
    else
      echo "==> AVISO: volumen montado pero sin carpeta insumos/"
    fi

    return 0
  fi

  php artisan storage:link --force --no-interaction 2>/dev/null || true
}

vincular_storage_volumen

if php -m 2>/dev/null | grep -qi '^gd$'; then
  echo "==> PHP ext-gd: disponible (firmas en PDF habilitadas)"
else
  echo "==> AVISO: PHP ext-gd NO cargada; los PDF usarán texto en lugar de imagen de firma"
fi

php artisan config:clear --no-interaction || true
php artisan migrate --force --no-interaction || echo "==> Migraciones: revise la conexión a la base de datos en Variables."

if [ "${RUN_SEED:-false}" = "true" ]; then
  php artisan db:seed --no-interaction || true
fi

# Siempre al final: migrate/seed no deben dejar storage apuntando a carpeta efímera.
vincular_storage_volumen

HOST="0.0.0.0"
PORT="${PORT:-8080}"

echo "==> Servidor HTTP en ${HOST}:${PORT}"
exec php artisan serve --host="${HOST}" --port="${PORT}"
