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
        <div class="alert alert-info envios-alert d-flex flex-wrap justify-content-between align-items-center">
            <span><i class="fas fa-truck mr-1"></i> Envíos asignados: almacén agrícola → planta y planta → puntos de venta en un solo listado.</span>
            <a href="{{ route('logistica.transportista.ingresos') }}" class="btn btn-sm btn-outline-primary mt-2 mt-md-0">
                <i class="fas fa-coins mr-1"></i> Mis ingresos
            </a>
        </div>
    @else
        <div class="alert alert-light border envios-alert">
            <i class="fas fa-route text-success mr-1"></i>
            <strong>Envíos unificados:</strong> solicitudes agrícolas hacia planta y rutas de distribución hacia minoristas en una sola lista.
            El costo del servicio (Bs) lo define logística al planificar cada ruta.
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
                @if(($transportistas ?? collect())->isNotEmpty())
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Chofer</label>
                    @include('partials.selector-catalogo', [
                        'id' => 'envios_filtro_transportista',
                        'name' => 'transportista',
                        'value' => request('transportista'),
                        'labelSelected' => $transportistaFiltroNombre ?? '',
                        'endpoint' => route('catalogo-selector.usuarios'),
                        'params' => ['roles' => 'transportista'],
                        'title' => 'Filtrar por chofer',
                        'searchPlaceholder' => 'Nombre, usuario o correo…',
                        'searchLabel' => 'Buscar chofer',
                        'allowEmpty' => true,
                        'emptyLabel' => 'Todos los choferes',
                        'placeholderEmpty' => 'Todos',
                        'inputGroup' => true,
                        'showLabel' => false,
                        'modalIcon' => 'fa-user-tie',
                        'rowIcon' => 'fa-id-badge',
                        'colNombre' => 'Transportista',
                        'colDetalle' => 'Usuario / contacto',
                        'variant' => 'filtros',
                    ])
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
                    <small class="text-muted font-weight-normal">({{ $items->total() }})</small>
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
                        <th>Tipo</th>
                        <th>Código</th>
                        <th>Cultivo / producto</th>
                        <th>Total</th>
                        <th>Destino</th>
                        <th>Chofer</th>
                        <th>Vehículo</th>
                        <th>Trayecto</th>
                        <th>Costo (Bs)</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="td-acciones">Gestión</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $item['tipo'] === 'agricola' ? 'success' : 'primary' }}">
                                    {{ $item['tipo_etiqueta'] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ $item['ver_url'] }}" class="font-weight-bold text-success">{{ $item['codigo'] }}</a>
                                @if($item['subcodigo'])
                                    <br><small class="text-muted">{{ $item['subcodigo'] }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="text-primary font-weight-bold">{{ $item['producto_label'] }}</span>
                                @if($item['producto_extra'])
                                    <br><small class="text-muted">{{ $item['producto_extra'] }}</small>
                                @endif
                            </td>
                            <td>
                                @if($item['total_kg'])
                                    <strong>{{ number_format($item['total_kg'], 2) }}</strong> <small class="text-muted">kg</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($item['destino_label'])
                                    <span>{{ $item['destino_label'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($item['chofer_nombre'])
                                    {{ $item['chofer_nombre'] }}
                                @elseif($item['puede_asignar'])
                                    @can('pedidos.update')
                                    <button type="button" class="btn btn-link btn-sm p-0 text-primary btn-asignar-transportista"
                                        data-pedido-id="{{ $item['pedido']->pedidoid }}"
                                        data-pedido-label="{{ $item['pedido']->numero_solicitud }}">
                                        <i class="fas fa-user-plus mr-1"></i>Asignar
                                    </button>
                                    @else
                                    <span class="text-muted small">Sin asignar</span>
                                    @endcan
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>{{ $item['vehiculo_placa'] ?? '—' }}</td>
                            <td class="small">
                                @if($item['trayecto_partes'] && (($item['trayecto_partes']['recogidas'] ?? []) !== [] || ($item['trayecto_partes']['destino'] ?? null)))
                                    @include('logistica.partials.trayecto-colores', ['trayectoPartes' => $item['trayecto_partes']])
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($item['costo_bs'] !== null)
                                    <strong>{{ number_format($item['costo_bs'], 2, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="pedido-estado-badge {{ $item['estado_badge']['clase'] }}"
                                      title="{{ $item['estado_badge']['titulo'] ?? $item['estado_badge']['etiqueta'] }}">
                                    {{ $item['estado_badge']['etiqueta'] }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $item['fecha']?->format('d/m/Y') ?? '—' }}</td>
                            <td class="td-acciones">
                                @if($item['asignacion'])
                                    @include('logistica.partials.acciones-tabla-asignacion', ['asignacion' => $item['asignacion']])
                                @elseif($item['ruta'])
                                    <a href="{{ $item['ver_url'] }}" class="btn btn-sm btn-outline-info" title="Ver ruta">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @elseif($item['pedido'])
                                    <a href="{{ route('pedidos.show', $item['pedido']) }}" class="btn btn-sm btn-outline-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                @if(($item['fase_logistica'] ?? null) === 'en_camino_planta' && ! $item['asignacion'] && $item['pedido'])
                                    @can('recepcion_planta.confirm')
                                    <form method="POST" action="{{ route('pedidos.confirmar-llegada-planta', $item['pedido']) }}" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-outline-success" data-confirm-modal
                                            data-confirm-tone="success" data-confirm-title="Confirmar llegada"
                                            data-confirm-message="¿Confirma llegada del pedido {{ $item['pedido']->numero_solicitud }} a planta?">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-5">
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
        @if($items->hasPages())
        <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
</div>

@include('partials.modal-confirmar-accion')

@can('pedidos.update')
<form id="formAsignarTransportista" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="transportista_usuarioid" id="inputTransportistaAsignar">
    <input type="hidden" name="vehiculoid" id="inputVehiculoAsignar">
    <input type="hidden" name="costo_bs" id="inputCostoAsignar">
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

    const inputCosto = document.getElementById('inputCostoAsignar');

    function solicitarCostoYEnviar() {
        if (!form || !pedidoIdAsignar) return;
        const pedirCosto = (valor) => {
            const costo = parseFloat(String(valor).replace(',', '.'));
            if (!Number.isFinite(costo) || costo <= 0) {
                if (window.Swal) {
                    Swal.fire('Costo inválido', 'Ingrese un monto mayor a 0 en bolivianos.', 'warning');
                } else {
                    alert('Ingrese un costo válido en bolivianos.');
                }
                return;
            }
            inputCosto.value = costo.toFixed(2);
            form.action = urlAsignar.replace('__PEDIDO__', pedidoIdAsignar);
            form.submit();
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Costo del servicio (Bs)',
                input: 'number',
                inputAttributes: { min: '0.01', step: '0.01' },
                inputPlaceholder: 'Ej. 150.00',
                showCancelButton: true,
                confirmButtonText: 'Asignar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) pedirCosto(result.value);
            });
        } else {
            const valor = prompt('Costo del servicio en bolivianos (Bs):');
            if (valor !== null) pedirCosto(valor);
        }
    }

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
                    solicitarCostoYEnviar();
                },
            });
        });
    });
});
</script>
@endpush
@endcan
@endsection
