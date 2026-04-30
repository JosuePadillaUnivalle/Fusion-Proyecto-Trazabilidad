# Matriz de Permisos Granular

Esta matriz se define en `config/permission_matrix.php` y se aplica con:

- Seeder: `database/seeders/RolePermissionSeeder.php`
- Middleware personalizado: `action.permission`
- Rutas: `routes/web.php`

## Modulos y acciones (CRUD/API)

| Modulo | Accion | Permiso |
|---|---|---|
| Envíos | Crear | `envios.create` |
| Envíos | Leer/Seguimiento | `envios.view` |
| Envíos | Actualizar | `envios.update` |
| Envíos | Eliminar | `envios.delete` |
| Envíos | Dashboard admin | `envios.admin.view` |
| Vehículos | Crear | `vehiculos.create` |
| Vehículos | Leer | `vehiculos.view` |
| Vehículos | Actualizar | `vehiculos.update` |
| Vehículos | Eliminar | `vehiculos.delete` |
| Transportistas | Crear | `transportistas.create` |
| Transportistas | Leer | `transportistas.view` |
| Transportistas | Actualizar | `transportistas.update` |
| Transportistas | Eliminar | `transportistas.delete` |
| Direcciones | Crear | `direcciones.create` |
| Direcciones | Leer | `direcciones.view` |
| Direcciones | Actualizar | `direcciones.update` |
| Direcciones | Eliminar | `direcciones.delete` |
| Reportes | Leer | `reportes.view` |
| Reportes | Exportar | `reportes.export` |
| Lotes | Crear | `lotes.create` |
| Lotes | Leer | `lotes.view` |
| Lotes | Actualizar | `lotes.update` |
| Lotes | Eliminar | `lotes.delete` |

## Roles base

| Rol | Permisos |
|---|---|
| admin | `*` (todos) |
| operador | envíos, vehículos, transportistas, direcciones, reportes (lectura/creación operativa) |
| agricultor | reportes y lectura de lotes |

## Comandos útiles

```bash
php artisan db:seed --class=RolePermissionSeeder --force
php artisan route:list --path=envios -vv
php artisan test --filter=OrgTrackAccessTest
```

