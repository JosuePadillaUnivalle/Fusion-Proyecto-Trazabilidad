@extends('layouts.app')

@php
    $soloListado = $soloListado ?? request()->boolean('listado');
@endphp

@php
    $esTransp = $esTransportista ?? false;
    $tituloPanel = $esTransp ? 'Mis envíos' : 'Panel de envíos';
@endphp
@section('title', $soloListado ? $tituloPanel.' | AgroFusion' : 'Asignar envíos | AgroFusion')
@section('page_title', $soloListado ? $tituloPanel : 'Asignar envíos a transportistas')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-head{font-weight:700}
.x-table thead th{background:#f2f7f3;border-bottom:0}
.x-empty{padding:28px;text-align:center;color:#6c757d}
.envios-filtros-card{border:0;border-radius:14px;box-shadow:0 2px 12px rgba(18,38,63,.06)}
.env-resumen .metric-card{border:0;border-radius:12px;box-shadow:0 4px 14px rgba(18,38,63,.08);margin-bottom:.75rem}
.env-resumen .metric-card .inner{padding:.75rem 1rem}
.env-resumen .metric-card h3{font-size:1.5rem;font-weight:800;margin:0}
.env-resumen .metric-card p{font-size:.78rem;margin:0;opacity:.85}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        @if($soloListado)
            <p class="text-muted mb-0">Resumen, filtros y gestión de envíos. Use <strong>Nueva asignación</strong> para asignar chofer y vehículo.</p>
        @elseif(auth()->user()?->can('asignaciones.create'))
            <p class="text-muted mb-0">Indique qué pedidos o envíos llevará cada chofer y en qué vehículo.</p>
        @else
            <p class="text-muted mb-0">Aquí ve los envíos que la empresa le asignó.</p>
        @endif
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if($resumenEnvios ?? null)
        <div class="row env-resumen mb-3">
            @foreach([
                ['total', 'Total envíos', 'clipboard-list', 'bg-success'],
                ['asignados', 'Asignados / pendientes', 'user-clock', 'bg-warning'],
                ['en_camino', 'En camino', 'shipping-fast', 'bg-info'],
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

        @if($soloListado && auth()->user()?->can('asignaciones.create'))
            <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('logistica.asignaciones.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nueva asignación
                </a>
                @if($transportistaFiltro ?? null)
                    <span class="badge badge-info px-3 py-2">
                        Filtrando: {{ trim($transportistaFiltro->nombre.' '.($transportistaFiltro->apellido ?? '')) }}
                    </span>
                    <a href="{{ route('logistica.asignaciones.listado') }}" class="small">Ver todos</a>
                @endif
            </div>
        @endif

        @if(! $soloListado && auth()->user()?->can('asignaciones.create'))
        <div class="log-guia">
            <p class="log-guia-titulo"><i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona?</p>
            <ol>
                <li><strong>Opción rápida:</strong> use <em>Asignación automática</em> (abajo) y el sistema asigna los envíos pendientes al chofer.</li>
                <li><strong>O con ruta:</strong> en <strong>Envíos → Rutas de entrega</strong> (o el <a href="{{ route('logistica.rutas.mapa') }}">Mapa de envíos</a>) planifique el recorrido; luego asigne los envíos aquí.</li>
                <li>Cuando el cliente reciba el pedido, pulse <strong>Confirmar entrega</strong> en la tabla.</li>
                <li>Si hace falta, suba el comprobante en <a href="{{ route('logistica.documentos.index') }}">Documentos de entrega</a>.</li>
            </ol>
        </div>
        @endif

        @if(! $soloListado)
        @can('asignaciones.multiple')
        <div class="row mb-3">
            <div class="col-lg-6 mb-3">
                <div class="card x-card border-primary h-100">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title x-head mb-0"><i class="fas fa-bolt mr-1"></i> Asignación automática</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Asigna de una vez los envíos <strong>sin chofer</strong> o en situación <strong>pendiente</strong>.
                            @if($enviosPendientes->count() > 0)
                                <span class="text-primary font-weight-bold">Hay {{ $enviosPendientes->count() }} listos.</span>
                            @else
                                <span class="text-warning">Ahora no hay pendientes; revise la tabla o cree envíos en Envíos.</span>
                            @endif
                        </p>
                        <form method="POST" action="{{ route('logistica.asignaciones.asignar-automatica') }}">
                            @csrf
                            <div class="form-group">
                                <label>Chofer <span class="text-danger">*</span></label>
                                <select name="transportista_usuarioid" class="form-control" required>
                                    <option value="">— Elija chofer —</option>
                                    @foreach($transportistas as $t)
                                        <option value="{{ $t->usuarioid }}">{{ $t->nombre }} {{ $t->apellido }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Placa o vehículo</label>
                                <input type="text" name="vehiculo_ref" class="form-control" placeholder="Ej: 1234-ABC" maxlength="80">
                            </div>
                            <div class="form-group">
                                <label>Usar ruta existente (opcional)</label>
                                <select name="rutamultientregaid" class="form-control">
                                    <option value="">— Crear ruta nueva automáticamente —</option>
                                    @foreach($rutasDisponibles as $r)
                                        <option value="{{ $r->rutamultientregaid }}">
                                            {{ $r->nombre }} ({{ $r->transportista?->nombreusuario ?? 'sin chofer' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="crear_ruta" name="crear_ruta" value="1" checked>
                                <label class="custom-control-label" for="crear_ruta">
                                    Crear ruta de entrega si no elige una arriba
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" {{ $enviosPendientes->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-magic mr-1"></i> Asignar pendientes automáticamente
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card x-card border-success h-100">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title x-head mb-0"><i class="fas fa-list-check mr-1"></i> Asignación paso a paso</h3>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="mb-2">Si prefiere elegir envío por envío: elija chofer, marque la lista y confirme.</p>
                        <a href="{{ route('logistica.asignaciones.create') }}" class="btn btn-success btn-lg mt-auto">
                            <i class="fas fa-play mr-1"></i> Comenzar asignación manual
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($enviosPendientes->isNotEmpty())
        <div class="card x-card mb-3">
            <div class="card-header"><h3 class="card-title x-head mb-0">Vista previa — envíos pendientes ({{ $enviosPendientes->count() }})</h3></div>
            <div class="card-body table-responsive p-0" style="max-height:200px;overflow-y:auto">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>Código</th><th>Destino</th><th>Situación</th></tr></thead>
                    <tbody>
                        @foreach($enviosPendientes as $e)
                            <tr>
                                <td>{{ $e->externo_envio_id }}</td>
                                <td>{{ $e->pedido?->nombre_planta ?? '—' }}</td>
                                <td>{{ $e->estado ?? 'pendiente' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endcan
        @endif

        @php
            $urlFiltros = $soloListado
                ? route('logistica.asignaciones.listado')
                : route('logistica.asignaciones.index');
        @endphp
        <div class="card envios-filtros-card mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ $urlFiltros }}" class="form-row align-items-end">
                    @unless($soloListado)
                        <input type="hidden" name="listado" value="1">
                    @endunless
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="search" name="q" class="form-control form-control-sm"
                               value="{{ request('q') }}" placeholder="Código envío, pedido, chofer…">
                    </div>
                    @unless($esTransportista ?? false)
                        @if(($transportistas ?? collect())->isNotEmpty())
                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="small text-muted mb-1">Chofer</label>
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
                    @endunless
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Situación</label>
                        <select name="estado" class="custom-select custom-select-sm">
                            <option value="">Todas</option>
                            @foreach($estadosEnvio ?? [] as $estVal => $estLabel)
                                <option value="{{ $estVal }}" @selected(request('estado') === $estVal)>{{ $estLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Vehículo</label>
                        <input type="text" name="vehiculo" class="form-control form-control-sm"
                               value="{{ request('vehiculo') }}" placeholder="Placa o ref.">
                    </div>
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
                @if(request()->except('page', 'listado'))
                    <p class="small text-muted mb-0 mt-2">
                        Filtros activos.
                        <a href="{{ $urlFiltros }}">Limpiar</a>
                    </p>
                @endif
            </div>
        </div>

        <div class="card card-outline card-success card-modulo-main elevation-1" id="envios-asignados-tabla">
            <x-modulo-index-header
                titulo="Envíos ya asignados"
                icono="fa-truck-loading"
                :registros="$asignaciones->total()"
                :nuevo-href="route('logistica.asignaciones.create')"
                nuevo-text="Asignar manualmente"
                nuevo-can="asignaciones.create"
            />
            <div class="card-body table-responsive p-0">
                <table class="table table-hover x-table">
                    <thead>
                        <tr>
                            <th>Código de envío</th>
                            <th>Chofer</th>
                            <th>Ruta de entrega</th>
                            <th>Vehículo</th>
                            <th>Situación</th>
                            <th>Fecha</th>
                            <th style="min-width:160px">Gestión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                            @php
                                $badgeClass = $asignacion->estado === 'entregado' ? 'badge-success' : ($asignacion->estado === 'en_ruta' ? 'badge-info' : 'badge-warning');
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('logistica.asignaciones.show', $asignacion) }}" class="font-weight-bold text-success">
                                        {{ $asignacion->externo_envio_id }}
                                    </a>
                                </td>
                                <td>{{ trim(($asignacion->transportista?->nombre ?? '').' '.($asignacion->transportista?->apellido ?? '')) ?: ($asignacion->transportista?->nombreusuario ?? 'Sin asignar') }}</td>
                                <td>
                                    @if($asignacion->ruta)
                                        <a href="{{ route('logistica.rutas.show', $asignacion->ruta) }}">{{ $asignacion->ruta->nombre }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $asignacion->vehiculo_ref ?? '—' }}</td>
                                <td>@include('logistica.partials.etiqueta-estado', ['estado' => $asignacion->estado, 'clase' => $badgeClass])</td>
                                <td>{{ optional($asignacion->fecha_asignacion)->format('d/m/Y H:i') }}</td>
                                <td>
                                    @include('logistica.partials.acciones-tabla-asignacion', ['asignacion' => $asignacion])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="x-empty">
                                    Aún no hay envíos asignados.
                                    @can('asignaciones.create')
                                        <a href="{{ route('logistica.asignaciones.create') }}" class="d-block mt-2">Crear primera asignación</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $asignaciones->links() }}</div>
        </div>
    </div>
</section>

@include('partials.modal-confirmar-accion')
@endsection
