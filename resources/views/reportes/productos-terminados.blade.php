@extends('reportes.layout-reporte')

@php
    $kpis = $datos['kpis'] ?? [];
    $tabla = $datos['tabla'] ?? collect();
    $kpisDisplay = [
        ['value' => $kpis['productos'] ?? 0, 'label' => 'Referencias con stock'],
        ['value' => number_format($kpis['stock'] ?? 0, 0), 'label' => 'Unidades totales'],
        ['value' => $kpis['planta'] ?? 0, 'label' => 'En planta'],
        ['value' => $kpis['mayorista'] ?? 0, 'label' => 'En mayorista'],
    ];
    $kpisPdf = $kpisDisplay;
    $chartConfig = [
        'type' => 'bar',
        'label' => 'Stock',
        'labels' => $datos['chart']['labels'] ?? [],
        'values' => $datos['chart']['values'] ?? [],
    ];
    $exportConfig = [
        'filename' => 'productos_terminados',
        'sheet' => 'Productos terminados',
        'headers' => ['Producto', 'Almacén', 'Ámbito', 'Stock', 'Unidad'],
        'rows' => $tabla->map(fn ($r) => [
            $r['nombre'],
            $r['almacen'],
            $r['ambito'],
            number_format($r['stock'], 2),
            $r['unidad'],
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
                <div class="rpt-panel__head"><i class="fas fa-chart-bar mr-1"></i>Stock por ámbito</div>
                <div class="rpt-panel__body">
                    <div class="rpt-chart-wrap">
                        <canvas id="rptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="rpt-panel">
                <div class="rpt-panel__head"><i class="fas fa-box-open mr-1"></i>Catálogo con existencias</div>
                <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Almacén</th>
                                <th>Ámbito</th>
                                <th class="text-right">Stock</th>
                                <th>Unidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tabla as $fila)
                                <tr>
                                    <td>{{ $fila['nombre'] }}</td>
                                    <td>{{ $fila['almacen'] }}</td>
                                    <td>{{ $fila['ambito'] }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($fila['stock'], 2) }}</td>
                                    <td>{{ $fila['unidad'] }}</td>
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
@endsection
