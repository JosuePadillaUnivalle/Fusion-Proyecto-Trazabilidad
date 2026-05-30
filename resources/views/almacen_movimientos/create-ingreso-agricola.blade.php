@extends('layouts.app')

@section('title', 'Ingreso manual de inventario | AgroNexus')
@section('page_title', 'Ingreso de almacén agrícola')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index', ['naturaleza' => 'ingreso']) }}">Movimientos</a></li>
    <li class="breadcrumb-item active">Ingreso manual</li>
@endsection

@push('styles')
<style>
.page-ingreso-agr .form-card { border: none; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.08); }
.page-ingreso-agr .form-card .card-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border-radius: 12px 12px 0 0 !important;
}
.page-ingreso-agr .guia-aviso {
    background: #f8fbf8;
    border-left: 3px solid #2c5530;
    border-radius: 0 8px 8px 0;
    padding: 0.65rem 0.9rem;
    font-size: 0.86rem;
}
.page-ingreso-agr .panel-categoria { display: none; }
.page-ingreso-agr .panel-categoria.visible { display: block; }
</style>
@endpush

@section('content')
@php
    $almacenPre = old('almacenid', $almacenes->first()?->almacenid);
    $categoriaPre = old('categoria_entrada', '');
@endphp
<div class="modulo-inv page-ingreso-agr">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card form-card card-modulo-main">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-arrow-down mr-2"></i>Ingreso manual de inventario
                    </h3>
                    <small class="d-block mt-1" style="opacity:.9">
                        Para cargar stock existente antes de usar el sistema (insumos o cultivo en material de siembra).
                    </small>
                </div>

                <form method="POST" action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.store', ['naturaleza' => 'ingreso']) }}" id="form-ingreso-agr">
                    @csrf
                    <input type="hidden" name="ingreso_manual_agricola" value="1">
                    <input type="hidden" name="tipo_movimiento_almacenid" value="{{ $tipoMovimientoAjusteId }}">

                    <div class="card-body">
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

                        <div class="form-group">
                            <label for="almacenid">Almacén <span class="text-danger">*</span></label>
                            <select name="almacenid" id="almacenid" class="form-control @error('almacenid') is-invalid @enderror" required>
                                <option value="">Seleccione un almacén activo...</option>
                                @foreach($almacenes as $almacen)
                                    <option value="{{ $almacen->almacenid }}" @selected((int) $almacenPre === (int) $almacen->almacenid)>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="guia-aviso mt-2 mb-0">Depósito donde se sumará el inventario.</div>
                            @error('almacenid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="categoria_entrada">Tipo <span class="text-danger">*</span></label>
                            <select name="categoria_entrada" id="categoria_entrada" class="form-control @error('categoria_entrada') is-invalid @enderror" required>
                                <option value="">Seleccione tipo de ingreso...</option>
                                <option value="insumo" @selected($categoriaPre === 'insumo')>Insumo</option>
                                <option value="cosecha" @selected($categoriaPre === 'cosecha')>Cosecha (cultivo / material de siembra)</option>
                            </select>
                            <div class="guia-aviso mt-2 mb-0">
                                <strong>Insumo:</strong> fertilizantes, pesticidas, riego, etc.
                                <strong class="ml-2">Cosecha:</strong> cultivo registrado como insumo de <em>Material de Siembra</em>.
                            </div>
                            @error('categoria_entrada')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div id="panel-insumo" class="panel-categoria {{ $categoriaPre === 'insumo' ? 'visible' : '' }}">
                            <div class="form-group">
                                <label for="insumoid_insumo">Insumo <span class="text-danger">*</span></label>
                                <select id="insumoid_insumo" class="form-control" disabled>
                                    <option value="">Primero seleccione almacén y tipo...</option>
                                </select>
                                <p class="guia-aviso mt-2 mb-0" id="hint-insumo">
                                    Lista de insumos registrados en el sistema (fertilizantes, pesticidas, riego, etc.).
                                    Si no aparece, debe ser primero
                                    <a href="{{ route('insumos.create') }}" class="font-weight-bold">creado en el sistema</a>.
                                </p>
                            </div>
                        </div>

                        <div id="panel-cosecha" class="panel-categoria {{ $categoriaPre === 'cosecha' ? 'visible' : '' }}">
                            <div class="form-group">
                                <label for="insumoid_cosecha">Cultivo (Material de Siembra) <span class="text-danger">*</span></label>
                                <select id="insumoid_cosecha" class="form-control" disabled>
                                    <option value="">Primero seleccione almacén y tipo...</option>
                                </select>
                                <p class="guia-aviso mt-2 mb-0" id="hint-cosecha">
                                    Cultivos registrados como insumo de tipo <strong>Material de Siembra</strong> en el catálogo.
                                    Si no aparece, créelo en
                                    <a href="{{ route('insumos.create') }}" class="font-weight-bold">Nuevo insumo</a>.
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="cantidad_kg">Cantidad (kilogramos) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01" id="cantidad_kg" class="form-control"
                                           value="{{ old('cantidad') }}" placeholder="Ej: 2500" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light font-weight-bold">kg</span>
                                    </div>
                                </div>
                                <p class="guia-aviso mt-2 mb-0">Peso del cultivo almacenado, siempre en kilogramos.</p>
                            </div>
                        </div>

                        <select name="insumoid" id="insumoid" class="d-none" required>
                            <option value="{{ old('insumoid') }}" selected>{{ old('insumoid') }}</option>
                        </select>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="fecha">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" id="fecha" class="form-control @error('fecha') is-invalid @enderror"
                                       value="{{ old('fecha', now()->toDateString()) }}" required>
                                @error('fecha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6 panel-categoria visible" id="panel-cantidad-insumo" style="{{ $categoriaPre === 'cosecha' ? 'display:none' : '' }}">
                                <label for="cantidad_insumo">Cantidad <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.001" min="0.001" id="cantidad_insumo" class="form-control"
                                           value="{{ old('cantidad') }}" placeholder="0" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light" id="unidad-insumo-label">—</span>
                                    </div>
                                </div>
                                @error('cantidad')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <input type="hidden" name="cantidad" id="cantidad_hidden" value="{{ old('cantidad') }}">

                        <div class="form-group mb-0">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control"
                                      placeholder="Detalle adicional del ingreso manual (opcional)">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between bg-white">
                        <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index', ['naturaleza' => 'ingreso']) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-success" id="btn-guardar-ingreso">
                            <i class="fas fa-save mr-1"></i> Registrar ingreso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const insumosData = @json($insumosJson);
    const almacenSelect = document.getElementById('almacenid');
    const categoriaSelect = document.getElementById('categoria_entrada');
    const panelInsumo = document.getElementById('panel-insumo');
    const panelCosecha = document.getElementById('panel-cosecha');
    const panelCantidadInsumo = document.getElementById('panel-cantidad-insumo');
    const selInsumo = document.getElementById('insumoid_insumo');
    const selCosecha = document.getElementById('insumoid_cosecha');
    const hiddenInsumo = document.getElementById('insumoid');
    const cantidadHidden = document.getElementById('cantidad_hidden');
    const cantidadKg = document.getElementById('cantidad_kg');
    const cantidadInsumo = document.getElementById('cantidad_insumo');
    const unidadLabel = document.getElementById('unidad-insumo-label');
    const oldInsumo = @json((int) old('insumoid', 0));
    const oldCategoria = @json(old('categoria_entrada', ''));

    function insumosGenerales() {
        return insumosData;
    }

    function insumosPorCategoria(cat) {
        const todos = insumosGenerales();
        if (cat === 'cosecha') {
            return todos.filter(i => i.tipo_slug === 'material_siembra');
        }
        if (cat === 'insumo') {
            return todos.filter(i => i.tipo_slug !== 'material_siembra');
        }
        return [];
    }

    function llenarSelect(select, items, placeholder, selectedId) {
        select.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = placeholder;
        select.appendChild(opt0);
        items.forEach(i => {
            const opt = document.createElement('option');
            opt.value = i.id;
            const tipoTxt = i.tipo_nombre ? ' · ' + i.tipo_nombre : '';
            opt.textContent = i.nombre + tipoTxt + ' (Stock global: ' + Number(i.stock).toFixed(2) + ' ' + (i.unidad || '') + ')';
            opt.dataset.unidad = i.unidad || '';
            if (String(selectedId) === String(i.id)) opt.selected = true;
            select.appendChild(opt);
        });
        select.disabled = items.length === 0;
    }

    function syncHiddenInsumo() {
        const cat = categoriaSelect.value;
        let val = '';
        if (cat === 'insumo' && selInsumo.value) val = selInsumo.value;
        if (cat === 'cosecha' && selCosecha.value) val = selCosecha.value;
        hiddenInsumo.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = val;
        opt.selected = true;
        hiddenInsumo.appendChild(opt);
    }

    function syncCantidad() {
        const cat = categoriaSelect.value;
        if (cat === 'cosecha') {
            cantidadHidden.value = cantidadKg.value || '';
        } else if (cat === 'insumo') {
            cantidadHidden.value = cantidadInsumo.value || '';
        }
    }

    function actualizarPaneles() {
        const cat = categoriaSelect.value;
        panelInsumo.classList.toggle('visible', cat === 'insumo');
        panelCosecha.classList.toggle('visible', cat === 'cosecha');
        panelCantidadInsumo.style.display = cat === 'insumo' ? '' : 'none';
        cantidadKg.disabled = cat !== 'cosecha';
        cantidadInsumo.disabled = cat !== 'insumo';
        refrescarListas();
    }

    function refrescarListas() {
        const almacenId = almacenSelect.value;
        const cat = categoriaSelect.value;

        if (!almacenId || !cat) {
            selInsumo.disabled = true;
            selCosecha.disabled = true;
            return;
        }

        const items = insumosPorCategoria(cat);

        if (cat === 'insumo') {
            llenarSelect(selInsumo, items, items.length ? 'Seleccione insumo del catálogo...' : 'No hay insumos registrados en el sistema', oldInsumo);
            selInsumo.onchange = function () {
                const opt = selInsumo.options[selInsumo.selectedIndex];
                unidadLabel.textContent = opt?.dataset?.unidad || 'und';
                syncHiddenInsumo();
            };
            if (selInsumo.value) selInsumo.onchange();
        }

        if (cat === 'cosecha') {
            llenarSelect(selCosecha, items, items.length ? 'Seleccione cultivo (material de siembra)...' : 'No hay cultivos registrados como Material de Siembra', oldInsumo);
            selCosecha.onchange = syncHiddenInsumo;
            if (selCosecha.value) syncHiddenInsumo();
        }

        syncHiddenInsumo();
    }

    almacenSelect.addEventListener('change', refrescarListas);
    categoriaSelect.addEventListener('change', actualizarPaneles);
    cantidadKg.addEventListener('input', syncCantidad);
    cantidadInsumo.addEventListener('input', syncCantidad);

    document.getElementById('form-ingreso-agr').addEventListener('submit', function () {
        syncHiddenInsumo();
        syncCantidad();
    });

    if (oldCategoria) {
        actualizarPaneles();
    } else if (almacenSelect.value && categoriaSelect.value) {
        refrescarListas();
    }
})();
</script>
@endpush
