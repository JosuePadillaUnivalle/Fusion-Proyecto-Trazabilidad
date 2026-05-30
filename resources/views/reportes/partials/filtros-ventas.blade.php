@php
    $filtrosAbiertos = request()->hasAny(['fecha_desde','fecha_hasta','cultivo_id','usuario_id']);
@endphp
<div class="card card-outline card-success card-modulo-main elevation-1 mb-4">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-filter text-success mr-1"></i> Filtros del reporte</h3>
        <div class="card-tools">
            @include('partials.btn-filtros-toggle', ['target' => '#filtrosRepVentas'])
        </div>
    </div>
    <div id="filtrosRepVentas" class="filtros-panel collapse {{ $filtrosAbiertos ? 'show' : '' }}">
        <form method="GET" action="{{ route('reportes.ventas') }}">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ $fechaDesde }}">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ $fechaHasta }}">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Cultivo</label>
                    <select name="cultivo_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($cultivos as $cultivo)
                            <option value="{{ $cultivo->cultivoid }}" @selected($cultivoId == $cultivo->cultivoid)>{{ $cultivo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Agricultor</label>
                    <select name="usuario_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->usuarioid }}" @selected($usuarioId == $usuario->usuarioid)>{{ $usuario->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex" style="gap: 8px;">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-search mr-1"></i> Filtrar</button>
                    <a href="{{ route('reportes.ventas') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</div>
