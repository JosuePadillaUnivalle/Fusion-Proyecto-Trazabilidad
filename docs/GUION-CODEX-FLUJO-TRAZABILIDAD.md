# PROMPT COMPLETO PARA CODEX — AgroFusion en Railway

Copia TODO lo que está entre `<<<PROMPT` y `PROMPT>>>` y pégalo en Codex.

<<<PROMPT
# CONTEXTO COMPLETO: AgroFusion (producción en Railway)

Eres un agente que debe trabajar SOBRE LA APP YA DESPLEGADA EN RAILWAY.
NO asumas que el trabajo termina en un repo local. La fuente de verdad operativa es Railway + su Postgres.

---

## 1. ¿Qué es AgroFusion?

AgroFusion (también conocido históricamente como AgroNexus / Fusion-Proyecto-Trazabilidad) es un **sistema de gestión agrícola + trazabilidad agroalimentaria de punta a punta** para Bolivia (timezone `America/La_Paz`).

Cadena de valor que modela:

```
CAMPO (lote, siembra, insumos, cosecha, certificación)
   → ENVÍO agrícola a PLANTA (transportista + vehículo + ruta con GPS)
   → PLANTA (recepción, procesos/máquinas, producto terminado, empaque)
   → TRASLADO planta → MAYORISTA (ruta con paradas lat/lng)
   → MAYORISTA (stock, acepta pedidos del minorista)
   → DISTRIBUCIÓN mayorista → PDV / minorista (ruta con GPS)
   → PUNTO DE VENTA (inventario)
   → QR PÚBLICO de trazabilidad completa (sin login)
```

Propósito del QR: un consumidor/auditor escanea el código del producto en tienda y ve el historial completo del producto (campo → planta → mayorista → distribución → tienda), incluyendo **mapa interactivo de la ruta** en cada envío.

---

## 2. URL y entorno (OBLIGATORIO)

| Recurso | Valor |
|---------|--------|
| **App web producción** | https://agrofusion-production-ef8c.up.railway.app |
| Login | https://agrofusion-production-ef8c.up.railway.app/login |
| QR público (patrón) | https://agrofusion-production-ef8c.up.railway.app/trazabilidad/{codigo} |
| QR demo existente | https://agrofusion-production-ef8c.up.railway.app/trazabilidad/TRZ-PDV-ZANAHORIA-202601 |
| Blockchain BaaS (opcional) | https://agrofusion-baas-production-064b.up.railway.app |
| Railway proyecto | AgroFusion (workspace zetacoreofficial) |
| Railway service app | `agrofusion` |
| BD producción | PostgreSQL en Railway (NO SQLite) |
| Repo GitHub (referencia) | https://github.com/ZetaCoreOfficial/AgroFusion |

Si usas CLI Railway:
```bash
railway link -p 41f1ff77-2517-46f5-9a72-8c1c1f97991f -e production -s agrofusion
railway run --service agrofusion php artisan ...
```

---

## 3. Stack técnico

- Backend: PHP 8.2+ / Laravel
- Auth web: sesión Laravel; API: Sanctum
- Permisos: Spatie roles/permissions
- Front: Blade + Bootstrap/AdminLTE-ish + Leaflet para mapas
- Mapas de ruta: Leaflet + OSRM (`public/js/ruta-por-calles.js`)
- Deploy: Dockerfile en Railway
- Modelos Eloquent con **PK custom** (ej. `loteid`, `usuarioid`, `almacenid`) y tablas en español (`lote`, `usuario`, `insumo`, …)
- Muchos modelos **sin** `created_at/updated_at`; usan fechas de dominio (`fecharegistro`, `fechapedido`, etc.)

---

## 4. Roles del sistema (Spatie)

| Rol | Qué hace |
|-----|----------|
| `admin` | Todo |
| `agricultor` / `jefe_agricultor` | Lotes, actividades, cosecha, certificaciones |
| `planta` / `jefe_planta` | Recepción, procesos, producto terminado, traslado a mayorista |
| `mayorista` / `jefe_mayorista` | Stock mayorista, aceptar pedidos PDV, rutas a PDV |
| `minorista` | Puntos de venta, pedidos a mayorista, inventario PDV + QR |
| `transportista` | Ejecutar/cerrar rutas (carga, salida, llegada, firmas) |

### Cuentas demo en Railway

| Rol | Email | Password |
|-----|-------|----------|
| Admin | admin@agrofusion.com | 12345 |
| Agricultor | agricultor@agrofusion.com | 12345 |
| Planta | planta@agrofusion.com | 12345 |
| Transportista | transportista@agrofusion.com | 12345 |
| Mayorista | Mayorista@gmail.com | password |
| Minorista | minorista@agrofusion.com | Minorista2026 |

---

## 5. Dominio / modelos clave

### Campo
- `Lote` (plot) — núcleo agrícola; `codigo_trazabilidad`
- `Cultivo`, `Actividad`, `TipoActividad`
- `Produccion` (cosecha), `LoteInsumo`, `Insumo`
- `CertificacionLote` (puede anclarse a blockchain BaaS)

### Almacenes (ámbitos)
`AlmacenAmbito`: `agricola` | `planta` | `mayorista` | `punto_venta`
- Movimientos: `AlmacenMovimiento`

### Logística agrícola → planta
- `Pedido` (pedido de materia prima a planta)
- `EnvioAsignacionMultiple` (envío/asignación)
- Rutas multi-parada / coords vía `EnvioPedidoService::paradasMapaEnvio`

### Planta
- `PlantillaTransformacion`, `ProcesoPlanta`, `MaquinaPlanta`
- `LoteProduccion*` / registros de proceso
- Producto terminado como `Insumo` en almacén de planta

### Planta → mayorista
- `RutaDistribucion` con `almacen_planta_origenid` + `almacen_mayorista_destinoid`
- `DetalleTrasladoPlantaMayorista`
- Paradas con `latitud`/`longitud` (`DistribucionRutaService::paradasMapa`)

### Mayorista → PDV
- `PuntoVenta` (tiene lat/lng)
- `PedidoDistribucion` + `DetallePedidoDistribucion`
- `RutaDistribucion` de distribución al PDV
- Insumo en almacén del PDV con `codigo_trazabilidad` tipo `TRZ-PDV-...`

### QR público
- Ruta: `GET /trazabilidad/{codigo}` (sin auth)
- Servicio: `TrazabilidadProductoPdvService`
- Vista: `resources/views/trazabilidad/publica.blade.php`
- En eventos de envío adjunta `mapa_ruta` (puntos lat/lng) y muestra botón **“Ver ruta en mapa”**

---

## 6. Flujo operativo (UI / negocio)

1. **Agricultor**: crea lote → actividades → cosecha → (certificación) → stock en almacén agrícola.
2. **Pedido/envío a planta**: se crea pedido + asignación de transportista/vehículo con origen/destino GPS.
3. **Transportista / cierre agrícola**: confirma carga → empieza ruta → llega a planta → recepción.
4. **Planta**: recibe MP → corre plantilla/procesos → genera producto terminado.
5. **Traslado a mayorista**: ruta planta→mayorista con paradas GPS → salida → llegada → aprobación ingreso.
6. **Minorista**: crea pedido de distribución al mayorista.
7. **Mayorista**: acepta → arma ruta a PDV → envía.
8. **PDV**: confirma recepción → el producto queda en inventario con `codigo_trazabilidad`.
9. **Público**: abre `/trazabilidad/{codigo}` y ve timeline + mapas de cada envío.

Referencia de seeder ya existente (NO lo sobrescribas a menos que sea necesario):
- Clase: `FlujoCompletoTrazabilidadQrSeeder`
- QR demo: `TRZ-PDV-ZANAHORIA-202601`
- Marca interna: `[AGROFUSION-TRAZ]`

---

## 7. TU TAREA AHORA (insertar datos NUEVOS en Railway)

Inserta un flujo inventado COMPLETO en la Postgres de Railway para un producto nuevo.

### Datos inventados OBLIGATORIOS
- Cultivo: Papa Huaycha
- Lote: Lote Papa Sacaba Norte
- Código lote: TRAZ-PAP-SAC-2026-001
- Cosecha: 320 kg
- Producto terminado: Papa Huaycha lavada
- Presentación PDV: Papa Huaycha lavada · Bolsa 2 kg
- **Código QR PDV: TRZ-PDV-PAPA-HUAYCHA-202609**
- Pedido agrícola: PED-PAP-2026-001
- Envío agrícola: ENV-PAP-2026-001
- Ruta planta→mayorista: RUT-PM-PAP-001
- Pedido PDV: PDV-PAP-2026-001

### Coordenadas (para que el mapa trace calles)
- Origen agrícola: lat -17.3985, lng -66.0402 (Almacén agrícola Sacaba)
- Planta: lat -17.3935, lng -66.1570 (Planta AgroFusion)
- Mayorista: lat -17.4140, lng -66.1655 (Centro mayorista)
- PDV: lat -17.3742, lng -66.1596 (Minimarket Los Olivos)

### Cómo insertar (elige la que funcione en Railway)
A) `railway run --service agrofusion php artisan db:seed --class=...` (seeder idempotente nuevo, basado en `FlujoCompletoTrazabilidadQrSeeder`, con marca propia tipo `[AGROFUSION-TRAZ-PAPA]`)
B) UI en https://agrofusion-production-ef8c.up.railway.app con las cuentas demo
C) `railway run --service agrofusion php artisan tinker` insertando por modelos/servicios

Reglas:
- Todo debe quedar en Railway (Postgres). Nada de “quedó en local”.
- No borres el demo de zanahoria (`TRZ-PDV-ZANAHORIA-202601`) ni datos ajenos.
- Si rehaces el seed, limpia SOLO registros de Papa Huaycha (marca propia).
- Respeta PKs custom, `$fillable`, fechas de dominio.
- Cada tramo de envío debe tener ≥2 puntos lat/lng para el botón de mapa.

### Criterio de hecho
Abrir (sin login):
https://agrofusion-production-ef8c.up.railway.app/trazabilidad/TRZ-PDV-PAPA-HUAYCHA-202609

Debe mostrar:
- Timeline: campo → planta → mayorista → distribución → tienda
- En eventos de envío: botón **Ver ruta en mapa**
- Stock PDV > 0

### Entrega final (obligatoria)
Responde SOLO con:
1. URL completa del QR
2. Resumen corto de lo insertado en Railway
3. Confirmación de que el mapa aparece en los envíos
PROMPT>>>
