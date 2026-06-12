@extends('layouts.app')

@section('title', 'Pedidos de planta | Producción agrícola')
@section('page_title', 'Pedidos de planta')

@push('styles')
<style>
.ag-pedidos-filtros {
    border: 0;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(18, 38, 63, .06);
    margin-bottom: 1rem;
}
.ag-pedidos-filtros .form-control,
.ag-pedidos-filtros .custom-select { border-radius: 8px; }
.ag-pedidos-resumen .small-box {
    border-radius: 10px;
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease;
}
.ag-pedidos-resumen .small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, .12);
}
.ag-pedidos-resumen .small-box.active-filter {
    outline: 3px solid rgba(255, 255, 255, .85);
    outline-offset: -3px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, .18);
}
.pedido-estado-badge {
    display: inline-block;
    font-size: .72rem;
    font-weight: 600;
    padding: .28rem .6rem;
    border-radius: 50rem;
    color: #fff;
    white-space: nowrap;
}
.pedido-estado-agricola { background: #64748b; }
.pedido-estado-logistica { background: #6366f1; }
.pedido-estado-confirmado { background: #16a34a; }
.pedido-estado-produccion { background: #d97706; }
.pedido-estado-rechazado { background: #dc2626; }
.pedido-estado-camino { background: #0284c7; }
.pedido-estado-recibido { background: #0d9488; }
</style>
@endpush

@section('content')
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if($pendientes->count() > 0 && ! request()->hasAny(['estado', 'q', 'transporte', 'fase_envio']))
            <div class="alert alert-warning border-warning shadow-sm">
                <i class="fas fa-bell mr-1"></i>
                Tiene <strong>{{ $pendientes->count() }}</strong> pedido(s) pendiente(s) de aceptar.
                Revise la tabla (filas resaltadas) y pulse <strong>Revisar</strong> para aprobar o rechazar.
            </div>
        @endif

        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            Aquí llegan los envíos que requieren aprobación de producción agrícola. Debe <strong>aceptar</strong> y reservar material del almacén
            antes de que el transporte pueda salir hacia planta.
        </div>

        <div class="row mb-3 ag-pedidos-resumen">
            <div class="col-md-4">
                <a href="{{ route('agricola.pedidos.index', ['estado' => 'pendiente_agricola']) }}"
                   class="small-box bg-warning d-block text-white {{ request('estado') === 'pendiente_agricola' ? 'active-filter' : '' }}">
                    <div class="inner">
                        <h3>{{ $pendientes->count() }}</h3>
                        <p>Pendientes de aceptar</p>
                    </div>
                    <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('agricola.pedidos.index', ['estado' => 'aceptado']) }}"
                   class="small-box bg-success d-block text-white {{ request('estado') === 'aceptado' ? 'active-filter' : '' }}">
                    <div class="inner">
                        <h3>{{ $procesados->where('estado', 'confirmado')->count() + $procesados->where('estado', 'en produccion')->count() }}</h3>
                        <p>Aceptados / listos para envío</p>
                    </div>
                    <div class="icon"><i class="fas fa-check"></i></div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('agricola.pedidos.index', ['estado' => 'rechazado']) }}"
                   class="small-box bg-danger d-block text-white {{ request('estado') === 'rechazado' ? 'active-filter' : '' }}">
                    <div class="inner">
                        <h3>{{ $procesados->where('estado', 'rechazado')->count() }}</h3>
                        <p>Rechazados</p>
                    </div>
                    <div class="icon"><i class="fas fa-times"></i></div>
                </a>
            </div>
        </div>

        <div class="card ag-pedidos-filtros mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('agricola.pedidos.index') }}" class="form-row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="search" name="q" class="form-control form-control-sm"
                               value="{{ request('q') }}" placeholder="Código, producto, chofer…">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado pedido</label>
                        <select name="estado" class="custom-select custom-select-sm">
                            <option value="">Todos</option>
                            <option value="pendiente_agricola" @selected(request('estado') === 'pendiente_agricola')>Pendiente agrícola</option>
                            <option value="aceptado" @selected(request('estado') === 'aceptado')>Aceptado / listo</option>
                            <option value="rechazado" @selected(request('estado') === 'rechazado')>Rechazado</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Transporte</label>
                        <select name="transporte" class="custom-select custom-select-sm">
                            <option value="">Todos</option>
                            <option value="con" @selected(request('transporte') === 'con')>Con chofer</option>
                            <option value="sin" @selected(request('transporte') === 'sin')>Sin chofer</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Fase envío</label>
                        <select name="fase_envio" class="custom-select custom-select-sm">
                            <option value="">Todas</option>
                            <option value="pendiente_salida" @selected(request('fase_envio') === 'pendiente_salida')>Pendiente de salida</option>
                            <option value="en_camino" @selected(request('fase_envio') === 'en_camino')>En camino a planta</option>
                            <option value="recibido" @selected(request('fase_envio') === 'recibido')>Recibido en planta</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0 d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm flex-grow-1">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('agricola.pedidos.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
                @if(request()->hasAny(['q', 'estado', 'transporte', 'fase_envio']))
                    <div class="small text-muted mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Mostrando <strong>{{ $pedidos->count() }}</strong> resultado(s) con filtros activos.
                    </div>
                @endif
            </div>
        </div>

        <div class="card card-outline card-success card-modulo-main elevation-1">
            <x-modulo-index-header
                titulo="Pedidos recibidos de la planta"
                icono="fa-leaf"
                :registros="$pedidos->count()"
            />

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped m-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Solicitud</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Entrega deseada</th>
                                <th>Estado</th>
                                <th>Transporte</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedidos as $pedido)
                                @php
                                    $totalKg = $pedido->detalles?->sum('cantidad') ?? 0;
                                    $pendiente = \App\Support\PedidoCatalogo::pendienteAprobacionAgricola($pedido);
                                    $logEnvio = \App\Support\EnvioPedidoService::datosLogistica($pedido->envioAsignacion);
                                    $estadoVisual = \App\Support\PedidoCatalogo::badgeEstadoLista($logEnvio, $pedido);
                                @endphp
                                <tr class="{{ $pendiente ? 'table-warning' : '' }}">
                                    <td><span class="badge badge-dark">{{ $pedido->numero_solicitud }}</span></td>
                                    <td>
                                        <strong>{{ $pedido->detalles->first()?->cultivo_personalizado ?? '—' }}</strong>
                                        @if($pedido->detalles->count() > 1)
                                            <br><small class="text-muted">+{{ $pedido->detalles->count() - 1 }} ítem(s) más</small>
                                        @endif
                                    </td>
                                    <td>{{ number_format($totalKg, 2) }} kg</td>
                                    <td>
                                        @if($pedido->fechaEntregaDeseada)
                                            {{ \Carbon\Carbon::parse($pedido->fechaEntregaDeseada)->format('d/m/Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <span class="pedido-estado-badge {{ $estadoVisual['clase'] }}" title="{{ $estadoVisual['titulo'] ?? $estadoVisual['etiqueta'] }}">
                                            {{ $estadoVisual['etiqueta'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($logEnvio)
                                            <strong>{{ $logEnvio['transportista_nombre'] }}</strong>
                                            <br><small class="text-muted">{{ $logEnvio['vehiculo_nombre'] }} · {{ $logEnvio['placa'] }}</small>
                                            @if($logEnvio['cargado_en_ruta'])
                                                <br><span class="badge badge-info mt-1">En camino a planta</span>
                                            @elseif($logEnvio['recibido_planta'])
                                                <br><span class="badge badge-success mt-1">Recibido</span>
                                            @else
                                                <br><span class="badge badge-secondary mt-1">Asignado</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Sin transportista</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('agricola.pedidos.show', $pedido) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye mr-1"></i> Revisar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        @if(request()->hasAny(['q', 'estado', 'transporte', 'fase_envio']))
                                            No hay pedidos que coincidan con los filtros.
                                            <br><a href="{{ route('agricola.pedidos.index') }}">Quitar filtros</a>
                                        @else
                                            No hay pedidos de planta por ahora.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection
