@extends('layouts.app')

@section('title', 'Nuevo pedido de distribución')
@section('page_title', 'Nuevo pedido de distribución')

@push('styles')
@include('punto_venta.partials.modulo-styles')
@endpush

@section('content')
    <div class="card pdv-card card-outline card-success border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h3 class="card-title mb-0 font-weight-bold">
                <i class="fas fa-truck-loading text-success mr-2"></i>
                Solicitud <span class="text-muted">{{ $numeroSolicitud }}</span>
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 mb-4">
                <small><i class="fas fa-info-circle mr-1"></i>
                @if($esMinorista ?? false)
                    Solicite producto terminado de planta para su punto de venta. Planta revisará stock y preparará el envío.
                @else
                    Registre una solicitud de distribución hacia un punto de venta. Planta revisará y despachará el producto.
                @endif
                </small>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('punto-venta.pedidos.store') }}" id="formPedidoDist">
                @csrf

                <div class="form-row mb-3">
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold">N° solicitud</label>
                        <input type="text" class="form-control bg-light" value="{{ $numeroSolicitud }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold" for="fecha_entrega_deseada">Fecha entrega deseada</label>
                        <input type="date" name="fecha_entrega_deseada" id="fecha_entrega_deseada" class="form-control"
                            value="{{ old('fecha_entrega_deseada') }}">
                    </div>
                </div>

                <p class="pdv-section-title">Destino y producto</p>
                <div class="form-row mb-3">
                    <div class="form-group col-md-6">
                        <label class="small font-weight-bold">Punto de venta destino <span class="text-danger">*</span></label>
                        @if(($esMinorista ?? false) && ($puntosMinorista ?? collect())->count() === 1)
                            @php $unicoPdv = $puntosMinorista->first(); @endphp
                            <input type="hidden" name="puntoventaid" value="{{ old('puntoventaid', $unicoPdv->puntoventaid) }}">
                            <input type="text" class="form-control bg-light" value="{{ $unicoPdv->nombre }}" readonly>
                        @elseif(($esMinorista ?? false) && ($puntosMinorista ?? collect())->isEmpty())
                            <div class="alert alert-warning py-2 mb-0 small">
                                Registre un punto de venta activo antes de solicitar producto.
                                <a href="{{ route('punto-venta.puntos.create') }}">Crear punto de venta</a>
                            </div>
                        @elseif($esMinorista ?? false)
                            <select name="puntoventaid" id="puntoventaid" class="form-control" required>
                                @foreach($puntosMinorista as $pdv)
                                    <option value="{{ $pdv->puntoventaid }}" @selected(old('puntoventaid') == $pdv->puntoventaid)>{{ $pdv->nombre }}</option>
                                @endforeach
                            </select>
                        @else
                        @include('partials.selector-catalogo', [
                            'id' => 'dist_punto_venta',
                            'name' => 'puntoventaid',
                            'value' => old('puntoventaid', ''),
                            'labelSelected' => $oldPuntoLabel,
                            'endpoint' => route('catalogo-selector.puntos-venta'),
                            'title' => 'Buscar punto de venta',
                            'searchPlaceholder' => 'Nombre, dirección o minorista…',
                            'required' => true,
                            'inputGroup' => true,
                            'filter' => $minoristasFiltro->isNotEmpty() ? [
                                'param' => 'minorista_usuarioid',
                                'options' => array_merge(
                                    [['value' => '', 'label' => 'Todos los minoristas']],
                                    $minoristasFiltro->map(fn ($m) => [
                                        'value' => (string) $m->usuarioid,
                                        'label' => trim($m->nombre.' '.$m->apellido),
                                    ])->all()
                                ),
                            ] : null,
                        ])
                        @endif
                    </div>
                    @if($esAdmin ?? false)
                    <div class="form-group col-md-6">
                        <label class="small font-weight-bold">Almacén planta (origen)</label>
                        @include('partials.selector-catalogo', [
                            'id' => 'dist_almacen_planta',
                            'name' => 'almacen_planta_origenid',
                            'value' => old('almacen_planta_origenid', ''),
                            'labelSelected' => $oldAlmacenLabel,
                            'endpoint' => route('catalogo-selector.almacenes'),
                            'params' => ['ambito' => 'planta'],
                            'title' => 'Buscar almacén de planta',
                            'searchPlaceholder' => 'Nombre o ubicación…',
                            'allowEmpty' => true,
                            'placeholderEmpty' => 'Automático según producto',
                            'inputGroup' => true,
                        ])
                    </div>
                    @endif
                </div>

                <p class="pdv-section-title">Producto y cantidad</p>
                <div class="form-row mb-3">
                    <div class="form-group col-md-7">
                        <label class="small font-weight-bold">Producto (stock planta) <span class="text-danger">*</span></label>
                        @include('partials.selector-catalogo', [
                            'id' => 'dist_producto_planta',
                            'name' => 'insumoid',
                            'value' => old('insumoid', ''),
                            'labelSelected' => $oldProductoLabel,
                            'endpoint' => route('catalogo-selector.insumos'),
                            'params' => ['ambito_planta' => '1'],
                            'title' => 'Buscar producto en planta',
                            'searchPlaceholder' => 'Nombre del producto…',
                            'required' => true,
                            'inputGroup' => true,
                        ])
                        <small id="txtStockDisponible" class="form-text text-muted"></small>
                    </div>
                    <div class="form-group col-md-5">
                        <label class="small font-weight-bold" for="cantidad">Cantidad <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" name="cantidad" id="cantidad" class="form-control" required
                                value="{{ old('cantidad') }}" placeholder="0.00">
                            <span class="pdv-unidad-badge" id="badgeUnidad">{{ $oldProductoUnidad ?: '—' }}</span>
                        </div>
                        <small class="form-text text-muted">Unidad según el producto seleccionado (kg, und, etc.).</small>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="small font-weight-bold" for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="2" class="form-control"
                        placeholder="Instrucciones de entrega, horario preferido…">{{ old('observaciones') }}</textarea>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Cancelar</a>
            <button type="submit" form="formPedidoDist" class="btn btn-success" id="btnEnviarPedido">
                <i class="fas fa-paper-plane mr-1"></i> Enviar solicitud
            </button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var stockActual = {{ $oldProductoStock !== null ? json_encode($oldProductoStock) : 'null' }};
    var unidadActual = @json($oldProductoUnidad ?: '');

    function actualizarUnidad(extra) {
        var unidad = (extra && extra.unidad) ? extra.unidad : '—';
        unidadActual = unidad !== '—' ? unidad : '';
        document.getElementById('badgeUnidad').textContent = unidad;
        stockActual = extra && typeof extra.stock === 'number' ? extra.stock : null;
        var txt = document.getElementById('txtStockDisponible');
        if (stockActual !== null) {
            txt.textContent = 'Disponible en planta: ' + stockActual.toFixed(2) + ' ' + unidad;
        } else {
            txt.textContent = '';
        }
    }

    if (unidadActual) {
        document.getElementById('badgeUnidad').textContent = unidadActual;
        if (stockActual !== null) {
            document.getElementById('txtStockDisponible').textContent =
                'Disponible en planta: ' + stockActual.toFixed(2) + ' ' + unidadActual;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;

        var almWrap = document.getElementById('selector_wrap_dist_almacen_planta');
        var prodWrap = document.getElementById('selector_wrap_dist_producto_planta');

        function paramsProducto() {
            var almId = document.querySelector('#selector_wrap_dist_almacen_planta .selector-catalogo-value')?.value || '';
            return {
                ambito_planta: '1',
                almacenid: almId,
            };
        }

        @if($esAdmin ?? false)
        CatalogoSelector.register('dist_almacen_planta', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén de planta',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'planta' },
            allowEmpty: true,
            emptyLabel: '— Automático según producto —',
            placeholderEmpty: 'Automático según producto',
            onSelect: function () {
                CatalogoSelector.instances.dist_producto_planta.params = paramsProducto();
                CatalogoSelector.clear('dist_producto_planta');
                actualizarUnidad(null);
            },
        });
        @endif

        CatalogoSelector.register('dist_producto_planta', {
            endpoint: @json(route('catalogo-selector.insumos')),
            title: 'Producto terminado (planta)',
            searchPlaceholder: 'Nombre del producto…',
            params: paramsProducto(),
            onSelect: function (item) {
                actualizarUnidad(item.extra || {});
            },
        });

        if (almWrap) {
            almWrap.addEventListener('selector-catalogo:change', function (e) {
                if (!e.detail.id) {
                    CatalogoSelector.instances.dist_producto_planta.params = paramsProducto();
                    actualizarUnidad(null);
                }
            });
        }

        document.getElementById('formPedidoDist').addEventListener('submit', function (e) {
            var pdvOk = document.querySelector('[name="puntoventaid"]')?.value
                || document.querySelector('#selector_wrap_dist_punto_venta .selector-catalogo-value')?.value;
            if (!pdvOk) {
                e.preventDefault();
                alert('Seleccione un punto de venta destino.');
                return;
            }
            if (!document.querySelector('#selector_wrap_dist_producto_planta .selector-catalogo-value')?.value) {
                e.preventDefault();
                alert('Seleccione un producto de planta.');
            }
        });
    });
})();
</script>
@endpush
