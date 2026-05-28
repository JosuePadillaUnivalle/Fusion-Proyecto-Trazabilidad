@extends('layouts.app')

@section('title', 'Recursos productivos | AgroNexus')
@section('page_title', 'Vista consolidada de recursos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('insumos.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active">Recursos productivos</li>
@endsection

@php
    $pctCritico = $stats['insumos'] > 0
        ? round(($stats['stock_bajo'] / $stats['insumos']) * 100)
        : 0;
@endphp

@push('styles')
<style>
.page-recursos .small-box {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.page-recursos .small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
.page-recursos .small-box .icon { font-size: 70px; }
.page-recursos .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
    color: #fff;
}
.page-recursos .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    color: #fff;
}
.page-recursos .small-box-red {
    background: linear-gradient(135deg, #dc3545, #e74a3b) !important;
    color: #fff;
}
.page-recursos .small-box-yellow {
    background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
    color: #1a252f;
}
.page-recursos .card {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.page-recursos .cultivo-item {
    border-left: 3px solid #28a745;
}
</style>
@endpush

@section('content')
<div class="page-recursos">

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-layer-group"></i> Vista consolidada</h5>
                Cultivos de la operación e insumos disponibles en un solo panel. Detecta niveles críticos y planifica compras o reabastecimiento.
                <div class="mt-2">
                    <a href="{{ route('insumos.index') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-boxes mr-1"></i> Insumos
                    </a>
                    <a href="{{ route('actores-abastecimiento.index') }}" class="btn btn-sm btn-info ml-1">
                        <i class="fas fa-handshake mr-1"></i> Actores
                    </a>
                    @can('reportes.view')
                    <a href="{{ route('reportes.inventario') }}" class="btn btn-sm btn-success ml-1">
                        <i class="fas fa-chart-pie mr-1"></i> Reporte inventario
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['cultivos'] }}</h3>
                    <p>Cultivos registrados</p>
                </div>
                <div class="icon"><i class="fas fa-seedling"></i></div>
                <a href="#panel-cultivos" class="small-box-footer">
                    Ver cultivos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['insumos'] }}</h3>
                    <p>Insumos registrados</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <a href="{{ route('insumos.index') }}" class="small-box-footer">
                    Gestionar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>{{ $stats['stock_bajo'] }}</h3>
                    <p>En nivel crítico</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="#filtros-recursos" class="small-box-footer" id="linkStockBajo">
                    Filtrar críticos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['valor_total'], 0) }}</h3>
                    <p>Valor en stock</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
                <span class="small-box-footer">Stock × precio unitario</span>
            </div>
        </div>
    </div>

    @if ($stats['insumos'] > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-secondary mb-0">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['insumos'] }}</h5>
                                <span class="description-text text-muted">INSUMOS</span>
                            </div>
                        </div>
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <span class="description-percentage text-danger">
                                    <i class="fas fa-caret-down"></i> {{ $pctCritico }}%
                                </span>
                                <h5 class="description-header">{{ $stats['stock_bajo'] }}</h5>
                                <span class="description-text text-muted">CRÍTICOS</span>
                            </div>
                        </div>
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['stock_normal'] }}</h5>
                                <span class="description-text text-muted">NORMALES</span>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['tipos'] }}</h5>
                                <span class="description-text text-muted">TIPOS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card card-primary card-outline elevation-2 mb-3" id="filtros-recursos">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Buscar insumos</h3>
        </div>
        <div class="card-body pb-2">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="rpSearch" class="form-control"
                            placeholder="Nombre de insumo o proveedor...">
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-2">
                    <select id="rpTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFiltro as $tipo)
                            <option value="{{ strtolower($tipo) }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <select id="rpStock" class="form-control form-control-sm">
                        <option value="">Todos los niveles</option>
                        <option value="bajo">Stock bajo</option>
                        <option value="normal">Stock normal</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-12 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros">
                        <i class="fas fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4" id="panel-cultivos">
            <div class="card card-success card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-seedling mr-1 text-success"></i> Cultivos</h3>
                    <span class="badge badge-success ml-2">{{ $stats['cultivos'] }}</span>
                    <div class="card-tools">
                        <a href="{{ route('cultivos.index') }}" class="btn btn-tool" title="Catálogo de cultivos">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 520px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        @forelse($cultivos as $cultivo)
                            <li class="list-group-item cultivo-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-leaf text-success mr-2"></i>{{ $cultivo->nombre }}</span>
                                <span class="badge badge-success">Cultivo</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-4">
                                <i class="fas fa-seedling fa-2x mb-2 text-light d-block"></i>
                                Sin cultivos registrados.
                                <a href="{{ route('cultivos.index') }}" class="d-block mt-2 small">Ir a Catálogos &gt; Cultivos</a>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-info card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cubes mr-1 text-info"></i> Insumos para abastecimiento</h3>
                    <span class="badge badge-info ml-2" id="rpContadorVisible">{{ $insumos->count() }} ítems</span>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>Insumo</th>
                                <th>Tipo</th>
                                <th>Nivel</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                                <th>Actor / proveedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($insumos as $insumo)
                                @php
                                    $esBajo = (float) $insumo->stock <= (float) $insumo->stockminimo;
                                    $actor = $insumo->actorAbastecimiento->nombre ?? $insumo->proveedor ?? '—';
                                @endphp
                                <tr class="rp-row"
                                    data-search="{{ strtolower($insumo->nombre.' '.$actor) }}"
                                    data-tipo="{{ strtolower($insumo->tipo->nombre ?? '') }}"
                                    data-stock="{{ $esBajo ? 'bajo' : 'normal' }}">
                                    <td>
                                        <strong class="text-success">{{ $insumo->nombre }}</strong>
                                    </td>
                                    <td>{{ $insumo->tipo->nombre ?? '—' }}</td>
                                    <td>
                                        @if($esBajo)
                                            <span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Crítico</span>
                                        @else
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Normal</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ number_format((float) $insumo->stock, 2) }}
                                        <small class="text-muted">{{ $insumo->unidadMedida->abreviatura ?? '' }}</small>
                                    </td>
                                    <td>{{ number_format((float) $insumo->stockminimo, 2) }}</td>
                                    <td>
                                        <i class="fas fa-handshake text-muted mr-1"></i>{{ $actor }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-boxes fa-3x mb-3 text-light d-block"></i>
                                        Sin insumos registrados.
                                        <a href="{{ route('insumos.index') }}" class="d-block mt-2">Ir a Inventario &gt; Insumos</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const q = document.getElementById('rpSearch');
    const t = document.getElementById('rpTipo');
    const s = document.getElementById('rpStock');
    const rows = Array.from(document.querySelectorAll('.rp-row'));
    const contador = document.getElementById('rpContadorVisible');

    function filtrar() {
        const vq = (q?.value || '').toLowerCase().trim();
        const vt = (t?.value || '').toLowerCase();
        const vs = (s?.value || '').toLowerCase();
        let visibles = 0;
        rows.forEach((tr) => {
            const okQ = !vq || (tr.dataset.search || '').includes(vq);
            const okT = !vt || (tr.dataset.tipo || '') === vt;
            const okS = !vs || (tr.dataset.stock || '') === vs;
            const show = okQ && okT && okS;
            tr.style.display = show ? '' : 'none';
            if (show) visibles++;
        });
        if (contador) {
            contador.textContent = visibles + ' ítems';
        }
    }

    q?.addEventListener('keyup', filtrar);
    t?.addEventListener('change', filtrar);
    s?.addEventListener('change', filtrar);

    document.getElementById('btnLimpiarFiltros')?.addEventListener('click', function () {
        if (q) q.value = '';
        if (t) t.value = '';
        if (s) s.value = '';
        filtrar();
    });

    document.getElementById('linkStockBajo')?.addEventListener('click', function (ev) {
        ev.preventDefault();
        if (s) s.value = 'bajo';
        filtrar();
        document.getElementById('filtros-recursos')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
@endpush
