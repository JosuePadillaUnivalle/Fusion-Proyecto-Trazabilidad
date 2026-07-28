# Deploy AgroFusion — cuenta nueva de Railway (sin perder datos)

## Por qué “volvía un backup” en la cuenta anterior

En cada reinicio/redeploy el contenedor ejecutaba seeders (o regeneraba cosas).
Eso **pisaba** lo que habías cargado en la web y parecía un rollback.

En esta versión:

- Por defecto **NO** se siembra nada al arrancar.
- `RUN_FLUJO_SEED=true` / `RUN_SEED=true` solo siembran **una vez** (lock en el volumen).
- Después de sembrar, deja esas variables en `false`.

## Checklist de persistencia (obligatorio)

1. **Postgres** como plugin de Railway (BD real, no SQLite en el contenedor).
2. **Volume** montado en el servicio web:  
   Mount path = `/app/storage/app/public`  
   (fotos, evidencias, locks de seed).
3. Variables fijas (no cambian en cada deploy):
   - `APP_KEY` (una sola vez, cópiala y no la regeneres)
   - `APP_URL` / `APP_PUBLIC_URL` = tu dominio `*.up.railway.app`
   - `DB_CONNECTION=pgsql`
   - `RUN_SEED=false`
   - `RUN_FLUJO_SEED=false` (después del primer seed)

## Pasos en la cuenta nueva

### 1) Login CLI con la cuenta nueva

```powershell
cd C:\Users\cjosu\Desktop\AGROFUSION
railway logout
railway login
railway whoami
```

Debe salir el usuario de la cuenta nueva (no el de Josué/cjosuepadilla).

### 2) Crear proyecto + Postgres + servicio

En el dashboard (o CLI):

1. **New Project** → Empty Project (o Deploy from GitHub si el repo está en tu cuenta).
2. En el proyecto: **+ Database** → **PostgreSQL**.
3. **+ Empty Service** (o deploy desde local con `railway up`).
4. En el servicio web: **Settings → Volumes** → Add volume  
   Path: `/app/storage/app/public`
5. Conecta la variable `DATABASE_URL` desde Postgres al servicio web (Reference).

### 3) Variables del servicio web

| Variable | Valor |
|----------|--------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://TU-SERVICIO.up.railway.app` |
| `APP_PUBLIC_URL` | igual que `APP_URL` |
| `APP_KEY` | generar una vez (`php artisan key:generate --show`) y pegarla |
| `DB_CONNECTION` | `pgsql` |
| `RUN_SEED` | `false` |
| `RUN_FLUJO_SEED` | `true` **solo el primer deploy**, luego `false` |

### 4) Subir el código

Desde esta carpeta (recomendado si GitHub no está en la cuenta nueva):

```powershell
railway link
railway up
```

O conecta el repo de GitHub al servicio (Deploy from GitHub).

### 5) Sembrar el flujo completo (QR) una sola vez

Opción A — con variable (primer deploy):

1. `RUN_FLUJO_SEED=true`
2. Redeploy
3. Cuando veas en logs “Seed OK / Flujo completo listo”
4. Pon `RUN_FLUJO_SEED=false` y **Restart** (no hace falta rebuild)

Opción B — a mano (mejor):

```powershell
# Con proxy público de Postgres (desde el servicio Postgres → Connect / DATABASE_PUBLIC_URL)
$env:DATABASE_URL="postgresql://..."
$env:APP_URL="https://TU-SERVICIO.up.railway.app"
php artisan db:seed --class=FlujoCompletoTrazabilidadQrSeeder --force
```

### 6) Verificar QR

Abre:

`https://TU-SERVICIO.up.railway.app/trazabilidad/TRZ-PDV-FLUJO-202601`

Y lotes:

`https://TU-SERVICIO.up.railway.app/lotes`

## Reglas para no perder datos nunca más

- Nunca uses `migrate:fresh` ni `db:wipe` en producción.
- No dejes `RUN_SEED=true` permanente.
- No borres el volumen ni la base Postgres.
- Si el trial se acaba, **upgrade** antes de que apaguen servicios (o exporta backups).
