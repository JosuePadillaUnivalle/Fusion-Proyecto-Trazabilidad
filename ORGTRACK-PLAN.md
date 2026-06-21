# Plan de integración OrgTrack (resumen)

Objetivo: integrar las funcionalidades del proyecto `ORGTRACK` dentro del proyecto principal para: gestión/seguimiento de envíos, transportistas, vehículos, direcciones, reportes de distribución, mejorar asignaciones múltiples e incidentes.

Prioridad y pasos propuestos (iterativos):

1) Preparación
- Ignorar carpeta `ORGTRACK - IDEA/` en `.gitignore` (ya aplicado).
- Revisar y mapear rutas y modelos existentes en `ORGTRACK - IDEA/logistica-produccion-planta/logistica-produccion-planta`.
- Crear branch de trabajo (opcional).

2) Diseño API/Integración
- Añadir un `OrgTrackService` en `app/Services/OrgTrackService.php` que encapsule llamadas HTTP a la API externa (o al código local del subproyecto).
- Proveer fallback local (ya existe `LocalOrgTrackFallback` en el proyecto principal); integrarlo si la API externa falla.

3) Rutas y controladores
- Crear controladores web dentro de `app/Http/Controllers/Web/OrgTrack/`:
  - `EnvioController.php` (index, show, create, store)
  - `TransportistaController.php` (index, store-batch)
  - `VehiculoController.php` (index, store-batch)
  - `DireccionController.php` (index, store-batch)
  - `ReporteEnviosController.php` (reportes distribución)
- Añadir rutas `routes/web.php` bajo el prefijo `orgtrack` o integrar en `envios` ya existente con middleware adecuado.

4) Vistas y UI
- Reutilizar vistas Blade del subproyecto cuando sea posible; adaptar estilos al layout principal.
- Implementar paginación y mensajes de carga.

5) Asignación múltiple (UX)
- Crear wizard en 3 pasos:
  1. Seleccionar transportista (dropdown) y vehículo (dropdown dependiente)
  2. Seleccionar envíos (tabla con checkboxes) y añadir productos (campos dinámicos)
  3. Revisar resumen y confirmar asignación
- Backend: endpoint que reciba transportista_id, vehiculo_id y lista de pedidos/envíos con cantidades y cree `RutaMultiEntrega` y `EnvioAsignacionMultiple`.

6) Incidentes
- Crear formulario `IncidenteController@create` con dropdown para tipos (cargar desde catálogo); si "Otro" mostrar input adicional.
- Vincular incidente a `externo_envio_id` o `pedidoid`.
- Validaciones y tests.

7) Seeders y pruebas
- Crear seeders demo para transportistas/vehículos/direcciones/envíos (ya existen algunos en `database/seeders` del subproyecto).
- Pruebas manuales y automatizadas (Feature tests) para los flujos críticos.

8) Reportes avanzados
- Implementar `ReporteEnviosController` que agregue métricas (conteo por estado, tiempos, viajes por transportista) y endpoints CSV/Excel.
- Añadir vistas con gráficos (Chart.js) y filtros por fecha/almacén/transportista.

9) QA y despliegue
- Ejecutar pruebas, revisar logs, ajustar timeouts para integraciones externas.

Archivos iniciales a crear (primer sprint):
- `app/Services/OrgTrackService.php`
- `app/Http/Controllers/Web/OrgTrack/EnvioController.php`
- `app/Http/Controllers/Web/OrgTrack/TransportistaController.php`
- Blade views básicos en `resources/views/orgtrack/*`
- Seeders demo si faltan

Siguientes pasos que puedo ejecutar ahora si confirmas:
- Crear el `OrgTrackService` y un controller `EnvioController` con `index` y `show` que use `LocalOrgTrackFallback` para mostrar datos demo.
- Scaffold rutas y vistas básicas para `envios` integradas en el layout.
- Escribir pruebas básicas Feature para `envios.index`.

Confirma qué paso quieres que ejecute primero y lo implemento.
