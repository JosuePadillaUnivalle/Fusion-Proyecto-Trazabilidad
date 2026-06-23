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

> El archivo `database/database.sqlite` ya viene en el repositorio con datos de demostración. **No ejecutes** `migrate:fresh` si quieres conservarlos.

## Usuarios de prueba

| Rol | Email | Contraseña | Qué puede hacer |
|-----|-------|------------|-----------------|
| **Admin** (acceso total) | `admin@agrofusion.com` | `12345` | Crear lotes, asignar actividades, inventario, usuarios, etc. |
| Agricultor | `agricultor@agrofusion.com` | `12345` | Solo sus lotes y actividades asignadas |
| Planta | `planta@agrofusion.com` | `12345` | Módulo de planta |
| Transportista | `transportista@agrofusion.com` | `12345` | Envíos y rutas |

**Importante:** si ves errores **403 Forbidden** o «no tienes acceso», casi siempre es porque entraste con un rol limitado (por ejemplo agricultor) intentando hacer algo de administrador. **Entra con `admin@agrofusion.com`.**

## Si aparece 403 o «sin acceso» después de clonar

Ejecuta el reparador de permisos:

```powershell
php artisan agrofusion:reparar-permisos
```

Eso sincroniza roles Spatie, la matriz de permisos y los usuarios demo.

Si la base quedó vacía o corrupta:

```powershell
php artisan migrate --force
php artisan db:seed --force
php artisan agrofusion:reparar-permisos
```

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| 404 al asignar siembra | Falta tipo «Siembra» en catálogo | `php artisan agrofusion:reparar-permisos` |
| 403 al crear lotes / asignar actividades | Sesión con rol agricultor o permisos no sembrados | Login como admin + `php artisan agrofusion:reparar-permisos` |
| Página en blanco / 500 | Falta `APP_KEY` o dependencias | `composer install` + `php artisan key:generate` |
| Sin imágenes / evidencias | Falta enlace storage | `php artisan storage:link` |
| Base vacía | Se corrió `migrate:fresh` | Volver a clonar o ejecutar `php artisan db:seed` |

## Puerto ocupado

```powershell
php artisan serve --port=8002
```

Y en `.env` ajusta `APP_URL=http://127.0.0.1:8002`.
