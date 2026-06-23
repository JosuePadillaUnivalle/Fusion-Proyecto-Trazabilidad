@php
    $transportistaId = old('transportista_usuarioid', '');
    $transportistaLabel = old('transportista_label', '');
    $vehiculoId = old('vehiculoid', '');
    $vehiculoLabel = old('vehiculo_label', '');
@endphp

{{-- PASO 3: ASIGNACIÓN (compartido agrícola→planta y planta→mayorista) --}}
<div class="wizard-step" data-wizard-step="3">
<div class="env-wizard-panel">
    <div class="env-wizard-panel__head"><i class="fas fa-user-check"></i> Asignación — Camión #1</div>
    <div class="env-wizard-panel__body">
        <p class="text-muted small mb-3" id="wizard-asignacion-subtitulo">
            Elija chofer y vehículo. Si la carga supera la capacidad, puede planificar un segundo camión.
        </p>
        <div class="row mb-3 env-asignacion-cards">
            <div class="col-md-6 mb-3 mb-md-0">
                <label class="small font-weight-bold d-block mb-2">Chofer <span class="text-danger">*</span></label>
                <div class="env-recurso-card" id="card-transportista" role="button" tabindex="0">
                    <div class="env-recurso-card__top">
                        <div class="env-recurso-card__icon"><i class="fas fa-user-tie"></i></div>
                        <div class="env-recurso-card__info">
                            <div class="env-recurso-card__title">{{ $transportistaLabel ?: 'Elegir chofer' }}</div>
                            <div class="env-recurso-card__detalle">
                                <div class="env-recurso-card__line env-recurso-card__estado text-muted" id="card-chofer-estado">Clic para buscar en el catálogo</div>
                            </div>
                        </div>
                    </div>
                    <div class="env-recurso-card__action">
                        <span><i class="fas fa-search mr-1"></i> Buscar chofer</span>
                    </div>
                </div>
                <input type="hidden" name="transportista_usuarioid" id="transportista_usuarioid_create" form="form-pedido" value="{{ $transportistaId }}" required>
                <input type="text" id="txtTransportistaCreate" class="d-none" value="{{ $transportistaLabel }}" tabindex="-1" aria-hidden="true">
            </div>
            <div class="col-md-6">
                <label class="small font-weight-bold d-block mb-2">Vehículo <span class="text-danger">*</span></label>
                <div class="env-recurso-card env-recurso-card--vehiculo" id="card-vehiculo" role="button" tabindex="0">
                    <div class="env-recurso-card__top">
                        <div class="env-recurso-card__icon"><i class="fas fa-truck"></i></div>
                        <div class="env-recurso-card__info">
                            <div class="env-recurso-card__title" id="card-vehiculo-titulo">{{ $vehiculoLabel ?: 'Elegir vehículo' }}</div>
                            <div class="env-recurso-card__detalle" id="card-vehiculo-detalle">
                                <div class="env-recurso-card__line d-none" id="card-vehiculo-sugerencia-wrap">
                                    <span class="env-recurso-card__chip">Sugerido</span>
                                    <span class="env-recurso-card__placa" id="card-vehiculo-sugerencia-placa"></span>
                                </div>
                                <div class="env-recurso-card__line d-none" id="card-vehiculo-cap-wrap">
                                    <i class="fas fa-weight-hanging mr-1 text-muted"></i>
                                    <span id="card-vehiculo-cap"></span>
                                </div>
                                <div class="env-recurso-card__line d-none" id="card-vehiculo-tipo-wrap">
                                    <i class="fas fa-snowflake mr-1 text-muted" id="card-vehiculo-tipo-icon"></i>
                                    <span id="card-vehiculo-tipo"></span>
                                </div>
                                <div class="env-recurso-card__line env-recurso-card__estado text-muted" id="card-vehiculo-estado">Primero elija el chofer</div>
                            </div>
                        </div>
                    </div>
                    <div class="env-recurso-card__action">
                        <span><i class="fas fa-search mr-1"></i> Buscar o cambiar vehículo</span>
                        <small class="env-recurso-card__hint">La sugerencia es orientativa; puede elegir otro manualmente.</small>
                    </div>
                </div>
                <input type="hidden" name="vehiculoid" id="vehiculoid_create" form="form-pedido" value="{{ $vehiculoId }}" required>
                <input type="text" id="txtVehiculoCreate" class="d-none" value="{{ $vehiculoLabel }}" tabindex="-1" aria-hidden="true">
            </div>
        </div>
        <div class="env-sugerencia-vehiculo-panel d-none" id="env-sugerencia-vehiculo" role="status">
            <div class="env-sugerencia-vehiculo-panel__icon"><i class="fas fa-lightbulb"></i></div>
            <div class="env-sugerencia-vehiculo-panel__body" id="env-sugerencia-vehiculo-texto"></div>
        </div>
        <div class="d-none">
            <button type="button" id="btnBuscarTransportistaCreate"></button>
            <button type="button" id="btnBuscarVehiculoCreate" @disabled(! $transportistaId)></button>
        </div>
        <div class="form-row">
            <div class="form-group col-md-5 mb-md-0">
                <label class="small font-weight-bold">Costo del servicio (Bs) <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text">Bs</span></div>
                    <input type="number" name="costo_bs" id="costo_bs" form="form-pedido" class="form-control" min="0" step="1"
                           value="{{ old('costo_bs', '0') }}" placeholder="Se calcula con la ruta" required>
                </div>
                <small class="text-muted d-block" id="wizard-costo-detalle">Se estima al trazar la ruta. Puede ajustarlo.</small>
            </div>
        </div>
        <div class="alert alert-warning small py-2 mt-2 mb-0" id="capacidad-vehiculo-alerta" style="display:none"></div>
        <div class="d-none mt-3" id="capacidad-vehiculo-resumen-wrap">
            <div class="veh-carga-preview-stats env-asignacion-capacidad-stats">
                <div class="veh-carga-preview-stat">
                    <div class="veh-carga-preview-stat__head">
                        <span><i class="fas fa-weight-hanging mr-1"></i> Peso de la carga</span>
                        <strong id="capacidad-vehiculo-pct-peso">—</strong>
                    </div>
                    <div class="veh-carga-preview-bar">
                        <div class="veh-carga-preview-bar__fill veh-carga-preview-bar__fill--peso" id="capacidad-vehiculo-barra-peso" style="width:0%"></div>
                    </div>
                    <div class="veh-carga-preview-stat__foot" id="capacidad-vehiculo-txt-peso">—</div>
                </div>
                <div class="veh-carga-preview-stat">
                    <div class="veh-carga-preview-stat__head">
                        <span><i class="fas fa-cube mr-1"></i> Volumen de la carga</span>
                        <strong id="capacidad-vehiculo-pct-vol">—</strong>
                    </div>
                    <div class="veh-carga-preview-bar">
                        <div class="veh-carga-preview-bar__fill veh-carga-preview-bar__fill--vol" id="capacidad-vehiculo-barra-vol" style="width:0%"></div>
                    </div>
                    <div class="veh-carga-preview-stat__foot" id="capacidad-vehiculo-txt-vol">—</div>
                </div>
            </div>
            <p class="text-muted small mb-2 mt-2" id="capacidad-vehiculo-resumen"></p>
            <button type="button" class="btn btn-sm btn-outline-primary d-none" id="btnVerPreviewVehiculoAsignacion">
                <i class="fas fa-cube mr-1"></i> Ver modelado 3D y ocupación
            </button>
        </div>
    </div>
</div>
<div id="particiones-extra-envio"></div>
<div id="bloque-particion-envio" class="d-none">
    <div class="alert alert-light border small py-2 mb-2">
        <i class="fas fa-truck-loading mr-1 text-muted"></i>
        <strong>¿La carga no cabe en un solo camión?</strong>
        Puede anotar aquí cómo dividirá el envío. El sistema registrará este camión primero;
        el resto lo programa en un segundo envío.
    </div>
    <button type="button" class="env-btn-particion" id="btnAgregarParticionEnvio">
        <i class="fas fa-plus-circle mr-1"></i> Anotar división para otro camión
    </button>
</div>
</div>{{-- wizard step 3 --}}
