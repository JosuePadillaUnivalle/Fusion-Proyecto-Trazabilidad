# PROMPT CODEX — Restaurar datos faltantes en Railway SIN borrar lo existente

Copia TODO entre `<<<PROMPT` y `PROMPT>>>` y pégalo en Codex.

<<<PROMPT
# CONTEXTO OBLIGATORIO

Trabajas sobre AgroFusion YA DESPLEGADO en Railway. Fuente de verdad = Postgres de Railway + la app live.

- App: https://agrofusion-production-ef8c.up.railway.app
- Login: https://agrofusion-production-ef8c.up.railway.app/login
- QR Papa (NO TOCAR / NO BORRAR): https://agrofusion-production-ef8c.up.railway.app/trazabilidad/TRZ-PDV-PAPA-HUAYCHA-202609
- QR Zanahoria (NO TOCAR / NO BORRAR): https://agrofusion-production-ef8c.up.railway.app/trazabilidad/TRZ-PDV-ZANAHORIA-202601
- Railway project: AgroFusion | service: `agrofusion` | workspace zetacoreofficial
- Project id: `41f1ff77-2517-46f5-9a72-8c1c1f97991f`
- Repo: https://github.com/ZetaCoreOfficial/AgroFusion

Cuentas:
- admin@agrofusion.com / 12345
- planta@agrofusion.com / 12345
- Mayorista@gmail.com / password
- minorista@agrofusion.com / Minorista2026

## REGLA DE ORO (si la rompes, fallaste)

1. **NO borres** datos existentes del usuario (lotes, envíos, QR Papa/Zanahoria, stock real, usuarios, rutas, certificaciones).
2. **NO** ejecutes `migrate:fresh`, `db:wipe`, `truncate` masivo, ni seeders con `limpiarDemoAnterior()` agresivo sobre datos ajenos.
3. Usa solo `updateOrCreate` / `firstOrCreate` / upserts acotados.
4. Si un seeder borra por marca (`[AGROFUSION-TRAZ]`, `[AGROFUSION-TRAZ-PAPA]`, etc.), úsalo SOLO sobre esa marca; nunca limpies tablas enteras.
5. Al terminar, los QR Papa y Zanahoria deben seguir respondiendo 200 con timeline completa.
6. Todo debe quedar visible en Railway (no “listo en local”).

## PROBLEMA A CORREGIR (8 puntos)

### 1) API key de clima rota / vacía
- Variables usadas por el código: `WEATHER_API_KEY` y/o `OPENWEATHER_API_KEY` (`config/services.php` → `services.weather.key`).
- También leen `env('OPENWEATHER_API_KEY')` DashboardController y GuardarClimaCommand.
- Acción:
  a) Verificar en Railway variables del service `agrofusion` si están vacías.
  b) Setear AMBAS con una API key válida de OpenWeather (si el usuario no te da una, pide una o usa la que ya esté en `.env.example`/historial; NO inventes una falsa y digas “ok”).
  c) Comando: `railway variables --service agrofusion set WEATHER_API_KEY=... OPENWEATHER_API_KEY=...`
  d) Probar: `GET /api/clima` o pantalla Climas / panel agrícola debe dejar de fallar por “sin key”.
  e) Opcional: `php artisan clima:guardar` vía `railway run`.

### 2) Documentos de entrega vacíos
La lista `/logistica/documentos` (o ruta equivalente de documentos de entrega) está vacía.
Restaura documentos con `DocumentoEntrega::updateOrCreate` (por `titulo`), SIN borrar los que ya existan.

Tipos válidos (catálogo): `guia_transporte`, `guia_entrega`, `nota_entrega`, `confirmacion_entrega`, `acta_salida`, `acuse_entrega`, `pod_foto`, etc.

Mínimo a insertar/restaurar (usa envíos reales de Railway si existen; si no, crea docs ligados a ENV-PAP / ENV-ZAN / PED conocidos):

| titulo | tipo_documento |
|--------|----------------|
| Nota entrega almacén central | nota_entrega |
| Nota entrega planta de procesamiento | nota_entrega |
| Guía de transporte Carlos Mamani | guia_entrega |
| Confirmación de entrega cliente norte | confirmacion_entrega |
| Guía de entrega ENV-01 — Carlos Mamani | guia_entrega |
| Confirmación de entrega ENV-03 — Carlos Mamani | confirmacion_entrega |
| Guía de transporte ENV-MOD-26-01 | guia_entrega |
| Nota de entrega ENV-MOD-26-03 | nota_entrega |
| Confirmación de entrega ENV-MOD-26-04 | confirmacion_entrega |

Para cada uno:
- `usuarioid` = admin o transportista existente
- `archivo_path`: genera PDFs reales con DomPDF (o `MaterializarPdfsDocumentoEntregaCommand`) y guárdalos en `storage/app/public/documentos_entrega/...` **en Railway**, o materializa vía `DocumentoEntregaArchivo::materializarPdfDocumento`.
- NO dejes paths fantasma `demo/mod-log/*.pdf` que no existen (eso deja la UI “vacía” o rota al abrir).
- Preferible: comando artisan `agrofusion:materializar-pdfs-documento-entrega` si existe; si no, créalos y materialízalos.

Referencias de seeders (solo como guía de contenido, NO correras módulos enteros destructivos):
- `LogisticaOperativaModuloSeeder::seedDocumentosOperativos`
- `EnviosDistribucionModuloSeeder::seedDocumentosEIncidentes`
- `PanelesPorRolModuloSeeder` (docs panel)

### 3) Vehículos: deben existir 3 por categoría/ámbito
Seeder idempotente: `FlotaVehiculosPorAmbitoSeeder`.

Requisito exacto — 3 vehículos por `ambito_flota`:

**agrícola**
- SCZ-MOD-01 Volvo FH (CAMION_GR)
- SCZ-MOD-02 Toyota Hilux (CAMIONETA)
- SCZ-MOD-03 Mercedes Atego (CAMION_PQ)

**planta**
- SCZ-PLT-01 Volvo FM (CAMION_GR)
- SCZ-PLT-02 Mercedes Accelo (CAMION_PQ)
- SCZ-PLT-03 Toyota Hilux (CAMIONETA)

**mayorista**
- SCZ-MAY-01 Iveco Daily (CAMION_GR)
- SCZ-MAY-02 Mercedes Atego (CAMION_PQ)
- SCZ-MAY-03 Nissan Frontier (CAMIONETA)

Antes: asegurar catálogo `tipo_vehiculo` (CAMIONETA, CAMION_PQ, CAMION_GR) con `LogisticaCatalogosVerdurasSeeder` si faltan tipos.
Ejecutar: `php artisan db:seed --class=FlotaVehiculosPorAmbitoSeeder --force`
Ese seeder solo borra placas obsoletas SCZ-*-04; NO toques otras placas del usuario.
Verificar en UI de vehículos: 9 unidades (3×3).

### 4) Tipos de empaque: completar medidas
Hoy tienen nombre pero faltan: largo, ancho, alto, tara, capacidad, unidades por pallet.

Fuente de verdad: `App\Support\EmpaquePlantaCatalogo::TIPOS_PREDEFINIDOS` + método que sincroniza a `tipo_empaque`.

Valores obligatorios:

| slug | nombre | largo_cm | ancho_cm | alto_cm | tara_kg | capacidad_unidades | unidades_por_pallet |
|------|--------|----------|----------|---------|---------|--------------------|---------------------|
| lata | Lata | 8 | 8 | 11 | 0.045 | 1 | 120 |
| frasco | Frasco | 7 | 7 | 12 | 0.18 | 1 | 96 |
| bidon | Bidón | 30 | 30 | 40 | 1.2 | 1 | 36 |
| pouch | Pouch | 12 | 8 | 18 | 0.025 | 1 | 200 |
| bolsa | Bolsa plástica | 25 | 15 | 8 | 0.05 | 1 | 80 |

Acción: llamar a la sincronización del catálogo (`EmpaquePlantaCatalogo` asegurar/sync si existe) o `updateOrCreate` por slug/nombre.
NO borres tipos de empaque personalizados del usuario; solo completa/actualiza estos 5.

### 5) Procesos / plantillas de transformación = 0
Restaurar con:
```bash
php artisan db:seed --class=MaquinasProcesoPlantaSeeder --force
php artisan db:seed --class=PlantillasTransformacionSeeder --force
php artisan db:seed --class=PlantillaPasoVariableSeeder --force
# o todo junto:
php artisan agrofusion:asegurar-datos-demo
```
OJO: `agrofusion:asegurar-datos-demo` también toca stock PDV/planta; está pensado para restaurar sin wipe, pero **verifica** que no borre QR Papa/Zanahoria. Si dudas, ejecuta solo los seeders de plantillas/máquinas.

Plantillas que DEBEN existir (nombres exactos):
- Puré de papa
- Papa frita congelada
- Papas chips
- Zanahoria en conserva
- Puré de zanahoria
- Salsa de tomate
- Cebolla en cubos IQF
- Cebolla deshidratada en polvo
- Mix vegetal ensalada
- Harina precocida
- Snack extruido
- Galleta o masa moldeada
- Jugo pasteurizado

(≥10–13 plantillas con pasos y máquinas L-100, BC-20, SC-500, EV-700, ET-800, etc.)
UI: `/plantillas-transformacion` debe listarlas.

### 6) Materia prima para procesar lote = 0
Seeder: `PlantaInsumosOperativosSeeder` (idempotente por nombre+almacenid).

Debe dejar stock > 0 en almacén de planta, entre otros:
- Papa industrial Monalisa (2740 kg)
- Papa rubíola granel
- Zanahoria fresca Imperator (1920 kg)
- Cebolla blanca/colorada granel
- Tomate pera granel
- Lechuga crespa, Repollo, Mandioca, Maíz
- Naranja Valencia, Mango Tommy
- Aceite vegetal, Harina, Azúcar, Sal, Vinagre, Agua tratada

Además, si aplica al flujo Papa Huaycha, asegurar que exista materia prima “Papa Huaycha” / cosecha vinculada sin pisar stock existente (sumar o updateOrCreate, no poner stock=0).

### 7) Productos en minorista / PDV + integridad del flujo QR
Hoy el minorista ve 0 productos, pero el QR de trazabilidad completa ya debería existir.

Acciones:
a) Verificar que existan insumos PDV con stock > 0 y `codigo_trazabilidad`:
   - `TRZ-PDV-PAPA-HUAYCHA-202609` → Papa Huaycha lavada · Bolsa 2 kg
   - `TRZ-PDV-ZANAHORIA-202601` → Zanahoria Imperator envasada · Bolsa 1 kg
b) Si faltan en inventario del punto de venta del minorista, restaurar con seeders NO destructivos:
   - `PdvInventarioDemoSeeder` (solo PDVs sin stock)
   - `PdvMercadoAlvaroCompletoSeeder` (CUIDADO: tiene `limpiarDemoAnterior` solo de su marca `[DEMO-PDV-ALVARO-COMPLETO]` / pedido `PDV-20260623-DEMO` — OK si no toca Papa)
   - `ProductosTerminadosPlantaMayoristaSeeder`
   - Si hace falta: `FlujoPapaHuaychaRailwaySeeder` / `FlujoCompletoTrazabilidadQrSeeder` SOLO si son idempotentes y NO borran otros datos; preferir completar stock PDV con updateOrCreate del insumo del almacén del PDV.
c) Login como `minorista@agrofusion.com` y confirmar inventario visible.
d) Abrir ambos QR públicos y confirmar timeline + mapas + equipos.

### 8) Centro de reportes da error
URL típica: `/reportes` (`ReporteCentroController@index`).
Al indexear, llama previews: `enviosEstadoPreview`, `stockAmbitoPreview`, `transportistasPreview`, `trasladosPreview`, `pedidosPdvPreview`, `productosTerminadosPreview` en `ReporteCentroService`.

Acción:
1. Reproducir el error (logs Railway + respuesta HTTP).
2. Arreglar la causa real (SQL, columna inexistente, null en preview, permiso `reportes.view`, mismatch `fecha_fin` vs `fecha_hasta`, etc.).
3. Envolver previews en try/catch para que un preview roto no tumbe TODO el centro (si hoy un fallo en un preview rompe la página).
4. Verificar con admin que `/reportes` carga 200 y cada reporte hijo abre.

## ORDEN DE EJECUCIÓN RECOMENDADO

1. Diagnóstico rápido (counts) en Railway:
   - vehiculos por ambito_flota
   - tipo_empaque con medidas null
   - plantilla_transformacion count
   - insumos planta stock
   - insumos PDV stock + codigos QR
   - documento_entrega count
   - variables clima
2. Clima (vars Railway)
3. Empaques (sync medidas)
4. Vehículos (FlotaVehiculosPorAmbitoSeeder)
5. Máquinas + Plantillas transformación
6. Materia prima planta
7. Documentos de entrega + PDFs reales
8. Inventario minorista / QR integrity
9. Fix Centro de reportes
10. Smoke test UI

Comandos útiles:
```bash
railway link -p 41f1ff77-2517-46f5-9a72-8c1c1f97991f -e production -s agrofusion
railway variables --service agrofusion
railway run --service agrofusion php artisan db:seed --class=FlotaVehiculosPorAmbitoSeeder --force
railway run --service agrofusion php artisan db:seed --class=PlantillasTransformacionSeeder --force
railway run --service agrofusion php artisan db:seed --class=PlantaInsumosOperativosSeeder --force
railway run --service agrofusion php artisan agrofusion:asegurar-datos-demo
railway up --service agrofusion   # solo si cambias código (reportes)
```

## CRITERIOS DE HECHO (checklist — todos deben pasar)

- [ ] Clima: key presente en Railway y `/api/clima` o vista clima responde sin error de API key
- [ ] Documentos de entrega: ≥ 6 documentos listables; al abrir, PDF/preview no 404
- [ ] Vehículos: 3 agrícola + 3 planta + 3 mayorista (placas SCZ-MOD/PLT/MAY 01..03)
- [ ] Tipos empaque: Lata/Frasco/Bidón/Pouch/Bolsa con largo, ancho, alto, tara, capacidad, unidades_por_pallet
- [ ] Plantillas transformación: ≥ 10 listadas en UI
- [ ] Materia prima planta: ≥ 10 insumos con stock > 0
- [ ] Minorista: productos visibles; QR Papa y Zanahoria 200 con timeline
- [ ] `/reportes` HTTP 200 sin exception
- [ ] Ningún dato previo del usuario eliminado (especialmente flujos QR)

## ENTREGA FINAL (formato obligatorio)

1. Tabla punto → qué hiciste → evidencia (count o URL)
2. URLs verificadas (reportes, documentos, vehículos, plantillas, QR Papa, QR Zanahoria)
3. Confirmación explícita: “No se borraron datos existentes”
4. Si algo no se pudo (ej. falta API key real de OpenWeather), dilo claro y qué falta del usuario

NO digas “listo en local”. Todo en Railway.
PROMPT>>>
