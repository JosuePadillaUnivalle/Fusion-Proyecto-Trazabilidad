@extends('layouts.app')

@section('title', 'Movimientos de almacén | AgroNexus')
@section('page_title', 'Movimientos de almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active">Movimientos</li>
@endsection

@php
    $pctIngresos = $totalMovimientos > 0
        ? round(($totalIngresos / $totalMovimientos) * 100)
        : 0;
@endphp

@push('styles')
<style>
.page-mov-almacen .small-box {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.page-mov-almacen .mov-filter-card {
    display: block;
    text-decoration: none !important;
    color: inherit !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.page-mov-almacen .mov-filter-card:hover {
    transform: translateY(-3px);
}
.page-mov-almacen .mov-filter-card:hover .small-box {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
}
.page-mov-almacen .mov-filter-card.active .small-box {
    outline: 3px solid rgba(255, 255, 255, 0.9);
    outline-offset: -3px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
}
.page-mov-almacen .small-box .icon { font-size: 70px; }
.page-mov-almacen .card { border-radius: 10px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06); }
</style>
@endpush

@section('content')
<div class="page-mov-almacen">

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-dolly"></i> Ingresos y salidas</h5>
                Controla entradas y salidas de insumos por almacén. Pulse una tarjeta para filtrar por tipo o use los filtros de la tabla.
                @if($filtroNaturaleza)
                <div class="mt-2">
                    <span class="badge badge-light">
                        Filtro activo: <strong>{{ $filtroNaturaleza === 'ingreso' ? 'Ingresos' : 'Salidas' }}</strong>
                    </span>
                    <a href="{{ route('almacen-movimientos.index') }}" class="btn btn-sm btn-outline-light ml-2">Ver todos</a>
                </div>
                @endif
                <div class="mt-2">
                    <a href="{{ route('almacenes.index') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-warehouse mr-1"></i> Almacenes
                    </a>
                    <a href="{{ route('insumos.index') }}" class="btn btn-sm btn-info ml-1">
                        <i class="fas fa-boxes mr-1"></i> Insumos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-4 col-md-4 col-12 mb-2">
            <a href="{{ route('almacen-movimientos.index', ['naturaleza' => 'ingreso']) }}"
               class="mov-filter-card {{ $filtroNaturaleza === 'ingreso' ? 'active' : '' }}">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $totalIngresos }}</h3>
                        <p>Ingresos registrados</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-down"></i></div>
                    <span class="small-box-footer">Filtrar ingresos <i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-4 col-12 mb-2">
            <a href="{{ route('almacen-movimientos.index', ['naturaleza' => 'salida']) }}"
               class="mov-filter-card {{ $filtroNaturaleza === 'salida' ? 'active' : '' }}">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalSalidas }}</h3>
                        <p>Salidas registradas</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-up"></i></div>
                    <span class="small-box-footer">Filtrar salidas <i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-4 col-12 mb-2">
            <a href="{{ route('almacen-movimientos.index') }}"
               class="mov-filter-card {{ $filtroNaturaleza === '' ? 'active' : '' }}">
                <div class="small-box bg-info">
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

    @if ($totalMovimientos > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-secondary mb-0">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-sm-4 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $totalMovimientos }}</h5>
                                <span class="description-text text-muted">MOVIMIENTOS</span>
                            </div>
                        </div>
                        <div class="col-sm-4 border-right">
                            <div class="description-block mb-0">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-arrow-down"></i> {{ $pctIngresos }}%
                                </span>
                                <h5 class="description-header">{{ $totalIngresos }}</h5>
                                <span class="description-text text-muted">INGRESOS</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $totalSalidas }}</h5>
                                <span class="description-text text-muted">SALIDAS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card card-primary card-outline elevation-2" id="filtros-movimientos">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-0">
                <i class="fas fa-list mr-1"></i> Registro de movimientos
            </h3>
            <div class="d-flex flex-wrap mt-2 mt-md-0" style="gap: 6px;">
                @can('almacen.ingresos.create')
                <a class="btn btn-success btn-sm" href="{{ route('almacen-movimientos.create', ['naturaleza' => 'ingreso']) }}">
                    <i class="fas fa-arrow-down mr-1"></i> Nuevo ingreso
                </a>
                @endcan
                @can('almacen.salidas.create')
                <a class="btn btn-warning btn-sm" href="{{ route('almacen-movimientos.create', ['naturaleza' => 'salida']) }}">
                    <i class="fas fa-arrow-up mr-1"></i> Nueva salida
                </a>
                @endcan
                @can('almacen.reportes.view')
                <a class="btn btn-info btn-sm" href="{{ route('almacen-movimientos.reportes') }}">
                    <i class="fas fa-chart-bar mr-1"></i> Reportes
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body border-bottom pb-2">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="movSearch" class="form-control"
                            placeholder="Insumo, responsable o referencia...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <select id="movFiltroAlmacen" class="form-control form-control-sm">
                        <option value="">Todos los almacenes</option>
                        @foreach($almacenesFiltro as $nombreAlmacen)
                            <option value="{{ strtolower($nombreAlmacen) }}">{{ $nombreAlmacen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <select id="movFiltroTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFiltro as $nombreTipo)
                            <option value="{{ strtolower($nombreTipo) }}">{{ $nombreTipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <select id="movFiltroNaturaleza" class="form-control form-control-sm">
                        <option value="">Ingreso / Salida</option>
                        <option value="ingreso" {{ $filtroNaturaleza === 'ingreso' ? 'selected' : '' }}>Ingresos</option>
                        <option value="salida" {{ $filtroNaturaleza === 'salida' ? 'selected' : '' }}>Salidas</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros">
                        <i class="fas fa-eraser mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Almacén</th>
                        <th>Insumo</th>
                        <th class="text-right">Cantidad</th>
                        <th>Responsable</th>
                        <th>Referencia</th>
                        <th class="text-center" style="width: 80px">Ver</th>
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
                                   class="btn btn-xs btn-info" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-exchange-alt fa-3x mb-3 text-light d-block"></i>
                                No hay movimientos para este filtro.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movimientos->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $movimientos->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: '¡Hecho!',
        text: @json(session('success')),
        confirmButtonColor: '#2c5530',
        timer: 3000,
        showConfirmButton: false
    });
    @endif
});
</script>
@endpush
