@php
    $campos = $guias['campos'] ?? [];
    $tiposAyuda = $tiposAyuda ?? ($guias['tipos'] ?? []);
    $esEdicion = isset($almacen);
    $ubicacionValor = old('ubicacion', $almacen->ubicacion ?? '');
    $dirLogId = old('direccionlogisticaid', $almacen->direccionlogisticaid ?? '');
    $nombreValor = old('nombre', $almacen->nombre ?? '');
    $descValor = old('descripcion', $almacen->descripcion ?? '');
    $capValor = old('capacidad', $almacen->capacidad ?? '');
    $tipoSel = old('tipoalmacenid', $almacen->tipoalmacenid ?? '');
    $unidadSel = old('unidadmedidaid', $almacen->unidadmedidaid ?? '');
    $activoChecked = old('activo', $esEdicion ? (bool) $almacen->activo : true);
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

<div class="alert alert-info mb-3">
    <i class="fas fa-info-circle mr-1"></i>
    Elija la <strong>ubicación</strong> del catálogo cuando exista en logística u otros almacenes; así mantiene coherencia con envíos y pedidos.
</div>

<div class="card card-outline card-secondary collapsed-card mb-3">
    <div class="card-header py-2">
        <h3 class="card-title small mb-0">
            <i class="fas fa-question-circle text-info mr-1"></i> Guía rápida de campos
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body py-2 small" style="display:none;">
        <div class="row">
            @foreach(['nombre', 'descripcion', 'ubicacion', 'capacidad', 'unidadmedidaid', 'tipoalmacenid', 'activo'] as $clave)
                @if(!empty($campos[$clave]))
                    <div class="col-md-6 mb-2">
                        <strong class="text-capitalize">{{ str_replace('_', ' ', $clave === 'unidadmedidaid' ? 'unidad de capacidad' : $clave) }}:</strong>
                        {{ $campos[$clave] }}
                    </div>
                @endif
            @endforeach
        </div>
        @if($tiposAyuda !== [])
            <hr class="my-2">
            <p class="mb-1 font-weight-bold">Tipos de almacén:</p>
            <ul class="mb-0 pl-3">
                @foreach($tiposAyuda as $nombreTipo => $desc)
                    <li><strong>{{ $nombreTipo }}:</strong> {{ $desc }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<div class="form-group">
    <label for="nombre">Nombre <span class="text-danger">*</span></label>
    <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
           maxlength="100" value="{{ $nombreValor }}" required>
    @if(!empty($campos['nombre']))<p class="field-hint mb-0">{{ $campos['nombre'] }}</p>@endif
    @error('nombre')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="descripcion">Descripción</label>
    <input type="text" name="descripcion" id="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
           maxlength="250" value="{{ $descValor }}">
    @if(!empty($campos['descripcion']))<p class="field-hint mb-0">{{ $campos['descripcion'] }}</p>@endif
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
            <button type="button" id="btn-abrir-selector-ubicacion" class="btn btn-outline-primary">
                <i class="fas fa-external-link-alt mr-1"></i>Buscar en otra pantalla
            </button>
        </div>
    </div>
    <input type="hidden" name="direccionlogisticaid" id="direccionlogisticaid" value="{{ $dirLogId }}">
    @if(!empty($campos['ubicacion']))<p class="field-hint mb-1">{{ $campos['ubicacion'] }}</p>@endif
    <small id="ubicacion_detalle_hint" class="text-muted d-block mt-1">Use el botón para abrir el selector con filtros por categoría y texto.</small>
    @error('ubicacion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @error('direccionlogisticaid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="capacidad">Capacidad</label>
        <input type="number" step="0.01" min="0" name="capacidad" id="capacidad"
               class="form-control @error('capacidad') is-invalid @enderror" value="{{ $capValor }}">
        @if(!empty($campos['capacidad']))<p class="field-hint mb-0">{{ $campos['capacidad'] }}</p>@endif
        @error('capacidad')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-6">
        <label for="unidadmedidaid">Unidad de medida de capacidad</label>
        <select name="unidadmedidaid" id="unidadmedidaid" class="form-control @error('unidadmedidaid') is-invalid @enderror">
            <option value="">Seleccione...</option>
            @foreach($unidades as $u)
                <option value="{{ $u->unidadmedidaid }}" @selected((string) $unidadSel === (string) $u->unidadmedidaid)>
                    {{ $u->nombre }}@if($u->abreviatura) ({{ $u->abreviatura }})@endif
                </option>
            @endforeach
        </select>
        @if(!empty($campos['unidadmedidaid']))<p class="field-hint mb-0">{{ $campos['unidadmedidaid'] }}</p>@endif
        @error('unidadmedidaid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-group">
    <label for="tipoalmacenid">Tipo de almacén</label>
    <select name="tipoalmacenid" id="tipoalmacenid" class="form-control @error('tipoalmacenid') is-invalid @enderror">
        <option value="">Seleccione...</option>
        @foreach($tipos as $t)
            <option value="{{ $t->tipoalmacenid }}"
                    data-ayuda="{{ $tiposAyuda[$t->nombre] ?? '' }}"
                    @selected((string) $tipoSel === (string) $t->tipoalmacenid)>{{ $t->nombre }}</option>
        @endforeach
    </select>
    @if(!empty($campos['tipoalmacenid']))<p class="field-hint mb-1">{{ $campos['tipoalmacenid'] }}</p>@endif
    <div id="tipo-almacen-ayuda" class="alert alert-light py-2 px-3 mb-0 small" style="display:none;"></div>
    @error('tipoalmacenid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="form-group form-check">
    <input type="checkbox" name="activo" value="1" class="form-check-input" id="activoCheck"
           @checked($activoChecked)>
    <label class="form-check-label" for="activoCheck">Activo</label>
    @if(!empty($campos['activo']))<p class="field-hint mb-0">{{ $campos['activo'] }}</p>@endif
</div>

@push('styles')
<style>
.field-hint{font-size:.8rem;color:#6c757d;margin-top:.25rem}
#tipo-almacen-ayuda{border-left:3px solid #17a2b8}
</style>
@endpush

@push('scripts')
<script>
$(function () {
    const inputUbic = document.getElementById('ubicacion');
    const inputDir = document.getElementById('direccionlogisticaid');
    const hint = document.getElementById('ubicacion_detalle_hint');
    const btnSelector = document.getElementById('btn-abrir-selector-ubicacion');
    const tipoSelect = document.getElementById('tipoalmacenid');
    const tipoAyuda = document.getElementById('tipo-almacen-ayuda');

    function aplicarUbicacionSeleccionada(payload) {
        if (!payload || !inputUbic) return;
        if (typeof payload.ubicacion === 'string') {
            inputUbic.value = payload.ubicacion;
        }
        if (inputDir) inputDir.value = payload.direccionlogisticaid || '';
        if (hint) hint.textContent = payload.direccionlogisticaid
            ? 'Ubicación vinculada a dirección logística existente.'
            : 'Ubicación seleccionada/manual sin vínculo de dirección logística.';
    }

    if (btnSelector) {
        btnSelector.addEventListener('click', function () {
            const url = @json(route('almacenes.selector-ubicacion'));
            window.open(url, '_blank');
        });
    }

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin) return;
        const data = event.data || {};
        if (data.type === 'almacen-ubicacion-seleccionada') {
            aplicarUbicacionSeleccionada(data.payload || {});
        }
    });

    window.addEventListener('focus', function () {
        try {
            const raw = localStorage.getItem('almacen_ubicacion_selector');
            if (!raw) return;
            const payload = JSON.parse(raw);
            aplicarUbicacionSeleccionada(payload);
            localStorage.removeItem('almacen_ubicacion_selector');
        } catch (e) {
            // No interrumpir formulario si localStorage falla.
        }
    });

    if (inputUbic) {
        inputUbic.addEventListener('input', function () {
            if (inputDir) inputDir.value = '';
            if (hint) {
                hint.textContent = inputUbic.value.trim() === ''
                    ? 'Use el botón para abrir el selector con filtros por categoría y texto.'
                    : 'Ubicación escrita manualmente.';
            }
        });
    }

    function actualizarAyudaTipo() {
        if (!tipoSelect || !tipoAyuda) return;
        const opt = tipoSelect.options[tipoSelect.selectedIndex];
        const txt = opt ? (opt.getAttribute('data-ayuda') || '') : '';
        if (txt) {
            tipoAyuda.style.display = '';
            tipoAyuda.innerHTML = '<i class="fas fa-warehouse text-info mr-1"></i>' + txt;
        } else {
            tipoAyuda.style.display = 'none';
            tipoAyuda.innerHTML = '';
        }
    }
    if (tipoSelect) {
        tipoSelect.addEventListener('change', actualizarAyudaTipo);
        actualizarAyudaTipo();
    }

    function checkSmartConversion() {
        const cantidadInput = $('input[name="capacidad"]');
        const unidadSelect = $('select[name="unidadmedidaid"]');
        const cantidad = parseFloat(cantidadInput.val()) || 0;
        const unidadNombre = unidadSelect.find('option:selected').text().toLowerCase();
        $('#smartConversionAlert').remove();
        if (unidadNombre.includes('kilo') || unidadNombre.includes('kg')) {
            if (cantidad >= 1000) {
                mostrarSugerenciaConversion(cantidadInput, 'Ton', cantidad / 1000, 'tonelada');
            }
        } else if (unidadNombre.includes('gramo') || unidadNombre.includes(' gr')) {
            if (cantidad >= 1000) {
                mostrarSugerenciaConversion(cantidadInput, 'Kg', cantidad / 1000, 'kilo');
            }
        }
    }

    function mostrarSugerenciaConversion(inputElement, nuevaUnidadTexto, nuevoValor, keywordNuevaUnidad) {
        const alertHtml = `
            <div id="smartConversionAlert" class="alert alert-info p-2 mt-2 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 8px;">
                <div>
                    <i class="fas fa-lightbulb text-info mr-2"></i>
                    <strong>Sugerencia:</strong> ¿Convertir a <strong>${nuevoValor.toFixed(2)} ${nuevaUnidadTexto}</strong>?
                </div>
                <button type="button" class="btn btn-sm btn-light border font-weight-bold" id="btnAplicarConversion">
                    Sí, cambiar
                </button>
            </div>`;
        if ($('#smartConversionAlert').length === 0) {
            inputElement.closest('.form-group, .form-row').first().append(alertHtml);
        }
        $('#btnAplicarConversion').off('click').on('click', function (e) {
            e.preventDefault();
            $('input[name="capacidad"]').val(nuevoValor.toFixed(2));
            $('select[name="unidadmedidaid"] option').each(function () {
                if ($(this).text().toLowerCase().includes(keywordNuevaUnidad)) {
                    $(this).prop('selected', true);
                    return false;
                }
            });
            $('#smartConversionAlert').remove();
        });
    }

    $('input[name="capacidad"], select[name="unidadmedidaid"]').on('change keyup blur', checkSmartConversion);
});
</script>
@endpush
