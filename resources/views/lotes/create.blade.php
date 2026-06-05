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
            código de trazabilidad, estado «Planificado» y unidad en hectáreas.
            La fecha de siembra se registrará al completar la actividad de siembra.
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
                            <label><i class="fas fa-tag mr-1"></i> Nombre del lote <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" maxlength="100" required
                                   placeholder="Ej: Lote Norte A1" value="{{ old('nombre') }}">
                            <p class="campo-guia">Un nombre corto que identifique la parcela en listados y reportes.</p>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-ruler-combined mr-1"></i> Superficie (hectáreas) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="superficie" id="superficie" class="form-control" min="0.01" required
                                   placeholder="Ej: 12.5" value="{{ old('superficie') }}">
                            <p class="campo-guia">Área cultivable. En el mapa se dibuja un círculo aproximado según este valor.</p>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-seedling mr-1"></i> Cultivo principal</label>
                            <div class="d-flex flex-wrap align-items-start" style="gap: 6px;">
                                @include('partials.selector-catalogo', [
                                    'id' => 'lote_cultivo',
                                    'name' => 'cultivoid',
                                    'value' => $cultivoidInicial ?? '',
                                    'labelSelected' => $cultivoLabel ?? '',
                                    'endpoint' => route('catalogo-selector.cultivos'),
                                    'allowEmpty' => true,
                                    'placeholderEmpty' => 'Opcional — sin cultivo asignado',
                                    'title' => 'Seleccionar cultivo',
                                    'searchPlaceholder' => 'Nombre del cultivo…',
                                    'inputGroup' => true,
                                ])
                                <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#modalCultivo" title="Agregar cultivo nuevo">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <p class="campo-guia">Opcional al crear; puedes asignarlo luego desde editar lote.</p>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt mr-1"></i> Referencia de ubicación</label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" maxlength="200"
                                   placeholder="Se completa al marcar el mapa" value="{{ old('ubicacion') }}" readonly>
                            <p class="campo-guia">Se genera al hacer clic en el mapa. Puedes editarla después si necesitas una descripción más clara.</p>
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
                        <div class="form-row">
                            <div class="form-group col-6 mb-0">
                                <label class="small text-muted">Latitud</label>
                                <input type="number" step="0.0000001" name="latitud" id="latitud" class="form-control form-control-sm"
                                       value="{{ old('latitud', '-17.7833') }}" readonly>
                            </div>
                            <div class="form-group col-6 mb-0">
                                <label class="small text-muted">Longitud</label>
                                <input type="number" step="0.0000001" name="longitud" id="longitud" class="form-control form-control-sm"
                                       value="{{ old('longitud', '-63.1821') }}" readonly>
                            </div>
                        </div>
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

    <div class="modal fade" id="modalCultivo" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title">Nuevo cultivo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="nuevoCultivoNombre" class="form-control" placeholder="Ej: Quinua" maxlength="100">
                    <div id="cultivoError" class="alert alert-danger mt-2 py-1 small" style="display:none;"></div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-success btn-sm" id="btnGuardarCultivo">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
            let circle = null;

            function calcularRadio(ha) {
                return Math.sqrt(ha * 10000 / Math.PI);
            }

            function colocarMarcador(lat, lng) {
                latInput.value = Number(lat).toFixed(7);
                lngInput.value = Number(lng).toFixed(7);
                ubicInput.removeAttribute('readonly');
                if (!ubicInput.value || ubicInput.value.startsWith('Parcela GPS')) {
                    ubicInput.value = 'Parcela GPS ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5);
                }

                if (marker) map.removeLayer(marker);
                if (circle) map.removeLayer(circle);

                marker = L.marker([lat, lng]).addTo(map);
                const sup = parseFloat(supInput.value);
                if (sup > 0) {
                    circle = L.circle([lat, lng], {
                        color: '#28a745', fillColor: '#28a745', fillOpacity: 0.25,
                        radius: calcularRadio(sup)
                    }).addTo(map);
                }
            }

            map.on('click', function (e) {
                colocarMarcador(e.latlng.lat, e.latlng.lng);
            });

            supInput.addEventListener('input', function () {
                const lat = latInput.value;
                const lng = lngInput.value;
                const sup = parseFloat(this.value);
                if (lat && lng && sup > 0) {
                    if (circle) map.removeLayer(circle);
                    circle = L.circle([lat, lng], {
                        color: '#28a745', fillColor: '#28a745', fillOpacity: 0.25,
                        radius: calcularRadio(sup)
                    }).addTo(map);
                }
            });

            if (latInput.value && lngInput.value) {
                colocarMarcador(parseFloat(latInput.value), parseFloat(lngInput.value));
            }

            document.getElementById('formNuevoLote').addEventListener('submit', function (e) {
                if (!latInput.value || !lngInput.value) {
                    e.preventDefault();
                    alert('Marca la ubicación del lote haciendo clic en el mapa.');
                }
            });

            document.getElementById('imagen')?.addEventListener('change', function () {
                var label = this.nextElementSibling;
                if (label) label.textContent = this.files[0]?.name || 'Elegir imagen…';
            });

            document.getElementById('btnGuardarCultivo').addEventListener('click', function () {
                const nombre = document.getElementById('nuevoCultivoNombre').value.trim();
                const err = document.getElementById('cultivoError');
                if (!nombre) {
                    err.textContent = 'Escribe el nombre del cultivo.';
                    err.style.display = 'block';
                    return;
                }
                fetch('{{ route("cultivos.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nombre: nombre })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.cultivoid) {
                            if (window.CatalogoSelector) {
                                CatalogoSelector.setValue('lote_cultivo', data.cultivoid, data.nombre);
                            }
                            $('#modalCultivo').modal('hide');
                            document.getElementById('nuevoCultivoNombre').value = '';
                            err.style.display = 'none';
                        }
                    })
                    .catch(() => {
                        err.textContent = 'No se pudo crear el cultivo.';
                        err.style.display = 'block';
                    });
            });
        })();
    </script>
@endpush
