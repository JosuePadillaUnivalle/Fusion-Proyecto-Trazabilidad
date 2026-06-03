@extends('layouts.app')

@section('title', 'Editar lote — '.$loteProduccion->nombre.' | AgroFusion')
@section('page_title', 'Editar lote de producción')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procesamiento.index') }}">Procesamiento de Lote</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procesamiento.show', $loteProduccion) }}">{{ $loteProduccion->nombre }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
<style>
.page-edit-lote .form-card { border: none; border-radius: 14px; box-shadow: 0 4px 18px rgba(0,0,0,.07); }
.page-edit-lote .lote-section {
    background: #fff; border: 1px solid #e2ebe3; border-radius: 12px;
    padding: 1rem 1.15rem; margin-bottom: 1rem;
}
.page-edit-lote .lote-section-title {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #2c5530; margin-bottom: .75rem;
}
.page-edit-lote .picker-field {
    display: flex; align-items: stretch; border: 2px solid #dee2e6; border-radius: 10px; overflow: hidden; background: #fff;
}
.page-edit-lote .picker-field:focus-within { border-color: #2c5530; }
.page-edit-lote .picker-field .picker-display { flex: 1; border: 0; background: transparent; padding: .55rem .85rem; min-height: 44px; }
.page-edit-lote .picker-actions { display: flex; border-left: 1px solid #e5e7eb; }
.page-edit-lote .tabla-materias { border-radius: 10px; overflow: hidden; border: 1px solid #e2ebe3; }
.page-edit-lote .tabla-materias thead th { background: #f0f7f1; font-size: .72rem; text-transform: uppercase; border: 0; }
.page-edit-lote .btn-agregar-mp {
    border: 2px dashed #2c5530; color: #2c5530; background: #f0fdf4;
    border-radius: 10px; font-weight: 600; width: 100%; padding: .65rem;
}
</style>
@endpush

@section('content')
<div class="page-edit-lote">
    <div class="card form-card mb-3">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 font-weight-bold text-success"><i class="fas fa-edit mr-2"></i>{{ $loteProduccion->nombre }}</h5>
                <p class="mb-0 small text-muted"><code>{{ $loteProduccion->codigo_lote }}</code> · Fase: {{ \App\Support\LoteProduccionTrazabilidadService::FASES[$fase]['label'] ?? $fase }}</p>
            </div>
            <a href="{{ route('procesamiento.show', $loteProduccion) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-eye mr-1"></i>Ver fases
            </a>
        </div>
        <div class="card-body">
            @unless($puedeEditarMaterias)
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle mr-1"></i>
                    El lote ya avanzó de la fase inicial. Solo puede editar pedido, cantidad objetivo y observaciones.
                </div>
            @endunless

            <form method="POST" action="{{ route('procesamiento.update', $loteProduccion) }}" id="formEditarLote">
                @csrf
                @method('PUT')

                @if($puedeEditarMaterias)
                <div class="lote-section">
                    <div class="lote-section-title"><i class="fas fa-tag mr-1"></i> Producto</div>
                    <input type="text"
                           name="producto"
                           id="productoLote"
                           class="form-control"
                           list="productosLoteList"
                           value="{{ old('producto', $productoActual) }}"
                           required
                           maxlength="100">
                    <datalist id="productosLoteList">
                        @foreach($productosLote as $prod)
                            <option value="{{ $prod }}"></option>
                        @endforeach
                    </datalist>
                    <small class="text-muted">Si cambia el producto, se asignará un nuevo nombre de lote numerado.</small>
                </div>
                @endif

                <div class="lote-section">
                    <div class="lote-section-title"><i class="fas fa-shopping-cart mr-1"></i> Pedido asociado</div>
                    <div class="picker-field">
                        <input type="text" id="pedido_display" class="picker-display {{ $pedidoLabel ? '' : 'text-muted' }}" readonly placeholder="Sin pedido asociado" value="{{ old('pedido_display', $pedidoLabel) }}">
                        <input type="hidden" name="pedidoid" id="pedidoid" value="{{ old('pedidoid', $loteProduccion->pedidoid) }}">
                        <div class="picker-actions">
                            <button type="button" class="btn btn-outline-success btn-sm" id="btnBuscarPedido"><i class="fas fa-search mr-1"></i>Buscar</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarPedido" title="Quitar"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>

                <div class="lote-section">
                    <div class="lote-section-title"><i class="fas fa-balance-scale mr-1"></i> Cantidad objetivo</div>
                    <div class="input-group">
                        <input type="number" name="cantidad_objetivo" class="form-control" step="0.01" min="0"
                               value="{{ old('cantidad_objetivo', $loteProduccion->cantidad_objetivo) }}" placeholder="500">
                        <select name="unidadmedidaid" class="custom-select" style="max-width:140px;">
                            <option value="">Unidad</option>
                            @foreach($unidadesMedida as $um)
                                <option value="{{ $um->unidadmedidaid }}" @selected(old('unidadmedidaid', $loteProduccion->unidadmedidaid) == $um->unidadmedidaid)>{{ $um->abreviatura ?? $um->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($puedeEditarMaterias)
                <div class="lote-section">
                    <div class="lote-section-title"><i class="fas fa-boxes mr-1"></i> Materia prima</div>
                    <div class="table-responsive tabla-materias mb-2">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Insumo</th><th style="width:130px">Cantidad</th><th style="width:44px"></th></tr></thead>
                            <tbody id="tbodyMaterias">
                                <tr id="filaMateriasVacia"><td colspan="3" class="text-center text-muted py-3 small">Sin materias.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-agregar-mp" id="btnBuscarInsumo">
                        <i class="fas fa-plus-circle mr-1"></i> Agregar materia prima
                    </button>
                    <small class="text-muted d-block mt-2">Al guardar se revierte el consumo anterior y se descuenta el stock nuevo.</small>
                </div>
                @else
                <div class="lote-section">
                    <div class="lote-section-title"><i class="fas fa-boxes mr-1"></i> Materias usadas</div>
                    <ul class="mb-0 pl-3">
                        @foreach($loteProduccion->materiasPrimas as $mp)
                            <li>{{ $mp->insumo?->nombre ?? 'MP' }}: {{ number_format((float) $mp->cantidad_usada, 2) }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="lote-section mb-0">
                    <label class="small font-weight-bold text-muted">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2" maxlength="500">{{ old('observaciones', $loteProduccion->observaciones) }}</textarea>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('procesamiento.index') }}" class="btn btn-light">Cancelar</a>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i>Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.selector-catalogo-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/selector-catalogo.js') }}"></script>
<script>
(function() {
    const materias = @json($materiasIniciales);
    const tbody = document.getElementById('tbodyMaterias');
    const filaVacia = document.getElementById('filaMateriasVacia');
    const pedidoDisplay = document.getElementById('pedido_display');
    const pedidoInput = document.getElementById('pedidoid');

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
    }

    function renderMaterias() {
        if (!tbody) return;
        tbody.querySelectorAll('tr:not(#filaMateriasVacia)').forEach(r => r.remove());
        if (!materias.length) { if (filaVacia) filaVacia.style.display = ''; return; }
        if (filaVacia) filaVacia.style.display = 'none';
        materias.forEach((m, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td><strong>' + esc(m.label) + '</strong><br><small class="text-muted">' + esc(m.meta) + '</small>' +
                '<input type="hidden" name="materias[' + i + '][insumoid]" value="' + m.id + '"></td>' +
                '<td><div class="input-group input-group-sm">' +
                '<input type="number" name="materias[' + i + '][cantidad]" class="form-control" step="0.001" min="0.001" max="' + m.stock + '" value="' + (m.cantidad || '') + '" required>' +
                '<div class="input-group-append"><span class="input-group-text">' + esc(m.unidad) + '</span></div></div></td>' +
                '<td><button type="button" class="btn btn-outline-danger btn-sm btn-quitar-materia" data-idx="' + i + '"><i class="fas fa-trash"></i></button></td>';
            tbody.appendChild(tr);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderMaterias();

        if (!window.CatalogoSelector) return;

        CatalogoSelector.register('edit_lote_pedido', {
            endpoint: @json(route('catalogo-selector.pedidos')),
            title: 'Seleccionar pedido',
            searchPlaceholder: 'Número, planta, dirección…',
            filter: { param: 'estado', options: @json($filtroEstadosPedido) },
            onSelect(item) {
                if (!item?.id) return;
                pedidoInput.value = item.id;
                pedidoDisplay.value = item.label;
                pedidoDisplay.classList.remove('text-muted');
            },
        });

        CatalogoSelector.register('edit_lote_insumo', {
            endpoint: @json(route('catalogo-selector.insumos')),
            title: 'Seleccionar materia prima',
            searchPlaceholder: 'Nombre del insumo…',
            params: { ambito_planta: '1', solo_con_stock: '1' },
            filter: { param: 'almacenid', options: @json($filtroAlmacenes) },
            onSelect(item) {
                if (!item?.id) return;
                const extra = item.extra || {};
                if (extra.sin_stock || (extra.stock ?? 0) <= 0) {
                    alert('El insumo seleccionado no tiene stock disponible.');
                    return;
                }
                if (materias.some(m => String(m.id) === String(item.id))) {
                    alert('Ese insumo ya está en la lista.');
                    return;
                }
                materias.push({
                    id: item.id,
                    label: item.label,
                    meta: item.meta || '',
                    stock: extra.stock ?? 999999,
                    unidad: extra.unidad || 'ud',
                    cantidad: '',
                });
                renderMaterias();
            },
        });

        document.getElementById('btnBuscarPedido')?.addEventListener('click', () => CatalogoSelector.open('edit_lote_pedido'));
        document.getElementById('btnBuscarInsumo')?.addEventListener('click', () => CatalogoSelector.open('edit_lote_insumo'));
    });

    document.getElementById('btnLimpiarPedido')?.addEventListener('click', function () {
        pedidoInput.value = '';
        pedidoDisplay.value = '';
        pedidoDisplay.classList.add('text-muted');
    });

    tbody?.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-quitar-materia');
        if (!btn) return;
        materias.splice(parseInt(btn.dataset.idx, 10), 1);
        renderMaterias();
    });

    document.getElementById('formEditarLote')?.addEventListener('submit', function (e) {
        if (tbody && !materias.length) { e.preventDefault(); alert('Agregue al menos una materia prima.'); }
    });
})();
</script>
@endpush
