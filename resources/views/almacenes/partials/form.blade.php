@php
    $campos = $guias['campos'] ?? [];
    $esEdicion = isset($almacen);
    $ubicacionRaw = old('ubicacion', $almacen->ubicacion ?? '');
    $ubicacionValor = preg_replace('/\s*·\s*GPS\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*$/iu', '', trim((string) $ubicacionRaw));
    $ubicacionValor = \App\Support\UbicacionGpsParser::direccionLegible($ubicacionValor) ?? $ubicacionValor;
    $nombreValor = old('nombre', $almacen->nombre ?? '');
    $descValor = old('descripcion', $almacen->descripcion ?? '');
    $capValor = old('capacidad', $almacen->capacidad ?? '');
    $responsableId = old('responsable_usuarioid', $almacen->responsable_usuarioid ?? '');
    $coordsInicial = \App\Support\UbicacionGpsParser::fromTexto($ubicacionRaw);
    $tieneGps = $coordsInicial !== null;
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <strong>No se pudo guardar:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@push('styles')
<style>
.page-almacen-form .form-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 14px rgba(0,0,0,.08);
}
.page-almacen-form .form-card .card-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border-radius: 12px 12px 0 0 !important;
    padding: 1.1rem 1.25rem;
}
.page-almacen-form .guia-campo {
    background: #f8fbf8;
    border-left: 3px solid #2c5530;
    border-radius: 0 8px 8px 0;
    padding: 0.55rem 0.8rem;
    margin-top: 0.4rem;
    font-size: 0.84rem;
    color: #495057;
}
.page-almacen-form .form-control {
    border-radius: 8px;
    border: 2px solid #dee2e6;
    min-height: 44px;
}
.page-almacen-form .form-control:focus {
    border-color: #2c5530;
    box-shadow: 0 0 0 0.15rem rgba(44,85,48,.15);
}
.page-almacen-form .capacidad-addon {
    background: #e8f5e9;
    color: #2c5530;
    font-weight: 600;
    border: 2px solid #dee2e6;
    border-left: none;
}

/* Panel ubicación + mapa inline */
.alm-ubicacion-panel {
    border: 1px solid #d1e7d4;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 18px rgba(44, 85, 48, .07);
    margin-bottom: 1.25rem;
}
.alm-ubicacion-panel__head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: .65rem;
    padding: .85rem 1.1rem;
    background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 55%, #fff 100%);
    border-bottom: 1px solid #d1e7d4;
}
.alm-ubicacion-panel__titulo {
    font-size: .95rem;
    font-weight: 700;
    color: #14532d;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.alm-ubicacion-panel__titulo i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: #fff;
    font-size: .8rem;
}
.alm-ubicacion-panel__hint {
    font-size: .78rem;
    color: #64748b;
    margin: 0;
}
.alm-ubicacion-panel__mapa-wrap {
    position: relative;
    padding: .65rem .65rem 0;
    background: #f8faf9;
}
#mapaAlmacenUbicacion {
    height: 360px;
    width: 100%;
    border-radius: 12px;
    border: 2px solid #d1e7d4;
    background: #e8eef4;
    z-index: 1;
}
.alm-ubicacion-panel__mapa-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 500;
    background: rgba(255,255,255,.94);
    backdrop-filter: blur(4px);
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .35rem .65rem;
    font-size: .72rem;
    font-weight: 600;
    color: #475569;
    box-shadow: 0 2px 8px rgba(15,23,42,.1);
    pointer-events: none;
}
.alm-ubicacion-panel__mapa-badge i { color: #16a34a; }
.alm-ubicacion-panel__coords {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem;
    padding: .75rem 1.1rem;
    background: #fafbfc;
    border-top: 1px solid #e8edf2;
    border-bottom: 1px solid #e8edf2;
}
.alm-ubicacion-coord-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-family: ui-monospace, 'Cascadia Code', monospace;
    font-size: .78rem;
    font-weight: 700;
    color: #15803d;
    background: #ecfdf5;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: .35rem .7rem;
    min-height: 34px;
}
.alm-ubicacion-coord-chip.is-empty {
    color: #94a3b8;
    background: #f1f5f9;
    border-color: #e2e8f0;
    font-weight: 600;
    font-family: inherit;
}
.alm-ubicacion-coord-chip i { font-size: .75rem; }
.alm-ubicacion-panel__coords .btn-centrar {
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 600;
    padding: .35rem .75rem;
    border-color: #cbd5e1;
    color: #475569;
}
.alm-ubicacion-panel__coords .btn-centrar:hover {
    border-color: #16a34a;
    color: #15803d;
    background: #f0fdf4;
}
.alm-ubicacion-panel__body {
    padding: 1rem 1.1rem 1.1rem;
}
.alm-ubicacion-panel__body label {
    font-size: .82rem;
    font-weight: 600;
    color: #334155;
}
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

<div class="page-almacen-form">
    <div class="alert alert-light border mb-3">
        <i class="fas fa-warehouse text-success mr-1"></i>
        Registrá el depósito con su <strong>ubicación en mapa</strong> (clic en el mapa) y capacidad en <strong>kilogramos</strong>.
        @if(($ambito ?? '') === \App\Support\AlmacenAmbito::AGRICOLA)
            Las cosechas que envíes aquí se verán en <strong>Movimientos</strong> y descontarán espacio disponible.
        @endif
    </div>

    @if($esAdmin ?? false)
    <div class="form-group">
        <label for="responsable_usuarioid">
            <i class="fas fa-user-check mr-1"></i>{{ $etiquetaResponsable ?? 'Responsable del almacén' }}
            <span class="text-danger">*</span>
        </label>
        @if(($responsables ?? collect())->isNotEmpty())
            <select name="responsable_usuarioid" id="responsable_usuarioid"
                    class="form-control @error('responsable_usuarioid') is-invalid @enderror" required>
                <option value="" disabled @selected(! $responsableId)>— Seleccione responsable —</option>
                @foreach($responsables as $responsable)
                    <option value="{{ $responsable->usuarioid }}" @selected((int) $responsableId === (int) $responsable->usuarioid)>
                        {{ trim($responsable->nombre.' '.($responsable->apellido ?? '')) }} — {{ $responsable->email }}
                    </option>
                @endforeach
            </select>
            <div class="guia-campo">
                El almacén queda bajo el usuario seleccionado. No se asigna al administrador para evitar datos mezclados entre roles.
            </div>
        @else
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No hay usuarios con rol válido para este tipo de almacén. Cree primero el responsable en Gestión de usuarios.
            </div>
        @endif
        @error('responsable_usuarioid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    @endif

    <div class="form-group">
        <label for="nombre">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
               maxlength="100" value="{{ $nombreValor }}" required placeholder="Ej: Almacén Norte">
        @if(! $esEdicion)
        <small class="text-muted d-block mt-1">Se sugiere automáticamente al marcar el mapa (tipo + calle + código único). Podés editarlo.</small>
        @endif
        @if(!empty($campos['nombre']))<div class="guia-campo">{{ $campos['nombre'] }}</div>@endif
        @error('nombre')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="descripcion">Descripción <span class="text-muted font-weight-normal">(opcional)</span></label>
        <input type="text" name="descripcion" id="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
               maxlength="250" value="{{ $descValor }}" placeholder="Ej: Cámara para producto fresco">
        @if(!empty($campos['descripcion']))<div class="guia-campo">{{ $campos['descripcion'] }}</div>@endif
        @error('descripcion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Ubicación con mapa inline --}}
    <div class="alm-ubicacion-panel">
        <div class="alm-ubicacion-panel__head">
            <div>
                <h4 class="alm-ubicacion-panel__titulo">
                    <i class="fas fa-map-marked-alt"></i> Ubicación en mapa
                </h4>
                <p class="alm-ubicacion-panel__hint mb-0 mt-1">
                    Hacé clic en el mapa para marcar el punto. Podés arrastrar el marcador para ajustar.
                </p>
            </div>
        </div>

        <div class="alm-ubicacion-panel__mapa-wrap">
            <span class="alm-ubicacion-panel__mapa-badge">
                <i class="fas fa-hand-pointer mr-1"></i> Clic para ubicar
            </span>
            <div id="mapaAlmacenUbicacion"></div>
        </div>

        <div class="alm-ubicacion-panel__coords d-flex justify-content-end py-2 px-3">
            <button type="button" id="btn-centrar-scz" class="btn btn-outline-secondary btn-centrar">
                <i class="fas fa-location-arrow mr-1"></i> Centrar en Santa Cruz
            </button>
        </div>

        <div class="alm-ubicacion-panel__body">
            <label for="ubicacion">Dirección o referencia</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-map-marker-alt text-success"></i></span>
                </div>
                <input type="text" name="ubicacion" id="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror"
                       maxlength="200" value="{{ $ubicacionValor }}" placeholder="Dirección, referencia o coordenadas GPS">
            </div>
            @if(!empty($campos['ubicacion']))<div class="guia-campo">{{ $campos['ubicacion'] }}</div>@endif
            <small id="ubicacion_detalle_hint" class="text-muted d-block mt-2">
                @if($tieneGps)
                    Ubicación GPS fijada desde el mapa. Podés editar el texto si necesitás agregar una referencia.
                @else
                    El mapa guardará las coordenadas automáticamente al marcar el punto.
                @endif
            </small>
            @error('ubicacion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-group mb-0">
        <label for="capacidad">Capacidad máxima (kg)</label>
        <div class="input-group">
            <input type="number" step="0.01" min="0.01" name="capacidad" id="capacidad" required
                   class="form-control @error('capacidad') is-invalid @enderror"
                   value="{{ $capValor }}" placeholder="Ej: 50000">
            <div class="input-group-append">
                <span class="input-group-text capacidad-addon">kg</span>
            </div>
        </div>
        @if(!empty($campos['capacidad']))<div class="guia-campo">{{ $campos['capacidad'] }}</div>@endif
        @error('capacidad')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(function () {
    const inputUbic = document.getElementById('ubicacion');
    const inputNombre = document.getElementById('nombre');
    const hint = document.getElementById('ubicacion_detalle_hint');
    const esEdicion = @json($esEdicion);
    const urlSugerirNombre = @json(route(($rutaPrefijo ?? 'almacen-agricola').'.sugerir-nombre'));
    const DEFAULT_LAT = -17.7833;
    const DEFAULT_LNG = -63.1821;

    let latActual = DEFAULT_LAT;
    let lngActual = DEFAULT_LNG;
    let mapaAlmacen = null;
    let marcadorAlmacen = null;
    let nombreEditadoManual = esEdicion && ((inputNombre?.value || '').trim() !== '');

    const coordsInicial = @json($coordsInicial);
    if (coordsInicial) {
        latActual = coordsInicial.lat;
        lngActual = coordsInicial.lng;
    }

    function formatoGps(lat, lng) {
        return 'GPS ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5);
    }

    let geocodeTimer = null;

    if (inputNombre) {
        inputNombre.addEventListener('input', function () {
            nombreEditadoManual = true;
        });
    }

    function sugerirNombreAlmacen(lat, lng, calle) {
        if (nombreEditadoManual || !inputNombre || !urlSugerirNombre) {
            return;
        }

        const params = new URLSearchParams({
            lat: String(lat),
            lng: String(lng),
        });
        if (calle) {
            params.set('zona', calle);
        }

        fetch(urlSugerirNombre + '?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!nombreEditadoManual && data && data.nombre) {
                    inputNombre.value = data.nombre;
                }
            })
            .catch(function () { /* silencioso */ });
    }

    function textoDireccionDesdeRespuesta(data) {
        const addr = data && data.address ? data.address : {};
        const partes = [
            addr.road || addr.pedestrian || addr.footway || addr.path,
            addr.suburb || addr.neighbourhood || addr.quarter,
            addr.city || addr.town || addr.municipality || 'Santa Cruz de la Sierra',
        ].filter(Boolean);
        if (partes.length) {
            return partes.slice(0, 2).join(', ');
        }
        if (data && data.display_name) {
            return String(data.display_name).split(',').slice(0, 2).join(',').trim();
        }
        return '';
    }

    function actualizarCampo(lat, lng) {
        latActual = lat;
        lngActual = lng;
        sugerirNombreAlmacen(lat, lng, '');
        if (inputUbic) {
            inputUbic.placeholder = 'Buscando dirección…';
        }
        if (hint) {
            hint.textContent = 'Obteniendo calle desde el mapa…';
        }

        clearTimeout(geocodeTimer);
        geocodeTimer = setTimeout(function () {
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1', {
                headers: { 'Accept-Language': 'es' },
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    const calle = textoDireccionDesdeRespuesta(data);
                    if (inputUbic) {
                        inputUbic.value = calle;
                        inputUbic.placeholder = 'Dirección o referencia';
                    }
                    if (hint) {
                        hint.textContent = calle
                            ? 'Dirección sugerida desde el mapa. Podés editar el texto si necesitás agregar una referencia.'
                            : 'Punto marcado en el mapa. Escribí una referencia si la calle no se detectó.';
                    }
                    sugerirNombreAlmacen(lat, lng, calle);
                })
                .catch(function () {
                    if (inputUbic) {
                        inputUbic.placeholder = 'Dirección o referencia';
                    }
                    if (hint) {
                        hint.textContent = 'Punto marcado en el mapa. Escribí la dirección o una referencia.';
                    }
                    sugerirNombreAlmacen(lat, lng, '');
                });
        }, 350);
    }

    function ubicacionParaGuardar() {
        const calle = (inputUbic?.value || '').trim();
        const gps = formatoGps(latActual, lngActual);
        if (!marcadorAlmacen) {
            return calle;
        }
        return calle ? (calle + ' · ' + gps) : gps;
    }

    function colocarMarcador(lat, lng, actualizarInput) {
        latActual = lat;
        lngActual = lng;
        if (!mapaAlmacen) return;

        if (marcadorAlmacen) {
            mapaAlmacen.removeLayer(marcadorAlmacen);
        }
        marcadorAlmacen = L.marker([lat, lng], { draggable: true }).addTo(mapaAlmacen);
        marcadorAlmacen.on('dragend', function (e) {
            const p = e.target.getLatLng();
            actualizarCampo(p.lat, p.lng);
        });

        if (actualizarInput !== false) {
            actualizarCampo(lat, lng);
        }
    }

    function parseGpsDesdeTexto(texto) {
        const t = (texto || '').trim();
        if (!t) return null;
        let m = t.match(/GPS\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/i);
        if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
        m = t.match(/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/);
        if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
        return null;
    }

    function initMapaAlmacen() {
        if (mapaAlmacen) {
            mapaAlmacen.invalidateSize();
            return;
        }

        mapaAlmacen = L.map('mapaAlmacenUbicacion').setView([latActual, lngActual], coordsInicial ? 14 : 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapaAlmacen);

        mapaAlmacen.on('click', function (e) {
            colocarMarcador(e.latlng.lat, e.latlng.lng, true);
        });

        if (coordsInicial) {
            colocarMarcador(latActual, lngActual, false);
        }
    }

    setTimeout(initMapaAlmacen, 150);
    $(window).on('resize', function () {
        if (mapaAlmacen) {
            setTimeout(function () { mapaAlmacen.invalidateSize(); }, 100);
        }
    });

    $('#btn-centrar-scz').on('click', function () {
        if (!mapaAlmacen) return;
        mapaAlmacen.setView([DEFAULT_LAT, DEFAULT_LNG], 12);
    });

    if (inputUbic) {
        inputUbic.addEventListener('change', function () {
            const parsed = parseGpsDesdeTexto(inputUbic.value);
            if (parsed && mapaAlmacen) {
                mapaAlmacen.setView([parsed.lat, parsed.lng], 14);
                colocarMarcador(parsed.lat, parsed.lng, false);
                if (hint) {
                    hint.textContent = 'Coordenadas reconocidas en el texto.';
                }
            } else if (hint && inputUbic.value.trim() === '') {
                hint.textContent = 'El mapa guardará las coordenadas automáticamente al marcar el punto.';
                if (marcadorAlmacen && mapaAlmacen) {
                    mapaAlmacen.removeLayer(marcadorAlmacen);
                    marcadorAlmacen = null;
                }
            }
        });
    }

    const formAlmacen = inputUbic?.closest('form');
    if (formAlmacen) {
        formAlmacen.addEventListener('submit', function () {
            if (inputUbic) {
                inputUbic.value = ubicacionParaGuardar();
            }
        });
    }
});
</script>
@endpush
