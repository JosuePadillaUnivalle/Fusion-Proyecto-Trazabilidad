@extends('layouts.app')

@section('title', 'Nuevo envío')
@section('page_title', 'Nuevo envío')

@push('styles')
@include('logistica.partials.mapa-ruta-styles')
@include('pedidos.partials.wizard-envio-estilos')
@include('pedidos.partials.pdv-envio-carga-styles')
<style>
#mapaPedidoEntrega { height: 380px; min-height: 380px; width: 100%; border-radius: 10px; border: 1px solid #d1e7d4; background: #e8eef4; }
#mapaTrasladoMayorista { height: 380px; min-height: 380px; width: 100%; border-radius: 10px; border: 1px solid #e9e5ff; background: #e8eef4; }
#mapaTrasladoMayorista.leaflet-container { font-family: inherit; z-index: 1; }
#mapaTrasladoMayorista-wrap { position: relative; }
#mapaPdvDistribucion { height: 380px; min-height: 380px; width: 100%; border-radius: 10px; border: 1px solid #fed7aa; background: #e8eef4; }
#mapaPdvDistribucion.leaflet-container { font-family: inherit; z-index: 1; }
#mapaPdvDistribucion-wrap { position: relative; }
#mapa-pdv-asignacion-flash {
    position: absolute; z-index: 1000; top: 10px; left: 50%; transform: translateX(-50%);
    background: #d97706; color: #fff; padding: .4rem .85rem; border-radius: 8px;
    font-size: .8rem; display: none; pointer-events: none;
}
#mapa-traslado-asignacion-flash {
    position: absolute; z-index: 1000; top: 10px; left: 50%; transform: translateX(-50%);
    background: #6d28d9; color: #fff; padding: .4rem .85rem; border-radius: 8px;
    font-size: .8rem; display: none; pointer-events: none;
}
.almacen-mapa-pin--traslado-planta { background: #7c3aed !important; }
.almacen-mapa-pin--traslado-mayorista { background: #ea580c !important; }
.almacen-mapa-pin--pdv-mayorista { background: #ea580c !important; }
.almacen-mapa-pin--pdv-tienda { background: #2563eb !important; }
.tpm-btn-mapa-toggle,
.env-btn-mapa-toggle--mayorista {
    border: 1.5px solid #6d28d9;
    color: #6d28d9;
    background: #fff;
    border-radius: 8px;
    font-weight: 600;
    font-size: .82rem;
    padding: .35rem .85rem;
    transition: background .15s ease, border-color .15s ease;
}
.tpm-btn-mapa-toggle:hover,
.tpm-btn-mapa-toggle:focus,
.env-btn-mapa-toggle--mayorista:hover,
.env-btn-mapa-toggle--mayorista:focus {
    background: #f5f3ff;
    color: #6d28d9;
    border-color: #6d28d9;
    box-shadow: none;
}
.tpm-btn-mapa-toggle.active,
.env-btn-mapa-toggle--mayorista.active {
    background: #f5f3ff;
    color: #6d28d9;
    border-color: #6d28d9;
    box-shadow: none;
}
.tpm-mapa-card .card-header { border-bottom: 1px solid #ede9fe; }
.traslado-producto-row {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    margin-bottom: .75rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
}
.traslado-producto-row__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .75rem;
    padding-bottom: .5rem;
    border-bottom: 1px solid #f1f5f9;
}
.traslado-producto-row__head label {
    font-size: .8rem;
    font-weight: 700;
    color: #334155;
    margin: 0;
}
.traslado-producto-row .field-label {
    display: block;
    font-size: .72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #64748b;
    margin-bottom: .35rem;
}
.traslado-producto-row .lbl-stock-traslado {
    display: block;
    font-size: .75rem;
    color: #15803d;
    font-weight: 600;
    margin-top: .35rem;
}
.traslado-producto-row .lbl-equiv-traslado {
    display: block;
    font-size: .75rem;
    color: #6d28d9;
    font-weight: 600;
    margin-top: .35rem;
}
.traslado-producto-row .lbl-equiv-traslado.is-error {
    color: #b91c1c;
}
.traslado-producto-row.is-stock-error {
    border-color: #fecaca;
    background: #fffafb;
}
.traslado-producto-row .js-bloque-presentacion.d-none + .js-bloque-cantidad-kg .field-label::after {
    content: '';
}
.pedido-mapa-hint { font-size: .875rem; color: #64748b; }
.custom-marker { background: transparent; border: none; }
.pedido-picker-field {
    display: flex; align-items: stretch; gap: 0;
    border: 2px solid #dee2e6; border-radius: 10px; overflow: hidden; background: #fff;
}
.pedido-picker-field:focus-within { border-color: #2c5530; box-shadow: 0 0 0 .15rem rgba(44,85,48,.12); }
.pedido-picker-field .picker-display {
    flex: 1; border: 0; background: transparent; padding: .55rem .85rem;
    font-size: .9rem; min-height: 42px;
}
.pedido-picker-field .picker-actions { display: flex; border-left: 1px solid #e5e7eb; }
.pedido-picker-field .picker-actions .btn { border-radius: 0; border: 0; padding: 0 .85rem; font-weight: 600; font-size: .85rem; }
.origen-extra-row {
    border: 1px dashed #cbd5e1; border-radius: 10px; padding: .75rem; margin-bottom: .5rem; background: #f8fafc;
}
.ruta-bloqueada { opacity: .55; pointer-events: none; }
.almacen-mapa-marker { background: transparent !important; border: none !important; }
.almacen-mapa-pin {
    width: 32px; height: 32px; border-radius: 50%; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.35); cursor: pointer;
    transition: transform .12s ease;
}
.almacen-mapa-pin:hover { transform: scale(1.12); }
.almacen-mapa-pin.is-selected { box-shadow: 0 0 0 3px #fbbf24, 0 2px 8px rgba(0,0,0,.35); }
.leaflet-tooltip.almacen-mapa-tooltip {
    background: #1e293b; color: #fff; border: 0; border-radius: 8px;
    font-size: .8rem; font-weight: 600; padding: .35rem .65rem;
    box-shadow: 0 4px 12px rgba(0,0,0,.2);
}
.leaflet-tooltip.almacen-mapa-tooltip::before { border-top-color: #1e293b; }
#mapa-asignacion-flash {
    position: absolute; z-index: 1000; top: 10px; left: 50%; transform: translateX(-50%);
    background: #1e293b; color: #fff; padding: .4rem .85rem; border-radius: 8px;
    font-size: .8rem; display: none; pointer-events: none;
}
#mapaPedidoEntrega-wrap { position: relative; }
.env-destino-pick { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin-bottom: 1.25rem; }
.env-destino-opcion {
    border: 2px solid #e2e8f0; border-radius: 14px; padding: 1rem 1.1rem;
    background: #fff; cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease; text-align: left;
}
.env-destino-opcion:hover { border-color: #94a3b8; box-shadow: none; }
.env-destino-opcion--planta.is-active { border-color: #2c5530; background: #f8fafc; box-shadow: none; }
.env-destino-opcion--mayorista.is-active { border-color: #6d28d9; background: #f8fafc; box-shadow: none; }
.env-destino-opcion--pdv.is-active { border-color: #d97706; background: #f8fafc; box-shadow: none; }
.env-destino-opcion__icon {
    width: 40px; height: 40px; border-radius: 8px; display: inline-flex;
    align-items: center; justify-content: center; margin-bottom: .5rem; font-size: 1.1rem;
}
.env-destino-opcion__title { font-weight: 700; color: #1e293b; display: block; margin-bottom: .2rem; }
.env-destino-opcion__desc { font-size: .78rem; color: #64748b; margin: 0; }
.env-paso{border:2px solid #e2e8f0;border-radius:12px;padding:1rem;margin-bottom:1rem;background:#fafbfc}
.tpm-paso{border:2px solid #e2e8f0;border-radius:12px;padding:1rem;margin-bottom:1rem;background:#fafbfc}
.tpm-paso__num{width:28px;height:28px;border-radius:50%;background:#6d28d9;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;margin-right:.5rem}
@media (max-width: 992px) { .env-destino-pick { grid-template-columns: 1fr; } }
.producto-recogida-row .js-bloque-empaque .env-carga-campos { align-items: flex-start !important; }
.env-carga-campo { display: flex; flex-direction: column; gap: .2rem; }
.env-carga-campo__lbl { font-size: .78rem; color: #64748b; margin: 0; line-height: 1.25; }
.env-empaque-hint { font-size: .75rem; line-height: 1.35; margin-top: .15rem; display: block; }
.producto-recogida-row .js-resumen-carga:empty { display: none !important; padding: 0 !important; margin: 0 !important; }
</style>
@endpush

@section('content')
    <div class="card card-outline card-primary border-0 shadow-sm modulo-env" style="border-radius:14px;">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-truck mr-2" style="color:#2c5530"></i> Nuevo envío</h3>
                <span class="badge badge-light border text-muted font-weight-normal">Asistente guiado</span>
            </div>
        </div>
        <div class="card-body">
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif

            <div id="env-wizard-unificado" class="env-wizard-wrap" data-tema="{{ $destinoInicial === 'mayorista' ? 'mayorista' : ($destinoInicial === 'punto-venta' ? 'punto-venta' : 'planta') }}">
            @include('pedidos.partials.wizard-progress-bar')

            <div id="flujo-planta" @if(in_array($destinoInicial, ['mayorista', 'punto-venta'], true)) class="d-none" @endif>
            <form action="{{ route('pedidos.store') }}" method="POST" id="form-pedido" novalidate>
                @csrf

                {{-- PASO 1: RUTA --}}
                <div class="wizard-step active" data-wizard-step="1">
                <div class="env-wizard-panel">
                    <div class="env-wizard-panel__head"><i class="fas fa-map-marked-alt"></i> Ruta de entrega</div>
                    <div class="env-wizard-panel__body">
                @include('pedidos.partials.selector-trayecto')

                <div class="form-row mb-3">
                    <div class="form-group col-md-3 mb-md-0">
                        <label class="small font-weight-bold">Código de envío</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $numeroSolicitud }}" readonly>
                        <small class="text-muted">Se confirma al guardar.</small>
                    </div>
                    <div class="form-group col-md-3 mb-md-0">
                        <label class="small font-weight-bold">Fecha de entrega deseada <span class="text-danger">*</span></label>
                        <input type="date" name="fechaEntregaDeseada" id="fechaEntregaDeseada" class="form-control form-control-sm" value="{{ old('fechaEntregaDeseada') }}" required>
                        <small class="text-muted">Obligatoria para programar el envío.</small>
                        @error('fechaEntregaDeseada')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3 mb-md-0">
                        <label class="small font-weight-bold">Hora de recogida</label>
                        <input type="time" name="hora_recogida" id="hora_recogida" class="form-control form-control-sm" value="{{ old('hora_recogida') }}">
                        <small class="text-muted">Cuándo debe pasar el camión por el origen.</small>
                    </div>
                    <div class="form-group col-md-3 mb-0">
                        <label class="small font-weight-bold">Hora entrega estimada</label>
                        <input type="time" name="hora_entrega_estimada" id="hora_entrega_estimada" class="form-control form-control-sm bg-light" value="{{ old('hora_entrega_estimada') }}" readonly>
                        <small class="text-muted" id="hora-entrega-ayuda">Se calcula al trazar la ruta…</small>
                    </div>
                </div>

                        <div class="alert alert-light border py-2 mb-3 small" id="ruta-paso-ayuda">
                            <strong>Paso a paso:</strong> 1) Elija todos los almacenes de recogida en orden (mapa o buscador).
                            2) Al final, elija el almacén de planta (destino). La ruta se traza automáticamente.
                        </div>

                        <div class="env-paso" id="bloque-recogidas">
                            <div class="d-flex align-items-center mb-2">
                                <span class="env-paso__num">1</span>
                                <strong>Puntos de recogida — Almacenes agrícolas</strong>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-muted mb-1">Recogida 1 <span class="text-danger">*</span></label>
                                <div class="pedido-picker-field">
                                    <input type="text" id="txtNombreOrigen" class="picker-display text-muted" readonly
                                           placeholder="Buscar almacén agrícola…" value="{{ old('origen_direccion') }}">
                                    <div class="picker-actions">
                                        <button type="button" class="btn btn-sm btn-picker-accion" id="btnBuscarOrigen" title="Buscar almacén">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-picker-accion d-none" id="btnVerProductosOrigen" title="Ver productos en este almacén">
                                            <i class="fas fa-box-open mr-1"></i> Ver
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarOrigen" title="Quitar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <small id="txtOrigenCoords" class="form-text text-muted"></small>
                            </div>
                            <div id="recogidas-extra-container"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-agregar-recogida" id="btnAgregarRecogida">
                                <i class="fas fa-plus mr-1"></i> Agregar otro almacén de recogida
                            </button>
                            <small class="text-muted d-block mt-1">Opcional: útil cuando el camión recoge en 2 o más almacenes antes de ir a planta.</small>
                        </div>

                        <div class="env-paso" id="bloque-destino">
                            <div class="d-flex align-items-center mb-2">
                                <span class="env-paso__num">2</span>
                                <strong>Destino (entrega) — Almacén de planta</strong>
                            </div>
                            <small class="text-muted d-block mb-2" id="destino-bloqueado-msg">Complete las recogidas antes de elegir destino.</small>
                            <div class="pedido-picker-field">
                                <input type="text" id="txtNombreDestino" class="picker-display text-muted" readonly
                                       placeholder="Buscar almacén de planta…" value="{{ old('direccion_texto') }}">
                                <div class="picker-actions">
                                    <button type="button" class="btn btn-sm btn-picker-accion" id="btnBuscarDestino" title="Buscar almacén">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarDestino" title="Quitar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <small id="txtDestinoCoords" class="form-text text-muted"></small>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <button type="button" class="btn btn-sm env-btn-mapa-toggle env-btn-mapa-toggle--planta" id="btnVerAlmacenesMapa">
                                <i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnReiniciarRuta">
                                <i class="fas fa-redo mr-1"></i> Reiniciar ruta
                            </button>
                        </div>
                        <div id="mapaPedidoEntrega-wrap" class="mb-2">
                            <div id="mapa-asignacion-flash"></div>
                            <div id="mapaPedidoEntrega"></div>
                        </div>
                        <small id="rutaResumen" class="text-muted pedido-mapa-hint d-block"></small>

                        <input type="hidden" name="origen_latitud" id="origen_latitud" value="{{ old('origen_latitud') }}">
                        <input type="hidden" name="origen_longitud" id="origen_longitud" value="{{ old('origen_longitud') }}">
                        <input type="hidden" name="origen_direccion" id="origen_direccion" value="{{ old('origen_direccion') }}">
                        <input type="hidden" name="origen_almacenid" id="origen_almacenid" value="{{ old('origen_almacenid') }}">
                        <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud') }}">
                        <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud') }}">
                        <input type="hidden" name="direccion_texto" id="direccion_texto" value="{{ old('direccion_texto') }}">
                        @error('latitud')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        @error('origen_latitud')<small class="text-danger d-block">{{ $message }}</small>@enderror

                <div class="form-row mt-3">
                    <div class="form-group col-md-6">
                        <label class="small font-weight-bold">Instrucciones de recogida <span class="text-muted">(opcional)</span></label>
                        <textarea name="instrucciones_recogida" class="form-control form-control-sm" rows="2"
                            placeholder="Contacto en almacén, acceso, horario…">{{ old('instrucciones_recogida') }}</textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="small font-weight-bold">Instrucciones de entrega <span class="text-muted">(opcional)</span></label>
                        <textarea name="instrucciones_entrega" class="form-control form-control-sm" rows="2"
                            placeholder="Muelle, báscula, persona de recepción…">{{ old('instrucciones_entrega') }}</textarea>
                    </div>
                </div>
                    </div>{{-- panel body datos --}}
                </div>{{-- panel datos --}}
                </div>{{-- wizard step 1 --}}

                {{-- PASO 2: CARGA --}}
                <div class="wizard-step" data-wizard-step="2">
                <div class="env-wizard-panel">
                    <div class="env-wizard-panel__head"><i class="fas fa-box-open"></i> Carga y productos</div>
                    <div class="env-wizard-panel__body">
                        <p class="text-muted small mb-3 mb-md-2">
                            Indique qué recoger en cada almacén. Los cuadros de ayuda le muestran cuánto hay disponible
                            y cuánto puede pedir sin pasarse del stock.
                        </p>
                <div id="bloque-productos">
                        <div id="productos-recogida-container"></div>
                        @error('detalles')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                        @error('detalles.*.producto_ref')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                </div>
                    </div>
                </div>
                </div>{{-- wizard step 2 --}}

            </form>
            </div>{{-- /flujo-planta --}}

            <div id="flujo-mayorista" @if($destinoInicial !== 'mayorista') class="d-none" @endif>
                @include('pedidos.partials.form-traslado-mayorista', [
                    'codigoTrasladoPreview' => $codigoTrasladoPreview,
                    'almacenesMapaTraslado' => $almacenesMapaTraslado ?? [],
                    'hubLat' => $hubLat,
                    'hubLng' => $hubLng,
                ])
            </div>

            <div id="wizard-pasos-compartidos">
                @include('pedidos.partials.wizard-paso-asignacion')
                @include('pedidos.partials.wizard-paso-confirmacion')
            </div>

            <div id="flujo-punto-venta" @if($destinoInicial !== 'punto-venta') class="d-none" @endif>
                @if(in_array('punto-venta', $trayectosPermitidos ?? [], true))
                    @include('pedidos.partials.flujo-distribucion-pdv')
                @else
                    <div class="alert alert-warning mb-0">
                        Su rol no tiene permiso para crear envíos hacia puntos de venta.
                    </div>
                @endif
            </div>

            <div class="env-wizard-nav" id="env-wizard-nav">
                <div>
                    <button type="button" class="btn btn-outline-secondary" id="wizard-btn-anterior" disabled>
                        <i class="fas fa-arrow-left mr-1"></i> Anterior
                    </button>
                    <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-link text-muted ml-2" id="wizard-btn-cancelar">Cancelar</a>
                </div>
                <div>
                    <button type="button" class="btn btn-success px-4" id="wizard-btn-siguiente">
                        Siguiente <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                    @can('pedidos.create')
                    <button type="submit" class="btn btn-success px-4 d-none" id="wizard-btn-guardar" form="form-pedido">
                        <i class="fas fa-check mr-1"></i> Confirmar y crear envío
                    </button>
                    @endcan
                    @if(in_array('mayorista', $trayectosPermitidos ?? [], true))
                    <button type="submit" class="btn btn-success px-4 d-none" id="wizard-btn-guardar-mayorista" form="form-traslado-mayorista">
                        <i class="fas fa-check mr-1"></i> Confirmar traslado
                    </button>
                    @endif
                    @if(in_array('punto-venta', $trayectosPermitidos ?? [], true))
                    <button type="submit" class="btn btn-success px-4 d-none" id="wizard-btn-guardar-pdv" form="form-pedido-dist-pdv">
                        <i class="fas fa-check mr-1"></i> Confirmar envío
                    </button>
                    @endif
                </div>
            </div>
            </div>{{-- /env-wizard-unificado --}}
        </div>
    </div>
    @include('partials.modal-confirmar-accion')
@endsection

@push('scripts')
@include('partials.selector-catalogo-assets')
@include('pedidos.partials.fase2-envio-scripts')
@include('pedidos.partials.wizard-envio-scripts')
@include('logistica.partials.mapa-ruta-libs')
@include('pedidos.partials.pdv-envio-carga-scripts')
<script>
(function () {
    const hub = { lat: {{ $hubLat }}, lng: {{ $hubLng }} };
    const destinoInicial = @json($destinoInicial ?? 'planta');

    function activarDestino(destino, opts) {
        const esMayorista = destino === 'mayorista';
        const esPdv = destino === 'punto-venta';
        document.querySelectorAll('.js-trayecto-pick .env-destino-opcion').forEach((b) => {
            b.classList.toggle('is-active', b.dataset.destino === destino);
        });
        document.getElementById('flujo-planta')?.classList.toggle('d-none', esMayorista || esPdv);
        document.getElementById('flujo-mayorista')?.classList.toggle('d-none', !esMayorista);
        document.getElementById('flujo-punto-venta')?.classList.toggle('d-none', !esPdv);
        const tema = esMayorista ? 'mayorista' : (esPdv ? 'punto-venta' : 'planta');
        document.getElementById('env-wizard-unificado')?.setAttribute('data-tema', tema);
        window.EnvioWizard?.syncTrayectoDescripcion?.(destino);
        window.EnvioWizard?.syncFormularioCompartido?.(destino);
        window.PedidoFase2?.actualizarSugerenciaVehiculo?.();
        if (!opts?.keepPaso) {
            window.EnvioWizard?.irPaso?.(1);
        }
        if (esMayorista) {
            requestAnimationFrame(function () {
                ensureMapaTraslado();
            });
        } else if (esPdv) {
            requestAnimationFrame(function () {
                ensureMapaPdv();
            });
        }
    }

    document.querySelectorAll('.js-trayecto-pick .env-destino-opcion').forEach((btn) => {
        btn.addEventListener('click', () => activarDestino(btn.dataset.destino));
    });
    window.activarDestinoEnvio = activarDestino;
    activarDestino(destinoInicial);
    const MAX_RECOGIDAS_EXTRA = 5;
    const PRODUCTO_ENDPOINT = @json(route('catalogo-selector.productos-pedido'));
    const COSTO_ENVIO_URL = @json(route('pedidos.calcular-costo-envio'));
    const CSRF_TOKEN = @json(csrf_token());
    let pickerRecogidaActivo = null;
    let redrawToken = 0;
    let costoEditadoManual = false;
    let costoEnvioToken = 0;

    function aviso(mensaje, titulo, tono) {
        if (window.ModalConfirmar && typeof ModalConfirmar.aviso === 'function') {
            ModalConfirmar.aviso({ mensaje: mensaje, titulo: titulo || 'Aviso', tono: tono || 'warning' });
        } else {
            window.alert(mensaje);
        }
    }

    function etiquetaAlmacenPedido(almacen) {
        const nombre = (almacen.label || '').trim();
        const dir = (almacen.extra?.direccion || '').trim();
        if (!dir || /^GPS\s/i.test(dir)) {
            return nombre || dir;
        }
        if (nombre && !dir.toLowerCase().includes(nombre.toLowerCase())) {
            return nombre + ' · ' + dir;
        }
        return dir || nombre;
    }

    const ALMACENES_MAPA = @json($almacenesMapa ?? []);
    const ALMACENES_MAPA_TRASLADO = @json($almacenesMapaTraslado ?? []);
    const PUNTOS_MAPA_PDV = @json($puntosMapaPdv ?? []);
    const PDV_MAPA_ES_ADMIN = @json($esOrigenMayoristaPdv ?? $esAdminPdv ?? false);

    const state = {
        map: null,
        capasRuta: null,
        capasAlmacenes: null,
        routeLayer: null,
        markers: [],
        almacenesVisibles: false,
        cargandoAlmacenes: false,
    };

    function iconMarker(color, numero) {
        const html = numero
            ? '<div style="background:' + color + ';color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)">' + numero + '</div>'
            : '<i class="fas fa-map-marker-alt" style="color:' + color + ';font-size:32px;"></i>';
        return L.divIcon({
            html: html,
            className: 'custom-marker',
            iconSize: numero ? [28, 28] : [32, 32],
            iconAnchor: numero ? [14, 14] : [16, 32],
        });
    }

    function tieneDestino() {
        return !!document.getElementById('latitud').value && !!document.getElementById('longitud').value;
    }

    function recogida1Lista() {
        return !!document.getElementById('origen_latitud').value;
    }

    function hayRecogidasExtraVacias() {
        let vacia = false;
        document.querySelectorAll('.origen-extra-row').forEach(function (row) {
            if (!row.querySelector('[data-field="latitud"]').value) vacia = true;
        });
        return vacia;
    }

    function puedeElegirDestino() {
        return recogida1Lista() && !hayRecogidasExtraVacias();
    }

    function bloquearRecogidaSiDestino() {
        if (tieneDestino()) {
            aviso('Ya definió el destino. Use «Reiniciar ruta» para volver a editar las recogidas.');
            return true;
        }
        return false;
    }

    function syncEstadoRuta() {
        const destinoFijado = tieneDestino();
        const puedeDestino = puedeElegirDestino();

        document.getElementById('bloque-recogidas').classList.toggle('ruta-bloqueada', destinoFijado);
        document.getElementById('btnAgregarRecogida').disabled = destinoFijado || !recogida1Lista();

        const bloqueDestino = document.getElementById('bloque-destino');
        const msgDestino = document.getElementById('destino-bloqueado-msg');
        bloqueDestino.classList.toggle('ruta-bloqueada', !puedeDestino);
        if (destinoFijado) {
            msgDestino.textContent = 'Destino definido. Reinicie la ruta si necesita cambiar el orden de recogidas.';
        } else if (!recogida1Lista()) {
            msgDestino.textContent = 'Primero elija la recogida 1.';
        } else if (hayRecogidasExtraVacias()) {
            msgDestino.textContent = 'Complete o quite las recogidas adicionales antes de elegir destino.';
        } else {
            msgDestino.textContent = 'Ahora puede elegir el almacén de planta (destino final).';
        }
    }

    function renumerarRecogidas() {
        document.querySelectorAll('.origen-extra-row').forEach(function (row, i) {
            const num = i + 2;
            row.querySelector('.lbl-recogida-num').textContent = 'Recogida ' + num;
            row.querySelector('[data-field="latitud"]').setAttribute('name', 'recogidas[' + i + '][latitud]');
            row.querySelector('[data-field="longitud"]').setAttribute('name', 'recogidas[' + i + '][longitud]');
            row.querySelector('[data-field="direccion"]').setAttribute('name', 'recogidas[' + i + '][direccion]');
            const almacenField = row.querySelector('[data-field="almacenid"]');
            if (almacenField) almacenField.setAttribute('name', 'recogidas[' + i + '][almacenid]');
        });
    }

    function waypointsActuales() {
        const puntos = [];
        const oLat = parseFloat(document.getElementById('origen_latitud').value);
        const oLng = parseFloat(document.getElementById('origen_longitud').value);
        if (!isNaN(oLat) && !isNaN(oLng)) {
            puntos.push({ lat: oLat, lng: oLng, orden: 1, label: 'Recogida 1', tipo: 'recogida' });
        }
        document.querySelectorAll('.origen-extra-row').forEach(function (row, i) {
            const lat = parseFloat(row.querySelector('[data-field="latitud"]').value);
            const lng = parseFloat(row.querySelector('[data-field="longitud"]').value);
            const dir = row.querySelector('[data-field="direccion"]').value;
            if (!isNaN(lat) && !isNaN(lng)) {
                puntos.push({ lat: lat, lng: lng, orden: puntos.length + 1, label: dir || ('Recogida ' + (i + 2)), tipo: 'recogida' });
            }
        });
        const dLat = parseFloat(document.getElementById('latitud').value);
        const dLng = parseFloat(document.getElementById('longitud').value);
        if (!isNaN(dLat) && !isNaN(dLng)) {
            puntos.push({ lat: dLat, lng: dLng, orden: puntos.length + 1, label: document.getElementById('direccion_texto').value || 'Planta', tipo: 'destino' });
        }
        return puntos;
    }

    function deduplicarWaypoints(puntos) {
        const out = [];
        puntos.forEach(function (p) {
            const idx = out.findIndex(function (x) {
                return Math.abs(x.lat - p.lat) < 0.000001 && Math.abs(x.lng - p.lng) < 0.000001;
            });
            if (idx === -1) {
                out.push(p);
            } else if (p.tipo === 'destino') {
                out[idx] = p;
            }
        });
        return out;
    }

    function waypointsParaTrazado(puntos) {
        const dedupe = deduplicarWaypoints(puntos);
        return dedupe.length >= 2 ? dedupe : puntos;
    }

    function destinoYaAsignado(lat, lng) {
        const dLat = parseFloat(document.getElementById('latitud').value);
        const dLng = parseFloat(document.getElementById('longitud').value);
        if (isNaN(dLat) || isNaN(dLng)) return false;
        return Math.abs(dLat - lat) < 0.00001 && Math.abs(dLng - lng) < 0.00001;
    }

    function coordsSeleccionadas() {
        const coords = [];
        const oLat = parseFloat(document.getElementById('origen_latitud').value);
        const oLng = parseFloat(document.getElementById('origen_longitud').value);
        if (!isNaN(oLat) && !isNaN(oLng)) coords.push({ lat: oLat, lng: oLng });
        document.querySelectorAll('.origen-extra-row').forEach(function (row) {
            const lat = parseFloat(row.querySelector('[data-field="latitud"]').value);
            const lng = parseFloat(row.querySelector('[data-field="longitud"]').value);
            if (!isNaN(lat) && !isNaN(lng)) coords.push({ lat: lat, lng: lng });
        });
        const dLat = parseFloat(document.getElementById('latitud').value);
        const dLng = parseFloat(document.getElementById('longitud').value);
        if (!isNaN(dLat) && !isNaN(dLng)) coords.push({ lat: dLat, lng: dLng });
        return coords;
    }

    function coordYaSeleccionada(lat, lng) {
        return coordsSeleccionadas().some(function (c) {
            return Math.abs(c.lat - lat) < 0.00001 && Math.abs(c.lng - lng) < 0.00001;
        });
    }

    function flashMapaMsg(texto) {
        const el = document.getElementById('mapa-asignacion-flash');
        if (!el) return;
        el.textContent = texto;
        el.style.display = 'block';
        clearTimeout(flashMapaMsg._t);
        flashMapaMsg._t = setTimeout(function () { el.style.display = 'none'; }, 2200);
    }

    function iconAlmacen(ambito, seleccionado) {
        const esPlanta = ambito === 'planta';
        const color = esPlanta ? '#dc3545' : '#28a745';
        const icon = esPlanta ? 'fa-industry' : 'fa-warehouse';
        const sel = seleccionado ? ' is-selected' : '';
        return L.divIcon({
            html: '<div class="almacen-mapa-pin' + sel + '" style="background:' + color + '"><i class="fas ' + icon + '"></i></div>',
            className: 'almacen-mapa-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    function pintarAlmacenesEnMapa(items) {
        state.capasAlmacenes.clearLayers();
        const bounds = [];
        items.forEach(function (item) {
            const lat = parseFloat(item.extra.lat);
            const lng = parseFloat(item.extra.lng);
            const ambito = item.extra.ambito || 'agricola';
            const seleccionado = coordYaSeleccionada(lat, lng);
            const m = L.marker([lat, lng], {
                icon: iconAlmacen(ambito, seleccionado),
                zIndexOffset: seleccionado ? 200 : 0,
            })
                .bindTooltip(item.label, {
                    className: 'almacen-mapa-tooltip',
                    direction: 'top',
                    offset: [0, -14],
                })
                .addTo(state.capasAlmacenes);
            m.on('mouseover', function () { this.openTooltip(); });
            m.on('click', function () { asignarAlmacenDesdeMapa(item); });
            m._almacenItem = item;
            bounds.push([lat, lng]);
        });
        return bounds;
    }

    function resaltarAlmacenesEnMapa() {
        if (!state.almacenesVisibles || !state.capasAlmacenes) return;
        state.capasAlmacenes.eachLayer(function (layer) {
            if (!layer._almacenItem) return;
            const item = layer._almacenItem;
            const lat = parseFloat(item.extra.lat);
            const lng = parseFloat(item.extra.lng);
            const ambito = item.extra.ambito || 'agricola';
            const sel = coordYaSeleccionada(lat, lng);
            layer.setIcon(iconAlmacen(ambito, sel));
            layer.setZIndexOffset(sel ? 200 : 0);
        });
    }

    function asignarAlmacenDesdeMapa(item) {
        const ambito = item.extra?.ambito || 'agricola';
        if (ambito === 'planta') {
            if (!puedeElegirDestino()) {
                aviso('Complete todas las recogidas antes de asignar el almacén de planta.');
                return;
            }
            const latPlanta = parseFloat(item.extra?.lat);
            const lngPlanta = parseFloat(item.extra?.lng);
            if (!isNaN(latPlanta) && !isNaN(lngPlanta) && destinoYaAsignado(latPlanta, lngPlanta)) {
                flashMapaMsg('Este almacén ya está seleccionado como destino.');
                return;
            }
            aplicarDestino(item);
            flashMapaMsg('Destino asignado: ' + item.label);
            resaltarAlmacenesEnMapa();
            return;
        }
        if (bloquearRecogidaSiDestino()) return;

        if (!recogida1Lista()) {
            aplicarRecogidaPrincipal(item);
            flashMapaMsg('Recogida 1: ' + item.label);
            resaltarAlmacenesEnMapa();
            return;
        }

        let row = Array.prototype.find.call(
            document.querySelectorAll('.origen-extra-row'),
            function (r) { return !r.querySelector('[data-field="latitud"]').value; }
        );
        if (!row && document.querySelectorAll('.origen-extra-row').length < MAX_RECOGIDAS_EXTRA) {
            row = crearFilaRecogidaExtra();
            document.getElementById('recogidas-extra-container').appendChild(row);
            renumerarRecogidas();
            syncEstadoRuta();
        }
        if (row) {
            aplicarRecogidaExtra(row, item);
            const num = row.querySelector('.lbl-recogida-num')?.textContent || 'Recogida';
            flashMapaMsg(num + ': ' + item.label);
            resaltarAlmacenesEnMapa();
        } else {
            aviso('Máximo de recogidas alcanzado. Quite una o reinicie la ruta.');
        }
    }

    function ajustarVistaMapa(bounds) {
        if (!bounds.length || !state.map) return;
        const rutaPts = waypointsActuales();
        const puntos = bounds.slice();
        rutaPts.forEach(function (p) { puntos.push([p.lat, p.lng]); });
        try {
            state.map.fitBounds(L.latLngBounds(puntos).pad(0.1));
        } catch (e) {
            state.map.setView([puntos[0][0], puntos[0][1]], 12);
        }
    }

    function mostrarAlmacenesEnMapa() {
        const items = ALMACENES_MAPA || [];
        if (!items.length) {
            aviso('No hay almacenes con ubicación para mostrar en el mapa.');
            return false;
        }

        const bounds = pintarAlmacenesEnMapa(items);
        if (!state.map.hasLayer(state.capasAlmacenes)) {
            state.map.addLayer(state.capasAlmacenes);
        }
        state.almacenesVisibles = true;
        ajustarVistaMapa(bounds);
        return true;
    }

    function toggleAlmacenesEnMapa() {
        const btn = document.getElementById('btnVerAlmacenesMapa');
        if (state.cargandoAlmacenes) return;

        if (state.almacenesVisibles) {
            if (state.map.hasLayer(state.capasAlmacenes)) {
                state.map.removeLayer(state.capasAlmacenes);
            }
            state.almacenesVisibles = false;
            btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
            btn.classList.remove('active');
            return;
        }

        state.cargandoAlmacenes = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando almacenes…';

        window.requestAnimationFrame(function () {
            try {
                if (mostrarAlmacenesEnMapa()) {
                    btn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i> Ocultar almacenes';
                    btn.classList.add('active');
                }
            } catch (e) {
                console.error(e);
                if (state.map.hasLayer(state.capasAlmacenes)) {
                    state.map.removeLayer(state.capasAlmacenes);
                }
                state.almacenesVisibles = false;
                aviso('No se pudieron mostrar los almacenes en el mapa. Intente de nuevo.');
                btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
                btn.classList.remove('active');
            } finally {
                state.cargandoAlmacenes = false;
                btn.disabled = false;
            }
        });
    }

    function limpiarCapaMapa() {
        if (!state.capasRuta) return;
        if (state.routeLayer) {
            state.capasRuta.removeLayer(state.routeLayer);
            state.routeLayer = null;
        }
        state.markers.forEach(function (m) { state.capasRuta.removeLayer(m); });
        state.markers = [];
    }

    function rebuildMarcadores(puntos) {
        limpiarCapaMapa();
        if (!state.capasRuta) return;
        deduplicarWaypoints(puntos).forEach(function (p, i) {
            const esDestino = p.tipo === 'destino';
            const color = esDestino ? '#dc3545' : (i === 0 ? '#28a745' : '#16a34a');
            const marker = L.marker([p.lat, p.lng], { icon: iconMarker(color, i + 1), zIndexOffset: 1000 }).addTo(state.capasRuta);
            marker.bindTooltip(p.label, { className: 'almacen-mapa-tooltip', direction: 'top', offset: [0, -14] });
            state.markers.push(marker);
        });
        resaltarAlmacenesEnMapa();
    }

    async function redrawRoute() {
        if (!window.RutaPorCalles || !state.capasRuta) return;
        const token = ++redrawToken;
        const puntos = waypointsActuales();
        rebuildMarcadores(puntos);

        if (puntos.length < 2) {
            document.getElementById('rutaResumen').textContent = puntos.length === 1
                ? 'Agregue más recogidas o el destino para trazar la ruta.'
                : '';
            if (window.PedidoFase2) window.PedidoFase2.setRouteDuration(null);
            syncEstadoRuta();
            actualizarCostoEnvio(null);
            return;
        }

        const paraRuta = waypointsParaTrazado(puntos);
        let routeResult = null;
        routeResult = await RutaPorCalles.fetchRoute(paraRuta);
        if (token !== redrawToken) return;

        if (routeResult?.geojson) {
            state.routeLayer = L.geoJSON(routeResult.geojson, {
                style: {
                    color: routeResult.straight ? '#e67e22' : '#2563eb',
                    weight: 5,
                    opacity: 0.85,
                    dashArray: routeResult.straight ? '8,8' : null,
                },
            }).addTo(state.capasRuta);

            if (!state.almacenesVisibles) {
                const bounds = L.latLngBounds(puntos.map(function (p) { return [p.lat, p.lng]; }));
                try { state.map.fitBounds(bounds.pad(0.15)); } catch (e) {}
            }

            const km = routeResult.distance_m ? Math.round(routeResult.distance_m / 1000) : null;
            const min = routeResult.duration_s ? Math.round(routeResult.duration_s / 60) : null;
            let resumen = window.PedidoFase2?.formatRutaResumen
                ? window.PedidoFase2.formatRutaResumen({
                    km: km || 0,
                    min: min || 0,
                    paradas: puntos.length,
                    straight: routeResult.straight,
                })
                : ((puntos.length > 2 ? puntos.length + ' paradas · ' : '')
                    + (routeResult.straight ? 'Distancia aproximada' : 'Camino estimado')
                    + (km && min ? ' · ~' + km + ' km · ~' + min + ' min' : ''));
            document.getElementById('rutaResumen').textContent = resumen;
            if (window.PedidoFase2 && routeResult.duration_s) {
                window.PedidoFase2.setRouteDuration(routeResult.duration_s);
            }
        } else if (window.PedidoFase2) {
            window.PedidoFase2.setRouteDuration(null);
        }
        syncEstadoRuta();
        actualizarCostoEnvio(routeResult?.distance_m ?? null);
    }

    function resetCostoEnvio() {
        costoEditadoManual = false;
        const input = document.getElementById('costo_bs');
        if (input) {
            input.value = '0';
        }
        const detalle = document.getElementById('wizard-costo-detalle');
        if (detalle) {
            detalle.textContent = 'Fórmula: Bs 10 base + Bs 2,40/km + Bs 5 por parada extra (mín. Bs 15). Recargo +25% si hay lluvia. Puede editarlo.';
        }
    }

    async function actualizarCostoEnvio(distanciaMetros) {
        if (costoEditadoManual) {
            return;
        }

        const puntos = waypointsActuales();
        const token = ++costoEnvioToken;

        if (puntos.length < 2) {
            resetCostoEnvio();
            return;
        }

        const detalle = document.getElementById('wizard-costo-detalle');
        if (detalle) {
            detalle.textContent = 'Calculando costo de envío…';
        }

        try {
            const resp = await fetch(COSTO_ENVIO_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    paradas: puntos.map(function (p) { return { lat: p.lat, lng: p.lng }; }),
                    distancia_m: distanciaMetros,
                }),
            });
            if (token !== costoEnvioToken) return;
            if (!resp.ok) {
                throw new Error('No se pudo calcular el costo');
            }
            const data = await resp.json();
            const input = document.getElementById('costo_bs');
            if (input && !costoEditadoManual) {
                input.value = String(Math.round(Number(data.costo_bs || 0)));
            }
            if (detalle) {
                let texto = data.detalle || 'Costo estimado según la ruta.';
                if (data.lluvia && data.descripcion_clima) {
                    texto += ' Clima: ' + data.descripcion_clima + '.';
                }
                detalle.textContent = texto;
            }
        } catch (e) {
            if (detalle) {
                detalle.textContent = 'No se pudo calcular automáticamente. Ingrese el costo manualmente.';
            }
        }
    }

    function selectorIdForRecogida(key) {
        return 'pedido_producto_' + key;
    }

    function recogidaUidExtra(row) {
        if (!row.getAttribute('data-recogida-uid')) {
            row.setAttribute('data-recogida-uid', 'rec-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8));
        }
        return row.getAttribute('data-recogida-uid');
    }

    function recogidaKeyFromContext(context) {
        return context === 'principal' ? 'principal' : recogidaUidExtra(context);
    }

    function recogidasConAlmacen() {
        const list = [];
        if (recogida1Lista()) {
            list.push({
                key: 'principal',
                num: 1,
                almacenId: document.getElementById('origen_almacenid').value,
                almacenLabel: document.getElementById('txtNombreOrigen').value || document.getElementById('origen_direccion').value || 'Recogida 1',
            });
        }
        document.querySelectorAll('.origen-extra-row').forEach(function (row, i) {
            if (!row.querySelector('[data-field="latitud"]').value) return;
            list.push({
                key: recogidaUidExtra(row),
                num: i + 2,
                almacenId: row.querySelector('[data-field="almacenid"]').value,
                almacenLabel: row.querySelector('.txt-recogida-extra').value || ('Recogida ' + (i + 2)),
            });
        });
        return list;
    }

    function renumerarDetallesInputs() {
        document.querySelectorAll('.producto-recogida-row').forEach(function (fila, idx) {
            const hidden = fila.querySelector('.selector-catalogo-value');
            const cant = fila.querySelector('[data-field="cantidad"]');
            const obs = fila.querySelector('[data-field="observaciones"]');
            if (hidden) hidden.setAttribute('name', 'detalles[' + idx + '][producto_ref]');
            if (cant) cant.setAttribute('name', 'detalles[' + idx + '][cantidad]');
            if (obs) obs.setAttribute('name', 'detalles[' + idx + '][observaciones]');
        });
    }

    function actualizarEtiquetaFilaProducto(fila, rec) {
        const lbl = fila.querySelector('.lbl-producto-recogida');
        if (lbl) {
            lbl.innerHTML = '<i class="fas fa-seedling mr-1 text-success"></i> Recogida ' + rec.num + ' — ' + (rec.almacenLabel || 'Almacén');
        }
        fila.setAttribute('data-selector-id', selectorIdForRecogida(rec.key));
    }

    function formatearStock(valor) {
        const n = Number(valor);
        if (!Number.isFinite(n)) return '—';
        return n.toLocaleString('es-BO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function actualizarStockFilaProducto(fila, extra) {
        extra = extra || {};
        const lblStock = fila.querySelector('.lbl-stock-disponible');
        const lblCantidad = fila.querySelector('.lbl-cantidad-titulo');
        const cantInput = fila.querySelector('[data-field="cantidad"]');
        if (!lblStock || !cantInput) return;

        const stockKg = extra.stock_kg != null ? Number(extra.stock_kg) : Number(extra.stock);
        const unidad = 'kg';
        if (lblCantidad) lblCantidad.textContent = 'Cantidad (kg)';

        if (extra.calibre_id) {
            fila.dataset.calibreSugeridoId = String(extra.calibre_id);
        } else {
            delete fila.dataset.calibreSugeridoId;
        }

        if (Number.isFinite(stockKg) && stockKg > 0) {
            lblStock.textContent = 'Stock: ' + formatearStock(stockKg) + ' kg';
            lblStock.classList.remove('text-muted');
            lblStock.classList.add('text-success');
            cantInput.max = String(stockKg);
            cantInput.setAttribute('title', 'Máximo disponible: ' + formatearStock(stockKg) + ' kg');
            validarCantidadContraStock(fila, cantInput, stockKg, unidad);
            fila.dataset.stockKg = String(stockKg);
            fila.dataset.stockUnidad = unidad;
        } else if (extra.stock != null && extra.stock !== '' && Number.isFinite(Number(extra.stock))) {
            const stockNum = Number(extra.stock);
            lblStock.textContent = 'Stock: ' + formatearStock(stockNum) + ' kg';
            lblStock.classList.remove('text-muted');
            lblStock.classList.add('text-success');
            cantInput.max = String(stockNum);
            cantInput.setAttribute('title', 'Máximo disponible: ' + formatearStock(stockNum) + ' kg');
            validarCantidadContraStock(fila, cantInput, stockNum, unidad);
            fila.dataset.stockKg = String(stockNum);
            fila.dataset.stockUnidad = unidad;
        } else {
            lblStock.textContent = 'Stock: no registrado';
            lblStock.classList.remove('text-success');
            lblStock.classList.add('text-muted');
            fila.dataset.stockKg = '';
            cantInput.removeAttribute('max');
            cantInput.removeAttribute('title');
            cantInput.classList.remove('is-invalid');
            fila.querySelector('.stock-excedido-aviso')?.remove();
        }
        if (window.PedidoFase2) window.PedidoFase2.refrescarFilaEmpaque(fila);
    }

    function validarCantidadContraStock(fila, cantInput, stockNum, unidad) {
        if (!cantInput || !Number.isFinite(stockNum)) return;
        let avisoEl = fila.querySelector('.stock-excedido-aviso');
        const val = cantInput.value ? parseFloat(cantInput.value) : NaN;
        const excede = !isNaN(val) && val > stockNum;
        if (excede) {
            if (!avisoEl) {
                avisoEl = document.createElement('small');
                avisoEl.className = 'stock-excedido-aviso text-danger d-block mt-1';
                cantInput.closest('.col-md-3')?.appendChild(avisoEl);
            }
            avisoEl.textContent = 'Supera el stock disponible (' + formatearStock(stockNum) + ' ' + unidad + ').';
            cantInput.classList.add('is-invalid');
        } else {
            if (avisoEl) avisoEl.remove();
            cantInput.classList.remove('is-invalid');
        }
    }

    function vincularStockProducto(fila) {
        const wrap = fila.querySelector('.selector-catalogo-wrapper');
        if (!wrap || wrap.dataset.stockVinculado === '1') return;
        wrap.dataset.stockVinculado = '1';
        wrap.addEventListener('selector-catalogo:change', function (e) {
            actualizarStockFilaProducto(fila, e.detail?.extra || {});
        });
        const cantInput = fila.querySelector('[data-field="cantidad"]');
        if (cantInput && cantInput.dataset.stockListener !== '1') {
            cantInput.dataset.stockListener = '1';
            cantInput.addEventListener('input', function () {
                const max = cantInput.max ? parseFloat(cantInput.max) : NaN;
                if (!isNaN(max)) {
                    const unidad = fila.querySelector('.lbl-cantidad-titulo')?.textContent?.replace(/^Cantidad\s*\(/, '').replace(/\)$/, '') || 'kg';
                    validarCantidadContraStock(fila, cantInput, max, unidad);
                }
                if (window.PedidoFase2) window.PedidoFase2.validarCapacidadVehiculo();
                if (window.PedidoFase2) window.PedidoFase2.actualizarSugerenciaKg(fila);
            });
        }
    }

    function registrarSelectorProducto(selId, rec) {
        if (!window.CatalogoSelector) return;
        const params = rec.almacenId ? { almacenid: String(rec.almacenId) } : {};
        CatalogoSelector.register(selId, {
            endpoint: PRODUCTO_ENDPOINT,
            title: 'Productos en ' + (rec.almacenLabel || 'almacén'),
            searchPlaceholder: 'Cultivo, lote, insumo…',
            params: params,
            rowIcon: 'fa-box',
            theme: 'planta',
            onSelect: function (item) {
                const fila = document.querySelector('[data-selector-id="' + selId + '"]');
                if (fila) {
                    actualizarStockFilaProducto(fila, item.extra || {});
                    const insumoCalibre = item.extra?.insumoid_calibre;
                    if (insumoCalibre) {
                        fila.dataset.insumoCalibreId = String(insumoCalibre);
                    } else if (String(item.id || '').startsWith('insumo:')) {
                        fila.dataset.insumoCalibreId = String(item.id).split(':')[1];
                    } else {
                        delete fila.dataset.insumoCalibreId;
                    }
                    fila.dispatchEvent(new CustomEvent('producto-seleccionado', { detail: item }));
                }
            },
        });
    }

    function crearFilaProducto(rec, detalleIdx) {
        const selId = selectorIdForRecogida(rec.key);
        const fila = document.createElement('div');
        fila.className = 'producto-recogida-row env-producto-card border rounded p-3 mb-2 bg-white';
        fila.setAttribute('data-recogida-key', rec.key);
        fila.innerHTML =
            '<label class="small font-weight-bold lbl-producto-recogida d-block mb-2"></label>' +
            '<div class="form-row align-items-end">' +
                '<div class="col-md-5">' +
                    '<div class="selector-catalogo-wrapper flex-grow-1 w-100 mb-0 selector-catalogo--filtros" id="selector_wrap_' + selId + '">' +
                        '<div class="selector-filtros-field">' +
                            '<input type="text" class="selector-filtros-field__input selector-catalogo-label is-empty" readonly placeholder="Elegir producto…">' +
                            '<input type="hidden" name="detalles[' + detalleIdx + '][producto_ref]" class="selector-catalogo-value" required>' +
                            '<div class="selector-filtros-field__actions">' +
                                '<button type="button" class="selector-filtros-field__open" data-selector-open="' + selId + '" title="Abrir catálogo">' +
                                    '<i class="fas fa-chevron-down"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-3 js-modo-kg">' +
                    '<div class="d-flex justify-content-between align-items-baseline mb-1">' +
                        '<label class="small text-muted mb-0 lbl-cantidad-titulo">Cantidad (kg)</label>' +
                        '<small class="lbl-stock-disponible text-muted font-weight-bold">Stock: —</small>' +
                    '</div>' +
                    '<input type="number" step="0.01" min="0.01" name="detalles[' + detalleIdx + '][cantidad]" data-field="cantidad" class="form-control form-control-sm" placeholder="Ej: 350" required>' +
                '</div>' +
                '<div class="col-md-4">' +
                    '<label class="small text-muted mb-1">Observaciones del ítem</label>' +
                    '<input type="text" name="detalles[' + detalleIdx + '][observaciones]" data-field="observaciones" class="form-control form-control-sm" placeholder="Opcional">' +
                '</div>' +
            '</div>' +
            '<div class="js-bloque-empaque pt-2 mt-2">' +
                '<div class="form-row env-carga-campos">' +
                    '<div class="col-md-3 mb-2 mb-md-0 env-carga-campo">' +
                        '<label class="env-carga-campo__lbl">Forma de pedido</label>' +
                        '<select class="form-control form-control-sm js-forma-pedido">' +
                            '<option value="kg" selected>Por peso (kg)</option>' +
                            '<option value="empaques">Por empaques</option>' +
                            '<option value="unidades">Por unidades</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="col-md-3 mb-2 mb-md-0 js-campo-tipo-empaque d-none env-carga-campo">' +
                        '<label class="env-carga-campo__lbl">Tipo empaque</label>' +
                        '<select class="form-control form-control-sm js-tipo-empaque"><option value="">Seleccione…</option></select>' +
                        '<small class="env-empaque-hint js-hint-tipo-empaque text-muted d-none"></small>' +
                    '</div>' +
                    '<div class="col-md-3 mb-2 mb-md-0 js-campo-calibre d-none env-carga-campo">' +
                        '<label class="env-carga-campo__lbl">Calibre / conteo</label>' +
                        '<select class="form-control form-control-sm js-calibre"><option value="">Seleccione calibre…</option></select>' +
                    '</div>' +
                    '<div class="col-md-3 js-campo-cant-alt d-none env-carga-campo">' +
                        '<label class="env-carga-campo__lbl js-lbl-cantidad-pedido">¿Cuántos empaques?</label>' +
                        '<input type="number" step="1" min="1" class="form-control form-control-sm js-cantidad-pedido" placeholder="Ej: 100">' +
                    '</div>' +
                '</div>' +
                '<div class="env-carga-ayuda js-sugerencia-kg d-none mt-2"></div>' +
                '<div class="env-carga-ayuda env-carga-ayuda--empaque js-sugerencia-empaque d-none mt-2"></div>' +
                '<div class="env-carga-resumen js-resumen-carga d-none mt-2"></div>' +
                '<div class="env-carga-error js-aviso-limite d-none mt-2"></div>' +
            '</div>';
        actualizarEtiquetaFilaProducto(fila, rec);
        fila.setAttribute('data-selector-id', selId);
        registrarSelectorProducto(selId, rec);
        vincularStockProducto(fila);
        if (window.PedidoFase2) window.PedidoFase2.initFilaEmpaque(fila);
        return fila;
    }

    function syncFilasProducto() {
        const container = document.getElementById('productos-recogida-container');
        const bloque = document.getElementById('bloque-productos');
        if (!container || !bloque) return;

        const recogidas = recogidasConAlmacen();
        if (recogidas.length === 0) {
            container.innerHTML = '<p class="text-muted small mb-0 env-carga-compact"><i class="fas fa-info-circle mr-1"></i> Complete el paso <strong>Ruta</strong> para ver los productos de cada almacén.</p>';
            return;
        }
        if (container.querySelector('.env-carga-compact')) {
            container.innerHTML = '';
        }

        const keysActivas = new Set();
        recogidas.forEach(function (rec, idx) {
            keysActivas.add(rec.key);
            let fila = container.querySelector('[data-recogida-key="' + rec.key + '"]');
            if (!fila) {
                fila = crearFilaProducto(rec, idx);
                container.appendChild(fila);
            } else {
                actualizarEtiquetaFilaProducto(fila, rec);
                const selId = selectorIdForRecogida(rec.key);
                const cfg = CatalogoSelector?.instances?.[selId];
                if (cfg) {
                    cfg.title = 'Productos en ' + (rec.almacenLabel || 'almacén');
                    cfg.params = rec.almacenId ? { almacenid: String(rec.almacenId) } : {};
                }
            }
        });

        container.querySelectorAll('.producto-recogida-row').forEach(function (fila) {
            const key = fila.getAttribute('data-recogida-key');
            if (!keysActivas.has(key)) {
                const selId = selectorIdForRecogida(key);
                if (CatalogoSelector?.instances?.[selId]) {
                    delete CatalogoSelector.instances[selId];
                }
                fila.remove();
            }
        });

        renumerarDetallesInputs();
    }
    window.syncFilasProductoPlanta = syncFilasProducto;

    function actualizarBtnVerOrigen() {
        const btn = document.getElementById('btnVerProductosOrigen');
        const almacenId = document.getElementById('origen_almacenid')?.value;
        if (btn) btn.classList.toggle('d-none', !almacenId);
    }

    function abrirProductosDelAlmacen(almacen, context) {
        if (!almacen?.id || !window.CatalogoSelector) return;

        const ejecutar = function () {
            if (context === 'principal') {
                if (bloquearRecogidaSiDestino()) return;
                aplicarRecogidaPrincipal(almacen, true);
            } else if (context && typeof context.querySelector === 'function') {
                aplicarRecogidaExtra(context, almacen, true);
            }
            syncFilasProducto();

            const key = recogidaKeyFromContext(context);
            const selId = selectorIdForRecogida(key);
            const cfg = CatalogoSelector.instances[selId];
            if (cfg) {
                cfg.params = { almacenid: String(almacen.id) };
                cfg.title = 'Productos en ' + (almacen.label || almacen.extra?.direccion || 'almacén');
            }
            window.setTimeout(function () {
                CatalogoSelector.open(selId);
            }, 150);
        };

        const $m = window.jQuery('#modalSelectorCatalogo');
        if ($m.hasClass('show')) {
            $m.one('hidden.bs.modal', ejecutar);
            $m.modal('hide');
        } else {
            ejecutar();
        }
    }

    const VER_INVENTARIO_SEL_ID = 'envio_ver_inventario_almacen';

    function abrirInventarioAlmacenEnvio(almacen, opts) {
        if (!almacen?.id || !window.CatalogoSelector) return;
        const options = opts || {};

        const ejecutar = function () {
            if (typeof options.onApply === 'function') {
                options.onApply(almacen);
            }

            CatalogoSelector.register(VER_INVENTARIO_SEL_ID, {
                endpoint: options.endpoint || @json(route('catalogo-selector.insumos')),
                title: options.title || ('Inventario — ' + (almacen.label || 'almacén')),
                searchPlaceholder: options.searchPlaceholder || 'Buscar producto…',
                params: Object.assign({}, options.params || {}, { almacenid: String(almacen.id) }),
                rowIcon: options.rowIcon || 'fa-box',
                theme: options.theme || 'planta',
                colNombre: 'Producto',
                colDetalle: 'Detalle',
                onSelect: function (item) {
                    if (typeof options.onProductSelect === 'function') {
                        options.onProductSelect(item);
                    }
                },
            });

            window.setTimeout(function () {
                CatalogoSelector.open(VER_INVENTARIO_SEL_ID);
            }, 150);
        };

        const $m = window.jQuery('#modalSelectorCatalogo');
        if ($m.hasClass('show')) {
            $m.one('hidden.bs.modal', ejecutar);
            $m.modal('hide');
        } else {
            ejecutar();
        }
    }

    function inventarioOrigenTrasladoConfig() {
        return {
            onApply: function (item) { aplicarOrigenTraslado(item, { abrirInventario: false }); },
            title: 'Productos terminados en planta',
            searchPlaceholder: 'Buscar producto terminado…',
            params: {
                ambito_planta: '1',
                solo_con_stock: '1',
                solo_producto_terminado: '1',
            },
            theme: 'planta',
            onProductSelect: agregarProductoPreseleccionadoTraslado,
        };
    }

    function inventarioOrigenPdvConfig() {
        return {
            onApply: function (item) { aplicarOrigenPdv(item, { abrirInventario: false }); },
            title: 'Productos en almacén mayorista',
            searchPlaceholder: 'Buscar producto…',
            params: {
                ambito_mayorista: '1',
                solo_con_stock: '1',
            },
            theme: 'planta',
            onProductSelect: aplicarProductoPreseleccionadoPdv,
        };
    }

    function abrirInventarioOrigenTraslado(almacen) {
        if (!almacen?.id) return;
        abrirInventarioAlmacenEnvio(almacen, inventarioOrigenTrasladoConfig());
    }

    function abrirInventarioOrigenPdv(almacen) {
        if (!almacen?.id) return;
        abrirInventarioAlmacenEnvio(almacen, inventarioOrigenPdvConfig());
    }

    function abrirInventarioOrigenAgricola(almacen, context) {
        if (!almacen?.id) return;
        window.setTimeout(function () {
            abrirProductosDelAlmacen(almacen, context);
        }, 200);
    }

    function registrarVerInventarioEnSelector(selId, cfg) {
        const base = CatalogoSelector.instances[selId];
        if (!base || !window.CatalogoSelector) return;

        CatalogoSelector.register(selId, Object.assign({}, base, {
            rowAction: { label: 'Ver' },
            onRowAction: function (item) {
                if (typeof cfg.beforeOpen === 'function' && cfg.beforeOpen(item)) return;
                abrirInventarioAlmacenEnvio(item, cfg.inventario);
            },
            onSelect: cfg.onSelect || base.onSelect,
        }));
    }

    function aplicarRecogidaPrincipal(almacen, desdeVer) {
        document.getElementById('origen_almacenid').value = almacen.id || '';
        actualizarBtnVerOrigen();

        const lat = parseFloat(almacen.extra?.lat);
        const lng = parseFloat(almacen.extra?.lng);
        const label = etiquetaAlmacenPedido(almacen);
        if (isNaN(lat) || isNaN(lng)) {
            if (!desdeVer) syncFilasProducto();
            return;
        }

        document.getElementById('origen_latitud').value = lat.toFixed(7);
        document.getElementById('origen_longitud').value = lng.toFixed(7);
        document.getElementById('origen_direccion').value = label;
        const display = document.getElementById('txtNombreOrigen');
        display.value = label;
        display.classList.remove('text-muted');
        document.getElementById('txtOrigenCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        if (!desdeVer) syncFilasProducto();
        redrawRoute();
        if (!desdeVer && almacen.id) {
            abrirInventarioOrigenAgricola(almacen, 'principal');
        }
    }

    function aplicarRecogidaExtra(row, almacen, desdeVer) {
        const lat = parseFloat(almacen.extra?.lat);
        const lng = parseFloat(almacen.extra?.lng);
        const label = etiquetaAlmacenPedido(almacen);
        if (isNaN(lat) || isNaN(lng)) {
            if (!desdeVer) syncFilasProducto();
            return;
        }

        row.querySelector('[data-field="latitud"]').value = lat.toFixed(7);
        row.querySelector('[data-field="longitud"]').value = lng.toFixed(7);
        row.querySelector('[data-field="direccion"]').value = label;
        const almacenField = row.querySelector('[data-field="almacenid"]');
        if (almacenField) almacenField.value = almacen.id || '';
        const txt = row.querySelector('.txt-recogida-extra');
        txt.value = label;
        txt.classList.remove('text-muted');
        if (!desdeVer) syncFilasProducto();
        redrawRoute();
        if (!desdeVer && almacen.id) {
            abrirInventarioOrigenAgricola(almacen, row);
        }
    }

    function aplicarDestino(almacen) {
        if (!puedeElegirDestino()) {
            aviso('Complete todas las recogidas en orden antes de elegir el destino.');
            return;
        }
        const lat = parseFloat(almacen.extra?.lat);
        const lng = parseFloat(almacen.extra?.lng);
        const label = etiquetaAlmacenPedido(almacen);
        if (isNaN(lat) || isNaN(lng)) return;
        if (destinoYaAsignado(lat, lng)) {
            flashMapaMsg('Este almacén ya está seleccionado como destino.');
            return;
        }

        document.getElementById('latitud').value = lat.toFixed(7);
        document.getElementById('longitud').value = lng.toFixed(7);
        document.getElementById('direccion_texto').value = label;
        const display = document.getElementById('txtNombreDestino');
        display.value = label;
        display.classList.remove('text-muted');
        document.getElementById('txtDestinoCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        redrawRoute();
    }

    function limpiarOrigen() {
        ['origen_latitud', 'origen_longitud', 'origen_direccion', 'origen_almacenid'].forEach(function (id) {
            document.getElementById(id).value = '';
        });
        actualizarBtnVerOrigen();
        document.getElementById('txtNombreOrigen').value = '';
        document.getElementById('txtNombreOrigen').classList.add('text-muted');
        document.getElementById('txtOrigenCoords').textContent = '';
        syncFilasProducto();
        redrawRoute();
    }

    function limpiarDestino() {
        ['latitud', 'longitud', 'direccion_texto'].forEach(function (id) {
            document.getElementById(id).value = '';
        });
        document.getElementById('txtNombreDestino').value = '';
        document.getElementById('txtNombreDestino').classList.add('text-muted');
        document.getElementById('txtDestinoCoords').textContent = '';
        redrawRoute();
    }

    function crearFilaRecogidaExtra(datos) {
        datos = datos || {};
        const row = document.createElement('div');
        row.className = 'origen-extra-row';
        row.setAttribute('data-recogida-uid', 'rec-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8));
        row.innerHTML =
            '<div class="d-flex justify-content-between align-items-center mb-1">' +
                '<label class="small text-muted mb-0 lbl-recogida-num">Recogida</label>' +
                '<button type="button" class="btn btn-link btn-sm text-danger p-0 btn-quitar-recogida">Quitar</button>' +
            '</div>' +
            '<div class="pedido-picker-field">' +
                '<input type="text" class="picker-display txt-recogida-extra text-muted" readonly placeholder="Buscar almacén agrícola…">' +
                '<div class="picker-actions">' +
                    '<button type="button" class="btn btn-sm btn-picker-accion btn-buscar-recogida-extra" title="Buscar almacén">' +
                        '<i class="fas fa-search"></i>' +
                    '</button>' +
                '</div>' +
            '</div>' +
            '<input type="hidden" data-field="latitud" value="">' +
            '<input type="hidden" data-field="longitud" value="">' +
            '<input type="hidden" data-field="direccion" value="">' +
            '<input type="hidden" data-field="almacenid" value="">';

        if (datos.latitud) row.querySelector('[data-field="latitud"]').value = datos.latitud;
        if (datos.longitud) row.querySelector('[data-field="longitud"]').value = datos.longitud;
        if (datos.direccion) {
            row.querySelector('[data-field="direccion"]').value = datos.direccion;
            row.querySelector('.txt-recogida-extra').value = datos.direccion;
            row.querySelector('.txt-recogida-extra').classList.remove('text-muted');
        }
        if (datos.almacenid) row.querySelector('[data-field="almacenid"]').value = datos.almacenid;

        row.querySelector('.btn-buscar-recogida-extra').addEventListener('click', function () {
            if (bloquearRecogidaSiDestino()) return;
            if (!recogida1Lista()) {
                aviso('Primero elija la recogida 1.');
                return;
            }
            pickerRecogidaActivo = row;
            CatalogoSelector.open('pedido_almacen_recogida_extra');
        });
        row.querySelector('.btn-quitar-recogida').addEventListener('click', function () {
            if (bloquearRecogidaSiDestino()) return;
            row.remove();
            renumerarRecogidas();
            syncFilasProducto();
            redrawRoute();
        });

        return row;
    }

    function ejecutarReinicioRuta() {
        document.getElementById('recogidas-extra-container').innerHTML = '';
        limpiarOrigen();
        limpiarDestino();
        document.getElementById('rutaResumen').textContent = '';
        document.getElementById('productos-recogida-container').innerHTML = '';
        syncFilasProducto();
        resetCostoEnvio();
        redrawRoute();
    }

    function reiniciarRuta() {
        if (window.ModalConfirmar && typeof ModalConfirmar.confirmar === 'function') {
            ModalConfirmar.confirmar({
                titulo: 'Reiniciar ruta',
                mensaje: '¿Reiniciar toda la ruta? Se borrarán recogidas, destino, productos y el trazo del mapa.',
                tono: 'warning',
                btnText: 'Reiniciar',
            }).then(function (ok) {
                if (ok) ejecutarReinicioRuta();
            });
            return;
        }
        if (confirm('¿Reiniciar toda la ruta? Se borrarán recogidas, destino, productos y el trazo del mapa.')) {
            ejecutarReinicioRuta();
        }
    }

    function prepararEnvioPlanta() {
        document.querySelectorAll('.producto-recogida-row').forEach(function (fila) {
            if (window.PedidoFase2?.syncObservacionesCargaFila) {
                window.PedidoFase2.syncObservacionesCargaFila(fila);
            }
        });
    }

    function registrarEnvioPlantaSubmit() {
        const form = document.getElementById('form-pedido');
        if (!form || form.dataset.submitPlantaInit === '1') return;
        form.dataset.submitPlantaInit = '1';

        const costoInput = document.getElementById('costo_bs');
        if (costoInput && costoInput.dataset.costoListener !== '1') {
            costoInput.dataset.costoListener = '1';
            costoInput.addEventListener('input', function () {
                costoEditadoManual = true;
            });
        }

        form.addEventListener('submit', function (e) {
            prepararEnvioPlanta();

            if (!document.getElementById('fechaEntregaDeseada')?.value) {
                e.preventDefault();
                aviso('Indique la fecha de entrega deseada.');
                return;
            }
            if (!document.getElementById('origen_latitud').value) {
                e.preventDefault();
                aviso('Seleccione al menos la recogida 1.');
                return;
            }
            if (hayRecogidasExtraVacias()) {
                e.preventDefault();
                aviso('Complete o elimine las recogidas adicionales vacías.');
                return;
            }
            if (!document.getElementById('latitud').value) {
                e.preventDefault();
                aviso('Seleccione el almacén de planta (destino).');
                return;
            }
            const filasProducto = document.querySelectorAll('.producto-recogida-row');
            if (!filasProducto.length) {
                e.preventDefault();
                aviso('Indique el producto a recoger en cada almacén de la ruta.');
                return;
            }
            let faltaProducto = false;
            let faltaCantidad = false;
            filasProducto.forEach(function (fila) {
                if (!fila.querySelector('.selector-catalogo-value')?.value) faltaProducto = true;
                const cant = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value);
                if (!Number.isFinite(cant) || cant <= 0) faltaCantidad = true;
            });
            if (faltaProducto) {
                e.preventDefault();
                aviso('Complete el producto solicitado en cada recogida.');
                return;
            }
            if (faltaCantidad) {
                e.preventDefault();
                aviso('Indique la cantidad en kg para cada producto de la carga.');
                return;
            }
            if (window.PedidoFase2?.validarCargaPlanta) {
                const v = window.PedidoFase2.validarCargaPlanta();
                if (!v.ok) {
                    e.preventDefault();
                    aviso(v.mensaje);
                    return;
                }
            }
            let excedeStock = false;
            filasProducto.forEach(function (fila) {
                const cantInput = fila.querySelector('[data-field="cantidad"]');
                const max = cantInput?.max ? parseFloat(cantInput.max) : NaN;
                const val = cantInput?.value ? parseFloat(cantInput.value) : NaN;
                if (!isNaN(max) && !isNaN(val) && val > max) excedeStock = true;
            });
            if (excedeStock) {
                e.preventDefault();
                aviso('La cantidad solicitada supera el stock disponible en algún producto.');
                return;
            }
            if (!document.getElementById('transportista_usuarioid_create').value || !document.getElementById('vehiculoid_create').value) {
                e.preventDefault();
                aviso('Seleccione chofer y vehículo para el envío.');
                return;
            }
            const costoInput = document.getElementById('costo_bs');
            const costo = costoInput ? parseFloat(String(costoInput.value).replace(',', '.')) : NaN;
            if (!Number.isFinite(costo) || costo <= 0) {
                e.preventDefault();
                aviso('Ingrese el costo del servicio en bolivianos (mayor a 0).');
                return;
            }
        });
    }

    function initMapa() {
        const el = document.getElementById('mapaPedidoEntrega');
        if (!el || !window.L) return;

        state.map = L.map(el, { scrollWheelZoom: true }).setView([hub.lat, hub.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap',
        }).addTo(state.map);
        state.capasAlmacenes = L.layerGroup();
        state.capasRuta = L.layerGroup().addTo(state.map);

        document.getElementById('btnVerAlmacenesMapa')?.addEventListener('click', toggleAlmacenesEnMapa);
        document.getElementById('btnReiniciarRuta')?.addEventListener('click', reiniciarRuta);
        document.getElementById('btnLimpiarOrigen')?.addEventListener('click', function () {
            if (bloquearRecogidaSiDestino()) return;
            limpiarOrigen();
        });
        document.getElementById('btnLimpiarDestino')?.addEventListener('click', limpiarDestino);

        setTimeout(function () {
            state.map.invalidateSize();
            @if(is_array(old('recogidas')))
            @foreach(old('recogidas') as $rec)
            document.getElementById('recogidas-extra-container').appendChild(crearFilaRecogidaExtra(@json($rec)));
            @endforeach
            renumerarRecogidas();
            @endif
            syncFilasProducto();
            @if(is_array(old('detalles')))
            (function () {
                const oldDetalles = @json(old('detalles'));
                document.querySelectorAll('.producto-recogida-row').forEach(function (fila, i) {
                    const d = oldDetalles[i];
                    if (!d) return;
                    const selId = fila.getAttribute('data-selector-id');
                    if (selId && d.producto_ref) {
                        CatalogoSelector.setValue(selId, d.producto_ref, d.producto_label || d.producto_ref);
                    }
                    const cant = fila.querySelector('[data-field="cantidad"]');
                    const obs = fila.querySelector('[data-field="observaciones"]');
                    if (cant && d.cantidad) cant.value = d.cantidad;
                    if (obs && d.observaciones) obs.value = d.observaciones;
                });
            })();
            @endif
            redrawRoute();
            if (ALMACENES_MAPA.length) {
                toggleAlmacenesEnMapa();
            }
        }, 200);
    }

    const stateTraslado = {
        map: null,
        capasRuta: null,
        capasAlmacenes: null,
        routeLayer: null,
        markers: [],
        almacenesVisibles: false,
        cargandoAlmacenes: false,
    };
    let redrawTrasladoToken = 0;
    let mapaTrasladoListo = false;
    let costoTrasladoEditadoManual = false;
    let costoTrasladoToken = 0;
    let costoPdvEditadoManual = false;
    let costoPdvToken = 0;
    let trasladoProductoIdx = 0;
    const trasladoProductosPreseleccionados = [];
    let ultimoOrigenTrasladoId = '';
    let pdvProductoPreseleccionado = null;
    let ultimoAlmacenPdvId = '';
    const INSUMOS_PLANTA_ENDPOINT = @json(route('catalogo-selector.insumos'));
    const PRESENTACIONES_PRODUCTO_ENDPOINT = @json(route('catalogo-selector.presentaciones-producto'));
    const STOCK_PRESENTACION_LOTE_ENDPOINT = @json(route('catalogo-selector.stock-presentacion-lote'));

    function valorSelectorTraslado(id) {
        return document.querySelector('#selector_wrap_' + id + ' .selector-catalogo-value')?.value || '';
    }

    function tieneOrigenTraslado() {
        return !!valorSelectorTraslado('traslado_planta_origen');
    }

    function tieneDestinoTraslado() {
        return !!valorSelectorTraslado('traslado_mayorista_destino');
    }

    function itemTrasladoPorId(id) {
        return ALMACENES_MAPA_TRASLADO.find(function (x) { return String(x.id) === String(id); });
    }

    function coordsTrasladoSeleccionadas() {
        const coords = [];
        const origen = itemTrasladoPorId(valorSelectorTraslado('traslado_planta_origen'));
        const destino = itemTrasladoPorId(valorSelectorTraslado('traslado_mayorista_destino'));
        if (origen) {
            coords.push({ lat: parseFloat(origen.extra.lat), lng: parseFloat(origen.extra.lng) });
        }
        if (destino) {
            coords.push({ lat: parseFloat(destino.extra.lat), lng: parseFloat(destino.extra.lng) });
        }
        return coords;
    }

    function coordTrasladoYaSeleccionada(lat, lng) {
        return coordsTrasladoSeleccionadas().some(function (c) {
            return Math.abs(c.lat - lat) < 0.00001 && Math.abs(c.lng - lng) < 0.00001;
        });
    }

    function flashMapaMsgTraslado(texto) {
        const el = document.getElementById('mapa-traslado-asignacion-flash');
        if (!el) return;
        el.textContent = texto;
        el.style.display = 'block';
        clearTimeout(flashMapaMsgTraslado._t);
        flashMapaMsgTraslado._t = setTimeout(function () { el.style.display = 'none'; }, 2200);
    }

    function iconAlmacenTraslado(ambito, seleccionado) {
        const esPlanta = ambito === 'planta';
        const icon = esPlanta ? 'fa-industry' : 'fa-warehouse';
        const clase = esPlanta ? 'almacen-mapa-pin--traslado-planta' : 'almacen-mapa-pin--traslado-mayorista';
        const sel = seleccionado ? ' is-selected' : '';
        return L.divIcon({
            html: '<div class="almacen-mapa-pin ' + clase + sel + '"><i class="fas ' + icon + '"></i></div>',
            className: 'almacen-mapa-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    function iconMarkerTraslado(color, numero) {
        const html = '<div style="background:' + color + ';color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)">' + numero + '</div>';
        return L.divIcon({
            html: html,
            className: 'custom-marker',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });
    }

    function syncEstadoRutaTraslado() {
        const origenFijado = tieneOrigenTraslado();
        const destinoFijado = tieneDestinoTraslado();
        const bloqueOrigen = document.getElementById('bloque-origen-traslado');
        const bloqueDestino = document.getElementById('bloque-destino-traslado');
        const msgDestino = document.getElementById('traslado-destino-bloqueado-msg');

        if (bloqueOrigen) {
            bloqueOrigen.classList.toggle('ruta-bloqueada', destinoFijado);
        }
        if (bloqueDestino && msgDestino) {
            bloqueDestino.classList.toggle('ruta-bloqueada', !origenFijado);
            if (!origenFijado) {
                msgDestino.textContent = 'Primero elija el almacén de planta (origen).';
            } else if (destinoFijado) {
                msgDestino.textContent = 'Ruta definida. Use Reiniciar ruta si necesita cambiar el origen.';
            } else {
                msgDestino.textContent = 'Ahora puede elegir el centro mayorista (destino).';
            }
        }
        actualizarBtnVerOrigenTraslado();
    }

    function actualizarBtnVerOrigenTraslado() {
        const btn = document.getElementById('btnVerProductosOrigenTraslado');
        if (btn) btn.classList.toggle('d-none', !valorSelectorTraslado('traslado_planta_origen'));
    }

    function actualizarPickerOrigenTraslado(item) {
        const display = document.getElementById('txtNombreOrigenTraslado');
        const coords = document.getElementById('txtOrigenTrasladoCoords');
        if (!display) return;
        if (item) {
            display.value = etiquetaAlmacenPedido(item) || item.label || '';
            display.classList.remove('text-muted');
            if (coords && item.extra?.lat) {
                coords.textContent = 'GPS: ' + parseFloat(item.extra.lat).toFixed(5) + ', ' + parseFloat(item.extra.lng).toFixed(5);
            }
        } else {
            display.value = '';
            display.classList.add('text-muted');
            if (coords) coords.textContent = '';
        }
    }

    function actualizarPickerDestinoTraslado(item) {
        const display = document.getElementById('txtNombreDestinoTraslado');
        const coords = document.getElementById('txtDestinoTrasladoCoords');
        if (!display) return;
        if (item) {
            display.value = etiquetaAlmacenPedido(item) || item.label || '';
            display.classList.remove('text-muted');
            if (coords && item.extra?.lat) {
                coords.textContent = 'GPS: ' + parseFloat(item.extra.lat).toFixed(5) + ', ' + parseFloat(item.extra.lng).toFixed(5);
            }
        } else {
            display.value = '';
            display.classList.add('text-muted');
            if (coords) coords.textContent = '';
        }
    }

    function waypointsTraslado() {
        const puntos = [];
        const origen = itemTrasladoPorId(valorSelectorTraslado('traslado_planta_origen'));
        const destino = itemTrasladoPorId(valorSelectorTraslado('traslado_mayorista_destino'));
        if (origen) {
            const lat = parseFloat(origen.extra.lat);
            const lng = parseFloat(origen.extra.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                puntos.push({ lat: lat, lng: lng, label: origen.label, tipo: 'origen' });
            }
        }
        if (destino) {
            const lat = parseFloat(destino.extra.lat);
            const lng = parseFloat(destino.extra.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                puntos.push({ lat: lat, lng: lng, label: destino.label, tipo: 'destino' });
            }
        }
        return puntos;
    }

    function aplicarOrigenTraslado(item, opts) {
        opts = opts || {};
        if (!item || !window.CatalogoSelector) return;
        CatalogoSelector.setValue('traslado_planta_origen', item.id, item.label);
        actualizarPickerOrigenTraslado(item);
        syncEstadoRutaTraslado();
        actualizarParamsProductosTraslado();
        recargarPresentacionesFilasTraslado();
        if (opts.abrirInventario !== false && item.id) {
            abrirInventarioOrigenTraslado(item);
        }
    }

    function aplicarDestinoTraslado(item) {
        if (!item || !window.CatalogoSelector) return;
        CatalogoSelector.setValue('traslado_mayorista_destino', item.id, item.label);
        actualizarPickerDestinoTraslado(item);
        syncEstadoRutaTraslado();
    }

    function bloquearOrigenTrasladoSiDestino() {
        if (tieneDestinoTraslado()) {
            aviso('Ya definió el destino. Use «Reiniciar ruta» para volver a editar el origen.');
            return true;
        }
        return false;
    }

    function asignarAlmacenDesdeMapaTraslado(item) {
        const ambito = item.extra?.ambito || 'planta';
        if (ambito === 'mayorista') {
            if (!tieneOrigenTraslado()) {
                aviso('Primero elija el almacén de planta (origen).');
                return;
            }
            aplicarDestinoTraslado(item);
            flashMapaMsgTraslado('Destino asignado: ' + item.label);
            resaltarAlmacenesEnMapaTraslado();
            redrawRutaTraslado();
            return;
        }
        if (bloquearOrigenTrasladoSiDestino()) return;
        aplicarOrigenTraslado(item);
        flashMapaMsgTraslado('Origen asignado: ' + item.label);
        resaltarAlmacenesEnMapaTraslado();
        redrawRutaTraslado();
    }

    function pintarAlmacenesEnMapaTraslado(items) {
        stateTraslado.capasAlmacenes.clearLayers();
        const bounds = [];
        items.forEach(function (item) {
            const lat = parseFloat(item.extra?.lat);
            const lng = parseFloat(item.extra?.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            const ambito = item.extra.ambito || 'planta';
            const seleccionado = coordTrasladoYaSeleccionada(lat, lng);
            const m = L.marker([lat, lng], {
                icon: iconAlmacenTraslado(ambito, seleccionado),
                zIndexOffset: seleccionado ? 200 : 0,
            })
                .bindTooltip(item.label, {
                    className: 'almacen-mapa-tooltip',
                    direction: 'top',
                    offset: [0, -14],
                })
                .addTo(stateTraslado.capasAlmacenes);
            m.on('mouseover', function () { this.openTooltip(); });
            m.on('click', function () { asignarAlmacenDesdeMapaTraslado(item); });
            m._almacenItem = item;
            bounds.push([lat, lng]);
        });
        return bounds;
    }

    function resaltarAlmacenesEnMapaTraslado() {
        if (!stateTraslado.almacenesVisibles || !stateTraslado.capasAlmacenes) return;
        stateTraslado.capasAlmacenes.eachLayer(function (layer) {
            if (!layer._almacenItem) return;
            const item = layer._almacenItem;
            const lat = parseFloat(item.extra.lat);
            const lng = parseFloat(item.extra.lng);
            const ambito = item.extra.ambito || 'planta';
            const sel = coordTrasladoYaSeleccionada(lat, lng);
            layer.setIcon(iconAlmacenTraslado(ambito, sel));
            layer.setZIndexOffset(sel ? 200 : 0);
        });
    }

    function ajustarVistaMapaTraslado(bounds) {
        if (!bounds.length || !stateTraslado.map) return;
        const rutaPts = waypointsTraslado();
        const puntos = bounds.slice();
        rutaPts.forEach(function (p) { puntos.push([p.lat, p.lng]); });
        try {
            stateTraslado.map.fitBounds(L.latLngBounds(puntos).pad(0.1));
        } catch (e) {
            stateTraslado.map.setView([puntos[0][0], puntos[0][1]], 12);
        }
    }

    function mostrarAlmacenesEnMapaTraslado() {
        const items = ALMACENES_MAPA_TRASLADO || [];
        if (!items.length) {
            aviso('No hay almacenes con ubicación para mostrar en el mapa.');
            return false;
        }
        const bounds = pintarAlmacenesEnMapaTraslado(items);
        if (!stateTraslado.map.hasLayer(stateTraslado.capasAlmacenes)) {
            stateTraslado.map.addLayer(stateTraslado.capasAlmacenes);
        }
        stateTraslado.almacenesVisibles = true;
        ajustarVistaMapaTraslado(bounds);
        return true;
    }

    function toggleAlmacenesEnMapaTraslado() {
        const btn = document.getElementById('btnVerAlmacenesMapaTraslado');
        if (!btn || stateTraslado.cargandoAlmacenes) return;

        if (stateTraslado.almacenesVisibles) {
            if (stateTraslado.map.hasLayer(stateTraslado.capasAlmacenes)) {
                stateTraslado.map.removeLayer(stateTraslado.capasAlmacenes);
            }
            stateTraslado.almacenesVisibles = false;
            btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
            btn.classList.remove('active');
            return;
        }

        stateTraslado.cargandoAlmacenes = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando almacenes…';

        window.requestAnimationFrame(function () {
            try {
                if (mostrarAlmacenesEnMapaTraslado()) {
                    btn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i> Ocultar almacenes';
                    btn.classList.add('active');
                }
            } catch (e) {
                console.error(e);
                if (stateTraslado.map.hasLayer(stateTraslado.capasAlmacenes)) {
                    stateTraslado.map.removeLayer(stateTraslado.capasAlmacenes);
                }
                stateTraslado.almacenesVisibles = false;
                aviso('No se pudieron mostrar los almacenes en el mapa. Intente de nuevo.');
                btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
                btn.classList.remove('active');
            } finally {
                stateTraslado.cargandoAlmacenes = false;
                btn.disabled = false;
            }
        });
    }

    function limpiarCapaMapaTraslado() {
        if (!stateTraslado.capasRuta) return;
        if (stateTraslado.routeLayer) {
            stateTraslado.capasRuta.removeLayer(stateTraslado.routeLayer);
            stateTraslado.routeLayer = null;
        }
        stateTraslado.markers.forEach(function (m) { stateTraslado.capasRuta.removeLayer(m); });
        stateTraslado.markers = [];
    }

    function rebuildMarcadoresTraslado(puntos) {
        limpiarCapaMapaTraslado();
        if (!stateTraslado.capasRuta) return;
        puntos.forEach(function (p, i) {
            const esDestino = p.tipo === 'destino';
            const color = esDestino ? '#ea580c' : '#7c3aed';
            const marker = L.marker([p.lat, p.lng], { icon: iconMarkerTraslado(color, i + 1), zIndexOffset: 1000 }).addTo(stateTraslado.capasRuta);
            marker.bindTooltip(p.label, { className: 'almacen-mapa-tooltip', direction: 'top', offset: [0, -14] });
            stateTraslado.markers.push(marker);
        });
        resaltarAlmacenesEnMapaTraslado();
    }

    async function redrawRutaTraslado() {
        if (!window.RutaPorCalles || !stateTraslado.capasRuta) return;
        const token = ++redrawTrasladoToken;
        const puntos = waypointsTraslado();
        rebuildMarcadoresTraslado(puntos);
        const resumenEl = document.getElementById('rutaResumenTraslado');

        if (puntos.length < 2) {
            if (resumenEl) {
                resumenEl.textContent = puntos.length === 1
                    ? 'Agregue el centro mayorista (destino) para trazar la ruta.'
                    : '';
            }
            if (window.PedidoFase2) window.PedidoFase2.setRouteDuration(null);
            syncEstadoRutaTraslado();
            return;
        }

        const routeResult = await RutaPorCalles.fetchRoute(puntos);
        if (token !== redrawTrasladoToken) return;

        if (routeResult?.geojson) {
            stateTraslado.routeLayer = L.geoJSON(routeResult.geojson, {
                style: {
                    color: routeResult.straight ? '#c084fc' : '#7c3aed',
                    weight: 5,
                    opacity: 0.85,
                    dashArray: routeResult.straight ? '8,8' : null,
                },
            }).addTo(stateTraslado.capasRuta);

            if (!stateTraslado.almacenesVisibles) {
                const bounds = L.latLngBounds(puntos.map(function (p) { return [p.lat, p.lng]; }));
                try { stateTraslado.map.fitBounds(bounds.pad(0.15)); } catch (e) {}
            }

            const km = routeResult.distance_m ? Math.round(routeResult.distance_m / 1000) : null;
            const min = routeResult.duration_s ? Math.round(routeResult.duration_s / 60) : null;
            let resumen = routeResult.straight ? 'Ruta estimada (línea recta)' : 'Ruta por calles';
            if (km && min) resumen += ' · ~' + km + ' km · ~' + min + ' min';
            if (resumenEl) resumenEl.textContent = resumen;
        }
        if (window.PedidoFase2) {
            window.PedidoFase2.setRouteDuration(routeResult?.duration_s ?? null);
        }
        syncEstadoRutaTraslado();
        actualizarCostoTraslado(routeResult?.distance_m ?? null);
    }

    function resetCostoTraslado() {
        costoTrasladoEditadoManual = false;
        const input = document.getElementById('costo_bs');
        if (input) input.value = '0';
        const detalle = document.getElementById('wizard-costo-detalle');
        if (detalle) {
            detalle.textContent = 'Fórmula: Bs 10 base + Bs 2,40/km + Bs 5 por parada extra (mín. Bs 15). Recargo +25% si hay lluvia. Puede editarlo.';
        }
    }

    async function actualizarCostoTraslado(distanciaMetros) {
        if (costoTrasladoEditadoManual) return;

        const puntos = waypointsTraslado();
        const token = ++costoTrasladoToken;
        const detalle = document.getElementById('wizard-costo-detalle');

        if (puntos.length < 2) {
            resetCostoTraslado();
            return;
        }

        if (detalle) detalle.textContent = 'Calculando costo de envío…';

        try {
            const resp = await fetch(COSTO_ENVIO_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    paradas: puntos.map(function (p) { return { lat: p.lat, lng: p.lng }; }),
                    distancia_m: distanciaMetros,
                }),
            });
            if (token !== costoTrasladoToken) return;
            if (!resp.ok) throw new Error('No se pudo calcular el costo');
            const data = await resp.json();
            const input = document.getElementById('costo_bs');
            if (input && !costoTrasladoEditadoManual) {
                input.value = String(Math.round(Number(data.costo_bs || 0)));
            }
            if (detalle) {
                let texto = data.detalle || 'Costo estimado según la ruta.';
                if (data.lluvia && data.descripcion_clima) {
                    texto += ' Clima: ' + data.descripcion_clima + '.';
                }
                detalle.textContent = texto;
            }
        } catch (e) {
            if (detalle) {
                detalle.textContent = 'No se pudo calcular automáticamente. Ingrese el costo manualmente.';
            }
        }
    }

    function resetCostoPdv() {
        costoPdvEditadoManual = false;
        const input = document.getElementById('costo_bs');
        if (input) input.value = '0';
        const detalle = document.getElementById('wizard-costo-detalle');
        if (detalle) {
            detalle.textContent = 'Fórmula: Bs 10 base + Bs 2,40/km + Bs 5 por parada extra (mín. Bs 15). Recargo +25% si hay lluvia. Puede editarlo.';
        }
    }

    async function actualizarCostoPdv(distanciaMetros) {
        if (costoPdvEditadoManual) return;

        const puntos = waypointsPdv();
        const token = ++costoPdvToken;
        const detalle = document.getElementById('wizard-costo-detalle');

        if (puntos.length < 2) {
            resetCostoPdv();
            return;
        }

        if (detalle) detalle.textContent = 'Calculando costo de envío…';

        try {
            const resp = await fetch(COSTO_ENVIO_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    paradas: puntos.map(function (p) { return { lat: p.lat, lng: p.lng }; }),
                    distancia_m: distanciaMetros,
                }),
            });
            if (token !== costoPdvToken) return;
            if (!resp.ok) throw new Error('No se pudo calcular el costo');
            const data = await resp.json();
            const input = document.getElementById('costo_bs');
            if (input && !costoPdvEditadoManual) {
                input.value = String(Math.round(Number(data.costo_bs || 0)));
            }
            if (detalle) {
                let texto = data.detalle || 'Costo estimado según la ruta.';
                if (data.lluvia && data.descripcion_clima) {
                    texto += ' Clima: ' + data.descripcion_clima + '.';
                }
                detalle.textContent = texto;
            }
        } catch (e) {
            if (detalle) {
                detalle.textContent = 'No se pudo calcular automáticamente. Ingrese el costo manualmente.';
            }
        }
    }

    function almacenPlantaTrasladoId() {
        return valorSelectorTraslado('traslado_planta_origen');
    }

    function syncBloqueProductosTraslado() {
        const visible = !!almacenPlantaTrasladoId();
        if (visible) {
            if (trasladoProductosPreseleccionados.length) {
                sincronizarFilasTrasladoDesdePreseleccion();
            } else {
                const filas = document.querySelectorAll('.traslado-producto-row');
                if (!filas.length) agregarFilaProductoTraslado();
            }
            actualizarParamsProductosTraslado();
        }
    }

    function textoCantidadProductos(n) {
        if (n === 1) return '1 producto seleccionado';
        return n + ' productos seleccionados';
    }

    function actualizarResumenProductosPreseleccionadosTraslado() {
        const el = document.getElementById('traslado-productos-preseleccion-resumen');
        if (!el) return;
        const n = trasladoProductosPreseleccionados.length;
        if (n <= 0) {
            el.classList.add('d-none');
            el.textContent = '';
            return;
        }
        el.textContent = textoCantidadProductos(n);
        el.classList.remove('d-none');
    }

    function parseStockNum(val) {
        if (val === null || val === undefined || val === '') return NaN;
        const n = parseFloat(val);
        return Number.isFinite(n) ? n : NaN;
    }

    function stockDisponibleFilaTraslado(fila) {
        if (!fila) return { unidades: NaN, kg: NaN, loteRequerido: false, loteOk: true };
        const modo = fila.dataset.modoCantidad || '';
        if (modo !== 'unidades') {
            return { unidades: NaN, kg: parseStockNum(fila.dataset.stockKg), loteRequerido: false, loteOk: true };
        }
        const selLote = fila.querySelector('[data-field="inventario_lote"]');
        const loteRequerido = !!(selLote && !selLote.disabled && selLote.options.length > 1);
        const loteOk = !loteRequerido || !!selLote?.value;
        return {
            unidades: parseStockNum(fila.dataset.stockUnidades),
            kg: parseStockNum(fila.dataset.stockKgLote || fila.dataset.stockKg),
            loteRequerido: loteRequerido,
            loteOk: loteOk,
        };
    }

    function filaTrasladoTieneStockSuficiente(fila) {
        const stock = stockDisponibleFilaTraslado(fila);
        const modo = fila.dataset.modoCantidad || '';
        if (!stock.loteOk) return false;
        if (modo !== 'unidades') {
            const cantKg = parseStockNum(fila.querySelector('[data-field="cantidad"]')?.value);
            if (!Number.isFinite(cantKg) || cantKg <= 0) return false;
            return !Number.isFinite(stock.kg) || stock.kg <= 0 || cantKg <= stock.kg + 0.0001;
        }
        const pres = fila.querySelector('[data-field="presentacion"]')?.value;
        const unidades = parseInt(fila.querySelector('[data-field="cantidad_unidades"]')?.value || '0', 10);
        const cantKg = parseStockNum(fila.querySelector('[data-field="cantidad"]')?.value);
        if (!pres || unidades <= 0 || cantKg <= 0) return false;
        if (Number.isFinite(stock.unidades) && stock.unidades > 0) {
            return unidades <= stock.unidades;
        }
        if (Number.isFinite(stock.kg) && stock.kg > 0) {
            return cantKg <= stock.kg + 0.0001;
        }
        return false;
    }

    function aplicarStockEnFilaTraslado(fila, extra) {
        if (!fila) return;
        const stockLbl = fila.querySelector('.lbl-stock-traslado');
        const stock = parseStockNum(extra?.stock);
        const unidad = extra?.unidad || 'kg';
        fila.dataset.stockKg = Number.isFinite(stock) ? String(stock) : '';
        if (stockLbl) {
            if (Number.isFinite(stock) && (fila.dataset.modoCantidad || '') !== 'unidades') {
                stockLbl.textContent = 'Disponible (total producto): ' + stock.toLocaleString('es-BO', { maximumFractionDigits: 2 }) + ' ' + unidad;
                stockLbl.classList.remove('d-none');
            } else {
                stockLbl.textContent = '';
                stockLbl.classList.add('d-none');
            }
        }
        recalcularKgFilaTraslado(fila);
    }

    async function fetchPresentacionesInsumo(insumoId) {
        if (!insumoId) return [];
        const almacenId = almacenPlantaTrasladoId();
        try {
            let url = PRESENTACIONES_PRODUCTO_ENDPOINT + '?insumoid=' + encodeURIComponent(insumoId);
            if (almacenId) url += '&almacenid=' + encodeURIComponent(almacenId);
            const res = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return [];
            const json = await res.json();
            return json.data || [];
        } catch (e) {
            return [];
        }
    }

    async function fetchLotesPresentacion(presentacionId) {
        if (!presentacionId) return { data: [], stock_total_unidades: 0 };
        const almacenId = almacenPlantaTrasladoId();
        if (!almacenId) return { data: [], stock_total_unidades: 0 };
        try {
            const url = STOCK_PRESENTACION_LOTE_ENDPOINT
                + '?almacenid=' + encodeURIComponent(almacenId)
                + '&insumo_presentacionid=' + encodeURIComponent(presentacionId);
            const res = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return { data: [], stock_total_unidades: 0 };
            return await res.json();
        } catch (e) {
            return { data: [], stock_total_unidades: 0 };
        }
    }

    function resetPresentacionFilaTraslado(fila) {
        if (!fila) return;
        const bloquePres = fila.querySelector('.js-bloque-presentacion');
        const bloqueUnidades = fila.querySelector('.js-bloque-cantidad-unidades');
        const bloqueKg = fila.querySelector('.js-bloque-cantidad-kg');
        const bloqueLote = fila.querySelector('.js-bloque-lote');
        const selPres = fila.querySelector('[data-field="presentacion"]');
        const selLote = fila.querySelector('[data-field="inventario_lote"]');
        const inputUnidades = fila.querySelector('[data-field="cantidad_unidades"]');
        const inputKg = fila.querySelector('[data-field="cantidad"]');
        const hint = fila.querySelector('.lbl-equiv-traslado');
        if (selPres) {
            selPres.innerHTML = '<option value="">Seleccione…</option>';
            selPres.value = '';
            selPres.disabled = true;
        }
        if (selLote) {
            selLote.innerHTML = '<option value="">Seleccione presentación…</option>';
            selLote.value = '';
            selLote.disabled = true;
        }
        fila.dataset.stockUnidades = '';
        fila.dataset.stockKgLote = '';
        if (inputUnidades) {
            inputUnidades.value = '';
            inputUnidades.disabled = true;
        }
        if (inputKg) inputKg.value = '';
        if (hint) {
            hint.textContent = '';
            hint.classList.add('d-none');
        }
        bloquePres?.classList.remove('d-none');
        bloqueLote?.classList.add('d-none');
        bloqueUnidades?.classList.add('d-none');
        bloqueKg?.classList.add('d-none');
        fila.dataset.modoCantidad = '';
    }

    function mostrarPresentacionesVaciasFilaTraslado(fila) {
        const selPres = fila.querySelector('[data-field="presentacion"]');
        const bloquePres = fila.querySelector('.js-bloque-presentacion');
        const bloqueUnidades = fila.querySelector('.js-bloque-cantidad-unidades');
        const bloqueKg = fila.querySelector('.js-bloque-cantidad-kg');
        const bloqueLote = fila.querySelector('.js-bloque-lote');
        const inputUnidades = fila.querySelector('[data-field="cantidad_unidades"]');
        const hint = fila.querySelector('.lbl-equiv-traslado');
        bloquePres?.classList.remove('d-none');
        bloqueUnidades?.classList.add('d-none');
        bloqueKg?.classList.add('d-none');
        bloqueLote?.classList.add('d-none');
        if (selPres) {
            selPres.innerHTML = '<option value="">Sin empaques configurados</option>';
            selPres.disabled = true;
            selPres.value = '';
        }
        if (inputUnidades) {
            inputUnidades.value = '';
            inputUnidades.disabled = true;
            inputUnidades.required = false;
        }
        if (hint) {
            hint.textContent = 'Configure presentaciones comerciales (empaque) en el catálogo de productos terminados.';
            hint.classList.remove('d-none');
            hint.classList.add('is-error');
        }
        fila.dataset.modoCantidad = 'sin_presentacion';
        fila.classList.add('is-stock-error');
    }

    function poblarPresentacionesFilaTraslado(fila, presentaciones, seleccionId) {
        const selPres = fila.querySelector('[data-field="presentacion"]');
        const bloqueUnidades = fila.querySelector('.js-bloque-cantidad-unidades');
        const bloqueKg = fila.querySelector('.js-bloque-cantidad-kg');
        const inputUnidades = fila.querySelector('[data-field="cantidad_unidades"]');
        const inputKgHidden = fila.querySelector('[data-field="cantidad"]');
        const lblCantidad = fila.querySelector('.js-lbl-cantidad-unidades');

        if (!presentaciones.length) {
            mostrarPresentacionesVaciasFilaTraslado(fila);
            return;
        }

        fila.classList.remove('is-stock-error');

        fila.dataset.modoCantidad = 'unidades';
        bloqueKg?.classList.add('d-none');
        bloqueUnidades?.classList.remove('d-none');
        fila.querySelector('.js-bloque-lote')?.classList.remove('d-none');
        if (inputKgHidden) inputKgHidden.value = '';
        if (selPres) {
            selPres.innerHTML = '<option value="">Seleccione…</option>';
            presentaciones.forEach(function (p) {
                const opt = document.createElement('option');
                opt.value = String(p.id);
                opt.textContent = p.label;
                opt.dataset.pesoKg = String(p.extra?.peso_neto_kg || '');
                opt.dataset.unidadEtiqueta = p.extra?.unidad_etiqueta || 'unidades';
                opt.dataset.stockUnidades = String(p.extra?.stock_unidades ?? '');
                opt.dataset.stockKg = String(p.extra?.stock_kg ?? '');
                selPres.appendChild(opt);
            });
            selPres.disabled = false;
            if (seleccionId) selPres.value = String(seleccionId);
        }
        if (inputUnidades) {
            inputUnidades.disabled = !selPres?.value;
            inputUnidades.required = true;
        }
        actualizarEtiquetaCantidadTraslado(fila);
        if (selPres?.value) {
            return cargarLotesParaFilaTraslado(fila, selPres.value, null);
        }
        recalcularKgFilaTraslado(fila);
    }

    async function cargarLotesParaFilaTraslado(fila, presentacionId, seleccionLoteId) {
        const selLote = fila.querySelector('[data-field="inventario_lote"]');
        const bloqueLote = fila.querySelector('.js-bloque-lote');
        if (!selLote) {
            recalcularKgFilaTraslado(fila);
            return;
        }

        selLote.innerHTML = '<option value="">Cargando lotes…</option>';
        selLote.disabled = true;
        const payload = await fetchLotesPresentacion(presentacionId);
        const lotes = payload.data || [];

        if (!lotes.length) {
            selLote.innerHTML = '<option value="">Sin stock por lote</option>';
            selLote.disabled = true;
            selLote.value = '';
            bloqueLote?.classList.remove('d-none');
            fila.dataset.stockUnidades = '0';
            fila.dataset.stockKgLote = '0';
            recalcularKgFilaTraslado(fila);
            return;
        }

        bloqueLote?.classList.remove('d-none');
        selLote.innerHTML = '<option value="">Seleccione lote…</option>';
        lotes.forEach(function (l) {
            const opt = document.createElement('option');
            opt.value = String(l.id);
            opt.textContent = l.label;
            opt.dataset.unidades = String(l.extra?.cantidad_unidades || '');
            opt.dataset.kg = String(l.extra?.cantidad_kg || '');
            selLote.appendChild(opt);
        });
        selLote.disabled = false;
        if (seleccionLoteId) {
            selLote.value = String(seleccionLoteId);
        } else {
            selLote.value = String(lotes[0].id);
        }
        aplicarStockLoteEnFilaTraslado(fila);
        recalcularKgFilaTraslado(fila);
    }

    function aplicarStockLoteEnFilaTraslado(fila) {
        const selLote = fila.querySelector('[data-field="inventario_lote"]');
        const opt = selLote?.options[selLote.selectedIndex];
        if (opt && opt.value) {
            fila.dataset.stockUnidades = opt.dataset.unidades || '';
            fila.dataset.stockKgLote = opt.dataset.kg || '';
        } else {
            const selPres = fila.querySelector('[data-field="presentacion"]');
            const optPres = selPres?.options[selPres.selectedIndex];
            const su = parseStockNum(optPres?.dataset?.stockUnidades);
            const sk = parseStockNum(optPres?.dataset?.stockKg);
            fila.dataset.stockUnidades = Number.isFinite(su) ? String(su) : '';
            fila.dataset.stockKgLote = Number.isFinite(sk) && sk > 0 ? String(sk) : (fila.dataset.stockKg || '');
        }
    }

    function actualizarEtiquetaCantidadTraslado(fila) {
        const selPres = fila.querySelector('[data-field="presentacion"]');
        const lbl = fila.querySelector('.js-lbl-cantidad-unidades');
        if (!lbl || !selPres) return;
        const opt = selPres.options[selPres.selectedIndex];
        const unidad = opt?.dataset?.unidadEtiqueta || 'unidades';
        lbl.textContent = 'Cantidad (' + unidad + ')';
    }

    function recalcularKgFilaTraslado(fila) {
        if (!fila) return;
        const modo = fila.dataset.modoCantidad || '';
        const hint = fila.querySelector('.lbl-equiv-traslado');
        const inputKgHidden = fila.querySelector('[data-field="cantidad"]');
        const stockKg = parseStockNum(fila.dataset.stockKgLote || fila.dataset.stockKg);
        const stockUnidades = parseStockNum(fila.dataset.stockUnidades);
        const stockLbl = fila.querySelector('.lbl-stock-traslado');
        if (stockLbl) stockLbl.classList.add('d-none');

        if (modo !== 'unidades') {
            if (hint) hint.classList.add('d-none');
            fila.classList.remove('is-stock-error');
            window.PedidoFase2?.validarCapacidadVehiculo?.();
            return;
        }

        const selPres = fila.querySelector('[data-field="presentacion"]');
        const inputUnidades = fila.querySelector('[data-field="cantidad_unidades"]');
        const opt = selPres?.options[selPres.selectedIndex];
        const peso = parseFloat(opt?.dataset?.pesoKg || 0);
        const unidadEtiqueta = opt?.dataset?.unidadEtiqueta || 'unidades';
        const unidades = parseInt(inputUnidades?.value || '0', 10);
        const kg = peso > 0 && unidades > 0 ? peso * unidades : 0;

        if (inputKgHidden) inputKgHidden.value = kg > 0 ? kg.toFixed(4) : '';

        const sinStock = (!Number.isFinite(stockUnidades) || stockUnidades <= 0)
            && (!Number.isFinite(stockKg) || stockKg <= 0);
        const excede = (Number.isFinite(stockUnidades) && stockUnidades > 0 && unidades > stockUnidades)
            || (Number.isFinite(stockKg) && stockKg > 0 && kg > stockKg + 0.0001);
        const selLote = fila.querySelector('[data-field="inventario_lote"]');
        const lotePendiente = false;
        const mostrarErrorLote = lotePendiente && fila.dataset.mostrarErrorLote === '1';

        if (hint) {
            hint.classList.remove('is-error');
            if (mostrarErrorLote) {
                hint.textContent = 'Seleccione el lote a despachar.';
                hint.classList.remove('d-none');
                hint.classList.add('is-error');
            } else if (sinStock) {
                hint.textContent = 'Sin stock disponible para esta presentación.';
                hint.classList.remove('d-none');
                hint.classList.add('is-error');
            } else if (excede) {
                hint.textContent = 'Cantidad supera el stock del lote seleccionado.';
                hint.classList.remove('d-none');
                hint.classList.add('is-error');
            } else if (kg > 0) {
                let texto = 'Equivale a ' + kg.toLocaleString('es-BO', { maximumFractionDigits: 2 }) + ' kg';
                if (Number.isFinite(stockUnidades) && stockUnidades > 0) {
                    texto += ' · Disp. lote: ' + stockUnidades.toLocaleString('es-BO', { maximumFractionDigits: 0 }) + ' ' + unidadEtiqueta;
                } else if (Number.isFinite(stockKg) && stockKg > 0) {
                    texto += ' · Disponible: ' + stockKg.toLocaleString('es-BO', { maximumFractionDigits: 2 }) + ' kg';
                }
                hint.textContent = texto;
                hint.classList.remove('d-none');
            } else if (Number.isFinite(stockUnidades) && stockUnidades > 0) {
                hint.textContent = 'Disp. lote: ' + stockUnidades.toLocaleString('es-BO', { maximumFractionDigits: 0 }) + ' ' + unidadEtiqueta;
                hint.classList.remove('d-none');
            } else if (Number.isFinite(stockKg) && stockKg > 0) {
                hint.textContent = 'Disponible: ' + stockKg.toLocaleString('es-BO', { maximumFractionDigits: 2 }) + ' kg';
                hint.classList.remove('d-none');
            } else {
                hint.textContent = '';
                hint.classList.add('d-none');
            }
        }

        fila.classList.toggle('is-stock-error', !!(mostrarErrorLote || sinStock || excede));

        if (inputUnidades) {
            if (Number.isFinite(stockUnidades) && stockUnidades > 0) {
                inputUnidades.max = String(Math.floor(stockUnidades));
            } else if (Number.isFinite(stockKg) && stockKg > 0 && peso > 0) {
                inputUnidades.max = String(Math.floor(stockKg / peso));
            } else {
                inputUnidades.removeAttribute('max');
            }
        }

        window.PedidoFase2?.validarCapacidadVehiculo?.();
    }

    window.recalcularKgFilaTraslado = recalcularKgFilaTraslado;
    window.marcarErroresValidacionTraslado = function () {
        document.querySelectorAll('.traslado-producto-row').forEach(function (f) {
            delete f.dataset.mostrarErrorLote;
            const ins = f.querySelector('[data-field="insumoid"]')?.value;
            if (!ins) return;
            if ((f.dataset.modoCantidad || '') !== 'unidades') return;
            recalcularKgFilaTraslado(f);
        });
    };

    window.filaTrasladoTieneStockSuficiente = filaTrasladoTieneStockSuficiente;

    async function cargarPresentacionesParaFilaTraslado(fila, insumoId, seleccionId) {
        resetPresentacionFilaTraslado(fila);
        if (!insumoId) return;
        if (!almacenPlantaTrasladoId()) {
            const hint = fila.querySelector('.lbl-equiv-traslado');
            if (hint) {
                hint.textContent = 'Primero elija el almacén de planta (origen) en el paso Ruta.';
                hint.classList.remove('d-none');
                hint.classList.add('is-error');
            }
            return;
        }
        const presentaciones = await fetchPresentacionesInsumo(insumoId);
        poblarPresentacionesFilaTraslado(fila, presentaciones, seleccionId || null);
    }

    async function recargarPresentacionesFilasTraslado() {
        const filas = document.querySelectorAll('.traslado-producto-row');
        for (const fila of filas) {
            const insumoId = fila.querySelector('[data-field="insumoid"]')?.value;
            if (!insumoId) continue;
            const presId = fila.querySelector('[data-field="presentacion"]')?.value || null;
            const loteId = fila.querySelector('[data-field="inventario_lote"]')?.value || null;
            const unidades = fila.querySelector('[data-field="cantidad_unidades"]')?.value || '';
            await cargarPresentacionesParaFilaTraslado(fila, insumoId, presId);
            if (presId && loteId && (fila.dataset.modoCantidad || '') === 'unidades') {
                await cargarLotesParaFilaTraslado(fila, presId, loteId);
                const inputUnidades = fila.querySelector('[data-field="cantidad_unidades"]');
                if (inputUnidades && unidades) inputUnidades.value = unidades;
            }
            recalcularKgFilaTraslado(fila);
        }
    }

    function initEventosPresentacionFilaTraslado(fila) {
        const selPres = fila.querySelector('[data-field="presentacion"]');
        const inputUnidades = fila.querySelector('[data-field="cantidad_unidades"]');
        const inputKgVisible = fila.querySelector('[data-field="cantidad_kg_input"]');
        const inputKgHidden = fila.querySelector('[data-field="cantidad"]');

        selPres?.addEventListener('change', async function () {
            if (inputUnidades) {
                inputUnidades.disabled = !selPres.value;
                if (!selPres.value) inputUnidades.value = '';
            }
            actualizarEtiquetaCantidadTraslado(fila);
            if (selPres.value) {
                await cargarLotesParaFilaTraslado(fila, selPres.value, null);
            } else {
                resetPresentacionFilaTraslado(fila);
            }
        });
        fila.querySelector('[data-field="inventario_lote"]')?.addEventListener('change', function () {
            delete fila.dataset.mostrarErrorLote;
            aplicarStockLoteEnFilaTraslado(fila);
            recalcularKgFilaTraslado(fila);
        });
        inputUnidades?.addEventListener('input', function () {
            recalcularKgFilaTraslado(fila);
        });
        inputKgVisible?.addEventListener('input', function () {
            const stock = parseFloat(fila.dataset.stockKg || '');
            let val = parseFloat(inputKgVisible.value || 0);
            if (Number.isFinite(stock) && val > stock) {
                val = stock;
                inputKgVisible.value = String(stock);
            }
            if (inputKgHidden) inputKgHidden.value = val > 0 ? String(val) : '';
        });
    }

    function agregarProductoPreseleccionadoTraslado(item) {
        if (!item?.id) return;
        const id = String(item.id);
        const existente = trasladoProductosPreseleccionados.find(function (p) { return String(p.id) === id; });
        const prod = {
            id: id,
            label: item.label || '',
            extra: item.extra || {},
        };
        if (existente) {
            existente.label = prod.label;
            existente.extra = prod.extra;
        } else {
            trasladoProductosPreseleccionados.push(prod);
        }
        actualizarResumenProductosPreseleccionadosTraslado();
        sincronizarFilasTrasladoDesdePreseleccion();
    }

    function sincronizarFilasTrasladoDesdePreseleccion() {
        const container = document.getElementById('traslado-productos-container');
        if (!container || !trasladoProductosPreseleccionados.length) return;

        trasladoProductosPreseleccionados.forEach(function (prod, index) {
            let fila = Array.from(container.querySelectorAll('.traslado-producto-row')).find(function (f) {
                return f.querySelector('[data-field="insumoid"]')?.value === String(prod.id);
            });

            if (!fila) {
                const vacia = Array.from(container.querySelectorAll('.traslado-producto-row')).find(function (f) {
                    return !f.querySelector('[data-field="insumoid"]')?.value;
                });
                if (vacia && index === 0 && container.querySelectorAll('.traslado-producto-row').length === 1) {
                    fila = vacia;
                    const selId = fila.getAttribute('data-selector-id');
                    CatalogoSelector.setValue(selId, prod.id, prod.label);
                    aplicarStockEnFilaTraslado(fila, prod.extra);
                    cargarPresentacionesParaFilaTraslado(fila, prod.id);
                } else {
                    agregarFilaProductoTraslado({
                        insumoid: prod.id,
                        producto_nombre: prod.label,
                        extra: prod.extra,
                    });
                }
                return;
            }

            const selId = fila.getAttribute('data-selector-id');
            CatalogoSelector.setValue(selId, prod.id, prod.label);
            aplicarStockEnFilaTraslado(fila, prod.extra);
            cargarPresentacionesParaFilaTraslado(fila, prod.id);
        });
    }

    function limpiarPreseleccionProductosTraslado() {
        trasladoProductosPreseleccionados.length = 0;
        actualizarResumenProductosPreseleccionadosTraslado();
    }

    function actualizarResumenPdvProductoPreseleccionado() {
        const el = document.getElementById('pdv-producto-preseleccion-resumen');
        if (!el) return;
        if (!pdvProductoPreseleccionado?.id) {
            el.classList.add('d-none');
            el.textContent = '';
            return;
        }
        el.textContent = '1 producto seleccionado';
        el.classList.remove('d-none');
    }

    function aplicarProductoPreseleccionadoPdv(item) {
        if (!item?.id) return;
        pdvProductoPreseleccionado = {
            id: String(item.id),
            label: item.label || '',
            extra: item.extra || {},
            almacenId: valorSelectorPdv('pdv_unificado_almacen') || '',
        };
        actualizarResumenPdvProductoPreseleccionado();
        if (document.getElementById('selector_wrap_pdv_unificado_producto')) {
            CatalogoSelector.setValue('pdv_unificado_producto', pdvProductoPreseleccionado.id, pdvProductoPreseleccionado.label);
        }
    }

    function limpiarPreseleccionProductoPdv() {
        pdvProductoPreseleccionado = null;
        actualizarResumenPdvProductoPreseleccionado();
    }

    function actualizarParamsProductosTraslado() {
        const almacenId = almacenPlantaTrasladoId();
        document.querySelectorAll('.traslado-producto-row').forEach(function (fila) {
            const selId = fila.getAttribute('data-selector-id');
            const cfg = CatalogoSelector?.instances?.[selId];
            if (cfg) {
                cfg.params = {
                    ambito_planta: '1',
                    solo_con_stock: '1',
                    solo_producto_terminado: '1',
                    almacenid: almacenId || '',
                };
                cfg.title = 'Productos terminados en planta';
            }
        });
    }

    function renumerarDetallesTraslado() {
        document.querySelectorAll('.traslado-producto-row').forEach(function (fila, idx) {
            fila.querySelector('[data-field="insumoid"]')?.setAttribute('name', 'detalles[' + idx + '][insumoid]');
            fila.querySelector('[data-field="presentacion"]')?.setAttribute('name', 'detalles[' + idx + '][insumo_presentacionid]');
            fila.querySelector('[data-field="inventario_lote"]')?.setAttribute('name', 'detalles[' + idx + '][inventario_presentacion_loteid]');
            fila.querySelector('[data-field="cantidad_unidades"]')?.setAttribute('name', 'detalles[' + idx + '][cantidad_unidades]');
            fila.querySelector('[data-field="cantidad"]')?.setAttribute('name', 'detalles[' + idx + '][cantidad]');
            fila.querySelector('[data-field="observaciones"]')?.setAttribute('name', 'detalles[' + idx + '][observaciones]');
        });
    }

    function registrarSelectorProductoTraslado(selId) {
        if (!window.CatalogoSelector) return;
        CatalogoSelector.register(selId, {
            endpoint: INSUMOS_PLANTA_ENDPOINT,
            title: 'Productos terminados en planta',
            searchPlaceholder: 'Buscar producto terminado…',
            params: {
                ambito_planta: '1',
                solo_con_stock: '1',
                solo_producto_terminado: '1',
                almacenid: almacenPlantaTrasladoId() || '',
            },
            rowIcon: 'fa-box',
            theme: 'planta',
            onSelect: function (item) {
                const fila = document.querySelector('[data-selector-id="' + selId + '"]');
                if (!fila) return;
                aplicarStockEnFilaTraslado(fila, item.extra || {});
                cargarPresentacionesParaFilaTraslado(fila, item.id);
            },
        });
    }

    function agregarFilaProductoTraslado(detalle) {
        const container = document.getElementById('traslado-productos-container');
        if (!container) return;

        const idx = trasladoProductoIdx++;
        const selId = 'traslado_producto_' + idx;
        const fila = document.createElement('div');
        fila.className = 'traslado-producto-row';
        fila.setAttribute('data-selector-id', selId);
        fila.innerHTML =
            '<div class="traslado-producto-row__head">' +
                '<label>Producto terminado</label>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary btn-quitar-producto-traslado" title="Quitar línea">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</div>' +
            '<div class="form-row">' +
                '<div class="col-lg-3 col-md-6 mb-2 mb-lg-0">' +
                    '<span class="field-label">Producto</span>' +
                    '<div class="selector-catalogo-wrapper flex-grow-1 w-100 mb-0 selector-catalogo--filtros" id="selector_wrap_' + selId + '">' +
                        '<div class="selector-filtros-field">' +
                            '<input type="text" class="selector-filtros-field__input selector-catalogo-label is-empty" readonly placeholder="Elegir producto terminado…">' +
                            '<input type="hidden" data-field="insumoid" name="detalles[' + idx + '][insumoid]" class="selector-catalogo-value" required>' +
                            '<div class="selector-filtros-field__actions">' +
                                '<button type="button" class="selector-filtros-field__open" data-selector-open="' + selId + '">' +
                                    '<i class="fas fa-chevron-down"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<small class="lbl-stock-traslado d-none"></small>' +
                '</div>' +
                '<div class="col-lg-2 col-md-6 mb-2 mb-lg-0 js-bloque-presentacion">' +
                    '<span class="field-label">Presentación</span>' +
                    '<select data-field="presentacion" name="detalles[' + idx + '][insumo_presentacionid]" class="form-control form-control-sm" disabled>' +
                        '<option value="">Seleccione producto…</option>' +
                    '</select>' +
                '</div>' +
                '<div class="col-lg-2 col-md-6 mb-2 mb-lg-0 js-bloque-lote d-none">' +
                    '<span class="field-label">Lote <span class="text-muted font-weight-normal">(FIFO)</span></span>' +
                    '<select data-field="inventario_lote" name="detalles[' + idx + '][inventario_presentacion_loteid]" class="form-control form-control-sm" disabled>' +
                        '<option value="">Auto (primer lote)…</option>' +
                    '</select>' +
                '</div>' +
                '<div class="col-lg-2 col-md-4 mb-2 mb-lg-0 js-bloque-cantidad-unidades d-none">' +
                    '<span class="field-label js-lbl-cantidad-unidades">Cantidad</span>' +
                    '<input type="number" step="1" min="1" data-field="cantidad_unidades" name="detalles[' + idx + '][cantidad_unidades]" class="form-control form-control-sm" placeholder="Ej: 100" disabled>' +
                    '<small class="lbl-equiv-traslado d-none"></small>' +
                '</div>' +
                '<div class="col-lg-2 col-md-4 mb-2 mb-lg-0 js-bloque-cantidad-kg d-none">' +
                    '<span class="field-label">Cantidad (kg)</span>' +
                    '<input type="number" step="0.01" min="0.01" data-field="cantidad_kg_input" class="form-control form-control-sm" placeholder="Ej: 120">' +
                '</div>' +
                '<input type="hidden" data-field="cantidad" name="detalles[' + idx + '][cantidad]" value="">' +
                '<div class="col-lg-3 col-md-12">' +
                    '<span class="field-label">Observaciones <span class="text-muted font-weight-normal">(opcional)</span></span>' +
                    '<input type="text" data-field="observaciones" name="detalles[' + idx + '][observaciones]" class="form-control form-control-sm" placeholder="Lote, notas…">' +
                '</div>' +
            '</div>';

        fila.querySelector('.btn-quitar-producto-traslado').addEventListener('click', function () {
            if (document.querySelectorAll('.traslado-producto-row').length <= 1) {
                aviso('Debe indicar al menos un producto en el traslado.');
                return;
            }
            delete CatalogoSelector?.instances?.[selId];
            fila.remove();
            renumerarDetallesTraslado();
        });

        container.appendChild(fila);
        initEventosPresentacionFilaTraslado(fila);
        registrarSelectorProductoTraslado(selId);

        if (detalle?.insumoid) {
            CatalogoSelector.setValue(selId, detalle.insumoid, detalle.producto_nombre || detalle.label || '');
            const obsInput = fila.querySelector('[data-field="observaciones"]');
            if (obsInput && detalle.observaciones) obsInput.value = detalle.observaciones;
            if (detalle.extra) aplicarStockEnFilaTraslado(fila, detalle.extra);
            cargarPresentacionesParaFilaTraslado(fila, detalle.insumoid, detalle.insumo_presentacionid).then(function () {
                if (detalle.inventario_presentacion_loteid) {
                    const presId = detalle.insumo_presentacionid || fila.querySelector('[data-field="presentacion"]')?.value;
                    if (presId) {
                        cargarLotesParaFilaTraslado(fila, presId, detalle.inventario_presentacion_loteid);
                    }
                }
                if (detalle.cantidad_unidades) {
                    const inputUnidades = fila.querySelector('[data-field="cantidad_unidades"]');
                    if (inputUnidades) inputUnidades.value = detalle.cantidad_unidades;
                } else if (detalle.cantidad && fila.dataset.modoCantidad === 'kg') {
                    const inputKgVisible = fila.querySelector('[data-field="cantidad_kg_input"]');
                    const inputKgHidden = fila.querySelector('[data-field="cantidad"]');
                    if (inputKgVisible) inputKgVisible.value = detalle.cantidad;
                    if (inputKgHidden) inputKgHidden.value = detalle.cantidad;
                }
                recalcularKgFilaTraslado(fila);
            });
        }

        renumerarDetallesTraslado();
    }

    function limpiarProductosTraslado() {
        const container = document.getElementById('traslado-productos-container');
        if (!container) return;
        container.innerHTML = '';
        trasladoProductoIdx = 0;
        syncBloqueProductosTraslado();
    }

    function ejecutarReinicioRutaTraslado() {
        limpiarPreseleccionProductosTraslado();
        if (window.CatalogoSelector) {
            CatalogoSelector.clear('traslado_planta_origen');
            CatalogoSelector.clear('traslado_mayorista_destino');
        }
        actualizarPickerOrigenTraslado(null);
        actualizarPickerDestinoTraslado(null);
        const resumenEl = document.getElementById('rutaResumenTraslado');
        if (resumenEl) resumenEl.textContent = '';
        limpiarProductosTraslado();
        resetCostoTraslado();
        syncEstadoRutaTraslado();
        redrawRutaTraslado();
    }

    function reiniciarRutaTraslado() {
        if (window.ModalConfirmar && typeof ModalConfirmar.confirmar === 'function') {
            ModalConfirmar.confirmar({
                titulo: 'Reiniciar ruta',
                mensaje: '¿Reiniciar la ruta de traslado? Se borrarán origen, destino y el trazo del mapa.',
                tono: 'warning',
                btnText: 'Reiniciar',
            }).then(function (ok) {
                if (ok) ejecutarReinicioRutaTraslado();
            });
            return;
        }
        if (confirm('¿Reiniciar la ruta de traslado? Se borrarán origen, destino y el trazo del mapa.')) {
            ejecutarReinicioRutaTraslado();
        }
    }

    function mapaTrasladoContainerListo() {
        const el = document.getElementById('mapaTrasladoMayorista');
        if (!el) return false;
        const flujo = document.getElementById('flujo-mayorista');
        if (flujo?.classList.contains('d-none')) return false;
        const rect = el.getBoundingClientRect();
        return rect.width > 20 && rect.height > 20;
    }

    function initMapaTraslado() {
        const el = document.getElementById('mapaTrasladoMayorista');
        if (!el || !window.L || mapaTrasladoListo) return false;
        if (!mapaTrasladoContainerListo()) return false;

        stateTraslado.map = L.map(el, { scrollWheelZoom: true }).setView([hub.lat, hub.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap',
        }).addTo(stateTraslado.map);
        stateTraslado.capasAlmacenes = L.layerGroup();
        stateTraslado.capasRuta = L.layerGroup().addTo(stateTraslado.map);

        document.getElementById('btnVerAlmacenesMapaTraslado')?.addEventListener('click', toggleAlmacenesEnMapaTraslado);
        document.getElementById('btnReiniciarRutaTraslado')?.addEventListener('click', reiniciarRutaTraslado);

        const costoTrasladoInput = document.getElementById('costo_bs');
        if (costoTrasladoInput) {
            costoTrasladoInput.addEventListener('input', function () {
                costoTrasladoEditadoManual = true;
            });
        }

        document.getElementById('btnAgregarProductoTraslado')?.addEventListener('click', function () {
            if (!almacenPlantaTrasladoId()) {
                aviso('Primero elija el almacén de planta (origen).');
                return;
            }
            agregarFilaProductoTraslado();
        });

        document.getElementById('form-traslado-mayorista')?.addEventListener('submit', function (e) {
            if (!valorSelectorTraslado('traslado_planta_origen')) {
                e.preventDefault();
                aviso('Seleccione el almacén de planta (origen).');
                return;
            }
            if (!valorSelectorTraslado('traslado_mayorista_destino')) {
                e.preventDefault();
                aviso('Seleccione el centro mayorista (destino).');
                return;
            }
            const filas = document.querySelectorAll('.traslado-producto-row');
            if (!filas.length) {
                e.preventDefault();
                aviso('Indique al menos un producto a trasladar desde planta.');
                return;
            }
            let falta = false;
            let faltaPresentacion = false;
            filas.forEach(function (fila) {
                if (!fila.querySelector('.selector-catalogo-value')?.value) falta = true;
                const pres = fila.querySelector('[data-field="presentacion"]');
                if (pres && !pres.disabled && !pres.value) faltaPresentacion = true;
                const cant = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value || '0');
                if (!Number.isFinite(cant) || cant <= 0) falta = true;
            });
            if (faltaPresentacion) {
                e.preventDefault();
                aviso('Seleccione la presentación (empaque) de cada producto terminado.');
                return;
            }
            if (falta) {
                e.preventDefault();
                aviso('Complete producto y cantidad en cada línea del traslado.');
                return;
            }
            if (!document.getElementById('transportista_usuarioid_create')?.value) {
                e.preventDefault();
                aviso('Seleccione el chofer de planta.');
                return;
            }
            if (!document.getElementById('vehiculoid_create')?.value) {
                e.preventDefault();
                aviso('Seleccione el vehículo de planta.');
                return;
            }
            const costo = parseFloat(String(costoTrasladoInput?.value || '').replace(',', '.'));
            if (!Number.isFinite(costo) || costo <= 0) {
                e.preventDefault();
                aviso('Ingrese el costo del servicio en bolivianos (mayor a 0).');
            }
        });

        mapaTrasladoListo = true;
        syncEstadoRutaTraslado();

        setTimeout(function () {
            if (!stateTraslado.map) return;
            stateTraslado.map.invalidateSize();
            redrawRutaTraslado();
            if (ALMACENES_MAPA_TRASLADO.length) {
                toggleAlmacenesEnMapaTraslado();
            }
        }, 250);

        return true;
    }

    function refrescarTamanoMapaTraslado() {
        if (!stateTraslado.map) return;
        stateTraslado.map.invalidateSize();
        redrawRutaTraslado();
    }

    function ensureMapaTraslado() {
        if (!document.getElementById('mapaTrasladoMayorista')) return;

        function intentarInit() {
            if (!window.L) {
                return mapaTrasladoListo;
            }
            if (mapaTrasladoListo) {
                refrescarTamanoMapaTraslado();
                return true;
            }
            return initMapaTraslado() === true;
        }

        if (intentarInit()) {
            setTimeout(refrescarTamanoMapaTraslado, 80);
            setTimeout(refrescarTamanoMapaTraslado, 350);
            setTimeout(refrescarTamanoMapaTraslado, 800);
            return;
        }

        let intentos = 0;
        const reintentar = setInterval(function () {
            intentos += 1;
            if (intentarInit() || intentos >= 80) {
                clearInterval(reintentar);
                if (mapaTrasladoListo) {
                    setTimeout(refrescarTamanoMapaTraslado, 80);
                    setTimeout(refrescarTamanoMapaTraslado, 350);
                    setTimeout(refrescarTamanoMapaTraslado, 800);
                }
            }
        }, 100);
    }

    window.ensureMapaTraslado = ensureMapaTraslado;
    window.refrescarTamanoMapaTraslado = refrescarTamanoMapaTraslado;

    const statePdv = {
        map: null,
        capasRuta: null,
        capasPuntos: null,
        routeLayer: null,
        markers: [],
        puntosVisibles: false,
        cargandoPuntos: false,
    };
    let redrawPdvToken = 0;
    let mapaPdvListo = false;
    let pdvFiltroMinoristaId = null;
    let pdvFiltroMinoristaLabel = '';

    function valorSelectorPdv(id) {
        return document.querySelector('#selector_wrap_' + id + ' .selector-catalogo-value')?.value || '';
    }

    function itemPdvMapaPorId(id, tipo) {
        return PUNTOS_MAPA_PDV.find(function (x) {
            return String(x.id) === String(id) && (!tipo || x.tipo === tipo);
        });
    }

    function tieneOrigenPdv() {
        return PDV_MAPA_ES_ADMIN ? !!valorSelectorPdv('pdv_unificado_almacen') : false;
    }

    function tieneDestinoPdv() {
        return !!valorSelectorPdv('pdv_unificado_punto');
    }

    function puntosMapaPdvVisibles() {
        return PUNTOS_MAPA_PDV.filter(function (p) {
            if (p.tipo === 'mayorista') {
                return PDV_MAPA_ES_ADMIN;
            }
            if (!pdvFiltroMinoristaId) {
                return true;
            }
            return String(p.extra?.minorista_usuarioid) === String(pdvFiltroMinoristaId);
        });
    }

    function actualizarResumenFiltroMinoristaPdv() {
        const resumen = document.getElementById('pdv-filtro-minorista-resumen');
        const btnMapa = document.getElementById('btnFiltrarMinoristaPdv');
        const btnDest = document.getElementById('btnFiltrarDestinoPdv');
        const activo = !!pdvFiltroMinoristaId;
        if (resumen) {
            resumen.classList.toggle('d-none', !activo);
            resumen.textContent = activo
                ? 'Filtro activo: ' + pdvFiltroMinoristaLabel + '. Pulse «Filtrar» otra vez para cambiar de minorista.'
                : '';
        }
        [btnMapa, btnDest].forEach(function (b) {
            if (b) b.classList.toggle('active', activo);
        });
    }

    function aplicarFiltroMinoristaPdv(item) {
        pdvFiltroMinoristaId = item?.id ? String(item.id) : null;
        pdvFiltroMinoristaLabel = item?.label || '';
        if (window.CatalogoSelector) {
            if (item?.id) {
                CatalogoSelector.setValue('pdv_unificado_minorista', item.id, item.label);
            } else {
                CatalogoSelector.clear('pdv_unificado_minorista');
            }
        }
        const cfgPunto = CatalogoSelector.instances?.pdv_unificado_punto;
        if (cfgPunto) {
            cfgPunto.params = pdvFiltroMinoristaId ? { minorista_usuarioid: pdvFiltroMinoristaId } : {};
        }
        actualizarResumenFiltroMinoristaPdv();
        if (statePdv.puntosVisibles) {
            mostrarPuntosEnMapaPdv();
        }
    }

    function abrirFiltroMinoristaPdv() {
        if (!window.CatalogoSelector) return;
        CatalogoSelector.open('pdv_unificado_minorista');
    }

    function limpiarFiltroMinoristaPdv() {
        pdvFiltroMinoristaId = null;
        pdvFiltroMinoristaLabel = '';
        aplicarFiltroMinoristaPdv(null);
    }

    function coordsPdvSeleccionadas() {
        const coords = [];
        if (PDV_MAPA_ES_ADMIN) {
            const origen = itemPdvMapaPorId(valorSelectorPdv('pdv_unificado_almacen'), 'mayorista');
            if (origen) {
                coords.push({ lat: parseFloat(origen.extra.lat), lng: parseFloat(origen.extra.lng) });
            }
        }
        const destino = itemPdvMapaPorId(valorSelectorPdv('pdv_unificado_punto'), 'pdv');
        if (destino) {
            coords.push({ lat: parseFloat(destino.extra.lat), lng: parseFloat(destino.extra.lng) });
        }
        return coords;
    }

    function coordPdvYaSeleccionada(lat, lng) {
        return coordsPdvSeleccionadas().some(function (c) {
            return Math.abs(c.lat - lat) < 0.00001 && Math.abs(c.lng - lng) < 0.00001;
        });
    }

    function flashMapaMsgPdv(texto) {
        const el = document.getElementById('mapa-pdv-asignacion-flash');
        if (!el) return;
        el.textContent = texto;
        el.style.display = 'block';
        clearTimeout(flashMapaMsgPdv._t);
        flashMapaMsgPdv._t = setTimeout(function () { el.style.display = 'none'; }, 2200);
    }

    function iconPuntoMapaPdv(tipo, seleccionado) {
        const esMayorista = tipo === 'mayorista';
        const icon = esMayorista ? 'fa-warehouse' : 'fa-store';
        const clase = esMayorista ? 'almacen-mapa-pin--pdv-mayorista' : 'almacen-mapa-pin--pdv-tienda';
        const sel = seleccionado ? ' is-selected' : '';
        return L.divIcon({
            html: '<div class="almacen-mapa-pin ' + clase + sel + '"><i class="fas ' + icon + '"></i></div>',
            className: 'almacen-mapa-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    function iconMarkerPdv(color, numero) {
        const html = '<div style="background:' + color + ';color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)">' + numero + '</div>';
        return L.divIcon({
            html: html,
            className: 'custom-marker',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });
    }

    function syncEstadoRutaPdv() {
        const origenFijado = tieneOrigenPdv();
        const destinoFijado = tieneDestinoPdv();
        const bloqueOrigen = document.getElementById('bloque-origen-pdv');
        const msg = document.getElementById('pdv-paso-ayuda');

        if (bloqueOrigen && PDV_MAPA_ES_ADMIN) {
            bloqueOrigen.classList.toggle('ruta-bloqueada', destinoFijado);
        }
        actualizarBtnVerOrigenPdv();
        actualizarResumenFiltroMinoristaPdv();

        if (!msg) return;
        if (PDV_MAPA_ES_ADMIN) {
            if (!origenFijado && !destinoFijado) {
                msg.innerHTML = '<strong>Paso a paso:</strong> 1) Elija el almacén mayorista (origen) en el mapa o en el buscador. 2) Luego el punto de venta (destino). Use <strong>Filtrar por Minorista</strong> para ver solo sus tiendas.';
            } else if (!destinoFijado) {
                msg.innerHTML = '<strong>Paso a paso:</strong> Elija el punto de venta (destino). Puede filtrar por minorista en el mapa o al buscar destino.';
            } else if (!origenFijado) {
                msg.innerHTML = '<strong>Paso a paso:</strong> Elija el almacén mayorista (origen) para completar la ruta.';
            } else {
                msg.innerHTML = '<strong>Ruta definida.</strong> Use <strong>Reiniciar ruta</strong> si necesita cambiar origen o destino.';
            }
        } else if (!destinoFijado) {
            msg.innerHTML = '<strong>Paso a paso:</strong> Elija el punto de venta (destino) en el mapa o en el buscador. La ruta se traza automáticamente.';
        } else {
            msg.innerHTML = '<strong>Ruta definida.</strong> Use <strong>Reiniciar ruta</strong> si necesita cambiar el destino.';
        }
    }

    function actualizarBtnVerOrigenPdv() {
        const btn = document.getElementById('btnVerProductosOrigenPdv');
        if (btn) btn.classList.toggle('d-none', !valorSelectorPdv('pdv_unificado_almacen'));
    }

    function actualizarPickerOrigenPdv(item) {
        const display = document.getElementById('txtNombreOrigenPdv');
        const coords = document.getElementById('txtOrigenPdvCoords');
        if (!display) return;
        if (item) {
            display.value = item.label || '';
            display.classList.remove('text-muted');
            if (coords && item.extra?.lat) {
                coords.textContent = 'GPS: ' + parseFloat(item.extra.lat).toFixed(5) + ', ' + parseFloat(item.extra.lng).toFixed(5);
            }
        } else {
            display.value = '';
            display.classList.add('text-muted');
            if (coords) coords.textContent = '';
        }
    }

    function actualizarPickerDestinoPdv(item) {
        const display = document.getElementById('txtNombreDestinoPdv');
        const coords = document.getElementById('txtDestinoPdvCoords');
        if (!display) return;
        if (item) {
            display.value = item.label || '';
            display.classList.remove('text-muted');
            if (coords && item.extra?.lat) {
                coords.textContent = 'GPS: ' + parseFloat(item.extra.lat).toFixed(5) + ', ' + parseFloat(item.extra.lng).toFixed(5);
            }
        } else {
            display.value = '';
            display.classList.add('text-muted');
            if (coords) coords.textContent = '';
        }
    }

    function waypointsPdv() {
        const puntos = [];
        if (PDV_MAPA_ES_ADMIN) {
            const origen = itemPdvMapaPorId(valorSelectorPdv('pdv_unificado_almacen'), 'mayorista');
            if (origen) {
                const lat = parseFloat(origen.extra.lat);
                const lng = parseFloat(origen.extra.lng);
                if (!isNaN(lat) && !isNaN(lng)) {
                    puntos.push({ lat: lat, lng: lng, label: origen.label, tipo: 'origen' });
                }
            }
        }
        const destino = itemPdvMapaPorId(valorSelectorPdv('pdv_unificado_punto'), 'pdv');
        if (destino) {
            const lat = parseFloat(destino.extra.lat);
            const lng = parseFloat(destino.extra.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                puntos.push({ lat: lat, lng: lng, label: destino.label, tipo: 'destino' });
            }
        }
        return puntos;
    }

    function aplicarOrigenPdv(item, opts) {
        opts = opts || {};
        if (!item || !window.CatalogoSelector) return;
        CatalogoSelector.setValue('pdv_unificado_almacen', item.id, item.label);
        actualizarPickerOrigenPdv(item);
        const cfgProd = CatalogoSelector.instances.pdv_unificado_producto;
        if (cfgProd) {
            cfgProd.params = {
                ambito_mayorista: '1',
                solo_con_stock: '1',
                requiere_almacen: '1',
                almacenid: item.id,
            };
        }
        if (!pdvProductoPreseleccionado) {
            CatalogoSelector.clear('pdv_unificado_producto');
        }
        syncEstadoRutaPdv();
        if (opts.abrirInventario !== false && item.id) {
            abrirInventarioOrigenPdv(item);
        }
    }

    function aplicarDestinoPdv(item) {
        if (!item || !window.CatalogoSelector) return;
        CatalogoSelector.setValue('pdv_unificado_punto', item.id, item.label);
        actualizarPickerDestinoPdv(item);
        if (PDV_MAPA_ES_ADMIN && item.extra?.minorista_usuarioid) {
            CatalogoSelector.setValue(
                'pdv_unificado_minorista',
                item.extra.minorista_usuarioid,
                item.extra.minorista_nombre || item.label
            );
        }
        syncEstadoRutaPdv();
    }

    function bloquearOrigenPdvSiDestino() {
        if (tieneDestinoPdv() && PDV_MAPA_ES_ADMIN) {
            aviso('Ya definió el destino. Use «Reiniciar ruta» para volver a editar el origen.');
            return true;
        }
        return false;
    }

    function asignarPuntoDesdeMapaPdv(item) {
        if (item.tipo === 'pdv') {
            if (pdvFiltroMinoristaId && String(item.extra?.minorista_usuarioid) !== String(pdvFiltroMinoristaId)) {
                aviso('Este punto de venta no pertenece al minorista filtrado.');
                return;
            }
            aplicarDestinoPdv(item);
            flashMapaMsgPdv('Destino asignado: ' + item.label);
            resaltarPuntosEnMapaPdv();
            redrawRutaPdv();
            return;
        }
        if (bloquearOrigenPdvSiDestino()) return;
        aplicarOrigenPdv(item);
        flashMapaMsgPdv('Origen asignado: ' + item.label);
        resaltarPuntosEnMapaPdv();
        redrawRutaPdv();
    }

    function pintarPuntosEnMapaPdv(items) {
        statePdv.capasPuntos.clearLayers();
        const bounds = [];
        items.forEach(function (item) {
            const lat = parseFloat(item.extra.lat);
            const lng = parseFloat(item.extra.lng);
            if (isNaN(lat) || isNaN(lng)) return;
            const seleccionado = coordPdvYaSeleccionada(lat, lng);
            const m = L.marker([lat, lng], {
                icon: iconPuntoMapaPdv(item.tipo, seleccionado),
                zIndexOffset: seleccionado ? 200 : 0,
            })
                .bindTooltip(item.label, {
                    className: 'almacen-mapa-tooltip',
                    direction: 'top',
                    offset: [0, -14],
                })
                .addTo(statePdv.capasPuntos);
            m.on('mouseover', function () { this.openTooltip(); });
            m.on('click', function () { asignarPuntoDesdeMapaPdv(item); });
            m._pdvItem = item;
            bounds.push([lat, lng]);
        });
        return bounds;
    }

    function resaltarPuntosEnMapaPdv() {
        if (!statePdv.puntosVisibles || !statePdv.capasPuntos) return;
        statePdv.capasPuntos.eachLayer(function (layer) {
            if (!layer._pdvItem) return;
            const item = layer._pdvItem;
            const lat = parseFloat(item.extra.lat);
            const lng = parseFloat(item.extra.lng);
            const sel = coordPdvYaSeleccionada(lat, lng);
            layer.setIcon(iconPuntoMapaPdv(item.tipo, sel));
            layer.setZIndexOffset(sel ? 200 : 0);
        });
    }

    function ajustarVistaMapaPdv(bounds) {
        if (!bounds.length || !statePdv.map) return;
        const rutaPts = waypointsPdv();
        const puntos = bounds.slice();
        rutaPts.forEach(function (p) { puntos.push([p.lat, p.lng]); });
        try {
            statePdv.map.fitBounds(L.latLngBounds(puntos).pad(0.1));
        } catch (e) {
            statePdv.map.setView([puntos[0][0], puntos[0][1]], 12);
        }
    }

    function mostrarPuntosEnMapaPdv() {
        const items = puntosMapaPdvVisibles();
        if (!items.length) {
            aviso('No hay ubicaciones con coordenadas para mostrar en el mapa.');
            return false;
        }
        const bounds = pintarPuntosEnMapaPdv(items);
        if (!statePdv.map.hasLayer(statePdv.capasPuntos)) {
            statePdv.map.addLayer(statePdv.capasPuntos);
        }
        statePdv.puntosVisibles = true;
        ajustarVistaMapaPdv(bounds);
        return true;
    }

    function togglePuntosEnMapaPdv() {
        const btn = document.getElementById('btnVerPuntosMapaPdv');
        if (!btn || statePdv.cargandoPuntos) return;

        if (statePdv.puntosVisibles) {
            if (statePdv.map.hasLayer(statePdv.capasPuntos)) {
                statePdv.map.removeLayer(statePdv.capasPuntos);
            }
            statePdv.puntosVisibles = false;
            btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
            btn.classList.remove('active');
            return;
        }

        statePdv.cargandoPuntos = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando…';

        window.requestAnimationFrame(function () {
            try {
                if (mostrarPuntosEnMapaPdv()) {
                    btn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i> Ocultar ubicaciones';
                    btn.classList.add('active');
                }
            } catch (e) {
                console.error(e);
                aviso('No se pudieron mostrar las ubicaciones en el mapa.');
                btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
                btn.classList.remove('active');
            } finally {
                statePdv.cargandoPuntos = false;
                btn.disabled = false;
            }
        });
    }

    function limpiarCapaMapaPdv() {
        if (!statePdv.capasRuta) return;
        if (statePdv.routeLayer) {
            statePdv.capasRuta.removeLayer(statePdv.routeLayer);
            statePdv.routeLayer = null;
        }
        statePdv.markers.forEach(function (m) { statePdv.capasRuta.removeLayer(m); });
        statePdv.markers = [];
    }

    function rebuildMarcadoresPdv(puntos) {
        limpiarCapaMapaPdv();
        if (!statePdv.capasRuta) return;
        puntos.forEach(function (p, i) {
            const esDestino = p.tipo === 'destino';
            const color = esDestino ? '#2563eb' : '#ea580c';
            const marker = L.marker([p.lat, p.lng], { icon: iconMarkerPdv(color, i + 1), zIndexOffset: 1000 }).addTo(statePdv.capasRuta);
            marker.bindTooltip(p.label, { className: 'almacen-mapa-tooltip', direction: 'top', offset: [0, -14] });
            statePdv.markers.push(marker);
        });
        resaltarPuntosEnMapaPdv();
    }

    async function redrawRutaPdv() {
        if (!window.RutaPorCalles || !statePdv.capasRuta) return;
        const token = ++redrawPdvToken;
        const puntos = waypointsPdv();
        rebuildMarcadoresPdv(puntos);
        const resumenEl = document.getElementById('rutaResumenPdv');

        if (puntos.length < 2) {
            if (resumenEl) {
                if (puntos.length === 1) {
                    resumenEl.textContent = PDV_MAPA_ES_ADMIN
                        ? 'Agregue el otro punto (origen o destino) para trazar la ruta.'
                        : 'Ruta con un punto. Seleccione origen mayorista si aplica.';
                } else {
                    resumenEl.textContent = '';
                }
            }
            if (window.PedidoFase2) window.PedidoFase2.setRouteDuration(null);
            syncEstadoRutaPdv();
            resetCostoPdv();
            return;
        }

        const routeResult = await RutaPorCalles.fetchRoute(puntos);
        if (token !== redrawPdvToken) return;

        if (routeResult?.geojson) {
            statePdv.routeLayer = L.geoJSON(routeResult.geojson, {
                style: {
                    color: routeResult.straight ? '#fb923c' : '#ea580c',
                    weight: 5,
                    opacity: 0.85,
                    dashArray: routeResult.straight ? '8,8' : null,
                },
            }).addTo(statePdv.capasRuta);

            if (!statePdv.puntosVisibles) {
                const bounds = L.latLngBounds(puntos.map(function (p) { return [p.lat, p.lng]; }));
                try { statePdv.map.fitBounds(bounds.pad(0.15)); } catch (e) {}
            }

            const km = routeResult.distance_m ? Math.round(routeResult.distance_m / 1000) : null;
            const min = routeResult.duration_s ? Math.round(routeResult.duration_s / 60) : null;
            let resumen = routeResult.straight ? 'Ruta estimada (línea recta)' : 'Ruta por calles';
            if (km && min) resumen += ' · ~' + km + ' km · ~' + min + ' min';
            if (resumenEl) resumenEl.textContent = resumen;
        }
        if (window.PedidoFase2) {
            window.PedidoFase2.setRouteDuration(routeResult?.duration_s ?? null);
        }
        syncEstadoRutaPdv();
        actualizarCostoPdv(routeResult?.distance_m ?? null);
    }

    function ejecutarReinicioRutaPdv() {
        if (window.CatalogoSelector) {
            if (PDV_MAPA_ES_ADMIN) {
                CatalogoSelector.clear('pdv_unificado_almacen');
            }
            CatalogoSelector.clear('pdv_unificado_punto');
            CatalogoSelector.clear('pdv_unificado_producto');
        }
        limpiarFiltroMinoristaPdv();
        actualizarPickerOrigenPdv(null);
        actualizarPickerDestinoPdv(null);
        const resumenEl = document.getElementById('rutaResumenPdv');
        if (resumenEl) resumenEl.textContent = '';
        syncEstadoRutaPdv();
        resaltarPuntosEnMapaPdv();
        redrawRutaPdv();
    }

    function reiniciarRutaPdv() {
        if (window.ModalConfirmar && typeof ModalConfirmar.confirmar === 'function') {
            ModalConfirmar.confirmar({
                titulo: 'Reiniciar ruta',
                mensaje: '¿Reiniciar la ruta? Se borrarán origen, destino y el trazo del mapa.',
                tono: 'warning',
                btnText: 'Reiniciar',
            }).then(function (ok) {
                if (ok) ejecutarReinicioRutaPdv();
            });
            return;
        }
        if (confirm('¿Reiniciar la ruta? Se borrarán origen, destino y el trazo del mapa.')) {
            ejecutarReinicioRutaPdv();
        }
    }

    function mapaPdvContainerListo() {
        const el = document.getElementById('mapaPdvDistribucion');
        if (!el) return false;
        const flujo = document.getElementById('flujo-punto-venta');
        if (flujo?.classList.contains('d-none')) return false;
        const pasoRuta = flujo?.querySelector('.wizard-step[data-wizard-step="1"]');
        if (pasoRuta && !pasoRuta.classList.contains('active')) return false;
        const rect = el.getBoundingClientRect();
        return rect.width > 20 && rect.height > 20;
    }

    function refrescarTamanoMapaPdv() {
        if (!statePdv.map) return;
        statePdv.map.invalidateSize();
        redrawRutaPdv();
        if (statePdv.puntosVisibles) {
            mostrarPuntosEnMapaPdv();
        }
    }

    function initMapaPdv() {
        const el = document.getElementById('mapaPdvDistribucion');
        if (!el || !window.L || mapaPdvListo) return false;
        if (!mapaPdvContainerListo()) return false;

        statePdv.map = L.map(el, { scrollWheelZoom: true }).setView([hub.lat, hub.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap',
        }).addTo(statePdv.map);
        statePdv.capasPuntos = L.layerGroup();
        statePdv.capasRuta = L.layerGroup().addTo(statePdv.map);

        document.getElementById('btnVerPuntosMapaPdv')?.addEventListener('click', togglePuntosEnMapaPdv);
        document.getElementById('btnReiniciarRutaPdv')?.addEventListener('click', reiniciarRutaPdv);

        mapaPdvListo = true;
        syncEstadoRutaPdv();

        setTimeout(function () {
            if (!statePdv.map) return;
            statePdv.map.invalidateSize();
            redrawRutaPdv();
            if (PUNTOS_MAPA_PDV.length) {
                togglePuntosEnMapaPdv();
            }
        }, 250);

        return true;
    }

    function ensureMapaPdv() {
        if (!document.getElementById('mapaPdvDistribucion')) return;

        function intentarInit() {
            if (!window.L) return false;
            if (mapaPdvListo) {
                refrescarTamanoMapaPdv();
                return true;
            }
            return initMapaPdv() === true;
        }

        if (intentarInit()) {
            setTimeout(refrescarTamanoMapaPdv, 80);
            setTimeout(refrescarTamanoMapaPdv, 350);
            setTimeout(refrescarTamanoMapaPdv, 800);
            return;
        }

        let intentos = 0;
        const reintentar = setInterval(function () {
            intentos += 1;
            if (intentarInit() || intentos >= 80) {
                clearInterval(reintentar);
                if (mapaPdvListo) {
                    setTimeout(refrescarTamanoMapaPdv, 80);
                    setTimeout(refrescarTamanoMapaPdv, 350);
                    setTimeout(refrescarTamanoMapaPdv, 800);
                }
            }
        }, 100);
    }

    window.ensureMapaPdv = ensureMapaPdv;
    window.refrescarTamanoMapaPdv = refrescarTamanoMapaPdv;

    function iniciarMapasSegunTrayecto() {
        if (!window.L) return;
        if (destinoInicial === 'mayorista') {
            ensureMapaTraslado();
        } else if (destinoInicial === 'punto-venta') {
            ensureMapaPdv();
        } else {
            initMapa();
        }
    }

    document.addEventListener('DOMContentLoaded', iniciarMapasSegunTrayecto);
    window.addEventListener('load', function () {
        if (destinoInicial === 'mayorista') {
            ensureMapaTraslado();
        } else if (destinoInicial === 'punto-venta') {
            ensureMapaPdv();
        } else if (state.map) {
            state.map.invalidateSize();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;

        CatalogoSelector.register('pedido_almacen_origen', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén agrícola — recogida 1',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'agricola' },
            rowAction: { label: 'Ver' },
            onRowAction: function (item) {
                if (bloquearRecogidaSiDestino()) return;
                abrirProductosDelAlmacen(item, 'principal');
            },
            onSelect: function (item) {
                if (bloquearRecogidaSiDestino()) return;
                aplicarRecogidaPrincipal(item);
            },
        });
        CatalogoSelector.register('pedido_almacen_recogida_extra', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén agrícola — recogida adicional',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'agricola' },
            rowAction: { label: 'Ver' },
            onRowAction: function (item) {
                if (!pickerRecogidaActivo) return;
                abrirProductosDelAlmacen(item, pickerRecogidaActivo);
            },
            onSelect: function (item) {
                if (!pickerRecogidaActivo) return;
                aplicarRecogidaExtra(pickerRecogidaActivo, item);
            },
        });

        CatalogoSelector.register('pedido_almacen_destino', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén de planta — destino',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'planta' },
            onSelect: function (item) { aplicarDestino(item); },
        });

        document.getElementById('btnBuscarOrigen')?.addEventListener('click', function () {
            if (bloquearRecogidaSiDestino()) return;
            CatalogoSelector.open('pedido_almacen_origen');
        });
        document.getElementById('btnVerProductosOrigen').addEventListener('click', function () {
            const almacenId = document.getElementById('origen_almacenid')?.value;
            if (!almacenId) return;
            abrirProductosDelAlmacen({
                id: almacenId,
                label: document.getElementById('txtNombreOrigen')?.value || document.getElementById('origen_direccion')?.value || '',
                extra: {
                    lat: document.getElementById('origen_latitud')?.value,
                    lng: document.getElementById('origen_longitud')?.value,
                    direccion: document.getElementById('origen_direccion')?.value,
                },
            }, 'principal');
        });

        actualizarBtnVerOrigen();
        document.getElementById('btnBuscarDestino')?.addEventListener('click', function () {
            if (!puedeElegirDestino()) {
                aviso('Complete todas las recogidas antes de elegir destino.');
                return;
            }
            CatalogoSelector.open('pedido_almacen_destino');
        });

        const btnAgregarRecogida = document.getElementById('btnAgregarRecogida');
        if (btnAgregarRecogida) {
            btnAgregarRecogida.addEventListener('click', function () {
                if (bloquearRecogidaSiDestino()) return;
                if (!recogida1Lista()) {
                    aviso('Primero elija la recogida 1.');
                    return;
                }
                if (hayRecogidasExtraVacias()) {
                    aviso('Complete la recogida adicional pendiente antes de agregar otra.');
                    return;
                }
                const actuales = document.querySelectorAll('.origen-extra-row').length;
                if (actuales >= MAX_RECOGIDAS_EXTRA) {
                    aviso('Máximo ' + MAX_RECOGIDAS_EXTRA + ' almacenes de recogida adicionales.');
                    return;
                }
                const container = document.getElementById('recogidas-extra-container');
                container.appendChild(crearFilaRecogidaExtra());
                renumerarRecogidas();
                syncEstadoRuta();
            });
        }

        syncEstadoRuta();

        ['traslado_planta_origen', 'traslado_mayorista_destino'].forEach(function (selId) {
            const wrap = document.getElementById('selector_wrap_' + selId);
            if (!wrap) return;
            wrap.addEventListener('selector-catalogo:change', function (e) {
                const id = e.detail?.id;
                const item = id ? itemTrasladoPorId(id) : null;
                if (selId === 'traslado_planta_origen') {
                    actualizarPickerOrigenTraslado(item || (id ? { id: id, label: e.detail?.label || '', extra: {} } : null));
                } else {
                    actualizarPickerDestinoTraslado(item || (id ? { id: id, label: e.detail?.label || '', extra: {} } : null));
                }
                syncEstadoRutaTraslado();
                syncBloqueProductosTraslado();
                if (selId === 'traslado_planta_origen') {
                    recargarPresentacionesFilasTraslado();
                }
                if (mapaTrasladoListo) {
                    redrawRutaTraslado();
                }
            });
        });

        document.getElementById('btnBuscarOrigenTraslado')?.addEventListener('click', function () {
            if (bloquearOrigenTrasladoSiDestino()) return;
            CatalogoSelector.open('traslado_planta_origen');
        });
        document.getElementById('btnBuscarDestinoTraslado')?.addEventListener('click', function () {
            if (!tieneOrigenTraslado()) {
                aviso('Primero elija el almacén de planta (origen).');
                return;
            }
            CatalogoSelector.open('traslado_mayorista_destino');
        });
        document.getElementById('btnLimpiarOrigenTraslado')?.addEventListener('click', function () {
            if (bloquearOrigenTrasladoSiDestino()) return;
            if (window.CatalogoSelector) CatalogoSelector.clear('traslado_planta_origen');
            actualizarPickerOrigenTraslado(null);
            limpiarPreseleccionProductosTraslado();
            limpiarProductosTraslado();
            syncEstadoRutaTraslado();
            redrawRutaTraslado();
        });
        document.getElementById('btnLimpiarDestinoTraslado')?.addEventListener('click', function () {
            if (window.CatalogoSelector) CatalogoSelector.clear('traslado_mayorista_destino');
            actualizarPickerDestinoTraslado(null);
            syncEstadoRutaTraslado();
            redrawRutaTraslado();
        });
        document.getElementById('btnVerProductosOrigenTraslado')?.addEventListener('click', function () {
            const almacenId = valorSelectorTraslado('traslado_planta_origen');
            if (!almacenId) return;
            const item = itemTrasladoPorId(almacenId);
            if (item) abrirInventarioOrigenTraslado(item);
        });

        const wrapOrigenTraslado = document.getElementById('selector_wrap_traslado_planta_origen');
        if (wrapOrigenTraslado) {
            wrapOrigenTraslado.addEventListener('selector-catalogo:change', function (e) {
                const nuevo = String(e.detail?.id || '');
                if (ultimoOrigenTrasladoId && nuevo !== ultimoOrigenTrasladoId) {
                    limpiarPreseleccionProductosTraslado();
                    limpiarProductosTraslado();
                }
                ultimoOrigenTrasladoId = nuevo;
            });
        }

        registrarVerInventarioEnSelector('traslado_planta_origen', {
            beforeOpen: function () { return bloquearOrigenTrasladoSiDestino(); },
            onSelect: function (item) {
                if (bloquearOrigenTrasladoSiDestino()) return;
                aplicarOrigenTraslado(item);
            },
            inventario: inventarioOrigenTrasladoConfig(),
        });

        window.EnvioTrasladoProductos = {
            syncAlPaso2: function () {
                sincronizarFilasTrasladoDesdePreseleccion();
                recargarPresentacionesFilasTraslado();
            },
        };

        syncEstadoRutaTraslado();
        syncBloqueProductosTraslado();

        @if(is_array(old('detalles')))
        (function () {
            const oldDetalles = @json(old('detalles'));
            oldDetalles.forEach(function (d) {
                agregarFilaProductoTraslado(d);
            });
        })();
        @endif

        const txtTransportista = document.getElementById('txtTransportistaCreate');
        if (txtTransportista) {
            const txtVehiculo = document.getElementById('txtVehiculoCreate');
            const inputTransportista = document.getElementById('transportista_usuarioid_create');
            const inputVehiculo = document.getElementById('vehiculoid_create');
            const btnBuscarVehiculo = document.getElementById('btnBuscarVehiculoCreate');

            function syncVehiculoBtn() {
                if (btnBuscarVehiculo) btnBuscarVehiculo.disabled = !inputTransportista.value;
            }

            function limpiarVehiculo() {
                inputVehiculo.value = '';
                txtVehiculo.value = '';
                txtVehiculo.classList.add('text-muted');
                txtVehiculo.placeholder = inputTransportista.value ? 'Buscar vehículo…' : 'Primero elija transportista…';
            }

            CatalogoSelector.register('create_envio_transportista', {
                endpoint: @json(route('catalogo-selector.usuarios')),
                title: 'Buscar chofer',
                searchPlaceholder: 'Nombre, usuario o correo…',
                modalIcon: 'fa-user-tie',
                rowIcon: 'fa-user-tie',
                theme: 'chofer',
                colNombre: 'Chofer',
                params: {
                    roles: 'transportista',
                    ambito_flota: (function () {
                        const f = window.EnvioWizard?.flujoActivo?.();
                        if (f === 'mayorista') return 'planta';
                        if (f === 'punto-venta') return 'mayorista';
                        return 'agricola';
                    })(),
                },
                onSelect(item) {
                    inputTransportista.value = item.id;
                    txtTransportista.value = item.label;
                    txtTransportista.classList.remove('text-muted');
                    limpiarVehiculo();
                    syncVehiculoBtn();
                    if (window.EnvioWizard?.actualizarParamsVehiculoCatalogo) {
                        window.EnvioWizard.actualizarParamsVehiculoCatalogo();
                    } else {
                        CatalogoSelector.instances.create_envio_vehiculo.params = {
                            transportista_usuarioid: item.id,
                            solo_transportista: '1',
                        };
                    }
                    if (window.EnvioWizard) window.EnvioWizard.syncTarjetasAsignacion();
                    if (window.PedidoFase2) window.PedidoFase2.actualizarSugerenciaVehiculo();
                },
            });

            function iconoVehiculoFila(item) {
                const codigos = item.extra?.tipos_transporte || [];
                if (codigos.includes('REFRIGERADO')) return 'fa-snowflake';
                if (codigos.includes('MULTITEMPERATURA')) return 'fa-layer-group';
                if (codigos.includes('ISOTERMICO')) return 'fa-thermometer-half';
                return 'fa-truck';
            }

            CatalogoSelector.register('create_envio_vehiculo', {
                endpoint: @json(route('catalogo-selector.vehiculos')),
                title: 'Buscar vehículo',
                searchPlaceholder: 'Placa, marca, modelo…',
                modalIcon: 'fa-truck',
                rowIcon: 'fa-truck',
                rowIconFn: iconoVehiculoFila,
                theme: 'vehiculo',
                colNombre: 'Placa',
                params: (function () {
                    const f = window.EnvioWizard?.flujoActivo?.();
                    if (f === 'mayorista') return { ambito_flota: 'planta' };
                    if (f === 'punto-venta') return { ambito_flota: 'mayorista' };
                    return {
                        transportista_usuarioid: inputTransportista.value || '',
                        solo_transportista: '1',
                    };
                })(),
                beforeOpen(cfg) {
                    if (window.EnvioWizard?.actualizarParamsVehiculoCatalogo) {
                        window.EnvioWizard.actualizarParamsVehiculoCatalogo();
                    }
                    cfg.suggestedItemId = window.PedidoFase2?.sugerenciaVehiculoId || null;
                },
                onSelect(item) {
                    inputVehiculo.value = item.id;
                    txtVehiculo.value = item.label;
                    txtVehiculo.classList.remove('text-muted');
                    if (window.PedidoFase2) {
                        window.PedidoFase2.validarCapacidadVehiculo();
                        window.PedidoFase2.actualizarSugerenciaVehiculo();
                    }
                    if (window.EnvioWizard) window.EnvioWizard.syncTarjetasAsignacion();
                },
            });

            document.getElementById('btnBuscarTransportistaCreate')?.addEventListener('click', function () {
                CatalogoSelector.open('create_envio_transportista');
            });
            document.getElementById('btnBuscarVehiculoCreate')?.addEventListener('click', function () {
                if (!inputTransportista.value) return;
                CatalogoSelector.open('create_envio_vehiculo');
            });
            syncVehiculoBtn();
        }

        registrarEnvioPlantaSubmit();

        (function initPdvUnificado() {
            const form = document.getElementById('form-pedido-dist-pdv');
            if (!form || !window.CatalogoSelector) return;

            function limpiarSelector(id) {
                if (CatalogoSelector.instances[id]) {
                    CatalogoSelector.clear(id);
                }
                if (id === 'pdv_unificado_producto') {
                    window.PdvEnvioCarga?.limpiar();
                }
            }

            const wrapMinorista = document.getElementById('selector_wrap_pdv_unificado_minorista');
            if (wrapMinorista && CatalogoSelector.instances.pdv_unificado_minorista) {
                CatalogoSelector.register('pdv_unificado_minorista', Object.assign({}, CatalogoSelector.instances.pdv_unificado_minorista, {
                    onSelect: function (item) {
                        aplicarFiltroMinoristaPdv(item);
                    },
                }));
            }

            document.getElementById('selector_wrap_pdv_unificado_almacen')?.addEventListener('selector-catalogo:change', function (e) {
                const aid = e.detail?.id;
                const nuevo = String(aid || '');
                const item = aid ? itemPdvMapaPorId(aid, 'mayorista') : null;
                actualizarPickerOrigenPdv(item || (aid ? { id: aid, label: e.detail?.label || '', extra: {} } : null));
                if (ultimoAlmacenPdvId && nuevo !== ultimoAlmacenPdvId) {
                    limpiarPreseleccionProductoPdv();
                    limpiarSelector('pdv_unificado_producto');
                }
                ultimoAlmacenPdvId = nuevo;
                const cfgProd = CatalogoSelector.instances.pdv_unificado_producto;
                if (cfgProd) {
                    cfgProd.params = {
                        ambito_mayorista: '1',
                        solo_con_stock: '1',
                        requiere_almacen: '1',
                        almacenid: aid || null,
                    };
                }
                syncEstadoRutaPdv();
                if (mapaPdvListo) {
                    resaltarPuntosEnMapaPdv();
                    redrawRutaPdv();
                }
            });

            document.getElementById('selector_wrap_pdv_unificado_punto')?.addEventListener('selector-catalogo:change', function (e) {
                const id = e.detail?.id;
                const item = id ? itemPdvMapaPorId(id, 'pdv') : null;
                actualizarPickerDestinoPdv(item || (id ? { id: id, label: e.detail?.label || '', extra: {} } : null));
                syncEstadoRutaPdv();
                if (mapaPdvListo) {
                    resaltarPuntosEnMapaPdv();
                    redrawRutaPdv();
                }
            });

            document.getElementById('btnFiltrarMinoristaPdv')?.addEventListener('click', abrirFiltroMinoristaPdv);
            document.getElementById('btnFiltrarDestinoPdv')?.addEventListener('click', abrirFiltroMinoristaPdv);
            document.getElementById('btnBuscarOrigenPdv')?.addEventListener('click', function () {
                if (bloquearOrigenPdvSiDestino()) return;
                CatalogoSelector.open('pdv_unificado_almacen');
            });
            document.getElementById('btnBuscarDestinoPdv')?.addEventListener('click', function () {
                CatalogoSelector.open('pdv_unificado_punto');
            });
            document.getElementById('btnLimpiarOrigenPdv')?.addEventListener('click', function () {
                if (bloquearOrigenPdvSiDestino()) return;
                limpiarSelector('pdv_unificado_almacen');
                actualizarPickerOrigenPdv(null);
                limpiarPreseleccionProductoPdv();
                limpiarSelector('pdv_unificado_producto');
                syncEstadoRutaPdv();
                redrawRutaPdv();
            });
            document.getElementById('btnLimpiarDestinoPdv')?.addEventListener('click', function () {
                limpiarSelector('pdv_unificado_punto');
                actualizarPickerDestinoPdv(null);
                syncEstadoRutaPdv();
                redrawRutaPdv();
            });
            document.getElementById('btnVerProductosOrigenPdv')?.addEventListener('click', function () {
                const almacenId = valorSelectorPdv('pdv_unificado_almacen');
                if (!almacenId) return;
                const item = itemPdvMapaPorId(almacenId, 'mayorista');
                if (item) abrirInventarioOrigenPdv(item);
            });

            registrarVerInventarioEnSelector('pdv_unificado_almacen', {
                beforeOpen: function () { return bloquearOrigenPdvSiDestino(); },
                onSelect: function (item) {
                    if (bloquearOrigenPdvSiDestino()) return;
                    aplicarOrigenPdv(item);
                },
                inventario: inventarioOrigenPdvConfig(),
            });

            document.getElementById('costo_bs')?.addEventListener('input', function () {
                if (document.getElementById('flujo-punto-venta') && !document.getElementById('flujo-punto-venta').classList.contains('d-none')) {
                    costoPdvEditadoManual = true;
                }
            });

            if (window.PdvEnvioCarga) {
                const syncPdvPaso2Base = window.PdvEnvioCarga.syncAlPaso2;
                window.PdvEnvioCarga.syncAlPaso2 = function () {
                    if (typeof syncPdvPaso2Base === 'function') syncPdvPaso2Base();
                    if (pdvProductoPreseleccionado && document.getElementById('selector_wrap_pdv_unificado_producto')) {
                        CatalogoSelector.setValue(
                            'pdv_unificado_producto',
                            pdvProductoPreseleccionado.id,
                            pdvProductoPreseleccionado.label
                        );
                    }
                };
                window.EnvioPdvProductos = window.PdvEnvioCarga;
            }

            syncEstadoRutaPdv();
        })();

        window.EnvioPlantaDraft = {
            collect() {
                const recogidasExtra = [];
                document.querySelectorAll('.origen-extra-row').forEach(function (row) {
                    recogidasExtra.push({
                        uid: row.getAttribute('data-recogida-uid'),
                        latitud: row.querySelector('[data-field="latitud"]')?.value || '',
                        longitud: row.querySelector('[data-field="longitud"]')?.value || '',
                        direccion: row.querySelector('[data-field="direccion"]')?.value || '',
                        almacenid: row.querySelector('[data-field="almacenid"]')?.value || '',
                        label: row.querySelector('.txt-recogida-extra')?.value || '',
                    });
                });
                const productos = [];
                document.querySelectorAll('.producto-recogida-row').forEach(function (fila) {
                    productos.push({
                        key: fila.getAttribute('data-recogida-key'),
                        producto_ref: fila.querySelector('.selector-catalogo-value')?.value || '',
                        producto_label: fila.querySelector('.selector-catalogo-label')?.value || '',
                        cantidad: fila.querySelector('[data-field="cantidad"]')?.value || '',
                        observaciones: fila.querySelector('[data-field="observaciones"]')?.value || '',
                        forma: fila.querySelector('.js-forma-pedido')?.value || 'kg',
                        tipo_empaque: fila.querySelector('.js-tipo-empaque')?.value || '',
                        calibre: fila.querySelector('.js-calibre')?.value || '',
                        cantidad_pedido: fila.querySelector('.js-cantidad-pedido')?.value || '',
                    });
                });
                return {
                    origen: {
                        latitud: document.getElementById('origen_latitud')?.value || '',
                        longitud: document.getElementById('origen_longitud')?.value || '',
                        direccion: document.getElementById('origen_direccion')?.value || '',
                        almacenid: document.getElementById('origen_almacenid')?.value || '',
                        label: document.getElementById('txtNombreOrigen')?.value || '',
                    },
                    destino: {
                        latitud: document.getElementById('latitud')?.value || '',
                        longitud: document.getElementById('longitud')?.value || '',
                        direccion: document.getElementById('direccion_texto')?.value || '',
                        label: document.getElementById('txtNombreDestino')?.value || '',
                    },
                    recogidasExtra: recogidasExtra,
                    productos: productos,
                    transportista_id: document.getElementById('transportista_usuarioid_create')?.value || '',
                    transportista_label: document.getElementById('txtTransportistaCreate')?.value || '',
                    vehiculo_id: document.getElementById('vehiculoid_create')?.value || '',
                    vehiculo_label: document.getElementById('txtVehiculoCreate')?.value || '',
                    costo_bs: document.getElementById('costo_bs')?.value || '',
                    fechaEntregaDeseada: document.querySelector('#form-pedido [name="fechaEntregaDeseada"]')?.value || '',
                    hora_recogida: document.getElementById('hora_recogida')?.value || '',
                    hora_entrega_estimada: document.getElementById('hora_entrega_estimada')?.value || '',
                    observaciones: document.querySelector('#form-pedido [name="observaciones"]')?.value || '',
                    rutaResumen: document.getElementById('rutaResumen')?.textContent || '',
                };
            },
            restore(data) {
                if (!data) return;
                const setVal = function (id, v) {
                    const e = document.getElementById(id);
                    if (e && v != null && v !== '') e.value = v;
                };
                setVal('origen_latitud', data.origen?.latitud);
                setVal('origen_longitud', data.origen?.longitud);
                setVal('origen_direccion', data.origen?.direccion);
                setVal('origen_almacenid', data.origen?.almacenid);
                const txtOrigen = document.getElementById('txtNombreOrigen');
                if (txtOrigen && data.origen?.label) {
                    txtOrigen.value = data.origen.label;
                    txtOrigen.classList.remove('text-muted');
                }
                const extraWrap = document.getElementById('recogidas-extra-container');
                if (extraWrap) {
                    extraWrap.innerHTML = '';
                    (data.recogidasExtra || []).forEach(function (r) {
                        const row = crearFilaRecogidaExtra({
                            latitud: r.latitud,
                            longitud: r.longitud,
                            direccion: r.direccion,
                            almacenid: r.almacenid,
                        });
                        if (r.uid) row.setAttribute('data-recogida-uid', r.uid);
                        if (r.label) {
                            row.querySelector('.txt-recogida-extra').value = r.label;
                            row.querySelector('.txt-recogida-extra').classList.remove('text-muted');
                        }
                        extraWrap.appendChild(row);
                    });
                }
                renumerarRecogidas();
                setVal('latitud', data.destino?.latitud);
                setVal('longitud', data.destino?.longitud);
                setVal('direccion_texto', data.destino?.direccion);
                const txtDestino = document.getElementById('txtNombreDestino');
                if (txtDestino && data.destino?.label) {
                    txtDestino.value = data.destino.label;
                    txtDestino.classList.remove('text-muted');
                }
                syncEstadoRuta();
                syncFilasProducto();
                window.setTimeout(function () {
                    (data.productos || []).forEach(function (p) {
                        const fila = document.querySelector('.producto-recogida-row[data-recogida-key="' + p.key + '"]');
                        if (!fila) return;
                        const hidden = fila.querySelector('.selector-catalogo-value');
                        const label = fila.querySelector('.selector-catalogo-label');
                        if (hidden && p.producto_ref) hidden.value = p.producto_ref;
                        if (label && p.producto_label) {
                            label.value = p.producto_label;
                            label.classList.remove('is-empty');
                        }
                        fila.dispatchEvent(new CustomEvent('producto-seleccionado', { bubbles: true }));
                        window.setTimeout(function () {
                            const forma = fila.querySelector('.js-forma-pedido');
                            if (forma && p.forma) {
                                forma.value = p.forma;
                                forma.dispatchEvent(new Event('change'));
                            }
                            window.setTimeout(function () {
                                const emp = fila.querySelector('.js-tipo-empaque');
                                if (emp && p.tipo_empaque) {
                                    emp.value = p.tipo_empaque;
                                    emp.dispatchEvent(new Event('change'));
                                }
                                const cal = fila.querySelector('.js-calibre');
                                if (cal && p.calibre) {
                                    cal.value = p.calibre;
                                    cal.dispatchEvent(new Event('change'));
                                }
                                const cp = fila.querySelector('.js-cantidad-pedido');
                                if (cp && p.cantidad_pedido) {
                                    cp.value = p.cantidad_pedido;
                                    cp.dispatchEvent(new Event('input'));
                                }
                                const cant = fila.querySelector('[data-field="cantidad"]');
                                if (cant && p.cantidad) cant.value = p.cantidad;
                                const obs = fila.querySelector('[data-field="observaciones"]');
                                if (obs && p.observaciones) obs.value = p.observaciones;
                            }, 100);
                        }, 100);
                    });
                    setVal('transportista_usuarioid_create', data.transportista_id);
                    setVal('txtTransportistaCreate', data.transportista_label);
                    setVal('vehiculoid_create', data.vehiculo_id);
                    setVal('txtVehiculoCreate', data.vehiculo_label);
                    setVal('costo_bs', data.costo_bs);
                    setVal('hora_recogida', data.hora_recogida);
                    setVal('hora_entrega_estimada', data.hora_entrega_estimada);
                    const fecha = document.querySelector('#form-pedido [name="fechaEntregaDeseada"]');
                    if (fecha && data.fechaEntregaDeseada) fecha.value = data.fechaEntregaDeseada;
                    const obsGen = document.querySelector('#form-pedido [name="observaciones"]');
                    if (obsGen && data.observaciones) obsGen.value = data.observaciones;
                    if (data.rutaResumen) {
                        const ruta = document.getElementById('rutaResumen');
                        if (ruta) ruta.textContent = data.rutaResumen;
                    }
                    window.EnvioWizard?.syncTarjetasAsignacion?.();
                    redrawRoute();
                    window.PedidoFase2?.validarCapacidadVehiculo?.();
                    window.PedidoFase2?.actualizarSugerenciaVehiculo?.();
                }, 80);
            },
        };
        window.EnvioWizard?.limpiarBorrador?.();
    });
})();
</script>
@endpush
