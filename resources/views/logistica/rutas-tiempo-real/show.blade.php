@extends('layouts.app')

@section('title', 'Recorrido '.$titulo.' | AgroFusion')
@section('page_title', 'Recorrido en vivo')

@push('styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
#mapaSimulacionVivo{height:480px;width:100%;min-height:420px;border-radius:12px;border:1px solid #dee2e6;z-index:1}
.sim-live-panel{border:0;border-radius:14px;box-shadow:0 4px 18px rgba(18,38,63,.08)}
.leaflet-div-icon.ruta-parada-marker{
    width:auto!important;height:auto!important;margin:0!important;padding:0!important;
    background:transparent!important;border:none!important;
}
.leaflet-marker-icon.sim-camion-marker{
    width:36px!important;height:36px!important;
    margin-left:-18px!important;margin-top:-18px!important;
    background:transparent!important;border:none!important;
}
.sim-camion-pin{
    width:36px;height:36px;border-radius:50%;
    background:#ea580c;border:2px solid #fff;color:#fff;
    display:flex!important;align-items:center;justify-content:center;
    line-height:1;
    box-shadow:0 3px 12px rgba(0,0,0,.45);
    pointer-events:auto;
}
.leaflet-marker-icon.sim-camion-marker{z-index:20000!important}
.sim-ruta-pendiente{stroke:#1e40af!important;stroke-opacity:1!important}
.sim-ruta-recorrida{stroke:#ea580c!important;stroke-opacity:1!important}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <p class="text-muted mb-0">Código <strong>{{ $titulo }}</strong></p>
            @if($trayecto)
                <p class="small mb-0">{{ $trayecto }}</p>
            @endif
        </div>
        <a href="{{ route('logistica.rutas-tiempo-real.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mb-3">
                <script type="application/json" id="sim-estado-inicial-{{ $tipo }}-{{ $id }}">@json($estado)</script>
                <div data-sim-mapa-vivo
                     data-sim-tipo="{{ $tipo }}"
                     data-sim-id="{{ $id }}"
                     data-sim-estado-id="sim-estado-inicial-{{ $tipo }}-{{ $id }}"
                     data-sim-inicio-unix="{{ $estado['inicio_at_unix'] ?? '' }}"
                     data-sim-duracion="{{ $estado['duracion_seg'] ?? 60 }}"
                     data-sim-volver-url="{{ route('logistica.rutas-tiempo-real.index') }}">
                    <div class="row">
                        <div class="col-lg-8 mb-3 mb-lg-0">
                            <div class="card sim-live-panel">
                                <div class="card-body p-2">
                                    <div id="mapaSimulacionVivo" data-sim-mapa></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card sim-live-panel">
                                <div class="card-header bg-white">
                                    <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-tachometer-alt text-primary mr-2"></i>Estado</h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge badge-{{ ($estado['completada'] ?? false) ? 'success' : 'primary' }} sim-live-badge">
                                            {{ ($estado['completada'] ?? false) ? 'Finalizada' : 'En curso' }}
                                        </span>
                                        <span class="h4 mb-0 text-{{ ($estado['completada'] ?? false) ? 'success' : 'primary' }} sim-live-pct">{{ $estado['progreso'] ?? 0 }}%</span>
                                    </div>
                                    <div class="progress mb-3" style="height:10px;">
                                        <div class="progress-bar bg-{{ ($estado['completada'] ?? false) ? 'success' : 'primary' }} sim-live-bar" style="width:{{ $estado['progreso'] ?? 0 }}%"></div>
                                    </div>
                                    <p class="text-muted small mb-0 sim-live-eta">
                                        @if($estado['completada'] ?? false)
                                            Envío recibido en planta.
                                        @elseif(($estado['progreso'] ?? 0) >= 100 || ($estado['segundos_restantes'] ?? 1) <= 0)
                                            Finalizando recepción en planta…
                                        @elseif(($estado['segundos_restantes'] ?? 0) > 0)
                                            @if(($estado['segundos_restantes'] ?? 0) < 60)
                                                Llegada estimada en ~{{ (int) ceil($estado['segundos_restantes']) }} s
                                            @else
                                                Llegada estimada en ~{{ (int) ceil($estado['segundos_restantes'] / 60) }} min
                                            @endif
                                        @else
                                            Calculando…
                                        @endif
                                    </p>
                                    <hr>
                                    <p class="small text-muted mb-0">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Al llegar al destino, el envío se marca como recibido automáticamente.
                                    </p>

                                    @if($puedeCerrarManual ?? false)
                                    <div class="sim-cierre-manual border-top pt-3 mt-3">
                                        <p class="small text-muted mb-2">
                                            <i class="fas fa-satellite-dish mr-1"></i>
                                            Si hay un problema con el GPS o el seguimiento, puede cerrar el recorrido manualmente.
                                        </p>
                                        <form method="POST"
                                              action="{{ route('logistica.simulacion.completar-manual', [$tipo, $id]) }}"
                                              class="mb-0">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button"
                                                    class="btn btn-outline-success btn-sm btn-block font-weight-bold"
                                                    data-confirm-modal
                                                    data-confirm-tone="success"
                                                    data-confirm-title="Confirmar cierre manual"
                                                    data-confirm-message="¿Confirma que el {{ $tipo === 'agricola' ? 'envío llegó a planta' : 'recorrido de distribución finalizó' }}? Se registrará como completado aunque el seguimiento GPS no haya llegado al 100%.">
                                                <i class="fas fa-check-double mr-1"></i>
                                                {{ $tipo === 'agricola' ? 'Marcar como recibido en planta' : 'Marcar ruta como completada' }}
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('partials.modal-confirmar-accion')
@endsection

@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script src="{{ asset('js/simulacion-ruta.js') }}?v=7"></script>
@endpush
