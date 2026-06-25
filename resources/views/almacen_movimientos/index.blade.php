@extends('layouts.app')

@section('title', 'Movimientos de almacén | AgroFusion')
@section('page_title', 'Movimientos de almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Movimientos</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-mov-almacen .mov-kpi-row {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.page-mov-almacen .mov-kpi {
    flex: 1 1 200px;
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .85rem 1rem;
    border-radius: 12px;
    border: 2px solid transparent;
    text-decoration: none !important;
    color: inherit !important;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    transition: transform .15s, box-shadow .15s, border-color .15s;
}
.page-mov-almacen .mov-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,.1);
}
.page-mov-almacen .mov-kpi.active {
    border-color: rgba(255,255,255,.85);
    box-shadow: 0 6px 20px rgba(0,0,0,.14);
}
.page-mov-almacen .mov-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    background: rgba(255,255,255,.22);
}
.page-mov-almacen .mov-kpi-body { min-width: 0; }
.page-mov-almacen .mov-kpi-value {
    font-size: 1.55rem;
    font-weight: 800;
    line-height: 1.1;
}
.page-mov-almacen .mov-kpi-label {
    font-size: .78rem;
    opacity: .92;
    margin-top: .1rem;
}
.page-mov-almacen .mov-kpi-hint {
    font-size: .7rem;
    opacity: .75;
    margin-top: .15rem;
}
.page-mov-almacen .mov-kpi-ingreso {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
}
.page-mov-almacen .mov-kpi-salida {
    background: linear-gradient(135deg, #e67e22, #f39c12);
    color: #fff;
}
.page-mov-almacen .mov-kpi-total {
    background: linear-gradient(135deg, #17a2b8, #20c997);
    color: #fff;
}
.page-mov-almacen .mov-toolbar-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    align-items: center;
}
.page-mov-almacen .mov-table-wrap {
    border-top: 1px solid #eef2f0;
}
.page-mov-almacen .mov-table .table-modulo tbody td {
    padding: .55rem .65rem;
    font-size: .86rem;
}
.page-mov-almacen .mov-tipo-pill {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .2rem .55rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 600;
    white-space: nowrap;
}
.page-mov-almacen .mov-tipo-pill.ingreso {
    background: #e8f5e9;
    color: #2e7d32;
}
.page-mov-almacen .mov-tipo-pill.salida {
    background: #fff3e0;
    color: #e65100;
}
.page-mov-almacen .mov-producto {
    font-weight: 600;
    color: #1e4620;
}
.page-mov-almacen .mov-cantidad {
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
}
.page-mov-almacen .mov-ref {
    font-size: .8rem;
    color: #64748b;
    font-family: ui-monospace, monospace;
}
.page-mov-almacen .mov-btn-ver {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid #dbeafe;
    background: #f0f9ff;
    color: #0284c7;
    transition: background .15s;
}
.page-mov-almacen .mov-btn-ver:hover {
    background: #e0f2fe;
    color: #0369a1;
}
.page-mov-almacen .mov-cosecha-tag {
    display: block;
    font-size: .72rem;
    color: #94a3b8;
    margin-top: .2rem;
}
.page-mov-almacen .mov-filter-active {
    border-radius: 10px;
    padding: .55rem .85rem;
    margin-bottom: 1rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    font-size: .88rem;
    color: #1e40af;
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-mov-almacen">

    @if($filtroNaturaleza)
    <div class="mov-filter-active d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>
            <i class="fas fa-filter mr-1"></i>
            Mostrando solo <strong>{{ $filtroNaturaleza === 'ingreso' ? 'ingresos' : 'salidas' }}</strong>
        </span>
        <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-times mr-1"></i> Quitar filtro
        </a>
    </div>
    @endif

    <div class="mov-kpi-row">
        <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index', ['naturaleza' => 'ingreso']) }}"
           class="mov-kpi mov-kpi-ingreso {{ $filtroNaturaleza === 'ingreso' ? 'active' : '' }}">
            <div class="mov-kpi-icon"><i class="fas fa-arrow-down"></i></div>
            <div class="mov-kpi-body">
                <div class="mov-kpi-value">{{ $totalIngresos }}</div>
                <div class="mov-kpi-label">Ingresos registrados</div>
                <div class="mov-kpi-hint">Clic para filtrar</div>
            </div>
        </a>
        <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index', ['naturaleza' => 'salida']) }}"
           class="mov-kpi mov-kpi-salida {{ $filtroNaturaleza === 'salida' ? 'active' : '' }}">
            <div class="mov-kpi-icon"><i class="fas fa-arrow-up"></i></div>
            <div class="mov-kpi-body">
                <div class="mov-kpi-value">{{ $totalSalidas }}</div>
                <div class="mov-kpi-label">Salidas registradas</div>
                <div class="mov-kpi-hint">Clic para filtrar</div>
            </div>
        </a>
        <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index') }}"
           class="mov-kpi mov-kpi-total {{ $filtroNaturaleza === '' ? 'active' : '' }}">
            <div class="mov-kpi-icon"><i class="fas fa-exchange-alt"></i></div>
            <div class="mov-kpi-body">
                <div class="mov-kpi-value">{{ $totalMovimientos }}</div>
                <div class="mov-kpi-label">Total de movimientos</div>
                <div class="mov-kpi-hint">Ver todos</div>
            </div>
        </a>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Movimientos de almacén"
            icono="fa-dolly"
            :registros="$movimientos->total()"
            filtros-target="#filtrosMovimientosPanel"
        >
            <x-slot:tools>
                <div class="mov-toolbar-actions">
                    @can('almacen.ingresos.create')
                    <a class="btn btn-success btn-sm" href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.create', ['naturaleza' => 'ingreso']) }}">
                        <i class="fas fa-plus mr-1"></i>
                        @if(($ambito ?? '') === 'agricola')
                            Ingreso manual
                        @else
                            Ingreso
                        @endif
                    </a>
                    @endcan
                    @can('almacen.salidas.create')
                    <a class="btn btn-warning btn-sm" href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.create', ['naturaleza' => 'salida']) }}">
                        <i class="fas fa-arrow-up mr-1"></i> Salida
                    </a>
                    @endcan
                    @can('almacen.reportes.view')
                    <a class="btn btn-outline-info btn-sm" href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.reportes') }}">
                        <i class="fas fa-chart-bar mr-1"></i> Reportes
                    </a>
                    @endcan
                </div>
            </x-slot:tools>
        </x-modulo-index-header>

        <div id="filtrosMovimientosPanel" class="filtros-panel collapse {{ $filtroNaturaleza ? 'show' : '' }}">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="movSearch" class="form-control"
                            placeholder="Producto, lote, responsable o referencia...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Almacén</label>
                    @include('partials.selector-catalogo', [
                        'id' => 'mov_filtro_almacen',
                        'name' => 'mov_almacenid',
                        'value' => '',
                        'labelSelected' => '',
                        'endpoint' => route('catalogo-selector.almacenes'),
                        'params' => ['ambito' => $ambito ?? 'agricola'],
                        'title' => 'Filtrar por almacén',
                        'searchPlaceholder' => 'Nombre o ubicación…',
                        'searchLabel' => 'Buscar almacén',
                        'allowEmpty' => true,
                        'emptyLabel' => 'Todos los almacenes',
                        'placeholderEmpty' => 'Todos los almacenes',
                        'inputGroup' => true,
                        'showLabel' => false,
                        'modalIcon' => 'fa-warehouse',
                        'rowIcon' => 'fa-warehouse',
                        'colNombre' => 'Almacén',
                        'colDetalle' => 'Ubicación',
                        'variant' => 'filtros',
                    ])
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Naturaleza</label>
                    <select id="movFiltroNaturaleza" class="form-control form-control-sm">
                        <option value="">Ingreso / Salida</option>
                        <option value="ingreso" {{ $filtroNaturaleza === 'ingreso' ? 'selected' : '' }}>Ingresos</option>
                        <option value="salida" {{ $filtroNaturaleza === 'salida' ? 'selected' : '' }}>Salidas</option>
                    </select>
                </div>
            </div>
            <x-filtros-client-actions />
        </div>

        <div class="table-responsive mov-table-wrap mov-table">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Almacén</th>
                        <th>Producto</th>
                        <th class="text-right">Cantidad</th>
                        <th>Responsable</th>
                        <th>Referencia</th>
                        <th class="text-center" style="width:52px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $linea)
                        <tr class="mov-row"
                            data-search="{{ $linea->search_text }}"
                            data-almacen="{{ strtolower($linea->almacen_nombre) }}"
                            data-almacen-id="{{ $linea->almacen_id ?? 0 }}"
                            data-tipo="{{ strtolower($linea->tipo_nombre) }}"
                            data-naturaleza="{{ strtolower($linea->naturaleza) }}">
                            <td class="text-nowrap">{{ $linea->fecha ? \Carbon\Carbon::parse($linea->fecha)->format('d/m/Y') : '—' }}</td>
                            <td>
                                <span class="mov-tipo-pill {{ $linea->naturaleza === 'ingreso' ? 'ingreso' : 'salida' }}">
                                    <i class="fas fa-arrow-{{ $linea->naturaleza === 'ingreso' ? 'down' : 'up' }}"></i>
                                    {{ $linea->tipo_nombre }}
                                </span>
                                @if($linea->tipo_linea === 'cosecha')
                                    <span class="mov-cosecha-tag"><i class="fas fa-seedling"></i> Desde cosecha</span>
                                @endif
                            </td>
                            <td>{{ $linea->almacen_nombre ?: '—' }}</td>
                            <td><span class="mov-producto">{{ $linea->producto }}</span></td>
                            <td class="text-right">
                                @php
                                    $esUnidadMov = ! str_contains(strtolower($linea->unidad ?? ''), 'kg');
                                @endphp
                                <span class="mov-cantidad">{{ number_format((float) $linea->cantidad, $esUnidadMov ? 0 : 2) }}</span>
                                <small class="text-muted">{{ $linea->unidad }}</small>
                            </td>
                            <td>{{ $linea->responsable ?: '—' }}</td>
                            <td><span class="mov-ref">{{ $linea->referencia ?: '—' }}</span></td>
                            <td class="text-center">
                                <a href="{{ $linea->url_ver }}" class="mov-btn-ver" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-lg mb-2 d-block opacity-50"></i>
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
    const fAlmacenWrap = document.getElementById('selector_wrap_mov_filtro_almacen');
    const fAlmacen = fAlmacenWrap?.querySelector('.selector-catalogo-value');
    const fNaturaleza = document.getElementById('movFiltroNaturaleza');
    const rows = Array.from(document.querySelectorAll('.mov-row'));

    function aplicarFiltroMovimientos() {
        const val = (q?.value || '').toLowerCase().trim();
        const almId = (fAlmacen?.value || '').trim();
        const nat = (fNaturaleza?.value || '').toLowerCase();

        rows.forEach((tr) => {
            const okSearch = !val || (tr.dataset.search || '').includes(val);
            const okAlm = !almId || (tr.dataset.almacenId || '') === almId;
            const okNat = !nat || (tr.dataset.naturaleza || '') === nat;
            tr.style.display = (okSearch && okAlm && okNat) ? '' : 'none';
        });
    }

    q?.addEventListener('keyup', aplicarFiltroMovimientos);
    fAlmacenWrap?.addEventListener('selector-catalogo:change', aplicarFiltroMovimientos);
    fNaturaleza?.addEventListener('change', aplicarFiltroMovimientos);
    document.getElementById('btnAplicarFiltros')?.addEventListener('click', aplicarFiltroMovimientos);

    document.getElementById('btnLimpiarFiltros')?.addEventListener('click', function () {
        if (q) q.value = '';
        if (window.CatalogoSelector) {
            CatalogoSelector.clear('mov_filtro_almacen');
        } else if (fAlmacen) {
            fAlmacen.value = '';
        }
        if (fNaturaleza) fNaturaleza.value = '';
        aplicarFiltroMovimientos();
    });
});
</script>
@endpush
