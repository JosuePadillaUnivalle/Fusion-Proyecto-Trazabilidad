@php
    $tieneDescripcion = $tieneDescripcion ?? false;
@endphp

<div class="modulo-cat">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="mb-3 d-flex flex-wrap" style="gap: 8px;">
        <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Listado
        </a>
        <a href="{{ route($routePrefix.'.edit', $item) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
    </div>

    <div class="card card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas {{ $icono }} text-success mr-1"></i>
                Detalle: {{ $item->nombre }}
            </h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3 text-muted">ID</dt>
                <dd class="col-sm-9">#{{ $item->{$pk} }}</dd>
                <dt class="col-sm-3 text-muted">Nombre</dt>
                <dd class="col-sm-9"><strong>{{ $item->nombre }}</strong></dd>
                @if($tieneDescripcion)
                <dt class="col-sm-3 text-muted">Descripción</dt>
                <dd class="col-sm-9">{{ $item->descripcion ?: '—' }}</dd>
                @endif
            </dl>
        </div>
        <div class="card-footer d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ route($routePrefix.'.edit', $item) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <form action="{{ route($routePrefix.'.destroy', $item) }}" method="POST" class="d-inline"
                onsubmit="return confirm('¿Eliminar este {{ $singular }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
