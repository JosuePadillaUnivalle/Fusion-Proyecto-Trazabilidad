@extends('layouts.app')

@section('title', 'Nuevo pedido de distribución')
@section('page_title', 'Nuevo pedido de distribución')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('punto_venta.partials.modulo-styles')
<style>
.pedido-dist-page .pdv-hero {
    background: #166534;
    border-radius: 14px 14px 0 0;
    padding: .85rem 1.15rem;
    color: #fff;
}
.pedido-dist-page .pdv-hero-kicker {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    opacity: .85;
    margin-bottom: .25rem;
}
.pedido-dist-page .pdv-hero-title {
    font-size: 1.15rem;
    font-weight: 800;
    margin: 0;
    line-height: 1.25;
}
.pedido-dist-page .pdv-hero-code {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    margin-top: .35rem;
    padding: .2rem .6rem;
    background: rgba(255,255,255,.16);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 999px;
    font-size: .82rem;
    font-weight: 600;
}
.pedido-dist-page .pdv-intro {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: .5rem .75rem;
    color: #166534;
    font-size: .82rem;
    line-height: 1.4;
}
.pedido-dist-page .pdv-form-block {
    background: #fff;
    border: 1px solid #e8f0ea;
    border-radius: 12px;
    padding: .65rem .85rem;
    margin-bottom: .55rem;
}
.pedido-dist-page .pdv-form-block-head {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .5rem;
    padding-bottom: .4rem;
    border-bottom: 1px solid #f1f5f9;
}
.pedido-dist-page .pdv-form-block-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    flex-shrink: 0;
}
.pedido-dist-page .pdv-form-block-icon--minorista { background: #e0e7ff; color: #4338ca; }
.pedido-dist-page .pdv-form-block-icon--destino { background: #dbeafe; color: #1d4ed8; }
.pedido-dist-page .pdv-minorista-pick {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .75rem 1rem;
    background: #fff;
    margin-bottom: .75rem;
    width: fit-content;
    max-width: 100%;
}
.pedido-dist-page .pdv-minorista-pick.is-selected {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.pedido-dist-page .pdv-destino-pick.is-locked {
    opacity: .55;
    pointer-events: none;
}
.pedido-dist-page .pdv-producto-pick.is-locked {
    opacity: .55;
    pointer-events: none;
}
.pedido-dist-page .pdv-producto-pick.is-locked .selector-filtros-field__open {
    pointer-events: none;
    cursor: not-allowed;
}
.pedido-dist-page .pdv-form-block-icon--producto { background: #fef3c7; color: #b45309; }
.pedido-dist-page .pdv-form-block-icon--datos { background: #f3e8ff; color: #7c3aed; }
.pedido-dist-page .pdv-form-block-title {
    font-size: .88rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    line-height: 1.2;
}
.pedido-dist-page .pdv-form-block-sub {
    font-size: .72rem;
    color: #64748b;
    margin: .1rem 0 0;
}
.pedido-dist-page .pdv-destino-pick {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: .85rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    padding: .85rem 1rem;
}
.pedido-dist-page .pdv-destino-pick.is-empty {
    background: #f8fafc;
    border-style: dashed;
}
.pedido-dist-page .pdv-destino-pick.is-selected {
    border-color: #86efac;
    background: #f0fdf4;
}
.pedido-dist-page .pdv-destino-pick-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #e2e8f0;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}
.pedido-dist-page .pdv-destino-pick.is-selected .pdv-destino-pick-icon {
    background: #dcfce7;
    color: #166534;
}
.pedido-dist-page .pdv-destino-pick-body {
    flex: 1;
    min-width: 0;
}
.pedido-dist-page .pdv-destino-kicker {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .66rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: .2rem;
}
.pedido-dist-page .pdv-destino-pick.is-selected .pdv-destino-kicker {
    color: #166534;
}
.pedido-dist-page .pdv-destino-nombre {
    display: block;
    font-weight: 800;
    font-size: 1rem;
    color: #0f172a;
    line-height: 1.25;
}
.pedido-dist-page .pdv-destino-nombre.is-placeholder {
    color: #94a3b8;
    font-weight: 500;
    font-size: .92rem;
}
.pedido-dist-page .pdv-destino-ubicacion {
    display: flex;
    align-items: flex-start;
    gap: .35rem;
    margin-top: .35rem;
    font-size: .78rem;
    color: #475569;
    line-height: 1.35;
}
.pedido-dist-page .pdv-destino-ubicacion i {
    color: #16a34a;
    margin-top: .15rem;
    flex-shrink: 0;
}
.pedido-dist-page .pdv-destino-ubicacion.is-empty {
    color: #94a3b8;
    font-style: italic;
}
.pedido-dist-page .pdv-destino-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .45rem;
    margin-top: .45rem;
}
.pedido-dist-page .pdv-destino-badge {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    padding: .15rem .5rem;
    border-radius: 999px;
    background: #dcfce7;
    color: #166534;
    font-size: .68rem;
    font-weight: 700;
}
.pedido-dist-page .pdv-destino-map-link {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .2rem .55rem;
    border: 1px solid #bbf7d0;
    border-radius: 999px;
    background: #fff;
    color: #166534;
    font-size: .72rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s ease, border-color .15s ease;
}
.pedido-dist-page .pdv-destino-map-link:hover {
    background: #f0fdf4;
    border-color: #86efac;
}
.pedido-dist-page .pdv-destino-map-link:disabled {
    color: #94a3b8;
    border-color: #e2e8f0;
    background: #f8fafc;
    cursor: not-allowed;
}
.pedido-dist-page .pdv-destino-actions {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: .35rem;
    flex-shrink: 0;
}
.pedido-dist-page .pdv-destino-btn {
    border-radius: 10px;
    font-size: .78rem;
    font-weight: 700;
    padding: .45rem .85rem;
    white-space: nowrap;
}
.pedido-dist-page .pdv-destino-btn--ghost {
    color: #64748b;
    border-color: #cbd5e1;
}
#modalMapaPdvDestino .pdv-mapa-wrap {
    height: 340px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
#modalMapaPdvDestino .pdv-mapa-lista {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .75rem;
    max-height: 110px;
    overflow-y: auto;
}
#modalMapaPdvDestino .pdv-mapa-chip {
    border: 1px solid #cbd5e1;
    background: #fff;
    border-radius: 999px;
    padding: .25rem .65rem;
    font-size: .75rem;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
}
#modalMapaPdvDestino .pdv-mapa-chip:hover,
#modalMapaPdvDestino .pdv-mapa-chip.is-active {
    border-color: #22c55e;
    background: #f0fdf4;
    color: #166534;
}
.pedido-dist-mapa-pin {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .72rem;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
}
.pedido-dist-mapa-pin.is-selected { box-shadow: 0 0 0 3px rgba(34,197,94,.45); }
.pedido-dist-page .pdv-field-label {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: .3rem;
}
.pedido-dist-page .pdv-fecha-hint {
    font-size: .72rem;
    color: #b45309;
    margin-top: .2rem;
    line-height: 1.35;
}
.pedido-dist-page .pdv-form-block .form-group {
    margin-bottom: 0;
}
.pedido-dist-page .pdv-selector-compact {
    width: fit-content;
    max-width: 100%;
}
.pedido-dist-page .pdv-selector-compact .selector-catalogo-wrapper,
.pedido-dist-page .pdv-producto-col .selector-catalogo-wrapper {
    flex-grow: 0 !important;
    width: 18rem !important;
    max-width: 100% !important;
}
.pedido-dist-page .pdv-producto-col .selector-catalogo-wrapper {
    width: 22rem !important;
}
.pedido-dist-page .pdv-selector-compact .selector-filtros-field,
.pedido-dist-page .pdv-producto-col .selector-filtros-field {
    width: 100%;
    max-width: 100%;
}
.pedido-dist-page .pdv-selector-compact .selector-filtros-field__input,
.pedido-dist-page .pdv-producto-col .selector-filtros-field__input {
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}
.pedido-dist-page .pdv-producto-cantidad-row {
    display: grid;
    grid-template-columns: 22rem 13rem;
    column-gap: 1.25rem;
    row-gap: 0;
    align-items: start;
}
@media (max-width: 575.98px) {
    .pedido-dist-page .pdv-producto-cantidad-row {
        grid-template-columns: 1fr;
    }
}
.pedido-dist-page .pdv-producto-cantidad-row .pdv-producto-col,
.pedido-dist-page .pdv-producto-cantidad-row .pdv-cantidad-col {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.pedido-dist-page .pdv-producto-cantidad-row .pdv-field-label {
    min-height: 2.5em;
    line-height: 1.3;
    margin-bottom: .3rem;
    display: block;
}
.pedido-dist-page .pdv-producto-cantidad-row .pdv-producto-col {
    max-width: 22rem;
}
.pedido-dist-page .pdv-producto-cantidad-row .selector-filtros-field {
    min-height: 40px;
    height: 40px;
    box-sizing: border-box;
}
.pedido-dist-page .pdv-producto-cantidad-row .pdv-cantidad-wrap .form-control {
    min-height: 40px;
    height: 40px;
    box-sizing: border-box;
}
.pedido-dist-page .pdv-producto-cantidad-row .pdv-cantidad-wrap.input-group {
    align-items: stretch;
}
.pedido-dist-page .pdv-producto-cantidad-row > [class*="col-"] {
    display: flex;
    flex-direction: column;
}
.pedido-dist-page .pdv-producto-cantidad-row .pdv-cantidad-wrap {
    flex: 0 0 auto;
    width: 9.5rem !important;
    max-width: 100% !important;
}
.pedido-dist-page .pdv-producto-cantidad-row .pdv-cantidad-wrap.has-unidad {
    width: 13rem !important;
}
.pedido-dist-page .pdv-cantidad-col {
    flex: 0 0 auto;
    width: auto;
    max-width: 13rem;
}
.pedido-dist-page .pdv-producto-cantidad-row .selector-catalogo-wrapper .input-group,
.pedido-dist-page .pdv-producto-cantidad-row .pdv-cantidad-wrap.input-group {
    width: 100%;
}
.pedido-dist-page .pdv-producto-cantidad-row .input-group .form-control,
.pedido-dist-page .pdv-producto-cantidad-row .input-group .btn,
.pedido-dist-page .pdv-producto-cantidad-row .input-group .input-group-text {
    min-height: 40px;
    border-width: 2px;
    border-color: #e2e8f0;
}
.pedido-dist-page .pdv-producto-cantidad-row .input-group .form-control {
    border-radius: 10px 0 0 10px;
    font-size: .95rem;
    font-weight: 600;
}
.pedido-dist-page .pdv-producto-cantidad-row .input-group .btn {
    border-radius: 0 10px 10px 0;
    font-weight: 600;
}
.pedido-dist-page .pdv-producto-cantidad-row .input-group .form-control:focus {
    border-color: #22c55e;
    box-shadow: none;
}
.pedido-dist-page .pdv-producto-cantidad-row .input-group:focus-within .btn,
.pedido-dist-page .pdv-producto-cantidad-row .input-group:focus-within .input-group-text {
    border-color: #22c55e;
}
.pedido-dist-page .pdv-unidad-append {
    background: #ecfdf5;
    color: #047857;
    font-weight: 700;
    font-size: .85rem;
    border-radius: 0 10px 10px 0;
    min-width: 56px;
    justify-content: center;
}
.pedido-dist-page .pdv-unidad-append.text-muted {
    background: #f8fafc;
    color: #94a3b8;
    border-color: #e2e8f0;
}
.pedido-dist-page .pdv-producto-cantidad-row .form-text {
    min-height: 1.35rem;
    margin-top: .35rem;
    margin-bottom: 0;
    line-height: 1.25;
}
.pedido-dist-page .pdv-field-readonly {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    font-weight: 600;
    color: #475569;
}
.pedido-dist-page .selector-filtros-field {
    background: #fff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: none !important;
}
.pedido-dist-page .selector-filtros-field:focus-within {
    border-color: #94a3b8 !important;
    box-shadow: none !important;
}
.pedido-dist-page .pdv-cantidad-wrap {
    position: relative;
    flex-grow: 0 !important;
    width: 9.5rem !important;
    max-width: 100% !important;
}
.pedido-dist-page .pdv-cantidad-wrap.has-unidad {
    width: 13rem !important;
}
.pedido-dist-page .pdv-cantidad-wrap .form-control {
    border: 1px solid #e2e8f0;
    font-size: .95rem;
    font-weight: 600;
    padding: .45rem .75rem;
    min-height: 40px;
    border-radius: 10px;
}
.pedido-dist-page .pdv-cantidad-wrap .form-control:focus {
    border-color: #94a3b8;
    box-shadow: none;
}
.pedido-dist-page .pdv-cantidad-wrap.has-unidad .form-control {
    border-radius: 10px 0 0 10px;
}
.pedido-dist-page .pdv-cantidad-wrap .input-group-append {
    display: none;
}
.pedido-dist-page .pdv-cantidad-wrap.has-unidad .input-group-append {
    display: flex;
}
.pedido-dist-page .pdv-footer {
    background: #f8fafc;
    border-top: 1px solid #e8f0ea;
    border-radius: 0 0 14px 14px;
}
.pedido-dist-page .btn-enviar-pedido {
    border-radius: 10px;
    font-weight: 700;
    padding: .6rem 1.35rem;
    box-shadow: none;
}
</style>
@endpush

@section('content')
<div class="pedido-dist-page">
    <div class="card pdv-card border-0 shadow-sm overflow-hidden">
        <div class="pdv-hero">
            <div class="pdv-hero-kicker">Nueva solicitud</div>
            <h2 class="pdv-hero-title"><i class="fas fa-truck-loading mr-2"></i>Pedido de distribución</h2>
            <div class="pdv-hero-code"><i class="fas fa-hashtag"></i> {{ $numeroSolicitud }}</div>
        </div>

        <div class="card-body px-3 py-3">
            <div class="pdv-intro mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                @if($esMinorista ?? false)
                    Solicite producto terminado del centro mayorista para su punto de venta. El mayorista revisará stock y preparará el envío.
                @else
                    Registre una solicitud hacia un punto de venta. El centro mayorista revisará stock y preparará el envío.
                @endif
            </div>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('punto-venta.pedidos.store') }}" id="formPedidoDist">
                @csrf

                <div class="pdv-form-block">
                    <div class="pdv-form-block-head">
                        <div class="pdv-form-block-icon pdv-form-block-icon--datos"><i class="fas fa-clipboard-list"></i></div>
                        <div>
                            <h3 class="pdv-form-block-title">Datos de la solicitud</h3>
                            <p class="pdv-form-block-sub">Número de referencia y fecha deseada de entrega</p>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5 mb-md-0">
                            <label class="pdv-field-label">N° solicitud</label>
                            <input type="text" class="form-control pdv-field-readonly" value="{{ $numeroSolicitud }}" readonly>
                        </div>
                        <div class="form-group col-md-7 mb-0">
                            <label class="pdv-field-label" for="fecha_entrega_deseada">Fecha entrega deseada</label>
                            <input type="date" name="fecha_entrega_deseada" id="fecha_entrega_deseada" class="form-control form-control-sm"
                                value="{{ old('fecha_entrega_deseada') }}">
                            <small class="pdv-fecha-hint d-block">
                                <i class="fas fa-exclamation-circle mr-1"></i>Si no marca fecha, el pedido quedará solicitado para hoy.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="pdv-form-block">
                    <div class="pdv-form-block-head">
                        <div class="pdv-form-block-icon pdv-form-block-icon--destino"><i class="fas fa-store"></i></div>
                        <div>
                            <h3 class="pdv-form-block-title">Destino del pedido</h3>
                            <p class="pdv-form-block-sub">
                                @if($esAdmin ?? false)
                                    Designe el minorista y el punto de venta que recibirá la solicitud
                                @else
                                    Elija a qué local llegará el pedido
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($esAdmin ?? false)
                        <div class="pdv-minorista-pick pdv-selector-compact {{ ($oldMinoristaLabel ?? '') ? 'is-selected' : '' }}" id="pdvMinoristaPick">
                            <label class="pdv-field-label mb-2">Minorista <span class="text-danger">*</span></label>
                            @include('partials.selector-catalogo', [
                                'id' => 'dist_minorista',
                                'name' => 'minorista_usuarioid',
                                'value' => old('minorista_usuarioid', $oldMinoristaId ?? ''),
                                'labelSelected' => $oldMinoristaLabel ?? '',
                                'endpoint' => route('catalogo-selector.usuarios'),
                                'params' => ['roles' => 'minorista'],
                                'title' => 'Buscar minorista',
                                'searchPlaceholder' => 'Nombre, usuario o correo…',
                                'searchLabel' => 'Buscar minorista',
                                'modalIcon' => 'fa-user-tie',
                                'rowIcon' => 'fa-user-tie',
                                'required' => true,
                                'inputGroup' => true,
                            ])
                        </div>
                    @endif

                    @if(($esMinorista ?? false) && ($puntosMinorista ?? collect())->isEmpty())
                        <div class="alert alert-warning py-2 mb-0 small">
                            Registre un punto de venta activo antes de solicitar producto.
                            <a href="{{ route('punto-venta.puntos.create') }}">Crear punto de venta</a>
                        </div>
                    @else
                        <label class="pdv-field-label mb-2">Punto de venta <span class="text-danger">*</span></label>
                        <div class="pdv-destino-pick {{ ($oldPuntoLabel ?? '') ? 'is-selected' : 'is-empty' }} {{ ($esAdmin ?? false) && empty($oldMinoristaId) ? 'is-locked' : '' }}" id="pdvDestinoPick">
                            <div class="pdv-destino-pick-icon"><i class="fas fa-store"></i></div>
                            <div class="pdv-destino-pick-body">
                                <div class="pdv-destino-kicker">
                                    <i class="fas fa-map-pin"></i>
                                    <span id="pdvDestinoKicker">{{ ($oldPuntoLabel ?? '') ? 'Punto de venta seleccionado' : 'Sin destino asignado' }}</span>
                                </div>
                                <span class="pdv-destino-nombre {{ ($oldPuntoLabel ?? '') ? '' : 'is-placeholder' }}" id="pdvDestinoNombre">
                                    {{ $oldPuntoLabel ?: 'Seleccione el local que recibirá el pedido' }}
                                </span>
                                <div class="pdv-destino-ubicacion {{ ($oldPuntoResumen ?? '') ? '' : 'is-empty' }}" id="pdvDestinoUbicacion">
                                    @if($oldPuntoResumen ?? '')
                                        <i class="fas fa-location-dot"></i>
                                        <span>{{ $oldPuntoResumen }}</span>
                                    @else
                                        <span>La dirección aparecerá al elegir un punto de venta.</span>
                                    @endif
                                </div>
                                <div class="pdv-destino-meta-row">
                                    <span class="pdv-destino-badge d-none" id="pdvDestinoBadge">
                                        <i class="fas fa-check-circle"></i> Listo para solicitar
                                    </span>
                                    <button type="button" class="pdv-destino-map-link" id="btnVerMapaPdvDestino"
                                        @if(empty($puntosVentaMapa)) disabled @endif>
                                        <i class="fas fa-map-marked-alt"></i> Ver en mapa
                                    </button>
                                </div>
                            </div>
                            <div class="pdv-destino-actions">
                                <button type="button" class="btn btn-outline-success pdv-destino-btn" id="btnBuscarPdvDestino"
                                    @if(($esAdmin ?? false) && empty($oldMinoristaId)) disabled @endif>
                                    <i class="fas fa-search mr-1"></i>Elegir destino
                                </button>
                                <button type="button" class="btn btn-outline-secondary pdv-destino-btn pdv-destino-btn--ghost d-none" id="btnCambiarPdvDestino">
                                    Cambiar
                                </button>
                            </div>
                        </div>
                        <div class="d-none" aria-hidden="true">
                            @include('partials.selector-catalogo', [
                                'id' => 'dist_punto_venta',
                                'name' => 'puntoventaid',
                                'value' => old('puntoventaid', $oldPuntoId ?? ''),
                                'labelSelected' => $oldPuntoLabel,
                                'endpoint' => route('catalogo-selector.puntos-venta'),
                                'title' => ($esMinorista ?? false) ? 'Mis puntos de venta' : 'Puntos de venta del minorista',
                                'searchPlaceholder' => ($esMinorista ?? false)
                                    ? 'Nombre o dirección del punto…'
                                    : 'Nombre o dirección del punto…',
                                'searchLabel' => 'Buscar punto de venta',
                                'modalIcon' => 'fa-store',
                                'rowIcon' => 'fa-store',
                                'colDetalle' => 'Ubicación',
                                'required' => true,
                                'inputGroup' => true,
                                'params' => ($esAdmin ?? false) && ! empty($oldMinoristaId)
                                    ? ['minorista_usuarioid' => (string) $oldMinoristaId]
                                    : [],
                            ])
                        </div>
                    @endif

                    @if($esAdmin ?? false)
                    <div class="pdv-minorista-pick pdv-selector-compact mt-3 {{ ($oldAlmacenLabel ?? '') ? 'is-selected' : '' }}" id="pdvAlmacenPick">
                        <label class="pdv-field-label mb-2">Almacén mayorista (origen) <span class="text-danger">*</span></label>
                        @include('partials.selector-catalogo', [
                            'id' => 'dist_almacen_mayorista',
                            'name' => 'almacen_mayorista_origenid',
                            'value' => old('almacen_mayorista_origenid', ''),
                            'labelSelected' => $oldAlmacenLabel,
                            'endpoint' => route('catalogo-selector.almacenes'),
                            'params' => ['ambito' => 'mayorista'],
                            'title' => 'Buscar almacén mayorista',
                            'searchPlaceholder' => 'Nombre o ubicación…',
                            'required' => true,
                            'inputGroup' => true,
                        ])
                    </div>
                    @endif
                </div>

                <div class="pdv-form-block">
                    <div class="pdv-form-block-head">
                        <div class="pdv-form-block-icon pdv-form-block-icon--producto"><i class="fas fa-box"></i></div>
                        <div>
                            <h3 class="pdv-form-block-title">Producto y cantidad</h3>
                            <p class="pdv-form-block-sub">Qué necesita y en qué cantidad</p>
                        </div>
                    </div>
                    <div class="pdv-producto-cantidad-row">
                        <div class="pdv-producto-col">
                            <label class="pdv-field-label">Producto (stock mayorista) <span class="text-danger">*</span></label>
                            <div class="pdv-producto-pick {{ ($esAdmin ?? false) && empty(old('almacen_mayorista_origenid')) ? 'is-locked' : '' }}" id="pdvProductoPick">
                            @include('partials.selector-catalogo', [
                                'id' => 'dist_producto_mayorista',
                                'name' => 'insumoid',
                                'value' => old('insumoid', ''),
                                'labelSelected' => $oldProductoLabel,
                                'endpoint' => route('catalogo-selector.insumos'),
                                'params' => array_filter([
                                    'ambito_mayorista' => '1',
                                    'solo_con_stock' => '1',
                                    'requiere_almacen' => ($esAdmin ?? false) ? '1' : '0',
                                    'almacenid' => old('almacen_mayorista_origenid') ?: null,
                                ]),
                                'title' => 'Buscar producto en mayorista',
                                'searchPlaceholder' => 'Nombre del producto…',
                                'required' => true,
                                'inputGroup' => true,
                            ])
                            </div>
                            <small id="txtStockDisponible" class="form-text text-success font-weight-bold"></small>
                        </div>
                        <div class="pdv-cantidad-col">
                            <label class="pdv-field-label" for="cantidad">Cantidad <span class="text-danger">*</span></label>
                            <div class="pdv-cantidad-wrap input-group {{ $oldProductoUnidad ? 'has-unidad' : '' }}" id="wrapCantidad">
                                <input type="number" step="0.01" min="0.01" name="cantidad" id="cantidad" class="form-control" required
                                    value="{{ old('cantidad') }}" placeholder="0.00">
                                <div class="input-group-append">
                                    <span class="input-group-text pdv-unidad-append" id="badgeUnidad">{{ $oldProductoUnidad }}</span>
                                </div>
                            </div>
                            <small class="form-text text-muted" id="txtAyudaCantidad">
                                @if($oldProductoUnidad)
                                    Indique la cantidad en {{ $oldProductoUnidad }}.
                                @else
                                    La unidad aparece al elegir el producto.
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="pdv-field-label" for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="2" class="form-control"
                        style="border-radius:10px;border:2px solid #e2e8f0;"
                        placeholder="Instrucciones de entrega, horario preferido…">{{ old('observaciones') }}</textarea>
                </div>
            </form>
        </div>

        <div class="card-footer pdv-footer d-flex justify-content-between align-items-center py-2 px-3">
            <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-light border">
                <i class="fas fa-arrow-left mr-1"></i> Cancelar
            </a>
            <button type="submit" form="formPedidoDist" class="btn btn-success btn-enviar-pedido" id="btnEnviarPedido">
                <i class="fas fa-paper-plane mr-1"></i> Enviar solicitud
            </button>
        </div>
    </div>
</div>

@if(! (($esMinorista ?? false) && ($puntosMinorista ?? collect())->isEmpty()))
<div class="modal fade" id="modalMapaPdvDestino" tabindex="-1" role="dialog" aria-labelledby="modalMapaPdvDestinoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;border:0;">
            <div class="modal-header py-2" style="background:linear-gradient(135deg,#14532d,#22c55e);color:#fff;border:0;">
                <h5 class="modal-title font-weight-bold" id="modalMapaPdvDestinoLabel">
                    <i class="fas fa-map-marked-alt mr-2"></i>
                    {{ ($esMinorista ?? false) ? 'Mis puntos de venta' : 'Puntos de venta en mapa' }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body py-3">
                <p class="small text-muted mb-2">Clic en un marcador o en la lista para elegir el destino del pedido.</p>
                <div id="mapaPdvDestino" class="pdv-mapa-wrap"></div>
                <div class="pdv-mapa-lista" id="listaPdvMapaDestino"></div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var stockActual = {{ $oldProductoStock !== null ? json_encode($oldProductoStock) : 'null' }};
    var unidadActual = @json($oldProductoUnidad ?: '');
    var puntosVentaMapa = @json($puntosVentaMapa ?? []);
    var pdvSeleccionadoId = @json(old('puntoventaid', $oldPuntoId ?? ''));
    var esAdmin = @json($esAdmin ?? false);
    var minoristaSeleccionadoId = @json(old('minorista_usuarioid', $oldMinoristaId ?? ''));
    var almacenSeleccionadoId = @json(old('almacen_mayorista_origenid', ''));

    function almacenIdActual() {
        return document.querySelector('#selector_wrap_dist_almacen_mayorista .selector-catalogo-value')?.value || '';
    }

    function limpiarProducto() {
        var wrap = document.getElementById('selector_wrap_dist_producto_mayorista');
        if (!wrap) return;
        wrap.querySelector('.selector-catalogo-value').value = '';
        var display = wrap.querySelector('.selector-catalogo-label');
        if (display) {
            display.value = '';
            display.classList.add('is-empty', 'text-muted');
        }
        if (window.CatalogoSelector) {
            CatalogoSelector.clear('dist_producto_mayorista');
        }
        actualizarUnidad(null);
        var cantidad = document.getElementById('cantidad');
        if (cantidad) cantidad.value = '';
    }

    function syncBloqueoProductoAdmin() {
        if (!esAdmin) return;
        var pickEl = document.getElementById('pdvProductoPick');
        almacenSeleccionadoId = almacenIdActual();
        var bloqueado = !almacenSeleccionadoId;
        if (pickEl) pickEl.classList.toggle('is-locked', bloqueado);
    }

    function puntosMapaFiltrados() {
        if (!esAdmin || !minoristaSeleccionadoId) {
            return puntosVentaMapa;
        }
        return puntosVentaMapa.filter(function (p) {
            return String(p.minorista_usuarioid) === String(minoristaSeleccionadoId);
        });
    }

    function limpiarPdvDestino() {
        var wrap = getPdvWrap();
        if (!wrap) return;
        wrap.querySelector('.selector-catalogo-value').value = '';
        var display = wrap.querySelector('.selector-catalogo-label');
        if (display) {
            display.value = '';
            display.classList.add('text-muted');
        }
        if (window.CatalogoSelector) {
            CatalogoSelector.clear('dist_punto_venta');
        }
        actualizarDestinoVisible('', '', {});
    }

    function syncBloqueoDestinoAdmin() {
        if (!esAdmin) return;
        var pickEl = document.getElementById('pdvDestinoPick');
        var btnBuscar = document.getElementById('btnBuscarPdvDestino');
        var btnMapa = document.getElementById('btnVerMapaPdvDestino');
        var bloqueado = !minoristaSeleccionadoId;
        if (pickEl) pickEl.classList.toggle('is-locked', bloqueado);
        if (btnBuscar) btnBuscar.disabled = bloqueado;
        if (btnMapa) btnMapa.disabled = bloqueado || !puntosMapaFiltrados().length;
    }

    function paramsPuntoVenta() {
        if (esAdmin && minoristaSeleccionadoId) {
            return { minorista_usuarioid: String(minoristaSeleccionadoId) };
        }
        return {};
    }

    function getPdvWrap() {
        return document.getElementById('selector_wrap_dist_punto_venta');
    }

    function actualizarDestinoVisible(id, label, extra) {
        var nombreEl = document.getElementById('pdvDestinoNombre');
        var pickEl = document.getElementById('pdvDestinoPick');
        var ubicacionEl = document.getElementById('pdvDestinoUbicacion');
        var kickerEl = document.getElementById('pdvDestinoKicker');
        var badgeEl = document.getElementById('pdvDestinoBadge');
        var btnCambiar = document.getElementById('btnCambiarPdvDestino');
        if (!nombreEl || !pickEl) return;

        extra = extra || {};
        var resumen = extra.ubicacion_resumen || extra.direccion || '';
        pdvSeleccionadoId = id ? String(id) : '';

        if (id && label) {
            nombreEl.textContent = label;
            nombreEl.classList.remove('is-placeholder');
            pickEl.classList.remove('is-empty');
            pickEl.classList.add('is-selected');
            if (kickerEl) kickerEl.textContent = 'Punto de venta seleccionado';
            if (badgeEl) badgeEl.classList.remove('d-none');
            if (btnCambiar) btnCambiar.classList.remove('d-none');
            if (ubicacionEl) {
                ubicacionEl.classList.remove('is-empty');
                ubicacionEl.innerHTML = resumen
                    ? '<i class="fas fa-location-dot"></i><span>' + resumen + '</span>'
                    : '<span>Sin dirección registrada para este punto.</span>';
            }
        } else {
            nombreEl.textContent = 'Seleccione el local que recibirá el pedido';
            nombreEl.classList.add('is-placeholder');
            pickEl.classList.add('is-empty');
            pickEl.classList.remove('is-selected');
            if (kickerEl) kickerEl.textContent = 'Sin destino asignado';
            if (badgeEl) badgeEl.classList.add('d-none');
            if (btnCambiar) btnCambiar.classList.add('d-none');
            if (ubicacionEl) {
                ubicacionEl.classList.add('is-empty');
                ubicacionEl.innerHTML = '<span>La dirección aparecerá al elegir un punto de venta.</span>';
            }
        }
    }

    function aplicarPdvSeleccionado(pdv) {
        if (!pdv || !pdv.id) return;
        var wrap = getPdvWrap();
        if (!wrap) return;

        wrap.querySelector('.selector-catalogo-value').value = pdv.id;
        var display = wrap.querySelector('.selector-catalogo-label');
        if (display) {
            display.value = pdv.label;
            display.classList.remove('text-muted');
        }
        actualizarDestinoVisible(pdv.id, pdv.label, {
            ubicacion_resumen: pdv.resumen || '',
            direccion: pdv.direccion || '',
        });
        wrap.dispatchEvent(new CustomEvent('selector-catalogo:change', {
            bubbles: true,
            detail: {
                id: pdv.id,
                label: pdv.label,
                extra: {
                    ubicacion_resumen: pdv.resumen || '',
                    direccion: pdv.direccion || '',
                    lat: pdv.lat,
                    lng: pdv.lng,
                },
            },
        }));
    }

    function buscarPdvEnMapa(id) {
        return puntosVentaMapa.find(function (p) { return String(p.id) === String(id); }) || null;
    }

    function actualizarUnidad(extra) {
        var badge = document.getElementById('badgeUnidad');
        var ayuda = document.getElementById('txtAyudaCantidad');
        var wrapCantidad = document.getElementById('wrapCantidad');
        var unidad = (extra && extra.unidad) ? String(extra.unidad).trim() : '';
        unidadActual = unidad;
        if (wrapCantidad) {
            wrapCantidad.classList.toggle('has-unidad', !!unidad);
        }
        if (badge) {
            badge.textContent = unidad;
        }
        if (ayuda) {
            ayuda.textContent = unidad
                ? 'Indique la cantidad en ' + unidad + '.'
                : 'La unidad aparece al elegir el producto.';
        }
        stockActual = extra && typeof extra.stock === 'number' ? extra.stock : null;
        var txt = document.getElementById('txtStockDisponible');
        if (stockActual !== null && unidad) {
            txt.textContent = 'Disponible en mayorista: ' + stockActual.toFixed(2) + ' ' + unidad;
        } else {
            txt.textContent = '';
        }
    }

    if (unidadActual) {
        actualizarUnidad({ unidad: unidadActual, stock: stockActual });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;

        var almWrap = document.getElementById('selector_wrap_dist_almacen_mayorista');
        var pdvWrap = getPdvWrap();
        var mapaPdv = null;
        var capasPdvMapa = null;

        document.getElementById('btnBuscarPdvDestino')?.addEventListener('click', function () {
            if (esAdmin && !minoristaSeleccionadoId) {
                alert('Primero seleccione el minorista.');
                return;
            }
            CatalogoSelector.open('dist_punto_venta');
        });
        document.getElementById('btnCambiarPdvDestino')?.addEventListener('click', function () {
            if (esAdmin && !minoristaSeleccionadoId) {
                alert('Primero seleccione el minorista.');
                return;
            }
            CatalogoSelector.open('dist_punto_venta');
        });

        function iconoPdvMapa(seleccionado) {
            var cls = seleccionado ? ' is-selected' : '';
            return L.divIcon({
                html: '<div class="pedido-dist-mapa-pin' + cls + '" style="background:#2563eb"><i class="fas fa-store"></i></div>',
                className: '',
                iconSize: [30, 30],
                iconAnchor: [15, 15],
            });
        }

        function pintarListaPdvMapa() {
            var lista = document.getElementById('listaPdvMapaDestino');
            if (!lista) return;
            lista.innerHTML = '';
            puntosMapaFiltrados().forEach(function (pdv) {
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'pdv-mapa-chip' + (String(pdvSeleccionadoId) === String(pdv.id) ? ' is-active' : '');
                chip.textContent = pdv.label;
                chip.addEventListener('click', function () {
                    aplicarPdvSeleccionado(pdv);
                    if (mapaPdv && pdv.lat && pdv.lng) {
                        mapaPdv.setView([parseFloat(pdv.lat), parseFloat(pdv.lng)], 15);
                    }
                    pintarMapaPdvDestino();
                    window.jQuery('#modalMapaPdvDestino').modal('hide');
                });
                lista.appendChild(chip);
            });
        }

        function pintarMapaPdvDestino() {
            if (!mapaPdv || !capasPdvMapa) return;
            capasPdvMapa.clearLayers();
            var bounds = [];
            var conCoords = puntosMapaFiltrados().filter(function (p) {
                return p.lat != null && p.lng != null && !isNaN(parseFloat(p.lat)) && !isNaN(parseFloat(p.lng));
            });

            conCoords.forEach(function (pdv) {
                var lat = parseFloat(pdv.lat);
                var lng = parseFloat(pdv.lng);
                var seleccionado = String(pdvSeleccionadoId) === String(pdv.id);
                var marker = L.marker([lat, lng], { icon: iconoPdvMapa(seleccionado) })
                    .bindTooltip(pdv.label, { direction: 'top', offset: [0, -12] })
                    .addTo(capasPdvMapa);
                marker.on('click', function () {
                    aplicarPdvSeleccionado(pdv);
                    pintarMapaPdvDestino();
                    pintarListaPdvMapa();
                    window.jQuery('#modalMapaPdvDestino').modal('hide');
                });
                bounds.push([lat, lng]);
            });

            if (bounds.length === 1) {
                mapaPdv.setView(bounds[0], 14);
            } else if (bounds.length > 1) {
                mapaPdv.fitBounds(bounds, { padding: [28, 28], maxZoom: 14 });
            }
            pintarListaPdvMapa();
        }

        function abrirMapaPdvDestino() {
            if (esAdmin && !minoristaSeleccionadoId) {
                alert('Primero seleccione el minorista.');
                return;
            }
            if (!puntosMapaFiltrados().length) return;
            window.jQuery('#modalMapaPdvDestino').modal('show');
        }

        window.jQuery('#modalMapaPdvDestino').on('shown.bs.modal', function () {
            if (!window.L) return;
            if (!mapaPdv) {
                mapaPdv = L.map('mapaPdvDestino').setView([-17.7833, -63.1821], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap',
                }).addTo(mapaPdv);
                capasPdvMapa = L.layerGroup().addTo(mapaPdv);
            }
            window.setTimeout(function () {
                mapaPdv.invalidateSize();
                pintarMapaPdvDestino();
                var sel = buscarPdvEnMapa(pdvSeleccionadoId);
                if (sel && sel.lat && sel.lng) {
                    mapaPdv.setView([parseFloat(sel.lat), parseFloat(sel.lng)], 15);
                }
            }, 180);
        });

        document.getElementById('btnVerMapaPdvDestino')?.addEventListener('click', abrirMapaPdvDestino);

        function paramsProducto() {
            var params = {
                ambito_mayorista: '1',
                solo_con_stock: '1',
            };
            if (esAdmin) {
                params.requiere_almacen = '1';
                var almId = almacenIdActual();
                if (almId) {
                    params.almacenid = almId;
                }
            }
            return params;
        }

        function validarCantidad() {
            var input = document.getElementById('cantidad');
            if (!input) return 'Indique la cantidad solicitada.';
            var raw = String(input.value || '').trim();
            if (raw === '') return 'Indique la cantidad solicitada.';
            var v = parseFloat(raw);
            if (isNaN(v)) return 'La cantidad debe ser un número válido.';
            if (v <= 0) return 'La cantidad debe ser mayor que cero.';
            if (stockActual !== null && v > stockActual) {
                return 'La cantidad no puede superar el stock disponible (' + stockActual.toFixed(2) + (unidadActual ? ' ' + unidadActual : '') + ').';
            }
            return null;
        }

        function mostrarErrorFormulario(mensaje) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Revise el formulario', text: mensaje, confirmButtonText: 'Entendido' });
            } else {
                alert(mensaje);
            }
        }

        if (pdvWrap) {
            pdvWrap.addEventListener('selector-catalogo:change', function (e) {
                actualizarDestinoVisible(e.detail?.id, e.detail?.label, e.detail?.extra || {});
            });
        }

        @if($esAdmin ?? false)
        var minoristaWrap = document.getElementById('selector_wrap_dist_minorista');
        CatalogoSelector.register('dist_minorista', {
            endpoint: @json(route('catalogo-selector.usuarios')),
            title: 'Minorista',
            searchPlaceholder: 'Nombre, usuario o correo…',
            params: { roles: 'minorista' },
            onSelect: function (item) {
                minoristaSeleccionadoId = item.id ? String(item.id) : '';
                document.getElementById('pdvMinoristaPick')?.classList.toggle('is-selected', !!minoristaSeleccionadoId);
                if (CatalogoSelector.instances.dist_punto_venta) {
                    CatalogoSelector.instances.dist_punto_venta.params = paramsPuntoVenta();
                }
                limpiarPdvDestino();
                syncBloqueoDestinoAdmin();
            },
        });
        if (minoristaWrap) {
            minoristaWrap.addEventListener('selector-catalogo:change', function (e) {
                minoristaSeleccionadoId = e.detail?.id ? String(e.detail.id) : '';
                document.getElementById('pdvMinoristaPick')?.classList.toggle('is-selected', !!minoristaSeleccionadoId);
                if (CatalogoSelector.instances.dist_punto_venta) {
                    CatalogoSelector.instances.dist_punto_venta.params = paramsPuntoVenta();
                }
                if (!minoristaSeleccionadoId) {
                    limpiarPdvDestino();
                }
                syncBloqueoDestinoAdmin();
            });
        }
        CatalogoSelector.register('dist_punto_venta', {
            endpoint: @json(route('catalogo-selector.puntos-venta')),
            title: 'Puntos de venta del minorista',
            searchPlaceholder: 'Nombre o dirección del punto…',
            params: paramsPuntoVenta(),
            onSelect: function (item) {
                actualizarDestinoVisible(item.id, item.label, item.extra || {});
            },
        });
        syncBloqueoDestinoAdmin();
        @endif

        if (pdvSeleccionadoId) {
            var pdvInicial = buscarPdvEnMapa(pdvSeleccionadoId);
            if (pdvInicial) {
                actualizarDestinoVisible(pdvInicial.id, pdvInicial.label, {
                    ubicacion_resumen: pdvInicial.resumen || '',
                    direccion: pdvInicial.direccion || '',
                });
            } else if (@json($oldPuntoLabel ?? '')) {
                document.getElementById('pdvDestinoBadge')?.classList.remove('d-none');
                document.getElementById('btnCambiarPdvDestino')?.classList.remove('d-none');
            }
        }

        @if($esAdmin ?? false)
        CatalogoSelector.register('dist_almacen_mayorista', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén mayorista',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'mayorista' },
            onSelect: function (item) {
                almacenSeleccionadoId = item.id ? String(item.id) : '';
                document.getElementById('pdvAlmacenPick')?.classList.toggle('is-selected', !!almacenSeleccionadoId);
                if (CatalogoSelector.instances.dist_producto_mayorista) {
                    CatalogoSelector.instances.dist_producto_mayorista.params = paramsProducto();
                }
                limpiarProducto();
                syncBloqueoProductoAdmin();
            },
        });
        if (almWrap) {
            almWrap.addEventListener('selector-catalogo:change', function (e) {
                almacenSeleccionadoId = e.detail?.id ? String(e.detail.id) : '';
                document.getElementById('pdvAlmacenPick')?.classList.toggle('is-selected', !!almacenSeleccionadoId);
                if (CatalogoSelector.instances.dist_producto_mayorista) {
                    CatalogoSelector.instances.dist_producto_mayorista.params = paramsProducto();
                }
                if (!almacenSeleccionadoId) {
                    limpiarProducto();
                }
                syncBloqueoProductoAdmin();
            });
        }
        syncBloqueoProductoAdmin();
        @endif

        CatalogoSelector.register('dist_producto_mayorista', {
            endpoint: @json(route('catalogo-selector.insumos')),
            title: 'Producto terminado (mayorista)',
            searchPlaceholder: 'Nombre del producto…',
            params: paramsProducto(),
            onSelect: function (item) {
                actualizarUnidad(item.extra || {});
            },
        });

        if (almWrap && !esAdmin) {
            almWrap.addEventListener('selector-catalogo:change', function (e) {
                CatalogoSelector.instances.dist_producto_mayorista.params = paramsProducto();
                if (!e.detail.id) {
                    actualizarUnidad(null);
                }
            });
        }

        var prodWrap = document.getElementById('selector_wrap_dist_producto_mayorista');
        if (prodWrap) {
            prodWrap.addEventListener('selector-catalogo:change', function (e) {
                actualizarUnidad(e.detail?.extra || null);
            });
            prodWrap.querySelector('[data-selector-open]')?.addEventListener('click', function (e) {
                if (esAdmin && !almacenIdActual()) {
                    e.preventDefault();
                    e.stopPropagation();
                    mostrarErrorFormulario('Seleccione primero el almacén mayorista de origen.');
                }
            });
        }

        var inputCantidad = document.getElementById('cantidad');
        if (inputCantidad) {
            inputCantidad.addEventListener('input', function () {
                var v = parseFloat(this.value);
                if (!isNaN(v) && v < 0) {
                    this.value = '';
                }
            });
        }

        document.getElementById('formPedidoDist').addEventListener('submit', function (e) {
            if (esAdmin) {
                var minoristaOk = document.querySelector('#selector_wrap_dist_minorista .selector-catalogo-value')?.value;
                if (!minoristaOk) {
                    e.preventDefault();
                    mostrarErrorFormulario('Seleccione el minorista destino.');
                    return;
                }
                if (!almacenIdActual()) {
                    e.preventDefault();
                    mostrarErrorFormulario('Seleccione el almacén mayorista de origen.');
                    return;
                }
            }
            var pdvOk = document.querySelector('#selector_wrap_dist_punto_venta .selector-catalogo-value')?.value;
            if (!pdvOk) {
                e.preventDefault();
                mostrarErrorFormulario('Seleccione un punto de venta destino.');
                return;
            }
            if (!document.querySelector('#selector_wrap_dist_producto_mayorista .selector-catalogo-value')?.value) {
                e.preventDefault();
                mostrarErrorFormulario('Seleccione un producto del centro mayorista.');
                return;
            }
            var errCantidad = validarCantidad();
            if (errCantidad) {
                e.preventDefault();
                mostrarErrorFormulario(errCantidad);
            }
        });
    });
})();
</script>
@endpush
