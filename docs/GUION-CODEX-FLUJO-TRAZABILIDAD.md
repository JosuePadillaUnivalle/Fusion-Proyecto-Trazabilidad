# Prompt Codex — insertar datos inventados (flujo completo QR)

Copia **solo el bloque** entre las líneas `<<<PROMPT` y `PROMPT>>>` y pégalo en Codex.

<<<PROMPT
Trabaja en el repo AgroFusion (Laravel + PostgreSQL). Tu objetivo es INSERTAR datos inventados de un producto nuevo de punta a punta, para que exista un QR público de trazabilidad completa con mapas de ruta en cada envío.

## Qué debes hacer
1. NO inventes pantallas nuevas. Usa el código y modelos existentes.
2. Prefiere crear/adaptar un seeder idempotente (basado en `database/seeders/FlujoCompletoTrazabilidadQrSeeder.php`) O insertar vía `php artisan tinker` / servicios existentes.
3. Al terminar, imprime:
   - el `codigo_trazabilidad` del insumo PDV
   - la URL pública `/trazabilidad/{codigo}`
   - confirmación de que hay eventos de envío con coordenadas (`mapa_ruta` / paradas con lat-lng)

## Datos inventados OBLIGATORIOS (usa exactamente estos nombres)
- Cultivo: Papa Huaycha
- Lote agrícola: Lote Papa Sacaba Norte
- Código lote: TRAZ-PAP-SAC-2026-001
- Cosecha: 320 kg
- Producto terminado planta: Papa Huaycha lavada
- Presentación PDV: Papa Huaycha lavada · Bolsa 2 kg
- Código QR PDV: TRZ-PDV-PAPA-HUAYCHA-202609
- Pedido agrícola: PED-PAP-2026-001
- Envío agrícola: ENV-PAP-2026-001
- Ruta planta→mayorista: RUT-PM-PAP-001
- Pedido PDV: PDV-PAP-2026-001
- Coordenadas (Cochabamba / Sacaba) para que el mapa trace:
  - Origen agrícola: lat -17.3985, lng -66.0402  (etiqueta: Almacén agrícola Sacaba)
  - Planta: lat -17.3935, lng -66.1570  (etiqueta: Planta AgroFusion)
  - Mayorista: lat -17.4140, lng -66.1655  (etiqueta: Centro mayorista)
  - PDV: lat -17.3742, lng -66.1596  (etiqueta: Minimarket Los Olivos)

## Flujo de datos que debes dejar persistido
1. Lote agrícola + actividades (siembra/riego/cosecha) + certificación conforme si el modelo lo permite.
2. Pedido/envío agrícola → planta con transportista/vehículo y paradas/coords (para mapa).
3. Recepción en planta + procesamiento (plantilla o registros de proceso) + stock producto terminado.
4. Traslado planta → mayorista (RutaDistribucion con paradas lat/lng) completado/aprobado.
5. Stock en mayorista.
6. Pedido distribución minorista/PDV + ruta mayorista→PDV con paradas lat/lng + recepción en PDV.
7. Insumo en almacén del PDV con `codigo_trazabilidad = TRZ-PDV-PAPA-HUAYCHA-202609` y stock > 0.

## Usuarios/roles a reutilizar (no crear otros si ya existen)
- agricultor@agrofusion.com / 12345
- planta@agrofusion.com / 12345
- transportista@agrofusion.com / 12345
- Mayorista@gmail.com / password
- minorista@agrofusion.com / Minorista2026
- admin@agrofusion.com / 12345

## Reglas del proyecto
- PKs custom (`loteid`, `usuarioid`, etc.), tablas snake_case en español.
- Muchos modelos sin timestamps Laravel; usa `fecharegistro` / fechas de dominio.
- Respeta `$fillable` y relaciones existentes (EnvioAsignacionMultiple, RutaDistribucion, PedidoDistribucion, Insumo PDV).
- No borres datos ajenos al demo de Papa Huaycha; limpia solo registros marcados de este flujo si rehaces el seed.
- No toques `.env`, secretos ni `database.sqlite` con datos sensibles.

## Cómo ejecutar
Si creas seeder, por ejemplo:
`php artisan db:seed --class=FlujoPapaHuaychaTrazabilidadQrSeeder`

Si estás en Railway/producción, ejecuta el seed allí o genera un comando artisan y documenta el comando exacto.

## Criterio de hecho
- Existe `/trazabilidad/TRZ-PDV-PAPA-HUAYCHA-202609` con timeline: campo → planta → mayorista → distribución → tienda.
- Hay al menos 3 tramos de envío y cada uno con puntos lat/lng suficientes para el botón “Ver ruta en mapa”.
- Devuélveme al final el código QR y la URL completa lista para abrir.
PROMPT>>>
