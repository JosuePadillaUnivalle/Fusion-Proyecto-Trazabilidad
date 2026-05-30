# Demo final - Fusion-Proyectos

## Objetivo
Demostrar integracion completa de modulos con control granular de permisos en Web y API.

## Credenciales de demo
- admin: `admin@agrofusion.com` / `12345`
- agricultor: `agricultor@agrofusion.com` / `12345`
- operador: `operador@agrofusion.com` / `12345`
- planta: `planta@agrofusion.com` / `12345`
- transportista: `transportista@agrofusion.com` / `12345`
- almacen: `almacen@agrofusion.com` / `12345`

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
