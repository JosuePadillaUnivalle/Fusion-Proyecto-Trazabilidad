@extends('layouts.app')

@section('title', 'Reportes de almacén')
@section('page_title', 'Reportes de almacén')

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-rep-almacen .rep-hero {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
.page-rep-almacen .rep-hero__title {
    font-weight: 800;
    color: #0f172a;
    font-size: .95rem;
    margin-bottom: .35rem;
}
.page-rep-almacen .rep-hero__text {
    font-size: .86rem;
    color: #64748b;
    margin: 0;
}
.page-rep-almacen .rep-hero__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: .65rem;
    margin-top: .85rem;
}
.page-rep-almacen .rep-hero__item {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .65rem .75rem;
    font-size: .78rem;
    color: #475569;
}
.page-rep-almacen .rep-hero__item strong { color: #0f172a; display: block; margin-bottom: .15rem; }
.page-rep-almacen .rep-kpi-link {
    display: block;
    text-decoration: none !important;
    color: inherit !important;
    transition: transform .15s ease, box-shadow .15s ease;
}
.page-rep-almacen .rep-kpi-link:hover { transform: translateY(-2px); }
.page-rep-almacen .rep-kpi-row { margin-left: -8px; margin-right: -8px; }
.page-rep-almacen .rep-kpi-col { padding-left: 8px; padding-right: 8px; margin-bottom: 1rem; }
.page-rep-almacen .rep-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    margin-bottom: 1.25rem;
    background: #fff;
}
.page-rep-almacen .rep-card .card-header {
    background: #fafbfc;
    border-bottom: 1px solid #eef2f6;
    border-radius: 12px 12px 0 0;
    padding: .85rem 1.15rem;
}
.page-rep-almacen .rep-section-title {
    font-size: .98rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .45rem;
}
.page-rep-almacen .rep-section-title i { color: #059669; }
.page-rep-almacen .rep-section-sub {
    font-size: .78rem;
    color: #64748b;
    margin: .15rem 0 0;
}
.page-rep-almacen .small-box { border-radius: 12px; margin-bottom: 0; }
.page-rep-almacen .small-box .inner h3 { font-size: 2rem; }
.page-rep-almacen .small-box .inner p { font-size: .9rem; opacity: .95; }
.page-rep-almacen .small-box .inner small { display: block; margin-top: .25rem; opacity: .85; font-size: .75rem; }
.page-rep-almacen .quick-period .btn { margin: 0 .35rem .35rem 0; border-radius: 20px; }
.page-rep-almacen .rep-panel { padding: .65rem 1rem 1rem; }
.page-rep-almacen .rep-prod-item,
.page-rep-almacen .rep-stock-item,
.page-rep-almacen .rep-rec-item { padding: .75rem .15rem; border-bottom: 1px solid #eef2f0; }
.page-rep-almacen .rep-prod-item:last-child,
.page-rep-almacen .rep-stock-item:last-child,
.page-rep-almacen .rep-rec-item:last-child { border-bottom: 0; }
.page-rep-almacen .rep-item-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: .75rem;
    margin-bottom: .35rem;
}
.page-rep-almacen .rep-item-name { font-weight: 700; color: #0f172a; font-size: .9rem; }
.page-rep-almacen .rep-item-value { font-weight: 700; color: #047857; font-size: .88rem; white-space: nowrap; }
.page-rep-almacen .rep-stat-row { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .45rem; }
.page-rep-almacen .rep-stat-pill {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .18rem .55rem;
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.page-rep-almacen .rep-stat-pill.ingreso { background: #ecfdf5; color: #047857; border-color: #bbf7d0; }
.page-rep-almacen .rep-stat-pill.salida { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
.page-rep-almacen .rep-bar {
    height: 6px;
    background: #e8f0ea;
    border-radius: 999px;
    overflow: hidden;
    display: flex;
}
.page-rep-almacen .rep-bar-ing { background: #4a7c59; min-width: 2px; }
.page-rep-almacen .rep-bar-sal { background: #f59e0b; min-width: 2px; }
.page-rep-almacen .rep-bar-fill { background: #4a7c59; height: 100%; border-radius: 999px; min-width: 4px; }
.page-rep-almacen .rep-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: .75rem;
    padding-top: .65rem;
    border-top: 1px dashed #e2e8f0;
    font-size: .78rem;
    color: #64748b;
}
.page-rep-almacen .rep-empty {
    text-align: center;
    padding: 1.5rem .5rem;
    color: #94a3b8;
    font-size: .88rem;
}
.page-rep-almacen .rep-empty i { display: block; font-size: 1.5rem; margin-bottom: .4rem; opacity: .6; }
.page-rep-almacen .rep-rec-list { list-style: none; margin: 0; padding: 0; }
.page-rep-almacen .rep-rec-item {
    display: flex;
    gap: .75rem;
    align-items: flex-start;
    transition: background .15s ease;
}
.page-rep-almacen .rep-rec-item:hover { background: #f8fafc; }
.page-rep-almacen .rep-rec-marker {
    width: 4px;
    min-height: 44px;
    border-radius: 999px;
    flex-shrink: 0;
    margin-top: .15rem;
}
.page-rep-almacen .rep-rec-marker.ingreso { background: #4a7c59; }
.page-rep-almacen .rep-rec-marker.salida { background: #f59e0b; }
.page-rep-almacen .rep-rec-body { flex: 1; min-width: 0; }
.page-rep-almacen .rep-rec-sub {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem .65rem;
    font-size: .78rem;
    color: #64748b;
}
.page-rep-almacen .rep-rec-link {
    font-size: .76rem;
    font-weight: 600;
    color: #0284c7;
    white-space: nowrap;
}
.page-rep-almacen .rep-nav {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1rem;
}
.page-rep-almacen .rep-nav a {
    font-size: .78rem;
    font-weight: 600;
    padding: .35rem .75rem;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    text-decoration: none;
}
.page-rep-almacen .rep-nav a:hover { border-color: #86efac; color: #047857; background: #f0fdf4; }
</style>
@endpush

@section('content')
@php
    $prefijo = $rutaPrefijo ?? 'almacen-agricola';
    $stockMax = max(1, (float) ($stockPorAlmacen->max('stock') ?? 0));
    $stockTotal = (float) $stockPorAlmacen->sum('stock');
    $totalIngProd = (int) $resumenProducto->sum('ingresos');
    $totalSalProd = (int) $resumenProducto->sum('salidas');
    $totalKgIngProd = (float) $resumenProducto->sum('cantidad_ingresos');
    $totalKgSalProd = (float) $resumenProducto->sum('cantidad_salidas');
    $urlMovimientos = route($prefijo.'.movimientos.index');
    $paramsPeriodo = array_filter([
        'almacenid' => $almacenId ?: null,
        'fecha_desde' => $fechaDesde,
        'fecha_hasta' => $fechaHasta,
        'periodo' => 'personalizado',
    ]);
@endphp

<div class="modulo-inv page-rep-almacen">

    <div class="rep-hero">
        <div class="rep-hero__title"><i class="fas fa-chart-bar text-success mr-1"></i> Cómo leer este reporte</div>
        <p class="rep-hero__text">
            Las tarjetas superiores cuentan <strong>operaciones</strong> (cada ingreso o salida registrado).
            Las tablas inferiores muestran <strong>kilogramos o unidades</strong> por producto y el <strong>stock actual</strong> en almacén.
        </p>
        <div class="rep-hero__grid">
            <div class="rep-hero__item"><strong>1. Resumen del período</strong>Cantidad de movimientos de entrada y salida.</div>
            <div class="rep-hero__item"><strong>2. Por producto</strong>Kg/un. movidos y número de operaciones.</div>
            <div class="rep-hero__item"><strong>3. Stock actual</strong>Inventario acumulado hoy, sin depender del filtro de fechas.</div>
        </div>
    </div>

    <div class="rep-nav">
        <a href="#rep-filtros"><i class="fas fa-filter mr-1"></i>Filtros</a>
        <a href="#rep-productos"><i class="fas fa-boxes mr-1"></i>Por producto</a>
        <a href="#rep-stock"><i class="fas fa-warehouse mr-1"></i>Stock</a>
        <a href="#rep-recientes"><i class="fas fa-history mr-1"></i>Recientes</a>
        <a href="{{ $urlMovimientos }}"><i class="fas fa-dolly mr-1"></i>Ir a movimientos</a>
    </div>

    <div class="row rep-kpi-row mb-1">
        <div class="col-md-4 rep-kpi-col">
            <a href="{{ $urlMovimientos.'?'.http_build_query(array_merge($paramsPeriodo, ['naturaleza' => 'ingreso'])) }}" class="rep-kpi-link">
                <div class="small-box small-box-green mb-0">
                    <div class="inner">
                        <h3>{{ $totalIngresos ?? 0 }}</h3>
                        <p>Ingresos registrados</p>
                        <small>Operaciones de entrada en el período · clic para ver listado</small>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-down"></i></div>
                </div>
            </a>
        </div>
        <div class="col-md-4 rep-kpi-col">
            <a href="{{ $urlMovimientos.'?'.http_build_query(array_merge($paramsPeriodo, ['naturaleza' => 'salida'])) }}" class="rep-kpi-link">
                <div class="small-box small-box-yellow mb-0">
                    <div class="inner">
                        <h3>{{ $totalSalidas ?? 0 }}</h3>
                        <p>Salidas registradas</p>
                        <small>Operaciones de salida en el período · clic para ver listado</small>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-up"></i></div>
                </div>
            </a>
        </div>
        <div class="col-md-4 rep-kpi-col">
            <a href="{{ $urlMovimientos.'?'.http_build_query($paramsPeriodo) }}" class="rep-kpi-link">
                <div class="small-box small-box-blue mb-0">
                    <div class="inner">
                        <h3>{{ $movimientos->count() }}</h3>
                        <p>Movimientos en detalle</p>
                        <small>Últimos registros del filtro · clic para explorar</small>
                    </div>
                    <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                </div>
            </a>
        </div>
    </div>

    <div class="card rep-card" id="rep-filtros">
        <div class="card-header py-3">
            <h3 class="rep-section-title mb-0"><i class="fas fa-filter"></i> Filtros del reporte</h3>
            <p class="rep-section-sub mb-0">Ajuste almacén y fechas. El stock actual no cambia con el período.</p>
        </div>
        <form method="GET" action="{{ route($prefijo.'.movimientos.reportes') }}">
            <div class="card-body">
                <div class="mb-3 quick-period">
                    <label class="d-block mb-2 text-muted small font-weight-bold">Período rápido</label>
                    @php
                        $periodos = [
                            'hoy' => 'Hoy',
                            '7d' => 'Últimos 7 días',
                            '30d' => 'Últimos 30 días',
                            '90d' => 'Últimos 90 días',
                            'mes_actual' => 'Mes actual',
                            'mes_pasado' => 'Mes pasado',
                        ];
                    @endphp
                    @foreach($periodos as $clave => $label)
                        <button type="submit" name="periodo" value="{{ $clave }}"
                                class="btn btn-sm {{ $periodoActivo === $clave ? 'btn-success' : 'btn-outline-success' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4 mb-md-0">
                        <label class="small text-muted">Almacén</label>
                        @include('partials.selector-catalogo', [
                            'id' => 'rep_almacen_filtro',
                            'name' => 'almacenid',
                            'value' => $almacenId ?: '',
                            'labelSelected' => $almacenNombreFiltro ?? '',
                            'endpoint' => route('catalogo-selector.almacenes'),
                            'params' => ['ambito' => $ambito ?? 'agricola'],
                            'title' => 'Filtrar reporte por almacén',
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
                    <div class="form-group col-md-3 mb-md-0">
                        <label class="small text-muted">Fecha desde</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_desde" value="{{ $fechaDesde }}">
                    </div>
                    <div class="form-group col-md-3 mb-md-0">
                        <label class="small text-muted">Fecha hasta</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="{{ $fechaHasta }}">
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end mb-md-0">
                        <button class="btn btn-success btn-sm w-100" name="periodo" value="personalizado">
                            <i class="fas fa-search mr-1"></i> Aplicar
                        </button>
                    </div>
                </div>
                <p class="text-muted small mb-0 mt-2">
                    Período activo:
                    <strong>{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}</strong>
                    —
                    <strong>{{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</strong>
                    @if($almacenNombreFiltro)
                        · Almacén: <strong>{{ $almacenNombreFiltro }}</strong>
                    @endif
                </p>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3 mb-lg-0">
            <div class="card rep-card h-100" id="rep-productos">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="rep-section-title mb-0"><i class="fas fa-boxes"></i> Movimientos por producto</h3>
                        <p class="rep-section-sub">Cantidades del período filtrado</p>
                    </div>
                    @if($resumenProducto->isNotEmpty())
                        <span class="badge badge-success">{{ $resumenProducto->count() }} producto{{ $resumenProducto->count() === 1 ? '' : 's' }}</span>
                    @endif
                </div>
                <div class="rep-panel">
                    @forelse($resumenProducto as $item)
                        @php
                            $ing = (int) $item->ingresos;
                            $sal = (int) $item->salidas;
                            $movTotal = max(1, $ing + $sal);
                            $pctIng = round(($ing / $movTotal) * 100, 1);
                            $pctSal = round(($sal / $movTotal) * 100, 1);
                            $kgIng = (float) ($item->cantidad_ingresos ?? 0);
                            $kgSal = (float) ($item->cantidad_salidas ?? 0);
                        @endphp
                        <div class="rep-prod-item">
                            <div class="rep-item-head">
                                <span class="rep-item-name">{{ $item->producto }}</span>
                                <span class="rep-item-value">{{ number_format($kgIng, 2) }} entradas · {{ number_format($kgSal, 2) }} salidas</span>
                            </div>
                            <div class="rep-stat-row">
                                <span class="rep-stat-pill ingreso"><i class="fas fa-arrow-down"></i> {{ $ing }} operación{{ $ing === 1 ? '' : 'es' }}</span>
                                <span class="rep-stat-pill salida"><i class="fas fa-arrow-up"></i> {{ $sal }} operación{{ $sal === 1 ? '' : 'es' }}</span>
                            </div>
                            <div class="rep-bar" title="Proporción de operaciones: entradas vs salidas">
                                @if($ing > 0)<div class="rep-bar-ing" style="width: {{ $pctIng }}%;"></div>@endif
                                @if($sal > 0)<div class="rep-bar-sal" style="width: {{ $pctSal }}%;"></div>@endif
                            </div>
                        </div>
                    @empty
                        <div class="rep-empty"><i class="fas fa-boxes"></i>Sin movimientos en este período</div>
                    @endforelse
                    @if($resumenProducto->isNotEmpty())
                        <div class="rep-meta">
                            <span>{{ $totalIngProd }} entradas · {{ $totalSalProd }} salidas</span>
                            <strong class="text-success">{{ number_format($totalKgIngProd, 2) }} kg/un. entraron · {{ number_format($totalKgSalProd, 2) }} salieron</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card rep-card h-100" id="rep-stock">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="rep-section-title mb-0"><i class="fas fa-warehouse"></i> Stock actual por almacén</h3>
                        <p class="rep-section-sub">Inventario acumulado hoy (no depende del período)</p>
                    </div>
                    @if($stockPorAlmacen->isNotEmpty())
                        <span class="badge badge-success">{{ $stockPorAlmacen->count() }} almacén{{ $stockPorAlmacen->count() === 1 ? '' : 'es' }}</span>
                    @endif
                </div>
                <div class="rep-panel">
                    @forelse($stockPorAlmacen as $item)
                        @php
                            $stockValor = (float) $item->stock;
                            $porcentaje = min(100, round(($stockValor / $stockMax) * 100, 1));
                        @endphp
                        <div class="rep-stock-item">
                            <div class="rep-item-head">
                                <span class="rep-item-name">{{ $item->almacen }}</span>
                                <span class="rep-item-value">{{ number_format($stockValor, 2) }} kg/un.</span>
                            </div>
                            <div class="rep-bar" title="Comparado con el almacén de mayor stock del listado">
                                <div class="rep-bar-fill" style="width: {{ $porcentaje }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rep-empty"><i class="fas fa-warehouse"></i>Sin stock registrado</div>
                    @endforelse
                    @if($stockPorAlmacen->isNotEmpty())
                        <div class="rep-meta">
                            <span>Total acumulado en almacenes listados</span>
                            <strong class="text-success">{{ number_format($stockTotal, 2) }} kg/un.</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card rep-card mb-0" id="rep-recientes">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="rep-section-title mb-0"><i class="fas fa-history"></i> Movimientos recientes del período</h3>
                <p class="rep-section-sub">Detalle de las últimas operaciones incluidas en el filtro</p>
            </div>
            <span class="badge badge-success">{{ $movimientos->count() }} registro{{ $movimientos->count() === 1 ? '' : 's' }}</span>
        </div>
        <div class="rep-panel">
            <ul class="rep-rec-list">
                @forelse($movimientos as $mov)
                    @php
                        $esIngreso = $mov->tipo?->naturaleza === 'ingreso';
                        $unidad = $mov->insumo?->unidadMedida?->abreviatura ?? 'kg/un.';
                        $urlVer = route($prefijo.'.movimientos.show', $mov);
                    @endphp
                    <li class="rep-rec-item">
                        <div class="rep-rec-marker {{ $esIngreso ? 'ingreso' : 'salida' }}"></div>
                        <div class="rep-rec-body">
                            <div class="rep-item-head">
                                <span class="rep-item-name">{{ $mov->insumo?->nombre ?? '—' }}</span>
                                <span class="rep-item-value">
                                    {{ number_format((float) $mov->cantidad, 2) }} {{ $unidad }}
                                </span>
                            </div>
                            <div class="rep-rec-sub">
                                <span class="rep-stat-pill {{ $esIngreso ? 'ingreso' : 'salida' }}">
                                    <i class="fas fa-arrow-{{ $esIngreso ? 'down' : 'up' }}"></i>
                                    {{ $mov->tipo?->nombre ?? 'Movimiento' }}
                                </span>
                                <span><i class="fas fa-calendar-alt mr-1"></i>{{ optional($mov->fecha)->format('d/m/Y') }}</span>
                                <span><i class="fas fa-warehouse mr-1"></i>{{ $mov->almacen?->nombre ?? '—' }}</span>
                                <a href="{{ $urlVer }}" class="rep-rec-link"><i class="fas fa-eye mr-1"></i>Ver detalle</a>
                            </div>
                        </div>
                    </li>
                @empty
                    <li><div class="rep-empty"><i class="fas fa-history"></i>Sin movimientos en el período</div></li>
                @endforelse
            </ul>
            @if($movimientos->isNotEmpty())
                <div class="rep-meta">
                    <span>Mostrando hasta 200 registros del período</span>
                    <a href="{{ $urlMovimientos.'?'.http_build_query($paramsPeriodo) }}" class="rep-rec-link">Ver todos en movimientos</a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
