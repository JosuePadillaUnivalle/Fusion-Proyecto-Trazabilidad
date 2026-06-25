@extends('reportes.layout-reporte')

@php
    $kpis = $datos['kpis'] ?? [];
    $tabla = $datos['tabla'] ?? collect();
    $resumenProducto = $datos['resumenProducto'] ?? collect();
    $detallePresentacion = $datos['detallePresentacion'] ?? collect();
    $detallePlanta = $datos['detallePlanta'] ?? collect();
    $kpisDisplay = [
        ['value' => $kpis['productos'] ?? 0, 'label' => 'Referencias con stock'],
        ['value' => number_format($kpis['stock_kg'] ?? 0, 0).' kg', 'label' => 'Stock total (kg)'],
        ['value' => $kpis['planta'] ?? 0, 'label' => 'En planta'],
        ['value' => $kpis['mayorista'] ?? 0, 'label' => 'En mayorista'],
        ['value' => $kpis['lotes'] ?? 0, 'label' => 'Lotes / ubicaciones'],
        ['value' => $kpis['presentaciones'] ?? 0, 'label' => 'Presentaciones'],
    ];
    $kpisPdf = $kpisDisplay;
    $chartConfig = [
        'type' => 'bar',
        'label' => 'Stock (kg)',
        'labels' => $datos['chart']['labels'] ?? [],
        'values' => $datos['chart']['values'] ?? [],
    ];
    $exportConfig = [
        'filename' => 'productos_terminados',
        'sheet' => 'Productos terminados',
        'headers' => ['Producto', 'Almacén', 'Ámbito', 'Stock', 'Kg', 'Unidad', 'Presentaciones'],
        'rows' => $tabla->map(fn ($r) => [
            $r['nombre'],
            $r['almacen'],
            $r['ambito'],
            number_format($r['stock'], 2),
            number_format($r['stock_kg'] ?? $r['stock'], 2),
            $r['unidad'],
            $r['presentaciones'] ?? 0,
        ])->all(),
    ];
@endphp

@section('rpt_kpis')
    @include('reportes.partials.kpis', ['kpis' => $kpisDisplay])
@endsection

@section('rpt_body')
    <div class="row">
        <div class="col-lg-4 mb-3 mb-lg-0">
            <div class="rpt-panel h-100">
                <div class="rpt-panel__head"><i class="fas fa-chart-bar mr-1"></i>Stock por ámbito (kg)</div>
                <div class="rpt-panel__body">
                    <div class="rpt-chart-wrap">
                        <canvas id="rptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="rpt-panel">
                <div class="rpt-panel__head"><i class="fas fa-layer-group mr-1"></i>Resumen por producto</div>
                <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-right">Refs.</th>
                                <th class="text-right">Planta (kg)</th>
                                <th class="text-right">Mayorista (kg)</th>
                                <th class="text-right">Total (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resumenProducto as $fila)
                                <tr>
                                    <td class="font-weight-bold">{{ $fila['nombre'] }}</td>
                                    <td class="text-right">{{ $fila['referencias'] }}</td>
                                    <td class="text-right">{{ number_format($fila['planta_kg'], 1) }}</td>
                                    <td class="text-right">{{ number_format($fila['mayorista_kg'], 1) }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($fila['stock_kg'], 1) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin productos terminados con stock</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="rpt-panel mt-3">
        <div class="rpt-panel__head"><i class="fas fa-box-open mr-1"></i>Existencias por almacén</div>
        <div class="rpt-panel__body rpt-panel__body--table table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Almacén</th>
                        <th>Ámbito</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Kg equiv.</th>
                        <th>Unidad</th>
                        <th class="text-right">Pres.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tabla as $fila)
                        <tr>
                            <td>{{ $fila['nombre'] }}</td>
                            <td>{{ $fila['almacen'] }}</td>
                            <td>{{ $fila['ambito'] }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($fila['stock'], 2) }}</td>
                            <td class="text-right">{{ number_format($fila['stock_kg'] ?? $fila['stock'], 2) }}</td>
                            <td>{{ $fila['unidad'] }}</td>
                            <td class="text-right">{{ $fila['presentaciones'] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Sin referencias en catálogo</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($detallePresentacion->isNotEmpty())
        <div class="rpt-panel mt-3">
            <div class="rpt-panel__head"><i class="fas fa-cubes mr-1"></i>Detalle por presentación y lote (mayorista)</div>
            <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Presentación</th>
                            <th>Empaque</th>
                            <th>Lote</th>
                            <th>Almacén</th>
                            <th class="text-right">Unidades</th>
                            <th class="text-right">Kg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detallePresentacion as $fila)
                            <tr>
                                <td>{{ $fila['producto'] }}</td>
                                <td>{{ $fila['presentacion'] }}</td>
                                <td>{{ $fila['empaque'] }}</td>
                                <td><code class="small">{{ $fila['lote'] }}</code></td>
                                <td>{{ $fila['almacen'] }}</td>
                                <td class="text-right">{{ number_format($fila['unidades'], 0) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($fila['kg'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($detallePlanta->isNotEmpty())
        <div class="rpt-panel mt-3">
            <div class="rpt-panel__head"><i class="fas fa-industry mr-1"></i>Almacenaje en planta (lotes de producción)</div>
            <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Almacén</th>
                            <th>Ubicación</th>
                            <th class="text-right">Cantidad</th>
                            <th>Unidad</th>
                            <th>Fecha ingreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detallePlanta as $fila)
                            <tr>
                                <td class="font-weight-bold">{{ $fila['producto'] }}</td>
                                <td><code class="small">{{ $fila['lote'] }}</code></td>
                                <td>{{ $fila['almacen'] }}</td>
                                <td>{{ $fila['ubicacion'] }}</td>
                                <td class="text-right">{{ number_format($fila['cantidad'], 2) }}</td>
                                <td>{{ $fila['unidad'] }}</td>
                                <td>{{ $fila['fecha'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
