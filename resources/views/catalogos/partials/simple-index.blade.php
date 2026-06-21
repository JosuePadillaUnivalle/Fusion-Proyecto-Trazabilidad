@php

    $tieneDescripcion = $tieneDescripcion ?? false;

    $kpiClass = $kpiClass ?? 'small-box-green';

    $filtrosAbiertos = request()->filled('buscar') || request('filtros_abiertos') === '1';

@endphp



<div class="modulo-cat">



<div class="row mb-2">

        <div class="col-lg-4 col-6">

            <div class="small-box {{ $kpiClass }}">

                <div class="inner">

                    <h3>{{ $items->total() }}</h3>

                    <p>Total registros</p>

                </div>

                <div class="icon"><i class="fas {{ $icono }}"></i></div>

                <span class="small-box-footer">{{ $subtitulo }}</span>

            </div>

        </div>

    </div>



    <div class="card card-outline card-success card-modulo-main elevation-1 mb-3">

        <x-modulo-index-header

            :titulo="$titulo"

            :icono="$icono"

            :registros="$items->total()"

            filtros-target="#filtrosCatPanel"

            :nuevo-href="route($routePrefix.'.create')"

        />



        <div id="filtrosCatPanel" class="filtros-panel collapse {{ $filtrosAbiertos ? 'show' : '' }}">

            <form method="GET" action="{{ route($routePrefix.'.index') }}">

                <div class="row align-items-end">

                    <div class="col-md-8 mb-2 mb-md-0">

                        <label class="small text-muted mb-1">Buscar</label>

                        <div class="input-group input-group-sm">

                            <div class="input-group-prepend">

                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>

                            </div>

                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}"

                                placeholder="Nombre{{ $tieneDescripcion ? ' o descripción' : '' }}…">

                        </div>

                    </div>

                </div>

                <x-filtros-form-actions :limpiar-url="route($routePrefix.'.index', ['filtros_abiertos' => 1])" />

            </form>

        </div>



        <div class="card-body p-0 table-responsive">

            <table class="table table-modulo table-hover mb-0">

                <thead>

                    <tr>

                        <th style="width: 55px">#</th>

                        <th>Nombre</th>

                        @if($tieneDescripcion)

                        <th>Descripción</th>

                        @endif

                        <th style="width: 130px" class="text-center">Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($items as $item)

                    <tr>

                        <td class="text-muted font-weight-bold">#{{ $item->{$pk} }}</td>

                        <td><strong>{{ $item->nombre }}</strong></td>

                        @if($tieneDescripcion)

                        <td class="text-muted">{{ $item->descripcion ?: '—' }}</td>

                        @endif

                        <td class="text-center btn-actions">

                            <a href="{{ route($routePrefix.'.show', $item) }}" class="btn btn-sm btn-outline-info" title="Ver detalles">

                                <i class="fas fa-eye"></i>

                            </a>

                            <a href="{{ route($routePrefix.'.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Editar">

                                <i class="fas fa-edit"></i>

                            </a>

                            <form action="{{ route($routePrefix.'.destroy', $item) }}" method="POST" class="d-inline"

                                onsubmit="return confirm('¿Eliminar este {{ $singular }}?')">

                                @csrf

                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="{{ $tieneDescripcion ? 4 : 3 }}" class="text-center text-muted py-5">

                            <i class="fas {{ $icono }} fa-3x mb-3 d-block"></i>

                            No hay registros que coincidan con los filtros.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>



        @if($items->hasPages())

        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">

            <small class="text-muted mb-2 mb-md-0">

                Mostrando {{ $items->firstItem() }}–{{ $items->lastItem() }} de {{ $items->total() }}

            </small>

            {{ $items->links() }}

        </div>

        @endif

    </div>



</div>


