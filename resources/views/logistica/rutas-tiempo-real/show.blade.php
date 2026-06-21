@extends('layouts.app')

@section('title', 'Recorrido '.$titulo.' | AgroFusion')
@section('page_title', 'Recorrido en vivo')

@push('styles')
@include('logistica.partials.mapa-ruta-styles')
@include('logistica.partials.ruta-tiempo-real-estilos')
<style>
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
    border:2px solid #fff;color:#fff;
    display:flex!important;align-items:center;justify-content:center;
    line-height:1;
    pointer-events:auto;
}
.leaflet-marker-icon.sim-camion-marker{z-index:20000!important}
</style>
@endpush

@section('content')
@php
    $completada = (bool) ($estado['completada'] ?? false);
    $partesTrayecto = $trayecto ? preg_split('/\s*[→\-–—>]\s*/u', $trayecto, 2) : [];
    $metaTipo = \App\Support\SimulacionRutaCatalogo::metaVariante($tipo);
@endphp

<div class="sim-vivo-head" data-sim-tipo="{{ $tipo }}">
    <div class="sim-vivo-head__main">
        <div class="sim-vivo-head__label">Código de envío</div>
        <span class="sim-vivo-head__code">{{ $titulo }}</span>
        <span class="sim-vivo-head__tipo">
            <i class="fas {{ $metaTipo['icono'] ?? 'fa-truck' }}"></i>
            {{ $metaTipo['etiqueta'] }}
        </span>
        @if($trayecto)
        <div class="sim-vivo-head__ruta">
            <div class="sim-vivo-ruta-flow">
                <span class="sim-vivo-ruta-punto sim-vivo-ruta-punto--origen">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <span>{{ trim($partesTrayecto[0] ?? $trayecto) }}</span>
                </span>
                @if(count($partesTrayecto) > 1)
                <span class="sim-vivo-ruta-flecha" aria-hidden="true">
                    <i class="fas fa-long-arrow-alt-right"></i>
                </span>
                <span class="sim-vivo-ruta-punto sim-vivo-ruta-punto--destino">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <span>{{ trim($partesTrayecto[1]) }}</span>
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>
    <div class="sim-vivo-head__actions">
        <a href="{{ $volverUrl ?? route('logistica.rutas-tiempo-real.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
</div>

<section class="content pt-0">
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
                                <div class="card-header">
                                    <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-route mr-2"></i>Estado del recorrido</h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge sim-live-badge {{ $completada ? 'sim-live--completado' : 'sim-live--activo' }}">
                                            {{ $completada ? 'Finalizada' : 'En curso' }}
                                        </span>
                                        <span class="h4 mb-0 sim-live-pct {{ $completada ? 'sim-live--completado' : 'sim-live--activo' }}">{{ $estado['progreso'] ?? 0 }}%</span>
                                    </div>
                                    <div class="progress mb-3" style="height:8px;">
                                        <div class="progress-bar sim-live-bar {{ $completada ? 'sim-live--completado' : 'sim-live--activo' }}" style="width:{{ $estado['progreso'] ?? 0 }}%"></div>
                                    </div>
                                    <p class="sim-live-eta mb-3">
                                        @if($completada)
                                            Recorrido simulado finalizado. Pendiente confirmación con firmas (fase 6).
                                        @elseif(($estado['progreso'] ?? 0) >= 100 || ($estado['segundos_restantes'] ?? 1) <= 0)
                                            Llegada al destino — en espera de recepción formal.
                                        @elseif(($estado['segundos_restantes'] ?? 0) > 0)
                                            @if(($estado['segundos_restantes'] ?? 0) < 60)
                                                Llegada estimada en ~{{ (int) ceil($estado['segundos_restantes']) }} s
                                            @else
                                                Llegada estimada en ~{{ (int) ceil($estado['segundos_restantes'] / 60) }} min
                                            @endif
                                        @else
                                            Calculando posición…
                                        @endif
                                    </p>
                                    <p class="small mb-0" style="color:#64748b;">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        El seguimiento GPS es orientativo. El cierre operativo requerirá firma del transportista y del receptor (próxima fase).
                                    </p>

                                    @if($puedeCerrarManual ?? false)
                                    <div class="sim-cierre-manual border-top pt-3 mt-3">
                                        <p class="small mb-2" style="color:#64748b;">
                                            <i class="fas fa-satellite-dish mr-1"></i>
                                            Si hay un problema con el GPS, puede cerrar el seguimiento manualmente (solo administración).
                                        </p>
                                        <form method="POST"
                                              action="{{ route('logistica.simulacion.completar-manual', [$tipo, $id]) }}"
                                              class="mb-0">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm btn-block font-weight-bold"
                                                    data-confirm-modal
                                                    data-confirm-tone="neutral"
                                                    data-confirm-title="Confirmar cierre manual"
                                                    data-confirm-message="¿Confirma cerrar el seguimiento GPS? Esto no sustituye las firmas de entrega y recepción.">
                                                <i class="fas fa-flag-checkered mr-1"></i>
                                                Cerrar seguimiento manual
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
<script src="{{ asset('js/simulacion-ruta.js') }}?v=9"></script>
@endpush
