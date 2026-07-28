#!/usr/bin/env bash
set -euo pipefail

echo "==> AgroFusion — arranque en Railway"

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
  echo "==> Generando APP_KEY..."
  php artisan key:generate --force --no-interaction
fi

# Volumen persistente de archivos (fotos, evidencias, firmas).
# IMPORTANTE: sin este volumen, cada redeploy “borra” lo subido a storage.
vincular_storage_volumen() {
  if [ -d /app/storage/app/public ] && [ -f /var/www/artisan ]; then
    echo "==> Enlazando storage al volumen persistente de Railway"
    mkdir -p /var/www/storage/app /var/www/public
    rm -rf /var/www/storage/app/public
    ln -sfn /app/storage/app/public /var/www/storage/app/public
    rm -f /var/www/public/storage
    ln -sfn /var/www/storage/app/public /var/www/public/storage
    return 0
  fi

  if [ -d /app/storage/app/public ] && [ -f ./artisan ]; then
    echo "==> Enlazando storage (cwd) al volumen persistente"
    mkdir -p storage/app public
    rm -rf storage/app/public
    ln -sfn /app/storage/app/public storage/app/public
    rm -f public/storage
    ln -sfn /app/storage/app/public public/storage
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

# Solo migraciones (esquema). NUNCA migrate:fresh ni seed automático en cada reinicio.
# Eso era lo que hacía parecer que “volvió un backup”: cada restart re-sembraba datos demo.
php artisan migrate --force --no-interaction || echo "==> Migraciones: revise DATABASE_URL / Postgres."

# Locks en el volumen para que un seed no se repita aunque dejen la variable en true.
SEED_LOCK_DIR="/app/storage/app/public"
if [ ! -d "$SEED_LOCK_DIR" ]; then
  SEED_LOCK_DIR="storage/app/public"
  mkdir -p "$SEED_LOCK_DIR" 2>/dev/null || true
fi

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

  echo "==> Ejecutando seed único ($flag_name)..."
  if eval "$seed_cmd"; then
    date -u +"%Y-%m-%dT%H:%M:%SZ" > "$lock_file" 2>/dev/null || true
    echo "==> Seed OK. Lock creado: $lock_file"
    echo "==> RECOMENDADO: pon $flag_name=false en Variables de Railway."
  else
    echo "==> Seed falló; no se creó lock (se reintentará si la flag sigue en true)."
  fi
}

# Flujo completo QR (campo → planta → mayorista → PDV). Preferido para demo.
run_seed_once \
  "RUN_FLUJO_SEED" \
  "${RUN_FLUJO_SEED:-false}" \
  "$SEED_LOCK_DIR/.seed_flujo_completo.done" \
  "php artisan db:seed --class=FlujoCompletoTrazabilidadQrSeeder --force --no-interaction"

# Seed base completo (solo si lo pides explícitamente y NO pediste el de flujo).
if [ "${RUN_FLUJO_SEED:-false}" != "true" ]; then
  run_seed_once \
    "RUN_SEED" \
    "${RUN_SEED:-false}" \
    "$SEED_LOCK_DIR/.seed_base.done" \
    "php artisan db:seed --force --no-interaction"
fi

if [ "${RUN_FLUJO_SEED:-false}" != "true" ] && [ "${RUN_SEED:-false}" != "true" ]; then
  echo "==> Seeders omitidos (RUN_FLUJO_SEED/RUN_SEED != true). Tus datos de BD están seguros."
fi

vincular_storage_volumen

HOST="0.0.0.0"
PORT="${PORT:-8080}"

echo "==> Servidor HTTP en ${HOST}:${PORT}"
exec php artisan serve --host="${HOST}" --port="${PORT}"
