@php
    $filtrosAbiertos = request()->hasAny(['fecha_desde','fecha_hasta','tipo_id','lote_id']);
@endphp
<div class="card card-outline card-modulo-main rep-filtros-purple elevation-1 mb-4">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-filter text-purple mr-1"></i> Filtros del reporte</h3>
        <div class="card-tools">
            @include('partials.btn-filtros-toggle', ['target' => '#filtrosRepActividades'])
        </div>
    </div>
    <div id="filtrosRepActividades" class="filtros-panel collapse {{ $filtrosAbiertos ? 'show' : '' }}">
        <form method="GET" action="{{ route('reportes.actividades') }}">
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
                    <label class="small text-muted mb-1">Tipo</label>
                    <select name="tipo_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->tipoactividadid }}" @selected($tipoId == $tipo->tipoactividadid)>{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Lote</label>
                    <select name="lote_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($lotes as $lote)
                            <option value="{{ $lote->loteid }}" @selected($loteId == $lote->loteid)>{{ $lote->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex" style="gap: 8px;">
                    <button type="submit" class="btn btn-sm btn-purple"><i class="fas fa-search mr-1"></i> Filtrar</button>
                    <a href="{{ route('reportes.actividades') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</div>
