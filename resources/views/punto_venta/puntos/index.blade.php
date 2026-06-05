@extends('layouts.app')

@section('title', 'Puntos de venta')
@section('page_title', 'Puntos de venta')

@push('styles')
@include('punto_venta.partials.modulo-styles')
@endpush

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    <div class="card pdv-card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Minoristas y puntos de venta"
            icono="fa-store"
            :registros="$puntos->count()"
            filtros-target="#filtrosPdvPanel"
            :nuevo-href="route('punto-venta.puntos.create')"
            nuevo-text="Nuevo punto de venta"
            nuevo-can="punto_venta.create"
        />

        <div id="filtrosPdvPanel" class="collapse {{ request()->hasAny(['q','activo']) ? 'show' : '' }}">
            @include('partials.modulo-filtros-form', [
                'action' => route('punto-venta.puntos.index'),
                'campos' => [
                    ['name' => 'q', 'label' => 'Buscar', 'placeholder' => 'Nombre, dirección o minorista…', 'col' => 'col-md-5'],
                    ['name' => 'activo', 'label' => 'Estado', 'type' => 'select', 'col' => 'col-md-3', 'options' => ['1' => 'Activos', '0' => 'Inactivos']],
                ],
            ])
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Punto de venta</th>
                            @if($esAdmin)<th>Minorista</th>@endif
                            <th>Dirección</th>
                            <th>Inventario</th>
                            <th>Estado</th>
                            <th class="text-center" style="min-width:200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($puntos as $punto)
                            <tr class="{{ ! $punto->activo ? 'text-muted' : '' }}">
                                <td><strong>{{ $punto->nombre }}</strong></td>
                                @if($esAdmin)<td>{{ $punto->nombreMinorista() }}</td>@endif
                                <td>{{ \Illuminate\Support\Str::limit($punto->direccion ?: '—', 50) }}</td>
                                <td>
                                    @if($punto->almacenid)
                                        <span class="badge badge-info">Con stock</span>
                                    @else
                                        <span class="badge badge-light">Vacío</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $punto->activo ? 'success' : 'secondary' }}">
                                        {{ $punto->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-center text-nowrap pdv-acciones-grupo">
                                    <a href="{{ route('punto-venta.puntos.show', $punto) }}" class="btn btn-xs btn-primary" title="Ver detalles">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    @can('punto_venta.update')
                                    <a href="{{ route('punto-venta.puntos.edit', $punto) }}" class="btn btn-xs btn-outline-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('punto_venta.delete')
                                    <form method="POST" action="{{ route('punto-venta.puntos.destroy', $punto) }}" class="d-inline"
                                        onsubmit="return confirm('¿Eliminar {{ $punto->nombre }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $esAdmin ? 6 : 5 }}" class="text-center text-muted py-4">
                                    No hay puntos de venta registrados.
                                    @can('punto_venta.create')
                                        <a href="{{ route('punto-venta.puntos.create') }}">Crear el primero</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
