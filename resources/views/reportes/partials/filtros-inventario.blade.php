@php
    $filtrosAbiertos = request()->hasAny(['buscar','tipo_id','estado']);
@endphp
<div class="card card-outline card-primary card-modulo-main elevation-1 mb-4">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-filter text-primary mr-1"></i> Filtros del reporte</h3>
        <div class="card-tools">
            @include('partials.btn-filtros-toggle', ['target' => '#filtrosRepInventario'])
        </div>
    </div>
    <div id="filtrosRepInventario" class="filtros-panel collapse {{ $filtrosAbiertos ? 'show' : '' }}">
        <form method="GET" action="{{ route('reportes.inventario') }}">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Buscar insumo</label>
                    <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Nombre del insumo…">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Tipo</label>
                    <select name="tipo_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($tiposInsumo as $tipo)
                            <option value="{{ $tipo->tipoinsumoid }}" @selected(request('tipo_id') == $tipo->tipoinsumoid)>{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Estado de stock</label>
                    <select name="estado" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="critico" @selected(request('estado') === 'critico')>Crítico</option>
                        <option value="bajo" @selected(request('estado') === 'bajo')>Bajo</option>
                        <option value="ok" @selected(request('estado') === 'ok')>Normal</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex" style="gap: 8px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i> Filtrar</button>
                    <a href="{{ route('reportes.inventario') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</div>
