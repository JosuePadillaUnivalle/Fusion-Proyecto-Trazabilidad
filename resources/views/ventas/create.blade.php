@extends('layouts.app')

@section('title', 'Registrar Venta | Fusion-Proyectos')
@section('page_title', 'Registrar Venta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
    <li class="breadcrumb-item active">Registrar</li>
@endsection

@push('styles')
@include('partials.modulo-ventas-styles')
@endpush

@section('content')
<div class="modulo-ven">

    <div class="ven-page-header">
        <div class="ven-page-header-inner">
            <div class="ven-page-header-icon"><i class="fas fa-cash-register"></i></div>
            <div class="ven-page-header-text">
                <h2>Registrar venta desde almacén</h2>
                <p>Selecciona el producto en stock, indica cliente y cantidad. El sistema calcula el total y descuenta el almacén.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    @if(!$hayProductosConStock)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        No hay productos con stock en almacén. Primero registra una cosecha y envíala a almacén desde el módulo de Producción.
    </div>
    @endif

    <form action="{{ route('ventas.store') }}" method="POST" id="formVenta">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-outline card-success card-form-modulo elevation-1">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-edit mr-1"></i> Datos de la venta</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-warehouse mr-1 text-success"></i> Producto en almacén <span class="text-danger">*</span></label>
                            @if($hayProductosConStock)
                            @include('partials.selector-catalogo', [
                                'id' => 'venta_producto',
                                'name' => 'produccionid',
                                'value' => old('produccionid', ''),
                                'labelSelected' => $oldProduccionLabel,
                                'endpoint' => route('catalogo-selector.producciones-stock-almacen'),
                                'title' => 'Buscar producto con stock en almacén',
                                'searchPlaceholder' => 'Cultivo o lote…',
                                'required' => true,
                                'filterAlmacen' => [
                                    'param' => 'almacenid',
                                    'endpoint' => route('catalogo-selector.almacenes'),
                                    'placeholder' => 'Escriba el nombre del almacén (ej. Norte, Santa Cruz)…',
                                    'params' => ['con_stock' => '1'],
                                ],
                                'filter' => [
                                    'param' => 'cultivoid',
                                    'options' => array_merge(
                                        [['value' => '', 'label' => 'Todos los cultivos']],
                                        $cultivosFiltro->map(fn ($c) => [
                                            'value' => (string) $c->cultivoid,
                                            'label' => $c->nombre,
                                        ])->all()
                                    ),
                                ],
                            ])
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pulse <strong>Buscar</strong>: elija el almacén en la lista, filtre por cultivo y seleccione el producto.
                            </small>
                            @else
                            <input type="text" class="form-control" disabled placeholder="Sin productos con stock">
                            @endif
                        </div>

                        <div id="productoPreview" class="producto-preview" style="display:none;">
                            <div class="row">
                                <div class="col-sm-6"><span class="info-label">Cultivo / Lote</span><strong id="pvCultivoLote">—</strong></div>
                                <div class="col-sm-6"><span class="info-label">Almacén</span><strong id="pvAlmacen">—</strong></div>
                                <div class="col-sm-6 mt-2"><span class="info-label">Stock disponible</span><strong class="text-success" id="pvStock">—</strong></div>
                                <div class="col-sm-6 mt-2"><span class="info-label">Precio sugerido</span><strong id="pvPrecio">—</strong></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-user mr-1 text-success"></i> Cliente <span class="text-danger">*</span></label>
                            <input type="text" name="cliente" id="cliente" class="form-control" required maxlength="100"
                                placeholder="Nombre del comprador" value="{{ old('cliente') }}" {{ !$hayProductosConStock ? 'disabled' : '' }}>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-balance-scale mr-1 text-success"></i> Cantidad a vender <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="cantidad" id="cantidad" class="form-control" min="0.01" required
                                    value="{{ old('cantidad') }}" placeholder="Ej: 100" {{ !$hayProductosConStock ? 'disabled' : '' }}>
                                <div class="input-group-append">
                                    <select name="unidadmedidaid" id="unidadmedidaid" class="form-control" required {{ !$hayProductosConStock ? 'disabled' : '' }}>
                                        @foreach($unidades as $u)
                                        <option value="{{ $u->unidadmedidaid }}" @selected(old('unidadmedidaid', $u->es_base ? $u->unidadmedidaid : null) == $u->unidadmedidaid)>
                                            {{ $u->abreviatura }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center mt-2" style="gap:8px;">
                                <small class="text-muted mb-0">Máximo: <strong id="maxDisponible">—</strong></small>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnVenderTodo" disabled>
                                    <i class="fas fa-box-open mr-1"></i> Vender stock completo
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-tag mr-1 text-success"></i> Precio unitario (Bs.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>
                                <input type="number" step="0.01" name="preciounitario" id="preciounitario" class="form-control" min="0" required
                                    value="{{ old('preciounitario') }}" placeholder="Ej: 15.00" {{ !$hayProductosConStock ? 'disabled' : '' }}>
                                <div class="input-group-append"><span class="input-group-text">por <span id="unidadPrecio">kg</span></span></div>
                            </div>
                            <small class="text-muted" id="hintPrecio" style="display:none;">
                                <i class="fas fa-magic mr-1"></i> Precio sugerido según ventas anteriores del mismo producto o cultivo.
                            </small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold"><i class="fas fa-comment mr-1 text-success"></i> Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2" maxlength="200" placeholder="Opcional…" {{ !$hayProductosConStock ? 'disabled' : '' }}>{{ old('observaciones') }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('ventas.index') }}" class="btn btn-secondary"><i class="fas fa-times mr-1"></i> Cancelar</a>
                        <button type="submit" class="btn btn-success" {{ !$hayProductosConStock ? 'disabled' : '' }}>
                            <i class="fas fa-save mr-1"></i> Registrar venta
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel-lateral">
                    <div class="panel-header"><i class="fas fa-calculator mr-1"></i> Total estimado</div>
                    <div class="panel-body">
                        <div class="total-estimado-box">
                            <small class="text-muted d-block mb-1">Total a cobrar</small>
                            <div class="amount" id="totalEstimado">Bs. 0.00</div>
                            <small class="text-muted mt-2 d-block"><i class="far fa-calendar-alt mr-1"></i> Fecha: hoy (automática)</small>
                        </div>
                    </div>
                </div>

                <div class="panel-lateral">
                    <div class="panel-header"><i class="fas fa-route mr-1"></i> Flujo de la operación</div>
                    <div class="panel-body flujo-venta">
                        <div class="flujo-item"><i class="fas fa-check-circle text-success"></i> Cosecha registrada</div>
                        <div class="flujo-item"><i class="fas fa-check-circle text-success"></i> Enviado a almacén</div>
                        <div class="flujo-item active"><i class="fas fa-arrow-right"></i> Venta desde almacén (este paso)</div>
                        <div class="flujo-item text-muted"><i class="fas fa-circle" style="font-size:0.5rem;vertical-align:middle;"></i> Stock actualizado al guardar</div>
                    </div>
                </div>

                <div class="panel-lateral">
                    <div class="panel-header"><i class="fas fa-list-ol mr-1"></i> Pasos a seguir</div>
                    <div class="panel-body p-0">
                        <ul class="guia-pasos px-3 pb-2">
                            <li><span class="paso-num" id="paso1">1</span><span><strong>Elige el producto</strong> con stock en almacén.</span></li>
                            <li><span class="paso-num" id="paso2">2</span><span><strong>Indica cliente</strong> y cantidad (o usa «Vender stock completo»).</span></li>
                            <li><span class="paso-num" id="paso3">3</span><span><strong>Confirma el precio</strong> — se sugiere automáticamente si hay historial.</span></li>
                            <li><span class="paso-num" id="paso4">4</span><span><strong>Guarda</strong> y el stock se descuenta del almacén.</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var stockActual = 0;

    function calcularTotal() {
        var cantidad = parseFloat($('#cantidad').val()) || 0;
        var precio = parseFloat($('#preciounitario').val()) || 0;
        $('#totalEstimado').text('Bs. ' + (cantidad * precio).toFixed(2));
    }

    function marcarPasos() {
        var prodId = document.querySelector('#selector_wrap_venta_producto .selector-catalogo-value')?.value || '';
        var p1 = prodId ? 'done' : 'active';
        var p2 = $('#cliente').val() && $('#cantidad').val() ? 'done' : (prodId ? 'active' : '');
        var p3 = $('#preciounitario').val() ? 'done' : (p2 === 'done' ? 'active' : '');
        $('#paso1').attr('class', 'paso-num ' + (p1 === 'done' ? 'done' : 'active'));
        $('#paso2').attr('class', 'paso-num ' + (p2 || ''));
        $('#paso3').attr('class', 'paso-num ' + (p3 || ''));
        $('#paso4').attr('class', 'paso-num');
    }

    function aplicarProducto(extra) {
        if (!extra || !extra.disponible) {
            $('#productoPreview').slideUp();
            $('#btnVenderTodo').prop('disabled', true);
            stockActual = 0;
            $('#maxDisponible').text('—');
            marcarPasos();
            return;
        }

        stockActual = parseFloat(extra.disponible) || 0;
        var unidad = extra.unidad || 'kg';
        var unidadId = extra.unidad_id;
        var precio = extra.precio;

        $('#pvCultivoLote').text((extra.cultivo || '') + ' · ' + (extra.lote || ''));
        $('#pvAlmacen').text(extra.almacen || '—');
        $('#pvStock').text(stockActual.toFixed(2) + ' ' + unidad);
        $('#maxDisponible').text(stockActual.toFixed(2) + ' ' + unidad);
        $('#unidadPrecio').text(unidad);
        $('#cantidad').attr('max', stockActual);
        $('#btnVenderTodo').prop('disabled', stockActual <= 0);
        $('#productoPreview').slideDown();

        if (unidadId) {
            $('#unidadmedidaid').val(unidadId);
        }

        if (precio && !$('#preciounitario').val()) {
            $('#preciounitario').val(parseFloat(precio).toFixed(2));
            $('#pvPrecio').text('Bs. ' + parseFloat(precio).toFixed(2));
            $('#hintPrecio').show();
        } else if (precio) {
            $('#pvPrecio').text('Bs. ' + parseFloat(precio).toFixed(2));
            $('#hintPrecio').show();
        } else {
            $('#pvPrecio').text('Sin historial — ingresa manualmente');
            $('#hintPrecio').hide();
        }

        calcularTotal();
        marcarPasos();
        $('#cliente').focus();
    }

    if (window.CatalogoSelector) {
        var cfgBase = CatalogoSelector.instances.venta_producto || {};
        CatalogoSelector.register('venta_producto', Object.assign({}, cfgBase, {
            onSelect: function (item) {
                aplicarProducto(item.extra || {});
            },
        }));
    }

    document.getElementById('selector_wrap_venta_producto')?.addEventListener('selector-catalogo:change', function (e) {
        aplicarProducto(e.detail?.extra || {});
    });

    $('#btnVenderTodo').on('click', function () {
        if (stockActual > 0) {
            $('#cantidad').val(stockActual);
            calcularTotal();
            marcarPasos();
            $('#preciounitario').focus();
        }
    });

    $('#cantidad, #preciounitario, #cliente').on('input', function () {
        calcularTotal();
        marcarPasos();
    });

    $('#unidadmedidaid').on('change', function () {
        $('#unidadPrecio').text($(this).find(':selected').text());
    });

    @if($oldProduccionExtra)
    aplicarProducto(@json($oldProduccionExtra));
    @endif

    calcularTotal();
    marcarPasos();
});
</script>
@endpush
