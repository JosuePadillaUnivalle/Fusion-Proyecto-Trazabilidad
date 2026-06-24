# AgroFusion — Instalación rápida (Windows / local)

Guía para que cualquier compañero clone el repo y tenga **los mismos datos y permisos** que en tu máquina.

## Requisitos

- PHP >= 8.2 (extensiones: `sqlite3`, `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`)
- [Composer](https://getcomposer.org/)
- Git

## Opción A — Automática (recomendada)

En PowerShell, dentro de la carpeta del proyecto:

```powershell
.\scripts\instalar-local.ps1
php artisan serve --port=8001
```

Abrir: **http://127.0.0.1:8001**

> **Importante (sesión):** deja `PHP_CLI_SERVER_WORKERS=1` y `SESSION_DRIVER=file`. Usa **una sola URL** en el navegador (solo `http://127.0.0.1:8001` **o** solo la IP WiFi; no alternes entre ambas). Los enlaces del menú ya se adaptan al host actual, pero la cookie de sesión sigue ligada al dominio con el que entraste.

## Opción B — Manual

```powershell
git clone https://github.com/JosuePadillaUnivalle/Fusion-Proyecto-Trazabilidad.git
cd Fusion-Proyecto-Trazabilidad
composer install
copy .env.example .env
php artisan key:generate
php artisan storage:link
php artisan serve --port=8001
```

> Los datos de demostración viven en `database/database.snapshot.sqlite`. Tu copia de trabajo `database/database.sqlite` **no se versiona en Git** (así no se borra al hacer pull). Si la base queda sin usuarios, la app la restaura sola en local.

## Usuarios de prueba

| Rol | Email | Contraseña | Qué puede hacer |
|-----|-------|------------|-----------------|
| **Admin** (acceso total) | `admin@agrofusion.com` | `12345` | Crear lotes, asignar actividades, inventario, usuarios, etc. |
| Agricultor | `agricultor@agrofusion.com` | `12345` | Solo sus lotes y actividades asignadas |
| Agricultor (Luis) | `LuisGuerrero123@gmail.com` | `12345` | Sus lotes y actividades asignadas |
| Planta | `planta@agrofusion.com` | `12345` | Módulo de planta |
| Transportista | `transportista@agrofusion.com` | `12345` | Envíos y rutas |

**Importante:** si ves errores **403 Forbidden** o «no tienes acceso», casi siempre es porque entraste con un rol limitado (por ejemplo agricultor) intentando hacer algo de administrador. **Entra con `admin@agrofusion.com`.**

## Si aparece 403 o «sin acceso» después de clonar

Ejecuta el reparador de permisos:

```powershell
php artisan agrofusion:reparar-permisos
php artisan agrofusion:asegurar-datos-demo
```

Eso sincroniza roles Spatie, la matriz de permisos y los usuarios demo.

Si la base quedó vacía (login «Credenciales inválidas» para todos):

```powershell
php artisan agrofusion:restaurar-datos-locales --force
```

Eso copia `database/database.snapshot.sqlite` (respaldo con lotes reales) y repara permisos.

Si la base quedó vacía o corrupta (alternativa manual):

```powershell
php artisan migrate --force
php artisan db:seed --force
php artisan agrofusion:reparar-permisos
php artisan agrofusion:asegurar-datos-demo
```

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| 404 al asignar siembra | Falta tipo «Siembra» en catálogo | `php artisan agrofusion:reparar-permisos` y `agrofusion:asegurar-datos-demo` |
| 403 al crear lotes / asignar actividades | Sesión con rol agricultor o permisos no sembrados | Login como admin + reparar permisos y datos demo |
| Almacén mayorista/planta vacío | Stock demo no sembrado | `php artisan agrofusion:asegurar-datos-demo` |
| Página en blanco / 500 | Falta `APP_KEY` o dependencias | `composer install` + `php artisan key:generate` |
| Sin imágenes / evidencias | Falta enlace storage | `php artisan storage:link` |
| Base vacía / login falla para todos | Git o `migrate:fresh` dejó la base sin usuarios | En local se **restaura sola** al abrir la app. Manual: `php artisan agrofusion:restaurar-datos-locales --force` |
| Luis Guerrero no entra | Contraseña distinta a la demo | `LuisGuerrero123@gmail.com` / `12345` (la restauración normaliza contraseñas demo) |

## Puerto ocupado

```powershell
php artisan serve --port=8002
```

Y en `.env` ajusta `APP_URL=http://127.0.0.1:8002`.
