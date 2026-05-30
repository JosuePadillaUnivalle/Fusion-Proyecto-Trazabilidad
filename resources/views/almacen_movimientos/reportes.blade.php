@extends('layouts.app')



@section('title', 'Reportes de almacén')

@section('page_title', 'Reportes de almacén')



@push('styles')

@include('partials.modulo-inventario-styles')

<style>

.page-rep-almacen .rep-kpi-row { margin-left: -8px; margin-right: -8px; }

.page-rep-almacen .rep-kpi-col { padding-left: 8px; padding-right: 8px; margin-bottom: 1rem; }

.page-rep-almacen .rep-card {

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    box-shadow: 0 2px 12px rgba(0,0,0,.06);

    margin-bottom: 1.25rem;

}

.page-rep-almacen .rep-card .card-header {
    background: linear-gradient(135deg, #f0f7f1, #fff);
    border-bottom: 1px solid #e8f0e9;
    border-radius: 12px 12px 0 0;
    padding: .85rem 1.15rem;
}
.page-rep-almacen .rep-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #2c5530;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .45rem;
}
.page-rep-almacen .rep-section-title i { color: #4a7c59; }
.page-rep-almacen .rep-section-spaced { margin-top: 1.75rem; }

.page-rep-almacen .small-box { border-radius: 12px; margin-bottom: 0; }

.page-rep-almacen .small-box .inner h3 { font-size: 2rem; }

.page-rep-almacen .small-box .inner p { font-size: .9rem; opacity: .95; }

.page-rep-almacen .small-box .inner small { display: block; margin-top: .25rem; opacity: .85; font-size: .75rem; }

.page-rep-almacen .quick-period .btn { margin: 0 .35rem .35rem 0; border-radius: 20px; }

.page-rep-almacen .table-modulo thead th { background: #f2f7f3; }

</style>

@endpush



@section('content')

<div class="modulo-inv page-rep-almacen">



    <div class="alert alert-light border mb-3">

        <i class="fas fa-chart-bar text-success mr-1"></i>

        Resumen de <strong>movimientos</strong> (cada registro cuenta como un ingreso o salida), no la suma de kilos.

        Use las tablas inferiores para ver cantidades por producto.

    </div>



    <div class="row rep-kpi-row mb-1">

        <div class="col-md-4 rep-kpi-col">

            <div class="small-box small-box-green mb-0">

                <div class="inner">

                    <h3>{{ $totalIngresos ?? 0 }}</h3>

                    <p>Ingresos registrados</p>

                    <small>Operaciones de entrada en el período</small>

                </div>

                <div class="icon"><i class="fas fa-arrow-down"></i></div>

            </div>

        </div>

        <div class="col-md-4 rep-kpi-col">

            <div class="small-box small-box-yellow mb-0">

                <div class="inner">

                    <h3>{{ $totalSalidas ?? 0 }}</h3>

                    <p>Salidas registradas</p>

                    <small>Operaciones de salida en el período</small>

                </div>

                <div class="icon"><i class="fas fa-arrow-up"></i></div>

            </div>

        </div>

        <div class="col-md-4 rep-kpi-col">

            <div class="small-box small-box-blue mb-0">

                <div class="inner">

                    <h3>{{ $movimientos->count() }}</h3>

                    <p>Movimientos listados</p>

                    <small>Últimos registros del filtro aplicado</small>

                </div>

                <div class="icon"><i class="fas fa-exchange-alt"></i></div>

            </div>

        </div>

    </div>



    <div class="card rep-card">

        <div class="card-header py-3">
            <h3 class="rep-section-title mb-0"><i class="fas fa-filter"></i> Filtros del reporte</h3>
        </div>

        <form method="GET" action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.reportes') }}">

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

                        <button type="submit"

                                name="periodo"

                                value="{{ $clave }}"

                                class="btn btn-sm {{ $periodoActivo === $clave ? 'btn-success' : 'btn-outline-success' }}">

                            {{ $label }}

                        </button>

                    @endforeach

                </div>

                <div class="form-row">

                    <div class="form-group col-md-4 mb-md-0">

                        <label class="small text-muted">Almacén</label>

                        <select class="form-control form-control-sm" name="almacenid">

                            <option value="">Todos</option>

                            @foreach($almacenes as $almacen)

                                <option value="{{ $almacen->almacenid }}" @selected((int) $almacenId === (int) $almacen->almacenid)>{{ $almacen->nombre }}</option>

                            @endforeach

                        </select>

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

                </p>

            </div>

        </form>

    </div>



    <div class="row">

        <div class="col-lg-6">

            <div class="card rep-card h-100">

                <div class="card-header">
                    <h3 class="rep-section-title mb-0">
                        <i class="fas fa-boxes"></i> Movimientos por producto
                    </h3>
                </div>

                <div class="table-responsive">

                    <table class="table table-modulo table-sm table-hover mb-0">

                        <thead>

                            <tr>

                                <th>Producto</th>

                                <th class="text-center">Nº ingresos</th>

                                <th class="text-center">Nº salidas</th>

                                <th class="text-right">Kg/un. ingresados</th>

                            </tr>

                        </thead>

                        <tbody>

                        @forelse($resumenProducto as $item)

                            <tr>

                                <td>{{ $item->producto }}</td>

                                <td class="text-center"><span class="badge badge-success">{{ (int) $item->ingresos }}</span></td>

                                <td class="text-center"><span class="badge badge-warning">{{ (int) $item->salidas }}</span></td>

                                <td class="text-right">{{ number_format((float) ($item->cantidad_ingresos ?? 0), 2) }}</td>

                            </tr>

                        @empty

                            <tr><td colspan="4" class="text-center text-muted py-4">Sin movimientos en este período</td></tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="col-lg-6">

            <div class="card rep-card h-100">

                <div class="card-header">
                    <h3 class="rep-section-title mb-0">
                        <i class="fas fa-warehouse"></i> Stock actual por almacén
                    </h3>
                </div>

                <div class="table-responsive">

                    <table class="table table-modulo table-sm table-hover mb-0">

                        <thead><tr><th>Almacén</th><th class="text-right">Stock total</th></tr></thead>

                        <tbody>

                        @forelse($stockPorAlmacen as $item)

                            <tr>

                                <td>{{ $item->almacen }}</td>

                                <td class="text-right">{{ number_format((float) $item->stock, 2) }}</td>

                            </tr>

                        @empty

                            <tr><td colspan="2" class="text-center text-muted py-4">Sin stock registrado</td></tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>



    <div class="card rep-card mb-0 rep-section-spaced">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="rep-section-title mb-0">
                <i class="fas fa-history"></i> Movimientos recientes
            </h3>
            <span class="badge badge-success">{{ $movimientos->count() }} registros</span>
        </div>

        <div class="table-responsive">

            <table class="table table-modulo table-sm table-hover mb-0">

                <thead>

                    <tr>

                        <th>Fecha</th>

                        <th>Almacén</th>

                        <th>Producto</th>

                        <th>Tipo</th>

                        <th class="text-right">Cantidad</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($movimientos as $mov)

                        <tr>

                            <td>{{ optional($mov->fecha)->format('d/m/Y') }}</td>

                            <td>{{ $mov->almacen?->nombre }}</td>

                            <td>{{ $mov->insumo?->nombre }}</td>

                            <td>

                                <span class="badge badge-{{ $mov->tipo?->naturaleza === 'ingreso' ? 'success' : 'warning' }}">

                                    {{ $mov->tipo?->nombre }}

                                </span>

                            </td>

                            <td class="text-right">

                                {{ number_format((float) $mov->cantidad, 2) }}

                                <small class="text-muted">{{ $mov->insumo?->unidadMedida?->abreviatura }}</small>

                            </td>

                        </tr>

                    @empty

                        <tr><td colspan="5" class="text-center text-muted py-4">Sin movimientos en el período</td></tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>



</div>

@endsection

