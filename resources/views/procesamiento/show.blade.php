@extends('layouts.app')

@section('title', 'Trazabilidad — '.$lote->nombre.' | AgroFusion')
@section('page_title', $lote->nombre)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procesamiento.index') }}">Procesamiento de Lote</a></li>
    <li class="breadcrumb-item active">{{ $lote->nombre }}</li>
@endsection

@push('styles')
    @include('lotes.partials.trazabilidad-styles')
    @include('partials.almacen-envio-styles')
    <style>
    .lp-header { background: linear-gradient(135deg, #1e4620, #4a7c59); color: #fff; border-radius: 14px; padding: 1.25rem 1.5rem; margin-bottom: 1.25rem; }
    .lp-header-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; margin: -.25rem; }
    .lp-header-actions > * { margin: .35rem; }
    .lp-header-actions .btn { font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,.12); border: 0; }
    .lp-header-actions .btn-warning { background: #f59e0b; color: #fff; }
    .lp-header-actions .btn-warning:hover { background: #d97706; color: #fff; }
    .lp-header-actions .btn-danger { background: #dc2626; color: #fff; }
    .lp-header-actions .btn-danger:hover { background: #b91c1c; color: #fff; }
    .lp-header-actions .btn-light { background: #fff; color: #1e4620; }
    .lp-header-actions .btn-light:hover { background: #f0fdf4; color: #1e4620; }
    .lp-paso { border: 1px solid #e2ebe3; border-radius: 10px; padding: .75rem 1rem; margin-bottom: .5rem; background: #fff; }
    .lp-paso.done { border-color: #28a745; background: #f0fdf4; }
    .lp-paso.cierre { border-color: #0ea5e9; background: #f0f9ff; }
    .lp-paso.pending { opacity: .85; }
    .maq-etapa-card {
        border: 2px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff;
        cursor: pointer; transition: all .2s ease; height: 100%;
        display: flex; flex-direction: column;
    }
    .maq-etapa-card:hover { border-color: #28a745; box-shadow: 0 4px 14px rgba(40,167,69,.15); }
    .maq-etapa-card.is-selected { border-color: #28a745; background: #f0fdf4; }
    .maq-etapa-card__img {
        height: 220px; background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; padding: .65rem;
    }
    .maq-etapa-card__img img {
        width: 100%; height: 100%; object-fit: contain; object-position: center;
        image-rendering: auto;
    }
    .maq-etapa-card__img i { font-size: 2.5rem; color: #94a3b8; }
    .maq-etapa-card__body { padding: .75rem .85rem; flex: 1; }
    .maq-etapa-card__nombre { font-weight: 700; color: #1e293b; font-size: .9rem; }
    .maq-etapa-card__meta { font-size: .75rem; color: #64748b; margin-top: .2rem; }
    .maq-etapa-card__desc { font-size: .78rem; color: #475569; margin-top: .45rem; line-height: 1.4; }
    .lp-etapa-asignar-row { align-items: flex-start; }
    .lp-etapa-asignar-row .lp-etapa-field {
        display: flex; flex-direction: column; margin-bottom: 0;
    }
    .lp-etapa-asignar-row .lp-etapa-label,
    .lp-etapa-asignar-row .selector-catalogo-wrapper > label {
        min-height: 1.2rem; margin-bottom: .35rem; line-height: 1.2;
    }
    .lp-etapa-asignar-row .lp-etapa-control { flex: 1 1 auto; width: 100%; }
    .lp-etapa-asignar-row .selector-catalogo-wrapper.form-group { margin-bottom: 0; width: 100%; }
    .lp-etapa-asignar-row .lp-proceso-fijo {
        min-height: 38px;
        border-radius: 10px;
        padding: .45rem .75rem;
    }
    .lp-etapa-asignar-row .form-control.form-control-sm { min-height: 38px; border-radius: 10px; }
    .lp-etapa-field {
        display: flex; flex-direction: column; margin-bottom: 1rem;
    }
    .lp-etapa-field .lp-etapa-label {
        min-height: 1.2rem; margin-bottom: .35rem; line-height: 1.2;
    }
    .lp-etapa-field .lp-etapa-control { flex: 0 0 auto; }
    .lp-etapa-field .lp-etapa-hint {
        font-size: .75rem; color: #6c757d; min-height: 2.55rem;
        margin-top: .35rem; margin-bottom: 0; line-height: 1.35;
    }
    .lp-maquina-slot { min-height: calc(1.8125rem + 2px); }
    .lp-maquina-filled {
        display: flex; gap: .55rem; align-items: center;
        border: 1px solid #ced4da; border-radius: .2rem;
        padding: .3rem .4rem; background: #fff;
        min-height: calc(1.8125rem + 2px);
    }
    .lp-maquina-filled__media {
        width: 52px; height: 52px; flex-shrink: 0;
        background: #f8fafc; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; border: 1px solid #e2e8f0;
    }
    .lp-maquina-filled__media img {
        width: 100%; height: 100%; object-fit: contain; object-position: center;
    }
    .lp-maquina-filled__body { min-width: 0; flex: 1; }
    .lp-maquina-filled__body .btn { font-size: .72rem; }
    #selector_wrap_etapa_maquinaria.is-bloqueado .selector-filtros-field__open,
    #selector_wrap_etapa_maquinaria.is-bloqueado .selector-filtros-field__input { pointer-events: none; opacity: .55; }

    .lp-timeline-num {
        width: 28px; height: 28px; border-radius: 50%; background: #2c5530; color: #fff;
        display: inline-flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; margin-right: .65rem;
    }
    .lp-form-etapa { background: #f8faf8; border: 1px solid #e2ebe3; border-radius: 12px; padding: 1rem 1.15rem; }
    .lp-fase-card { border: 0; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.06); margin-bottom: 1.25rem; overflow: hidden; }
    .lp-fase-card .card-header { border-bottom: 1px solid #e8f0e9; padding: .9rem 1.15rem; }
    .lp-resumen-card {
        border: 1px solid #e2ebe3; border-radius: 12px; overflow: hidden; height: 100%;
        background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.05);
    }
    .lp-resumen-card .lp-resumen-head {
        padding: .7rem 1rem; font-weight: 700; font-size: .82rem;
        text-transform: uppercase; letter-spacing: .04em;
        display: flex; align-items: center; gap: .5rem;
    }
    .lp-resumen-card .lp-resumen-body { padding: 1rem 1.1rem; }
    .lp-resumen-mp .lp-resumen-head { background: linear-gradient(135deg, #e8f5e9, #f0fdf4); color: #1e4620; }
    .lp-resumen-cert .lp-resumen-head { background: linear-gradient(135deg, #5b21b6, #7c3aed); color: #fff; }
    .lp-resumen-alm .lp-resumen-head { background: linear-gradient(135deg, #c2410c, #ea580c); color: #fff; }
    .lp-resumen-item {
        display: flex; justify-content: space-between; align-items: flex-start;
        gap: .75rem; padding: .55rem 0; border-bottom: 1px solid #f1f5f1;
    }
    .lp-resumen-item:last-child { border-bottom: 0; padding-bottom: 0; }
    .lp-resumen-item .nombre { font-weight: 600; color: #1a252f; font-size: .9rem; }
    .lp-resumen-item .dato { font-weight: 700; color: #2c5530; white-space: nowrap; }
    .lp-resumen-empty { color: #94a3b8; font-size: .88rem; text-align: center; padding: .5rem 0; }
    .lp-resumen-badge { font-size: .72rem; font-weight: 700; padding: .35rem .65rem; border-radius: 6px; }
    .lp-fases-toolbar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: .75rem; margin-bottom: 1rem;
    }
    .lp-fases-toolbar .lp-fase-activa-hint {
        font-size: .88rem; color: #495057; margin: 0;
    }
    .lp-fases-toolbar .lp-fase-activa-hint strong { color: #2c5530; }
    #lp-fases-workspace.lp-modo-todas-fases .lp-fase-panel--historial {
        display: block;
    }
    .lp-fase-panel { display: none; margin-bottom: 1.25rem; }
    .lp-fase-panel--activa { display: block; }
    .lp-fase-panel--historial { display: none; }
    .lp-fase-panel--historial .card-header { background: #f8faf8 !important; }
    .lp-fase-panel--historial .lp-form-etapa,
    .lp-fase-panel--historial form:not(.lp-historial-readonly) { display: none !important; }
    .lp-historial-badge {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: #6c757d;
    }
    .fase-step.fase-step--scroll { cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
    .fase-step.fase-step--scroll:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(44,85,48,.2); }
    .lp-proceso-timeline-wrap {
        background: linear-gradient(180deg, #f8fbf8 0%, #fff 100%);
        border: 1px solid #dce8dd;
        border-radius: 14px;
        padding: 1.1rem 1rem 1rem;
        margin-bottom: 1.25rem;
        overflow-x: auto;
    }
    .lp-proceso-timeline-wrap__head {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: .5rem; margin-bottom: 1rem;
    }
    .lp-proceso-timeline-wrap__head .titulo {
        font-size: .82rem; font-weight: 700; color: #1e4620;
    }
    .lp-proceso-timeline-wrap__head .sub {
        font-size: .72rem; color: #64748b;
    }
    .lp-proceso-timeline {
        display: flex; align-items: flex-start; min-width: min(100%, 720px);
        position: relative;
    }
    .lp-proceso-timeline__item {
        flex: 1 1 0; min-width: 110px; text-align: center; position: relative;
    }
    .lp-proceso-timeline__item:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 18px;
        left: calc(50% + 20px);
        width: calc(100% - 40px);
        height: 3px;
        background: #cbd5e1;
        border-radius: 999px;
        z-index: 0;
    }
    .lp-proceso-timeline__item.is-hecho:not(:last-child)::after { background: linear-gradient(90deg, #22c55e, #86efac); }
    .lp-proceso-timeline__item.is-en-curso:not(:last-child)::after { background: linear-gradient(90deg, #22c55e, #cbd5e1); }
    .lp-proceso-timeline__node {
        width: 38px; height: 38px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: .82rem;
        border: 3px solid #cbd5e1; background: #fff; color: #64748b;
        position: relative; z-index: 1; margin: 0 auto .55rem;
        box-shadow: 0 2px 8px rgba(15,23,42,.06);
    }
    .lp-proceso-timeline__item.is-hecho .lp-proceso-timeline__node {
        border-color: #22c55e; background: #22c55e; color: #fff;
    }
    .lp-proceso-timeline__item.is-actual .lp-proceso-timeline__node {
        border-color: #f59e0b; background: #fffbeb; color: #b45309;
        box-shadow: 0 0 0 4px rgba(245,158,11,.22);
    }
    .lp-proceso-timeline__item.is-en-curso .lp-proceso-timeline__node {
        border-color: #3b82f6; background: #eff6ff; color: #1d4ed8;
        box-shadow: 0 0 0 4px rgba(59,130,246,.18);
    }
    .lp-proceso-timeline__item.is-bloqueado .lp-proceso-timeline__node {
        border-color: #e2e8f0; background: #f8fafc; color: #94a3b8;
        opacity: .85;
    }
    .lp-proceso-timeline__label {
        font-size: .72rem; font-weight: 700; color: #1e293b; line-height: 1.25;
        padding: 0 .25rem;
    }
    .lp-proceso-timeline__item.is-bloqueado .lp-proceso-timeline__label { color: #94a3b8; }
    .lp-proceso-timeline__meta {
        font-size: .64rem; color: #64748b; margin-top: .25rem; line-height: 1.3;
        min-height: 1.5rem;
    }
    .lp-proceso-timeline__item.is-actual .lp-proceso-timeline__meta { color: #b45309; font-weight: 700; }
    .lp-proceso-timeline__item.is-bloqueado .lp-proceso-timeline__meta { color: #94a3b8; }
    .lp-proceso-timeline__meta .fa-lock { font-size: .58rem; }
    .lp-proceso-fijo {
        display: flex; align-items: center; gap: .45rem;
        background: #f1f5f9 !important; border-color: #cbd5e1 !important;
        color: #334155; font-weight: 700; cursor: not-allowed;
        min-height: calc(1.8125rem + 2px);
    }
    .lp-proceso-fijo .fa-lock { color: #64748b; font-size: .72rem; }
    .lp-proceso-fijo__badge {
        font-size: .62rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .03em; color: #64748b; margin-left: auto;
    }
    .lp-asignaciones-pendientes { margin-top: 1rem; }
    .lp-asignaciones-pendientes__titulo {
        font-size: .78rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: #92400e; margin-bottom: .65rem;
    }
    .lp-paso--pendiente {
        border-color: #fbbf24;
        background: linear-gradient(135deg, #fffbeb 0%, #fff 100%);
        align-items: center;
        gap: .75rem;
    }
    .lp-paso--pendiente .lp-timeline-num {
        background: #f59e0b;
        flex-shrink: 0;
    }
    .lp-paso--pendiente__accion { flex-shrink: 0; }
    .lp-paso--pendiente .lp-paso-meta {
        font-size: .8rem; color: #64748b; line-height: 1.45;
    }
    .lp-paso--pendiente .lp-paso-meta strong { color: #1e293b; }
    .lp-paso--entrada { animation: lpPasoEntrada .4s ease; }
    @keyframes lpPasoEntrada {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .lp-ajax-loading { opacity: .65; pointer-events: none; }
    </style>
@endpush

@section('content')
<script>
(function () {
    var key = 'lp_procesamiento_scroll';
    var saved = sessionStorage.getItem(key);
    if (saved === null) return;
    var y = parseInt(saved, 10);
    if (isNaN(y)) return;
    sessionStorage.removeItem(key);
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

    function restaurarScroll() {
        var html = document.documentElement;
        var prev = html.style.scrollBehavior;
        html.style.scrollBehavior = 'auto';
        window.scrollTo(0, y);
        html.style.scrollBehavior = prev;
    }

    restaurarScroll();
    document.addEventListener('DOMContentLoaded', restaurarScroll);
    window.addEventListener('load', function () {
        restaurarScroll();
        setTimeout(restaurarScroll, 120);
    });
})();
</script>
<div class="trz-dash">
    <div class="lp-header d-flex flex-wrap justify-content-between align-items-start">
        <div>
            <p class="mb-1 small opacity-90"><code class="text-white">{{ $lote->codigo_lote }}</code></p>
            <h4 class="mb-1 font-weight-bold">{{ $lote->nombre }}</h4>
            <p class="mb-0 small opacity-90">
                @if($lote->producto) Producto: {{ $lote->producto }} · @endif
                Pedido: {{ $lote->pedido?->numero_solicitud ?? '—' }}
                @if(!empty($produccionEstimada['entrada_kg']))
                    · MP: {{ number_format($produccionEstimada['entrada_kg'], 2) }} kg
                    → {{ number_format($produccionEstimada['cantidad'], 0) }} {{ $produccionEstimada['unidad'] }}
                    (aprox. {{ number_format($produccionEstimada['kg'], 2) }} kg)
                @elseif($lote->cantidad_objetivo)
                    · Objetivo: {{ number_format((float) $lote->cantidad_objetivo, 2) }} {{ $lote->unidadMedida?->abreviatura ?? '' }}
                @endif
            </p>
        </div>
        <div class="lp-header-actions">
            @can('lote_produccion.create')
                <a href="{{ route('procesamiento.edit', $lote) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit mr-1"></i>Editar</a>
                @if(!empty($puedeEliminar))
                <form action="{{ route('procesamiento.destroy', $lote) }}" method="POST" class="d-inline m-0">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger btn-sm"
                            data-confirm-modal
                            data-confirm-title="Eliminar lote"
                            data-confirm-message="¿Eliminar el lote «{{ $lote->nombre }}»? Se revertirá el stock de materias primas.">
                        <i class="fas fa-trash mr-1"></i>Eliminar
                    </button>
                </form>
                @endif
            @endcan
            <a href="{{ route('procesamiento.index') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left mr-1"></i>Volver</a>
        </div>
    </div>

    <div class="card lote-section-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-1 text-success">
                        <i class="fas fa-route mr-2"></i>Fase actual:
                        <span class="badge badge-success">{{ $fase_actual_label }}</span>
                    </h5>
                    <p class="text-muted small mb-0">Cadena de industrialización en planta</p>
                </div>
                <span class="h4 mb-0 text-success font-weight-bold">{{ $progreso }}%</span>
            </div>
            <div class="progress-fase mb-3">
                <div class="bar" style="width: {{ $progreso }}%"></div>
            </div>

            <div class="fase-pipeline">
                @foreach($fases_pipeline as $step)
                    @php
                        $scrollFase = $step['key'] === 'transformacion'
                            && ($step['estado'] === 'active' || $step['estado'] === 'done' || $step['estado'] === 'next');
                    @endphp
                    <div class="fase-step {{ $step['estado'] }}{{ $scrollFase ? ' fase-step--scroll' : '' }}"
                         @if($scrollFase) data-fase-scroll="{{ $step['key'] }}" role="button" tabindex="0" @endif
                         title="{{ $step['label'] }}{{ $scrollFase ? ' — clic para ir al formulario' : '' }}">
                        <i class="fas fa-{{ $step['icon'] }} d-block mb-1"></i>
                        {{ $step['label'] }}
                        @if($step['estado'] === 'skipped')
                            <span class="d-block small mt-1 text-muted">Omitido</span>
                        @elseif(!empty($step['fase_unica']) && !empty($step['completada']))
                            <span class="d-block small mt-1"><i class="fas fa-check"></i></span>
                        @endif
                    </div>
                @endforeach
            </div>

            @php $pend = $pendiente ?? []; @endphp
            @if(empty($pend['completo']))
                <div class="alert alert-light border trz-pasos-panel mb-0 mt-3">
                    <h6 class="text-success mb-2"><i class="fas fa-list-check mr-1"></i> Qué falta para llegar al 100 %</h6>
                    <p class="small text-muted mb-2">{{ $pend['resumen'] ?? '' }}</p>
                    @if(!empty($pend['acciones']))
                        <ol class="mb-0 pl-3 small">
                            @foreach($pend['acciones'] as $paso)
                                <li class="mb-1">{{ $paso }}</li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            @else
                <p class="small text-success mb-0 mt-3"><i class="fas fa-check-circle mr-1"></i> {{ $pend['resumen'] ?? 'Lote completado' }}</p>
            @endif
        </div>
    </div>

    @php
        $panelActivo = $panel_fase_activo ?? 'transformacion';
        $fasesHechas = $fases_completadas ?? [];
        $mostrarTransformacion = $panelActivo === 'transformacion' || in_array('transformacion', $fasesHechas) || count($etapas_transformacion ?? []) > 0;
        $mostrarCertificacion = $panelActivo === 'certificacion' || in_array('certificacion', $fasesHechas);
        $mostrarAlmacenaje = in_array('almacenaje', $fasesHechas)
            || ($panelActivo === 'almacenaje' && empty($lote_rechazado));
        $hayHistorial = count(array_intersect(['transformacion', 'certificacion', 'almacenaje'], $fasesHechas)) > 0
            && $panelActivo !== 'completado';
    @endphp

    <div class="lp-fases-toolbar">
        <p class="lp-fase-activa-hint mb-0">
            <i class="fas fa-crosshairs mr-1 text-success"></i>
            Trabajando en: <strong>{{ $fase_actual_label }}</strong>
        </p>
        @if($hayHistorial || count($fasesHechas) > 1)
        <button type="button" class="btn btn-outline-success btn-sm" id="btnVerTodasFases" aria-expanded="false">
            <i class="fas fa-layer-group mr-1"></i>
            <span class="btn-label">Ver todas las fases</span>
        </button>
        @endif
    </div>

    <div id="lp-fases-workspace">

    @if($panelActivo === 'completado')
    <div class="card lp-fase-card mb-3 lp-fase-panel lp-fase-panel--activa" data-fase="completado">
        <div class="card-body text-center py-4">
            @if(!empty($lote_rechazado))
                <i class="fas fa-times-circle fa-3x text-warning mb-3"></i>
                <h5 class="font-weight-bold text-warning mb-2">Lote cerrado — no conforme</h5>
                <p class="text-muted mb-0">La transformación se completó, pero la evaluación final fue <strong>no conforme</strong>. El producto no ingresó a almacén.</p>
            @else
                <i class="fas fa-flag-checkered fa-3x text-success mb-3"></i>
                <h5 class="font-weight-bold text-success mb-2">Lote completado</h5>
                <p class="text-muted mb-0">Trazabilidad cerrada al 100 %. Todas las fases fueron registradas correctamente.</p>
            @endif
        </div>
    </div>
    @endif

    @if($mostrarTransformacion)
    <div id="lp-seccion-transformacion" class="card mb-3 lp-fase-panel {{ $panelActivo === 'transformacion' ? 'lp-fase-panel--activa' : 'lp-fase-panel--historial' }}" data-fase="transformacion">
        <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-cogs text-info mr-2"></i>Transformación — línea de procesos</span>
            <div>
                @if($panelActivo !== 'transformacion')
                    <span class="lp-historial-badge mr-2"><i class="fas fa-check mr-1"></i>Fase completada</span>
                @endif
                @if(!empty($transformacion_completa))
                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Finalizada con Empaquetado</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div id="lp-ajax-flash" class="d-none"></div>
            @if($panelActivo === 'transformacion')
            <p class="small text-muted mb-3">
                @if(!empty($rutaPlantilla))
                    Siga el <strong>proceso de transformación</strong> paso a paso. El formulario sugiere el siguiente proceso y máquina.
                @else
                    Registre cada etapa con el proceso de planta, la maquinaria utilizada y el horario.
                @endif
                La transformación se cierra al registrar <strong>{{ \App\Support\ProcesoPlantaCatalogo::PROCESO_CIERRE_TRANSFORMACION }}</strong>.
            </p>
            @endif

            @if(!empty($rutaPlantilla))
            <div class="lp-proceso-timeline-wrap">
                <div class="lp-proceso-timeline-wrap__head">
                    <div>
                        <div class="titulo">
                            <i class="fas fa-route mr-1"></i>
                            {{ $lote->plantillaTransformacion?->nombre ?? 'Ruta de transformación' }}
                        </div>
                        <div class="sub">Secuencia obligatoria — de izquierda a derecha. Empaquetado solo al completar todas las etapas previas.</div>
                    </div>
                    @if($lote->plantillaTransformacion)
                        <a href="{{ route('plantillas-transformacion.show', $lote->plantillaTransformacion) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>Ver plantilla
                        </a>
                    @endif
                </div>
                <div class="lp-proceso-timeline" id="lp-ruta-plantilla-pasos">
                    @foreach($rutaPlantilla as $paso)
                    @php
                        $estadoPaso = $paso['estado'];
                        $claseItem = 'is-'.str_replace('_', '-', $estadoPaso === 'en_curso' ? 'en-curso' : $estadoPaso);
                        $iconoNodo = match ($estadoPaso) {
                            'hecho' => '<i class="fas fa-check"></i>',
                            'en_curso' => '<i class="fas fa-hourglass-half"></i>',
                            'bloqueado' => '<i class="fas fa-lock"></i>',
                            default => (string) $paso['orden'],
                        };
                        $metaPaso = match ($estadoPaso) {
                            'hecho' => 'Completada',
                            'actual' => 'Etapa actual',
                            'en_curso' => 'Asignada — en curso',
                            'bloqueado' => '<i class="fas fa-lock mr-1"></i>Complete etapa '.max(1, $paso['orden'] - 1),
                            default => '',
                        };
                    @endphp
                    <div class="lp-proceso-timeline__item {{ $claseItem }}"
                         title="{{ $estadoPaso === 'bloqueado' ? 'Complete la etapa anterior para habilitar este paso' : '' }}">
                        <div class="lp-proceso-timeline__node">{!! $iconoNodo !!}</div>
                        <div class="lp-proceso-timeline__label">{{ $paso['proceso'] }}</div>
                        <div class="lp-proceso-timeline__meta">{!! $metaPaso !!}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div id="lp-lista-etapas-completadas">
            @forelse($etapas_transformacion ?? [] as $etapa)
                <div class="lp-paso done {{ !empty($etapa['es_cierre']) ? 'cierre' : '' }} d-flex align-items-start">
                    <span class="lp-timeline-num">{{ $etapa['numero'] }}</span>
                    <div class="flex-grow-1">
                        <strong>{{ $etapa['proceso'] }}</strong>
                        @if(!empty($etapa['es_cierre']))
                            <span class="badge badge-info ml-1">Cierre</span>
                        @endif
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-industry mr-1"></i>{{ $etapa['maquina'] }}
                            · <i class="far fa-clock mr-1"></i>
                            {{ optional($etapa['inicio'])->format('d/m/Y H:i') }}
                            → {{ optional($etapa['fin'])->format('d/m/Y H:i') }}
                            @if($etapa['operador']) · {{ $etapa['operador'] }} @endif
                        </small>
                        @if($etapa['observaciones'])
                            <br><small class="text-secondary">{{ $etapa['observaciones'] }}</small>
                        @endif
                    </div>
                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                </div>
            @empty
                @if($panelActivo === 'transformacion')
                <p class="text-muted small mb-3" id="lp-etapas-vacio">Aún no hay etapas registradas. Ejemplo para papas fritas: Preparación de materias primas (pelar/cortar) → Tratamiento térmico (freír) → Empaquetado.</p>
                @endif
            @endforelse
            </div>

            @if(!empty($asignacionesPendientesLote) && $asignacionesPendientesLote->count())
            <div class="lp-asignaciones-pendientes" id="lp-asignaciones-pendientes">
                <div class="lp-asignaciones-pendientes__titulo">
                    <i class="fas fa-user-clock mr-1"></i>Asignaciones pendientes ({{ $asignacionesPendientesLote->count() }})
                </div>
                @foreach($asignacionesPendientesLote as $asig)
                <div class="lp-paso lp-paso--pendiente d-flex flex-wrap" data-asignacion-id="{{ $asig->asignacionetapaplantaid }}">
                    <span class="lp-timeline-num" aria-hidden="true"><i class="fas fa-hourglass-half" style="font-size:.7rem"></i></span>
                    <div class="flex-grow-1 min-width-0">
                        <strong class="d-block">{{ $asig->proceso?->nombre }}</strong>
                        <div class="lp-paso-meta">
                            <i class="fas fa-industry mr-1"></i>{{ $asig->maquina?->nombre }}
                            <span class="mx-1">·</span>
                            <i class="fas fa-user-cog mr-1"></i><strong>{{ $asig->operador?->nombreCompleto() }}</strong>
                            @if($asig->observaciones)
                                <br><span class="text-secondary">{{ $asig->observaciones }}</span>
                            @endif
                        </div>
                    </div>
                    @if(!empty($puedeAsignarEtapa))
                    <div class="lp-paso--pendiente__accion">
                        <form method="POST" action="{{ route('procesamiento.completar-etapa-asignada', [$lote, $asig]) }}" class="mb-0"
                              data-ajax-lp-action="completar-etapa">
                            @csrf
                            <button type="button" class="btn btn-success btn-sm font-weight-bold"
                                    data-confirm-modal
                                    data-confirm-tone="success"
                                    data-confirm-title="Completar fase"
                                    data-confirm-message="¿Marcar «{{ $asig->proceso?->nombre }}» como completada? Se retirará la alerta de {{ $asig->operador?->nombreCompleto() }}.">
                                <i class="fas fa-check mr-1"></i>Marcar completada
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($panelActivo === 'transformacion' && in_array($fase_actual, ['transformacion', 'creacion']) && empty($transformacion_completa))
            @if(!empty($puedeAsignarEtapa) && !empty($puedeAsignarNuevaEtapa))
            <div class="lp-form-etapa mt-3" id="lp-form-registrar-etapa">
                <h6 class="font-weight-bold text-success mb-3">
                    <i class="fas fa-user-plus mr-1"></i>
                    Asignar etapa {{ $ordenEtapaActual ?? 1 }} a operario
                </h6>
                @php
                    $procesoEtapaAsignar = $siguientePasoPlantilla?->proceso ?? $procesosDisponibles->first();
                    $procesoEtapaId = (int) ($procesoEtapaAsignar?->procesoplantaid ?? 0);
                    $procesoEtapaNombre = $procesoEtapaAsignar?->nombre ?? '—';
                    $procesoEtapaFijo = ! empty($rutaPlantilla) || $procesosDisponibles->count() <= 1;
                @endphp
                <form method="POST" action="{{ route('procesamiento.asignar-etapa', $lote) }}" id="formAsignarEtapa" class="js-lp-guardar-scroll">
                    @csrf
                    <div class="form-row lp-etapa-asignar-row">
                        <div class="col-md-3 lp-etapa-field">
                            <label class="small font-weight-bold lp-etapa-label mb-1 d-block">
                                <i class="fas fa-lock mr-1"></i>Proceso de planta
                            </label>
                            <div class="lp-etapa-control">
                                @if($procesoEtapaFijo && $procesoEtapaId > 0)
                                    <input type="hidden" name="procesoplantaid" id="inputProcesoEtapa" value="{{ $procesoEtapaId }}">
                                    <div class="form-control form-control-sm lp-proceso-fijo" aria-readonly="true">
                                        <i class="fas fa-lock" aria-hidden="true"></i>
                                        <span>{{ $procesoEtapaNombre }}</span>
                                        <span class="lp-proceso-fijo__badge">Fijo</span>
                                    </div>
                                @else
                                    <select name="procesoplantaid" id="selectProcesoEtapa" class="form-control form-control-sm" required>
                                        <option value="">Seleccionar…</option>
                                        @foreach($procesosDisponibles as $proc)
                                            <option value="{{ $proc->procesoplantaid }}">{{ $proc->nombre }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 lp-etapa-field">
                            <div class="lp-etapa-control">
                                @include('partials.selector-catalogo', [
                                    'id' => 'etapa_maquinaria',
                                    'name' => 'maquinaplantaid',
                                    'label' => 'Maquinaria',
                                    'icon' => 'fa-industry',
                                    'endpoint' => route('catalogo-selector.maquinas-planta'),
                                    'params' => array_filter([
                                        'activo' => '1',
                                        'procesoplantaid' => $procesoEtapaId > 0 ? (string) $procesoEtapaId : null,
                                    ]),
                                    'title' => 'Elegir maquinaria',
                                    'searchPlaceholder' => 'Nombre, código…',
                                    'colNombre' => 'Equipo',
                                    'colDetalle' => 'Código / estado',
                                    'rowIcon' => 'fa-cogs',
                                    'theme' => 'planta',
                                    'required' => true,
                                    'allowEmpty' => false,
                                    'showLabel' => true,
                                    'size' => 'sm',
                                ])
                            </div>
                        </div>
                        <div class="col-md-3 lp-etapa-field">
                            <div class="lp-etapa-control">
                                @include('partials.selector-catalogo', [
                                    'id' => 'etapa_operario',
                                    'name' => 'operador_usuarioid',
                                    'label' => 'Operario',
                                    'icon' => 'fa-user-cog',
                                    'endpoint' => route('catalogo-selector.usuarios'),
                                    'params' => ['operarios_planta' => '1'],
                                    'title' => 'Elegir operario',
                                    'searchPlaceholder' => 'Nombre, correo…',
                                    'colNombre' => 'Operario',
                                    'colDetalle' => 'Contacto',
                                    'rowIcon' => 'fa-user-cog',
                                    'theme' => 'planta',
                                    'required' => true,
                                    'allowEmpty' => false,
                                    'showLabel' => true,
                                    'size' => 'sm',
                                ])
                            </div>
                        </div>
                        <div class="col-md-3 lp-etapa-field">
                            <label class="small font-weight-bold lp-etapa-label mb-1 d-block">
                                <i class="fas fa-comment-alt mr-1"></i>Observaciones
                            </label>
                            <div class="lp-etapa-control">
                                <input type="text" name="observaciones" class="form-control form-control-sm" maxlength="500" placeholder="Ej. Pelado y corte en cubos">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 form-group mb-0 d-flex justify-content-end">
                            <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                                <i class="fas fa-paper-plane mr-1"></i>Asignar a operario
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @elseif(!empty($puedeAsignarEtapa) && empty($puedeAsignarNuevaEtapa) && !empty($mensajeBloqueoAsignacion))
            <div class="alert alert-light border mt-3 mb-0 small text-muted" id="lp-bloqueo-asignacion">
                <i class="fas fa-lock mr-1"></i>{{ $mensajeBloqueoAsignacion }}
            </div>
            @elseif(\App\Support\UsuarioRol::esOperarioPlanta(auth()->user()))
            <div class="alert alert-info py-2 px-3 mt-3 mb-0 small">
                <i class="fas fa-info-circle mr-1"></i>
                Las etapas se asignan desde el jefe de planta. Revise sus tareas en
                <a href="{{ route('tareas-planta.index') }}" class="alert-link font-weight-bold">Mis tareas de transformación</a>.
            </div>
            @endif
            @endif
        </div>
    </div>
    @endif

    @if($mostrarCertificacion)
    <div id="certificacion" class="card lp-fase-card mb-3 lp-fase-panel {{ $panelActivo === 'certificacion' ? 'lp-fase-panel--activa' : 'lp-fase-panel--historial' }}" data-fase="certificacion">
        <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-certificate mr-2" style="color:#7c3aed"></i>Certificación</span>
            @if($panelActivo !== 'certificacion' && $evaluacion)
                <span class="lp-historial-badge"><i class="fas fa-check mr-1"></i>Fase completada</span>
            @endif
        </div>
        <div class="card-body">
            @if($evaluacion && $panelActivo !== 'certificacion')
                <div class="lp-historial-readonly">
                    <span class="badge badge-{{ $evaluacion->razon === 'Certificado' ? 'success' : 'warning' }} mb-2">{{ $evaluacion->razon }}</span>
                    <p class="small text-muted mb-0">
                        <i class="far fa-clock mr-1"></i>{{ optional($evaluacion->fecha_evaluacion)->format('d/m/Y H:i') }}
                        @if($evaluacion->observaciones) — {{ $evaluacion->observaciones }} @endif
                    </p>
                </div>
            @elseif($panelActivo === 'certificacion')
            <p class="small text-muted mb-3">
                Completar la transformación no implica certificación automática: debe registrar el resultado del control de calidad.
                Solo los lotes <strong>certificados</strong> pueden pasar a almacenaje.
            </p>
            <form method="POST" action="{{ route('procesamiento.certificar', $lote) }}" class="lp-form-etapa mb-0">
                @csrf
                <div class="form-row">
                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold">Resultado</label>
                        <select name="razon" class="form-control form-control-sm" required>
                            <option value="{{ \App\Models\EvaluacionFinalLoteProduccion::RAZON_CERTIFICADO }}">Certificado</option>
                            <option value="{{ \App\Models\EvaluacionFinalLoteProduccion::RAZON_NO_CONFORME }}">No conforme</option>
                        </select>
                        <small class="text-muted d-block mt-1">No conforme cierra el lote sin almacenar.</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold">Motivo / observaciones</label>
                        <input type="text" name="observaciones" class="form-control form-control-sm" maxlength="500" placeholder="Obligatorio si es no conforme">
                    </div>
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="small font-weight-bold">Recomendaciones</label>
                        <input type="text" name="recomendaciones" class="form-control form-control-sm" maxlength="2000" placeholder="Sugerencias para mejorar (no conforme)">
                    </div>
                </div>
                <button type="submit" class="btn btn-sm mt-2 font-weight-bold" style="background:#7c3aed;color:#fff">
                    <i class="fas fa-stamp mr-1"></i>Registrar evaluación
                </button>
            </form>
            @endif
        </div>
    </div>
    @endif

    @if($mostrarAlmacenaje)
    <div class="card lp-fase-card mb-3 lp-fase-panel {{ $panelActivo === 'almacenaje' ? 'lp-fase-panel--activa' : 'lp-fase-panel--historial' }}" data-fase="almacenaje">
        <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-warehouse text-warning mr-2"></i>Almacenaje del producto terminado</span>
            @if($panelActivo !== 'almacenaje' && $almacenaje)
                <span class="lp-historial-badge"><i class="fas fa-check mr-1"></i>Fase completada</span>
            @endif
        </div>
        <div class="card-body {{ $panelActivo === 'almacenaje' && !$almacenaje ? 'p-0' : '' }}">
            @if($almacenaje && $panelActivo !== 'almacenaje')
                <div class="lp-historial-readonly">
                    <p class="mb-1"><strong>{{ $almacenaje->ubicacion }}</strong></p>
                    <p class="small text-muted mb-0">
                        {{ number_format((float) $almacenaje->cantidad, 2) }} {{ $unidadProductoAlmacen ?? 'kg' }}
                        · {{ $almacenaje->condicion }}
                        · {{ optional($almacenaje->fecha_almacenaje)->format('d/m/Y H:i') }}
                    </p>
                </div>
            @elseif($panelActivo === 'almacenaje')
            @if(!empty($produccionEstimada['entrada_kg']))
            <div class="alert alert-light border small mx-3 mt-3 mb-0">
                <i class="fas fa-calculator text-success mr-1"></i>
                <strong>Producción calculada:</strong>
                {{ number_format($produccionEstimada['entrada_kg'], 2) }} kg de materia prima
                × {{ number_format($produccionEstimada['rendimiento'] * 100, 0) }}&nbsp;% rendimiento
                → <strong>{{ number_format($produccionEstimada['cantidad'], 0) }} {{ $produccionEstimada['unidad'] }}</strong>
                (aprox. {{ number_format($produccionEstimada['kg'], 2) }} kg).
            </div>
            @endif
            <form method="POST" action="{{ route('procesamiento.almacenar', $lote) }}" id="formAlmacenajeLote" class="p-3">
                @csrf
                @push('almacen-envio-extra-almacenSectionLote')
                <div class="almacen-section-extra">
                    <h6 class="small font-weight-bold text-success mb-3 mb-md-2">
                        <i class="fas fa-thermometer-half mr-1"></i> Detalles del ingreso
                    </h6>
                    <div class="form-row">
                        <div class="col-md-6 form-group mb-md-0">
                            <label class="small font-weight-bold">Condición de conservación <span class="text-danger">*</span></label>
                            <select name="condicion" class="form-control form-control-sm" required>
                                <option value="">Seleccionar condición…</option>
                                @foreach($condicionesAlmacenaje ?? [] as $cond)
                                    <option value="{{ $cond }}" @selected($cond === 'A temperatura controlada (2–8 °C)')>{{ $cond }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label class="small font-weight-bold">Observaciones <span class="text-muted font-weight-normal">(opcional)</span></label>
                            <input type="text" name="observaciones" class="form-control form-control-sm" maxlength="500" placeholder="Ej. Estante A — cámara 2" value="{{ old('observaciones') }}">
                        </div>
                    </div>
                </div>
                <div class="almacen-section-actions">
                    <button type="submit" class="btn btn-warning btn-sm font-weight-bold text-dark">
                        <i class="fas fa-warehouse mr-1"></i>Registrar almacenaje
                    </button>
                </div>
                @endpush
                @include('partials.almacen-envio-selector', [
                    'almacenes' => $almacenesPlanta ?? collect(),
                    'resumenesCapacidad' => $resumenesCapacidadPlanta ?? [],
                    'sectionId' => 'almacenSectionLote',
                    'hiddenInputId' => 'almacenidLote',
                    'guiaTexto' => 'Toda la producción terminada del lote debe ingresar al inventario del almacén elegido. El sistema valida la capacidad disponible y puede sugerir un almacén según el producto.',
                    'instruccion' => 'Seleccione el almacén de planta donde guardar el producto terminado',
                    'crearAlmacenUrl' => route('almacen-planta.create'),
                    'emptyTexto' => 'No hay almacenes de planta registrados.',
                    'productoResumen' => trim($lote->nombre . ($lote->producto ? ' ('.$lote->producto.')' : '')),
                    'cantidadResumen' => number_format((float) ($cantidadProductoAlmacen ?? 0), 0).' '.($unidadProductoAlmacen ?? 'kg').' (aprox. '.number_format((float) ($cantidadProductoAlmacenKg ?? 0), 2).' kg según materia prima)',
                ])
            </form>
            @endif
        </div>
    </div>
    @endif

    </div>{{-- #lp-fases-workspace --}}

    <div class="row mb-3">
        <div class="col-12 mb-2">
            <h5 class="font-weight-bold text-success mb-0"><i class="fas fa-clipboard-list mr-2"></i>Resumen del lote</h5>
            <p class="small text-muted mb-0">Materias consumidas, certificación y almacenaje registrados</p>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="lp-resumen-card lp-resumen-mp">
                <div class="lp-resumen-head"><i class="fas fa-boxes"></i> Materias primas usadas</div>
                <div class="lp-resumen-body">
                    @forelse($lote->materiasPrimas as $mp)
                        <div class="lp-resumen-item">
                            <span class="nombre">{{ $mp->insumo?->nombre ?? 'Materia prima' }}</span>
                            <span class="dato">
                                {{ number_format((float) $mp->cantidad_usada, 2) }}
                                <small class="text-muted font-weight-normal">{{ $mp->insumo?->unidadMedida?->abreviatura ?? $mp->insumo?->unidadMedida?->nombre ?? 'ud' }}</small>
                            </span>
                        </div>
                    @empty
                        <p class="lp-resumen-empty mb-0"><i class="fas fa-inbox d-block mb-2 opacity-25"></i>Sin materias registradas</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="lp-resumen-card lp-resumen-cert">
                <div class="lp-resumen-head"><i class="fas fa-certificate"></i> Certificación</div>
                <div class="lp-resumen-body">
                    @if($evaluacion)
                        <div class="text-center py-1">
                            <span class="lp-resumen-badge badge-{{ $evaluacion->razon === 'Certificado' ? 'success' : 'warning' }} mb-2 d-inline-block">
                                {{ $evaluacion->razon }}
                            </span>
                            <small class="text-muted d-block">
                                <i class="far fa-clock mr-1"></i>{{ optional($evaluacion->fecha_evaluacion)->format('d/m/Y H:i') }}
                            </small>
                            @if($evaluacion->observaciones)
                                <p class="small text-secondary mt-2 mb-0 text-left border-top pt-2">{{ $evaluacion->observaciones }}</p>
                            @endif
                        </div>
                    @else
                        <p class="lp-resumen-empty mb-0">
                            <i class="fas fa-hourglass-half d-block mb-2"></i>
                            Pendiente de evaluación final
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="lp-resumen-card lp-resumen-alm">
                <div class="lp-resumen-head"><i class="fas fa-warehouse"></i> Almacenaje</div>
                <div class="lp-resumen-body">
                    @if(!empty($lote_rechazado))
                        <p class="lp-resumen-empty mb-0">
                            <i class="fas fa-ban d-block mb-2 text-warning"></i>
                            Sin ingreso a almacén<br><small class="text-muted">Lote no conforme</small>
                        </p>
                    @elseif($almacenaje)
                        <div class="lp-resumen-item">
                            <span class="nombre"><i class="fas fa-map-marker-alt text-warning mr-1"></i>{{ $almacenaje->ubicacion }}</span>
                        </div>
                        <div class="lp-resumen-item">
                            <span class="nombre">Producto almacenado</span>
                            <span class="dato">{{ number_format((float) $almacenaje->cantidad, 2) }} {{ $unidadProductoAlmacen ?? 'kg' }}</span>
                        </div>
                        <div class="lp-resumen-item">
                            <span class="nombre">Condición</span>
                            <span class="dato" style="white-space:normal;font-size:.8rem">{{ $almacenaje->condicion }}</span>
                        </div>
                        <small class="text-muted d-block mt-2 text-center">
                            <i class="far fa-clock mr-1"></i>{{ optional($almacenaje->fecha_almacenaje)->format('d/m/Y H:i') }}
                        </small>
                    @else
                        <p class="lp-resumen-empty mb-0">
                            <i class="fas fa-warehouse d-block mb-2"></i>
                            Sin ingreso a almacén
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.modal-confirmar-accion')
@endsection

@push('scripts')
@if($panelActivo === 'almacenaje' && empty($almacenaje))
    @include('partials.almacen-envio-scripts', [
        'sectionId' => 'almacenSectionLote',
        'hiddenInputId' => 'almacenidLote',
        'formSelector' => '#formAlmacenajeLote',
        'cantidadFija' => $cantidadProductoAlmacenKg ?? 0,
        'productoHint' => trim(($lote->producto ?? '').' '.($lote->nombre ?? '')),
    ])
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectProceso = document.getElementById('selectProcesoEtapa');
    const inputProceso = document.getElementById('inputProcesoEtapa');
    const procesoControl = selectProceso || inputProceso;
    const wrapMaquina = document.getElementById('selector_wrap_etapa_maquinaria');
    const formEtapa = document.getElementById('formAsignarEtapa');

    if (!formEtapa || !procesoControl) return;

    function valorProceso() {
        return procesoControl.value || '';
    }

    function syncParamsMaquinaria() {
        if (!window.CatalogoSelector || !CatalogoSelector.instances.etapa_maquinaria) return;
        const procesoId = valorProceso();
        CatalogoSelector.instances.etapa_maquinaria.params = procesoId
            ? { activo: '1', procesoplantaid: procesoId }
            : { activo: '1' };
    }

    function bloquearMaquinaria(bloquear) {
        if (!wrapMaquina) return;
        wrapMaquina.classList.toggle('is-bloqueado', bloquear);
        if (bloquear && window.CatalogoSelector) {
            CatalogoSelector.clear('etapa_maquinaria');
        }
    }

    function actualizarEstadoProceso(limpiarMaquina) {
        const procesoId = valorProceso();
        syncParamsMaquinaria();
        bloquearMaquinaria(!procesoId);
        if (limpiarMaquina && procesoId && window.CatalogoSelector) {
            CatalogoSelector.clear('etapa_maquinaria');
        }
    }

    selectProceso?.addEventListener('change', function () { actualizarEstadoProceso(true); });
    actualizarEstadoProceso(false);

    @php
        $sugeridoPasoJs = $siguientePasoPlantilla ? [
            'procesoplantaid' => $siguientePasoPlantilla->procesoplantaid,
            'maquinaplantaid' => $siguientePasoPlantilla->maquinaplantaid,
            'maquina_nombre' => $siguientePasoPlantilla->maquina?->nombre,
            'maquina_codigo' => $siguientePasoPlantilla->maquina?->codigo,
            'notas' => $siguientePasoPlantilla->notas,
        ] : null;
    @endphp
    const sugerido = @json($sugeridoPasoJs);
    if (sugerido && sugerido.procesoplantaid) {
        if (selectProceso) selectProceso.value = String(sugerido.procesoplantaid);
        if (inputProceso) inputProceso.value = String(sugerido.procesoplantaid);
        syncParamsMaquinaria();
        bloquearMaquinaria(false);
        actualizarEstadoProceso(false);
        if (sugerido.maquinaplantaid && window.CatalogoSelector) {
            const etiquetaMaquina = (sugerido.maquina_nombre || 'Maquinaria sugerida')
                + (sugerido.maquina_codigo ? ' (' + sugerido.maquina_codigo + ')' : '');
            CatalogoSelector.setValue('etapa_maquinaria', String(sugerido.maquinaplantaid), etiquetaMaquina);
        }
        const obs = formEtapa.querySelector('input[name="observaciones"]');
        if (obs && sugerido.notas && !obs.value) obs.value = sugerido.notas;
    }
});

(function () {
    const btn = document.getElementById('btnVerTodasFases');
    const workspace = document.getElementById('lp-fases-workspace');
    if (!btn || !workspace) return;

    btn.addEventListener('click', function () {
        const expandido = workspace.classList.toggle('lp-modo-todas-fases');
        btn.setAttribute('aria-expanded', expandido ? 'true' : 'false');
        const label = btn.querySelector('.btn-label');
        if (label) {
            label.textContent = expandido ? 'Ver solo fase actual' : 'Ver todas las fases';
        }
        btn.classList.toggle('btn-success', expandido);
        btn.classList.toggle('btn-outline-success', !expandido);
    });
})();

(function () {
    const workspace = document.getElementById('lp-fases-workspace');
    const btnVerTodas = document.getElementById('btnVerTodasFases');

    function irASeccionFase(fase) {
        if (!fase) return;

        if (workspace && !workspace.classList.contains('lp-modo-todas-fases')) {
            workspace.classList.add('lp-modo-todas-fases');
            if (btnVerTodas) {
                btnVerTodas.setAttribute('aria-expanded', 'true');
                const label = btnVerTodas.querySelector('.btn-label');
                if (label) label.textContent = 'Ver solo fase actual';
                btnVerTodas.classList.add('btn-success');
                btnVerTodas.classList.remove('btn-outline-success');
            }
        }

        const panel = document.querySelector('.lp-fase-panel[data-fase="' + fase + '"]');
        const destino = fase === 'transformacion'
            ? (document.getElementById('lp-form-registrar-etapa') || panel)
            : panel;

        if (destino) {
            setTimeout(function () {
                destino.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 80);
        }
    }

    document.querySelectorAll('[data-fase-scroll]').forEach(function (step) {
        step.addEventListener('click', function () {
            irASeccionFase(step.dataset.faseScroll);
        });
        step.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                irASeccionFase(step.dataset.faseScroll);
            }
        });
    });

    if (window.location.hash === '#transformacion') {
        irASeccionFase('transformacion');
    }

    const scrollKey = 'lp_procesamiento_scroll';

    function guardarScrollProcesamiento() {
        try {
            sessionStorage.setItem(scrollKey, String(window.scrollY));
        } catch (err) {}
    }

    document.querySelectorAll('.js-lp-guardar-scroll').forEach(function (form) {
        form.addEventListener('submit', guardarScrollProcesamiento);
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm-modal]');
        if (!btn || !btn.closest('.js-lp-guardar-scroll')) return;
        guardarScrollProcesamiento();
    }, true);
})();
</script>
<script>
(function () {
    function escHtml(text) {
        const d = document.createElement('div');
        d.textContent = text == null ? '' : String(text);
        return d.innerHTML;
    }

    function mostrarFlashAjax(mensaje, tipo) {
        const cont = document.getElementById('lp-ajax-flash');
        if (!cont) return;
        const icon = tipo === 'error' ? 'fa-ban' : 'fa-check';
        const clase = tipo === 'error' ? 'ag-flash--error' : 'ag-flash--success';
        cont.className = '';
        cont.innerHTML =
            '<div class="ag-flash ' + clase + ' mb-3" role="status">' +
                '<span class="ag-flash__icon"><i class="fas ' + icon + '"></i></span>' +
                '<span class="ag-flash__text">' + escHtml(mensaje) + '</span>' +
                '<button type="button" class="ag-flash__close" aria-label="Cerrar"><i class="fas fa-times"></i></button>' +
            '</div>';
        cont.classList.remove('d-none');
        cont.querySelector('.ag-flash__close')?.addEventListener('click', function () {
            cont.classList.add('d-none');
            cont.innerHTML = '';
        });
    }

    function htmlEtapaCompletada(etapa) {
        if (!etapa) return '';
        const cierre = etapa.es_cierre ? '<span class="badge badge-info ml-1">Cierre</span>' : '';
        const obs = etapa.observaciones
            ? '<br><small class="text-secondary">' + escHtml(etapa.observaciones) + '</small>' : '';
        const operador = etapa.operador ? ' · ' + escHtml(etapa.operador) : '';

        return '<div class="lp-paso done lp-paso--entrada d-flex align-items-start">' +
            '<span class="lp-timeline-num">' + escHtml(etapa.numero) + '</span>' +
            '<div class="flex-grow-1"><strong>' + escHtml(etapa.proceso) + '</strong>' + cierre + '<br>' +
            '<small class="text-muted"><i class="fas fa-industry mr-1"></i>' + escHtml(etapa.maquina) +
            ' · <i class="far fa-clock mr-1"></i>' + escHtml(etapa.inicio_fmt) + ' → ' + escHtml(etapa.fin_fmt) +
            operador + '</small>' + obs + '</div>' +
            '<span class="badge badge-success"><i class="fas fa-check"></i></span></div>';
    }

    function htmlRutaPaso(paso) {
        const estado = paso.estado;
        const claseItem = 'lp-proceso-timeline__item is-' + (estado === 'en_curso' ? 'en-curso' : estado);
        let iconoNodo = String(paso.orden);
        if (estado === 'hecho') iconoNodo = '<i class="fas fa-check"></i>';
        else if (estado === 'en_curso') iconoNodo = '<i class="fas fa-hourglass-half"></i>';
        else if (estado === 'bloqueado') iconoNodo = '<i class="fas fa-lock"></i>';
        let meta = '';
        if (estado === 'hecho') meta = 'Completada';
        else if (estado === 'actual') meta = 'Etapa actual';
        else if (estado === 'en_curso') meta = 'Asignada — en curso';
        else if (estado === 'bloqueado') {
            meta = '<i class="fas fa-lock mr-1"></i>Complete etapa ' + Math.max(1, paso.orden - 1);
        }
        const title = estado === 'bloqueado' ? ' title="Complete la etapa anterior para habilitar este paso"' : '';
        return '<div class="' + claseItem + '"' + title + '>' +
            '<div class="lp-proceso-timeline__node">' + iconoNodo + '</div>' +
            '<div class="lp-proceso-timeline__label">' + escHtml(paso.proceso) + '</div>' +
            '<div class="lp-proceso-timeline__meta">' + meta + '</div>' +
            '</div>';
    }

    window.LpProcesamientoAjax = {
        completarEtapa: function (form) {
            const card = form.closest('[data-asignacion-id]');
            const btn = form.querySelector('button');
            if (btn) btn.classList.add('lp-ajax-loading');

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then(function (r) {
                    return r.text().then(function (text) {
                        let data = null;
                        try {
                            data = text ? JSON.parse(text) : null;
                        } catch (parseErr) {
                            throw {
                                message: r.ok
                                    ? 'Respuesta inválida del servidor.'
                                    : 'Error del servidor (' + r.status + '). Recargue la página e intente de nuevo.',
                            };
                        }
                        if (!r.ok || !data || data.ok === false) {
                            throw { message: (data && data.message) || 'No se pudo completar la etapa.' };
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    if (btn) btn.classList.remove('lp-ajax-loading');
                    if (card) {
                        card.style.transition = 'opacity .3s ease';
                        card.style.opacity = '0';
                        setTimeout(function () { card.remove(); }, 300);
                    }
                    const lista = document.getElementById('lp-lista-etapas-completadas');
                    const vacio = document.getElementById('lp-etapas-vacio');
                    if (vacio) vacio.remove();
                    if (lista && data.etapa) lista.insertAdjacentHTML('beforeend', htmlEtapaCompletada(data.etapa));

                    const ruta = document.getElementById('lp-ruta-plantilla-pasos');
                    if (ruta && Array.isArray(data.ruta_plantilla)) {
                        ruta.innerHTML = data.ruta_plantilla.map(htmlRutaPaso).join('');
                    }

                    const bloqueo = document.getElementById('lp-bloqueo-asignacion');
                    if (bloqueo) {
                        if (data.mensaje_bloqueo) {
                            bloqueo.classList.remove('d-none');
                            bloqueo.innerHTML = '<i class="fas fa-lock mr-1"></i>' + escHtml(data.mensaje_bloqueo);
                        } else {
                            bloqueo.classList.add('d-none');
                        }
                    }

                    const seccion = document.getElementById('lp-asignaciones-pendientes');
                    if (seccion && (data.asignaciones_pendientes_count || 0) <= 0) {
                        seccion.style.transition = 'opacity .35s ease';
                        seccion.style.opacity = '0';
                        setTimeout(function () { seccion.remove(); }, 350);
                    }

                    mostrarFlashAjax(data.message, 'success');
                })
                .catch(function (err) {
                    if (btn) btn.classList.remove('lp-ajax-loading');
                    mostrarFlashAjax((err && err.message) || 'No se pudo completar la etapa.', 'error');
                });
        },
    };
})();
</script>
@endpush
