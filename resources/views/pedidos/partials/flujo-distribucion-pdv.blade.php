{{-- Mayorista → Punto de venta — wizard 4 pasos --}}
<form method="POST" action="{{ route('punto-venta.pedidos.store') }}" id="form-pedido-dist-pdv">
    @csrf

    {{-- PASO 1: RUTA (misma estructura que Agrícola → Planta) --}}
    <div class="wizard-step active" data-wizard-step="1">
    <div class="env-wizard-panel">
        <div class="env-wizard-panel__head"><i class="fas fa-map-marked-alt"></i> Ruta de entrega</div>
        <div class="env-wizard-panel__body">
            @include('pedidos.partials.selector-trayecto')

            <div class="form-row mb-3">
                <div class="form-group col-md-3 mb-md-0">
                    <label class="small font-weight-bold">Código de envío</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $numeroSolicitudDist }}" readonly>
                    <small class="text-muted">Se confirma al guardar.</small>
                </div>
                <div class="form-group col-md-3 mb-md-0">
                    <label class="small font-weight-bold">Fecha de entrega deseada <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_entrega_deseada" class="form-control form-control-sm" value="{{ old('fecha_entrega_deseada') }}" required>
                    <small class="text-muted">Obligatoria para programar la distribución.</small>
                </div>
                <div class="form-group col-md-3 mb-md-0">
                    <label class="small font-weight-bold">Hora de recogida</label>
                    <input type="time" name="hora_entrega_deseada" id="hora_recogida_pdv" class="form-control form-control-sm" value="{{ old('hora_entrega_deseada') }}">
                    <small class="text-muted">Cuándo debe pasar el camión por el origen.</small>
                </div>
                <div class="form-group col-md-3 mb-0">
                    <label class="small font-weight-bold">Hora entrega estimada</label>
                    <input type="time" id="hora_entrega_estimada_pdv" class="form-control form-control-sm bg-light" readonly>
                    <small class="text-muted" id="hora-entrega-pdv-ayuda">Se calcula al trazar la ruta…</small>
                </div>
            </div>

            <div class="alert alert-light border py-2 mb-3 small" id="pdv-paso-ayuda">
                <strong>Paso a paso:</strong>
                @if($esAdminPdv ?? false)
                    1) Elija el almacén mayorista (origen) en el mapa o en el buscador.
                    2) Luego el punto de venta (destino). La ruta se traza automáticamente.
                @else
                    Elija el punto de venta (destino) en el mapa o en el buscador. La ruta se traza automáticamente.
                @endif
            </div>

            @if($esAdminPdv ?? false)
            <div class="env-paso" id="bloque-origen-pdv">
                <div class="d-flex align-items-center mb-2">
                    <span class="env-paso__num">1</span>
                    <strong>Origen</strong>
                </div>
                <div class="form-group mb-2">
                    <label class="small text-muted mb-1">Almacén mayorista <span class="text-danger">*</span></label>
                    <div class="pedido-picker-field">
                        <input type="text" id="txtNombreOrigenPdv" class="picker-display text-muted" readonly
                               placeholder="Buscar almacén mayorista…" value="{{ $oldAlmacenLabel ?? '' }}">
                        <div class="picker-actions">
                            <button type="button" class="btn btn-sm btn-picker-accion" id="btnBuscarOrigenPdv" title="Buscar almacén">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-picker-accion d-none" id="btnVerProductosOrigenPdv" title="Ver productos en este almacén">
                                <i class="fas fa-box-open mr-1"></i> Ver
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarOrigenPdv" title="Quitar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small id="txtOrigenPdvCoords" class="form-text text-muted"></small>
                    <small id="pdv-producto-preseleccion-resumen" class="text-success small d-none mt-1 mb-0"></small>
                </div>
            </div>
            @endif

            <div class="env-paso" id="bloque-destino-pdv">
                <div class="d-flex align-items-center mb-2">
                    <span class="env-paso__num">{{ ($esAdminPdv ?? false) ? '2' : '1' }}</span>
                    <strong>Destino</strong>
                </div>
                <div class="pedido-picker-field">
                    <input type="text" id="txtNombreDestinoPdv" class="picker-display text-muted" readonly
                           placeholder="Buscar punto de venta…" value="{{ $oldPuntoLabel ?? '' }}">
                    <div class="picker-actions">
                        <button type="button" class="btn btn-sm btn-picker-accion" id="btnBuscarDestinoPdv" title="Buscar punto de venta">
                            <i class="fas fa-search"></i>
                        </button>
                        @if($esAdminPdv ?? false)
                        <button type="button" class="btn btn-sm btn-picker-accion" id="btnFiltrarDestinoPdv" title="Filtrar por minorista">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarDestinoPdv" title="Quitar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <small id="txtDestinoPdvCoords" class="form-text text-muted"></small>
                <small id="pdv-filtro-minorista-resumen" class="text-muted small d-none mt-1 mb-0"></small>
            </div>

            <div class="d-none">
                @if($esAdminPdv ?? false)
                @include('partials.selector-catalogo', [
                    'id' => 'pdv_unificado_minorista',
                    'name' => 'minorista_usuarioid',
                    'value' => old('minorista_usuarioid', $oldMinoristaId ?? ''),
                    'labelSelected' => $oldMinoristaLabel ?? '',
                    'endpoint' => route('catalogo-selector.usuarios'),
                    'params' => ['roles' => 'minorista'],
                    'title' => 'Buscar minorista',
                    'searchPlaceholder' => 'Nombre o usuario…',
                    'required' => true,
                ])
                @include('partials.selector-catalogo', [
                    'id' => 'pdv_unificado_almacen',
                    'name' => 'almacen_mayorista_origenid',
                    'value' => old('almacen_mayorista_origenid', ''),
                    'labelSelected' => $oldAlmacenLabel ?? '',
                    'endpoint' => route('catalogo-selector.almacenes'),
                    'params' => ['ambito' => 'mayorista'],
                    'title' => 'Almacén mayorista',
                    'searchPlaceholder' => 'Nombre…',
                    'required' => true,
                ])
                @endif
                @include('partials.selector-catalogo', [
                    'id' => 'pdv_unificado_punto',
                    'name' => 'puntoventaid',
                    'value' => old('puntoventaid', $oldPuntoId ?? ''),
                    'labelSelected' => $oldPuntoLabel ?? '',
                    'endpoint' => route('catalogo-selector.puntos-venta'),
                    'title' => 'Puntos de venta',
                    'searchPlaceholder' => 'Nombre o dirección…',
                    'required' => true,
                    'params' => [],
                ])
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <button type="button" class="btn btn-sm env-btn-mapa-toggle env-btn-mapa-toggle--pdv" id="btnVerPuntosMapaPdv">
                    <i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa
                </button>
                @if($esAdminPdv ?? false)
                <button type="button" class="btn btn-sm env-btn-mapa-toggle env-btn-mapa-toggle--pdv" id="btnFiltrarMinoristaPdv">
                    <i class="fas fa-filter mr-1"></i> Filtrar por Minorista
                </button>
                @endif
                <button type="button" class="btn btn-sm btn-outline-danger ml-auto" id="btnReiniciarRutaPdv">
                    <i class="fas fa-redo mr-1"></i> Reiniciar ruta
                </button>
            </div>
            <div id="mapaPdvDistribucion-wrap" class="mb-2">
                <div id="mapa-pdv-asignacion-flash"></div>
                <div id="mapaPdvDistribucion"></div>
            </div>
            <small id="rutaResumenPdv" class="text-muted pedido-mapa-hint d-block"></small>

            <div class="form-row mt-3">
                <div class="form-group col-md-6 mb-md-0">
                    <label class="small font-weight-bold">Instrucciones de recogida <span class="text-muted">(opcional)</span></label>
                    @if($esAdminPdv ?? false)
                    <textarea class="form-control form-control-sm" rows="2"
                        placeholder="Contacto en almacén mayorista, acceso, horario…"></textarea>
                    @else
                    <textarea class="form-control form-control-sm bg-light" rows="2" readonly disabled
                        placeholder="Su almacén mayorista asignado."></textarea>
                    @endif
                </div>
                <div class="form-group col-md-6 mb-0">
                    <label class="small font-weight-bold">Instrucciones de entrega <span class="text-muted">(opcional)</span></label>
                    <textarea name="observaciones" class="form-control form-control-sm" rows="2"
                        placeholder="Horario en tienda, contacto en mostrador…">{{ old('observaciones') }}</textarea>
                </div>
            </div>
        </div>
    </div>
    </div>{{-- wizard step 1 --}}

    {{-- PASO 2: CARGA --}}
    <div class="wizard-step" data-wizard-step="2">
    <div class="env-wizard-panel">
        <div class="env-wizard-panel__head"><i class="fas fa-box-open"></i> Carga y productos</div>
        <div class="env-wizard-panel__body">
            <p class="small text-muted mb-3">
                Indique el producto y cantidad a distribuir desde el almacén mayorista hacia el punto de venta.
            </p>
            <div class="form-row">
                <div class="form-group col-md-7">
                    <label class="small font-weight-bold">Producto <span class="text-danger">*</span></label>
                    @include('partials.selector-catalogo', [
                        'id' => 'pdv_unificado_producto',
                        'name' => 'insumoid',
                        'value' => old('insumoid', ''),
                        'labelSelected' => $oldProductoLabel ?? '',
                        'endpoint' => route('catalogo-selector.insumos'),
                        'params' => array_filter([
                            'ambito_mayorista' => '1',
                            'solo_con_stock' => '1',
                            'requiere_almacen' => ($esAdminPdv ?? false) ? '1' : '0',
                            'almacenid' => old('almacen_mayorista_origenid') ?: null,
                        ]),
                        'title' => 'Producto mayorista',
                        'searchPlaceholder' => 'Nombre del producto…',
                        'required' => true,
                        'inputGroup' => true,
                    ])
                </div>
                <div class="form-group col-md-5">
                    <label class="small font-weight-bold">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="cantidad" id="pdv_cantidad" class="form-control form-control-sm" required
                        value="{{ old('cantidad') }}" placeholder="Ej: 120">
                </div>
            </div>
        </div>
    </div>
    </div>{{-- wizard step 2 --}}

    {{-- PASO 3: ASIGNACIÓN (logística asigna después; paso informativo) --}}
    <div class="wizard-step" data-wizard-step="3">
    <div class="env-wizard-panel">
        <div class="env-wizard-panel__head"><i class="fas fa-user-check"></i> Asignación — Camión #1</div>
        <div class="env-wizard-panel__body">
            <div class="alert alert-light border small mb-0 py-3">
                <i class="fas fa-info-circle text-warning mr-1"></i>
                En este trayecto el <strong>mayorista</strong> revisa stock y prepara el envío.
                La asignación de chofer y vehículo se realiza cuando el pedido sea aprobado.
            </div>
        </div>
    </div>
    </div>{{-- wizard step 3 --}}

    {{-- PASO 4: CONFIRMAR --}}
    <div class="wizard-step" data-wizard-step="4">
    <div class="env-wizard-panel">
        <div class="env-wizard-panel__head"><i class="fas fa-clipboard-check"></i> Resumen del envío</div>
        <div class="env-wizard-panel__body">
            <div class="env-confirm-grid">
                <div class="env-confirm-tile env-confirm-tile--origen">
                    <div class="env-confirm-tile__label"><i class="fas fa-warehouse text-warning mr-1"></i> Origen</div>
                    <div class="env-confirm-tile__value" id="conf-pdv-origen">—</div>
                </div>
                <div class="env-confirm-tile env-confirm-tile--destino">
                    <div class="env-confirm-tile__label"><i class="fas fa-store text-orange mr-1"></i> Punto de venta</div>
                    <div class="env-confirm-tile__value" id="conf-pdv-destino">—</div>
                </div>
                <div class="env-confirm-tile env-confirm-tile--carga">
                    <div class="env-confirm-tile__label"><i class="fas fa-box-open text-primary mr-1"></i> Producto</div>
                    <div class="env-confirm-tile__value" id="conf-pdv-producto">—</div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><span class="text-muted small">Fecha entrega</span><div class="font-weight-bold" id="conf-pdv-fecha">—</div></div>
                <div class="col-md-6"><span class="text-muted small">Hora preferida</span><div class="font-weight-bold" id="conf-pdv-hora">—</div></div>
            </div>
            <p class="small text-muted mb-1">Ruta</p>
            <p class="small mb-0" id="conf-pdv-ruta">—</p>
        </div>
    </div>
    </div>{{-- wizard step 4 --}}
</form>
