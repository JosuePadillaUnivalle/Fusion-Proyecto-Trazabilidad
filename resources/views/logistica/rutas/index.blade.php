@extends('layouts.app')
@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-table thead th{background:#f2f7f3;border-bottom:0}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-start">
        <div>
        <h1 class="m-0">
            @can('rutas_multi.create')
                Rutas de entrega
            @else
                Mis rutas de entrega
            @endcan
        </h1>
        <p class="text-muted mb-0 mt-1">
            @can('rutas_multi.create')
                Organice el recorrido del chofer: lugares donde debe parar y cuándo sale.
            @else
                Rutas que la empresa le asignó para repartir pedidos.
            @endcan
        </p>
        </div>
        <a href="{{ route('logistica.rutas.planificar') }}" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0">
            <i class="fas fa-arrow-left mr-1"></i> Planificar rutas
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @can('rutas_multi.create')
        @php $drivers = \App\Models\Usuario::where('role','transportista')->where('activo', true)->orderBy('nombre')->get(); @endphp
        <div class="card x-card mb-3 border-primary">
            <div class="card-body">
                <h5 class="mb-2"><i class="fas fa-bolt text-primary mr-1"></i> Generar ruta automática</h5>
                <p class="text-muted small mb-3">Arma una ruta con los envíos pendientes sin ruta (hasta 30). Verá el mapa y la lista antes de confirmar la creación.</p>
                <form method="GET" action="{{ route('logistica.rutas.generar-automatica.preview') }}" class="form-inline flex-wrap">
                    <label class="mr-2 mb-2">Filtrar por chofer:</label>
                    <select name="transportista_usuarioid" class="form-control mr-2 mb-2">
                        <option value="">Todos los choferes</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->usuarioid }}">{{ $d->nombre }} {{ $d->apellido }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary mb-2">
                        <i class="fas fa-magic mr-1"></i> Vista previa y confirmar
                    </button>
                </form>
            </div>
        </div>

        <div class="log-guia">
            <p class="log-guia-titulo"><i class="fas fa-route mr-1"></i> Pasos recomendados</p>
            <ol>
                <li><strong>Crear nueva ruta</strong> — ponga un nombre claro (ej. “Zona norte mañana”), elija chofer y fecha de salida.</li>
                <li><strong>Agregar paradas</strong> — cada parada es un lugar o un código de envío donde debe entregar.</li>
                <li>Abra la ruta con <strong>Ver detalle</strong> y cambie la situación a <em>En camino</em> cuando el camión salga.</li>
                <li>En <em>Asignar envíos a transportistas</em> vincule los mismos envíos al chofer de esa ruta.</li>
                <li>Al terminar el día, marque la ruta como <em>Completada</em>.</li>
            </ol>
        </div>
        @endcan

        <div class="card card-outline card-success card-modulo-main elevation-1">
            <x-modulo-index-header
                titulo="Rutas de entrega"
                icono="fa-route"
                :registros="$rutas->total()"
                :nuevo-href="route('logistica.rutas.create')"
                nuevo-text="Nueva ruta"
                nuevo-can="rutas_multi.create"
            >
                <x-slot:tools>
                    @can('rutas_multi.create')
                    <a href="{{ route('logistica.rutas.mapa') }}" class="btn btn-outline-primary btn-sm ml-1">
                        <i class="fas fa-map mr-1"></i> Mapa
                    </a>
                    @endcan
                </x-slot:tools>
            </x-modulo-index-header>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover x-table">
                    <thead>
                        <tr>
                            <th>Nombre de la ruta</th>
                            <th>Chofer</th>
                            <th>Nº de paradas</th>
                            <th>Situación</th>
                            <th>Salida programada</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rutas as $ruta)
                            @php
                                $badgeClass = $ruta->estado === 'completada' ? 'badge-success' : ($ruta->estado === 'en_ruta' ? 'badge-info' : ($ruta->estado === 'cancelada' ? 'badge-danger' : 'badge-warning'));
                            @endphp
                            <tr>
                                <td><strong>{{ $ruta->nombre }}</strong></td>
                                <td>{{ $ruta->transportista?->nombreusuario ?? 'Sin asignar' }}</td>
                                <td>{{ $ruta->paradas_count }}</td>
                                <td>@include('logistica.partials.etiqueta-estado', ['estado' => $ruta->estado, 'clase' => $badgeClass])</td>
                                <td>{{ optional($ruta->fecha_salida)->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('logistica.rutas.show', $ruta) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye mr-1"></i>Ver detalle
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-route mr-1"></i> No hay rutas todavía.
                                    @can('rutas_multi.create')
                                        <br><a href="{{ route('logistica.rutas.create') }}">Crear la primera ruta</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $rutas->links() }}</div>
        </div>

        @can('rutas_multi.create')
        <div class="log-guia log-guia-compact mt-3">
            <strong>Significado de la situación:</strong>
            <em>Planificada</em> = aún no sale;
            <em>En camino</em> = el chofer ya está repartiendo;
            <em>Completada</em> = terminó el recorrido;
            <em>Cancelada</em> = se suspendió la ruta.
        </div>
        @endcan
    </div>
</section>
@endsection
