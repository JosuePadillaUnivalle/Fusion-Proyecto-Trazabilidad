@php

    $campos = $guias['campos'] ?? [];

    $esEdicion = isset($almacen);

    $ubicacionValor = old('ubicacion', $almacen->ubicacion ?? '');

    $nombreValor = old('nombre', $almacen->nombre ?? '');

    $descValor = old('descripcion', $almacen->descripcion ?? '');

    $capValor = old('capacidad', $almacen->capacidad ?? '');

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

#mapaAlmacenUbicacion { height: 320px; border-radius: 8px; }

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



    <div class="form-group">

        <label for="nombre">Nombre <span class="text-danger">*</span></label>

        <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"

               maxlength="100" value="{{ $nombreValor }}" required placeholder="Ej: Almacén Norte">

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



    <div class="form-group">

        <label for="ubicacion">Ubicación</label>

        <div class="input-group">

            <div class="input-group-prepend">

                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>

            </div>

            <input type="text" name="ubicacion" id="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror"

                   maxlength="200" value="{{ $ubicacionValor }}" placeholder="Dirección o referencia física">

            <div class="input-group-append">

                <button type="button" id="btn-abrir-mapa-ubicacion" class="btn btn-outline-success">

                    <i class="fas fa-map mr-1"></i>Buscar ubicación

                </button>

            </div>

        </div>

        @if(!empty($campos['ubicacion']))<div class="guia-campo">{{ $campos['ubicacion'] }}</div>@endif

        <small id="ubicacion_detalle_hint" class="text-muted d-block mt-1">

            Usá <strong>Buscar ubicación</strong> para abrir el mapa y marcar el punto con un clic.

        </small>

        @error('ubicacion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

    </div>



    <div class="form-group">

        <label for="capacidad">Capacidad máxima (kg)</label>

        <div class="input-group">

            <input type="number" step="0.01" min="0" name="capacidad" id="capacidad"

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



<div class="modal fade" id="modalMapaAlmacen" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header bg-success text-white py-2">

                <h5 class="modal-title"><i class="fas fa-map mr-1"></i> Ubicación del almacén</h5>

                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>

            </div>

            <div class="modal-body p-2">

                <p class="small text-muted mb-2 px-1">Hacé clic en el mapa para marcar el punto. Podés arrastrar el marcador.</p>

                <div id="mapaAlmacenUbicacion"></div>

            </div>

            <div class="modal-footer py-2">

                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>

                <button type="button" class="btn btn-success btn-sm" id="btnConfirmarMapaAlmacen">Usar esta ubicación</button>

            </div>

        </div>

    </div>

</div>



@push('scripts')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

$(function () {

    const inputUbic = document.getElementById('ubicacion');

    const hint = document.getElementById('ubicacion_detalle_hint');

    let mapaAlmacen = null;

    let marcadorAlmacen = null;

    let latPendiente = -17.7833;

    let lngPendiente = -63.1821;



    const coordsInicial = @json(\App\Support\UbicacionGpsParser::fromTexto($ubicacionValor));

    if (coordsInicial) {

        latPendiente = coordsInicial.lat;

        lngPendiente = coordsInicial.lng;

    }



    function initMapaAlmacen() {

        if (mapaAlmacen) {

            mapaAlmacen.invalidateSize();

            return;

        }

        mapaAlmacen = L.map('mapaAlmacenUbicacion').setView([latPendiente, lngPendiente], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapaAlmacen);



        function colocarMarcador(lat, lng) {

            latPendiente = lat;

            lngPendiente = lng;

            if (marcadorAlmacen) mapaAlmacen.removeLayer(marcadorAlmacen);

            marcadorAlmacen = L.marker([lat, lng], { draggable: true }).addTo(mapaAlmacen);

            marcadorAlmacen.on('dragend', function (e) {

                const p = e.target.getLatLng();

                latPendiente = p.lat;

                lngPendiente = p.lng;

            });

        }



        mapaAlmacen.on('click', function (e) {

            colocarMarcador(e.latlng.lat, e.latlng.lng);

        });



        colocarMarcador(latPendiente, lngPendiente);

    }



    $('#btn-abrir-mapa-ubicacion').on('click', function () {

        $('#modalMapaAlmacen').modal('show');

    });



    $('#modalMapaAlmacen').on('shown.bs.modal', function () {

        setTimeout(initMapaAlmacen, 200);

    });



    $('#btnConfirmarMapaAlmacen').on('click', function () {

        if (inputUbic) {

            inputUbic.value = 'GPS ' + Number(latPendiente).toFixed(5) + ', ' + Number(lngPendiente).toFixed(5);

        }

        if (hint) hint.textContent = 'Ubicación fijada desde el mapa.';

        $('#modalMapaAlmacen').modal('hide');

    });



    if (inputUbic) {

        inputUbic.addEventListener('input', function () {

            if (hint && inputUbic.value.trim() === '') {

                hint.textContent = 'Usá Buscar ubicación para abrir el mapa y marcar el punto con un clic.';

            }

        });

    }

});

</script>

@endpush

