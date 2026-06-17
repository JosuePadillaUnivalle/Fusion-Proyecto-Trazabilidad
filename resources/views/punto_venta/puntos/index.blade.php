@extends('layouts.app')

@section('title', 'Puntos de venta')
@section('page_title', 'Puntos de venta')

@push('styles')
@include('punto_venta.partials.modulo-styles')
<style>
.pdv-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; padding: 1.25rem; }
.pdv-tile {
    border: 1px solid #e8edf2;
    border-radius: 16px;
    background: #fff;
    padding: 1.15rem 1.2rem;
    transition: box-shadow .15s ease, transform .15s ease;
    position: relative;
    overflow: hidden;
}
.pdv-tile:hover { box-shadow: 0 10px 28px rgba(15, 23, 42, .1); transform: translateY(-2px); }
.pdv-tile::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #059669, #34d399);
}
.pdv-tile--inactivo::before { background: linear-gradient(90deg, #94a3b8, #cbd5e1); }
.pdv-tile__head { display: flex; align-items: flex-start; gap: .85rem; margin-bottom: .85rem; }
.pdv-tile__icon {
    width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    color: #047857; font-size: 1.1rem;
}
.pdv-tile--inactivo .pdv-tile__icon { background: #f1f5f9; color: #64748b; }
.pdv-tile__name { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin: 0 0 .15rem; }
.pdv-tile__minorista { font-size: .78rem; color: #64748b; }
.pdv-tile__meta { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .85rem; }
.pdv-tile__addr {
    font-size: .8rem; color: #64748b; margin-bottom: .85rem;
    display: flex; align-items: flex-start; gap: .4rem;
}
.pdv-tile__addr i { margin-top: .15rem; color: #94a3b8; }
.pdv-tile__actions { display: flex; flex-wrap: wrap; gap: .4rem; padding-top: .75rem; border-top: 1px solid #f1f5f9; }
.pdv-tile__actions .btn { border-radius: 8px; font-size: .78rem; font-weight: 600; }
.pdv-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
}
</style>
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

        <div class="pdv-grid">
            @forelse($puntos as $punto)
                <article class="pdv-tile {{ ! $punto->activo ? 'pdv-tile--inactivo' : '' }}">
                    <div class="pdv-tile__head">
                        <div class="pdv-tile__icon"><i class="fas fa-store"></i></div>
                        <div class="flex-grow-1 min-width-0">
                            <h3 class="pdv-tile__name">{{ $punto->nombre }}</h3>
                            @if($esAdmin)
                            <div class="pdv-tile__minorista"><i class="fas fa-user mr-1"></i>{{ $punto->nombreMinorista() }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="pdv-tile__meta">
                        <span class="badge badge-{{ $punto->activo ? 'success' : 'secondary' }}">{{ $punto->activo ? 'Activo' : 'Inactivo' }}</span>
                        <span class="badge badge-{{ $punto->almacenid ? 'info' : 'light' }}">{{ $punto->almacenid ? 'Con stock' : 'Sin inventario' }}</span>
                    </div>
                    <div class="pdv-tile__addr">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ \Illuminate\Support\Str::limit($punto->direccion ?: 'Sin dirección registrada', 70) }}</span>
                    </div>
                    <div class="pdv-tile__actions">
                        <a href="{{ route('punto-venta.puntos.show', $punto) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-eye mr-1"></i> Ver
                        </a>
                        @can('punto_venta.update')
                        <a href="{{ route('punto-venta.puntos.edit', $punto) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                        @can('punto_venta.delete')
                        <form method="POST" action="{{ route('punto-venta.puntos.destroy', $punto) }}" class="d-inline"
                            onsubmit="return confirm('¿Eliminar {{ $punto->nombre }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </article>
            @empty
                <div class="pdv-empty">
                    <i class="fas fa-store fa-2x mb-2 d-block text-muted"></i>
                    No hay puntos de venta registrados.
                    @can('punto_venta.create')
                        <div class="mt-2"><a href="{{ route('punto-venta.puntos.create') }}">Crear el primero</a></div>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>
@endsection
