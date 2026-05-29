@php
    $filtrosAbiertos = request()->filled('dias') && (int) request('dias') !== 7;
@endphp
<div class="card card-outline card-info card-modulo-main elevation-1 mb-4">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-filter text-info mr-1"></i> Filtros del reporte</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosRepClima"><i class="fas fa-filter"></i></button>
        </div>
    </div>
    <div id="filtrosRepClima" class="filtros-panel collapse {{ $filtrosAbiertos ? 'show' : '' }}">
        <form method="GET" action="{{ route('reportes.climatico') }}">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Período de historial</label>
                    <select name="dias" class="form-control form-control-sm">
                        <option value="7" @selected($dias == 7)>Últimos 7 días</option>
                        <option value="15" @selected($dias == 15)>Últimos 15 días</option>
                        <option value="30" @selected($dias == 30)>Últimos 30 días</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex" style="gap: 8px;">
                    <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-search mr-1"></i> Filtrar</button>
                    <a href="{{ route('reportes.climatico') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</div>
