@extends('layouts.app')

@section('title', 'Editar Estado | AgroFusion')
@section('page_title', 'Actualizar Almacenamiento')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('producciones_almacenamiento.index') }}" style="color: #2c5530;">Almacenamiento</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0 font-weight-bold"><i class="fas fa-edit mr-2"></i>Actualizar Registro de Almacenamiento</h4>
            </div>

            <form action="{{ route('producciones_almacenamiento.update', $registro) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body p-4">
                    
                    <div class="alert alert-info border-left-info shadow-sm">
                        <div class="row align-items-center">
                            <div class="col-auto"><i class="fas fa-info-circle fa-2x"></i></div>
                            <div class="col">
                                <h6 class="font-weight-bold mb-0">Información General</h6>
                                <small>Edite los valores de ambiente y movimiento. La producción origen no se puede cambiar.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Producción (Solo Lectura)</label>
                                <input type="text" class="form-control" value="Producción N°{{ $registro->produccionid }}" readonly disabled>
                                <input type="hidden" name="produccionid" value="{{ $registro->produccionid }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="almacenid" class="font-weight-bold">Almacén Ubicación</label>
                                <select name="almacenid" class="form-control" required>
                                    @foreach($almacenes as $a)
                                        <option value="{{ $a->almacenid }}"
                                            {{ $registro->almacenid == $a->almacenid ? 'selected' : '' }}>
                                            {{ $a->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidad" class="font-weight-bold">Cantidad Almacenada</label>
                                <input type="number" step="0.01" min="0.01" name="cantidad"
                                       class="form-control" value="{{ $registro->cantidad }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unidadmedidaid" class="font-weight-bold">Unidad</label>
                                <select name="unidadmedidaid" class="form-control">
                                    <option value="">Seleccione...</option>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->unidadmedidaid }}"
                                            {{ $registro->unidadmedidaid == $u->unidadmedidaid ? 'selected' : '' }}>
                                            {{ $u->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-secondary mt-4 mb-3 border-bottom pb-2">Condiciones Ambientales</h6>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold text-danger">Temperatura Actual (°C)</label>
                            <input type="number" step="0.01" name="temperatura"
                                   class="form-control" value="{{ $registro->temperatura }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold text-primary">Humedad Actual (%)</label>
                            <input type="number" step="0.01" name="humedad"
                                   class="form-control" value="{{ $registro->humedad }}">
                        </div>
                    </div>

                    <div class="form-row bg-light p-3 rounded">
                        <div class="form-group col-md-3">
                            <label class="small text-muted">Temp. Mín (°C)</label>
                            <input type="number" step="0.01" name="temperatura_min"
                                   class="form-control form-control-sm" value="{{ $registro->temperatura_min }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="small text-muted">Temp. Máx (°C)</label>
                            <input type="number" step="0.01" name="temperatura_max"
                                   class="form-control form-control-sm" value="{{ $registro->temperatura_max }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="small text-muted">Hum. Mín (%)</label>
                            <input type="number" step="0.01" name="humedad_min"
                                   class="form-control form-control-sm" value="{{ $registro->humedad_min }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="small text-muted">Hum. Máx (%)</label>
                            <input type="number" step="0.01" name="humedad_max"
                                   class="form-control form-control-sm" value="{{ $registro->humedad_max }}">
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-secondary mt-4 mb-3 border-bottom pb-2">Movimientos</h6>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Fecha de Entrada</label>
                            <input type="datetime-local" name="fechaentrada" class="form-control"
                                   value="{{ $registro->fechaentrada ? \Carbon\Carbon::parse($registro->fechaentrada)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Fecha de Salida</label>
                            <input type="datetime-local" name="fechasalida" class="form-control"
                                   value="{{ $registro->fechasalida ? \Carbon\Carbon::parse($registro->fechasalida)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3" maxlength="250" pattern="^[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ\.]+$">{{ $registro->observaciones }}</textarea>
                    </div>

                </div>

                <div class="card-footer bg-light text-right py-3">
                    <a href="{{ route('producciones_almacenamiento.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">Actualizar Registro</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // SMART UNIT CONVERSION
        function checkSmartConversion() {
            const cantidadInput = $('input[name="cantidad"]');
            const unidadSelect = $('select[name="unidadmedidaid"]');
            const cantidad = parseFloat(cantidadInput.val()) || 0;
            const unidadOption = unidadSelect.find('option:selected');
            const unidadNombre = unidadOption.text().toLowerCase();

            $('#smartConversionAlert').remove();

            if (unidadNombre.includes('kilo') || unidadNombre.includes('kg')) {
                if (cantidad >= 1000) {
                    const toneladas = cantidad / 1000;
                    mostrarSugerenciaConversion(cantidadInput, 'Ton', toneladas, 'tonelada');
                }
            }
            else if (unidadNombre.includes('gramo') || unidadNombre.includes(' gr')) {
                if (cantidad >= 1000) {
                    const kilos = cantidad / 1000;
                    mostrarSugerenciaConversion(cantidadInput, 'Kg', kilos, 'kilo');
                }
            }
        }

        function mostrarSugerenciaConversion(inputElement, nuevaUnidadTexto, nuevoValor, keywordNuevaUnidad) {
            const alertHtml = `
                <div id="smartConversionAlert" class="alert alert-info p-2 mt-2 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 8px;">
                    <div>
                        <i class="fas fa-lightbulb text-info mr-2"></i>
                        <strong>Sugerencia:</strong> ¿Convertir a <strong>${nuevoValor} ${nuevaUnidadTexto}</strong>?
                    </div>
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold" id="btnAplicarConversion">
                        Sí, cambiar
                    </button>
                </div>
            `;
            if ($('#smartConversionAlert').length === 0) {
                inputElement.closest('.form-group').append(alertHtml);
            }
            $('#btnAplicarConversion').on('click', function(e) {
                e.preventDefault();
                $('input[name="cantidad"]').val(nuevoValor);
                $('select[name="unidadmedidaid"] option').each(function() {
                    const text = $(this).text().toLowerCase();
                    if (text.includes(keywordNuevaUnidad)) {
                        $(this).prop('selected', true);
                        return false; 
                    }
                });
                $('#smartConversionAlert').remove();
            });
        }

        $('input[name="cantidad"], select[name="unidadmedidaid"]').on('change keyup blur', function() {
            checkSmartConversion();
        });
    });
</script>
@endpush