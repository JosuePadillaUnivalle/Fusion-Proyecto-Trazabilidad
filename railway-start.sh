#!/usr/bin/env bash
set -euo pipefail

echo "==> AgroFusion — arranque en Railway"

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
  echo "==> Generando APP_KEY..."
  php artisan key:generate --force --no-interaction
fi

# Volumen persistente: en Railway se monta en /app/storage/app/public.
# NO borrar esa ruta (falla con "Device or resource busy").
vincular_storage_volumen() {
  local public_storage="storage/app/public"

  if [ -d "$public_storage" ] && [ -f ./artisan ]; then
    mkdir -p "$public_storage" public
    if [ ! -e public/storage ]; then
      ln -sfn "$(pwd)/$public_storage" public/storage 2>/dev/null \
        || php artisan storage:link --force --no-interaction 2>/dev/null \
        || true
    fi
    echo "==> Storage listo en $public_storage"
    return 0
  fi

  php artisan storage:link --force --no-interaction 2>/dev/null || true
}

vincular_storage_volumen

if php -m 2>/dev/null | grep -qi '^gd$'; then
  echo "==> PHP ext-gd: disponible"
else
  echo "==> AVISO: PHP ext-gd NO cargada"
fi

php artisan config:clear --no-interaction || true

# Solo migraciones. Nunca migrate:fresh ni seed en cada reinicio.
php artisan migrate --force --no-interaction || echo "==> Migraciones: revise DATABASE_URL / Postgres."

SEED_LOCK_DIR="storage/app/public"
mkdir -p "$SEED_LOCK_DIR" 2>/dev/null || true

run_seed_once() {
  local flag_name="$1"
  local flag_value="$2"
  local lock_file="$3"
  local seed_cmd="$4"

  if [ "$flag_value" != "true" ]; then
    return 0
  fi

  if [ -f "$lock_file" ]; then
    echo "==> Seed omitido: ya existe lock $lock_file (datos preservados)."
    echo "    Para forzar de nuevo: borra ese archivo del volumen y redeploy."
    return 0
  fi

  echo "==> Ejecutando seed unico ($flag_name)..."
  if eval "$seed_cmd"; then
    date -u +"%Y-%m-%dT%H:%M:%SZ" > "$lock_file" 2>/dev/null || true
    echo "==> Seed OK. Lock creado: $lock_file"
    echo "==> RECOMENDADO: pon $flag_name=false en Variables de Railway."
  else
    echo "==> Seed fallo; no se creo lock."
  fi
}

run_seed_once \
  "RUN_FLUJO_SEED" \
  "${RUN_FLUJO_SEED:-false}" \
  "$SEED_LOCK_DIR/.seed_flujo_completo.done" \
  "php artisan db:seed --class=FlujoCompletoTrazabilidadQrSeeder --force --no-interaction"

if [ "${RUN_FLUJO_SEED:-false}" != "true" ]; then
  run_seed_once \
    "RUN_SEED" \
    "${RUN_SEED:-false}" \
    "$SEED_LOCK_DIR/.seed_base.done" \
    "php artisan db:seed --force --no-interaction"
fi

if [ "${RUN_FLUJO_SEED:-false}" != "true" ] && [ "${RUN_SEED:-false}" != "true" ]; then
  echo "==> Seeders omitidos. Datos de BD seguros."
fi

vincular_storage_volumen

HOST="0.0.0.0"
PORT="${PORT:-8080}"

echo "==> Servidor HTTP en ${HOST}:${PORT}"
exec php artisan serve --host="${HOST}" --port="${PORT}"
