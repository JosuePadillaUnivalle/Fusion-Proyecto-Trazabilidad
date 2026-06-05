@extends('layouts.app')

@section('title', 'Trazabilidad — '.$lote->nombre)
@section('page_title', 'Trazabilidad: '.$lote->nombre)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.show', $lote) }}">{{ $lote->nombre }}</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@push('styles')
    @include('lotes.partials.detalle-styles')
    @include('lotes.partials.trazabilidad-styles')
@endpush

@section('content')
<div class="trz-dash">
    @include('lotes.partials.detalle-header')
    @include('lotes.partials.detalle-stats')
    @include('lotes.partials.detalle-nav')

    <div class="card lote-section-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-1 text-success">
                        <i class="fas fa-route mr-2"></i>Fase actual:
                        <span class="badge badge-success">{{ $fase_actual_label }}</span>
                    </h5>
                    <p class="text-muted small mb-0">Progreso estimado en la cadena productiva</p>
                </div>
                <span class="h4 mb-0 text-success font-weight-bold">{{ $progreso }}%</span>
            </div>
            <div class="progress-fase mb-3">
                <div class="bar" style="width: {{ $progreso }}%"></div>
            </div>

            <div class="fase-pipeline">
                @foreach($fases_pipeline as $step)
                    @php
                        $titulo = !empty($step['fase_unica'])
                            ? ($step['completada'] ? 'Completada' : 'Pendiente — ocurre una sola vez')
                            : ($step['eventos'].' evento(s)');
                        if (!empty($step['url'])) {
                            $titulo = 'Ir a '.$step['label'].' — '.$titulo;
                        }
                    @endphp
                    @if(!empty($step['url']))
                        <a href="{{ $step['url'] }}" class="fase-step {{ $step['estado'] }} fase-step-link" title="{{ $titulo }}">
                            <i class="fas fa-{{ $step['icon'] }} d-block mb-1"></i>
                            {{ $step['label'] }}
                            @if($step['estado'] === 'next')
                                <span class="d-block small mt-1"><i class="fas fa-arrow-right"></i> Siguiente</span>
                            @endif
                            @if(!empty($step['fase_unica']) && !empty($step['completada']))
                                <span class="d-block small mt-1"><i class="fas fa-check"></i></span>
                            @elseif($step['eventos'] > 0)
                                <span class="badge badge-light">{{ $step['eventos'] }}</span>
                            @endif
                        </a>
                    @else
                        <div class="fase-step {{ $step['estado'] }}" title="{{ $titulo }}">
                            <i class="fas fa-{{ $step['icon'] }} d-block mb-1"></i>
                            {{ $step['label'] }}
                            @if(!empty($step['fase_unica']) && !empty($step['completada']))
                                <span class="d-block small mt-1"><i class="fas fa-check"></i></span>
                            @elseif($step['eventos'] > 0)
                                <span class="badge badge-light">{{ $step['eventos'] }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            @if($fase_actual === 'en_crecimiento' && ! empty($url_asignar_actividad))
                <div class="text-center mb-3 d-flex flex-wrap justify-content-center" style="gap: 8px;">
                    <a href="{{ $url_asignar_actividad }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-tasks mr-1"></i> Asignar actividad
                    </a>
                    @if(!empty($url_siguiente_fase) && !empty($siguiente_fase_label))
                    <a href="{{ $url_siguiente_fase }}" class="btn btn-success btn-sm">
                        <i class="fas fa-forward mr-1"></i> Ir a {{ $siguiente_fase_label }}
                    </a>
                    @endif
                </div>
            @elseif(!empty($url_siguiente_fase) && !empty($siguiente_fase_label))
                <div class="text-center mb-3">
                    <a href="{{ $url_siguiente_fase }}" class="btn btn-success btn-sm">
                        <i class="fas fa-forward mr-1"></i> Ir a {{ $siguiente_fase_label }}
                    </a>
                </div>
            @endif

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
                <p class="small text-success mb-0 mt-3"><i class="fas fa-check-circle mr-1"></i> {{ $pend['resumen'] ?? 'Trazabilidad completa' }}</p>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card lote-section-card" id="historial-eventos">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0"><i class="fas fa-history mr-2 text-success"></i>Historial de eventos</h5>
                    <span class="badge badge-secondary" id="trz-event-count">{{ $trazabilidad->count() }} eventos</span>
                </div>
                <div class="card-body border-bottom pb-3">
                    <h6 class="text-muted small text-uppercase mb-2">
                        <i class="fas fa-filter mr-1"></i> Filtrar historial de eventos
                    </h6>
                    @include('lotes.partials.trazabilidad-filtros', [
                        'action' => route('lotes.trazabilidad', $lote),
                        'anchor' => '#historial-eventos',
                        'cultivos' => collect(),
                        'estados' => collect(),
                        'responsables' => collect(),
                        'fases' => $fases,
                        'filtros' => $filtros,
                        'hideBuscar' => true,
                    ])
                </div>
                <div class="card-body timeline-trz" id="trz-timeline">
                    @forelse($trazabilidad as $evento)
                        <div class="evento-trz"
                            data-tipo="{{ $evento['tipo'] }}"
                            data-fase="{{ $evento['fase'] }}"
                            style="border-left-color: {{ ($fases_evento[$evento['fase']] ?? $fases[$evento['fase']])['color'] ?? '#dee2e6' }}">
                            <div class="d-flex justify-content-between flex-wrap">
                                <div>
                                    <span class="badge badge-fase mr-1" style="background:{{ ($fases_evento[$evento['fase']] ?? $fases[$evento['fase']])['color'] ?? '#6c757d' }}">
                                        {{ $evento['fase_label'] }}
                                    </span>
                                    <span class="badge badge-light text-uppercase">{{ $evento['tipo'] }}</span>
                                </div>
                                <small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i>{{ $evento['fecha_fmt'] }}</small>
                            </div>
                            <strong class="d-block mt-2">{{ $evento['titulo'] }}</strong>
                            <p class="mb-1 small text-muted">{{ $evento['descripcion'] }}</p>
                            @if(!empty($evento['usuario']))
                                <small class="text-muted"><i class="fas fa-user mr-1"></i>{{ $evento['usuario'] }}</small>
                            @endif
                            @if(isset($evento['completada']))
                                <span class="badge badge-{{ $evento['completada'] ? 'success' : 'warning' }} ml-1">
                                    {{ $evento['completada'] ? 'Completada' : 'Pendiente' }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <div class="empty-timeline">
                            <i class="fas fa-inbox d-block"></i>
                            <p class="mb-0">No hay eventos con los filtros aplicados.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @include('lotes.partials.detalle-actions')
</div>
@endsection

@push('scripts')
<script>
(function () {
    if (window.location.hash === '#historial-eventos') {
        var el = document.getElementById('historial-eventos');
        if (el) {
            setTimeout(function () {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }
})();
</script>
@endpush
