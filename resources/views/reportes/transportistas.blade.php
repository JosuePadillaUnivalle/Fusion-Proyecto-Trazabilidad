@extends('reportes.layout-reporte')

@php
    $kpis = $datos['kpis'] ?? [];
    $tabla = $datos['tabla'] ?? collect();
    $chartConfig = [
        'type' => 'bar',
        'label' => 'Asignaciones',
        'labels' => $datos['chart']['labels'] ?? [],
        'values' => $datos['chart']['values'] ?? [],
    ];
    $exportConfig = [
        'filename' => 'transportistas',
        'sheet' => 'Transportistas',
        'headers' => ['Transportista', 'Asignaciones', 'Completados', 'Cancelados', 'Eficiencia %'],
        'rows' => $tabla->map(fn ($r) => [
            $r['nombre'],
            $r['asignaciones'],
            $r['completados'],
            $r['cancelados'],
            $r['eficiencia'].'%',
        ])->all(),
    ];
    $kpisDisplay = [
        ['value' => $kpis['transportistas'] ?? 0, 'label' => 'Transportistas activos'],
        ['value' => $kpis['asignaciones'] ?? 0, 'label' => 'Asignaciones'],
        ['value' => $kpis['promedio'] ?? 0, 'label' => 'Promedio / chofer'],
        ['value' => ($kpis['eficiencia'] ?? 0).'%', 'label' => 'Eficiencia global'],
    ];
    $kpisPdf = $kpisDisplay;
@endphp

@section('rpt_kpis')
    @include('reportes.partials.kpis', ['kpis' => $kpisDisplay])
@endsection

@section('rpt_body')
    <div class="row">
        <div class="col-lg-5 mb-3 mb-lg-0">
            <div class="rpt-panel h-100">
                <div class="rpt-panel__head"><i class="fas fa-chart-bar mr-1"></i>Top asignaciones</div>
                <div class="rpt-panel__body">
                    <div class="rpt-chart-wrap">
                        <canvas id="rptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="rpt-panel">
                <div class="rpt-panel__head"><i class="fas fa-truck mr-1"></i>Ranking</div>
                <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Transportista</th>
                                <th class="text-right">Asig.</th>
                                <th class="text-right">OK</th>
                                <th class="text-right">Canc.</th>
                                <th class="text-right">Efic.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tabla as $fila)
                                <tr>
                                    <td>{{ $fila['nombre'] }}</td>
                                    <td class="text-right font-weight-bold">{{ $fila['asignaciones'] }}</td>
                                    <td class="text-right">{{ $fila['completados'] }}</td>
                                    <td class="text-right">{{ $fila['cancelados'] }}</td>
                                    <td class="text-right">{{ $fila['eficiencia'] }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin asignaciones en el período</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
