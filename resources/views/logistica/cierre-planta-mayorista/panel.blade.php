@extends('layouts.app')

@section('title', 'Cierre operativo — '.$ruta->codigo.' | AgroFusion')
@section('page_title', 'Cierre operativo del envío')

@push('styles')
<style>
.cierre-ag-page { max-width: 820px; margin: 0 auto; padding: 0 .25rem 1.5rem; }
.cierre-ag-page--resumen { max-width: 920px; }
.cierre-ag-toolbar {
    display: flex; align-items: stretch; gap: 0;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    box-shadow: 0 4px 20px rgba(15,23,42,.07); overflow: hidden; margin-bottom: 1.25rem;
}
.cierre-ag-toolbar__back {
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    padding: .85rem 1.1rem; background: #f8fafc; border-right: 1px solid #e2e8f0;
    color: #475569; font-weight: 600; font-size: .84rem; text-decoration: none;
    transition: background .15s ease, color .15s ease;
}
.cierre-ag-toolbar__back:hover { background: #f1f5f9; color: #1e293b; text-decoration: none; }
.cierre-ag-toolbar__main { flex: 1; padding: .85rem 1.15rem; min-width: 0; }
.cierre-ag-toolbar__tipo {
    font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #166534;
}
.cierre-ag-toolbar__codigo { font-size: 1.2rem; font-weight: 800; color: #14532d; line-height: 1.2; margin-top: .1rem; }

.cierre-ag-stepper {
    display: flex; align-items: flex-start; gap: 0;
    margin-bottom: 1.35rem; padding: .15rem 0;
    overflow-x: auto; scrollbar-width: thin;
}
.cierre-ag-stepper::-webkit-scrollbar { height: 4px; }
.cierre-ag-stepper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.cierre-ag-step {
    flex: 1 1 0; min-width: 108px; max-width: 140px;
    display: flex; flex-direction: column; align-items: center; text-align: center;
    position: relative; padding: 0 .35rem;
}
.cierre-ag-step:not(:last-child)::after {
    content: ''; position: absolute; top: 17px; left: calc(50% + 18px); right: calc(-50% + 18px);
    height: 2px; background: #e2e8f0; z-index: 0;
}
.cierre-ag-step.done:not(:last-child)::after { background: #86efac; }
.cierre-ag-step__bubble {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .78rem; font-weight: 800; border: 2px solid #e2e8f0;
    background: #fff; color: #94a3b8; position: relative; z-index: 1;
    transition: all .15s ease;
}
.cierre-ag-step__bubble i { font-size: .72rem; }
.cierre-ag-step.pending .cierre-ag-step__bubble { background: #f8fafc; }
.cierre-ag-step.done .cierre-ag-step__bubble {
    background: #f0fdf4; border-color: #22c55e; color: #166534;
}
.cierre-ag-step.active .cierre-ag-step__bubble {
    background: linear-gradient(135deg, #2c5530, #3d7a46);
    border-color: #2c5530; color: #fff;
    box-shadow: 0 4px 12px rgba(44,85,48,.35);
}
.cierre-ag-step__dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: #fff; display: block;
}
.cierre-ag-step__label {
    margin-top: .45rem; font-size: .64rem; font-weight: 600; line-height: 1.25;
    color: #94a3b8; max-width: 100%;
}
.cierre-ag-step.done .cierre-ag-step__label { color: #166534; }
.cierre-ag-step.active .cierre-ag-step__label { color: #1e3a22; font-weight: 700; }
.cierre-ag-step__link {
    display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;
    width: 100%; border-radius: 10px; padding: .15rem 0 .25rem;
    transition: background .12s ease;
}
.cierre-ag-step__link:hover { background: #f0fdf4; text-decoration: none; }
.cierre-ag-step__link:hover .cierre-ag-step__bubble { border-color: #22c55e; }

.cierre-ag-consulta {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .65rem;
    padding: .65rem .9rem; margin-bottom: 1rem;
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;
    font-size: .84rem; color: #1e40af;
}
.cierre-ag-card {
    border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;
    box-shadow: 0 6px 24px rgba(15,23,42,.07);
}
.cierre-ag-card__head {
    padding: .9rem 1.15rem; border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(180deg, #fafafa 0%, #f4f6f8 100%);
    font-weight: 700; font-size: .98rem; color: #1e293b;
    display: flex; align-items: center; gap: .5rem;
}
.cierre-ag-card__head-num {
    width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
    background: #2c5530; color: #fff; font-size: .72rem; font-weight: 800;
    display: inline-flex; align-items: center; justify-content: center;
}
.cierre-ag-card__body { padding: 1.15rem; background: #fff; }
.cierre-ag-check-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .65rem .85rem; border: 1px solid #e2e8f0; border-radius: 10px;
    background: #fafbfc; gap: .75rem; margin-bottom: .5rem;
}
.cierre-ag-check-row:last-child { margin-bottom: 0; }
.cierre-ag-check-row__label { font-weight: 600; font-size: .88rem; color: #334155; flex: 1; min-width: 0; }
.cierre-ag-toggle {
    display: inline-flex; align-items: center; gap: .45rem; flex-shrink: 0;
}
.cierre-ag-toggle__btn {
    min-width: 52px; padding: .35rem .75rem; border-radius: 8px;
    font-size: .78rem; font-weight: 700; border: 2px solid transparent;
    cursor: pointer; transition: all .12s ease; text-align: center;
    background: #fff; user-select: none;
}
.cierre-ag-toggle__btn input { position: absolute; opacity: 0; pointer-events: none; }
.cierre-ag-toggle__btn--si { border-color: #bbf7d0; color: #166534; }
.cierre-ag-toggle__btn--si.active { background: #22c55e; border-color: #16a34a; color: #fff; box-shadow: 0 2px 6px rgba(34,197,94,.3); }
.cierre-ag-toggle__btn--no { border-color: #fecaca; color: #b91c1c; }
.cierre-ag-toggle__btn--no.active { background: #ef4444; border-color: #dc2626; color: #fff; box-shadow: 0 2px 6px rgba(239,68,68,.25); }
.cierre-ag-form-actions {
    display: flex; flex-wrap: wrap; align-items: center; gap: .65rem;
    margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;
}
.cierre-ag-form-actions .btn-success { padding: .5rem 1.25rem; }
.cierre-ag-incidentes-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: .55rem; margin-bottom: .25rem;
}
.cierre-ag-incidente-card {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    padding: .65rem .85rem; border: 1px solid #e2e8f0; border-radius: 10px;
    background: #fff; transition: border-color .12s ease, background .12s ease;
}
.cierre-ag-incidente-card:has(.inc-check:checked) {
    border-color: #fcd34d; background: #fffbeb;
}
.cierre-ag-incidente-card__label {
    font-size: .84rem; font-weight: 600; color: #334155; flex: 1; min-width: 0; line-height: 1.3;
}
.cierre-ag-incidente-card .custom-control { padding-left: 1.75rem; min-height: auto; }
.cierre-ag-incidente-card .custom-control-label {
    font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b;
}
.cierre-ag-incidente-card .custom-control-label::before,
.cierre-ag-incidente-card .custom-control-label::after { top: .1rem; }
.cierre-ag-ruta-actions { display: flex; flex-wrap: wrap; gap: .65rem; align-items: stretch; }
.cierre-ag-ruta-actions form { flex: 1 1 180px; margin: 0; }
.cierre-ag-ruta-actions .btn-block { width: 100%; }
.cierre-ag-llegada-hint {
    display: flex; align-items: flex-start; gap: .55rem;
    padding: .65rem .85rem; border-radius: 10px; margin-bottom: .85rem;
    background: #fffbeb; border: 1px solid #fde68a; font-size: .84rem; color: #92400e;
}
.cierre-ag-firma-box {
    border: 2px dashed #cbd5e1; border-radius: 10px; background: #fff;
    touch-action: none; cursor: crosshair; width: 100%; height: 160px;
}
.cierre-ag-firma-actions { margin-top: .65rem; display: flex; gap: .5rem; flex-wrap: wrap; }
.cierre-ag-atajo {
    border: 1px solid #d1e7d4; background: #f0f7f1; color: #2c5530; font-weight: 600;
}
.cierre-ag-atajo:hover { background: #e8f5e9; color: #1b4332; }
.cierre-ag-status {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: .9rem 1rem; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0;
}
.cierre-ag-status__icon {
    width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
}
.cierre-ag-status--ok { background: #f0fdf4; border-color: #bbf7d0; }
.cierre-ag-status--ok .cierre-ag-status__icon { background: #dcfce7; color: #15803d; }
.cierre-ag-status--ruta { background: #eff6ff; border-color: #bfdbfe; }
.cierre-ag-status--ruta .cierre-ag-status__icon { background: #dbeafe; color: #1d4ed8; }
.cierre-ag-status--llegada { background: #fffbeb; border-color: #fde68a; }
.cierre-ag-status--llegada .cierre-ag-status__icon { background: #fef3c7; color: #b45309; }
.cierre-ag-status--incidente { background: #faf5ff; border-color: #e9d5ff; }
.cierre-ag-status--incidente .cierre-ag-status__icon { background: #f3e8ff; color: #7c3aed; }
.cierre-ag-status--firma { background: #f5f3ff; border-color: #ddd6fe; }
.cierre-ag-status--firma .cierre-ag-status__icon { background: #ede9fe; color: #5b21b6; }
.cierre-ag-status--entrega { background: #ecfdf5; border-color: #a7f3d0; }
.cierre-ag-status--entrega .cierre-ag-status__icon { background: #d1fae5; color: #047857; }
.cierre-ag-btn-finalizar {
    display: inline-flex; align-items: center; justify-content: center; gap: .45rem;
    width: auto; min-width: 200px; max-width: 100%;
    padding: .55rem 1.15rem; margin-top: .5rem;
    border: 0; border-radius: 10px; font-size: .88rem; font-weight: 700;
    color: #fff; cursor: pointer;
    background: linear-gradient(135deg, #1e4620 0%, #2c5530 50%, #3d7a46 100%);
    box-shadow: 0 3px 10px rgba(44,85,48,.28);
    transition: transform .12s ease, box-shadow .12s ease;
}
.cierre-ag-btn-finalizar:hover {
    color: #fff; transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(44,85,48,.36);
}
.cierre-ag-btn-finalizar__icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center;
    font-size: .82rem;
}
.cierre-ag-finalizar-wrap { margin-top: .25rem; text-align: center; }
.cierre-ag-progress-wrap { margin-top: .75rem; }
.cierre-ag-readonly-tag {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    color: #64748b; background: #f1f5f9; border-radius: 999px; padding: .2rem .55rem; margin-bottom: .75rem;
}
.cierre-ag-resumen-stack .cierre-ag-card { margin-bottom: 1rem; }
.cierre-ag-resumen-stack .cierre-ag-card:last-child { margin-bottom: 0; }
.cierre-ag-detalle-table { width: 100%; border-collapse: collapse; margin-top: .75rem; font-size: .84rem; }
.cierre-ag-detalle-table th,
.cierre-ag-detalle-table td { padding: .45rem .65rem; border: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
.cierre-ag-detalle-table th { background: #f8fafc; font-size: .72rem; text-transform: uppercase; color: #64748b; }
.cierre-ag-detalle-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: .65rem; margin-top: .75rem; }
.cierre-ag-detalle-meta__item { padding: .65rem .75rem; border: 1px solid #e2e8f0; border-radius: 8px; background: #fafbfc; }
.cierre-ag-detalle-meta__label { display: block; font-size: .68rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: .15rem; }
.cierre-ag-firma-preview { max-height: 90px; max-width: 220px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; margin-top: .5rem; }
.cierre-ag-resumen-banner {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .65rem;
    padding: .75rem 1rem; margin-bottom: 1rem;
    background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px;
    font-size: .84rem; color: #065f46;
}
@media (max-width: 575px) {
    .cierre-ag-toolbar { flex-direction: column; }
    .cierre-ag-toolbar__back { border-right: 0; border-bottom: 1px solid #e2e8f0; justify-content: flex-start; }
    .cierre-ag-step { min-width: 92px; }
}
</style>
@endpush

@section('content')
@php
    use App\Support\EnvioCierreAgricolaCatalogo as CierreCat;
    $paso = $pasoVista ?? ($resumen['paso_actual'] ?? CierreCat::PASO_CONDICIONES);
    $pasoActual = $pasoActual ?? ($resumen['paso_actual'] ?? CierreCat::PASO_CONDICIONES);
    $modoConsulta = $modoConsulta ?? false;
    $vistaResumenCompleto = $vistaResumenCompleto ?? false;
    $pasosCompletados = $pasosCompletados ?? CierreCat::pasosCompletados($resumen);
    $documentoEntrega = $documentoEntrega ?? null;
    $codigo = $ruta->codigo ?? ('#'.$ruta->rutadistribucionid);
    $pasos = CierreCat::pasosOrdenados();
    $ordenActual = array_search($pasoActual, $pasos, true);
    $rutaPrefijo = $rutaPrefijo ?? 'logistica.traslados-planta';
    $panelUrl = route($rutaPrefijo.'.cierre.panel', $ruta);
    $mostrarPaso = static function (string $p) use ($vistaResumenCompleto, $paso): bool {
        return $vistaResumenCompleto || $paso === $p;
    };
    $soloLecturaPaso = static fn (): bool => $vistaResumenCompleto || ($modoConsulta ?? false);
    $numeroPaso = static function (string $p) use ($pasos): int {
        $idx = array_search($p, $pasos, true);
        return $idx !== false ? $idx + 1 : 0;
    };
    $esTransportista = \App\Support\UsuarioRol::esTransportista(auth()->user());
    $esVistaMayorista = $esVistaMayorista ?? false;
    $esVistaMinorista = $esVistaMinorista ?? false;
    $vistaMayoristaEspera = $vistaMayoristaEspera ?? false;
    $vistaMinoristaEspera = $vistaMinoristaEspera ?? false;
    $vistaReceptorEspera = $vistaMayoristaEspera || $vistaMinoristaEspera;
    $ui = $uiCierre ?? \App\Support\EnvioCierreRutaUiCatalogo::plantaMayorista();
    $esCierrePdv = ($ui['tipo_tiempo_real'] ?? '') === 'distribucion';
    $recibidoEnDestino = (bool) ($resumen[$ui['recibido_key']] ?? false);
    $estadoRecepcion = $estadoRecepcion ?? [];
    $etiquetaPaso = static function (string $p) use ($esTransportista): string {
        if ($esTransportista && $p === CierreCat::PASO_EN_RUTA) {
            return 'Salida';
        }
        return CierreCat::etiquetaPaso($p);
    };
@endphp

<div class="cierre-ag-page @if($vistaResumenCompleto) cierre-ag-page--resumen @endif">
    <div class="cierre-ag-toolbar">
        <a href="{{ $volverUrl }}" class="cierre-ag-toolbar__back" title="Volver al detalle del envío">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
        <div class="cierre-ag-toolbar__main">
            <div class="cierre-ag-toolbar__tipo"><i class="fas fa-industry mr-1"></i> {{ $ui['toolbar_titulo'] }}</div>
            <div class="cierre-ag-toolbar__codigo">{{ $codigo }}</div>
        </div>
    </div>

    @if($vistaReceptorEspera)
    <div class="cierre-ag-card mb-3">
        <div class="cierre-ag-card__head">
            <i class="fas fa-warehouse text-success"></i> {{ $ui['recepcion_titulo'] }}
        </div>
        <div class="cierre-ag-card__body">
            <div class="cierre-ag-status cierre-ag-status--llegada">
                <span class="cierre-ag-status__icon"><i class="fas fa-truck-loading"></i></span>
                <div class="w-100">
                    <strong class="d-block">{{ $estadoRecepcion['etiqueta'] ?? 'En proceso' }}</strong>
                    <span class="small text-muted d-block mt-1">{{ $estadoRecepcion['descripcion'] ?? '' }}</span>
                    <p class="small text-muted mb-0 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ $ui['recepcion_espera_info'] }}
                    </p>
                </div>
            </div>
            <a href="{{ $volverUrl }}" class="btn btn-outline-secondary btn-sm mt-3">
                <i class="fas fa-arrow-left mr-1"></i> Volver al detalle
            </a>
        </div>
    </div>
    @else

    <nav class="cierre-ag-stepper" aria-label="Pasos del cierre operativo">
        @foreach($pasos as $idx => $p)
            @php
                $num = $idx + 1;
                $completado = in_array($p, $pasosCompletados, true);
                $esPasoActualFlujo = $p === $pasoActual && ! $vistaResumenCompleto;
                $pasoConfirmado = $completado && ! $esPasoActualFlujo;
                $clickable = $pasoConfirmado && ! $vistaResumenCompleto;
                $clase = $vistaResumenCompleto
                    ? ($completado ? 'done' : 'pending')
                    : ($esPasoActualFlujo ? 'active' : ($pasoConfirmado ? 'done' : 'pending'));
            @endphp
            <div class="cierre-ag-step {{ $clase }}">
                @if($clickable)
                    <a href="{{ $panelUrl }}?paso={{ $p }}" class="cierre-ag-step__link" title="Ver paso confirmado">
                        <span class="cierre-ag-step__bubble">
                            <i class="fas fa-check"></i>
                        </span>
                        <span class="cierre-ag-step__label">{{ $etiquetaPaso($p) }}</span>
                    </a>
                @elseif($modoConsulta && $p === $pasoActual)
                    <a href="{{ $panelUrl }}" class="cierre-ag-step__link" title="Ir al paso actual del flujo">
                        <span class="cierre-ag-step__bubble">
                            <span class="cierre-ag-step__dot" aria-hidden="true"></span>
                        </span>
                        <span class="cierre-ag-step__label">{{ $etiquetaPaso($p) }}</span>
                    </a>
                @else
                    <span class="cierre-ag-step__link" style="cursor: default;">
                        <span class="cierre-ag-step__bubble">
                            @if($pasoConfirmado)
                                <i class="fas fa-check"></i>
                            @elseif($esPasoActualFlujo)
                                <span class="cierre-ag-step__dot" aria-hidden="true"></span>
                            @else
                                {{ $num }}
                            @endif
                        </span>
                        <span class="cierre-ag-step__label">{{ $etiquetaPaso($p) }}</span>
                    </span>
                @endif
            </div>
        @endforeach
    </nav>

    @if($vistaResumenCompleto)
        <div class="cierre-ag-resumen-banner">
            <span><i class="fas fa-clipboard-list mr-1"></i> Resumen completo del cierre operativo — todos los pasos registrados.</span>
            @if($documentoEntrega)
                <a href="{{ route('logistica.documentos.show', $documentoEntrega) }}" class="btn btn-sm btn-outline-success font-weight-bold">
                    <i class="fas fa-file-pdf mr-1"></i> Ver documento PDF
                </a>
            @endif
        </div>
    @elseif($modoConsulta)
        <div class="cierre-ag-consulta">
            <span><i class="fas fa-eye mr-1"></i> Modo consulta — este paso ya fue confirmado.</span>
            <a href="{{ $panelUrl }}" class="btn btn-sm btn-primary font-weight-bold">
                <i class="fas fa-arrow-right mr-1"></i> Ir al paso actual
            </a>
        </div>
    @endif

    <div class="@if($vistaResumenCompleto) cierre-ag-resumen-stack @endif">

    {{-- Paso 1: Condiciones --}}
    @if($mostrarPaso(CierreCat::PASO_CONDICIONES))
    <div class="cierre-ag-card">
        <div class="cierre-ag-card__head">
            <span class="cierre-ag-card__head-num">{{ $numeroPaso(CierreCat::PASO_CONDICIONES) }}</span>
            <i class="fas fa-clipboard-check text-success"></i> Condiciones del vehículo
        </div>
        <div class="cierre-ag-card__body">
            @if($soloLecturaPaso())<div class="cierre-ag-readonly-tag"><i class="fas fa-lock"></i> Solo lectura</div>@endif
            @if($resumen['tiene_condiciones'] ?? false)
                <div class="cierre-ag-status cierre-ag-status--ok">
                    <span class="cierre-ag-status__icon"><i class="fas fa-clipboard-check"></i></span>
                    <div class="w-100">
                        <strong class="d-block text-success">Condiciones registradas</strong>
                        <span class="small text-muted">
                            {{ \App\Support\EnvioCierreAgricolaCatalogo::etiquetaEstadoVehiculo($ruta->checklistCondicionVehiculo?->estado_general) }}
                        </span>
                        @if($ruta->checklistCondicionVehiculo?->fecha_revision)
                            <div class="small text-muted mt-1">
                                Revisión: {{ $ruta->checklistCondicionVehiculo->fecha_revision->format('d/m/Y H:i') }}
                            </div>
                        @endif
                        @if($ruta->vehiculo?->placa)
                            <div class="small text-muted">Vehículo: <strong>{{ $ruta->vehiculo->placa }}</strong></div>
                        @endif
                        @if($ruta->checklistCondicionVehiculo?->observaciones)
                            <p class="small text-muted mb-0 mt-2">{{ $ruta->checklistCondicionVehiculo->observaciones }}</p>
                        @endif
                        @if($ruta->checklistCondicionVehiculo?->detalles?->isNotEmpty())
                            <table class="cierre-ag-detalle-table">
                                <thead>
                                    <tr><th>Condición</th><th style="width:18%">Estado</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($ruta->checklistCondicionVehiculo->detalles as $det)
                                    <tr>
                                        <td>{{ $det->condicion?->titulo ?? 'Condición' }}</td>
                                        <td @if(! $det->valor) class="text-danger font-weight-bold" @endif>{{ $det->valor ? 'Sí' : 'No' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @elseif(($resumen['puede_registrar_condiciones'] ?? false) && ! $soloLecturaPaso())
                <p class="text-muted small mb-3">Revise el vehículo antes de salir o use el atajo si está en perfectas condiciones.</p>
                <form method="POST" action="{{ route($rutaPrefijo.'.cierre.condiciones', $ruta) }}" id="form-condiciones-detalle">
                    @csrf
                    @foreach($condicionesCatalogo as $cond)
                    <div class="cierre-ag-check-row">
                        <span class="cierre-ag-check-row__label">{{ $cond->titulo }}</span>
                        <div class="cierre-ag-toggle" role="group">
                            <label class="cierre-ag-toggle__btn cierre-ag-toggle__btn--si active mb-0">
                                <input type="radio" name="condiciones[{{ $loop->index }}][valor]" value="1"
                                       data-cond-id="{{ $cond->condiciontransporteid }}" class="cond-radio-si" checked> Sí
                            </label>
                            <label class="cierre-ag-toggle__btn cierre-ag-toggle__btn--no mb-0">
                                <input type="radio" name="condiciones[{{ $loop->index }}][valor]" value="0"
                                       data-cond-id="{{ $cond->condiciontransporteid }}" class="cond-radio-no"> No
                            </label>
                            <input type="hidden" name="condiciones[{{ $loop->index }}][id]" value="{{ $cond->condiciontransporteid }}">
                        </div>
                    </div>
                    @endforeach
                    <div class="form-group mt-3 mb-0">
                        <label class="small font-weight-bold">Observación general (opcional)</label>
                        <textarea name="observaciones" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Detalle si marcó alguna condición en «No»"></textarea>
                    </div>
                    <div class="cierre-ag-form-actions">
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Guardar revisión
                        </button>
                        <button type="submit" form="form-condiciones-atajo" class="btn btn-sm cierre-ag-atajo">
                            <i class="fas fa-thumbs-up mr-1"></i> Perfectas condiciones
                        </button>
                    </div>
                </form>
                <form method="POST" action="{{ route($rutaPrefijo.'.cierre.condiciones', $ruta) }}" id="form-condiciones-atajo" class="d-none">
                    @csrf
                    <input type="hidden" name="perfectas_condiciones" value="1">
                </form>
            @else
                <p class="text-muted mb-0 small">{{ $vistaResumenCompleto ? 'No se registraron condiciones del vehículo en este traslado.' : 'Complete este paso desde el flujo activo del envío.' }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Paso 2: En ruta --}}
    @if($mostrarPaso(CierreCat::PASO_EN_RUTA))
    <div class="cierre-ag-card">
        <div class="cierre-ag-card__head">
            <span class="cierre-ag-card__head-num">{{ $numeroPaso(CierreCat::PASO_EN_RUTA) }}</span>
            <i class="fas fa-shipping-fast text-primary"></i> {{ $esTransportista ? 'Iniciar recorrido' : 'Marcar en ruta' }}
        </div>
        <div class="cierre-ag-card__body">
            @if($soloLecturaPaso())<div class="cierre-ag-readonly-tag"><i class="fas fa-lock"></i> Solo lectura</div>@endif
            @if($resumen['en_ruta'] ?? false)
                <div class="cierre-ag-status cierre-ag-status--ruta">
                    <span class="cierre-ag-status__icon"><i class="fas fa-shipping-fast"></i></span>
                    <div class="w-100">
                        @if($esTransportista)
                            <strong class="d-block text-success">Recorrido iniciado</strong>
                            <span class="small text-muted">Avance al paso «Confirmar llegada» cuando esté en {{ $ui['destino_articulo'] }}.</span>
                        @else
                        <strong class="d-block text-success">Recorrido en curso</strong>
                        <span class="small text-muted">Avance: {{ number_format((float) ($resumen['progreso'] ?? 0), 0) }}% hacia {{ $ui['destino_articulo'] }}.</span>
                        @endif
                        @if($ruta->fecha_salida)
                            <div class="small text-muted mt-1">Salida del {{ $ui['origen_salida'] }}: {{ $ruta->fecha_salida->format('d/m/Y H:i') }}</div>
                        @endif
                        @if(! $soloLecturaPaso() && ! $esTransportista)
                        <div class="cierre-ag-progress-wrap">
                            @include('logistica.partials.progreso-simulacion-transportista', [
                                'tipo' => $ui['tipo_tiempo_real'],
                                'id' => $ruta->rutadistribucionid,
                            ])
                        </div>
                        @endif
                    </div>
                </div>
            @elseif(($resumen['puede_empezar_ruta'] ?? false) && ! $soloLecturaPaso())
                <p class="small text-muted mb-3">Condiciones registradas. Inicie el recorrido hacia {{ $ui['destino_articulo'] }} cuando salga del {{ $ui['origen_salida'] }}.</p>
                <div class="cierre-ag-ruta-actions">
                    @if($esCierrePdv)
                        @include('logistica.partials.accion-empezar-ruta-cierre-distribucion-pdv', [
                            'ruta' => $ruta,
                            'rutaPrefijo' => $rutaPrefijo,
                            'bloque' => true,
                        ])
                    @else
                        @include('logistica.partials.accion-empezar-ruta-cierre-traslado', ['ruta' => $ruta, 'bloque' => true])
                    @endif
                    @include('logistica.partials.btn-ver-recorrido-mapa', [
                        'paradasMapa' => $paradasMapa ?? [],
                        'bloque' => true,
                        'clase' => 'btn-outline-primary font-weight-bold',
                        'etiqueta' => 'Ver ruta',
                    ])
                </div>
            @elseif(in_array(CierreCat::PASO_EN_RUTA, $pasosCompletados, true))
                <div class="cierre-ag-status cierre-ag-status--ruta">
                    <span class="cierre-ag-status__icon"><i class="fas fa-shipping-fast"></i></span>
                    <div class="w-100">
                        <strong class="d-block text-success">Salida registrada</strong>
                        <span class="small text-muted">El envío salió del {{ $ui['origen_salida'] }} hacia {{ $ui['destino_articulo'] }}.</span>
                        <div class="cierre-ag-detalle-meta">
                            @if($ruta->fecha_salida)
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Salida del {{ $ui['origen_salida'] }}</span>
                                {{ $ruta->fecha_salida->format('d/m/Y H:i') }}
                            </div>
                            @endif
                            @if($ruta->vehiculo?->placa)
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Vehículo</span>
                                {{ $ruta->vehiculo->placa }}
                            </div>
                            @endif
                            @if(! $esCierrePdv && $ruta->almacenMayoristaDestino?->nombre)
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Destino</span>
                                {{ $ruta->almacenMayoristaDestino->nombre }}
                            </div>
                            @elseif($esCierrePdv)
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Destino</span>
                                {{ $ui['destino_label'] }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <p class="text-muted mb-0 small">Complete las condiciones del vehículo para continuar.</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Paso 3: Confirmar llegada --}}
    @if($mostrarPaso(CierreCat::PASO_ESPERA_LLEGADA))
    <div class="cierre-ag-card">
        <div class="cierre-ag-card__head">
            <span class="cierre-ag-card__head-num">{{ $numeroPaso(CierreCat::PASO_ESPERA_LLEGADA) }}</span>
            <i class="fas fa-map-marker-alt"></i> {{ $ui['llegada_titulo'] }}
        </div>
        <div class="cierre-ag-card__body">
            @if($soloLecturaPaso())<div class="cierre-ag-readonly-tag"><i class="fas fa-lock"></i> Solo lectura</div>@endif
            @if($resumen['llegada_confirmada'] ?? false)
                <div class="cierre-ag-status cierre-ag-status--llegada">
                    <span class="cierre-ag-status__icon"><i class="fas fa-map-marker-alt"></i></span>
                    <div class="w-100">
                        <strong class="d-block text-success">Llegada confirmada</strong>
                        <span class="small text-muted">{{ $ruta->llegada_confirmada_at?->format('d/m/Y H:i') }}</span>
                        <div class="cierre-ag-detalle-meta">
                            @if($ruta->llegadaConfirmadaPor)
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Confirmado por</span>
                                {{ trim(($ruta->llegadaConfirmadaPor->nombre ?? '').' '.($ruta->llegadaConfirmadaPor->apellido ?? '')) }}
                            </div>
                            @endif
                            @if($ruta->almacenMayoristaDestino?->nombre)
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Almacén destino</span>
                                {{ $ruta->almacenMayoristaDestino->nombre }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif(($resumen['puede_confirmar_llegada'] ?? false) && ! $soloLecturaPaso() && ! $esTransportista)
                <p class="small text-muted mb-3">El camión llegó al destino. Confirme la llegada para continuar con el cierre.</p>
                <form method="POST" action="{{ route($rutaPrefijo.'.cierre.confirmar-llegada', $ruta) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning font-weight-bold">
                        <i class="fas fa-flag-checkered mr-1"></i> Confirmar llegada
                    </button>
                </form>
            @elseif(($resumen['esperando_confirmacion'] ?? false) && ! $soloLecturaPaso() && ! $esTransportista)
                <div class="cierre-ag-llegada-hint">
                    <i class="fas fa-map-marker-alt mt-1"></i>
                    <span><strong>Esperando recepción.</strong> El recorrido llegó al destino; confirme la llegada para continuar.</span>
                </div>
                <form method="POST" action="{{ route($rutaPrefijo.'.cierre.confirmar-llegada', $ruta) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning font-weight-bold">
                        <i class="fas fa-flag-checkered mr-1"></i> Confirmar llegada
                    </button>
                </form>
            @elseif(! $soloLecturaPaso() && $esTransportista)
                @include('logistica.partials.cierre-llegada-transportista', [
                    'resumen' => $resumen,
                    'confirmarUrl' => route($rutaPrefijo.'.cierre.confirmar-llegada', $ruta),
                    'mensajeEspera' => $ui['mensaje_espera_transportista'],
                ])
            @elseif(($resumen['en_ruta'] ?? false) && ! $soloLecturaPaso())
                <div class="cierre-ag-llegada-hint">
                    <i class="fas fa-info-circle mt-1"></i>
                    <span>En camino hacia {{ $ui['destino_articulo'] }} ({{ number_format((float) ($resumen['progreso'] ?? 0), 0) }}%). Primero debe llegar al destino para confirmar la llegada.</span>
                </div>
                <div class="cierre-ag-progress-wrap">
                    @include('logistica.partials.progreso-simulacion-transportista', [
                        'tipo' => 'planta_mayorista',
                        'id' => $ruta->rutadistribucionid,
                    ])
                </div>
            @else
                <p class="text-muted small mb-0">Disponible cuando el envío esté en ruta y haya llegado al destino.</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Paso 4: Incidentes --}}
    @if($mostrarPaso(CierreCat::PASO_INCIDENTES))
    <div class="cierre-ag-card">
        <div class="cierre-ag-card__head">
            <span class="cierre-ag-card__head-num">{{ $numeroPaso(CierreCat::PASO_INCIDENTES) }}</span>
            <i class="fas fa-exclamation-triangle text-warning"></i> Incidentes de transporte
        </div>
        <div class="cierre-ag-card__body">
            @if($soloLecturaPaso())<div class="cierre-ag-readonly-tag"><i class="fas fa-lock"></i> Solo lectura</div>@endif
            @if($resumen['tiene_incidentes'] ?? false)
                @php
                    $obsIncidentes = \App\Support\IncidenteTransporteCatalogo::observacionDesdeChecklist($ruta->checklistIncidente);
                @endphp
                <div class="cierre-ag-status cierre-ag-status--incidente">
                    <span class="cierre-ag-status__icon"><i class="fas fa-clipboard-list"></i></span>
                    <div class="w-100">
                        <strong class="d-block text-success">Registro completado</strong>
                        @if($obsIncidentes['alerta'] ?? false)
                            <div class="alert alert-danger small py-2 px-3 mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $obsIncidentes['texto'] }}
                            </div>
                        @else
                            <span class="small text-muted">{{ $obsIncidentes['texto'] ?: 'Transporte sin incidentes reportados.' }}</span>
                        @endif
                        @if($ruta->checklistIncidente?->detalles?->isNotEmpty())
                            <table class="cierre-ag-detalle-table">
                                <thead>
                                    <tr><th>Incidente</th><th style="width:18%">Ocurrió</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($ruta->checklistIncidente->detalles as $det)
                                    <tr>
                                        <td>{{ $det->tipoIncidente?->titulo ?? 'Incidente' }}</td>
                                        <td @if($det->ocurrio) class="text-danger font-weight-bold" @endif>{{ $det->ocurrio ? 'Sí' : 'No' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                        @if($ruta->checklistIncidente?->observaciones)
                            <p class="small text-muted mb-0 mt-2"><strong>Observación:</strong> {{ $ruta->checklistIncidente->observaciones }}</p>
                        @endif
                    </div>
                </div>
            @elseif(($resumen['puede_registrar_incidentes'] ?? false) && ! $soloLecturaPaso())
                <form method="POST" action="{{ route($rutaPrefijo.'.cierre.incidentes', $ruta) }}" id="form-incidentes-detalle">
                    @csrf
                    <div class="cierre-ag-incidentes-grid">
                    @foreach($incidentesCatalogo as $inc)
                    <div class="cierre-ag-incidente-card">
                        <span class="cierre-ag-incidente-card__label">{{ $inc->titulo }}</span>
                        <div class="custom-control custom-checkbox mb-0">
                            <input type="checkbox" class="custom-control-input inc-check"
                                   id="inc-{{ $inc->tipoincidentetransporteid }}"
                                   name="incidentes[{{ $loop->index }}][ocurrio]" value="1">
                            <input type="hidden" name="incidentes[{{ $loop->index }}][id]" value="{{ $inc->tipoincidentetransporteid }}">
                            <label class="custom-control-label" for="inc-{{ $inc->tipoincidentetransporteid }}">Ocurrió</label>
                        </div>
                    </div>
                    @endforeach
                    </div>
                    <div class="form-group mt-3 mb-0">
                        <label class="small font-weight-bold">Observación general</label>
                        <textarea name="observaciones" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Detalle si marcó algún incidente"></textarea>
                    </div>
                    <div class="cierre-ag-form-actions">
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Guardar incidentes
                        </button>
                        <button type="submit" form="form-incidentes-atajo" class="btn btn-sm cierre-ag-atajo">
                            <i class="fas fa-clipboard-check mr-1"></i> Sin incidentes
                        </button>
                    </div>
                </form>
                <form method="POST" action="{{ route($rutaPrefijo.'.cierre.incidentes', $ruta) }}" id="form-incidentes-atajo" class="d-none">
                    @csrf
                    <input type="hidden" name="sin_incidentes" value="1">
                </form>
            @else
                <p class="text-muted small mb-0">Confirme la llegada a {{ $ui['destino_articulo'] }} para continuar.</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Paso 5: Firma transportista --}}
    @if($mostrarPaso(CierreCat::PASO_FIRMA_TRANSPORTISTA))
    <div class="cierre-ag-card">
        <div class="cierre-ag-card__head">
            <span class="cierre-ag-card__head-num">{{ $numeroPaso(CierreCat::PASO_FIRMA_TRANSPORTISTA) }}</span>
            <i class="fas fa-signature"></i> Firma del transportista
        </div>
        <div class="cierre-ag-card__body">
            @if($soloLecturaPaso())<div class="cierre-ag-readonly-tag"><i class="fas fa-lock"></i> Solo lectura</div>@endif
            @if($resumen['firma_transportista'] ?? false)
                <div class="cierre-ag-status cierre-ag-status--firma">
                    <span class="cierre-ag-status__icon"><i class="fas fa-signature"></i></span>
                    <div class="w-100">
                        <strong class="d-block text-success">Firma registrada</strong>
                        <span class="small text-muted">{{ $ruta->firmaTransportista?->fechafirma?->format('d/m/Y H:i') ?? 'Confirmada correctamente.' }}</span>
                        @if($ruta->firmaTransportista?->imagenfirma)
                            <img src="{{ $ruta->firmaTransportista->imagenfirma }}" alt="Firma transportista" class="cierre-ag-firma-preview">
                        @endif
                    </div>
                </div>
            @elseif(($resumen['puede_firmar_transportista'] ?? false) && ! $soloLecturaPaso())
                <p class="small text-muted mb-3">Firme como transportista para confirmar la entrega de la carga.</p>
                <canvas class="cierre-ag-firma-box" data-firma-canvas="transportista" width="400" height="160"></canvas>
                <div class="cierre-ag-firma-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-limpiar-firma" data-target="transportista">Limpiar</button>
                    <button type="button" class="btn btn-success btn-sm font-weight-bold btn-guardar-firma"
                            data-target="transportista"
                            data-url="{{ route($rutaPrefijo.'.cierre.firma-transportista', $ruta) }}">
                        Guardar firma transportista
                    </button>
                </div>
            @else
                <p class="text-muted small mb-0">Complete el registro de incidentes para continuar.</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Paso 6: Firma recepción --}}
    @if($mostrarPaso(CierreCat::PASO_FIRMA_RECEPCION))
    <div class="cierre-ag-card">
        <div class="cierre-ag-card__head">
            <span class="cierre-ag-card__head-num">{{ $numeroPaso(CierreCat::PASO_FIRMA_RECEPCION) }}</span>
            {{ $ui['firma_recepcion_titulo'] }}
        </div>
        <div class="cierre-ag-card__body">
            @if($soloLecturaPaso() && ! ($resumen['puede_finalizar'] ?? false))
                <div class="cierre-ag-readonly-tag"><i class="fas fa-lock"></i> Solo lectura</div>
            @endif
            @if($resumen['firma_recepcion'] ?? false)
                <div class="cierre-ag-status cierre-ag-status--firma mb-3">
                    <span class="cierre-ag-status__icon"><i class="fas fa-file-signature"></i></span>
                    <div class="w-100">
                        <strong class="d-block text-success">Firma de recepción registrada</strong>
                        <span class="small text-muted">{{ $ruta->firmaRecepcion?->fechafirma?->format('d/m/Y H:i') ?? 'Confirmada correctamente.' }}</span>
                        @if($ruta->firmaRecepcion?->imagenfirma)
                            <img src="{{ $ruta->firmaRecepcion->imagenfirma }}" alt="Firma recepción" class="cierre-ag-firma-preview">
                        @endif
                    </div>
                </div>
                @if(($resumen['puede_finalizar'] ?? false) && ! $soloLecturaPaso())
                <div class="cierre-ag-finalizar-wrap">
                <form method="POST" action="{{ route($rutaPrefijo.'.cierre.finalizar', $ruta) }}" id="form-finalizar-cierre-traslado"
                      data-confirm-tone="success"
                      data-confirm-title="Finalizar traslado"
                      data-confirm-btn="Finalizar traslado"
                      data-confirm-message="{{ $ui['mensaje_finalizar'] }}">
                    @csrf
                    <button type="button" class="cierre-ag-btn-finalizar" data-confirm-submit>
                        <span class="cierre-ag-btn-finalizar__icon"><i class="fas fa-flag-checkered"></i></span>
                        Finalizar entrega
                    </button>
                </form>
                </div>
                @endif
            @elseif($recibidoEnDestino)
                <div class="cierre-ag-status cierre-ag-status--entrega">
                    <span class="cierre-ag-status__icon"><i class="fas fa-box-open"></i></span>
                    <div>
                        <strong class="d-block text-success">Entrega finalizada</strong>
                        <span class="small text-muted">{{ $ui['recibido_mensaje'] }}</span>
                    </div>
                </div>
            @elseif(($resumen['puede_firmar_recepcion'] ?? false) && ! $soloLecturaPaso())
                @if(! $esTransportista)
                <p class="small text-muted mb-3"><i class="fas fa-signature mr-1" style="color:#7c3aed;"></i> {{ $ui['firma_recepcion_hint'] }}</p>
                @endif
                <canvas class="cierre-ag-firma-box" data-firma-canvas="recepcion" width="400" height="160"></canvas>
                <div class="cierre-ag-firma-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-limpiar-firma" data-target="recepcion">Limpiar</button>
                    <button type="button" class="btn btn-success btn-sm font-weight-bold btn-guardar-firma"
                            data-target="recepcion"
                            data-url="{{ route($rutaPrefijo.'.cierre.firma-recepcion', $ruta) }}">
                        Guardar firma recepción
                    </button>
                </div>
            @else
                <p class="text-muted small mb-0">Primero debe firmar el transportista asignado.</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Paso 7: Completado --}}
    @if($mostrarPaso(CierreCat::PASO_COMPLETADO))
    <div class="cierre-ag-card">
        <div class="cierre-ag-card__head">
            <span class="cierre-ag-card__head-num">{{ $numeroPaso(CierreCat::PASO_COMPLETADO) }}</span>
            <i class="fas fa-box-open text-success"></i> Entrega finalizada
        </div>
        <div class="cierre-ag-card__body @if(! $vistaResumenCompleto) text-center py-4 @endif">
            @if($vistaResumenCompleto)
                <div class="cierre-ag-status cierre-ag-status--entrega">
                    <span class="cierre-ag-status__icon"><i class="fas fa-check-double"></i></span>
                    <div class="w-100">
                        <strong class="d-block text-success">Cierre operativo completado</strong>
                        <span class="small text-muted">{{ $ui['documento_mensaje'] }}</span>
                        <div class="cierre-ag-detalle-meta">
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Código</span>
                                {{ $codigo }}
                            </div>
                            @if($ruta->fecha_salida)
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Salida</span>
                                {{ $ruta->fecha_salida->format('d/m/Y H:i') }}
                            </div>
                            @endif
                            @if($ruta->llegada_confirmada_at)
                            <div class="cierre-ag-detalle-meta__item">
                                <span class="cierre-ag-detalle-meta__label">Llegada</span>
                                {{ $ruta->llegada_confirmada_at->format('d/m/Y H:i') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <i class="fas fa-box-open text-success mb-3" style="font-size:2.5rem;"></i>
                <p class="font-weight-bold mb-1">Cierre operativo completado</p>
                <p class="text-muted small mb-3">{{ $ui['documento_mensaje'] }}</p>
            @endif
            <div class="@if(! $vistaResumenCompleto) @else mt-3 @endif">
                @if($documentoEntrega)
                    <a href="{{ route('logistica.documentos.show', $documentoEntrega) }}" class="btn btn-outline-success btn-sm mr-2">
                        <i class="fas fa-file-pdf mr-1"></i> Ver documento PDF
                    </a>
                @endif
                <a href="{{ $volverUrl }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al envío
                </a>
            </div>
        </div>
    </div>
    @endif
    </div>
    @endif
</div>

@include('partials.modal-confirmar-accion')

@if(count($paradasMapa ?? []) >= 1)
@include('logistica.partials.mapa-recorrido-modal', [
    'paradasMapa' => $paradasMapa,
    'trayectoPartes' => null,
])
@endif
@endsection

@push('scripts')
<script src="{{ asset('js/firma-canvas.js') }}?v=2"></script>
<script>
document.querySelectorAll('.cond-radio-si, .cond-radio-no').forEach(function (radio) {
    radio.addEventListener('change', function () {
        var toggle = radio.closest('.cierre-ag-toggle');
        if (!toggle) return;
        toggle.querySelectorAll('.cierre-ag-toggle__btn').forEach(function (btn) { btn.classList.remove('active'); });
        if (radio.checked) radio.closest('.cierre-ag-toggle__btn').classList.add('active');
    });
});
</script>
@endpush
