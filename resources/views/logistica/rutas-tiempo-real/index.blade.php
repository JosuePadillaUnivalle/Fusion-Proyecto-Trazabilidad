@extends('layouts.app')

@section('title', 'Ruta en tiempo real | AgroFusion')
@section('page_title', 'Ruta en tiempo real')

@push('styles')
@include('partials.logistica-modulo-styles')
<style>
.rt-live-card{border:0;border-radius:14px;box-shadow:0 2px 12px rgba(18,38,63,.08)}
.rt-live-pct{font-size:1.1rem;font-weight:800;color:#2563eb}
.sim-lista-avance{min-width:160px;padding-top:20px}
.sim-lista-camion{
    position:absolute;top:0;transform:translateX(-50%);
    width:20px;height:20px;border-radius:50%;
    background:#fff;border:2px solid #2563eb;color:#2563eb;
    display:flex;align-items:center;justify-content:center;
    font-size:10px;box-shadow:0 1px 4px rgba(0,0,0,.25);
    transition:left .15s linear;z-index:2;
}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center">
        <p class="text-muted mb-0">Envíos con simulación activa en este momento. Seleccione uno para ver el recorrido.</p>
        <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-list mr-1"></i> Todos los envíos
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="alert alert-info">
            <i class="fas fa-satellite-dish mr-1"></i>
            Solo supervisión: el transportista inicia la ruta desde su detalle de envío. Al completarse, el envío desaparece de esta lista.
        </div>

        <div class="card rt-live-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-route text-primary mr-2"></i>En curso
                    <span class="badge badge-primary ml-1 sim-lista-total">{{ $totalActivas }}</span>
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Código</th>
                            <th>Chofer</th>
                            <th>Destino</th>
                            <th>Avance</th>
                            <th>Tiempo restante</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="sim-lista-tbody">
                        @forelse($rutas as $ruta)
                        <tr class="sim-lista-fila" data-sim-tipo="{{ $ruta['tipo'] }}" data-sim-id="{{ $ruta['id'] }}">
                            <td><span class="badge badge-{{ $ruta['tipo'] === 'agricola' ? 'success' : 'primary' }}">{{ $ruta['tipo_etiqueta'] }}</span></td>
                            <td class="font-weight-bold">{{ $ruta['codigo'] }}</td>
                            <td>{{ $ruta['chofer'] ?: '—' }}</td>
                            <td class="small">{{ $ruta['destino'] }}</td>
                            <td>
                                <div class="sim-lista-avance position-relative">
                                    <div class="sim-lista-camion" style="left:calc({{ min(100, $ruta['progreso']) }}% - 10px)">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-primary sim-lista-bar" style="width:{{ $ruta['progreso'] }}%"></div>
                                    </div>
                                    <span class="rt-live-pct small sim-lista-pct">{{ $ruta['progreso'] }}%</span>
                                </div>
                            </td>
                            <td class="text-muted small sim-lista-tiempo">
                                @if($ruta['progreso'] >= 100 || ($ruta['segundos_restantes'] ?? 0) <= 0)
                                    Finalizando recepción en planta…
                                @elseif(($ruta['segundos_restantes'] ?? 0) < 60)
                                    ~{{ (int) ceil($ruta['segundos_restantes']) }} s
                                @else
                                    ~{{ (int) ceil($ruta['segundos_restantes'] / 60) }} min
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ $ruta['ver_url'] }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-map-marked-alt mr-1"></i> Ver recorrido
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr class="sim-lista-vacio">
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-road fa-2x mb-2 d-block opacity-25"></i>
                                No hay rutas en simulación en este momento.
                            </td>
                        </tr>
                        @endforelse
                        @if($rutas->isNotEmpty())
                        <tr class="sim-lista-vacio d-none">
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-road fa-2x mb-2 d-block opacity-25"></i>
                                No hay rutas en simulación en este momento.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/simulacion-ruta.js') }}?v=7"></script>
@endpush
