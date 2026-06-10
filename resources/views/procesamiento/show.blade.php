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
    window.addEventListener('load', restaurarScroll);
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
                    (~{{ number_format($produccionEstimada['kg'], 2) }} kg)
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
            <div class="mb-3 p-2 rounded border" style="background:#f8fbf8;">
                <div class="small font-weight-bold text-success mb-2">
                    <i class="fas fa-project-diagram mr-1"></i>
                    Proceso: {{ $lote->plantillaTransformacion?->nombre ?? 'Predefinido' }}
                    @if($lote->plantillaTransformacion)
                        <a href="{{ route('plantillas-transformacion.show', $lote->plantillaTransformacion) }}" class="ml-2 font-weight-normal">Ver detalle</a>
                    @endif
                </div>
                <div class="d-flex flex-wrap" style="gap:6px;">
                    @foreach($rutaPlantilla as $paso)
                    <span class="badge px-2 py-1 {{ $paso['estado'] === 'hecho' ? 'badge-success' : ($paso['estado'] === 'actual' ? 'badge-warning' : 'badge-light border text-muted') }}">
                        {{ $paso['orden'] }}. {{ $paso['proceso'] }}
                        @if($paso['estado'] === 'hecho')<i class="fas fa-check ml-1"></i>@endif
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

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
                <p class="text-muted small mb-3">Aún no hay etapas registradas. Ejemplo para papas fritas: Preparación de materias primas (pelar/cortar) → Tratamiento térmico (freír) → Empaquetado.</p>
                @endif
            @endforelse

            @if(!empty($asignacionesPendientesLote) && $asignacionesPendientesLote->count())
            <div class="alert alert-warning py-2 px-3 mb-3">
                <strong class="small d-block mb-1"><i class="fas fa-user-clock mr-1"></i>Asignaciones pendientes</strong>
                <ul class="mb-0 pl-0 list-unstyled small">
                    @foreach($asignacionesPendientesLote as $asig)
                    <li class="d-flex flex-wrap justify-content-between align-items-center border-bottom py-2" style="gap:.5rem;">
                        <span>
                            {{ $asig->proceso?->nombre }} · {{ $asig->maquina?->nombre }}
                            → <strong>{{ $asig->operador?->nombreCompleto() }}</strong>
                            @if($asig->observaciones) <span class="text-muted">({{ Str::limit($asig->observaciones, 60) }})</span> @endif
                        </span>
                        @if(!empty($puedeAsignarEtapa))
                        <form method="POST" action="{{ route('procesamiento.completar-etapa-asignada', [$lote, $asig]) }}" class="mb-0">
                            @csrf
                            <button type="button" class="btn btn-success btn-sm font-weight-bold"
                                    data-confirm-modal
                                    data-confirm-tone="success"
                                    data-confirm-title="Completar fase"
                                    data-confirm-message="¿Marcar «{{ $asig->proceso?->nombre }}» como completada? Se retirará la alerta de {{ $asig->operador?->nombreCompleto() }}.">
                                <i class="fas fa-check mr-1"></i>Marcar completada
                            </button>
                        </form>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($panelActivo === 'transformacion' && in_array($fase_actual, ['transformacion', 'creacion']) && empty($transformacion_completa))
            @if(!empty($puedeAsignarEtapa) && !empty($puedeAsignarNuevaEtapa))
            <div class="lp-form-etapa mt-3" id="lp-form-registrar-etapa">
                <h6 class="font-weight-bold text-success mb-3">
                    <i class="fas fa-user-plus mr-1"></i>
                    Asignar etapa {{ count($etapas_transformacion ?? []) + count($asignacionesPendientesLote ?? []) + 1 }} a operario
                </h6>
                <form method="POST" action="{{ route('procesamiento.asignar-etapa', $lote) }}" id="formAsignarEtapa">
                    @csrf
                    <div class="form-row">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Proceso de planta</label>
                            <select name="procesoplantaid" id="selectProcesoEtapa" class="form-control form-control-sm" required>
                                <option value="">Seleccionar…</option>
                                @foreach($procesosDisponibles as $proc)
                                    <option value="{{ $proc->procesoplantaid }}">{{ $proc->nombre }}</option>
                                @endforeach
                            </select>
                            @if(!empty($siguientePasoPlantilla))
                            <small class="text-success"><i class="fas fa-magic mr-1"></i>Sugerido: {{ $siguientePasoPlantilla->proceso?->nombre }}</small>
                            @else
                            <small class="text-muted">Puede repetir el mismo proceso las veces que necesite.</small>
                            @endif
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Maquinaria</label>
                            <select name="maquinaplantaid" id="selectMaquinaEtapa" class="form-control form-control-sm" required disabled>
                                <option value="">Primero elija un proceso…</option>
                                @foreach($maquinasPlanta as $maq)
                                    <option value="{{ $maq->maquinaplantaid }}" data-codigo="{{ $maq->codigo }}">{{ $maq->nombre }}@if($maq->codigo) ({{ $maq->codigo }})@endif</option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="hintMaquinaEtapa">Solo equipos compatibles con el proceso.</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Operario (rol planta)</label>
                            <select name="operador_usuarioid" id="selectOperadorEtapa" class="form-control form-control-sm" required disabled>
                                <option value="">Primero elija maquinaria…</option>
                                @foreach($operadoresPlanta as $op)
                                    <option value="{{ $op->usuarioid }}">{{ $op->nombreCompleto() }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="hintOperadorEtapa">El operario recibirá una alerta en su panel.</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Observaciones</label>
                            <input type="text" name="observaciones" class="form-control form-control-sm" maxlength="500" placeholder="Ej. Pelado y corte en cubos">
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
                    <div class="col-md-8 form-group mb-md-0">
                        <label class="small font-weight-bold">Observaciones</label>
                        <input type="text" name="observaciones" class="form-control form-control-sm" maxlength="500" placeholder="Opcional">
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
                (~{{ number_format($produccionEstimada['kg'], 2) }} kg).
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
                    'sectionId' => 'almacenSectionLote',
                    'hiddenInputId' => 'almacenidLote',
                    'guiaTexto' => 'Toda la producción terminada del lote debe ingresar al inventario del almacén elegido. El sistema valida la capacidad disponible y puede sugerir un almacén según el producto.',
                    'instruccion' => 'Seleccione el almacén de planta donde guardar el producto terminado',
                    'crearAlmacenUrl' => route('almacen-planta.create'),
                    'emptyTexto' => 'No hay almacenes de planta registrados.',
                    'productoResumen' => trim($lote->nombre . ($lote->producto ? ' ('.$lote->producto.')' : '')),
                    'cantidadResumen' => number_format((float) ($cantidadProductoAlmacen ?? 0), 0).' '.($unidadProductoAlmacen ?? 'kg').' (~'.number_format((float) ($cantidadProductoAlmacenKg ?? 0), 2).' kg según materia prima)',
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
(function () {
    const mapa = @json($mapaCompatibilidad ?? ['proceso_maquina' => [], 'maquina_proceso' => []]);
    const selectProceso = document.getElementById('selectProcesoEtapa');
    const selectMaquina = document.getElementById('selectMaquinaEtapa');
    const hintMaquina = document.getElementById('hintMaquinaEtapa');

    if (!selectProceso || !selectMaquina) return;

    const todasMaquinas = Array.from(selectMaquina.querySelectorAll('option[value]')).map(function (opt) {
        return { id: opt.value, label: opt.textContent, codigo: opt.dataset.codigo || '' };
    });

    function actualizarMaquinas() {
        const procesoId = selectProceso.value;
        const permitidas = (mapa.proceso_maquina && mapa.proceso_maquina[procesoId]) ? mapa.proceso_maquina[procesoId].map(String) : [];

        selectMaquina.innerHTML = '';
        if (!procesoId) {
            selectMaquina.disabled = true;
            selectMaquina.innerHTML = '<option value="">Primero elija un proceso…</option>';
            if (hintMaquina) hintMaquina.textContent = 'Solo equipos compatibles con el proceso.';
            return;
        }

        const opciones = todasMaquinas.filter(function (m) { return permitidas.includes(String(m.id)); });

        if (!opciones.length) {
            selectMaquina.disabled = true;
            selectMaquina.innerHTML = '<option value="">Sin maquinaria compatible</option>';
            if (hintMaquina) hintMaquina.textContent = 'Configure equipos en catálogo o ejecute el seeder MaquinasProcesoPlantaSeeder.';
            return;
        }

        selectMaquina.disabled = false;
        selectMaquina.innerHTML = '<option value="">Seleccionar…</option>';
        opciones.forEach(function (m) {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.label;
            selectMaquina.appendChild(opt);
        });
        if (hintMaquina) hintMaquina.textContent = opciones.length + ' equipo(s) compatible(s) con este proceso.';
        actualizarOperador();
    }

    const selectOperador = document.getElementById('selectOperadorEtapa');
    const hintOperador = document.getElementById('hintOperadorEtapa');

    function actualizarOperador() {
        if (!selectOperador) return;
        const maquinaOk = selectMaquina && !selectMaquina.disabled && selectMaquina.value;
        selectOperador.disabled = !maquinaOk;
        if (!maquinaOk) {
            selectOperador.value = '';
            if (hintOperador) hintOperador.textContent = 'Primero elija maquinaria…';
            return;
        }
        if (hintOperador) hintOperador.textContent = 'El operario recibirá una alerta en su panel.';
    }

    selectProceso.addEventListener('change', actualizarMaquinas);
    if (selectMaquina) selectMaquina.addEventListener('change', actualizarOperador);

    @php
        $sugeridoPasoJs = $siguientePasoPlantilla ? [
            'procesoplantaid' => $siguientePasoPlantilla->procesoplantaid,
            'maquinaplantaid' => $siguientePasoPlantilla->maquinaplantaid,
            'notas' => $siguientePasoPlantilla->notas,
        ] : null;
    @endphp
    const sugerido = @json($sugeridoPasoJs);
    if (sugerido && sugerido.procesoplantaid) {
        selectProceso.value = String(sugerido.procesoplantaid);
        actualizarMaquinas();
        if (sugerido.maquinaplantaid) {
            selectMaquina.value = String(sugerido.maquinaplantaid);
            actualizarOperador();
        }
        const obs = document.querySelector('#formAsignarEtapa input[name="observaciones"]');
        if (obs && sugerido.notas && !obs.value) obs.value = sugerido.notas;
    }
})();

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

    const formAsignarEtapa = document.getElementById('formAsignarEtapa');
    if (formAsignarEtapa) {
        formAsignarEtapa.addEventListener('submit', function () {
            try {
                sessionStorage.setItem('lp_procesamiento_scroll', String(window.scrollY));
            } catch (err) {}
        });
    }
})();
</script>
@endpush
