@extends('layouts.app')

@section('title', 'Crear lote | Fusion-Proyectos')
@section('page_title', 'Nuevo lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 320px; width: 100%; border-radius: 8px; border: 2px solid #dee2e6; }
        .campo-guia { font-size: .85rem; color: #6c757d; margin-top: 4px; }
        .auto-badge { font-size: .75rem; }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-map-marked-alt mr-2"></i>Registrar parcela nueva</h3>
        </div>

        @if($errors->any())
            <div class="alert alert-danger m-3 mb-0">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-light border m-3 mb-0">
            <strong><i class="fas fa-magic text-success mr-1"></i> Se completa automáticamente:</strong>
            código de trazabilidad, estado «Planificado», unidad en hectáreas,
            <strong>nombre del lote</strong> (ej. <em>Tomate - Lote 001</em>) y estimaciones de <strong>cosecha / semilla</strong>
            al elegir cultivo y planificar por hectáreas, unidades o empaques.
        </div>

        <form action="{{ route('lotes.store') }}" method="POST" enctype="multipart/form-data" id="formNuevoLote">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-5">
                        @if($mostrarSelectorPropietario)
                            @include('partials.selector-catalogo', [
                                'id' => 'lote_responsable',
                                'name' => 'usuarioid',
                                'label' => 'Empleado asignado',
                                'icon' => 'fa-user',
                                'value' => $usuarioidInicial ?: '',
                                'labelSelected' => $responsableLabel ?? '',
                                'endpoint' => route('catalogo-selector.usuarios'),
                                'params' => $responsableSelectorParams ?? ['roles' => 'agricultor'],
                                'title' => 'Seleccionar empleado',
                                'searchPlaceholder' => 'Nombre, correo o usuario…',
                                'help' => ! empty($esJefeAgricultorDesignando)
                                    ? 'Solo aparecen los agricultores registrados bajo tu equipo.'
                                    : 'Solo usuarios con rol agricultor. El administrador supervisa el sistema y no es responsable de parcelas.',
                                'required' => true,
                            ])
                        @else
                            <input type="hidden" name="usuarioid" value="{{ $propietarioPorDefecto }}">
                        @endif

                        <div class="form-group">
                            <label><i class="fas fa-tag mr-1"></i> Nombre del lote <span class="text-muted font-weight-normal">(opcional)</span></label>
                            <input type="text" name="nombre" id="nombreLote" class="form-control" maxlength="100"
                                   placeholder="Se genera al elegir semilla (ej. Tomate - Lote 001)" value="{{ old('nombre') }}">
                            <p class="campo-guia mb-1">Déjelo vacío para usar la secuencia automática por cultivo, o escriba un nombre propio.</p>
                            <div class="alert alert-light border py-2 px-3 mb-0 small">
                                <i class="fas fa-magic text-success mr-1"></i>
                                Se creará como: <strong id="nombreLotePreview" class="text-success">—</strong>
                            </div>
                        </div>

                        @include('lotes.partials.selector-semilla', [
                            'selectorId' => 'lote_semilla',
                            'insumoSemillaId' => $insumoSemillaId ?? '',
                            'insumoSemillaLabel' => $insumoSemillaLabel ?? '',
                            'semillaStockInicial' => $semillaStockInicial ?? null,
                        ])

                        @include('lotes.partials.planificacion-cosecha', [
                            'cantidadSemillaPlanificada' => old('cantidad_semilla_planificada', ''),
                            'cantidadSemillaUnidad' => $dosisInicial['unidad'] ?? 'kg',
                            'catalogoTamanoConteoId' => old('catalogotamanoconteoid', ''),
                        ])

                        <div id="superficieWrap" class="form-group {{ ($insumoSemillaId ?? '') ? '' : 'd-none' }}">
                            <label><i class="fas fa-ruler-combined mr-1"></i> Superficie (hectáreas) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="superficie" id="superficie" class="form-control" min="0.01"
                                   {{ ($insumoSemillaId ?? '') ? 'required' : '' }}
                                   placeholder="Ej: 12.5" value="{{ old('superficie') }}">
                            <p class="campo-guia">Área cultivable. En el mapa se dibuja un círculo según este valor (1 ha = 10.000 m²).</p>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-road mr-1"></i> Calle o referencia</label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" maxlength="200"
                                   placeholder="Se completa al marcar el mapa" value="{{ old('ubicacion') }}">
                            <p class="campo-guia">Al hacer clic en el mapa se sugiere la calle. Puedes corregirla si hace falta.</p>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-image mr-1"></i> Imagen del lote <span class="text-muted">(opcional)</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/*">
                                <label class="custom-file-label" for="imagen">Elegir imagen…</label>
                            </div>
                            <p class="campo-guia">Puedes omitirla y agregarla más tarde.</p>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="form-group mb-2">
                            <label><i class="fas fa-map mr-1"></i> Marca la parcela en el mapa <span class="text-danger">*</span></label>
                            <p class="campo-guia mb-2">Haz clic donde está el lote (Santa Cruz por defecto). Es obligatorio para trazabilidad y el mapa general.</p>
                            <div id="map"></div>
                        </div>
                        <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud', '-17.7833') }}">
                        <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud', '-63.1821') }}">
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('lotes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Guardar lote
                </button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    @include('lotes.partials.mapa-calle-helper')
    @include('lotes.partials.mapa-superficie-helper')
    @include('lotes.partials.planificacion-cosecha-helper')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const latInput = document.getElementById('latitud');
            const lngInput = document.getElementById('longitud');
            const ubicInput = document.getElementById('ubicacion');
            const supInput = document.getElementById('superficie');

            const map = L.map('map').setView([-17.7833, -63.1821], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

            let marker = null;
            const circleRef = { current: null };

            function redibujarCirculo() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                window.AgroFusionLoteMapa.actualizarCirculo(map, circleRef, lat, lng, supInput.value);
            }

            async function colocarMarcador(lat, lng) {
                latInput.value = Number(lat).toFixed(7);
                lngInput.value = Number(lng).toFixed(7);

                ubicInput.value = 'Buscando calle…';
                const calle = await window.AgroFusionMapaCalle.resolver(lat, lng);
                ubicInput.value = calle || 'Zona agrícola, Santa Cruz de la Sierra';

                if (marker) map.removeLayer(marker);

                marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(ubicInput.value || 'Parcela').openPopup();
                redibujarCirculo();
            }

            map.on('click', function (e) {
                colocarMarcador(e.latlng.lat, e.latlng.lng);
            });

            supInput.addEventListener('input', redibujarCirculo);
            supInput.addEventListener('change', redibujarCirculo);

            if (latInput.value && lngInput.value) {
                colocarMarcador(parseFloat(latInput.value), parseFloat(lngInput.value));
            } else if (supInput.value) {
                redibujarCirculo();
            }

            document.getElementById('formNuevoLote').addEventListener('submit', function (e) {
                if (!latInput.value || !lngInput.value) {
                    e.preventDefault();
                    alert('Marca la ubicación del lote haciendo clic en el mapa.');
                }
            });

            const nombrePreview = document.getElementById('nombreLotePreview');
            const nombreInput = document.getElementById('nombreLote');
            const semillaWrap = document.getElementById('selector_wrap_lote_semilla');
            const urlSiguienteNombre = @json(route('lotes.siguiente-nombre'));
            let previewTimer = null;

            function actualizarNombrePreview() {
                const insumoId = semillaWrap?.querySelector('.selector-catalogo-value')?.value || '';
                if (!insumoId) {
                    if (nombrePreview) nombrePreview.textContent = '—';
                    return;
                }
                if (nombrePreview) nombrePreview.textContent = '…';
                fetch(urlSiguienteNombre + '?insumoid=' + encodeURIComponent(insumoId), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(r => r.json())
                    .then(j => {
                        if (nombrePreview) nombrePreview.textContent = j.nombre || '—';
                        if (!nombreInput.value.trim() && j.nombre) {
                            nombreInput.placeholder = j.nombre;
                        }
                    })
                    .catch(() => {
                        if (nombrePreview) nombrePreview.textContent = '—';
                    });
            }

            semillaWrap?.addEventListener('selector-catalogo:change', function () {
                clearTimeout(previewTimer);
                previewTimer = setTimeout(actualizarNombrePreview, 200);
            });

            if (semillaWrap?.querySelector('.selector-catalogo-value')?.value) {
                actualizarNombrePreview();
            }

            document.getElementById('imagen')?.addEventListener('change', function () {
                var label = this.nextElementSibling;
                if (label) label.textContent = this.files[0]?.name || 'Elegir imagen…';
            });

            window.AgroFusionLoteMapa.vincularDosisSiembra({
                selectorId: 'lote_semilla',
                initialDosis: @json(($dosisInicial['tiene_dosis'] ?? false) ? [
                    'dosis_por_ha' => $dosisInicial['por_ha'] ?? 0,
                    'dosis_unidad' => $dosisInicial['unidad'] ?? 'kg',
                ] : null),
                initialCantidad: @json(old('cantidad_semilla_planificada')),
                initialStock: @json($semillaStockInicial ?? null),
            });

            window.AgroFusionPlanificacionCosecha.vincular({
                selectorId: 'lote_semilla',
                urlPlanificar: @json(route('lotes.planificar-cosecha')),
                onHectareasChange: function () {
                    redibujarCirculo();
                },
            });
        })();
    </script>
@endpush
