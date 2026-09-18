# Guion Codex — flujo completo con datos inventados (AgroFusion)

Copia este prompt a Codex. Objetivo: cargar un producto inventado de punta a punta y verificar el QR público con mapa de ruta en cada envío.

## Contexto técnico

- App Laravel AgroFusion (trazabilidad agrícola → planta → mayorista → PDV).
- URL producción: `https://agrofusion-production-ef8c.up.railway.app`
- QR público: `/trazabilidad/{codigo}` (ej. seeder demo `TRZ-PDV-ZANAHORIA-202601`).
- En cada evento de envío del QR debe aparecer **Ver ruta en mapa** (Leaflet + OSRM).
- Cuentas demo típicas (password salvo indicación): `12345`
  - `agricultor@agrofusion.com`
  - `planta@agrofusion.com`
  - `transportista@agrofusion.com`
  - `Mayorista@gmail.com` / `password`
  - `minorista@agrofusion.com` / `Minorista2026`
  - `admin@agrofusion.com`

## Datos inventados a usar

| Campo | Valor |
|-------|--------|
| Cultivo | Papa Huaycha |
| Lote | Lote Papa Sacaba Norte |
| Cantidad cosecha | 320 kg |
| Producto terminado | Papa Huaycha lavada · Bolsa 2 kg |
| PDV destino | Minimarket Los Olivos (o el PDV del minorista demo) |
| Coordenadas | Usar puntos con lat/lng reales cerca de Cochabamba para que el mapa trace calles |

## Orden de trabajo (UI)

1. **Agricultor** — crear/abrir lote, registrar siembra, insumos, cosecha y (si aplica) certificación conforme.
2. **Pedido agrícola → planta** — crear pedido/envío con origen (almacén agrícola) y destino planta; asignar transportista y vehículo.
3. **Transportista / cierre agrícola** — confirmar carga, iniciar ruta, confirmar llegada a planta.
4. **Planta** — recepción de materia prima, proceso de transformación (plantilla), empaque, stock de producto terminado.
5. **Traslado planta → mayorista** — crear ruta de traslado con paradas (carga planta + entrega mayorista), salir, llegar, aprobar ingreso.
6. **Mayorista** — aceptar pedido del minorista, armar ruta de distribución al PDV, salir en ruta, entregar.
7. **Minorista / PDV** — confirmar recepción; localizar insumo PDV y su `codigo_trazabilidad`.
8. **Verificación QR** — abrir `/trazabilidad/{codigo}` sin login y comprobar:
   - Timeline completo (campo → planta → mayorista → distribución → tienda)
   - En eventos de envío: botón **Ver ruta en mapa**
   - Al abrir: markers origen/destino y trazo por calles (o línea recta si OSRM falla)

## Si prefieres seed en vez de UI

```bash
php artisan db:seed --class=FlujoCompletoTrazabilidadQrSeeder
```

Luego abrir: `/trazabilidad/TRZ-PDV-ZANAHORIA-202601`

## Criterio de hecho

- Hay al menos un envío agrícola→planta, uno planta→mayorista y uno mayorista→PDV en la timeline.
- Cada tramo con coordenadas muestra mapa interactivo abrible.
- El QR es público (sin autenticación).
