@extends('layouts.app')

@section('title', 'Recursos productivos | AgroNexus')
@section('page_title', 'Vista consolidada de recursos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Recursos productivos</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-recursos .cultivo-item {
    border-left: 3px solid #28a745;
}
.page-recursos .cultivos-panel {
    max-height: 520px;
    overflow-y: auto;
}
.page-recursos .insumos-panel {
    max-height: 520px;
    overflow-y: auto;
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-recursos">

    <div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['cultivos'] }}</h3>
                    <p>Cultivos registrados</p>
                </div>
                <div class="icon"><i class="fas fa-seedling"></i></div>
                <span class="small-box-footer">En la operación</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['insumos'] }}</h3>
                    <p>Insumos registrados</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <span class="small-box-footer">Catálogo disponible</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>{{ $stats['stock_bajo'] }}</h3>
                    <p>En nivel crítico</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="#" class="small-box-footer" id="linkStockBajo">
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

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group text-success mr-1"></i>
                Recursos productivos
                <span class="badge badge-light border text-muted badge-registros ml-2">{{ $insumos->count() }} insumos</span>
            </h3>
            <div class="card-tools d-flex align-items-center flex-wrap" style="gap: 6px;">
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosRecursosPanel" title="Filtros">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>

        <div id="filtrosRecursosPanel" class="filtros-panel collapse">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar insumos</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="rpSearch" class="form-control"
                            placeholder="Nombre de insumo o proveedor...">
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-2">
                    <label class="small text-muted mb-1">Tipo</label>
                    <select id="rpTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFiltro as $tipo)
                            <option value="{{ strtolower($tipo) }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="small text-muted mb-1">Nivel</label>
                    <select id="rpStock" class="form-control form-control-sm">
                        <option value="">Todos los niveles</option>
                        <option value="bajo">Stock bajo</option>
                        <option value="normal">Stock normal</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-12 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros" title="Limpiar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body pb-0">
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <h6 class="text-muted text-uppercase small mb-2">
                        <i class="fas fa-seedling text-success mr-1"></i> Cultivos
                        <span class="badge badge-success ml-1">{{ $stats['cultivos'] }}</span>
                    </h6>
                    <div class="cultivos-panel border rounded">
                        <ul class="list-group list-group-flush mb-0">
                            @forelse($cultivos as $cultivo)
                                <li class="list-group-item cultivo-item d-flex justify-content-between align-items-center py-2">
                                    <span><i class="fas fa-leaf text-success mr-2"></i>{{ $cultivo->nombre }}</span>
                                    <span class="badge badge-success">Cultivo</span>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4">
                                    <i class="fas fa-seedling fa-2x mb-2 text-light d-block"></i>
                                    Sin cultivos registrados.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="col-lg-8 mb-3">
                    <h6 class="text-muted text-uppercase small mb-2">
                        <i class="fas fa-cubes text-info mr-1"></i> Insumos para abastecimiento
                        <span class="badge badge-light border text-muted ml-1" id="rpContadorVisible">{{ $insumos->count() }} ítems</span>
                    </h6>
                    <div class="insumos-panel table-responsive border rounded">
                        <table class="table table-modulo table-hover mb-0">
                            <thead>
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
                                        <td><strong class="text-success">{{ $insumo->nombre }}</strong></td>
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
                                            <i class="fas fa-boxes fa-2x mb-2 text-light d-block"></i>
                                            Sin insumos registrados.
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
        $('#filtrosRecursosPanel').collapse('show');
    });
});
</script>
@endpush
