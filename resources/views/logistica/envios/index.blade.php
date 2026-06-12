@extends('layouts.app')

@php
    $tituloPagina = ($esTransportista ?? false) ? 'Mis envíos' : 'Envíos';
@endphp
@section('title', $tituloPagina.' | AgroFusion')
@section('page_title', $tituloPagina)

@push('styles')
@include('partials.logistica-modulo-styles')
@include('logistica.partials.ayuda-logistica-styles')
<style>
.envios-wrap { padding: 0 .25rem; }
.envios-alert { border-radius: 12px; padding: .65rem 1rem; margin-bottom: .75rem; }
.env-resumen .metric-card{border:0;border-radius:12px;box-shadow:0 4px 14px rgba(18,38,63,.08);margin-bottom:.75rem}
.env-resumen .metric-card .inner{padding:.75rem 1rem}
.env-resumen .metric-card h3{font-size:1.5rem;font-weight:800;margin:0}
.env-resumen .metric-card p{font-size:.78rem;margin:0;opacity:.85}
.envios-filtros-card, .envios-table-card {
    border: 0; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(18, 38, 63, .06);
}
.pedido-estado-badge {
    display: inline-block; font-size: .72rem; font-weight: 600;
    padding: .25rem .6rem; border-radius: 50rem; color: #fff;
    white-space: nowrap;
}
.pedido-estado-agricola { background: #64748b; }
.pedido-estado-logistica { background: #6366f1; }
.pedido-estado-confirmado { background: #16a34a; }
.pedido-estado-produccion { background: #d97706; }
.pedido-estado-rechazado { background: #dc2626; }
.pedido-estado-camino { background: #0284c7; }
.pedido-estado-recibido { background: #0d9488; }
.envios-table-card .td-acciones { white-space: nowrap; min-width: 180px; }
.btn-asignar-transportista { font-size: .78rem; padding: 0; }
</style>
@endpush

@section('content')
<div class="envios-wrap">
    @if($esTransportista ?? false)
        <div class="alert alert-info envios-alert">
            <i class="fas fa-truck mr-1"></i> Envíos asignados a su cuenta: solicitud agrícola y datos de transporte en un solo lugar.
        </div>
    @else
        <div class="alert alert-light border envios-alert">
            <i class="fas fa-route text-success mr-1"></i>
            <strong>Envíos unificados:</strong> cultivo, kg, trayecto almacén agrícola → planta, chofer y vehículo en un solo lugar.
            Los envíos nuevos requieren aceptación agrícola; puede programar el chofer al crearlos
            (<a href="{{ route('agricola.pedidos.index') }}">Producción → Pedidos de planta</a>).
        </div>
    @endif

    @if($resumenEnvios ?? null)
    <div class="row env-resumen mb-3">
        @foreach([
            ['total', 'Total envíos', 'clipboard-list', 'bg-success'],
            ['asignados', 'Pendientes de salida', 'user-clock', 'bg-warning'],
            ['en_camino', 'En camino a planta', 'shipping-fast', 'bg-info'],
            ['recibidos', 'Recibidos en planta', 'warehouse', 'bg-primary'],
            ['recibidos_hoy', 'Llegadas hoy', 'check-circle', 'bg-secondary'],
        ] as [$key, $label, $icon, $bg])
        <div class="col-6 col-lg mb-2">
            <div class="small-box {{ $bg }} metric-card">
                <div class="inner">
                    <h3>{{ $resumenEnvios[$key] ?? 0 }}</h3>
                    <p>{{ $label }}</p>
                </div>
                <div class="icon"><i class="fas fa-{{ $icon }}"></i></div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="card envios-filtros-card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ $urlListado }}" class="form-row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Buscar</label>
                    <input type="search" name="q" class="form-control form-control-sm"
                           value="{{ request('q') }}" placeholder="Código, cultivo, planta, chofer…">
                </div>
                @unless($esTransportista ?? false)
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Chofer</label>
                    <input type="text" name="transportista_nombre" class="form-control form-control-sm"
                           value="{{ request('transportista_nombre') }}" placeholder="Nombre chofer">
                </div>
                @if(($transportistas ?? collect())->isNotEmpty())
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Lista choferes</label>
                    <select name="transportista" class="custom-select custom-select-sm">
                        <option value="">Todos</option>
                        @foreach($transportistas as $t)
                            <option value="{{ $t->usuarioid }}" @selected((string) request('transportista') === (string) $t->usuarioid)>
                                {{ trim($t->nombre.' '.$t->apellido) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Vehículo</label>
                    <input type="text" name="vehiculo" class="form-control form-control-sm"
                           value="{{ request('vehiculo') }}" placeholder="Placa">
                </div>
                @endunless
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Estado solicitud</label>
                    <select name="estado" class="custom-select custom-select-sm">
                        <option value="">Todos</option>
                        @foreach($estadosPedido ?? [] as $estVal => $estLabel)
                            <option value="{{ $estVal }}" @selected(request('estado') === $estVal)>{{ $estLabel }}</option>
                        @endforeach
                    </select>
                </div>
                @unless($esTransportista ?? false)
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Situación transporte</label>
                    <select name="estado_logistica" class="custom-select custom-select-sm">
                        <option value="">Todas</option>
                        @foreach($estadosLogistica ?? [] as $estVal => $estLabel)
                            <option value="{{ $estVal }}" @selected(request('estado_logistica') === $estVal)>{{ $estLabel }}</option>
                        @endforeach
                    </select>
                </div>
                @endunless
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
                </div>
                @unless($esTransportista ?? false)
                <div class="col-auto mb-2 mb-md-0">
                    <label class="small text-muted mb-1 d-block">&nbsp;</label>
                    <div class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="sin_asignar" name="sin_asignar" value="1"
                               @checked(request()->boolean('sin_asignar'))>
                        <label class="form-check-label small" for="sin_asignar">Sin chofer</label>
                    </div>
                </div>
                @endunless
                <div class="col-auto mb-2 mb-md-0">
                    <label class="small text-muted mb-1 d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-success btn-sm px-3">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                </div>
            </form>
            @if(request()->except('page'))
                <p class="small text-muted mb-0 mt-2">Filtros activos. <a href="{{ $urlListado }}">Limpiar</a></p>
            @endif
        </div>
    </div>

    <div class="card envios-table-card">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-2 mb-md-0 font-weight-bold">
                    <i class="fas fa-truck text-success mr-2"></i>{{ $tituloPagina }}
                    <small class="text-muted font-weight-normal">({{ $pedidos->total() }})</small>
                </h3>
                @can('pedidos.create')
                <a href="{{ route('pedidos.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo envío
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Código</th>
                        <th>Cultivo / producto</th>
                        <th>Total</th>
                        <th>Planta destino</th>
                        <th>Chofer</th>
                        <th>Vehículo</th>
                        <th>Trayecto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="td-acciones">Gestión</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        @php
                            $asignacion = $pedido->envioAsignacion;
                            $itemsCount = $pedido->detalles?->count() ?? 0;
                            $totalKg = $pedido->detalles?->sum('cantidad') ?? 0;
                            $transportistaAsignado = $asignacion?->transportista;
                            $logisticaEnvio = \App\Support\EnvioPedidoService::datosLogistica($asignacion);
                            $estadoVisual = \App\Support\PedidoCatalogo::badgeEstadoLista($logisticaEnvio, $pedido);
                            $faseLogistica = \App\Support\PedidoCatalogo::faseLogistica($logisticaEnvio);
                            $codigoEnvio = $asignacion?->externo_envio_id ?? $pedido->numero_solicitud;
                            $verUrl = $asignacion
                                ? route('logistica.asignaciones.show', $asignacion)
                                : route('pedidos.show', $pedido);
                            $trayectoPartes = \App\Support\EnvioPedidoService::trayectoPartesListaPedido($pedido);
                            $plantaDestinoLista = \App\Support\EnvioPedidoService::etiquetaPlantaDestinoLista($pedido);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ $verUrl }}" class="font-weight-bold text-success">{{ $codigoEnvio }}</a>
                                @if($pedido->numero_solicitud !== $codigoEnvio)
                                    <br><small class="text-muted">{{ $pedido->numero_solicitud }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="text-primary font-weight-bold">
                                    {{ $pedido->detalles->first()?->cultivo_personalizado ?? '—' }}
                                </span>
                                @if($itemsCount > 1)
                                    <br><small class="text-muted">+{{ $itemsCount - 1 }} ítem(s) más</small>
                                @endif
                            </td>
                            <td><strong>{{ number_format($totalKg, 2) }}</strong> <small class="text-muted">kg</small></td>
                            <td>
                                @if($plantaDestinoLista)
                                    <span>{{ $plantaDestinoLista }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($transportistaAsignado)
                                    {{ trim($transportistaAsignado->nombre.' '.$transportistaAsignado->apellido) }}
                                @elseif(\App\Support\PedidoCatalogo::puedeAsignarTransportista($pedido))
                                    @can('pedidos.update')
                                    <button type="button" class="btn btn-link btn-sm p-0 text-primary btn-asignar-transportista"
                                        data-pedido-id="{{ $pedido->pedidoid }}"
                                        data-pedido-label="{{ $pedido->numero_solicitud }}">
                                        <i class="fas fa-user-plus mr-1"></i>Asignar
                                    </button>
                                    @else
                                    <span class="text-muted small">Sin asignar</span>
                                    @endcan
                                @else
                                    <span class="text-muted small" title="Pendiente agrícola">—</span>
                                @endif
                            </td>
                            <td>{{ $asignacion?->vehiculo_ref ?? '—' }}</td>
                            <td class="small">
                                @if($trayectoPartes && (($trayectoPartes['recogidas'] ?? []) !== [] || ($trayectoPartes['destino'] ?? null)))
                                    @include('logistica.partials.trayecto-colores', ['trayectoPartes' => $trayectoPartes])
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="pedido-estado-badge {{ $estadoVisual['clase'] }}"
                                      title="{{ $estadoVisual['titulo'] ?? $estadoVisual['etiqueta'] }}">
                                    {{ $estadoVisual['etiqueta'] }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ \Carbon\Carbon::parse($pedido->fechapedido)->format('d/m/Y') }}</td>
                            <td class="td-acciones">
                                @if($asignacion)
                                    @include('logistica.partials.acciones-tabla-asignacion', ['asignacion' => $asignacion])
                                @else
                                    <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                @if($faseLogistica === 'en_camino_planta' && ! $asignacion)
                                    @can('recepcion_planta.confirm')
                                    <form method="POST" action="{{ route('pedidos.confirmar-llegada-planta', $pedido) }}" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-outline-success" data-confirm-modal
                                            data-confirm-tone="success" data-confirm-title="Confirmar llegada"
                                            data-confirm-message="¿Confirma llegada del pedido {{ $pedido->numero_solicitud }} a planta?">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                No hay envíos con esos filtros.
                                @can('pedidos.create')
                                    <a href="{{ route('pedidos.create') }}" class="d-block mt-2">Registrar nuevo envío</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pedidos->hasPages())
        <div class="card-footer">{{ $pedidos->links() }}</div>
        @endif
    </div>
</div>

@include('partials.modal-confirmar-accion')

@can('pedidos.update')
<form id="formAsignarTransportista" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="transportista_usuarioid" id="inputTransportistaAsignar">
    <input type="hidden" name="vehiculoid" id="inputVehiculoAsignar">
</form>
@once
    @push('styles')
    <style>
        #modalSelectorCatalogo .selector-catalogo-row { cursor: pointer; }
        #modalSelectorCatalogo .selector-catalogo-row:hover { background: #f4f9f4; }
    </style>
    @endpush
    @push('scripts')
    <script src="{{ asset('js/selector-catalogo.js') }}"></script>
    @endpush
@endonce
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.CatalogoSelector) return;
    let pedidoIdAsignar = null;
    const form = document.getElementById('formAsignarTransportista');
    const inputTransportista = document.getElementById('inputTransportistaAsignar');
    const inputVehiculo = document.getElementById('inputVehiculoAsignar');
    const urlAsignar = @json(route('pedidos.asignar-transportista', ['pedido' => '__PEDIDO__']));

    document.querySelectorAll('.btn-asignar-transportista').forEach(btn => {
        btn.addEventListener('click', () => {
            pedidoIdAsignar = btn.dataset.pedidoId;
            CatalogoSelector.abrir({
                titulo: 'Asignar transportista',
                subtitulo: 'Pedido ' + (btn.dataset.pedidoLabel || ''),
                tipo: 'transportistas',
                onSeleccionar(item) {
                    if (!form || !pedidoIdAsignar) return;
                    inputTransportista.value = item.id;
                    inputVehiculo.value = item.meta?.vehiculoid || '';
                    form.action = urlAsignar.replace('__PEDIDO__', pedidoIdAsignar);
                    form.submit();
                },
            });
        });
    });
});
</script>
@endpush
@endcan
@endsection
