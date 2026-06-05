@extends('layouts.app')

@section('title', 'Pedidos de planta | Producción agrícola')
@section('page_title', 'Pedidos de planta')

@section('content')
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            Aquí llegan los pedidos solicitados desde la planta. Debe <strong>aceptar</strong> y reservar material del almacén agrícola
            antes de que logística pueda asignar transportista.
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $pendientes->count() }}</h3>
                        <p>Pendientes de aceptar</p>
                    </div>
                    <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $procesados->where('estado', 'confirmado')->count() + $procesados->where('estado', 'en produccion')->count() }}</h3>
                        <p>Aceptados / listos para envío</p>
                    </div>
                    <div class="icon"><i class="fas fa-check"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $procesados->where('estado', 'rechazado')->count() }}</h3>
                        <p>Rechazados</p>
                    </div>
                    <div class="icon"><i class="fas fa-times"></i></div>
                </div>
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
                                        <span class="badge {{
                                            $pedido->estado === 'confirmado' ? 'badge-success' :
                                            ($pedido->estado === 'rechazado' ? 'badge-danger' :
                                            ($pedido->estado === 'en produccion' ? 'badge-warning' : 'badge-secondary'))
                                        }}">
                                            {{ \App\Support\PedidoCatalogo::etiquetaEstado($pedido->estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php $logEnvio = \App\Support\EnvioPedidoService::datosLogistica($pedido->envioAsignacion); @endphp
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
                                        No hay pedidos de planta por ahora.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection
