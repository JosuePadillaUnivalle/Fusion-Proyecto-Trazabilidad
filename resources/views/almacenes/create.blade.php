@extends('layouts.app')

@section('content')
    <div class="card">

        <div class="card-header">
            <h3 class="card-title">Registrar Almacén</h3>
        </div>

        <form action="{{ route('almacenes.store') }}" method="POST">
            @csrf

            <div class="card-body">

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" class="form-control" maxlength="250">
                </div>

                <div class="form-group">
                    <label>Ubicación</label>
                    <input type="text" name="ubicacion" class="form-control" maxlength="200">
                </div>

                <div class="form-group">
                    <label>Capacidad</label>
                    <input type="number" step="0.01" min="0" name="capacidad" class="form-control">
                </div>

                <div class="form-group">
                    <label>Unidad de medida de capacidad</label>
                    <select name="unidadmedidaid" class="form-control">
                        <option value="">Seleccione...</option>
                        @foreach($unidades as $u)
                            <option value="{{ $u->unidadmedidaid }}">{{ $u->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Tipo de almacén</label>
                    <select name="tipoalmacenid" class="form-control">
                        <option value="">Seleccione...</option>
                        @foreach($tipos as $t)
                            <option value="{{ $t->tipoalmacenid }}">{{ $t->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" name="activo" value="1" class="form-check-input" id="activoCheck" checked>
                    <label class="form-check-label" for="activoCheck">Activo</label>
                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('almacenes.index') }}" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary">Guardar</button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // SMART UNIT CONVERSION (Lógica Reutilizada)
            function checkSmartConversion() {
                const cantidadInput = $('input[name="capacidad"]');
                const unidadSelect = $('select[name="unidadmedidaid"]');

                const cantidad = parseFloat(cantidadInput.val()) || 0;
                const unidadOption = unidadSelect.find('option:selected');
                const unidadNombre = unidadOption.text().toLowerCase();
                // A veces la abreviatura no está en data, inferimos del texto

                $('#smartConversionAlert').remove();

                // KG -> TON
                if (unidadNombre.includes('kilo') || unidadNombre.includes('kg')) {
                    if (cantidad >= 1000) {
                        const toneladas = cantidad / 1000;
                        mostrarSugerenciaConversion(cantidadInput, 'Ton', toneladas, 'tonelada');
                    }
                }
                // GRAMOS -> KG
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

                // Evitar duplicados
                if ($('#smartConversionAlert').length === 0) {
                    inputElement.parent().append(alertHtml);
                }

                $('#btnAplicarConversion').on('click', function (e) {
                    e.preventDefault();
                    // Aplicar valor
                    $('input[name="capacidad"]').val(nuevoValor);

                    // Buscar y seleccionar la nueva unidad
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

            $('input[name="capacidad"], select[name="unidadmedidaid"]').on('change keyup blur', function () {
                checkSmartConversion();
            });
        });
    </script>
@endpush