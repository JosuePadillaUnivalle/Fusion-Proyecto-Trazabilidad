@extends('layouts.app')

@section('title', 'Editar lote | AgroFusion')
@section('page_title', 'Editar lote')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 300px;
            width: 100%;
            border-radius: 5px;
            border: 2px solid #ddd;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header bg-warning">
            <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Editar Lote: {{ $lote->nombre }}</h3>
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

        <form action="{{ route('lotes.update', $lote) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        @if($puedeDesignarResponsable ?? false)
                        @include('partials.selector-catalogo', [
                            'id' => 'lote_edit_responsable',
                            'name' => 'usuarioid',
                            'label' => 'Empleado asignado',
                            'icon' => 'fa-user',
                            'value' => $responsableLabel ? $lote->usuarioid : '',
                            'labelSelected' => $responsableLabel ?? '',
                            'endpoint' => route('catalogo-selector.usuarios'),
                            'params' => $responsableSelectorParams ?? ['roles' => 'agricultor'],
                            'title' => 'Seleccionar empleado',
                            'searchPlaceholder' => 'Nombre, correo o usuario…',
                            'help' => ! empty($esJefeAgricultorDesignando)
                                ? 'Solo puedes asignar agricultores de tu equipo.'
                                : null,
                            'required' => true,
                        ])
                        @else
                        <div class="form-group">
                            <label><i class="fas fa-user mr-1"></i> Empleado asignado</label>
                            <input type="text" class="form-control bg-light" readonly
                                value="{{ $responsableLabel ?: '—' }}">
                            <input type="hidden" name="usuarioid" value="{{ $lote->usuarioid }}">
                        </div>
                        @endif

                        <div class="form-group">
                            <label><i class="fas fa-tag mr-1"></i> Nombre del Lote <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" maxlength="100" required
                                value="{{ $lote->nombre }}">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt mr-1"></i> Ubicacion (texto)</label>
                            <input type="text" name="ubicacion" class="form-control" maxlength="200"
                                value="{{ $lote->ubicacion }}">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-ruler-combined mr-1"></i> Superficie (hectareas) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="superficie" id="superficie" class="form-control" min="0"
                                required value="{{ $lote->superficie }}">
                        </div>

                        @include('partials.selector-catalogo', [
                            'id' => 'lote_edit_cultivo',
                            'name' => 'cultivoid',
                            'label' => 'Cultivo',
                            'icon' => 'fa-seedling',
                            'value' => $lote->cultivoid ?? '',
                            'labelSelected' => $cultivoLabel ?? '',
                            'endpoint' => route('catalogo-selector.cultivos'),
                            'allowEmpty' => true,
                            'emptyLabel' => '— Sin cultivo —',
                            'title' => 'Seleccionar cultivo',
                            'searchPlaceholder' => 'Nombre del cultivo…',
                        ])

                        <div class="form-group">
                            <label><i class="fas fa-id-badge mr-1"></i> Código de trazabilidad</label>
                            <input type="text" class="form-control bg-light" readonly
                                value="{{ $lote->codigo_trazabilidad ?? '—' }}">
                            <small class="text-muted">Asignado automáticamente al crear el lote.</small>
                        </div>

                        @if($mostrarFechaSiembra)
                        <div class="form-group">
                            <label><i class="fas fa-calendar mr-1"></i> Fecha de Siembra</label>
                            <input type="text" class="form-control bg-light" readonly
                                value="{{ \Carbon\Carbon::parse($lote->fechasiembra)->format('d/m/Y') }}">
                            <small class="text-muted">Se registra al completar la actividad de siembra.</small>
                        </div>
                        @endif

                        <div class="form-group">
                            <label><i class="fas fa-flag mr-1"></i> Estado del Lote</label>
                            <input type="text" class="form-control bg-light" readonly
                                value="{{ ucfirst($lote->estadoTipo->nombre ?? '—') }}">
                            <small class="text-muted">Cámbialo desde el listado de lotes con el botón de estado.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-map mr-1"></i> Ubicacion en el Mapa</label>
                            <small class="text-muted d-block mb-2">Haz clic en el mapa para cambiar la ubicacion</small>
                            <div id="map"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Latitud</label>
                                <input type="number" step="0.0000001" name="latitud" id="latitud" class="form-control"
                                    min="-90" max="90" value="{{ $lote->latitud }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Longitud</label>
                                <input type="number" step="0.0000001" name="longitud" id="longitud" class="form-control"
                                    min="-180" max="180" value="{{ $lote->longitud }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('lotes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>
                        Cancelar</a>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Actualizar Lote</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var initialLat = {{ $lote->latitud ?? -17.7833 }};
        var initialLng = {{ $lote->longitud ?? -63.1821 }};
        var superficie = {{ $lote->superficie ?? 0 }};

        var map = L.map('map').setView([initialLat, initialLng], {{ $lote->latitud ? 14 : 10 }});
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

        var marker = null;
        var circle = null;

        function calcularRadio(ha) { return Math.sqrt(ha * 10000 / Math.PI); }

        @if($lote->latitud && $lote->longitud)
            marker = L.marker([initialLat, initialLng]).addTo(map).bindPopup('{{ $lote->nombre }}').openPopup();
            if (superficie > 0) {
                circle = L.circle([initialLat, initialLng], { color: 'green', fillColor: '#28a745', fillOpacity: 0.3, radius: calcularRadio(superficie) }).addTo(map);
            }
        @endif

        map.on('click', function (e) {
            var lat = e.latlng.lat.toFixed(7);
            var lng = e.latlng.lng.toFixed(7);
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;

            if (marker) map.removeLayer(marker);
            if (circle) map.removeLayer(circle);

            marker = L.marker([lat, lng]).addTo(map).bindPopup('Nueva ubicacion').openPopup();

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
    </script>
@endpush
