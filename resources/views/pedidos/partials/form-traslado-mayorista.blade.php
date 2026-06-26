<form method="POST" action="{{ route('logistica.traslados-planta.store') }}" id="form-traslado-mayorista">
    @csrf

    {{-- PASO 1: RUTA (misma estructura que Agrícola → Planta) --}}
    <div class="wizard-step active" data-wizard-step="1">
    <div class="env-wizard-panel">
        <div class="env-wizard-panel__head"><i class="fas fa-map-marked-alt"></i> Ruta de entrega</div>
        <div class="env-wizard-panel__body">
            @include('pedidos.partials.selector-trayecto')

            <div class="form-row mb-3">
                <div class="form-group col-md-3 mb-md-0">
                    <label class="small font-weight-bold">Código de traslado</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $codigoTrasladoPreview }}" readonly>
                    <small class="text-muted">Se confirma al guardar.</small>
                </div>
                <div class="form-group col-md-3 mb-md-0">
                    <label class="small font-weight-bold">Fecha de entrega deseada <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_entrega_deseada" class="form-control form-control-sm" value="{{ old('fecha_entrega_deseada') }}" required>
                    <small class="text-muted">Obligatoria para programar el traslado.</small>
                </div>
                <div class="form-group col-md-3 mb-md-0">
                    <label class="small font-weight-bold">Hora de recogida</label>
                    <input type="time" id="hora_recogida_traslado" class="form-control form-control-sm" value="{{ old('hora_recogida') }}">
                    <small class="text-muted">Cuándo debe pasar el camión por el origen.</small>
                </div>
                <div class="form-group col-md-3 mb-0">
                    <label class="small font-weight-bold">Hora entrega estimada</label>
                    <input type="time" id="hora_entrega_estimada_traslado" class="form-control form-control-sm bg-light" readonly>
                    <small class="text-muted" id="hora-entrega-traslado-ayuda">Se calcula al trazar la ruta…</small>
                </div>
            </div>

            <div class="alert alert-light border py-2 mb-3 small" id="traslado-paso-ayuda">
                <strong>Paso a paso:</strong> 1) Elija todos los almacenes de planta en orden (mapa o buscador) y el producto en cada uno en el paso 2.
                2) Al final, elija el centro mayorista (destino). La ruta se traza automáticamente.
            </div>

            <div class="env-paso" id="bloque-recogidas-traslado">
                <div class="d-flex align-items-center mb-2">
                    <span class="env-paso__num">1</span>
                    <strong>Puntos de recogida — Almacenes de planta</strong>
                </div>
                <div class="form-group mb-2">
                    <label class="small text-muted mb-1">Recogida 1 <span class="text-danger">*</span></label>
                    <div class="pedido-picker-field">
                        <input type="text" id="txtNombreOrigenTraslado" class="picker-display text-muted" readonly
                               placeholder="Buscar almacén de planta…">
                        <div class="picker-actions">
                            <button type="button" class="btn btn-sm btn-picker-accion" id="btnBuscarOrigenTraslado" title="Buscar almacén">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-picker-accion d-none" id="btnVerProductosOrigenTraslado" title="Ver productos en este almacén">
                                <i class="fas fa-box-open mr-1"></i> Ver
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarOrigenTraslado" title="Quitar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small id="txtOrigenTrasladoCoords" class="form-text text-muted"></small>
                    <small id="traslado-productos-preseleccion-resumen" class="text-success small d-none mt-1 mb-0"></small>
                </div>
                <div id="recogidas-extra-traslado-container"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAgregarRecogidaTraslado">
                    <i class="fas fa-plus mr-1"></i> Agregar otro almacén de planta
                </button>
                <small class="text-muted d-block mt-1">Opcional: cuando el camión recoge en 2 o más plantas antes de entregar al mayorista.</small>
            </div>

            <div class="env-paso" id="bloque-destino-traslado">
                <div class="d-flex align-items-center mb-2">
                    <span class="env-paso__num">2</span>
                    <strong>Destino — Centro mayorista</strong>
                </div>
                <small class="text-muted d-block mb-2" id="traslado-destino-bloqueado-msg">Primero elija la recogida 1 en planta.</small>
                <div class="pedido-picker-field">
                    <input type="text" id="txtNombreDestinoTraslado" class="picker-display text-muted" readonly
                           placeholder="Buscar almacén mayorista…">
                    <div class="picker-actions">
                        <button type="button" class="btn btn-sm btn-picker-accion" id="btnBuscarDestinoTraslado" title="Buscar almacén">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarDestinoTraslado" title="Quitar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <small id="txtDestinoTrasladoCoords" class="form-text text-muted"></small>
            </div>

            <div class="d-none">
                @include('partials.selector-catalogo', [
                    'id' => 'traslado_planta_origen',
                    'name' => 'almacen_planta_origenid',
                    'label' => 'Planta de carga',
                    'icon' => 'fa-industry',
                    'required' => true,
                    'value' => old('almacen_planta_origenid', ''),
                    'endpoint' => route('catalogo-selector.almacenes'),
                    'title' => 'Almacén de planta',
                    'searchPlaceholder' => 'Buscar almacén de planta…',
                    'params' => ['ambito' => 'planta'],
                ])
                @include('partials.selector-catalogo', [
                    'id' => 'traslado_mayorista_destino',
                    'name' => 'almacen_mayorista_destinoid',
                    'label' => 'Centro mayorista',
                    'icon' => 'fa-warehouse',
                    'required' => true,
                    'value' => old('almacen_mayorista_destinoid', ''),
                    'endpoint' => route('catalogo-selector.almacenes'),
                    'title' => 'Almacén mayorista',
                    'searchPlaceholder' => 'Buscar almacén mayorista…',
                    'params' => ['ambito' => 'mayorista'],
                ])
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <button type="button" class="btn btn-sm env-btn-mapa-toggle env-btn-mapa-toggle--mayorista" id="btnVerAlmacenesMapaTraslado">
                    <i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnReiniciarRutaTraslado">
                    <i class="fas fa-redo mr-1"></i> Reiniciar ruta
                </button>
            </div>
            <div id="mapaTrasladoMayorista-wrap" class="mb-2">
                <div id="mapa-traslado-asignacion-flash"></div>
                <div id="mapaTrasladoMayorista"></div>
            </div>
            <small id="rutaResumenTraslado" class="text-muted pedido-mapa-hint d-block"></small>

            <div class="form-row mt-3">
                <div class="form-group col-md-6 mb-md-0">
                    <label class="small font-weight-bold">Instrucciones de recogida <span class="text-muted">(opcional)</span></label>
                    <textarea name="instrucciones_recogida" class="form-control form-control-sm" rows="2"
                        placeholder="Contacto en planta, muelle de carga, horario…">{{ old('instrucciones_recogida') }}</textarea>
                </div>
                <div class="form-group col-md-6 mb-0">
                    <label class="small font-weight-bold">Instrucciones de entrega <span class="text-muted">(opcional)</span></label>
                    <textarea name="instrucciones_entrega" class="form-control form-control-sm" rows="2"
                        placeholder="Recepción en centro mayorista, báscula…">{{ old('instrucciones_entrega') }}</textarea>
                </div>
            </div>
            <input type="hidden" name="nombre" value="{{ old('nombre') }}">
        </div>
    </div>
    </div>{{-- wizard step 1 --}}

    {{-- PASO 2: CARGA --}}
    <div class="wizard-step" data-wizard-step="2">
    <div class="env-wizard-panel">
        <div class="env-wizard-panel__head"><i class="fas fa-box-open"></i> Carga y productos</div>
        <div class="env-wizard-panel__body">
            <p class="text-muted small mb-3">
                Indique qué <strong>productos terminados</strong> recoger en cada almacén de planta:
                presentación comercial, lote y cantidad. El sistema calcula los kg y valida stock por almacén.
            </p>
            <div id="traslado-productos-recogida-container"></div>
            @error('detalles')<small class="text-danger d-block">{{ $message }}</small>@enderror
            @error('detalles.*.insumoid')<small class="text-danger d-block">{{ $message }}</small>@enderror
        </div>
    </div>
    </div>{{-- wizard step 2 --}}
</form>
