@extends('layouts.app')

@section('title', 'Editar Almacén | AgroNexus')
@section('page_title', 'Editar Almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}" style="color: #2c5530;">Almacenes</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0 font-weight-bold"><i class="fas fa-edit mr-2"></i>Editar Almacén: {{ $almacen->nombre }}
                    </h4>
                </div>

                <form action="{{ route('almacenes.update', $almacen) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body p-4">
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="nombre" class="font-weight-bold text-dark">Nombre del Almacén <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control form-control-lg" maxlength="100"
                                    value="{{ $almacen->nombre }}" required pattern="^[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ\.]+$">
                                <small class="text-muted">Solo letras, números y espacios.</small>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="tipoalmacenid" class="font-weight-bold text-dark">Tipo</label>
                                <select name="tipoalmacenid" class="form-control form-control-lg">
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $t)
                                        <option value="{{ $t->tipoalmacenid }}" {{ $almacen->tipoalmacenid == $t->tipoalmacenid ? 'selected' : '' }}>
                                            {{ $t->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion" class="font-weight-bold text-dark">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="250"
                                pattern="^[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ\.]+$">{{ $almacen->descripcion }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="ubicacion" class="font-weight-bold text-dark">Ubicación Física</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                </div>
                                <input type="text" name="ubicacion" class="form-control" maxlength="200"
                                    value="{{ $almacen->ubicacion }}" pattern="^[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ\.]+$">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="capacidad" class="font-weight-bold text-dark">Capacidad Total</label>
                                <input type="number" step="0.01" min="0" name="capacidad" class="form-control"
                                    value="{{ $almacen->capacidad }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="unidadmedidaid" class="font-weight-bold text-dark">Unidad de Medida</label>
                                <select name="unidadmedidaid" class="form-control">
                                    <option value="">Seleccione...</option>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->unidadmedidaid }}" {{ $almacen->unidadmedidaid == $u->unidadmedidaid ? 'selected' : '' }}>
                                            {{ $u->nombre }} ({{ $u->abreviatura }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="activoCheck" name="activo" value="1"
                                    {{ $almacen->activo ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="activoCheck">Almacén Activo y
                                    Operativo</label>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer bg-light text-right py-3">
                        <a href="{{ route('almacenes.index') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Actualizar Almacén
                        </button>
                    </div>

                </form>
            </div>
        </div>
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

                if ($('#smartConversionAlert').length === 0) {
                    inputElement.closest('.form-group').append(alertHtml);
                }

                $('#btnAplicarConversion').on('click', function (e) {
                    e.preventDefault();
                    $('input[name="capacidad"]').val(nuevoValor);

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