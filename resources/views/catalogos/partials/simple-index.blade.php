@php
    $tieneDescripcion = $tieneDescripcion ?? false;
    $kpiClass = $kpiClass ?? 'small-box-green';
@endphp

<div class="modulo-cat">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

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
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas {{ $icono }} text-success mr-1"></i>
                {{ $titulo }}
                <span class="badge badge-light border text-muted badge-registros ml-2">{{ $items->total() }} registros</span>
            </h3>
            <div class="card-tools d-flex align-items-center flex-wrap" style="gap: 6px;">
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosCatPanel" title="Filtros">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route($routePrefix.'.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>

        <div id="filtrosCatPanel" class="filtros-panel collapse {{ request()->filled('buscar') ? 'show' : '' }}">
            <form method="GET" action="{{ route($routePrefix.'.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}"
                            placeholder="Nombre{{ $tieneDescripcion ? ' o descripción' : '' }}…">
                    </div>
                    <div class="col-md-4 d-flex" style="gap: 8px;">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
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

    <a href="{{ route('catalogos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver a Catálogos
    </a>
</div>
