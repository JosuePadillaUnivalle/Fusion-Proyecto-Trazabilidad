@extends('layouts.app')

@section('content')
    <div class="card">

        <div class="card-header">
            <h3 class="card-title">Registrar Almacenamiento de Producción</h3>
        </div>

        <form action="{{ route('producciones_almacenamiento.store') }}" method="POST">
            @csrf
            @if(!empty($returnUrl))
                <input type="hidden" name="return" value="{{ $returnUrl }}">
            @endif

            <div class="card-body">

                <div class="form-group">
                    <label>Producción</label>
                    <select name="produccionid" class="form-control" required>
                        <option value="">Seleccione...</option>
                        @foreach($producciones as $p)
                            <option value="{{ $p->produccionid }}"
                                @selected(old('produccionid', $produccionPreseleccionada ?? null) == $p->produccionid)>
                                #{{ $p->produccionid }}
                                @if($p->lote)
                                    - Lote: {{ $p->lote->nombre }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Almacén</label>
                    <select name="almacenid" class="form-control" required>
                        <option value="">Seleccione...</option>
                        @foreach($almacenes as $a)
                            <option value="{{ $a->almacenid }}">{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Cantidad</label>
                    <input type="number" step="0.01" min="0.01" name="cantidad" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Unidad de medida</label>
                    <select name="unidadmedidaid" class="form-control">
                        <option value="">Seleccione...</option>
                        @foreach($unidades as $u)
                            <option value="{{ $u->unidadmedidaid }}">{{ $u->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <hr>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Temperatura actual (°C)</label>
                        <input type="number" step="0.01" name="temperatura" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Humedad actual (%)</label>
                        <input type="number" step="0.01" name="humedad" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Temperatura mínima recomendada (°C)</label>
                        <input type="number" step="0.01" name="temperatura_min" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Temperatura máxima recomendada (°C)</label>
                        <input type="number" step="0.01" name="temperatura_max" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Humedad mínima recomendada (%)</label>
                        <input type="number" step="0.01" name="humedad_min" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Humedad máxima recomendada (%)</label>
                        <input type="number" step="0.01" name="humedad_max" class="form-control">
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Fecha de entrada</label>
                    <input type="datetime-local" name="fechaentrada" class="form-control">
                </div>

                <div class="form-group">
                    <label>Fecha de salida (opcional)</label>
                    <input type="datetime-local" name="fechasalida" class="form-control">
                </div>

                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" class="form-control" maxlength="250"></textarea>
                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ $returnUrl ?? route('producciones_almacenamiento.index') }}" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary">Guardar</button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
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
                $('#btnAplicarConversion').on('click', function (e) {
                    e.preventDefault();
                    $('input[name="cantidad"]').val(nuevoValor);
                    $('select[name="unidadmedidaid"] option').each(function () {
                        const text = $(this).text().toLowerCase();
                        if (text.includes(keywordNuevaUnidad)) {
                            $(this).prop('selected', true);
                            return false;
                        }
                    });
                    $('#smartConversionAlert').remove();
                });
            }

            $('input[name="cantidad"], select[name="unidadmedidaid"]').on('change keyup blur', function () {
                checkSmartConversion();
            });
        });
    </script>
@endpush