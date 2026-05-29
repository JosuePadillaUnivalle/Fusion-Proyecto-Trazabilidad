@extends('layouts.app')

@section('title', 'Crear lote | AgroNexus')
@section('page_title', 'Crear lote')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 300px;
            width: 100%;
            border-radius: 5px;
            border: 2px solid #ddd;
        }

        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 5px;
            border: 2px solid #ddd;
        }

        .image-upload-container {
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .image-upload-container:hover {
            border-color: #28a745;
            background-color: #f8fff8;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title"><i class="fas fa-map-marked-alt mr-2"></i>Crear Lote</h3>
        </div>

        @if($errors->any())
            <div class="alert alert-danger m-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('lotes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-user mr-1"></i> Usuario Propietario <span
                                    class="text-danger">*</span></label>
                            <select name="usuarioid" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->usuarioid }}">{{ $u->nombre }} {{ $u->apellido }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-tag mr-1"></i> Nombre del Lote <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" maxlength="100" required
                                placeholder="Ej: Lote Norte" value="{{ old('nombre') }}">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt mr-1"></i> Ubicacion (texto)</label>
                            <input type="text" name="ubicacion" class="form-control" maxlength="200"
                                placeholder="Ej: Km 5 Carretera Norte" value="{{ old('ubicacion') }}">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-ruler-combined mr-1"></i> Superficie (hectareas) <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="superficie" id="superficie" class="form-control" min="0"
                                required placeholder="Ej: 15.5" value="{{ old('superficie') }}">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-seedling mr-1"></i> Cultivo</label>
                            <div class="input-group">
                                <select name="cultivoid" id="cultivoid" class="form-control">
                                    <option value="">-- Sin cultivo --</option>
                                    @foreach($cultivos as $c)
                                        <option value="{{ $c->cultivoid }}">{{ $c->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" data-toggle="modal"
                                        data-target="#modalCultivo">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-id-badge mr-1"></i> Código de trazabilidad</label>
                            <input type="text" name="codigo_trazabilidad" class="form-control" maxlength="80"
                                value="{{ old('codigo_trazabilidad') }}" placeholder="Ej: LT-2026-0001">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-truck-loading mr-1"></i> Actor de abastecimiento</label>
                            <select name="actorid" class="form-control">
                                <option value="">-- Sin actor --</option>
                                @foreach($actores as $actor)
                                    <option value="{{ $actor->actorid }}" {{ (string) old('actorid') === (string) $actor->actorid ? 'selected' : '' }}>
                                        {{ $actor->nombre }} ({{ ucfirst($actor->tipo_actor) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-image mr-1"></i> Imagen del Lote</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/*">
                                <label class="custom-file-label" for="imagen">Seleccionar archivo...</label>
                            </div>
                            <small class="form-text text-muted">Formatos: JPG, PNG. Máx: 2MB.</small>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-calendar mr-1"></i> Fecha de Siembra</label>
                            <input type="date" name="fechasiembra" class="form-control" value="{{ old('fechasiembra') }}">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-flag mr-1"></i> Estado del Lote</label>
                            <select name="estadolotetipoid" class="form-control">
                                <option value="">-- Seleccione estado --</option>
                                @foreach($estados as $e)
                                    <option value="{{ $e->estadolotetipoid }}" {{ $e->nombre == 'disponible' ? 'selected' : '' }}>
                                        {{ ucfirst($e->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-map mr-1"></i> Ubicacion en el Mapa</label>
                            <small class="text-muted d-block mb-2">Haz clic en el mapa para seleccionar la ubicacion</small>
                            <div id="map"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Latitud</label>
                                <input type="number" step="0.0000001" name="latitud" id="latitud" class="form-control"
                                    min="-90" max="90" value="{{ old('latitud') }}" placeholder="-17.7833">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Longitud</label>
                                <input type="number" step="0.0000001" name="longitud" id="longitud" class="form-control"
                                    min="-180" max="180" value="{{ old('longitud') }}" placeholder="-63.1821">
                            </div>
                        </div>

                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle mr-1"></i> El mapa esta centrado en Santa Cruz, Bolivia. Haz clic
                            para marcar la ubicacion.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('lotes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>
                        Cancelar</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar Lote</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Cultivo -->
    <div class="modal fade" id="modalCultivo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-seedling mr-2"></i>Nuevo Cultivo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del Cultivo <span class="text-danger">*</span></label>
                        <input type="text" id="nuevoCultivoNombre" class="form-control" placeholder="Ej: Quinua"
                            maxlength="100">
                    </div>
                    <div id="cultivoError" class="alert alert-danger" style="display: none;"></div>
                    <div id="cultivoExito" class="alert alert-success" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarCultivo"><i class="fas fa-save mr-1"></i>
                        Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Preview imagen
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('previewContainer').style.display = 'block';
                    document.querySelector('.image-upload-container').style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeImage() {
            document.getElementById('imagen').value = '';
            document.getElementById('previewContainer').style.display = 'none';
            document.querySelector('.image-upload-container').style.display = 'block';
        }

        // Mapa
        var map = L.map('map').setView([-17.7833, -63.1821], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

        var marker = null;
        var circle = null;

        function calcularRadio(ha) { return Math.sqrt(ha * 10000 / Math.PI); }

        map.on('click', function (e) {
            var lat = e.latlng.lat.toFixed(7);
            var lng = e.latlng.lng.toFixed(7);
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;

            if (marker) map.removeLayer(marker);
            if (circle) map.removeLayer(circle);

            marker = L.marker([lat, lng]).addTo(map).bindPopup('Lat: ' + lat + '<br>Lng: ' + lng).openPopup();

            var sup = parseFloat(document.getElementById('superficie').value);
            if (sup > 0) {
                circle = L.circle([lat, lng], { color: 'green', fillColor: '#28a745', fillOpacity: 0.3, radius: calcularRadio(sup) }).addTo(map);
            }
        });

        document.getElementById('superficie').addEventListener('input', function () {
            var lat = document.getElementById('latitud').value;
            var lng = document.getElementById('longitud').value;
            var sup = parseFloat(this.value);
            if (lat && lng && sup > 0) {
                if (circle) map.removeLayer(circle);
                circle = L.circle([lat, lng], { color: 'green', fillColor: '#28a745', fillOpacity: 0.3, radius: calcularRadio(sup) }).addTo(map);
            }
        });

        // Modal cultivo
        document.getElementById('btnGuardarCultivo').addEventListener('click', function () {
            var nombre = document.getElementById('nuevoCultivoNombre').value.trim();
            if (!nombre) {
                document.getElementById('cultivoError').textContent = 'El nombre es obligatorio';
                document.getElementById('cultivoError').style.display = 'block';
                return;
            }

            fetch('{{ route("cultivos.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ nombre: nombre })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.cultivoid) {
                        document.getElementById('cultivoError').style.display = 'none';
                        document.getElementById('cultivoExito').textContent = 'Cultivo creado';
                        document.getElementById('cultivoExito').style.display = 'block';

                        var select = document.getElementById('cultivoid');
                        var option = document.createElement('option');
                        option.value = data.cultivoid;
                        option.text = data.nombre;
                        option.selected = true;
                        select.appendChild(option);

                        setTimeout(function () {
                            document.getElementById('nuevoCultivoNombre').value = '';
                            document.getElementById('cultivoExito').style.display = 'none';
                            $('#modalCultivo').modal('hide');
                        }, 1000);
                    }
                })
                .catch(error => {
                    document.getElementById('cultivoError').textContent = 'Error al crear';
                    document.getElementById('cultivoError').style.display = 'block';
                });
        });
    </script>
@endpush