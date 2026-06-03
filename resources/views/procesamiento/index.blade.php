@extends('layouts.app')

@section('title', 'Procesamiento de Lote | AgroFusion')
@section('page_title', 'Procesamiento de Lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Procesamiento de Lote</li>
@endsection

@push('styles')
<style>
.pl-stats .pl-stat {
    border-radius: 14px; color: #fff; padding: 18px 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
}
.pl-stats .pl-stat h3 { font-size: 1.75rem; font-weight: 800; margin: 0 0 2px; }
.pl-stats .pl-stat p { margin: 0; font-size: .78rem; opacity: .92; text-transform: uppercase; letter-spacing: .04em; }
.pl-stats .bg-total { background: linear-gradient(135deg, #0e7490, #06b6d4); }
.pl-stats .bg-pend { background: linear-gradient(135deg, #b45309, #f59e0b); }
.pl-stats .bg-proc { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
.pl-stats .bg-done { background: linear-gradient(135deg, #065f46, #10b981); }

#modalNuevoLote .modal-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,.18); }
#modalNuevoLote .modal-dialog.modal-dialog-scrollable .modal-content > form {
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 3.5rem);
    min-height: 0;
    overflow: hidden;
}
#modalNuevoLote .modal-dialog.modal-dialog-scrollable .modal-body {
    overflow-y: auto;
    flex: 1 1 auto;
    min-height: 0;
    -webkit-overflow-scrolling: touch;
}
#modalNuevoLote .modal-dialog.modal-dialog-scrollable .modal-header,
#modalNuevoLote .modal-dialog.modal-dialog-scrollable .modal-footer {
    flex-shrink: 0;
}
#modalNuevoLote .modal-header-lote {
    background: linear-gradient(135deg, #1e4620 0%, #2c5530 55%, #4a7c59 100%);
    color: #fff; border: 0; padding: 1.25rem 1.5rem;
}
#modalNuevoLote .modal-header-lote .close { color: #fff; opacity: .85; text-shadow: none; }
#modalNuevoLote .modal-body { padding: 1.25rem 1.5rem 1rem; background: #f8faf8; }
#modalNuevoLote .lote-section {
    background: #fff; border: 1px solid #e2ebe3; border-radius: 12px;
    padding: 1rem 1.15rem; margin-bottom: 1rem;
}
#modalNuevoLote .lote-section-title {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #2c5530; margin-bottom: .75rem;
}
#modalNuevoLote .lote-section-title i { opacity: .75; }
#modalNuevoLote .picker-field {
    display: flex; align-items: stretch; gap: 0;
    border: 2px solid #dee2e6; border-radius: 10px; overflow: hidden; background: #fff;
}
#modalNuevoLote .picker-field:focus-within { border-color: #2c5530; box-shadow: 0 0 0 .15rem rgba(44,85,48,.12); }
#modalNuevoLote .picker-field .picker-display {
    flex: 1; border: 0; background: transparent; padding: .55rem .85rem;
    font-size: .95rem; min-height: 44px;
}
#modalNuevoLote .picker-field .picker-display.text-muted { color: #9ca3af !important; }
#modalNuevoLote .picker-actions { display: flex; border-left: 1px solid #e5e7eb; }
#modalNuevoLote .picker-actions .btn { border-radius: 0; border: 0; padding: 0 1rem; font-weight: 600; }
#modalNuevoLote .tabla-materias { border-radius: 10px; overflow: hidden; border: 1px solid #e2ebe3; }
#modalNuevoLote .tabla-materias thead th { background: #f0f7f1; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; border: 0; }
#modalNuevoLote .btn-agregar-mp {
    border: 2px dashed #2c5530; color: #2c5530; background: #f0fdf4;
    border-radius: 10px; font-weight: 600; width: 100%; padding: .65rem;
}
#modalNuevoLote .btn-agregar-mp:hover { background: #dcfce7; }
#modalNuevoLote .modal-footer { background: #fff; border-top: 1px solid #e5e7eb; padding: 1rem 1.5rem; }
.pl-actions .btn { padding: .25rem .5rem; margin: 0 .15rem; }
.pl-actions .btn-danger { background: #dc3545; border-color: #dc3545; color: #fff; }
.pl-actions .btn-danger:hover { background: #c82333; color: #fff; }
</style>
@endpush

@section('content')
<div class="row mb-4 pl-stats">
    @foreach([
        ['total', 'Total lotes', 'bg-total'],
        ['pendientes', 'Pendientes', 'bg-pend'],
        ['en_proceso', 'En proceso', 'bg-proc'],
        ['completados', 'Completados', 'bg-done'],
    ] as [$key, $label, $bg])
    <div class="col-6 col-md-3 mb-2">
        <div class="pl-stat {{ $bg }}">
            <h3>{{ $stats[$key] ?? 0 }}</h3>
            <p>{{ $label }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-3" style="border-radius:14px;">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('procesamiento.index') }}" class="form-row align-items-end">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Buscar</label>
                <input type="search" name="q" class="form-control form-control-sm" value="{{ $busqueda }}" placeholder="Código, nombre, pedido…">
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Producto</label>
                <select name="producto" class="custom-select custom-select-sm">
                    <option value="">Todos</option>
                    @foreach($productosLote as $prod)
                        <option value="{{ $prod }}" @selected($productoFiltro === $prod)>{{ $prod }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Estado</label>
                <select name="estado" class="custom-select custom-select-sm">
                    <option value="">Todos</option>
                    <option value="pendiente" @selected($estadoFiltro === 'pendiente')>Pendiente</option>
                    <option value="en_proceso" @selected($estadoFiltro === 'en_proceso')>En proceso</option>
                    <option value="completado" @selected($estadoFiltro === 'completado')>Completado</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="{{ $desde }}">
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $hasta }}">
            </div>
            <div class="col-md-1 mb-2 mb-md-0">
                <button type="submit" class="btn btn-success btn-sm btn-block"><i class="fas fa-filter"></i></button>
            </div>
        </form>
        @if($productoFiltro || $estadoFiltro || $busqueda || $desde || $hasta)
            <p class="small text-muted mb-0 mt-2">
                Filtros activos.
                <a href="{{ route('procesamiento.index') }}">Limpiar</a>
            </p>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white py-3" style="border-radius:14px 14px 0 0;">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-2 mb-md-0 font-weight-bold"><i class="fas fa-industry text-success mr-2"></i>Lotes de producción</h3>
            <div>
                @can('lote_produccion.create')
                <button type="button" class="btn btn-success btn-sm px-3" data-toggle="modal" data-target="#modalNuevoLote">
                    <i class="fas fa-plus mr-1"></i>Nuevo lote
                </button>
                @endcan
            </div>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Código</th>
                    <th>Producto / Lote</th>
                    <th>Fase</th>
                    <th>Pedido</th>
                    <th>Cant. objetivo</th>
                    <th>Materias usadas</th>
                    <th>Fecha</th>
                    <th class="text-center" style="width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lotes as $lote)
                    <tr>
                        <td><a href="{{ route('procesamiento.show', $lote) }}"><code class="text-success">{{ $lote->codigo_lote }}</code></a></td>
                        <td class="font-weight-bold">
                            <a href="{{ route('procesamiento.show', $lote) }}" class="text-dark">{{ $lote->nombre }}</a>
                        </td>
                        <td>
                            <span class="badge badge-{{ $lote->estado_operativo === 'completado' ? 'success' : ($lote->estado_operativo === 'en_proceso' ? 'primary' : 'secondary') }}">{{ $lote->fase_label }}</span>
                        </td>
                        <td>{{ $lote->pedido?->numero_solicitud ?? '—' }}</td>
                        <td>
                            @if($lote->cantidad_objetivo)
                                {{ number_format((float) $lote->cantidad_objetivo, 2) }}
                                {{ $lote->unidadMedida?->abreviatura ?? $lote->unidadMedida?->nombre ?? '' }}
                            @else — @endif
                        </td>
                        <td>
                            @foreach($lote->materiasPrimas as $mp)
                                <span class="badge badge-light border mr-1">{{ $mp->insumo?->nombre ?? 'MP' }}: {{ number_format((float) $mp->cantidad_usada, 2) }}</span>
                            @endforeach
                        </td>
                        <td class="text-muted">{{ optional($lote->fecha_creacion)->format('d/m/Y') }}</td>
                        <td class="text-center pl-actions text-nowrap">
                            <a href="{{ route('procesamiento.show', $lote) }}" class="btn btn-sm btn-outline-info" title="Ver fases"><i class="fas fa-eye"></i></a>
                            @can('lote_produccion.create')
                                <a href="{{ route('procesamiento.edit', $lote) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                @if($lote->estado_operativo === 'pendiente')
                                <form action="{{ route('procesamiento.destroy', $lote) }}" method="POST" class="d-inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                            data-confirm-modal
                                            data-confirm-title="Eliminar lote"
                                            data-confirm-message="¿Eliminar el lote «{{ $lote->nombre }}»? Se revertirá el stock de materias primas.">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>No hay lotes. Cree uno con «Nuevo lote».</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lotes->hasPages())<div class="card-footer bg-white">{{ $lotes->links() }}</div>@endif
</div>

@can('lote_produccion.create')
<div class="modal fade" id="modalNuevoLote" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('procesamiento.store') }}" id="formNuevoLote">
                @csrf
                <div class="modal-header modal-header-lote">
                    <div>
                        <h5 class="modal-title mb-1 font-weight-bold"><i class="fas fa-flask mr-2"></i>Nuevo lote de producción</h5>
                        <p class="mb-0 small opacity-90">Industrialización de materia prima desde almacén de planta</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="lote-section">
                        <div class="lote-section-title"><i class="fas fa-tag mr-1"></i> Producto a procesar</div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Producto <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="producto"
                                   id="productoLote"
                                   class="form-control"
                                   list="productosLoteList"
                                   value="{{ old('producto') }}"
                                   required
                                   maxlength="100"
                                   placeholder="Ej. Papas Fritas"
                                   autocomplete="off">
                            <datalist id="productosLoteList">
                                @foreach($productosLote as $prod)
                                    <option value="{{ $prod }}"></option>
                                @endforeach
                            </datalist>
                            <small class="text-muted">Elegí uno existente o escribí un producto nuevo.</small>
                        </div>
                        <div class="alert alert-light border mb-0 py-2 px-3 small">
                            <i class="fas fa-magic text-success mr-1"></i>
                            Se creará como: <strong id="nombreLotePreview" class="text-success">—</strong>
                        </div>
                    </div>

                    <div class="lote-section">
                        <div class="lote-section-title"><i class="fas fa-shopping-cart mr-1"></i> Pedido asociado <span class="text-muted font-weight-normal">(opcional)</span></div>
                        <div class="picker-field">
                            <input type="text" id="pedido_display" class="picker-display text-muted" readonly placeholder="Sin pedido asociado" value="{{ $pedidoLabel ?? '' }}">
                            <input type="hidden" name="pedidoid" id="pedidoid" value="{{ old('pedidoid') }}">
                            <div class="picker-actions">
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnBuscarPedido"><i class="fas fa-search mr-1"></i>Buscar</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarPedido" title="Quitar"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">Se abre un buscador encima para filtrar y elegir el pedido.</small>
                    </div>

                    <div class="lote-section">
                        <div class="lote-section-title"><i class="fas fa-balance-scale mr-1"></i> Cantidad objetivo</div>
                        <div class="input-group">
                            <input type="number" name="cantidad_objetivo" class="form-control" step="0.01" min="0" value="{{ old('cantidad_objetivo') }}" placeholder="500">
                            <select name="unidadmedidaid" class="custom-select" style="max-width:140px;">
                                <option value="">Unidad</option>
                                @foreach($unidadesMedida as $um)
                                    <option value="{{ $um->unidadmedidaid }}" @selected(old('unidadmedidaid') == $um->unidadmedidaid)>{{ $um->abreviatura ?? $um->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="lote-section mb-0">
                        <div class="lote-section-title"><i class="fas fa-boxes mr-1"></i> Materia prima <span class="text-danger">*</span></div>
                        <div class="table-responsive tabla-materias mb-2">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Insumo</th><th style="width:130px">Cantidad</th><th style="width:44px"></th></tr></thead>
                                <tbody id="tbodyMaterias">
                                    <tr id="filaMateriasVacia"><td colspan="3" class="text-center text-muted py-3 small">Sin materias. Use el botón de abajo para buscar insumos del almacén de planta.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-agregar-mp" id="btnBuscarInsumo">
                            <i class="fas fa-plus-circle mr-1"></i> Agregar materia prima
                        </button>
                    </div>

                    <div class="lote-section mt-3 mb-0">
                        <label class="small font-weight-bold text-muted">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2" maxlength="500" placeholder="Opcional…">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check mr-1"></i>Crear lote</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.selector-catalogo-modal')
@endcan

@include('partials.modal-confirmar-accion')
@endsection

@can('lote_produccion.create')
@push('styles')
<style>
#modalSelectorCatalogo .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
#modalSelectorCatalogo .modal-header { background: linear-gradient(135deg, #1e4620, #4a7c59); }
#modalSelectorCatalogo .selector-catalogo-row { cursor: pointer; }
#modalSelectorCatalogo .selector-catalogo-row:hover { background: #eef7ef; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/selector-catalogo.js') }}"></script>
<script>
(function() {
    const materias = [];
    const tbody = document.getElementById('tbodyMaterias');
    const filaVacia = document.getElementById('filaMateriasVacia');
    const pedidoDisplay = document.getElementById('pedido_display');
    const pedidoInput = document.getElementById('pedidoid');
    const productoInput = document.getElementById('productoLote');
    const nombrePreview = document.getElementById('nombreLotePreview');
    const urlSiguienteNombre = @json(route('procesamiento.siguiente-nombre'));
    let previewTimer = null;

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
    }

    function renderMaterias() {
        tbody.querySelectorAll('tr:not(#filaMateriasVacia)').forEach(r => r.remove());
        if (!materias.length) { filaVacia.style.display = ''; return; }
        filaVacia.style.display = 'none';
        materias.forEach((m, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td><strong>' + esc(m.label) + '</strong><br><small class="text-muted">' + esc(m.meta) + '</small>' +
                '<input type="hidden" name="materias[' + i + '][insumoid]" value="' + m.id + '"></td>' +
                '<td><div class="input-group input-group-sm">' +
                '<input type="number" name="materias[' + i + '][cantidad]" class="form-control" step="0.001" min="0.001" max="' + m.stock + '" required>' +
                '<div class="input-group-append"><span class="input-group-text">' + esc(m.unidad) + '</span></div></div></td>' +
                '<td><button type="button" class="btn btn-outline-danger btn-sm btn-quitar-materia" data-idx="' + i + '"><i class="fas fa-trash"></i></button></td>';
            tbody.appendChild(tr);
        });
    }

    function aplicarPedido(payload) {
        if (!payload || !payload.id) return;
        pedidoInput.value = payload.id;
        pedidoDisplay.value = payload.label;
        pedidoDisplay.classList.remove('text-muted');
    }

    function aplicarInsumo(payload) {
        if (!payload || !payload.id) return;
        const extra = payload.extra || {};
        if (extra.sin_stock || (extra.stock ?? 0) <= 0) {
            alert('El insumo seleccionado no tiene stock disponible.');
            return;
        }
        if (materias.some(m => String(m.id) === String(payload.id))) {
            alert('Ese insumo ya está en la lista.');
            return;
        }
        materias.push({
            id: payload.id,
            label: payload.label,
            meta: payload.meta || ((extra.almacen || '') + ' · Stock: ' + (extra.stock ?? 0) + ' ' + (extra.unidad || '')),
            stock: extra.stock ?? 999999,
            unidad: extra.unidad || 'ud',
        });
        renderMaterias();
    }

    function actualizarNombrePreview() {
        const producto = (productoInput?.value || '').trim();
        if (!producto) {
            if (nombrePreview) nombrePreview.textContent = '—';
            return;
        }
        if (nombrePreview) nombrePreview.textContent = '…';
        fetch(urlSiguienteNombre + '?producto=' + encodeURIComponent(producto), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(j => { if (nombrePreview) nombrePreview.textContent = j.nombre || '—'; })
            .catch(() => { if (nombrePreview) nombrePreview.textContent = producto + ' - Lote 001'; });
    }

    productoInput?.addEventListener('input', function () {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(actualizarNombrePreview, 280);
    });
    productoInput?.addEventListener('change', actualizarNombrePreview);

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;

        CatalogoSelector.register('procesamiento_pedido', {
            endpoint: @json(route('catalogo-selector.pedidos')),
            title: 'Seleccionar pedido',
            searchPlaceholder: 'Número, planta, dirección…',
            filter: { param: 'estado', options: @json($filtroEstadosPedido) },
            onSelect(item) {
                aplicarPedido({ id: item.id, label: item.label, extra: item.extra });
            },
        });

        CatalogoSelector.register('procesamiento_insumo', {
            endpoint: @json(route('catalogo-selector.insumos')),
            title: 'Seleccionar materia prima',
            searchPlaceholder: 'Nombre del insumo…',
            params: { ambito_planta: '1', solo_con_stock: '1' },
            filter: { param: 'almacenid', options: @json($filtroAlmacenes) },
            onSelect(item) {
                aplicarInsumo({ id: item.id, label: item.label, meta: item.meta, extra: item.extra });
            },
        });

        document.getElementById('btnBuscarPedido')?.addEventListener('click', function () {
            CatalogoSelector.open('procesamiento_pedido');
        });
        document.getElementById('btnBuscarInsumo')?.addEventListener('click', function () {
            CatalogoSelector.open('procesamiento_insumo');
        });

        actualizarNombrePreview();
    });

    document.getElementById('btnLimpiarPedido')?.addEventListener('click', function () {
        pedidoInput.value = '';
        pedidoDisplay.value = '';
        pedidoDisplay.classList.add('text-muted');
        pedidoDisplay.placeholder = 'Sin pedido asociado';
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-quitar-materia');
        if (!btn) return;
        materias.splice(parseInt(btn.dataset.idx, 10), 1);
        renderMaterias();
    });

    document.getElementById('formNuevoLote')?.addEventListener('submit', function (e) {
        if (!materias.length) { e.preventDefault(); alert('Agregue al menos una materia prima.'); }
    });

    if (pedidoInput.value && pedidoDisplay.value) pedidoDisplay.classList.remove('text-muted');

    @if($errors->any() || old('producto'))
    $('#modalNuevoLote').modal('show');
    @endif
})();
</script>
@endpush
@endcan
