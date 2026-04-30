# Demo final - Fusion-Proyectos

## Objetivo
Demostrar integracion completa de modulos con control granular de permisos en Web y API.

## Credenciales de demo
- admin: `admin@agronexus.com` / `123456`
- operador: `operador@agronexus.com` / `123456`
- agricultor: `agricultor@agronexus.com` / `123456`

## Flujo recomendado (8-10 min)
1. Ingresar como `admin`.
2. Mostrar menu lateral con modulos habilitados (Lotes, Inventario, Pedidos, Ventas, Certificaciones, Envios).
3. Abrir rutas de gestion:
   - `lotes.index`, `insumos.index`, `pedidos.index`, `ventas.index`, `certificaciones.index`, `gestion.index`.
4. Mostrar acciones CRUD habilitadas (botones crear/editar/eliminar segun permisos).
5. Cerrar sesion e ingresar como `operador`:
   - Mostrar acceso a Pedidos/Ventas/Inventario/Lotes segun permisos.
   - Verificar restriccion de paneles admin (403 o no visible en UI).
6. Cerrar sesion e ingresar como `agricultor`:
   - Mostrar acceso a Certificaciones e Inventario.
   - Verificar bloqueo en modulos no permitidos.
7. Demostrar API (Postman/Insomnia):
   - `GET /api/pedidos`, `GET /api/ventas`, `GET /api/certificaciones`
   - Confirmar diferencias por rol.

## Comandos de validacion previos a demo
```bash
php artisan optimize:clear
php artisan db:seed --class=RolePermissionSeeder --force
php artisan route:list --path=envios -vv
php artisan route:list --path=pedidos -vv
php artisan route:list --path=api -vv
php artisan test
```

## Evidencia tecnica disponible
- Matriz: `config/permission_matrix.php`
- Seeder granular: `database/seeders/RolePermissionSeeder.php`
- Middleware custom: `app/Http/Middleware/ActionPermissionMiddleware.php`
- Rutas web: `routes/web.php`
- Rutas API: `routes/api.php`
- Pruebas:
  - `ApiAccessTest`
  - `CatalogosAccessTest`
  - `LotesAccessTest`
  - `InsumosAccessTest`
  - `PedidosAccessTest`
  - `VentasAccessTest`
  - `CertificacionesAccessTest`
  - `OrgTrackAccessTest`

