<input type="hidden" name="tipo_solicitud" id="tipo_solicitud" value="stock">

<div id="bloqueStock">
    <p class="small text-muted mb-2">
        Productos con stock en almacén mayorista. Elija producto y presentación; el pedido irá al mayorista que seleccione.
    </p>
    <input type="hidden" name="insumoid" id="insumoid_real" value="{{ old('insumoid', '') }}">
    <input type="hidden" name="almacen_mayorista_origenid" id="almacen_mayorista_origenid" value="{{ old('almacen_mayorista_origenid', '') }}">
    <div class="pdv-producto-grid pdv-producto-grid--duo">
        <div class="pdv-producto-col">
            <label class="pdv-field-label">Producto <span class="text-danger">*</span></label>
            @include('partials.selector-catalogo', [
                'id' => 'dist_producto_mayorista',
                'name' => '_catalogo_producto_id',
                'value' => old('_catalogo_producto_id', ''),
                'labelSelected' => old('_producto_mayorista_label', ''),
                'endpoint' => route('catalogo-selector.productos-mayorista-pdv'),
                'title' => 'Productos por mayorista',
                'searchPlaceholder' => 'Nombre del producto…',
                'required' => true,
                'inputGroup' => true,
                'registerScript' => false,
            ])
            <div id="panelProductoMeta" class="pdv-producto-meta d-none">
                <div class="pdv-producto-meta__row">
                    <span class="pdv-producto-meta__label">Mayorista</span>
                    <span id="txtMetaMayorista" class="pdv-producto-meta__value"></span>
                </div>
                <div class="pdv-producto-meta__row">
                    <span class="pdv-producto-meta__label">Ubicación</span>
                    <span id="txtMetaUbicacion" class="pdv-producto-meta__value"></span>
                </div>
                <div id="rowMetaStock" class="pdv-producto-meta__row d-none">
                    <span class="pdv-producto-meta__label">Stock</span>
                    <span id="txtMetaStockKg" class="pdv-producto-meta__value"></span>
                </div>
            </div>
        </div>
        <div class="pdv-producto-col">
            <label class="pdv-field-label" for="selectPresentacionMayorista">Presentación <span class="text-danger">*</span></label>
            <div class="pdv-producto-pick {{ old('insumoid') ? '' : 'is-locked' }}" id="pdvPresentacionPick">
                <select name="insumo_presentacionid" id="selectPresentacionMayorista" class="form-control form-control-sm" required disabled>
                    <option value="">Elegir producto primero…</option>
                </select>
                <small id="txtPresentacionAyuda" class="form-text text-muted mb-0 d-none">Cargando presentaciones…</small>
                <small id="txtPresentacionVacia" class="form-text text-muted mb-0 d-none">Este producto no tiene presentaciones con stock. Elija otro producto.</small>
            </div>
            <div id="panelStockDisponible" class="pdv-stock-disponible d-none">
                <i class="fas fa-check-circle text-success"></i>
                <span id="txtStockDisponible"></span>
            </div>
        </div>
    </div>
</div>

<div class="pdv-cantidad-row mt-2">
    <div class="pdv-cantidad-col">
        <label class="pdv-field-label" for="cantidad">Cantidad <span class="text-danger">*</span></label>
        <div class="pdv-cantidad-wrap input-group has-unidad" id="wrapCantidad">
            <input type="number" step="1" min="1" name="cantidad" id="cantidad" class="form-control" required
                value="{{ old('cantidad') }}" placeholder="0">
            <div class="input-group-append">
                <span class="input-group-text pdv-unidad-append" id="badgeUnidad">{{ old('_unidad_cantidad', 'unidades') }}</span>
            </div>
        </div>
        <small class="form-text text-muted mb-0" id="txtAyudaCantidad">Indique cuántas unidades necesita.</small>
        <div id="alertaStockExcedido" class="pdv-stock-alerta d-none">
            <i class="fas fa-exclamation-triangle"></i> <span id="txtAlertaStockExcedido">Supera el stock disponible.</span>
        </div>
    </div>
</div>

<div id="panelCapacidadPdv" class="pdv-capacidad-preview d-none">
    <div class="pdv-capacidad-preview__head">
        <span class="pdv-capacidad-preview__badge"><i class="fas fa-warehouse mr-1"></i> Almacén del punto de venta</span>
        <strong id="txtCapacidadPdvNombre" class="d-block mt-1"></strong>
    </div>
    <div class="pdv-capacidad-preview__ingreso d-none" id="rowCapacidadIngreso">
        <span class="text-muted">Este pedido ocupará</span>
        <strong id="txtCapacidadIngresoKg" class="text-dark"></strong>
    </div>
    <div class="pdv-capacidad-preview__stats small">
        <span><strong id="txtCapacidadDisponible">—</strong> kg libres</span>
        <span class="text-muted">de <span id="txtCapacidadTotal">—</span> kg</span>
        <span class="text-muted">(<span id="txtCapacidadPorcentaje">0</span> % ocupado)</span>
    </div>
    <div class="pdv-capacidad-bar mt-1">
        <div class="pdv-capacidad-bar__base" id="barCapacidadBase"></div>
        <div class="pdv-capacidad-bar__proyeccion d-none" id="barCapacidadProyeccion"></div>
    </div>
    <p class="pdv-capacidad-preview__hint mb-0" id="txtCapacidadHint">
        <i class="fas fa-info-circle mr-1"></i> Seleccione punto de venta, producto y cantidad para ver el espacio que ocupará.
    </p>
    <div id="alertaCapacidadPdv" class="pdv-capacidad-alerta d-none">
        <i class="fas fa-exclamation-triangle"></i>
        <span id="txtAlertaCapacidadPdv">Supera la capacidad disponible del almacén del punto de venta.</span>
    </div>
</div>

<style>
.pdv-producto-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: .65rem 1rem;
    align-items: start;
}
.pdv-cantidad-row { max-width: 9.5rem; }
.pdv-producto-meta {
    margin-top: .45rem; padding: .45rem .6rem;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;
    font-size: .76rem; line-height: 1.35;
}
.pdv-producto-meta__row {
    display: grid; grid-template-columns: 4.5rem 1fr; gap: .35rem .5rem;
    padding: .15rem 0;
}
.pdv-producto-meta__row + .pdv-producto-meta__row { border-top: 1px dashed #e2e8f0; }
.pdv-producto-meta__label {
    font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
    color: #64748b; font-size: .66rem;
}
.pdv-producto-meta__value { color: #334155; font-weight: 600; word-break: break-word; }
.pdv-stock-disponible {
    display: flex; align-items: flex-start; gap: .35rem;
    margin-top: .35rem; font-size: .78rem; font-weight: 600; color: #166534;
    line-height: 1.3;
}
.pdv-stock-alerta {
    margin-top: .3rem; font-size: .76rem; font-weight: 600; color: #b45309;
}
.pdv-cantidad-wrap.is-invalid .form-control,
.pdv-cantidad-wrap.is-invalid .input-group-text {
    border-color: #f59e0b !important;
}
.pdv-capacidad-preview {
    margin-top: .75rem;
    padding: .65rem .75rem;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    background: linear-gradient(180deg, #f8fbff, #f0f7ff);
    font-size: .8rem;
}
.pdv-capacidad-preview.is-excedido {
    border-color: #fecaca;
    background: linear-gradient(180deg, #fff5f5, #fef2f2);
}
.pdv-capacidad-preview__badge {
    display: inline-block;
    font-size: .66rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #1d4ed8;
    background: rgba(59, 130, 246, .12);
    border-radius: 999px;
    padding: .15rem .55rem;
}
.pdv-capacidad-preview__ingreso { margin: .35rem 0 .2rem; }
.pdv-capacidad-preview__stats { color: #475569; }
.pdv-capacidad-preview__hint { margin-top: .4rem; font-size: .74rem; color: #64748b; }
.pdv-capacidad-bar {
    position: relative;
    height: 8px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
}
.pdv-capacidad-bar__base,
.pdv-capacidad-bar__proyeccion {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    border-radius: 999px;
    transition: width .25s ease;
}
.pdv-capacidad-bar__base { background: #64748b; z-index: 1; }
.pdv-capacidad-bar__proyeccion { background: #22c55e; z-index: 2; opacity: .85; }
.pdv-capacidad-preview.is-excedido .pdv-capacidad-bar__proyeccion { background: #ef4444; }
.pdv-capacidad-alerta {
    margin-top: .45rem;
    font-size: .76rem;
    font-weight: 600;
    color: #b45309;
}
.pdv-capacidad-preview.is-excedido .pdv-capacidad-alerta { color: #b91c1c; }
</style>
