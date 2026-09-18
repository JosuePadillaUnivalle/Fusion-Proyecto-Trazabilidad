# Prompt Codex — insertar datos inventados EN RAILWAY

Copia **solo el bloque** entre `<<<PROMPT` y `PROMPT>>>` y pégalo en Codex.

<<<PROMPT
NO trabajes en el repo local. Tu entorno de verdad es la app YA DESPLEGADA en Railway.

## URL base (obligatoria)
https://agrofusion-production-ef8c.up.railway.app

Si necesitas CLI de Railway:
- Proyecto: AgroFusion
- Service: agrofusion
- Cuenta: zetacoreofficial / Zplir1501
- Comando típico para seed/artisan en el contenedor:
  railway run --service agrofusion php artisan …
  o railway ssh / railway shell según lo disponible.

## Objetivo
INSERTAR datos inventados de un producto nuevo de punta a punta DENTRO de Railway, para que el QR público quede usable con mapa de ruta en cada envío.

Al final debes darme:
1. el codigo_trazabilidad del producto en PDV
2. la URL completa del QR (https://agrofusion-production-ef8c.up.railway.app/trazabilidad/{codigo})
3. confirmación de que en la timeline hay envíos con “Ver ruta en mapa”

## Cómo insertar (elige la vía que funcione en Railway)
Opción A (preferida si hay CLI): crear/ejecutar un seeder idempotente vía
`railway run --service agrofusion php artisan db:seed --class=...`
Opción B: cargar el flujo usando la UI de la app en la URL de Railway (login con cuentas demo).
Opción C: `php artisan tinker` dentro del servicio Railway.

NO digas “listo en local”. Todo debe quedar persistido en la Postgres de Railway.

## Datos inventados OBLIGATORIOS
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
- Coordenadas (para mapa):
  - Origen agrícola: -17.3985, -66.0402 (Almacén agrícola Sacaba)
  - Planta: -17.3935, -66.1570 (Planta AgroFusion)
  - Mayorista: -17.4140, -66.1655 (Centro mayorista)
  - PDV: -17.3742, -66.1596 (Minimarket Los Olivos)

## Flujo que debe quedar en Railway
1. Lote + actividades + cosecha (+ certificación conforme si aplica)
2. Envío agrícola → planta (con coords/paradas)
3. Recepción + proceso en planta + producto terminado
4. Traslado planta → mayorista (ruta con lat/lng) completado
5. Stock mayorista
6. Pedido + ruta mayorista → PDV (con lat/lng) + recepción
7. Insumo PDV con codigo_trazabilidad = TRZ-PDV-PAPA-HUAYCHA-202609 y stock > 0

## Cuentas demo (login en la URL de Railway)
- agricultor@agrofusion.com / 12345
- planta@agrofusion.com / 12345
- transportista@agrofusion.com / 12345
- Mayorista@gmail.com / password
- minorista@agrofusion.com / Minorista2026
- admin@agrofusion.com / 12345

## Verificación final (obligatoria)
Abrí en navegador:
https://agrofusion-production-ef8c.up.railway.app/trazabilidad/TRZ-PDV-PAPA-HUAYCHA-202609

Debe mostrar timeline completa (campo → planta → mayorista → distribución → tienda) y botón “Ver ruta en mapa” en los envíos.

Si el código no existe aún, créalo con ese valor exacto.
Devuélveme al final SOLO:
- URL del QR
- resumen corto de lo insertado en Railway
PROMPT>>>
