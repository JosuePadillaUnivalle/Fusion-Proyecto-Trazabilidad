@extends('layouts.app')

@section('title', 'Registrar Venta | AgroNexus')
@section('page_title', 'Registrar Venta')

@section('content')
<div class="card">

    <div class="card-header bg-purple text-white" style="background: #6f42c1 !important;">
        <h3 class="card-title"><i class="fas fa-dollar-sign mr-2"></i>Registrar Venta</h3>
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

    <form action="{{ route('ventas.store') }}" method="POST">
        @csrf

        <div class="card-body">
            <div class="row">
                <div class="col-md-8">

                    <div class="form-group">
                        <label><i class="fas fa-warehouse mr-1"></i> Seleccionar Producto del Almacen <span class="text-danger">*</span></label>
                        <select name="produccionid" id="produccionid" class="form-control" required>
                            <option value="">-- Seleccione producto en almacen --</option>
                            @foreach($producciones as $p)
                                <option value="{{ $p->produccionid }}"
                                        data-disponible="{{ $p->stock_disponible }}"
                                        data-unidad="{{ $p->unidadMedida->abreviatura ?? 'kg' }}"
                                        data-cultivo="{{ $p->lote->cultivo->nombre ?? 'Sin cultivo' }}"
                                        data-lote="{{ $p->lote->nombre ?? '' }}"
                                        data-almacen="{{ $p->almacen_nombre }}">
                                    {{ $p->lote->cultivo->nombre ?? 'Producto' }} - {{ $p->almacen_nombre }}
                                    (Stock: {{ $p->stock_disponible }} {{ $p->unidadMedida->abreviatura ?? 'kg' }})
                                </option>
                            @endforeach
                        </select>
                        @if($producciones->isEmpty())
                            <small class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle"></i> No hay productos en almacen disponibles para venta.
                            </small>
                        @endif
                    </div>

                    <div id="produccionInfo" class="alert alert-info" style="display: none;">
                        <strong><i class="fas fa-info-circle mr-1"></i> Producto:</strong>
                        <span id="infoCultivo"></span> del lote <span id="infoLote"></span><br>
                        <strong>Almacen:</strong> <span id="infoAlmacen"></span><br>
                        <strong>Stock disponible:</strong> <span id="infoDisponible" class="text-success font-weight-bold"></span>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-user mr-1"></i> Cliente <span class="text-danger">*</span></label>
                        <input type="text" name="cliente" class="form-control" required 
                               maxlength="100" placeholder="Nombre del comprador" value="{{ old('cliente') }}">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-balance-scale mr-1"></i> Cantidad a Vender <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="cantidad" id="cantidad"
                                   class="form-control" min="0.01" required value="{{ old('cantidad') }}"
                                   placeholder="Ej: 100">
                            <div class="input-group-append">
                                <select name="unidadmedidaid" id="unidadmedidaid" class="form-control" required>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->unidadmedidaid }}" {{ $u->es_base ? 'selected' : '' }}>
                                            {{ $u->abreviatura }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Maximo disponible en almacen: <span id="maxDisponible" class="font-weight-bold">-</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-tag mr-1"></i> Precio Unitario (Bs.) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Bs.</span>
                            </div>
                            <input type="number" step="0.01" name="preciounitario" id="preciounitario"
                                   class="form-control" min="0" required value="{{ old('preciounitario') }}"
                                   placeholder="Ej: 15.00">
                            <div class="input-group-append">
                                <span class="input-group-text">por <span id="unidadPrecio">kg</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-comment mr-1"></i> Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2" 
                                  maxlength="200" placeholder="Opcional...">{{ old('observaciones') }}</textarea>
                    </div>

                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Informacion</h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-2">
                                <i class="fas fa-calendar mr-1"></i> <strong>Fecha:</strong> Se registra automaticamente
                            </p>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-calculator mr-1"></i> <strong>Total:</strong> Se calcula automaticamente
                            </p>
                        </div>
                    </div>

                    <div class="card mt-3 border-success">
                        <div class="card-body text-center">
                            <small class="text-muted">Total Estimado</small>
                            <h2 class="text-success mb-0" id="totalEstimado">Bs. 0.00</h2>
                        </div>
                    </div>

                    <div class="card mt-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-exchange-alt mr-1"></i> Flujo</h6>
                        </div>
                        <div class="card-body small">
                            <p class="mb-1"><i class="fas fa-check text-success"></i> Cosecha registrada</p>
                            <p class="mb-1"><i class="fas fa-check text-success"></i> Enviado a almacen</p>
                            <p class="mb-0"><i class="fas fa-arrow-right text-info"></i> <strong>Venta desde almacen</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" style="background: #6f42c1; border-color: #6f42c1;" 
                        {{ $producciones->isEmpty() ? 'disabled' : '' }}>
                    <i class="fas fa-save mr-1"></i> Registrar Venta
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#produccionid').on('change', function() {
        const selected = $(this).find(':selected');
        if (selected.val()) {
            const disponible = selected.data('disponible');
            const unidad = selected.data('unidad');
            
            $('#infoCultivo').text(selected.data('cultivo'));
            $('#infoLote').text(selected.data('lote'));
            $('#infoAlmacen').text(selected.data('almacen'));
            $('#infoDisponible').text(disponible + ' ' + unidad);
            $('#maxDisponible').text(disponible + ' ' + unidad);
            $('#cantidad').attr('max', disponible);
            $('#produccionInfo').slideDown();
        } else {
            $('#produccionInfo').slideUp();
        }
        calcularTotal();
    });

    $('#unidadmedidaid').on('change', function() {
        const unidad = $(this).find(':selected').text();
        $('#unidadPrecio').text(unidad);
    });

    $('#cantidad, #preciounitario').on('input', function() {
        calcularTotal();
    });

    function calcularTotal() {
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const precio = parseFloat($('#preciounitario').val()) || 0;
        const total = cantidad * precio;
        $('#totalEstimado').text('Bs. ' + total.toFixed(2));
    }

    // SMART UNIT CONVERSION
    function checkSmartConversion() {
        const cantidadInput = $('#cantidad');
        const unidadSelect = $('#unidadmedidaid');
        const cantidad = parseFloat(cantidadInput.val()) || 0;
        const unidadOption = unidadSelect.find('option:selected');
        const unidadNombre = unidadOption.text().toLowerCase();
        
        // Limpiar sugerencias
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

        $('#btnAplicarConversion').on('click', function(e) {
            e.preventDefault();
            $('#cantidad').val(nuevoValor);
            
            // Buscar y seleccionar nueva unidad
            $('#unidadmedidaid option').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.includes(keywordNuevaUnidad)) {
                    $(this).prop('selected', true);
                    return false; 
                }
            });

            $('#smartConversionAlert').remove();
            calcularTotal(); // Recalcular total si es venta
        });
    }

    $('#cantidad, #unidadmedidaid').on('change keyup blur', function() {
        checkSmartConversion();
    });
});
</script>
@endpush