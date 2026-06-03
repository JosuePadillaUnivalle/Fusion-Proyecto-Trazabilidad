@extends('layouts.app')
@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-head{font-weight:700}
.x-table thead th{background:#f2f7f3;border-bottom:0}
.x-empty{padding:28px;text-align:center;color:#6c757d}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">
            @if(auth()->user()?->can('asignaciones.create'))
                Asignar envíos a transportistas
            @else
                Mis envíos asignados
            @endif
        </h1>
        @if(auth()->user()?->can('asignaciones.create'))
            <p class="text-muted mb-0">Indique qué pedidos o envíos llevará cada chofer y en qué vehículo.</p>
        @else
            <p class="text-muted mb-0">Aquí ve los envíos que la empresa le asignó.</p>
        @endif
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(auth()->user()?->can('asignaciones.create'))
        <div class="log-guia">
            <p class="log-guia-titulo"><i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona?</p>
            <ol>
                <li><strong>Opción rápida:</strong> use <em>Asignación automática</em> (abajo) y el sistema asigna los envíos pendientes al chofer.</li>
                <li><strong>O con ruta:</strong> en <a href="{{ route('logistica.rutas.index') }}"><strong>Rutas de entrega</strong></a> planifique el recorrido; luego asigne los envíos aquí.</li>
                <li>Cuando el cliente reciba el pedido, pulse <strong>Confirmar entrega</strong> en la tabla.</li>
                <li>Si hace falta, suba el comprobante en <a href="{{ route('logistica.documentos.index') }}">Documentos de entrega</a>.</li>
            </ol>
        </div>
        @endif

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

        <div class="card card-outline card-success card-modulo-main elevation-1">
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
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                            @php
                                $badgeClass = $asignacion->estado === 'entregado' ? 'badge-success' : ($asignacion->estado === 'en_ruta' ? 'badge-info' : 'badge-warning');
                            @endphp
                            <tr>
                                <td>{{ $asignacion->externo_envio_id }}</td>
                                <td>{{ $asignacion->transportista?->nombreusuario ?? 'Sin asignar' }}</td>
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
                                    @if(in_array($asignacion->estado, ['recibido_planta', 'entregado', 'entregada'], true) || $asignacion->fecha_recepcion_planta)
                                        <span class="text-success small"><i class="fas fa-check mr-1"></i>Recibido en planta</span>
                                    @elseif(in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true))
                                        <span class="text-info small">En transporte hacia planta</span>
                                    @elseif(in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente'], true))
                                        @can('asignaciones.update')
                                        <form method="POST" action="{{ route('logistica.asignaciones.en-transporte', $asignacion) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-shipping-fast mr-1"></i>Iniciar transporte a planta
                                            </button>
                                        </form>
                                        @endcan
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="x-empty">
                                    Aún no hay envíos asignados.
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
@endsection
