# Prompt Codex — imágenes REALES de máquinas e insumos (Railway)

Copia TODO entre `<<<PROMPT` y `PROMPT>>>` y pégalo en Codex.

<<<PROMPT
# CONTEXTO

Trabajas sobre AgroFusion YA DESPLEGADO en Railway (NO solo repo local).

- App: https://agrofusion-production-ef8c.up.railway.app
- Login: https://agrofusion-production-ef8c.up.railway.app/login
- QR Papa (ejemplo con máquinas): https://agrofusion-production-ef8c.up.railway.app/trazabilidad/TRZ-PDV-PAPA-HUAYCHA-202609
- QR Zanahoria demo: https://agrofusion-production-ef8c.up.railway.app/trazabilidad/TRZ-PDV-ZANAHORIA-202601
- Service Railway: `agrofusion` (Postgres en Railway)
- Repo referencia: https://github.com/ZetaCoreOfficial/AgroFusion

AgroFusion es trazabilidad agrícola Bolivia: campo → planta → mayorista → PDV → QR público.
En el QR, cada paso de TRANSFORMACIÓN muestra el equipo (`MaquinaPlanta`) y en campo aparecen insumos (`Insumo`) con foto.

## PROBLEMA ACTUAL (obligatorio corregir)

Las fotos actuales de máquinas/insumos NO tienen sentido:
- “Lavadora Industrial L-100” muestra una persona picando hierbas / cocina casera.
- “Secador Industrial SC-500” muestra un deshidratador doméstico pequeño.
- Otras máquinas/insumos también usan imágenes genéricas o irrelevantes.

El usuario necesita ver la imagen del EQUIPO REAL (industrial agrícola/planta) y de los INSUMOS REALES (fertilizante, semilla, producto, etc.).

## TU TAREA

1. Asigna imágenes REALES y COHERENTES a:
   - TODAS las filas de `maquina_planta` (campo `imagenurl`)
   - Los insumos visibles en trazabilidad / catálogo relevantes (`insumo.imagenurl`), al menos:
     - insumos agrícolas típicos (semillas, fertilizantes, agroquímicos)
     - productos terminados / presentaciones que salen en el QR

2. Las imágenes deben verse en:
   - QR público `/trazabilidad/{codigo}` (pasos TRANSFORMACIÓN → “Equipo de planta”)
   - Vistas de máquinas e insumos dentro de la app

3. Persistencia en Railway (Postgres). Nada de “quedó solo en local”.

## CRITERIOS DE IMAGEN (muy importante)

Cada imagen DEBE representar visualmente el objeto nombrado:

| Código / nombre | Qué debe verse |
|-----------------|----------------|
| L-100 Lavadora Industrial | Lavadora industrial de vegetales / tambor de lavado de raíces en planta |
| BC-20 Cinta / clasificadora | Cinta transportadora / línea de clasificación agrícola |
| SE-10 Selladora | Selladora de bandas / sellado de bolsas industrial |
| BD-500 Balanza | Balanza industrial / báscula de piso o checkweigher |
| MX-200 Mezcladora | Mezcladora industrial de alimentos |
| EX-300 Extrusora | Extrusora / procesadora industrial de alimentos |
| MD-400 Moldes / cortadora | Equipo de corte/moldeado industrial (no cortadores de galletas caseros) |
| SC-500 Secador Industrial | Secador/túnel de secado industrial de alimentos (NO deshidratador de cocina) |
| TR-600 Freidora | Freidora industrial continua |
| EV-700 Envasadora | Envasadora / línea de empaque al vacío o form-fill-seal |
| ET-800 Etiquetadora | Etiquetadora industrial automática de botellas/bolsas |

Para insumos:
- Fertilizante NPK → sacos/granulado fertilizante
- Semillas → empaque o semillas del cultivo
- Herbicida/insecticida → envase agroquímico (genérico, sin marca pirata)
- Producto terminado (papa/zanahoria envasada) → bolsa/producto realista

Prohibido:
- Fotos de cocina casera, personas picando, utensilios domésticos
- Memes, ilustraciones cartoon, logos genéricos
- Imágenes que no coincidan con el nombre del equipo/insumo

## CÓMO OBTENER LAS IMÁGENES

Puedes usar CUALQUIERA de estas vías (elige la más fiable):

A) Descargar imágenes libres de uso comercial / Wikimedia Commons / Unsplash / Pexels / Pixabay
   - Buscar términos en inglés: “industrial vegetable washing machine”, “food drying tunnel industrial”, “automatic labeling machine”, “vacuum packaging machine food”, “NPK fertilizer bags”, etc.
B) URLs HTTPS directas a imágenes públicas estables (CDN, commons upload, etc.)
C) Guardar archivos en el deploy:
   - Preferido durable: `public/images/maquinas/{codigo}.jpg` y `public/images/insumos/{slug}.jpg`
   - Luego poner en BD: `/images/maquinas/SC-500.jpg` (ruta pública servida por Laravel)

Si usas storage (`storage/app/public/...`), asegúrate de que el archivo exista EN Railway (volumen o embebido en imagen Docker). En Railway muchas veces falla storage efímero: **prioriza `public/images/...` o URL HTTPS absoluta**.

Actualiza:
- `maquina_planta.imagenurl`
- `insumo.imagenurl` (respetar `InsumoImagenCatalogo` si existe; puedes extenderlo)

Código útil ya existente:
- `App\Models\MaquinaPlanta::imagenSrc()`
- `App\Support\MaquinaImagenCatalogo` (REEMPLAZA sus URLs basura por URLs reales correctas, o deja de usarlo si ya guardas rutas buenas en BD)
- `App\Support\InsumoImagenCatalogo`
- Vista QR: `resources/views/trazabilidad/publica.blade.php` (evidencia tipo `maquina`)

## CÓMO EJECUTAR EN RAILWAY

```bash
railway link -p 41f1ff77-2517-46f5-9a72-8c1c1f97991f -e production -s agrofusion
# Si creas comando/seeder:
railway run --service agrofusion php artisan ...
# O railway up si cambias código + imágenes en public/
```

También puedes:
1. Subir imágenes a `public/images/...`
2. Deploy con `railway up`
3. Correr artisan/tinker para setear `imagenurl`
4. Verificar el QR

## VERIFICACIÓN OBLIGATORIA

Abre sin login:
https://agrofusion-production-ef8c.up.railway.app/trazabilidad/TRZ-PDV-PAPA-HUAYCHA-202609

En pasos de TRANSFORMACIÓN (Lavadora L-100, Secador SC-500, Envasadora EV-700, Etiquetadora ET-800, etc.):
- Debe verse foto del equipo industrial correcto
- Caption “Equipo de planta”

También revisa al menos un QR/insumo de campo donde salgan fertilizantes/semillas con foto coherente.

## ENTREGA FINAL

Responde con:
1. Lista código_máquina → URL o ruta de imagen usada (1 línea c/u)
2. Lista insumos clave actualizados
3. Confirmación de que el QR Papa ya muestra equipos reales
4. URL del QR verificado

NO digas “listo en local”. Todo debe quedar visible en Railway.
PROMPT>>>
