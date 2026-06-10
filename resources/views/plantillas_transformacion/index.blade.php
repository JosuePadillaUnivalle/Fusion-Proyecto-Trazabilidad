@extends('layouts.app')

@section('title', 'Procesos de transformación | Fusion-Proyectos')
@section('page_title', 'Procesos de transformación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Procesos de transformación</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@endpush

@section('content')
<div class="modulo-prod">
    <div class="alert alert-light border mb-3 small">
        <i class="fas fa-project-diagram text-success mr-1"></i>
        Procesos completos para <strong>puré de papa</strong>, <strong>papas fritas</strong>, snacks, conservas y más.
        Al crear un lote en Procesamiento, el sistema asigna el proceso según el producto y guía cada etapa hasta <strong>Empaquetado</strong>.
        Si una máquina del proceso entra en <strong>mantenimiento</strong>, el proceso queda automáticamente no disponible hasta que la máquina vuelva a estar activa.
    </div>

    <div class="row mb-2">
        <div class="col-lg-4 col-6">
            <div class="small-box small-box-green">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Procesos</p></div>
                <div class="icon"><i class="fas fa-project-diagram"></i></div>
                <span class="small-box-footer">Líneas de producción</span>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box small-box-teal">
                <div class="inner"><h3>{{ $stats['activas'] }}</h3><p>Activas</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">En uso</span>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box small-box-yellow">
                <div class="inner"><h3>{{ $stats['inactivas'] }}</h3><p>Inactivas</p></div>
                <div class="icon"><i class="fas fa-pause-circle"></i></div>
                <span class="small-box-footer">Máquina en mantenimiento</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Procesos de transformación"
            icono="fa-project-diagram"
            :registros="$plantillas->total()"
            filtros-target="#filtrosPlantillasPanel"
            :nuevo-href="route('plantillas-transformacion.create')"
            nuevo-text="Nuevo proceso"
        />
        <div id="filtrosPlantillasPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','estado']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('plantillas-transformacion.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Nombre, producto…">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <option value="activo" @selected(request('estado') === 'activo')>Disponibles</option>
                            <option value="mantenimiento" @selected(request('estado') === 'mantenimiento')>En mantenimiento</option>
                        </select>
                    </div>
                </div>
                <x-filtros-form-actions :limpiar-url="route('plantillas-transformacion.index', ['filtros_abiertos' => 1])" />
            </form>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Producto ejemplo</th>
                        <th>Pasos</th>
                        <th>Estado</th>
                        <th style="width:140px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plantillas as $p)
                    <tr>
                        <td><strong>{{ $p->nombre }}</strong><br><small class="text-muted">{{ \Illuminate\Support\Str::limit($p->descripcion, 60) }}</small></td>
                        <td>{{ $p->producto_ejemplo ?: '—' }}</td>
                        <td><span class="badge badge-light border">{{ $p->pasos_count }} etapas</span></td>
                        <td>
                            @include('plantillas_transformacion.partials.badge-estado', ['plantilla' => $p])
                        </td>
                        <td class="btn-actions text-nowrap">
                            <a href="{{ route('plantillas-transformacion.show', $p) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('plantillas-transformacion.edit', $p) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('plantillas-transformacion.destroy', $p) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este proceso de transformación?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Sin procesos definidos. Ejecute el seeder o cree uno nuevo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plantillas->hasPages())<div class="card-footer">{{ $plantillas->links() }}</div>@endif
    </div>
</div>
@endsection
