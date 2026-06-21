@php
    $t = $transportista ?? null;
    $numLicencia = old('licencia', $t?->perfilTransportista?->licencia ?? '');
    $licenciasActuales = old('licencias', $t?->licencias_json ?? $t?->perfilTransportista?->licencias_json ?? []);
    if (is_string($licenciasActuales)) {
        $decoded = json_decode($licenciasActuales, true);
        $licenciasActuales = is_array($decoded) ? $decoded : [];
    }
    if ($licenciasActuales === [] && ($t?->tipo_licencia ?? $t?->perfilTransportista?->tipo_licencia)) {
        $licenciasActuales = [$t->tipo_licencia ?? $t->perfilTransportista->tipo_licencia];
    }
    $licenciasTodas = (bool) old('licencias_todas', count(\App\Support\TiposLicenciaBolivia::normalizarLista($licenciasActuales)) === count(\App\Support\TiposLicenciaBolivia::codigosVehiculo()));
@endphp
<div class="col-md-8">
    <div class="form-group">
        @include('envios.partials.campo-licencias-checkboxes', [
            'licenciasActuales' => $licenciasActuales,
            'licenciasTodas' => $licenciasTodas,
            'inputPrefix' => 'edit_',
            'licenciasTema' => 'light',
        ])
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <label>Nº de licencia</label>
        <input name="licencia" class="form-control text-uppercase" value="{{ $numLicencia }}"
               placeholder="Ej. B-4521987" maxlength="50">
        <small class="text-muted">Número del documento de licencia.</small>
    </div>
</div>
