@php
    $punto = $punto ?? null;
    $esAdmin = \App\Support\UsuarioRol::esAdminGlobal(auth()->user());
    $esEdicion = $punto !== null;
    $latInicial = old('latitud', $punto?->latitud ?? -17.7833);
    $lngInicial = old('longitud', $punto?->longitud ?? -63.1821);
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-5">
        <p class="pdv-section-title"><i class="fas fa-store mr-1"></i> Datos del local</p>

        @if($esAdmin)
        <div class="form-group">
            <label><i class="fas fa-user mr-1"></i> Minorista responsable</label>
            @if($minoristas->isNotEmpty())
                <select name="usuarioid" id="usuarioid" class="form-control @error('usuarioid') is-invalid @enderror">
                    @php
                        $usuarioidActual = old('usuarioid', $punto?->usuarioid);
                        $esResponsableAdmin = ! $usuarioidActual
                            || (int) $usuarioidActual === (int) auth()->id()
                            || ! $minoristas->contains('usuarioid', (int) $usuarioidActual);
                    @endphp
                    <option value="" @selected($esResponsableAdmin)>— Yo (administrador) —</option>
                    @foreach($minoristas as $m)
                        <option value="{{ $m->usuarioid }}" @selected(old('usuarioid', $punto?->usuarioid) == $m->usuarioid)>
                            {{ trim($m->nombre.' '.$m->apellido) }} — {{ $m->email }}
                        </option>
                    @endforeach
                </select>
                <p class="pdv-campo-guia mb-0">Si no elige minorista, el punto quedará bajo su cuenta de administrador.</p>
            @else
                <input type="hidden" name="usuarioid" value="{{ auth()->id() }}">
                <div class="alert alert-warning py-2 mb-0 small">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    No hay minoristas aprobados. Este punto se registrará bajo su cuenta de administrador.
                    @can('usuarios.view')
                        <a href="{{ route('gestion.index') }}" class="alert-link">Gestionar usuarios</a>
                    @endcan
                </div>
            @endif
            @error('usuarioid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        @endif

        <div class="form-group">
            <label for="nombre"><i class="fas fa-tag mr-1"></i> Nombre del punto de venta <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
                value="{{ old('nombre', $punto?->nombre) }}" required maxlength="150"
                placeholder="Ej. Tienda Centro, Mercado Los Pozos">
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="direccion"><i class="fas fa-map-marker-alt mr-1"></i> Referencia de ubicación</label>
            <input type="text" name="direccion" id="direccion" class="form-control @error('direccion') is-invalid @enderror"
                maxlength="500" placeholder="Se completa al marcar el mapa"
                value="{{ old('direccion', $punto?->direccion) }}">
            <p class="pdv-campo-guia mb-0">Puede editarla después si necesita una descripción más clara.</p>
            @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="observaciones"><i class="fas fa-sticky-note mr-1"></i> Observaciones</label>
            <textarea name="observaciones" id="observaciones" rows="2" class="form-control"
                placeholder="Horario, referencias adicionales…">{{ old('observaciones', $punto?->observaciones) }}</textarea>
        </div>

        @if($esEdicion)
        <div class="card border-left border-warning shadow-sm mb-0">
            <div class="card-body py-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1"
                        @checked(old('activo', $punto?->activo ?? true))>
                    <label class="custom-control-label" for="activo">
                        <strong>Punto de venta activo</strong>
                    </label>
                </div>
                <p class="pdv-campo-guia mb-0 mt-1">Desactívelo para dar de baja el local sin eliminarlo del historial.</p>
            </div>
        </div>
        @else
        <div class="alert alert-light border small mb-0">
            <i class="fas fa-check-circle text-success mr-1"></i>
            El punto de venta se registrará como <strong>activo</strong>. Podrá desactivarlo más tarde desde Editar.
        </div>
        @endif
    </div>

    <div class="col-lg-7">
        <p class="pdv-section-title"><i class="fas fa-map mr-1"></i> Ubicación en mapa <span class="text-danger">*</span></p>
        <p class="pdv-campo-guia">Haga clic donde está el local (Santa Cruz por defecto). Las coordenadas se completan automáticamente.</p>
        <div id="pdvMap" class="pdv-map mb-2"></div>
        <div class="form-row">
            <div class="form-group col-6 mb-0">
                <label class="small text-muted">Latitud</label>
                <input type="number" step="0.0000001" name="latitud" id="latitud" class="form-control form-control-sm" required readonly
                    value="{{ $latInicial }}">
            </div>
            <div class="form-group col-6 mb-0">
                <label class="small text-muted">Longitud</label>
                <input type="number" step="0.0000001" name="longitud" id="longitud" class="form-control form-control-sm" required readonly
                    value="{{ $lngInicial }}">
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    function initPdvMap() {
        var mapEl = document.getElementById('pdvMap');
        if (!mapEl || !window.L) return;

        var latInput = document.getElementById('latitud');
        var lngInput = document.getElementById('longitud');
        var dirInput = document.getElementById('direccion');
        var lat = parseFloat(latInput.value) || -17.7833;
        var lng = parseFloat(lngInput.value) || -63.1821;

        var map = L.map('pdvMap').setView([lat, lng], latInput.value ? 14 : 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

        var marker = null;
        function colocarMarcador(nlat, nlng) {
            latInput.value = Number(nlat).toFixed(7);
            lngInput.value = Number(nlng).toFixed(7);
            if (!dirInput.value || dirInput.value.indexOf('PDV GPS') === 0) {
                dirInput.value = 'PDV GPS ' + Number(nlat).toFixed(5) + ', ' + Number(nlng).toFixed(5);
            }
            if (marker) map.removeLayer(marker);
            marker = L.marker([nlat, nlng]).addTo(map);
        }

        if (latInput.value && lngInput.value) {
            colocarMarcador(lat, lng);
        }

        map.on('click', function (e) {
            colocarMarcador(e.latlng.lat, e.latlng.lng);
        });

        setTimeout(function () { map.invalidateSize(); }, 200);
    }

    document.addEventListener('DOMContentLoaded', initPdvMap);
})();
</script>
@endpush
@endonce
