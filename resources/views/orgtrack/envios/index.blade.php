@extends('layouts.app')

@section('title', 'Envíos | AgroFusion')
@section('page_title', 'Envíos (Fusion)')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Envíos</li>
@endsection

@section('content')
<div class="modulo-env">
    @include('partials.flash-messages')

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Envíos"
            icono="fa-shipping-fast"
            :registros="$envios->total()"
            filtros-target="#filtrosEnviosPanel"
        />

        <div id="filtrosEnviosPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','estado']) || request('filtros_abiertos') === '1' ? 'show' : '' }}">
            <form method="GET" action="{{ route('orgtrack.envios.index') }}" id="filtrosEnviosForm">
                <div class="row align-items-end">
                    <div class="col-lg-5 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}"
                                placeholder="Planta, transportista o vehículo…">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach(['pendiente','en_ruta','entregado'] as $estado)
                                <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-filtros-form-actions :limpiar-url="route('orgtrack.envios.index', ['filtros_abiertos' => 1])" />
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pedido</th>
                        <th>Transportista</th>
                        <th>Vehículo</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th style="width: 150px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($envios as $e)
                    <tr>
                        <td class="text-muted font-weight-bold">#{{ $e->envioasignacionmultipleid }}</td>
                        <td>{{ optional($e->pedido)->nombre_planta ?? '—' }}</td>
                        <td>{{ optional($e->transportista)->nombre ?? '—' }}</td>
                        <td>{{ $e->vehiculo_ref ?? '—' }}</td>
                        <td>{{ $e->estado }}</td>
                        <td>{{ optional($e->fecha_asignacion)->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-center btn-actions">
                            <a href="{{ route('orgtrack.envios.show', $e->envioasignacionmultipleid) }}" class="btn btn-sm btn-outline-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('envios.update')
                            <a href="{{ route('orgtrack.envios.edit', $e->envioasignacionmultipleid) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('envios.delete')
                            <form action="{{ route('orgtrack.envios.destroy', $e->envioasignacionmultipleid) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('¿Eliminar envío?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-shipping-fast fa-3x mb-3 d-block"></i>
                            No hay envíos registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($envios->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-2 mb-md-0">
                Mostrando {{ $envios->firstItem() }}–{{ $envios->lastItem() }} de {{ $envios->total() }}
            </small>
            {{ $envios->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
