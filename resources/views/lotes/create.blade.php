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

        <div class="alert alert-light border m-3 mb-0 small">
            <i class="fas fa-magic text-success mr-1"></i>
            Nombre, trazabilidad y consumo del material de siembra se calculan solos. La siembra se registra al completar la actividad correspondiente.
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

                        @include('lotes.partials.selector-semilla', [
                            'selectorId' => 'lote_semilla',
                            'insumoSemillaId' => $insumoSemillaId ?? '',
                            'insumoSemillaLabel' => $insumoSemillaLabel ?? '',
                            'cantidadSemillaPlanificada' => old('cantidad_semilla_planificada', ''),
                            'cantidadSemillaUnidad' => $dosisInicial['unidad'] ?? 'kg',
                            'semillaStockInicial' => $semillaStockInicial ?? null,
                            'omitirCantidadSemilla' => true,
                            'semillaRequerida' => true,
                        ])

                        @include('lotes.partials.planificacion-cosecha', [
                            'superficieValor' => old('superficie'),
                            'cantidadSemillaPlanificada' => old('cantidad_semilla_planificada', ''),
                            'cantidadSemillaUnidad' => $dosisInicial['unidad'] ?? 'kg',
                            'catalogoTamanoConteoId' => old('catalogotamanoconteoid', ''),
                        ])

                        <div class="form-group">
                            <label><i class="fas fa-tag mr-1"></i> Nombre del lote <span class="text-danger">*</span>
                                <span class="badge badge-success auto-badge ml-1">Automático</span>
                            </label>
                            <input type="text" name="nombre" id="nombreLote" class="form-control" maxlength="100" required
                                   placeholder="Se genera al elegir semilla / cultivo" value="{{ old('nombre') }}"
                                   autocomplete="off">
                            <p class="campo-guia">Se sugiere automáticamente al elegir la semilla; puede editarlo si lo necesita.</p>
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
                            <div id="lote-imagen-preview" class="lote-imagen-preview d-none mt-2">
                                <img id="lote-imagen-preview-img" src="" alt="Vista previa del lote" class="lote-imagen-preview__img">
                            </div>
                            <p class="campo-guia">Puedes omitirla y agregarla más tarde.</p>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="form-group mb-2">
                            <label><i class="fas fa-map mr-1"></i> Marca la parcela en el mapa <span class="text-danger">*</span></label>
                            <p class="campo-guia mb-2">Haz clic donde está el lote (Santa Cruz por defecto). Es obligatorio para trazabilidad y el mapa general.</p>
                            <div id="map"></div>
                            <div id="mapaUbicacionError" class="alert alert-warning small mt-2 mb-0 py-2 px-3 d-none">
                                <i class="fas fa-water mr-1"></i>
                                <span id="mapaUbicacionErrorTexto"></span>
                            </div>
                        </div>
                        <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud') }}">
                        <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud') }}">
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
    @include('lotes.partials.mapa-ubicacion-validacion-helper')
    @include('lotes.partials.planificacion-cosecha-helper')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const latInput = document.getElementById('latitud');
            const lngInput = document.getElementById('longitud');
            const ubicInput = document.getElementById('ubicacion');
            const supInput = document.getElementById('superficie');
            const nombreInput = document.getElementById('nombreLote');
            const semillaWrap = document.getElementById('selector_wrap_lote_semilla');
            const urlSiguienteNombre = @json(route('lotes.siguiente-nombre'));
            const urlPlanificar = @json(route('lotes.planificar-cosecha'));
            const urlValidarUbicacion = @json(route('lotes.validar-ubicacion'));
            let nombreEditadoManual = !!(nombreInput && nombreInput.value && nombreInput.value.trim());

            if (nombreInput) {
                nombreInput.addEventListener('input', function () {
                    nombreEditadoManual = this.value.trim() !== '';
                });
            }

            const map = L.map('map').setView([-17.7833, -63.1821], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

            let marker = null;
            const circleRef = { current: null };

            function avisoValidacion(titulo, texto) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: titulo,
                        text: texto,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#f59e0b',
                    });
                } else {
                    window.alert(texto || titulo);
                }
            }

            let ubicacionPendiente = null;
            let circuloHaTimer = null;

            const validadorUbicacion = window.AgroFusionLoteUbicacion.vincular({
                urlValidar: urlValidarUbicacion,
                onAviso: avisoValidacion,
                onResultado: function (estado) {
                    if (estado.ok || !ubicacionPendiente) {
                        return;
                    }
                    const actual = {
                        lat: parseFloat(latInput.value),
                        lng: parseFloat(lngInput.value),
                    };
                    if (actual.lat !== ubicacionPendiente.lat || actual.lng !== ubicacionPendiente.lng) {
                        return;
                    }
                    if (marker) {
                        map.removeLayer(marker);
                        marker = null;
                    }
                    latInput.value = '';
                    lngInput.value = '';
                    ubicacionPendiente = null;
                    window.AgroFusionLoteMapa.actualizarCirculo(map, circleRef, NaN, NaN, supInput.value);
                    avisoValidacion('Ubicación no válida', estado.mensaje);
                },
            });

            function redibujarCirculo(opciones) {
                const opts = Object.assign({ validar: false, ajustarVista: true }, opciones || {});
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    window.AgroFusionLoteMapa.actualizarCirculo(map, circleRef, NaN, NaN, supInput.value);
                    return;
                }
                window.AgroFusionLoteMapa.actualizarCirculo(map, circleRef, lat, lng, supInput.value, {
                    ajustarVista: opts.ajustarVista,
                });
                if (opts.validar) {
                    validadorUbicacion.programarValidacion({ silencioso: true, espera: 1500 });
                }
            }

            function onHectareasPlanificacion() {
                clearTimeout(circuloHaTimer);
                circuloHaTimer = setTimeout(function () {
                    redibujarCirculo({ validar: false, ajustarVista: false });
                }, 60);
            }

            function colocarMarcador(lat, lng) {
                const latNum = Number(lat);
                const lngNum = Number(lng);
                ubicacionPendiente = { lat: latNum, lng: lngNum };

                latInput.value = latNum.toFixed(7);
                lngInput.value = lngNum.toFixed(7);

                if (marker) {
                    map.removeLayer(marker);
                }

                const popupInicial = ubicInput.value && !window.AgroFusionMapaCalle.esTextoGps(ubicInput.value)
                    ? ubicInput.value
                    : 'Parcela';
                marker = L.marker([latNum, lngNum]).addTo(map);
                marker.bindPopup(popupInicial).openPopup();
                redibujarCirculo();

                const debeActualizarCalle = !ubicInput.value
                    || window.AgroFusionMapaCalle.esTextoGps(ubicInput.value);
                if (debeActualizarCalle) {
                    ubicInput.value = 'Buscando calle…';
                    window.AgroFusionMapaCalle.resolver(latNum, lngNum).then(function (calle) {
                        if (!latInput.value || !lngInput.value) {
                            return;
                        }
                        ubicInput.value = calle || 'Zona agrícola, Santa Cruz de la Sierra';
                        if (marker) {
                            marker.setPopupContent(ubicInput.value).openPopup();
                        }
                    });
                }
            }

            map.on('click', function (e) {
                colocarMarcador(e.latlng.lat, e.latlng.lng);
            });

            supInput.addEventListener('input', redibujarCirculo);
            supInput.addEventListener('change', redibujarCirculo);

            if (latInput.value && lngInput.value) {
                colocarMarcador(parseFloat(latInput.value), parseFloat(lngInput.value));
            }

            function actualizarNombreAutomatico(insumoId) {
                if (!nombreInput || !insumoId || nombreEditadoManual) {
                    return;
                }
                fetch(urlSiguienteNombre + '?insumoid=' + encodeURIComponent(insumoId), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.nombre) {
                            nombreInput.value = data.nombre;
                        }
                    })
                    .catch(function () {});
            }

            semillaWrap?.addEventListener('selector-catalogo:change', function (e) {
                const id = e.detail?.id || '';
                if (id) {
                    actualizarNombreAutomatico(id);
                }
            });

            const semillaInicial = semillaWrap?.querySelector('.selector-catalogo-value')?.value || '';
            if (semillaInicial && !nombreInput.value) {
                actualizarNombreAutomatico(semillaInicial);
            }

            window.AgroFusionPlanificacionCosecha.vincular({
                selectorId: 'lote_semilla',
                superficieInputId: 'superficie',
                cantidadInputId: 'cantidad_semilla_planificada',
                cantidadWrapId: 'cantidadSemillaWrap',
                urlPlanificar: urlPlanificar,
                onHectareasChange: onHectareasPlanificacion,
            });

            const formNuevoLote = document.getElementById('formNuevoLote');
            const btnGuardarLote = formNuevoLote?.querySelector('button[type="submit"]');
            let guardandoLote = false;

            formNuevoLote?.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (guardandoLote) {
                    return;
                }

                window.AgroFusionPlanificacionCosecha?.sincronizarAntesEnvio?.();

                const semillaId = semillaWrap?.querySelector('.selector-catalogo-value')?.value || '';
                if (!semillaId) {
                    avisoValidacion('Semilla requerida', 'Seleccione la semilla o cultivo a cosechar.');
                    return;
                }
                if (!latInput.value || !lngInput.value) {
                    avisoValidacion('Ubicación requerida', 'Marca la ubicación del lote haciendo clic en el mapa.');
                    return;
                }

                const validacionPlan = window.AgroFusionPlanificacionCosecha?.validarEnvio?.();
                if (validacionPlan && !validacionPlan.ok) {
                    avisoValidacion('No se puede guardar', validacionPlan.mensaje);
                    return;
                }

                if (!validadorUbicacion.esValida()) {
                    const validacionUbicacion = await Promise.race([
                        validadorUbicacion.validar({ silencioso: true }),
                        new Promise(function (resolve) {
                            setTimeout(function () {
                                resolve({ ok: true, omitida: true });
                            }, 3500);
                        }),
                    ]);
                    if (!validacionUbicacion.ok && !validacionUbicacion.omitida) {
                        validadorUbicacion.mostrarErrorUi(validacionUbicacion.mensaje);
                        avisoValidacion('Ubicación no válida', validacionUbicacion.mensaje);
                        return;
                    }
                }

                guardandoLote = true;
                if (btnGuardarLote) {
                    btnGuardarLote.disabled = true;
                    btnGuardarLote.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando…';
                }
                formNuevoLote.submit();
            });

            document.getElementById('imagen')?.addEventListener('change', function () {
                var label = this.nextElementSibling;
                if (label) label.textContent = this.files[0]?.name || 'Elegir imagen…';

                var preview = document.getElementById('lote-imagen-preview');
                var previewImg = document.getElementById('lote-imagen-preview-img');
                if (!preview || !previewImg) return;

                var file = this.files && this.files[0];
                if (!file || !file.type.startsWith('image/')) {
                    preview.classList.add('d-none');
                    previewImg.removeAttribute('src');
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endpush
