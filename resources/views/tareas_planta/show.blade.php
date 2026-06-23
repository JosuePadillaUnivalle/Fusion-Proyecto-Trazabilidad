@extends('layouts.app')

@section('title', 'Detalle de tarea | AgroFusion')
@section('page_title', 'Detalle de tarea')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tareas-planta.index') }}">Mis tareas</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@push('styles')
<style>
.tp-detail-hero {
    background: linear-gradient(135deg, #14532d 0%, #2c5530 55%, #4a7c59 100%);
    border-radius: 16px;
    color: #fff;
    padding: 1.35rem 1.5rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 10px 28px rgba(20, 83, 45, .22);
}
.tp-detail-hero h1 { font-size: 1.45rem; font-weight: 800; margin: 0 0 .35rem; }
.tp-detail-hero-meta { opacity: .92; font-size: .9rem; margin: 0; }
.tp-detail-hero .tp-badge-estado {
    display: inline-flex; align-items: center; gap: .35rem;
    border-radius: 999px; padding: .28rem .75rem; font-size: .78rem; font-weight: 700;
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.25);
}
.tp-detail-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
@media (min-width: 992px) { .tp-detail-grid { grid-template-columns: 1.15fr .85fr; } }
.tp-card {
    background: #fff; border-radius: 16px; overflow: hidden;
    box-shadow: 0 8px 24px rgba(15, 23, 42, .06); border: 1px solid #eef2f0;
}
.tp-card-hd {
    padding: .95rem 1.15rem; font-weight: 700; color: #1f2937;
    border-bottom: 1px solid #f1f5f3; background: #fafcfb;
}
.tp-kv { display: grid; grid-template-columns: 1fr; gap: .65rem; padding: 1rem 1.15rem 1.1rem; }
.tp-kv-item {
    display: flex; gap: .75rem; align-items: flex-start;
    padding: .7rem .85rem; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7;
}
.tp-kv-icon {
    width: 2.1rem; height: 2.1rem; border-radius: 10px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    background: #ecfdf5; color: #15803d; font-size: .95rem;
}
.tp-kv-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; font-weight: 700; margin-bottom: .1rem; }
.tp-kv-value { color: #111827; font-weight: 600; font-size: .92rem; line-height: 1.35; }
.tp-action-card { border: 2px solid #bbf7d0; }
.tp-action-hd {
    background: linear-gradient(135deg, #166534, #2c5530);
    color: #fff; padding: 1rem 1.15rem; font-weight: 700;
}
.tp-time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; margin-bottom: 1rem; }
.tp-time-box {
    border-radius: 12px; padding: .75rem .85rem; background: #f0fdf4; border: 1px solid #bbf7d0;
}
.tp-time-box.fin { background: #eff6ff; border-color: #bfdbfe; }
.tp-time-box .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; font-weight: 700; color: #047857; margin-bottom: .15rem; }
.tp-time-box.fin .label { color: #1d4ed8; }
.tp-time-box .value { font-size: .95rem; font-weight: 800; color: #111827; }
.tp-btn-complete {
    border-radius: 12px; padding: .8rem; font-weight: 800; font-size: 1rem;
    box-shadow: 0 8px 20px rgba(44, 85, 48, .28);
}
.tp-done-card { text-align: center; padding: 2rem 1.25rem; }
.tp-done-card i { font-size: 2.5rem; color: #16a34a; margin-bottom: .5rem; }
.tp-alert-block {
    border-radius: 14px; padding: 1rem 1.1rem; background: #fffbeb;
    border: 1px solid #fde68a; color: #92400e;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="tp-detail-hero d-flex flex-wrap justify-content-between align-items-start" style="gap:1rem;">
        <div>
            <div class="mb-2">
                @if($tarea->estaPendiente())
                    <span class="tp-badge-estado"><i class="fas fa-clock"></i> Pendiente</span>
                @else
                    <span class="tp-badge-estado"><i class="fas fa-check"></i> Completada</span>
                @endif
            </div>
            <h1><i class="fas fa-cogs mr-2"></i>{{ $tarea->proceso?->nombre }}</h1>
            <p class="tp-detail-hero-meta mb-0">
                <i class="fas fa-tools mr-1"></i>{{ $tarea->maquina?->nombre }}
                @if($tarea->maquina?->codigo)<span class="opacity-75">({{ $tarea->maquina->codigo }})</span>@endif
                <span class="mx-2">·</span>
                <i class="fas fa-box mr-1"></i>{{ $tarea->loteProduccion?->codigo_lote }}
            </p>
        </div>
        <a href="{{ route('tareas-planta.index') }}" class="btn btn-light btn-sm font-weight-bold" style="border-radius:10px;">
            <i class="fas fa-arrow-left mr-1"></i>Volver
        </a>
    </div>

    <div class="tp-detail-grid">
        @if(!empty($timelineLote))
        <div class="tp-card mb-3" style="grid-column: 1 / -1">
            <div class="tp-card-hd"><i class="fas fa-route text-success mr-2"></i>Ruta del lote</div>
            <div class="p-3">
                @include('planta.partials.timeline-transformacion', ['items' => $timelineLote, 'compacto' => true])
            </div>
        </div>
        @endif
        <div class="tp-card">
            <div class="tp-card-hd"><i class="fas fa-clipboard-list text-success mr-2"></i>Detalle de la tarea</div>
            <div class="tp-kv">
                <div class="tp-kv-item">
                    <div class="tp-kv-icon"><i class="fas fa-project-diagram"></i></div>
                    <div>
                        <div class="tp-kv-label">Proceso</div>
                        <div class="tp-kv-value">{{ $tarea->proceso?->nombre }}</div>
                    </div>
                </div>
                <div class="tp-kv-item">
                    <div class="tp-kv-icon"><i class="fas fa-industry"></i></div>
                    <div>
                        <div class="tp-kv-label">Maquinaria</div>
                        <div class="tp-kv-value">{{ $tarea->maquina?->nombre }}@if($tarea->maquina?->codigo) <span class="text-muted">({{ $tarea->maquina->codigo }})</span>@endif</div>
                    </div>
                </div>
                <div class="tp-kv-item">
                    <div class="tp-kv-icon"><i class="fas fa-flask"></i></div>
                    <div>
                        <div class="tp-kv-label">Lote de producción</div>
                        <div class="tp-kv-value">
                            {{ $tarea->loteProduccion?->codigo_lote }} — {{ $tarea->loteProduccion?->nombre }}
                            <a href="{{ route('procesamiento.show', $tarea->loteProduccion) }}" class="d-inline-block mt-1 small font-weight-bold">Ver lote</a>
                        </div>
                    </div>
                </div>
                <div class="tp-kv-item">
                    <div class="tp-kv-icon"><i class="fas fa-user-check"></i></div>
                    <div>
                        <div class="tp-kv-label">Asignado por</div>
                        <div class="tp-kv-value">{{ $tarea->asignadoPor?->nombreCompleto() ?? '—' }}</div>
                    </div>
                </div>
                <div class="tp-kv-item">
                    <div class="tp-kv-icon"><i class="far fa-calendar-alt"></i></div>
                    <div>
                        <div class="tp-kv-label">Fecha de asignación</div>
                        <div class="tp-kv-value">{{ optional($tarea->creado_en)->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @if(!empty($empaquePlan))
                <div class="tp-kv-item">
                    <div class="tp-kv-icon"><i class="fas fa-box-open"></i></div>
                    <div>
                        <div class="tp-kv-label">Empaquetado</div>
                        <div class="tp-kv-value">{{ $empaquePlan }}</div>
                    </div>
                </div>
                @endif
                @if($tarea->observaciones)
                <div class="tp-kv-item">
                    <div class="tp-kv-icon"><i class="fas fa-sticky-note"></i></div>
                    <div>
                        <div class="tp-kv-label">Observaciones</div>
                        <div class="tp-kv-value">{{ $tarea->observaciones }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div>
            @if($puedeCompletar)
            <div class="tp-card tp-action-card">
                <div class="tp-action-hd"><i class="fas fa-play-circle mr-1"></i>Completar transformación</div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-3">
                        Al confirmar se registrará el inicio (fecha de asignación) y el fin (ahora) de esta etapa en el lote.
                    </p>
                    @if(!empty($vistaEmpaquetado))
                    <div class="alert alert-light border small py-2 px-3 mb-3" style="background:#f0fdf4;border-color:#bbf7d0!important">
                        <strong class="text-success d-block mb-2"><i class="fas fa-box-open mr-1"></i>{{ $vistaEmpaquetado['titulo'] }}</strong>
                        <div class="tp-kv-item mb-0 py-2" style="background:#fff">
                            <div class="w-100">
                                <div class="tp-kv-label">Empaque</div>
                                <div class="tp-kv-value">{{ $vistaEmpaquetado['empaque'] }}</div>
                            </div>
                        </div>
                        <div class="row mt-2 small">
                            <div class="col-6"><strong>Cantidad:</strong> {{ $vistaEmpaquetado['unidades'] }}</div>
                            <div class="col-6"><strong>Producto:</strong> {{ $vistaEmpaquetado['kg_producto'] }}</div>
                            <div class="col-12 mt-1 text-muted">{{ $vistaEmpaquetado['kg_materia'] }} consumidos</div>
                        </div>
                    </div>
                    @elseif(!empty($empaquePlan))
                    <p class="small text-muted mb-3">
                        <span class="text-success font-weight-bold"><i class="fas fa-box-open mr-1"></i>Empaquetar en: {{ $empaquePlan }}</span>
                    </p>
                    @endif
                    <div class="tp-time-grid">
                        <div class="tp-time-box">
                            <div class="label"><i class="far fa-play-circle mr-1"></i>Inicio</div>
                            <div class="value">{{ optional($tarea->creado_en)->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="tp-time-box fin">
                            <div class="label"><i class="far fa-stop-circle mr-1"></i>Fin</div>
                            <div class="value">{{ now()->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('tareas-planta.completar', $tarea) }}" class="mb-0">
                        @csrf
                        @include('planta.partials.form-parametros-etapa', [
                            'parametrosRequeridos' => $parametrosRequeridos ?? [],
                            'prefix' => 'parametros',
                            'inputIdPrefix' => 'tp-param',
                        ])
                        <button type="submit" class="btn btn-success btn-block tp-btn-complete">
                            <i class="fas fa-check-double mr-1"></i>Completar tarea
                        </button>
                    </form>
                </div>
            </div>
            @elseif($tarea->estaPendiente())
            <div class="tp-alert-block">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i>No se puede completar</strong>
                <p class="mb-0 mt-1 small">La transformación de este lote ya finalizó. Si cree que es un error, contacte al jefe de planta.</p>
            </div>
            @else
            <div class="tp-card tp-done-card">
                <i class="fas fa-check-circle d-block"></i>
                <p class="mb-1 font-weight-bold text-success h5">Tarea completada</p>
                <p class="text-muted small mb-0">{{ optional($tarea->completada_en)->format('d/m/Y H:i') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

@include('partials.modal-confirmar-accion')
@endsection
