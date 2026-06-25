@extends('reportes.layout-reporte')

@php
    $kpis = $datos['kpis'] ?? [];
    $detalle = $datos['detalleAlmacen'] ?? collect();
    $resumenAmbito = $datos['resumenAmbito'] ?? collect();
    $kpisDisplay = [
        ['value' => number_format($kpis['stock'] ?? 0, 0).' kg', 'label' => 'Stock total'],
        ['value' => $kpis['almacenes'] ?? 0, 'label' => 'Almacenes'],
        ['value' => $kpis['productos'] ?? 0, 'label' => 'Referencias'],
        ['value' => $kpis['criticos'] ?? 0, 'label' => 'Bajo mínimo'],
    ];
    $kpisPdf = $kpisDisplay;
    $chartConfig = [
        'type' => 'bar',
        'label' => 'Stock (kg)',
        'labels' => $datos['chart']['labels'] ?? [],
        'values' => $datos['chart']['values'] ?? [],
    ];
    $exportConfig = [
        'filename' => 'stock_ambito',
        'sheet' => 'Stock por ámbito',
        'headers' => ['Ámbito', 'Almacén', 'Stock', 'Productos', 'Críticos'],
        'rows' => $detalle->map(fn ($r) => [
            $r['ambito'],
            $r['almacen'],
            number_format($r['stock'], 2),
            $r['productos'],
            $r['criticos'],
        ])->all(),
    ];
@endphp

@section('rpt_kpis')
    @include('reportes.partials.kpis', ['kpis' => $kpisDisplay])
@endsection

@section('rpt_body')
    <div class="row">
        <div class="col-lg-5 mb-3 mb-lg-0">
            <div class="rpt-panel h-100">
                <div class="rpt-panel__head"><i class="fas fa-chart-bar mr-1"></i>Stock por ámbito</div>
                <div class="rpt-panel__body">
                    <div class="rpt-chart-wrap">
                        <canvas id="rptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="rpt-panel">
                <div class="rpt-panel__head"><i class="fas fa-warehouse mr-1"></i>Detalle por almacén</div>
                <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ámbito</th>
                                <th>Almacén</th>
                                <th class="text-right">Stock</th>
                                <th class="text-right">Prod.</th>
                                <th class="text-right">Críticos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalle as $fila)
                                <tr>
                                    <td>{{ $fila['ambito'] }}</td>
                                    <td>{{ $fila['almacen'] }}</td>
                                    <td class="text-right">{{ number_format($fila['stock'], 2) }} kg</td>
                                    <td class="text-right">{{ $fila['productos'] }}</td>
                                    <td class="text-right">{{ $fila['criticos'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin inventario</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="rpt-panel mt-3">
        <div class="rpt-panel__head"><i class="fas fa-sitemap mr-1"></i>Resumen por ámbito</div>
        <div class="rpt-panel__body rpt-panel__body--table table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ámbito</th>
                        <th class="text-right">Almacenes</th>
                        <th class="text-right">Referencias</th>
                        <th class="text-right">Stock (kg)</th>
                        <th class="text-right">Bajo mínimo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resumenAmbito as $fila)
                        <tr>
                            <td class="font-weight-bold">{{ $fila['ambito'] }}</td>
                            <td class="text-right">{{ $fila['almacenes'] }}</td>
                            <td class="text-right">{{ $fila['productos'] }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($fila['stock'], 0) }} kg</td>
                            <td class="text-right {{ ($fila['criticos'] ?? 0) > 0 ? 'text-danger font-weight-bold' : '' }}">{{ $fila['criticos'] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin datos por ámbito</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
