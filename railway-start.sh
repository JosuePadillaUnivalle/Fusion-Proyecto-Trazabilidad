#!/usr/bin/env bash
set -euo pipefail

echo "==> AgroFusion — arranque en Railway"

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
  echo "==> Generando APP_KEY..."
  php artisan key:generate --force --no-interaction
fi

php artisan config:clear --no-interaction || true
php artisan migrate --force --no-interaction || echo "==> Migraciones: revise la conexión a la base de datos en Variables."

if [ "${RUN_SEED:-false}" = "true" ]; then
  php artisan db:seed --force --no-interaction || true
fi

php artisan storage:link --no-interaction 2>/dev/null || true

HOST="0.0.0.0"
PORT="${PORT:-8080}"

echo "==> Servidor HTTP en ${HOST}:${PORT}"
exec php artisan serve --host="${HOST}" --port="${PORT}"
