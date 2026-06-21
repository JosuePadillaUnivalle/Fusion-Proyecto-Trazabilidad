@extends('layouts.app')

@section('title', 'Ruta en tiempo real | AgroFusion')
@section('page_title', 'Ruta en tiempo real')

@push('styles')
@include('partials.logistica-modulo-styles')
@include('logistica.partials.ruta-tiempo-real-estilos')
<style>
.rt-live-pct{font-size:1.1rem;font-weight:800}
.sim-lista-avance{min-width:160px;padding-top:20px}
.sim-lista-camion{
    position:absolute;top:0;transform:translateX(-50%);
    width:20px;height:20px;border-radius:50%;
    background:#fff;border:2px solid currentColor;
    display:flex;align-items:center;justify-content:center;
    font-size:10px;box-shadow:0 1px 4px rgba(0,0,0,.25);
    transition:left .15s linear;z-index:2;
}
.rt-filtros-card .form-control,.rt-filtros-card .custom-select{border-radius:10px}
.rt-origen-text{color:#64748b;font-size:.85rem}
.rt-destino-text{font-weight:600;color:#1e293b}
</style>
@endpush

@section('content')
<div class="rt-page-head">
    <div>
        <h2 class="rt-page-head__title">Rutas en simulación</h2>
        <p class="rt-page-head__subtitle">Supervise envíos activos. Filtre por tipo o abra el mapa global.</p>
    </div>
    <div class="rt-page-head__actions">
        <a href="{{ route('logistica.rutas-tiempo-real.mapa') }}" class="btn btn-sm rt-btn-mapa">
            <i class="fas fa-map mr-1"></i> Ver en mapa
        </a>
        <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-list mr-1"></i> Todos los envíos
        </a>
    </div>
</div>

<section class="content pt-0">
    <div class="container-fluid">
        <div class="rt-info-banner">
            <i class="fas fa-satellite-dish mr-1"></i>
            El transportista inicia cada ruta desde su detalle. Al cerrarse el seguimiento, el envío sale de esta lista.
        </div>

        <div class="rt-leyenda-trayectos" aria-label="Tipos de ruta">
            @foreach($variantes as $meta)
            <span class="rt-leyenda-item">
                <span class="rt-leyenda-item__dot" style="background:{{ $meta['color'] }}"></span>
                <i class="fas {{ $meta['icono'] ?? 'fa-truck' }}" style="color:{{ $meta['color'] }};font-size:.75rem"></i>
                {{ $meta['etiqueta'] }}
            </span>
            @endforeach
        </div>

        <div class="card rt-filtros-card mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('logistica.rutas-tiempo-real.index') }}" class="row align-items-end">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="small text-muted mb-1" for="rt-busqueda">Buscar</label>
                        <input type="search" id="rt-busqueda" name="q" class="form-control form-control-sm"
                               value="{{ $busqueda }}" placeholder="Código, chofer, origen o destino…">
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small text-muted mb-1" for="rt-variante">Tipo de ruta</label>
                        <select id="rt-variante" name="variante" class="custom-select custom-select-sm">
                            <option value="">Todos los tipos</option>
                            @foreach($variantes as $tipo => $meta)
                            <option value="{{ $meta['variante'] }}" @selected($variante === $meta['variante'])>
                                {{ $meta['etiqueta'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-success flex-grow-1">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        @if($busqueda !== '' || $variante)
                        <a href="{{ route('logistica.rutas-tiempo-real.index') }}" class="btn btn-sm btn-light" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card rt-live-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-route mr-2"></i>En curso
                    <span class="badge ml-1 sim-lista-total">{{ $totalActivas }}</span>
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Código</th>
                            <th>Chofer</th>
                            <th>Origen → Destino</th>
                            <th>Avance</th>
                            <th>Tiempo restante</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="sim-lista-tbody">
                        @forelse($rutas as $ruta)
                        @php
                            $color = $ruta['color'] ?? '#2563eb';
                        @endphp
                        <tr class="sim-lista-fila"
                            data-sim-tipo="{{ $ruta['tipo'] }}"
                            data-sim-id="{{ $ruta['id'] }}"
                            data-sim-color="{{ $color }}">
                            <td>
                                <span class="rt-tipo-pill" style="background:{{ $color }}">
                                    <i class="fas {{ $ruta['icono'] ?? 'fa-truck' }}"></i>
                                    {{ $ruta['tipo_etiqueta'] }}
                                </span>
                            </td>
                            <td>
                                <span class="rt-estado-pill">
                                    <i class="fas fa-circle-notch fa-spin" style="font-size:.55rem"></i>
                                    {{ $ruta['estado_etiqueta'] ?? 'En curso' }}
                                </span>
                            </td>
                            <td class="font-weight-bold text-dark">{{ $ruta['codigo'] }}</td>
                            <td>{{ $ruta['chofer'] ?: '—' }}</td>
                            <td>
                                <span class="rt-origen-text">{{ $ruta['origen'] ?? '—' }}</span>
                                <i class="fas fa-long-arrow-alt-right mx-1 text-muted" style="font-size:.75rem"></i>
                                <span class="rt-destino-text">{{ $ruta['destino'] }}</span>
                            </td>
                            <td>
                                <div class="sim-lista-avance position-relative">
                                    <div class="sim-lista-camion" style="left:calc({{ min(100, $ruta['progreso']) }}% - 10px);color:{{ $color }};border-color:{{ $color }}">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar sim-lista-bar" style="width:{{ $ruta['progreso'] }}%;background:{{ $color }}"></div>
                                    </div>
                                    <span class="rt-live-pct small sim-lista-pct" style="color:{{ $color }}">{{ $ruta['progreso'] }}%</span>
                                </div>
                            </td>
                            <td class="text-muted small sim-lista-tiempo">
                                @if($ruta['progreso'] >= 100 || ($ruta['segundos_restantes'] ?? 0) <= 0)
                                    Finalizando entrega…
                                @elseif(($ruta['segundos_restantes'] ?? 0) < 60)
                                    ~{{ (int) ceil($ruta['segundos_restantes']) }} s
                                @else
                                    ~{{ (int) ceil($ruta['segundos_restantes'] / 60) }} min
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ $ruta['ver_url'] }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-map-marked-alt mr-1"></i> Ver recorrido
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr class="sim-lista-vacio">
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-road fa-2x mb-2 d-block opacity-25"></i>
                                @if($busqueda !== '' || $variante)
                                    No hay rutas activas que coincidan con los filtros.
                                @else
                                    No hay rutas en simulación en este momento.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                        @if($rutas->isNotEmpty())
                        <tr class="sim-lista-vacio d-none">
                            <td colspan="8" class="text-center text-muted py-5">
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
<script src="{{ asset('js/simulacion-ruta.js') }}?v=9"></script>
@endpush
