@extends('layouts.app')

@section('title', 'Movimientos de almacén | AgroNexus')
@section('page_title', 'Movimientos de almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Movimientos</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-mov-almacen .mov-filter-card {
    display: block;
    text-decoration: none !important;
    color: inherit !important;
}
.page-mov-almacen .mov-filter-card.active .small-box {
    outline: 3px solid rgba(255, 255, 255, 0.9);
    outline-offset: -3px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-mov-almacen">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
    @endif

    @if($filtroNaturaleza)
    <div class="alert alert-info alert-dismissible fade show shadow-sm py-2">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-filter mr-1"></i>
        Filtro activo: <strong>{{ $filtroNaturaleza === 'ingreso' ? 'Ingresos' : 'Salidas' }}</strong>
        <a href="{{ route('almacen-movimientos.index') }}" class="alert-link ml-2">Ver todos</a>
    </div>
    @endif

    <div class="row mb-2">
        <div class="col-lg-4 col-md-4 col-12">
            <a href="{{ route('almacen-movimientos.index', ['naturaleza' => 'ingreso']) }}"
               class="mov-filter-card {{ $filtroNaturaleza === 'ingreso' ? 'active' : '' }}">
                <div class="small-box small-box-green mb-0">
                    <div class="inner">
                        <h3>{{ $totalIngresos }}</h3>
                        <p>Ingresos registrados</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-down"></i></div>
                    <span class="small-box-footer">Filtrar ingresos <i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-4 col-12">
            <a href="{{ route('almacen-movimientos.index', ['naturaleza' => 'salida']) }}"
               class="mov-filter-card {{ $filtroNaturaleza === 'salida' ? 'active' : '' }}">
                <div class="small-box small-box-yellow mb-0">
                    <div class="inner">
                        <h3>{{ $totalSalidas }}</h3>
                        <p>Salidas registradas</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-up"></i></div>
                    <span class="small-box-footer">Filtrar salidas <i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-4 col-12">
            <a href="{{ route('almacen-movimientos.index') }}"
               class="mov-filter-card {{ $filtroNaturaleza === '' ? 'active' : '' }}">
                <div class="small-box small-box-blue mb-0">
                    <div class="inner">
                        <h3>{{ $totalMovimientos }}</h3>
                        <p>Total de movimientos</p>
                    </div>
                    <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                    <span class="small-box-footer">Ver todos <i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </a>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-dolly text-success mr-1"></i>
                Movimientos de almacén
                <span class="badge badge-light border text-muted badge-registros ml-2">{{ $movimientos->total() }} registros</span>
            </h3>
            <div class="card-tools d-flex align-items-center flex-wrap" style="gap: 6px;">
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosMovimientosPanel" title="Filtros">
                    <i class="fas fa-filter"></i>
                </button>
                @can('almacen.ingresos.create')
                <a class="btn btn-success btn-sm" href="{{ route('almacen-movimientos.create', ['naturaleza' => 'ingreso']) }}">
                    <i class="fas fa-arrow-down mr-1"></i> Ingreso
                </a>
                @endcan
                @can('almacen.salidas.create')
                <a class="btn btn-warning btn-sm" href="{{ route('almacen-movimientos.create', ['naturaleza' => 'salida']) }}">
                    <i class="fas fa-arrow-up mr-1"></i> Salida
                </a>
                @endcan
                @can('almacen.reportes.view')
                <a class="btn btn-info btn-sm" href="{{ route('almacen-movimientos.reportes') }}">
                    <i class="fas fa-chart-bar mr-1"></i> Reportes
                </a>
                @endcan
            </div>
        </div>

        <div id="filtrosMovimientosPanel" class="filtros-panel collapse {{ $filtroNaturaleza ? 'show' : '' }}">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="movSearch" class="form-control"
                            placeholder="Insumo, responsable o referencia...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Almacén</label>
                    <select id="movFiltroAlmacen" class="form-control form-control-sm">
                        <option value="">Todos los almacenes</option>
                        @foreach($almacenesFiltro as $nombreAlmacen)
                            <option value="{{ strtolower($nombreAlmacen) }}">{{ $nombreAlmacen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Tipo</label>
                    <select id="movFiltroTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFiltro as $nombreTipo)
                            <option value="{{ strtolower($nombreTipo) }}">{{ $nombreTipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Naturaleza</label>
                    <select id="movFiltroNaturaleza" class="form-control form-control-sm">
                        <option value="">Ingreso / Salida</option>
                        <option value="ingreso" {{ $filtroNaturaleza === 'ingreso' ? 'selected' : '' }}>Ingresos</option>
                        <option value="salida" {{ $filtroNaturaleza === 'salida' ? 'selected' : '' }}>Salidas</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Almacén</th>
                        <th>Insumo</th>
                        <th class="text-right">Cantidad</th>
                        <th>Responsable</th>
                        <th>Referencia</th>
                        <th class="text-center" style="width: 70px;">Ver</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                        @php
                            $responsable = trim(($mov->usuario?->nombre ?? '') . ' ' . ($mov->usuario?->apellido ?? ''));
                            $searchText = strtolower(trim(
                                ($mov->insumo?->nombre ?? '') . ' ' . $responsable . ' ' . ($mov->referencia ?? '')
                            ));
                        @endphp
                        <tr class="mov-row"
                            data-search="{{ $searchText }}"
                            data-almacen="{{ strtolower($mov->almacen?->nombre ?? '') }}"
                            data-tipo="{{ strtolower($mov->tipo?->nombre ?? '') }}"
                            data-naturaleza="{{ strtolower($mov->tipo?->naturaleza ?? '') }}">
                            <td>{{ optional($mov->fecha)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $mov->tipo?->naturaleza === 'ingreso' ? 'success' : 'warning' }}">
                                    <i class="fas fa-arrow-{{ $mov->tipo?->naturaleza === 'ingreso' ? 'down' : 'up' }} mr-1"></i>
                                    {{ $mov->tipo?->nombre ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $mov->almacen?->nombre ?? '—' }}</td>
                            <td><strong class="text-success">{{ $mov->insumo?->nombre ?? '—' }}</strong></td>
                            <td class="text-right">
                                {{ number_format((float) $mov->cantidad, 3) }}
                                <small class="text-muted">{{ $mov->insumo?->unidadMedida?->abreviatura }}</small>
                            </td>
                            <td>{{ $responsable ?: '—' }}</td>
                            <td>{{ $mov->referencia ?: '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('almacen-movimientos.show', ['almacenMovimiento' => $mov->almacen_movimientoid, 'naturaleza' => $filtroNaturaleza]) }}"
                                   class="btn btn-default btn-sm" title="Ver detalle">
                                    <i class="fas fa-eye text-info"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-exchange-alt fa-2x mb-2 text-light d-block"></i>
                                No hay movimientos para este filtro.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center py-2">
            {{ $movimientos->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const q = document.getElementById('movSearch');
    const fAlmacen = document.getElementById('movFiltroAlmacen');
    const fTipo = document.getElementById('movFiltroTipo');
    const fNaturaleza = document.getElementById('movFiltroNaturaleza');
    const rows = Array.from(document.querySelectorAll('.mov-row'));

    function aplicarFiltroMovimientos() {
        const val = (q?.value || '').toLowerCase().trim();
        const alm = (fAlmacen?.value || '').toLowerCase();
        const tipo = (fTipo?.value || '').toLowerCase();
        const nat = (fNaturaleza?.value || '').toLowerCase();

        rows.forEach((tr) => {
            const okSearch = !val || (tr.dataset.search || '').includes(val);
            const okAlm = !alm || (tr.dataset.almacen || '') === alm;
            const okTipo = !tipo || (tr.dataset.tipo || '') === tipo;
            const okNat = !nat || (tr.dataset.naturaleza || '') === nat;
            tr.style.display = (okSearch && okAlm && okTipo && okNat) ? '' : 'none';
        });
    }

    q?.addEventListener('keyup', aplicarFiltroMovimientos);
    fAlmacen?.addEventListener('change', aplicarFiltroMovimientos);
    fTipo?.addEventListener('change', aplicarFiltroMovimientos);
    fNaturaleza?.addEventListener('change', aplicarFiltroMovimientos);

    document.getElementById('btnLimpiarFiltros')?.addEventListener('click', function () {
        if (q) q.value = '';
        if (fAlmacen) fAlmacen.value = '';
        if (fTipo) fTipo.value = '';
        if (fNaturaleza) fNaturaleza.value = '';
        aplicarFiltroMovimientos();
    });
});
</script>
@endpush
